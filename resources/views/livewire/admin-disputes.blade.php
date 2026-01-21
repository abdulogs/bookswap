<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Dispute;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    #[Rule('nullable|string|max:1000')]
    public $adminNotes = '';

    public $disputeId;
    public $dispute;

    public function mount($dispute = null)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($dispute) {
            $this->disputeId = is_numeric($dispute) ? $dispute : $dispute->id;
            $this->loadDispute();
        }
    }

    public function loadDispute()
    {
        $this->dispute = Dispute::with(['bookRequest.book', 'reporter', 'resolver'])->findOrFail($this->disputeId);
    }

    public function getDisputesProperty()
    {
        return Dispute::with(['bookRequest.book', 'reporter'])
            ->latest()
            ->paginate(15);
    }

    public function updateStatus($status)
    {
        if (!$this->dispute) {
            return;
        }

        $data = ['status' => $status];

        if ($status === 'resolved' || $status === 'closed') {
            $data['resolved_by'] = auth()->id();
            $data['resolved_at'] = now();
        }

        if ($this->adminNotes) {
            $data['admin_notes'] = $this->adminNotes;
        }

        $this->dispute->update($data);
        $this->adminNotes = '';
        session()->flash('success', 'Dispute status updated successfully!');
    }
};

?>

<section>
    <div class="max-w-7xl mx-auto">
        @if ($this->dispute)
            <!-- Dispute Detail -->
            <div class="mb-8">
                <a href="{{ route('admin.disputes.index') }}"
                    class="inline-flex items-center text-indigo-600 hover:text-indigo-700 font-medium transition-colors group mb-4">
                    <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Disputes
                </a>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-8 mb-8">
                <div class="flex items-start space-x-6 mb-6">
                    <div
                        class="w-24 h-32 bg-slate-100 rounded-2xl overflow-hidden flex items-center justify-center flex-shrink-0">
                        @if ($this->dispute->bookRequest->book->image)
                            <img src="{{ Storage::url($this->dispute->bookRequest->book->image) }}"
                                alt="{{ $this->dispute->bookRequest->book->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <span class="text-3xl mb-1">📚</span>
                                <span class="text-[10px] font-medium text-center px-1">No cover image</span>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-slate-800 mb-4">{{ $this->dispute->title }}</h1>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm font-bold text-slate-500 mb-2">Book</p>
                                <p class="text-lg text-slate-800">{{ $this->dispute->bookRequest->book->title }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 mb-2">Reporter</p>
                                <p class="text-lg text-slate-800">{{ $this->dispute->reporter->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 mb-2">Status</p>
                                <span
                                    class="px-3 py-1 rounded-full text-sm font-bold {{ $this->dispute->status === 'open' ? 'bg-red-100 text-red-800' : ($this->dispute->status === 'resolved' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800') }}">
                                    {{ ucfirst(str_replace('_', ' ', $this->dispute->status)) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 mb-2">Created</p>
                                <p class="text-lg text-slate-800">
                                    {{ $this->dispute->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <p class="text-sm font-bold text-slate-500 mb-2">Description</p>
                    <p class="text-slate-700 bg-slate-50 rounded-2xl p-4">{{ $this->dispute->description }}</p>
                </div>

                @if ($this->dispute->admin_notes)
                    <div class="mb-6">
                        <p class="text-sm font-bold text-slate-500 mb-2">Admin Notes</p>
                        <p class="text-slate-700 bg-indigo-50 rounded-2xl p-4">{{ $this->dispute->admin_notes }}</p>
                    </div>
                @endif

                <div class="border-t border-slate-200 pt-6">
                    <label class="block text-sm font-bold text-slate-700 mb-3">Add Admin Notes</label>
                    <textarea wire:model="adminNotes" rows="4"
                        class="w-full px-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm resize-none mb-4"
                        placeholder="Add notes about this dispute..."></textarea>

                    <div class="flex space-x-4">
                        <button wire:click="updateStatus('in_review')"
                            class="px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl font-bold transition-all">
                            Mark as In Review
                        </button>
                        <button wire:click="updateStatus('resolved')"
                            class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold transition-all">
                            Mark as Resolved
                        </button>
                        <button wire:click="updateStatus('closed')"
                            class="px-6 py-3 bg-slate-500 hover:bg-slate-600 text-white rounded-xl font-bold transition-all">
                            Close Dispute
                        </button>
                    </div>
                </div>
            </div>
        @else
            <!-- Disputes List -->
            <div class="text-center mb-16">
                <h1 class="text-5xl md:text-6xl font-bold text-slate-800 mb-6">
                    Dispute Management
                </h1>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-gradient-to-r from-slate-50 to-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Book</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Title</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Reporter</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Created</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white/50 divide-y divide-slate-200">
                            @forelse($this->disputes as $dispute)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="w-12 h-16 bg-slate-100 rounded-xl overflow-hidden flex items-center justify-center">
                                                @if ($dispute->bookRequest->book->image)
                                                    <img src="{{ Storage::url($dispute->bookRequest->book->image) }}"
                                                        alt="{{ $dispute->bookRequest->book->title }}"
                                                        class="w-full h-full object-cover">
                                                @else
                                                    <span class="text-xl text-slate-400">📚</span>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-800 line-clamp-1">
                                                    {{ $dispute->bookRequest->book->title }}</p>
                                                <p class="text-xs text-slate-500 line-clamp-1">
                                                    {{ $dispute->bookRequest->book->author ?? '' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-slate-800">{{ $dispute->title }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">{{ $dispute->reporter->name }}</td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-bold {{ $dispute->status === 'open' ? 'bg-red-100 text-red-800' : ($dispute->status === 'resolved' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800') }}">
                                            {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">{{ $dispute->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('admin.disputes.show', $dispute) }}"
                                            class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl text-sm font-bold transition-all">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">No disputes found
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
