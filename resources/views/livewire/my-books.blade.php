<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use App\Models\Book;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    public function getBooksProperty()
    {
        return Book::where('user_id', auth()->id())
            ->with('requests')
            ->latest()
            ->paginate(12);
    }

    public function deleteBook($bookId)
    {
        $book = Book::findOrFail($bookId);
        
        if ($book->user_id !== auth()->id()) {
            abort(403);
        }
        
        // Delete image if exists
        if ($book->image && Storage::disk('public')->exists($book->image)) {
            Storage::disk('public')->delete($book->image);
        }
        
        $book->delete();
        session()->flash('success', 'Book deleted successfully!');
    }

    public function toggleStatus($bookId)
    {
        $book = Book::findOrFail($bookId);
        
        if ($book->user_id !== auth()->id()) {
            abort(403);
        }
        
        $book->update([
            'status' => $book->status === 'Available' ? 'Lent Out' : 'Available'
        ]);
        
        session()->flash('success', 'Book status updated successfully!');
    }
};

?>

<section>
    <!-- Header Section -->
    <div class="text-center mb-16">
        <div class="w-24 h-24 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl">
            <span class="text-white text-4xl">📚</span>
        </div>
        <h1 class="text-5xl md:text-6xl font-bold text-slate-800 mb-6">
            My Books
        </h1>
        <p class="text-xl text-slate-600 max-w-3xl mx-auto font-light">
            Manage the books you've shared with the community
        </p>
    </div>

    <!-- Action Button -->
    <div class="text-center mb-12">
        <a href="{{ route('books.create') }}" 
           class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 hover:from-indigo-600 hover:via-purple-600 hover:to-pink-600 text-white rounded-2xl font-bold transition-all duration-300 shadow-xl hover:shadow-2xl transform hover:-translate-y-1">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add New Book
        </a>
    </div>

    @if(session('success'))
        <div class="bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200 rounded-2xl p-6 mb-12 text-center">
            <div class="flex items-center justify-center space-x-3">
                <div class="w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <p class="text-emerald-800 font-bold text-lg">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Books Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($this->books as $book)
            <div class="group bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl hover:shadow-2xl border border-white/20 overflow-hidden transform hover:-translate-y-2 transition-all duration-500">
                @if($book->image)
                    <div class="h-48 overflow-hidden">
                        <img src="{{ placeholder($book->image) }}" alt="{{ $book->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                @endif
                <div class="p-8">
                    <div class="flex items-center justify-between mb-6">
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold 
                                   {{ $book->status === 'Available' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $book->status }}
                        </span>
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                            {{ $book->genre }}
                        </span>
                    </div>

                    @if($book->average_rating)
                        <div class="flex items-center space-x-2 mb-4">
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= round($book->average_rating) ? 'text-yellow-400 fill-current' : 'text-slate-300' }}" viewBox="0 0 20 20">
                                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-sm font-bold text-slate-700">{{ $book->average_rating }}</span>
                            <span class="text-xs text-slate-500">({{ $book->total_ratings }})</span>
                        </div>
                    @endif
                    
                    <h3 class="text-xl font-bold text-slate-800 mb-3 group-hover:text-indigo-600 transition-colors duration-300">{{ $book->title }}</h3>
                    <p class="text-slate-600 mb-4 font-medium text-lg">by {{ $book->author }}</p>
                    
                    <div class="mb-6">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-100 text-slate-700">
                            {{ $book->condition }} condition
                        </span>
                    </div>
                    
                    @if($book->description)
                        <p class="text-slate-500 mb-6 text-base leading-relaxed">{{ Str::limit($book->description, 100) }}</p>
                    @endif
                    
                    <div class="mb-6 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <span class="text-slate-700 font-medium">Requests: {{ $book->requests->count() }}</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="space-x-2">
                            <button wire:click="toggleStatus({{ $book->id }})" 
                                    class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-medium hover:bg-slate-50 hover:border-indigo-400 hover:text-indigo-600 transition-all duration-300 text-sm">
                                {{ $book->status === 'Available' ? 'Mark as Lent' : 'Mark as Available' }}
                            </button>
                            <a href="{{ route('books.edit', $book) }}" 
                               class="px-4 py-2 rounded-xl border border-indigo-300 text-indigo-600 font-medium hover:bg-indigo-50 transition-all duration-300 text-sm">
                                Edit
                            </a>
                        </div>
                        <button wire:click="deleteBook({{ $book->id }})" 
                                onclick="return confirm('Are you sure you want to delete this book?')"
                                class="px-4 py-2 rounded-xl border border-red-300 text-red-600 font-medium hover:bg-red-50 transition-all duration-300 text-sm">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-20">
                <div class="w-32 h-32 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-8">
                    <span class="text-6xl">📚</span>
                </div>
                <p class="text-slate-600 text-xl mb-6 font-medium">You haven't added any books yet.</p>
                <a href="{{ route('books.create') }}" 
                   class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 hover:from-indigo-600 hover:via-purple-600 hover:to-pink-600 text-white rounded-2xl font-bold transition-all duration-300 shadow-xl hover:shadow-2xl transform hover:-translate-y-1">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Your First Book
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="py-20">
        @if ($this->books->hasPages())
            {{ $this->books->links() }}
        @endif
    </div>
</section>
