<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Book;
use App\Models\BookRequest;
use App\Models\Dispute;
use App\Models\Notification;

new class extends Component {
    public function mount()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
    }

    public function getStatsProperty()
    {
        return [
            'total_users' => User::count(),
            'total_books' => Book::count(),
            'total_requests' => BookRequest::count(),
            'pending_disputes' => Dispute::where('status', 'open')->count(),
            'active_borrowings' => BookRequest::where('status', 'Approved')->count(),
            'available_books' => Book::where('status', 'Available')->count(),
        ];
    }

    public function getRecentRequestsProperty()
    {
        return BookRequest::with(['book', 'borrower', 'owner'])
            ->latest()
            ->take(10)
            ->get();
    }

    public function getRecentDisputesProperty()
    {
        return Dispute::with(['bookRequest.book', 'reporter'])
            ->where('status', 'open')
            ->latest()
            ->take(5)
            ->get();
    }
};

?>

<section>
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-16">
            <div class="w-24 h-24 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl">
                <span class="text-white text-4xl">👑</span>
            </div>
            <h1 class="text-5xl md:text-6xl font-bold text-slate-800 mb-6">
                Admin Dashboard
            </h1>
            <p class="text-xl text-slate-600 max-w-3xl mx-auto font-light">
                Manage your BookSwap platform
            </p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/20 p-8">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-sm font-bold mb-2">Total Users</p>
                        <p class="text-4xl font-bold text-slate-800">{{ $this->stats['total_users'] }}</p>
                    </div>
                    <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center">
                        <span class="text-3xl">👥</span>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/20 p-8">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-sm font-bold mb-2">Total Books</p>
                        <p class="text-4xl font-bold text-slate-800">{{ $this->stats['total_books'] }}</p>
                    </div>
                    <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center">
                        <span class="text-3xl">📚</span>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/20 p-8">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-sm font-bold mb-2">Total Requests</p>
                        <p class="text-4xl font-bold text-slate-800">{{ $this->stats['total_requests'] }}</p>
                    </div>
                    <div class="w-16 h-16 bg-pink-100 rounded-2xl flex items-center justify-center">
                        <span class="text-3xl">🤝</span>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/20 p-8">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-sm font-bold mb-2">Pending Disputes</p>
                        <p class="text-4xl font-bold text-red-600">{{ $this->stats['pending_disputes'] }}</p>
                    </div>
                    <div class="w-16 h-16 bg-red-100 rounded-2xl flex items-center justify-center">
                        <span class="text-3xl">⚠️</span>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/20 p-8">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-sm font-bold mb-2">Active Borrowings</p>
                        <p class="text-4xl font-bold text-slate-800">{{ $this->stats['active_borrowings'] }}</p>
                    </div>
                    <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center">
                        <span class="text-3xl">📖</span>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/20 p-8">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-sm font-bold mb-2">Available Books</p>
                        <p class="text-4xl font-bold text-emerald-600">{{ $this->stats['available_books'] }}</p>
                    </div>
                    <div class="w-16 h-16 bg-emerald-100 rounded-2xl flex items-center justify-center">
                        <span class="text-3xl">✅</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Requests -->
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/20 p-8">
                <h2 class="text-2xl font-bold text-slate-800 mb-6">Recent Requests</h2>
                <div class="space-y-4">
                    @forelse($this->recentRequests as $request)
                        <div class="border border-slate-200 rounded-2xl p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-bold text-slate-800">{{ $request->book->title }}</p>
                                    <p class="text-sm text-slate-600">Borrower: {{ $request->borrower->name }}</p>
                                    <p class="text-sm text-slate-600">Owner: {{ $request->owner->name }}</p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $request->status === 'Pending' ? 'bg-yellow-100 text-yellow-800' : ($request->status === 'Approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $request->status }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-500 text-center py-8">No recent requests</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Disputes -->
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/20 p-8">
                <h2 class="text-2xl font-bold text-slate-800 mb-6">Pending Disputes</h2>
                <div class="space-y-4">
                    @forelse($this->recentDisputes as $dispute)
                        <div class="border border-red-200 rounded-2xl p-4 bg-red-50">
                            <div class="flex items-center justify-between mb-2">
                                <p class="font-bold text-slate-800">{{ $dispute->title }}</p>
                                <a href="{{ route('admin.disputes.show', $dispute) }}"
                                    class="text-indigo-600 hover:text-indigo-700 text-sm font-bold">
                                    View →
                                </a>
                            </div>
                            <p class="text-sm text-slate-600 mb-2">Book: {{ $dispute->bookRequest->book->title }}</p>
                            <p class="text-sm text-slate-600">Reporter: {{ $dispute->reporter->name }}</p>
                        </div>
                    @empty
                        <p class="text-slate-500 text-center py-8">No pending disputes</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>

