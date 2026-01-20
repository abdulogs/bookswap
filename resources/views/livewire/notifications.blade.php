<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use App\Models\Notification;

new class extends Component {
    public function getNotificationsProperty()
    {
        return Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function getUnreadCountProperty()
    {
        return Notification::where('user_id', auth()->id())
            ->where('read', false)
            ->count();
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::findOrFail($notificationId);
        
        if ($notification->user_id === auth()->id() && !$notification->read) {
            $notification->update([
                'read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now(),
            ]);
        
        session()->flash('success', 'All notifications marked as read!');
    }

    public function deleteNotification($notificationId)
    {
        $notification = Notification::findOrFail($notificationId);
        
        if ($notification->user_id === auth()->id()) {
            $notification->delete();
            session()->flash('success', 'Notification deleted!');
        }
    }
};

?>

<section>
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-16">
            <div class="w-24 h-24 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl">
                <span class="text-white text-4xl">🔔</span>
            </div>
            <h1 class="text-5xl md:text-6xl font-bold text-slate-800 mb-6">
                Notifications
            </h1>
            <p class="text-xl text-slate-600 max-w-3xl mx-auto font-light">
                Stay updated with your book requests and activities
            </p>
        </div>

        @if($this->unreadCount > 0)
            <div class="mb-8 flex justify-end">
                <button wire:click="markAllAsRead"
                    class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    Mark All as Read
                </button>
            </div>
        @endif

        @if(session('success'))
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

        <!-- Notifications List -->
        <div class="space-y-4">
            @forelse($this->notifications as $notification)
                <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/20 overflow-hidden transform hover:-translate-y-1 transition-all duration-300 {{ !$notification->read ? 'border-indigo-300 bg-indigo-50/50' : '' }}">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    @if(!$notification->read)
                                        <span class="w-3 h-3 bg-indigo-500 rounded-full"></span>
                                    @endif
                                    <h3 class="text-lg font-bold text-slate-800">{{ $notification->title }}</h3>
                                </div>
                                <p class="text-slate-600 mb-3">{{ $notification->message }}</p>
                                <p class="text-sm text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="flex space-x-2 ml-4">
                                @if(!$notification->read)
                                    <button wire:click="markAsRead({{ $notification->id }})"
                                        class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl text-sm font-bold transition-all duration-300">
                                        Mark Read
                                    </button>
                                @endif
                                <button wire:click="deleteNotification({{ $notification->id }})"
                                    onclick="return confirm('Are you sure you want to delete this notification?')"
                                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-bold transition-all duration-300">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-20">
                    <div class="w-32 h-32 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-8">
                        <span class="text-6xl">🔔</span>
                    </div>
                    <p class="text-slate-600 text-xl mb-4 font-medium">No notifications yet</p>
                    <p class="text-slate-500 text-lg">You'll see notifications here when you receive book requests or updates.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="py-20">
            @if ($this->notifications->hasPages())
                {{ $this->notifications->links() }}
            @endif
        </div>
    </div>
</section>

