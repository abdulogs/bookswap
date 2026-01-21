<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\BookRequest;
use App\Models\Notification;
use App\Models\Dispute;
use App\Mail\BookRequestNotification;
use Illuminate\Support\Facades\Mail;

new class extends Component {
    public function getIncomingRequestsProperty()
    {
        return BookRequest::where('owner_id', auth()->id())
            ->with(['book', 'borrower', 'swapBook'])
            ->latest()
            ->paginate(10);
    }

    public function getOutgoingRequestsProperty()
    {
        return BookRequest::where('borrower_id', auth()->id())
            ->with(['book', 'owner', 'swapBook'])
            ->latest()
            ->paginate(10);
    }

    public function approveRequest($requestId)
    {
        $request = BookRequest::findOrFail($requestId);

        if ($request->owner_id !== auth()->id()) {
            abort(403);
        }

        $dueDate = now()->addDays(14); // Default 14 days borrowing period

        $request->update([
            'status' => 'Approved',
            'borrowed_at' => now(),
            'due_date' => $dueDate,
        ]);
        $request->book->update(['status' => 'Lent Out']);

        // Create notification for borrower
        $notification = Notification::create([
            'user_id' => $request->borrower_id,
            'type' => 'request_approved',
            'title' => 'Book Request Approved',
            'message' => "Your request for '{$request->book->title}' has been approved. Due date: {$dueDate->format('M d, Y')}",
            'notifiable_type' => BookRequest::class,
            'notifiable_id' => $request->id,
        ]);

        // Send email notification
        try {
            Mail::to($request->borrower->email)->send(new BookRequestNotification($request, 'request_approved'));
            $notification->update(['email_sent' => true]);
        } catch (\Exception $e) {
            // Log error but don't fail the request
        }

        session()->flash('success', 'Request approved successfully!');
    }

    public function rejectRequest($requestId)
    {
        $request = BookRequest::findOrFail($requestId);

        if ($request->owner_id !== auth()->id()) {
            abort(403);
        }

        $request->update(['status' => 'Rejected']);

        // Create notification for borrower
        $notification = Notification::create([
            'user_id' => $request->borrower_id,
            'type' => 'request_rejected',
            'title' => 'Book Request Rejected',
            'message' => "Your request for '{$request->book->title}' has been rejected.",
            'notifiable_type' => BookRequest::class,
            'notifiable_id' => $request->id,
        ]);

        // Send email notification
        try {
            Mail::to($request->borrower->email)->send(new BookRequestNotification($request, 'request_rejected'));
            $notification->update(['email_sent' => true]);
        } catch (\Exception $e) {
            // Log error but don't fail the request
        }

        session()->flash('success', 'Request rejected successfully!');
    }

    public function markAsReturned($requestId)
    {
        $request = BookRequest::findOrFail($requestId);

        if ($request->owner_id !== auth()->id()) {
            abort(403);
        }

        $request->update([
            'status' => 'Returned',
            'returned_at' => now(),
        ]);
        $request->book->update(['status' => 'Available']);

        // Create notification for borrower
        $notification = Notification::create([
            'user_id' => $request->borrower_id,
            'type' => 'book_returned',
            'title' => 'Book Returned',
            'message' => "The book '{$request->book->title}' has been marked as returned.",
            'notifiable_type' => BookRequest::class,
            'notifiable_id' => $request->id,
        ]);

        // Send email notification
        try {
            Mail::to($request->borrower->email)->send(new BookRequestNotification($request, 'book_returned'));
            $notification->update(['email_sent' => true]);
        } catch (\Exception $e) {
            // Log error but don't fail the request
        }

        session()->flash('success', 'Book marked as returned successfully!');
    }

    #[Rule('required|string|max:255')]
    public $disputeTitle = '';

    #[Rule('required|string|max:1000')]
    public $disputeDescription = '';

    public $showDisputeForm = false;
    public $selectedRequestId = null;

    public function openDisputeForm($requestId)
    {
        $this->selectedRequestId = $requestId;
        $this->showDisputeForm = true;
        $this->reset(['disputeTitle', 'disputeDescription']);
    }

    public function closeDisputeForm()
    {
        $this->showDisputeForm = false;
        $this->selectedRequestId = null;
        $this->reset(['disputeTitle', 'disputeDescription']);
    }

    public function submitDispute()
    {
        $this->validate();

        $request = BookRequest::findOrFail($this->selectedRequestId);

        // Ensure user is part of this request
        if (auth()->id() !== $request->borrower_id && auth()->id() !== $request->owner_id) {
            abort(403);
        }

        Dispute::create([
            'book_request_id' => $request->id,
            'reporter_id' => auth()->id(),
            'title' => $this->disputeTitle,
            'description' => $this->disputeDescription,
            'status' => 'open',
        ]);

        $this->closeDisputeForm();
        session()->flash('success', 'Dispute reported successfully! An admin will review it.');
    }
};

?>

<section>
    <!-- Header Section -->
    <div class="text-center mb-16">
        <div
            class="w-24 h-24 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl">
            <span class="text-white text-4xl">🤝</span>
        </div>
        <h1 class="text-5xl md:text-6xl font-bold text-slate-800 mb-6">
            Book Requests
        </h1>
        <p class="text-xl text-slate-600 max-w-3xl mx-auto font-light">
            Manage incoming and outgoing book requests from our community
        </p>
    </div>

    @if (session('success'))
        <div
            class="bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200 rounded-2xl p-6 mb-12 text-center">
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

    <!-- Dispute Form Modal -->
    @if ($this->showDisputeForm)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full p-8 max-h-[90vh] overflow-y-auto">
                <h2 class="text-3xl font-bold text-slate-800 mb-6">Report Dispute</h2>

                <form wire:submit="submitDispute">
                    <div class="mb-6">
                        <label for="disputeTitle" class="block text-sm font-bold text-slate-700 mb-3">Title *</label>
                        <input wire:model="disputeTitle" type="text" id="disputeTitle"
                            class="w-full px-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm"
                            placeholder="Brief title for the dispute">
                        @error('disputeTitle')
                            <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="disputeDescription" class="block text-sm font-bold text-slate-700 mb-3">Description
                            *</label>
                        <textarea wire:model="disputeDescription" id="disputeDescription" rows="6"
                            class="w-full px-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm resize-none"
                            placeholder="Describe the issue in detail..."></textarea>
                        @error('disputeDescription')
                            <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end space-x-4">
                        <button type="button" wire:click="closeDisputeForm"
                            class="px-6 py-3 border border-slate-300 rounded-2xl text-slate-700 font-bold hover:bg-slate-50 transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white rounded-2xl font-bold transition-all">
                            Submit Dispute
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Incoming Requests -->
    <div class="mb-16">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-slate-800 mb-4">Incoming Requests</h2>
            <p class="text-slate-600 text-lg">Requests from other members to borrow or swap your books</p>
        </div>

        @if ($this->incomingRequests->count() > 0)
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-gradient-to-r from-slate-50 to-gray-50">
                            <tr>
                                <th
                                    class="px-8 py-6 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Book</th>
                                <th
                                    class="px-8 py-6 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Borrower</th>
                                <th
                                    class="px-8 py-6 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-8 py-6 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Message</th>
                                <th
                                    class="px-8 py-6 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Due Date</th>
                                <th
                                    class="px-8 py-6 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white/50 divide-y divide-slate-200">
                            @foreach ($this->incomingRequests as $request)
                                <tr class="hover:bg-slate-50/50 transition-colors duration-200">
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        <div class="flex items-center space-x-4">
                                            <div
                                                class="w-12 h-12 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-2xl flex items-center justify-center">
                                                <span class="text-2xl">📖</span>
                                            </div>
                                            <div>
                                                <div class="text-lg font-bold text-slate-800">
                                                    {{ $request->book->title }}
                                                    @if ($request->request_type === 'swap')
                                                        <span
                                                            class="ml-2 px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-bold">Swap</span>
                                                    @else
                                                        <span
                                                            class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">Borrow</span>
                                                    @endif
                                                </div>
                                                <div class="text-slate-600">by {{ $request->book->author }}</div>
                                                @if ($request->request_type === 'swap' && $request->swapBook)
                                                    <div class="text-sm text-purple-600 mt-1">Swap with:
                                                        {{ $request->swapBook->title }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center shadow-md">
                                                <span
                                                    class="text-white text-sm font-bold">{{ substr($request->borrower->name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <div class="text-lg font-bold text-slate-800">
                                                    {{ $request->borrower->name }}</div>
                                                <div class="text-slate-600 text-sm">{{ $request->borrower->email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold
                                                   {{ $request->status === 'Pending'
                                                       ? 'bg-yellow-100 text-yellow-800'
                                                       : ($request->status === 'Approved'
                                                           ? 'bg-emerald-100 text-emerald-800'
                                                           : ($request->status === 'Rejected'
                                                               ? 'bg-red-100 text-red-800'
                                                               : 'bg-blue-100 text-blue-800')) }}">
                                            {{ $request->status }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="text-slate-700 max-w-xs">
                                            {{ $request->message ?: 'No message' }}
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        @if ($request->due_date)
                                            <div class="text-slate-700">
                                                <div class="font-medium">{{ $request->due_date->format('M d, Y') }}
                                                </div>
                                                @if ($request->due_date->isPast())
                                                    <span class="text-red-600 text-sm font-bold">Overdue</span>
                                                @elseif($request->due_date->diffInDays(now()) <= 3)
                                                    <span class="text-amber-600 text-sm font-bold">Due Soon</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-slate-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap text-sm font-bold">
                                        @if ($request->status === 'Pending')
                                            <div class="flex space-x-3">
                                                <button wire:click="approveRequest({{ $request->id }})"
                                                    class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                                    Approve
                                                </button>
                                                <button wire:click="rejectRequest({{ $request->id }})"
                                                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                                    Reject
                                                </button>
                                            </div>
                                        @elseif($request->status === 'Approved')
                                            <div class="flex space-x-2">
                                                <a href="{{ route('messages.index') }}?request={{ $request->id }}"
                                                    class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                                    Message
                                                </a>
                                                <button wire:click="markAsReturned({{ $request->id }})"
                                                    class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                                    Mark as Returned
                                                </button>
                                                <button wire:click="openDisputeForm({{ $request->id }})"
                                                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                                    Report Issue
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($this->incomingRequests->hasPages())
                <div class="mt-8 flex justify-center">
                    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 p-4">
                        {{ $this->incomingRequests->links() }}
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-16">
                <div
                    class="w-32 h-32 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-8">
                    <span class="text-6xl">📥</span>
                </div>
                <p class="text-slate-600 text-xl font-medium">No incoming requests yet.</p>
                <p class="text-slate-500 text-lg">When members request your books, they'll appear here.</p>
            </div>
        @endif
    </div>

    <!-- Outgoing Requests -->
    <div>
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-slate-800 mb-4">Outgoing Requests</h2>
            <p class="text-slate-600 text-lg">Your requests to borrow books from other members</p>
        </div>

        @if ($this->outgoingRequests->count() > 0)
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-gradient-to-r from-slate-50 to-gray-50">
                            <tr>
                                <th
                                    class="px-8 py-6 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Book</th>
                                <th
                                    class="px-8 py-6 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Owner</th>
                                <th
                                    class="px-8 py-6 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-8 py-6 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Message</th>
                                <th
                                    class="px-8 py-6 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Due Date</th>
                                <th
                                    class="px-8 py-6 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white/50 divide-y divide-slate-200">
                            @foreach ($this->outgoingRequests as $request)
                                <tr class="hover:bg-slate-50/50 transition-colors duration-200">
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        <div class="flex items-center space-x-4">
                                            <div
                                                class="w-12 h-12 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-2xl flex items-center justify-center">
                                                <span class="text-2xl">📖</span>
                                            </div>
                                            <div>
                                                <div class="text-lg font-bold text-slate-800">
                                                    {{ $request->book->title }}
                                                    @if ($request->request_type === 'swap')
                                                        <span
                                                            class="ml-2 px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-bold">Swap</span>
                                                    @else
                                                        <span
                                                            class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">Borrow</span>
                                                    @endif
                                                </div>
                                                <div class="text-slate-600">by {{ $request->book->author }}</div>
                                                @if ($request->request_type === 'swap' && $request->swapBook)
                                                    <div class="text-sm text-purple-600 mt-1">Swap with:
                                                        {{ $request->swapBook->title }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center shadow-md">
                                                <span
                                                    class="text-white text-sm font-bold">{{ substr($request->owner->name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                              <div class="text-lg font-bold text-slate-800">
                                                    {{ $request->owner->name }}</div>
                                                <div class="text-slate-600 text-sm">{{ $request->owner->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold
                                                   {{ $request->status === 'Pending'
                                                       ? 'bg-yellow-100 text-yellow-800'
                                                       : ($request->status === 'Approved'
                                                           ? 'bg-emerald-100 text-emerald-800'
                                                           : ($request->status === 'Rejected'
                                                               ? 'bg-red-100 text-red-800'
                                                               : 'bg-blue-100 text-blue-800')) }}">
                                            {{ $request->status }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="text-slate-700 max-w-xs">
                                            {{ $request->message ?: 'No message' }}
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        @if ($request->due_date)
                                            <div class="text-slate-700">
                                                <div class="font-medium">{{ $request->due_date->format('M d, Y') }}
                                                </div>
                                                @if ($request->due_date->isPast())
                                                    <span class="text-red-600 text-sm font-bold">Overdue</span>
                                                @elseif($request->due_date->diffInDays(now()) <= 3)
                                                    <span class="text-amber-600 text-sm font-bold">Due Soon</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-slate-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        @if ($request->status === 'Returned')
                                            <a href="{{ route('ratings.index') }}?request={{ $request->id }}"
                                                class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                                Rate
                                            </a>
                                        @elseif($request->status === 'Approved')
                                            <div class="flex space-x-2">
                                                <a href="{{ route('messages.index') }}?request={{ $request->id }}"
                                                    class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                                    Message
                                                </a>
                                                <button wire:click="openDisputeForm({{ $request->id }})"
                                                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                                    Report Issue
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($this->outgoingRequests->hasPages())
                <div class="mt-8 flex justify-center">
                    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 p-4">
                        {{ $this->outgoingRequests->links() }}
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-16">
                <div
                    class="w-32 h-32 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-8">
                    <span class="text-6xl">📤</span>
                </div>
                <p class="text-slate-600 text-xl font-medium">No outgoing requests yet.</p>
                <p class="text-slate-500 text-lg">Start browsing books and make your first request!</p>
                <a href="{{ route('books.index') }}"
                    class="inline-block mt-6 px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    Browse Books
                </a>
            </div>
        @endif
    </div>
</section>
