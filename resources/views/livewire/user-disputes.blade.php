<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use App\Models\Dispute;

new class extends Component {
    public $disputeId;
    public $dispute;

    public function mount($dispute = null)
    {
        if ($dispute) {
            $this->disputeId = is_numeric($dispute) ? $dispute : $dispute->id;
            $this->loadDispute();
        }
    }

    public function loadDispute()
    {
        $this->dispute = Dispute::with(['bookRequest.book', 'bookRequest.borrower', 'bookRequest.owner', 'reporter', 'resolver'])
            ->where(function ($query) {
                $query->where('reporter_id', auth()->id())
                    ->orWhereHas('bookRequest', function ($q) {
                        $q->where('borrower_id', auth()->id())
                          ->orWhere('owner_id', auth()->id());
                    });
            })
            ->findOrFail($this->disputeId);
    }

    public function getDisputesProperty()
    {
        return Dispute::with(['bookRequest.book', 'reporter'])
            ->where(function ($query) {
                $query->where('reporter_id', auth()->id())
                    ->orWhereHas('bookRequest', function ($q) {
                        $q->where('borrower_id', auth()->id())
                          ->orWhere('owner_id', auth()->id());
                    });
            })
            ->latest()
            ->paginate(15);
    }
};

?>

<section>
    <div class="max-w-7xl mx-auto">
        @if($this->dispute)
            <!-- Dispute Detail -->
            <div class="mb-8">
                <a href="{{ route('disputes.index') }}"
                    class="inline-flex items-center text-indigo-600 hover:text-indigo-700 font-medium transition-colors group mb-4">
                    <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to My Disputes
                </a>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-8 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-3xl font-bold text-slate-800">{{ $this->dispute->title }}</h1>
                    @if($this->dispute->reporter_id === auth()->id())
                        <span class="px-4 py-2 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                            You are the Reporter
                        </span>
                    @else
                        <span class="px-4 py-2 rounded-full text-sm font-bold bg-purple-100 text-purple-800">
                            You are Involved
                        </span>
                    @endif
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-sm font-bold text-slate-500 mb-2">Book</p>
                        <p class="text-lg text-slate-800">{{ $this->dispute->bookRequest->book->title }}</p>
                        <a href="{{ route('books.show', $this->dispute->bookRequest->book) }}" 
                           class="text-indigo-600 hover:text-indigo-700 text-sm font-medium mt-1 inline-block">
                            View Book →
                        </a>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-500 mb-2">Book Request</p>
                        <a href="{{ route('requests.index') }}" 
                           class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                            View Request Details →
                        </a>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-500 mb-2">Reporter</p>
                        <p class="text-lg text-slate-800">{{ $this->dispute->reporter->name }}</p>
                        <p class="text-sm text-slate-600">{{ $this->dispute->reporter->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-500 mb-2">Other Party</p>
                        @php
                            $otherParty = $this->dispute->bookRequest->borrower_id === $this->dispute->reporter_id 
                                ? $this->dispute->bookRequest->owner 
                                : $this->dispute->bookRequest->borrower;
                        @endphp
                        <p class="text-lg text-slate-800">{{ $otherParty->name }}</p>
                        <p class="text-sm text-slate-600">{{ $otherParty->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-500 mb-2">Status</p>
                        <span class="px-3 py-1 rounded-full text-sm font-bold {{ $this->dispute->status === 'open' ? 'bg-red-100 text-red-800' : ($this->dispute->status === 'resolved' ? 'bg-emerald-100 text-emerald-800' : ($this->dispute->status === 'in_review' ? 'bg-yellow-100 text-yellow-800' : 'bg-slate-100 text-slate-800')) }}">
                            {{ ucfirst(str_replace('_', ' ', $this->dispute->status)) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-500 mb-2">Created</p>
                        <p class="text-lg text-slate-800">{{ $this->dispute->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    @if($this->dispute->resolved_at)
                        <div>
                            <p class="text-sm font-bold text-slate-500 mb-2">Resolved At</p>
                            <p class="text-lg text-slate-800">{{ $this->dispute->resolved_at->format('M d, Y h:i A') }}</p>
                        </div>
                    @endif
                    @if($this->dispute->resolver)
                        <div>
                            <p class="text-sm font-bold text-slate-500 mb-2">Resolved By</p>
                            <p class="text-lg text-slate-800">{{ $this->dispute->resolver->name }}</p>
                        </div>
                    @endif
                </div>

                <div class="mb-6">
                    <p class="text-sm font-bold text-slate-500 mb-2">Dispute Description</p>
                    <p class="text-slate-700 bg-slate-50 rounded-2xl p-4">{{ $this->dispute->description }}</p>
                </div>

                @if($this->dispute->admin_notes)
                    <div class="mb-6">
                        <p class="text-sm font-bold text-slate-500 mb-2">Admin Response</p>
                        <p class="text-slate-700 bg-indigo-50 rounded-2xl p-4 border border-indigo-100">{{ $this->dispute->admin_notes }}</p>
                    </div>
                @else
                    <div class="mb-6">
                        <p class="text-sm font-bold text-slate-500 mb-2">Admin Response</p>
                        <p class="text-slate-500 bg-slate-50 rounded-2xl p-4 italic">No response from admin yet. Your dispute is being reviewed.</p>
                    </div>
                @endif
            </div>
        @else
            <!-- Disputes List -->
            <div class="text-center mb-16">
                <div class="w-24 h-24 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl">
                    <span class="text-white text-4xl">⚖️</span>
                </div>
                <h1 class="text-5xl md:text-6xl font-bold text-slate-800 mb-6">
                    My Disputes
                </h1>
                <p class="text-xl text-slate-600 max-w-3xl mx-auto font-light">
                    Track and manage disputes you're involved in
                </p>
            </div>

            @if (session('success'))
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

            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-gradient-to-r from-slate-50 to-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Title</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Book</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Your Role</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Created</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white/50 divide-y divide-slate-200">
                            @forelse($this->disputes as $dispute)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-slate-800">{{ $dispute->title }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">{{ $dispute->bookRequest->book->title }}</td>
                                    <td class="px-6 py-4">
                                        @if($dispute->reporter_id === auth()->id())
                                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                                Reporter
                                            </span>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                                                Involved Party
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $dispute->status === 'open' ? 'bg-red-100 text-red-800' : ($dispute->status === 'resolved' ? 'bg-emerald-100 text-emerald-800' : ($dispute->status === 'in_review' ? 'bg-yellow-100 text-yellow-800' : 'bg-slate-100 text-slate-800')) }}">
                                            {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">{{ $dispute->created_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('disputes.show', $dispute) }}"
                                            class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl text-sm font-bold transition-all">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                        <div class="flex flex-col items-center">
                                            <div class="w-24 h-24 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mb-4">
                                                <span class="text-4xl">📋</span>
                                            </div>
                                            <p class="text-lg font-medium mb-2">No disputes found</p>
                                            <p class="text-sm text-slate-400">You don't have any disputes yet.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="py-8">
                @if ($this->disputes->hasPages())
                    {{ $this->disputes->links() }}
                @endif
            </div>
        @endif
    </div>
</section>
