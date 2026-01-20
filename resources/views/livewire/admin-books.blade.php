<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use App\Models\Book;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    public $search = '';
    public $status = '';

    public function mount()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
    }

    public function getBooksProperty()
    {
        $query = Book::with('owner');

        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->latest()->paginate(15);
    }

    public function deleteBook($bookId)
    {
        $book = Book::findOrFail($bookId);
        
        // Delete image if exists
        if ($book->image && Storage::disk('public')->exists($book->image)) {
            Storage::disk('public')->delete($book->image);
        }
        
        $book->delete();
        session()->flash('success', 'Book deleted successfully!');
    }
};

?>

<section>
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-16">
            <h1 class="text-5xl md:text-6xl font-bold text-slate-800 mb-6">
                Book Management
            </h1>
        </div>

        <!-- Filters -->
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input wire:model.live.debounce.300ms="search" type="text"
                    class="px-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm"
                    placeholder="Search books...">
                <select wire:model.live="status"
                    class="px-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm">
                    <option value="">All Status</option>
                    <option value="Available">Available</option>
                    <option value="Lent Out">Lent Out</option>
                </select>
            </div>
        </div>

        <!-- Books Table -->
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-gradient-to-r from-slate-50 to-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Book</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Owner</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Genre</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/50 divide-y divide-slate-200">
                        @forelse($this->books as $book)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-4">
                                        @if($book->image)
                                            <img src="{{ Storage::url($book->image) }}" alt="{{ $book->title }}" class="w-16 h-20 object-cover rounded-lg">
                                        @else
                                            <div class="w-16 h-20 bg-slate-200 rounded-lg flex items-center justify-center">
                                                <span class="text-2xl">📚</span>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-bold text-slate-800">{{ $book->title }}</p>
                                            <p class="text-sm text-slate-600">by {{ $book->author }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $book->owner->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $book->status === 'Available' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $book->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $book->genre }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button wire:click="deleteBook({{ $book->id }})"
                                        onclick="return confirm('Are you sure you want to delete this book?')"
                                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-bold transition-all">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">No books found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="py-8">
            @if ($this->books->hasPages())
                {{ $this->books->links() }}
            @endif
        </div>
    </div>
</section>

