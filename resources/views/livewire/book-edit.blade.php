<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use App\Models\Book;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithFileUploads;

    public $id = '';
    #[Rule('required|string|max:255')]
    public $title = '';

    #[Rule('required|string|max:255')]
    public $author = '';

    #[Rule('required|string|max:255')]
    public $genre = '';

    #[Rule('required|in:Excellent,Good,Fair,Poor')]
    public $condition = 'Good';

    #[Rule('nullable|string|max:1000')]
    public $description = '';
    #[Rule('nullable|string|max:255')]
    public $location = '';
    #[Rule('nullable|image|max:2048')]
    public $image;
    public $currentImage = null;

    public function mount()
    {
        $this->id = request()->route('book');
        $book = Book::findOrFail($this->id);

        // Check if user owns this book
        if ($book->user_id !== auth()->id()) {
            abort(403);
        }

        $this->title = $book->title;
        $this->author = $book->author;
        $this->genre = $book->genre;
        $this->condition = $book->condition;
        $this->description = $book->description;
        $this->location = $book->location;
        $this->currentImage = $book->image;
    }

    public function getGenresProperty()
    {
        return ['Fiction', 'Non-Fiction', 'Science Fiction', 'Fantasy', 'Mystery', 'Romance', 'Thriller', 'Biography', 'History', 'Self-Help', 'Business', 'Technology', 'Other'];
    }

    public function getConditionsProperty()
    {
        return ['Excellent', 'Good', 'Fair', 'Poor'];
    }

    public function save()
    {
        $this->validate();

        $book = Book::findOrFail($this->id);

        $data = [
            'title' => $this->title,
            'author' => $this->author,
            'genre' => $this->genre,
            'condition' => $this->condition,
            'description' => $this->description,
            'location' => $this->location,
        ];

        if ($this->image) {
            // Delete old image if exists
            if ($book->image && Storage::disk('public')->exists($book->image)) {
                Storage::disk('public')->delete($book->image);
            }
            $data['image'] = $this->image->store('books', 'public');
        }

        $book->update($data);

        session()->flash('success', 'Book updated successfully!');
        return redirect()->route('books.my-books');
    }
};
?>

<section>
    <div class="max-w-4xl mx-auto">
        <!-- Header Section -->
        <div class="text-center mb-16">
            <div
                class="w-24 h-24 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl">
                <span class="text-white text-4xl">✏️</span>
            </div>
            <h1 class="text-5xl md:text-6xl font-bold text-slate-800 mb-6">
                Edit Book
            </h1>
            <p class="text-xl text-slate-600 max-w-3xl mx-auto font-light">
                Update your book information and keep it current for the community
            </p>
        </div>

        <!-- Book Edit Form -->
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-12">
            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="md:col-span-2">
                        <label for="title" class="block text-sm font-bold text-slate-700 mb-3">Book Title *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 5.477 5.754 5 7.5 5s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.523 18.246 19 16.5 19c-1.746 0-3.332-.477-4.5-1.253">
                                    </path>
                                </svg>
                            </div>
                            <input wire:model="title" type="text" id="title" name="title" required
                                class="w-full pl-12 pr-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm"
                                placeholder="Enter book title">
                        </div>
                        @error('title')
                            <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="author" class="block text-sm font-bold text-slate-700 mb-3">Author *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <input wire:model="author" type="text" id="author" name="author" required
                                class="w-full pl-12 pr-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm"
                                placeholder="Enter author name">
                        </div>
                        @error('author')
                            <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="genre" class="block text-sm font-bold text-slate-700 mb-3">Genre *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                    </path>
                                </svg>
                            </div>
                            <select wire:model="genre" id="genre" name="genre" required
                                class="w-full pl-12 pr-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm">
                                <option value="">Select Genre</option>
                                @foreach ($this->genres as $genre)
                                    <option value="{{ $genre }}">{{ $genre }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('genre')
                            <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="condition" class="block text-sm font-bold text-slate-700 mb-3">Condition *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <select wire:model="condition" id="condition" name="condition" required
                                class="w-full pl-12 pr-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm">
                                @foreach ($this->conditions as $condition)
                                    <option value="{{ $condition }}">{{ $condition }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('condition')
                            <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-bold text-slate-700 mb-3">Location</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <input wire:model="location" type="text" id="location" name="location"
                                class="w-full pl-12 pr-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm"
                                placeholder="Enter location (optional)">
                        </div>
                        @error('location')
                            <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="image" class="block text-sm font-bold text-slate-700 mb-3">Book Cover Image
                            (Optional)</label>
                        @if ($this->currentImage)
                            <div class="mb-4">
                                <p class="text-sm text-slate-600 mb-2">Current Image:</p>
                                <img src="{{ Storage::url($this->currentImage) }}" alt="Current cover"
                                    class="max-w-xs rounded-2xl shadow-lg">
                            </div>
                        @endif
                        <div class="relative">
                            <input wire:model="image" type="file" id="image" name="image" accept="image/*"
                                class="w-full px-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm">
                            @if ($this->image)
                                <div class="mt-4">
                                    <p class="text-sm text-slate-600 mb-2">New Image Preview:</p>
                                    <img src="{{ $this->image->temporaryUrl() }}" alt="Preview"
                                        class="max-w-xs rounded-2xl shadow-lg">
                                </div>
                            @endif
                        </div>
                        @error('image')
                            <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span>
                        @enderror
                        <p class="text-sm text-slate-500 mt-2">Max file size: 2MB. Supported formats: JPG, PNG, GIF</p>
                    </div>

                    <div class="md:col-span-2">
                        <label for="description"
                            class="block text-sm font-bold text-slate-700 mb-3">Description</label>
                        <div class="relative">
                            <div class="absolute top-4 left-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                    </path>
                                </svg>
                            </div>
                            <textarea wire:model="description" id="description" name="description" rows="4"
                                class="w-full pl-12 pr-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm resize-none"
                                placeholder="Describe the book (optional)"></textarea>
                        </div>
                        @error('description')
                            <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 mt-12 pt-8 border-t border-slate-200">
                    <a href="{{ route('books.my-books') }}"
                        class="px-8 py-4 border border-slate-300 rounded-2xl text-slate-700 font-bold hover:bg-slate-50 hover:border-indigo-400 hover:text-indigo-600 transition-all duration-300 shadow-sm hover:shadow-md">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        Update Book
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
