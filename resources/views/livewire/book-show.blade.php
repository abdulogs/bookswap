<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Book;
use App\Models\BookRequest;
use App\Models\Notification;
use App\Mail\BookRequestNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    #[Rule('nullable|string|max:500')]
    public $message = '';

    #[Rule('required|in:borrow,swap')]
    public $requestType = 'borrow';

    #[Rule('required_if:requestType,swap|nullable|exists:books,id')]
    public $swapBookId = null;

    public $bookId;
    public $book;

    public function mount()
    {
        $this->bookId = Route::current()->parameter('book');
        $this->loadBook();
    }

    public function loadBook()
    {
        try {
            $this->book = Book::with('owner')->findOrFail($this->bookId);
        } catch (\Exception $e) {
            session()->flash('error', 'Book not found.');
            return redirect()->route('books.index');
        }
    }

    public function getCanRequestProperty()
    {
        if (!$this->book) {
            return false;
        }

        return auth()->check() && auth()->id() !== $this->book->user_id && $this->book->status === 'Available';
    }

    public function getMyBooksProperty()
    {
        if (!auth()->check()) {
            return collect();
        }

        return \App\Models\Book::where('user_id', auth()->id())
            ->where('status', 'Available')
            ->where('id', '!=', $this->bookId)
            ->get();
    }

    public function getHasPendingRequestProperty()
    {
        if (!auth()->check() || !$this->book) {
            return false;
        }

        return BookRequest::where('book_id', $this->book->id)
            ->where('borrower_id', auth()->id())
            ->whereIn('status', ['Pending', 'Approved'])
            ->exists();
    }

    public function requestBook()
    {
        try {
            $this->validate();

            // Check if user is authenticated
            if (!auth()->check()) {
                session()->flash('error', 'You must be logged in to request a book.');
                return;
            }

            // Check if book exists and is available
            if (!$this->book || $this->book->status !== 'Available') {
                session()->flash('error', 'This book is not available for borrowing.');
                return;
            }

            // Check if user is not the owner
            if (auth()->id() === $this->book->user_id) {
                session()->flash('error', 'You cannot request your own book.');
                return;
            }

            // Check if request already exists
            if ($this->hasPendingRequest) {
                session()->flash('error', 'You have already requested this book.');
                return;
            }

            // For swap requests, validate swap book
            if ($this->requestType === 'swap') {
                if (!$this->swapBookId) {
                    $this->addError('swapBookId', 'Please select a book to swap.');
                    return;
                }

                // Check if swap book belongs to user
                $swapBook = \App\Models\Book::find($this->swapBookId);
                if (!$swapBook || $swapBook->user_id !== auth()->id()) {
                    $this->addError('swapBookId', 'Invalid book selected.');
                    return;
                }

                if ($swapBook->status !== 'Available') {
                    $this->addError('swapBookId', 'The selected book must be available.');
                    return;
                }
            }

            $bookRequest = BookRequest::create([
                'book_id' => $this->book->id,
                'borrower_id' => auth()->id(),
                'owner_id' => $this->book->user_id,
                'request_type' => $this->requestType,
                'swap_book_id' => $this->requestType === 'swap' ? $this->swapBookId : null,
                'message' => $this->message,
                'status' => 'Pending',
            ]);

            // Create notification for book owner
            $requestTypeText = $this->requestType === 'swap' ? 'swap' : 'borrow';
            $notification = Notification::create([
                'user_id' => $this->book->user_id,
                'type' => 'request_received',
                'title' => 'New Book Request',
                'message' => auth()->user()->name . " has requested to {$requestTypeText} '{$this->book->title}'",
                'notifiable_type' => BookRequest::class,
                'notifiable_id' => $bookRequest->id,
            ]);

            // Send email notification
            try {
                Mail::to($this->book->owner->email)->send(new BookRequestNotification($bookRequest, 'request_received'));
                $notification->update(['email_sent' => true]);
            } catch (\Exception $e) {
                // Log error but don't fail the request
            }

            $this->message = '';
            $this->requestType = 'borrow';
            $this->swapBookId = null;
            session()->flash('success', 'Book request sent successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while sending the request: ' . $e->getMessage());
        }
    }
};

?>

<section>
    <div class="max-w-6xl mx-auto">
        <!-- Back Button -->
        <div class="mb-8">
            <a href="{{ route('books.index') }}"
                class="inline-flex items-center text-indigo-600 hover:text-indigo-700 font-medium transition-colors group">
                <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Books
            </a>
        </div>

        <!-- Book Details -->
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
            <div class="p-12">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
                    <div class="lg:col-span-1">
                        <div class="w-full h-80 bg-slate-100 rounded-3xl overflow-hidden flex items-center justify-center">
                            @if($this->book->image)
                                <img src="{{ Storage::url($this->book->image) }}" alt="{{ $this->book->title }}" class="w-full h-full object-cover shadow-2xl">
                            @else
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <span class="text-5xl mb-2">📚</span>
                                    <span class="text-sm font-medium">No cover image</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="lg:col-span-2">
                        <div class="flex items-start justify-between mb-6">
                            <div class="flex-1">
                                <h1 class="text-5xl font-bold text-slate-800 mb-4">{{ $this->book->title }}</h1>
                                <p class="text-2xl text-slate-600 mb-4 font-light">by {{ $this->book->author }}</p>
                                @if($this->book->average_rating)
                                    <div class="flex items-center space-x-3 mb-6">
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-6 h-6 {{ $i <= round($this->book->average_rating) ? 'text-yellow-400 fill-current' : 'text-slate-300' }}" viewBox="0 0 20 20">
                                                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                                </svg>
                                            @endfor
                                        </div>
                                        <span class="text-xl font-bold text-slate-700">{{ $this->book->average_rating }}</span>
                                        <span class="text-base text-slate-500">({{ $this->book->total_ratings }} {{ Str::plural('rating', $this->book->total_ratings) }})</span>
                                    </div>
                                @else
                                    <div class="mb-6">
                                        <span class="text-base text-slate-400">No ratings yet</span>
                                    </div>
                                @endif
                            </div>
                            <div class="flex space-x-3">
                                <span
                                    class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold
                                           {{ $this->book->status === 'Available' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $this->book->status }}
                                </span>
                                <span
                                    class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                                    {{ $this->book->genre }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-800 mb-6">Book Information</h3>
                        <dl class="space-y-6">
                            <div class="flex items-center space-x-4">
                                <div
                                    class="w-12 h-12 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-2xl flex items-center justify-center">
                                    <span class="text-2xl">📖</span>
                                </div>
                                <div>
                                    <dt class="text-sm font-bold text-slate-500 uppercase tracking-wide">Condition</dt>
                                    <dd class="text-lg text-slate-800 font-medium">{{ $this->book->condition }}</dd>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div
                                    class="w-12 h-12 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-2xl flex items-center justify-center">
                                    <span class="text-2xl">👤</span>
                                </div>
                                <div>
                                    <dt class="text-sm font-bold text-slate-500 uppercase tracking-wide">Owner</dt>
                                    <dd class="text-lg text-slate-800 font-medium">{{ $this->book->owner->name }}</dd>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div
                                    class="w-12 h-12 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-2xl flex items-center justify-center">
                                    <span class="text-2xl">📍</span>
                                </div>
                                <div>
                                    <dt class="text-sm font-bold text-slate-500 uppercase tracking-wide">Location</dt>
                                    <dd class="text-lg text-slate-800 font-medium">{{ $this->book->location ?: 'Not specified' }}</dd>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div
                                    class="w-12 h-12 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-2xl flex items-center justify-center">
                                    <span class="text-2xl">📅</span>
                                </div>
                                <div>
                                    <dt class="text-sm font-bold text-slate-500 uppercase tracking-wide">Listed</dt>
                                    <dd class="text-lg text-slate-800 font-medium">
                                        {{ $this->book->created_at->diffForHumans() }}</dd>
                                </div>
                            </div>
                        </dl>

                        @if ($this->book->description)
                            <div class="mt-10">
                                <h3 class="text-2xl font-bold text-slate-800 mb-4">Description</h3>
                                <div class="bg-slate-50 rounded-2xl p-6">
                                    <p class="text-slate-700 text-lg leading-relaxed">{{ $this->book->description }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div>
                        @if ($this->canRequest && !$this->hasPendingRequest)
                            <div
                                class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-3xl p-8 border border-indigo-100">
                                <div class="text-center mb-6">
                                    <div
                                        class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                        <span class="text-white text-2xl">📚</span>
                                    </div>
                                    <h3 class="text-2xl font-bold text-slate-800 mb-2">Request to Borrow or Swap</h3>
                                    <p class="text-slate-600">Send a request to the book owner</p>
                                </div>

                                @if (session('error'))
                                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl">
                                        <p class="text-red-800 font-medium">{{ session('error') }}</p>
                                    </div>
                                @endif

                                @if (session('success'))
                                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl">
                                        <p class="text-emerald-800 font-medium">{{ session('success') }}</p>
                                    </div>
                                @endif

                                <form wire:submit.prevent="requestBook">
                                    <div class="mb-6">
                                        <label for="requestType" class="block text-sm font-bold text-slate-700 mb-3">
                                            Request Type *
                                        </label>
                                        <div class="grid grid-cols-2 gap-4">
                                            <label class="flex items-center p-4 border-2 rounded-2xl cursor-pointer transition-all {{ $this->requestType === 'borrow' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-300 hover:border-indigo-300' }}">
                                                <input type="radio" wire:model.live="requestType" value="borrow" class="mr-3">
                                                <span class="font-bold">Borrow</span>
                                            </label>
                                            <label class="flex items-center p-4 border-2 rounded-2xl cursor-pointer transition-all {{ $this->requestType === 'swap' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-300 hover:border-indigo-300' }}">
                                                <input type="radio" wire:model.live="requestType" value="swap" class="mr-3">
                                                <span class="font-bold">Swap</span>
                                            </label>
                                        </div>
                                        @error('requestType')
                                            <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    @if($this->requestType === 'swap')
                                        <div class="mb-6">
                                            <label for="swapBookId" class="block text-sm font-bold text-slate-700 mb-3">
                                                Select Book to Swap *
                                            </label>
                                            <select wire:model="swapBookId" id="swapBookId"
                                                class="w-full px-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm">
                                                <option value="">Select a book to swap</option>
                                                @foreach($this->myBooks as $myBook)
                                                    <option value="{{ $myBook->id }}">{{ $myBook->title }} by {{ $myBook->author }}</option>
                                                @endforeach
                                            </select>
                                            @error('swapBookId')
                                                <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span>
                                            @enderror
                                            @if($this->myBooks->isEmpty())
                                                <p class="text-amber-600 text-sm mt-2">You need to have at least one available book to swap. <a href="{{ route('books.create') }}" class="underline">Add a book</a></p>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="mb-6">
                                        <label for="message" class="block text-sm font-bold text-slate-700 mb-3">
                                            Message to Owner (Optional)
                                        </label>
                                        <textarea wire:model="message" id="message" rows="4"
                                            class="w-full px-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm resize-none"
                                            placeholder="Tell the owner why you'd like to {{ $this->requestType === 'swap' ? 'swap' : 'borrow' }} this book..."></textarea>
                                        @error('message')
                                            <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <button type="submit"
                                        class="w-full bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white px-6 py-4 rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                        <span wire:loading.remove>Send {{ ucfirst($this->requestType) }} Request</span>
                                        <span wire:loading>Sending...</span>
                                    </button>
                                </form>
                            </div>
                        @elseif($this->hasPendingRequest)
                            <div
                                class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-3xl p-8 border border-blue-100 text-center">
                                <div
                                    class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                    <span class="text-white text-2xl">✅</span>
                                </div>
                                <h3 class="text-2xl font-bold text-blue-800 mb-2">Request Sent</h3>
                                <p class="text-blue-700 text-lg">You have already requested to borrow this book. The
                                    owner will review your request.</p>
                            </div>
                        @elseif($this->book->status === 'Lent Out')
                            <div
                                class="bg-gradient-to-br from-amber-50 to-yellow-50 rounded-3xl p-8 border border-amber-100 text-center">
                                <div
                                    class="w-16 h-16 bg-gradient-to-br from-amber-500 to-yellow-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                    <span class="text-white text-2xl">⏳</span>
                                </div>
                                <h3 class="text-2xl font-bold text-amber-800 mb-2">Currently Unavailable</h3>
                                <p class="text-amber-700 text-lg">This book is currently lent out to another member.</p>
                            </div>
                        @elseif(auth()->id() === $this->book->user_id)
                            <div
                                class="bg-gradient-to-br from-slate-50 to-gray-50 rounded-3xl p-8 border border-slate-100 text-center">
                                <div
                                    class="w-16 h-16 bg-gradient-to-br from-slate-500 to-gray-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                    <span class="text-white text-2xl">👑</span>
                                </div>
                                <h3 class="text-2xl font-bold text-slate-800 mb-2">Your Book</h3>
                                <p class="text-slate-700 text-lg mb-6">This is your book. You can edit it or manage
                                    requests.</p>
                                <a href="{{ route('books.edit', $this->book) }}"
                                    class="inline-block bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white px-6 py-3 rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                    Edit Book
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
