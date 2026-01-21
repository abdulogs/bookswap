<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use App\Models\Book;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    public $search = '';
    public $genre = '';
    public $condition = '';
    public $location = '';

    public function getBooksProperty()
    {
        $query = Book::query();

        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->genre) {
            $query->where('genre', $this->genre);
        }

        if ($this->condition) {
            $query->where('condition', $this->condition);
        }

        if ($this->location) {
            $query->byLocation($this->location);
        }

        return $query->with('owner')->latest()->paginate(5);
    }

    public function getGenresProperty()
    {
        return Book::distinct()->pluck('genre')->filter()->sort()->values();
    }

    public function getConditionsProperty()
    {
        return ['Excellent', 'Good', 'Fair', 'Poor'];
    }

    public function clearFilters()
    {
        $this->reset(['search', 'genre', 'condition', 'location']);
    }
};

?>

<section>
    <!-- Header Section -->
    <div class="text-center mb-16">
        <div
            class="w-24 h-24 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl">
            <span class="text-white text-4xl">📚</span>
        </div>
        <h1 class="text-5xl md:text-6xl font-bold text-slate-800 mb-6">
            Browse Books
        </h1>
        <p class="text-xl text-slate-600 max-w-3xl mx-auto font-light">
            Discover books shared by our community members and find your next great read
        </p>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-8 mb-12">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <div>
                <label for="search" class="block text-sm font-bold text-slate-700 mb-3">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" id="search"
                        class="w-full pl-12 pr-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm"
                        placeholder="Search by title or author...">
                </div>
            </div>

            <div>
                <label for="genre" class="block text-sm font-bold text-slate-700 mb-3">Genre</label>
                <select wire:model.live="genre" id="genre"
                    class="w-full px-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm">
                    <option value="">All Genres</option>
                    @foreach ($this->genres as $genre)
                        <option value="{{ $genre }}">{{ $genre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="condition" class="block text-sm font-bold text-slate-700 mb-3">Condition</label>
                <select wire:model.live="condition" id="condition"
                    class="w-full px-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm">
                    <option value="">All Conditions</option>
                    @foreach ($this->conditions as $condition)
                        <option value="{{ $condition }}">{{ $condition }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="location" class="block text-sm font-bold text-slate-700 mb-3">Location</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="location" type="text" id="location"
                        class="w-full pl-12 pr-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm"
                        placeholder="Search by location...">
                </div>
            </div>

            <div class="flex items-end">
                <button wire:click="clearFilters"
                    class="w-full px-6 py-4 bg-gradient-to-r from-slate-500 to-slate-600 hover:from-slate-600 hover:to-slate-700 text-white rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    Clear Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Books Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        @forelse($this->books as $book)
            <div
                class="group bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl hover:shadow-2xl border border-white/20 overflow-hidden transform hover:-translate-y-2 transition-all duration-500">
                <div class="h-48 bg-slate-100 overflow-hidden flex items-center justify-center">
                    @if ($book->image)
                        <img src="{{ placeholder($book->image) }}" alt="{{ $book->title }}"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    @else
                        <div class="flex flex-col items-center justify-center text-slate-400">
                            <span class="text-3xl mb-1">📚</span>
                            <span class="text-xs font-medium">No cover image</span>
                        </div>
                    @endif
                </div>
                <div class="p-8">
                    <div class="flex items-center justify-between mb-6">
                        <span
                            class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold
                                   {{ $book->status === 'Available' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $book->status }}
                        </span>
                        <span
                            class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
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

                    <h3
                        class="text-xl font-bold text-slate-800 mb-3 group-hover:text-indigo-600 transition-colors duration-300 line-clamp-2">
                        {{ $book->title }}</h3>
                    <p class="text-slate-600 mb-4 font-medium text-lg">by {{ $book->author }}</p>

                    <div class="mb-6">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-100 text-slate-700">
                            {{ $book->condition }} condition
                        </span>
                    </div>

                    @if ($book->description)
                        <p class="text-slate-500 mb-6 text-base leading-relaxed line-clamp-3">{{ $book->description }}
                        </p>
                    @endif

                    <div class="flex items-center justify-between pt-6 border-t border-slate-200">
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center shadow-md">
                                <span
                                    class="text-white text-sm font-bold">{{ substr($book->owner->name, 0, 1) }}</span>
                            </div>
                            <span class="text-sm text-slate-600 font-medium">{{ $book->owner->name }}</span>
                        </div>
                        <a href="{{ route('books.show', $book) }}"
                            class="text-indigo-600 hover:text-indigo-700 text-sm font-bold group-hover:underline transition-colors">
                            View Details →
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-20">
                <div
                    class="w-32 h-32 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-8">
                    <span class="text-6xl">🔍</span>
                </div>
                <p class="text-slate-600 text-xl mb-4 font-medium">No books found matching your criteria.</p>
                @if ($this->search || $this->genre || $this->condition || $this->location)
                    <button wire:click="clearFilters"
                        class="mt-6 px-8 py-3 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-2xl font-bold hover:from-indigo-600 hover:to-purple-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        Clear filters
                    </button>
                @endif
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
