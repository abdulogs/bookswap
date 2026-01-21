<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use App\Models\Book;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    public $search = '';

    public function getFeaturedBooksProperty()
    {
        return Book::available()->latest()->take(6)->get();
    }
};
?>

<section>
    <!-- Hero Section -->
    <div
        class="relative overflow-hidden bg-gradient-to-br from-white via-blue-50 to-indigo-100 rounded-3xl mb-20 shadow-2xl">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60"
            xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%239C92AC"
            fill-opacity="0.05"%3E%3Ccircle cx="30" cy="30" r="2" /%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]
            opacity-50"></div>
        <div class="relative px-8 py-20 text-center">
            <div class="max-w-5xl mx-auto">
                <div class="mb-8">
                    <div
                        class="w-24 h-24 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl">
                        <span class="text-white text-5xl">📚</span>
                    </div>
                </div>
                <h1 class="text-6xl md:text-7xl font-bold text-slate-800 mb-8 leading-tight">
                    Welcome to <span
                        class="bg-gradient-to-r from-indigo-500 via-purple-600 to-pink-600 bg-clip-text text-transparent">BookSwap</span>
                </h1>
                <p class="text-xl md:text-2xl text-slate-600 mb-12 leading-relaxed max-w-4xl mx-auto font-light">
                    Share your books, discover new reads, and build a thriving community of book lovers.
                    Connect with fellow readers and expand your literary horizons.
                </p>

                @guest
                    <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
                        <a href="{{ route('register') }}"
                            class="group relative px-10 py-5 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white font-bold rounded-2xl text-lg shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 transition-all duration-300">
                            <span class="relative z-10">Get Started Today</span>
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                        </a>
                        <a href="{{ route('books.index') }}"
                            class="px-10 py-5 border-2 border-slate-300 text-slate-700 font-bold rounded-2xl text-lg hover:border-indigo-400 hover:text-indigo-600 hover:bg-white/50 transition-all duration-300 backdrop-blur-sm">
                            Browse Library
                        </a>
                    </div>
                @else
                    <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
                        <a href="{{ route('books.create') }}"
                            class="group relative px-10 py-5 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white font-bold rounded-2xl text-lg shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 transition-all duration-300">
                            <span class="relative z-10">Add Your Book</span>
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                        </a>
                        <a href="{{ route('books.index') }}"
                            class="px-10 py-5 border-2 border-slate-300 text-slate-700 font-bold rounded-2xl text-lg hover:border-indigo-400 hover:text-indigo-600 hover:bg-white/50 transition-all duration-300 backdrop-blur-sm">
                            Explore Books
                        </a>
                    </div>
                @endguest
            </div>
        </div>
    </div>

    <!-- Featured Books Section -->
    <div class="mb-24">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold text-slate-800 mb-6">
                Featured Books
            </h2>
            <p class="text-xl text-slate-600 max-w-3xl mx-auto font-light">
                Discover the latest additions to our community library, carefully selected for you
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($this->featuredBooks as $book)
                <div
                    class="group bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl hover:shadow-2xl border border-white/20 overflow-hidden transform hover:-translate-y-2 transition-all duration-500">
                    @if ($book->image)
                        <div class="h-48 overflow-hidden">
                            <img src="{{ Storage::url($book->image) }}" alt="{{ $book->title }}"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                    @endif
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

                        @if ($book->average_rating)
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="flex items-center">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= round($book->average_rating) ? 'text-yellow-400 fill-current' : 'text-slate-300' }}"
                                            viewBox="0 0 20 20">
                                            <path
                                                d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                        </svg>
                                    @endfor
                                </div>
                                <span class="text-sm font-bold text-slate-700">{{ $book->average_rating }}</span>
                                <span class="text-xs text-slate-500">({{ $book->total_ratings }})</span>
                            </div>
                        @endif

                        <h3
                            class="text-2xl font-bold text-slate-800 mb-3 group-hover:text-indigo-600 transition-colors duration-300">
                            {{ $book->title }}
                        </h3>
                        <p class="text-slate-600 mb-4 font-medium text-lg">by {{ $book->author }}</p>

                        <div class="mb-6">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-100 text-slate-700">
                                {{ $book->condition }} condition
                            </span>
                        </div>

                        @if ($book->description)
                            <p class="text-slate-500 mb-6 text-base leading-relaxed">
                                {{ Str::limit($book->description, 120) }}
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
                        <span class="text-6xl">📚</span>
                    </div>
                    <p class="text-slate-600 text-xl mb-4 font-medium">No books available yet</p>
                    <p class="text-slate-500 text-lg">Be the first to add a book to our community!</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- How It Works Section -->
    <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-16">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold text-slate-800 mb-6">
                How It Works
            </h2>
            <p class="text-xl text-slate-600 max-w-3xl mx-auto font-light">
                Join our community in three simple steps and start sharing the joy of reading
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-16">
            <div class="text-center group">
                <div class="relative mb-8">
                    <div
                        class="w-28 h-28 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-3xl flex items-center justify-center mx-auto group-hover:scale-110 transition-transform duration-500 shadow-2xl">
                        <span class="text-4xl">📚</span>
                    </div>
                    <div
                        class="absolute -top-3 -right-3 w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center text-white text-lg font-bold shadow-lg">
                        1
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-4">Add Your Books</h3>
                <p class="text-slate-600 leading-relaxed text-lg">
                    List books you're willing to share with the community. Add details about condition, genre, and your
                    thoughts.
                </p>
            </div>

            <div class="text-center group">
                <div class="relative mb-8">
                    <div
                        class="w-28 h-28 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-3xl flex items-center justify-center mx-auto group-hover:scale-110 transition-transform duration-500 shadow-2xl">
                        <span class="text-4xl">🔍</span>
                    </div>
                    <div
                        class="absolute -top-3 -right-3 w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center text-white text-lg font-bold shadow-lg">
                        2
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-4">Discover Books</h3>
                <p class="text-slate-600 leading-relaxed text-lg">
                    Browse our extensive collection, search by genre, author, or condition. Find your next great read.
                </p>
            </div>

            <div class="text-center group">
                <div class="relative mb-8">
                    <div
                        class="w-28 h-28 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-3xl flex items-center justify-center mx-auto group-hover:scale-110 transition-transform duration-500 shadow-2xl">
                        <span class="text-4xl">🤝</span>
                    </div>
                    <div
                        class="absolute -top-3 -right-3 w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center text-white text-lg font-bold shadow-lg">
                        3
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-4">Borrow & Share</h3>
                <p class="text-slate-600 leading-relaxed text-lg">
                    Request books from other members and lend yours to create a sustainable book-sharing ecosystem.
                </p>
            </div>
        </div>
    </div>
</section>
