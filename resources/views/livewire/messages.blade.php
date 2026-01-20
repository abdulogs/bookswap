<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Message;
use App\Models\BookRequest;

new class extends Component {
    #[Rule('required|string|max:1000')]
    public $messageText = '';

    public $requestId;
    public $bookRequest;

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
        
        // Ensure user is part of this request
        if (auth()->id() !== $this->bookRequest->borrower_id && auth()->id() !== $this->bookRequest->owner_id) {
            abort(403);
        }
    }

    public function getMessagesProperty()
    {
        if (!$this->bookRequest) {
            return collect();
        }

        return Message::where('book_request_id', $this->bookRequest->id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getOtherUserProperty()
    {
        if (!$this->bookRequest) {
            return null;
        }

        return auth()->id() === $this->bookRequest->borrower_id 
            ? $this->bookRequest->owner 
            : $this->bookRequest->borrower;
    }

    public function sendMessage()
    {
        if (!$this->bookRequest) {
            return;
        }

        $this->validate();

        $receiverId = $this->otherUser->id;

        Message::create([
            'book_request_id' => $this->bookRequest->id,
            'sender_id' => auth()->id(),
            'receiver_id' => $receiverId,
            'message' => $this->messageText,
        ]);

        $this->messageText = '';
        $this->dispatch('message-sent');
    }

    public function markAsRead($messageId)
    {
        $message = Message::findOrFail($messageId);
        
        if ($message->receiver_id === auth()->id() && !$message->read) {
            $message->update(['read' => true]);
        }
    }
};

?>

<section>
    <div class="max-w-6xl mx-auto">
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
                    <h1 class="text-3xl font-bold text-slate-800 mb-2">Messages</h1>
                    <p class="text-slate-600">Book: <span class="font-bold">{{ $this->bookRequest->book->title }}</span></p>
                    <p class="text-slate-600">Conversation with: <span class="font-bold">{{ $this->otherUser->name }}</span></p>
                </div>
            </div>

            <!-- Messages -->
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-8 mb-8" style="max-height: 600px; overflow-y: auto;">
                <div class="space-y-6">
                    @forelse($this->messages as $message)
                        <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-2xl {{ $message->sender_id === auth()->id() ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-800' }} rounded-2xl p-4 shadow-lg">
                                <div class="flex items-center space-x-3 mb-2">
                                    <div class="w-8 h-8 {{ $message->sender_id === auth()->id() ? 'bg-indigo-600' : 'bg-slate-200' }} rounded-full flex items-center justify-center">
                                        <span class="text-sm font-bold">{{ substr($message->sender->name, 0, 1) }}</span>
                                    </div>
                                    <span class="font-bold text-sm">{{ $message->sender->name }}</span>
                                    <span class="text-xs opacity-75">{{ $message->created_at->format('M d, h:i A') }}</span>
                                </div>
                                <p class="leading-relaxed">{{ $message->message }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <p class="text-slate-500 text-lg">No messages yet. Start the conversation!</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Message Form -->
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-6">
                <form wire:submit="sendMessage">
                    <div class="flex space-x-4">
                        <div class="flex-1">
                            <textarea wire:model="messageText" rows="3"
                                class="w-full px-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm resize-none"
                                placeholder="Type your message..."></textarea>
                            @error('messageText')
                                <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                Send
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @else
            <div class="text-center py-20">
                <div class="w-32 h-32 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-8">
                    <span class="text-6xl">💬</span>
                </div>
                <p class="text-slate-600 text-xl mb-4 font-medium">No conversation selected</p>
                <p class="text-slate-500 text-lg">Select a book request to start messaging.</p>
                <a href="{{ route('requests.index') }}"
                    class="inline-block mt-6 px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    View Requests
                </a>
            </div>
        @endif
    </div>
</section>

