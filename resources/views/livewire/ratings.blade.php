<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Rating;
use App\Models\BookRequest;

new class extends Component {
    #[Rule('required|integer|min:1|max:5')]
    public $rating = 5;

    #[Rule('nullable|string|max:1000')]
    public $review = '';

    public $requestId;
    public $bookRequest;
    public $type = 'lender'; // lender or borrower

    public function mount($request = null)
    {
        if ($request) {
            $this->requestId = is_numeric($request) ? $request : $request->id;
        } else {
            $this->requestId = request()->query('request');
        }
        
        if ($this->requestId) {
            $this->loadRequest();
        }
    }

    public function loadRequest()
    {
        $this->bookRequest = BookRequest::with(['book', 'borrower', 'owner'])
            ->findOrFail($this->requestId);
        
        // Ensure user is part of this request and request is returned
        if (auth()->id() !== $this->bookRequest->borrower_id && auth()->id() !== $this->bookRequest->owner_id) {
            abort(403);
        }

        if ($this->bookRequest->status !== 'Returned') {
            session()->flash('error', 'You can only rate after the book is returned.');
            return redirect()->route('requests.index');
        }

        // Determine type based on user role
        $this->type = auth()->id() === $this->bookRequest->borrower_id ? 'lender' : 'borrower';
    }

    public function getRatedUserProperty()
    {
        if (!$this->bookRequest) {
            return null;
        }

        return $this->type === 'lender' 
            ? $this->bookRequest->owner 
            : $this->bookRequest->borrower;
    }

    public function getExistingRatingProperty()
    {
        if (!$this->bookRequest) {
            return null;
        }

        return Rating::where('book_request_id', $this->bookRequest->id)
            ->where('rater_id', auth()->id())
            ->where('type', $this->type)
            ->first();
    }

    public function submitRating()
    {
        $this->validate();

        if (!$this->bookRequest) {
            return;
        }

        // Check if rating already exists
        $existingRating = $this->existingRating;
        
        if ($existingRating) {
            $existingRating->update([
                'rating' => $this->rating,
                'review' => $this->review,
            ]);
            session()->flash('success', 'Rating updated successfully!');
        } else {
            Rating::create([
                'book_request_id' => $this->bookRequest->id,
                'rater_id' => auth()->id(),
                'rated_user_id' => $this->ratedUser->id,
                'rating' => $this->rating,
                'review' => $this->review,
                'type' => $this->type,
            ]);
            session()->flash('success', 'Rating submitted successfully!');
        }

        return redirect()->route('requests.index');
    }
};

?>

<section>
    <div class="max-w-4xl mx-auto">
        @if($this->bookRequest)
            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('requests.index') }}"
                    class="inline-flex items-center text-indigo-600 hover:text-indigo-700 font-medium transition-colors group mb-4">
                    <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Requests
                </a>
                
                <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-6">
                    <h1 class="text-3xl font-bold text-slate-800 mb-2">Rate {{ $this->type === 'lender' ? 'Lender' : 'Borrower' }}</h1>
                    <p class="text-slate-600">Book: <span class="font-bold">{{ $this->bookRequest->book->title }}</span></p>
                    <p class="text-slate-600">Rate: <span class="font-bold">{{ $this->ratedUser->name }}</span></p>
                </div>
            </div>

            @if($this->existingRating)
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 mb-6">
                    <p class="text-blue-800 font-medium">You have already rated this {{ $this->type }}. You can update your rating below.</p>
                </div>
            @endif

            <!-- Rating Form -->
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-8">
                <form wire:submit="submitRating">
                    <div class="mb-8">
                        <label class="block text-sm font-bold text-slate-700 mb-4">Rating *</label>
                        <div class="flex space-x-2">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" wire:click="$set('rating', {{ $i }})"
                                    class="w-16 h-16 rounded-2xl transition-all duration-300 transform hover:scale-110 {{ $this->rating >= $i ? 'bg-yellow-400 text-white' : 'bg-slate-200 text-slate-400' }} flex items-center justify-center text-2xl font-bold shadow-lg">
                                    ⭐
                                </button>
                            @endfor
                        </div>
                        @error('rating')
                            <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-8">
                        <label for="review" class="block text-sm font-bold text-slate-700 mb-3">Review (Optional)</label>
                        <textarea wire:model="review" id="review" rows="6"
                            class="w-full px-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm resize-none"
                            placeholder="Share your experience..."></textarea>
                        @error('review')
                            <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end space-x-4">
                        <a href="{{ route('requests.index') }}"
                            class="px-8 py-4 border border-slate-300 rounded-2xl text-slate-700 font-bold hover:bg-slate-50 hover:border-indigo-400 hover:text-indigo-600 transition-all duration-300 shadow-sm hover:shadow-md">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            {{ $this->existingRating ? 'Update Rating' : 'Submit Rating' }}
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="text-center py-20">
                <div class="w-32 h-32 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-8">
                    <span class="text-6xl">⭐</span>
                </div>
                <p class="text-slate-600 text-xl mb-4 font-medium">No request selected</p>
                <p class="text-slate-500 text-lg">Select a returned book request to rate.</p>
                <a href="{{ route('requests.index') }}"
                    class="inline-block mt-6 px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    View Requests
                </a>
            </div>
        @endif
    </div>
</section>

