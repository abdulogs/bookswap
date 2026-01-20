<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component {
    public $search = '';

    public function mount()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
    }

    public function getUsersProperty()
    {
        $query = User::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        return $query->withCount(['books', 'borrowingRequests', 'lendingRequests'])
            ->latest()
            ->paginate(15);
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);
        
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }

        $user->delete();
        session()->flash('success', 'User deleted successfully!');
    }

    public function toggleRole($userId)
    {
        $user = User::findOrFail($userId);
        
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot change your own role.');
            return;
        }

        $user->update([
            'role' => $user->role === 'admin' ? 'member' : 'admin'
        ]);
        
        session()->flash('success', 'User role updated successfully!');
    }
};

?>

<section>
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-16">
            <h1 class="text-5xl md:text-6xl font-bold text-slate-800 mb-6">
                User Management
            </h1>
        </div>

        <!-- Search -->
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-6 mb-8">
            <input wire:model.live.debounce.300ms="search" type="text"
                class="w-full px-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm"
                placeholder="Search users by name or email...">
        </div>

        <!-- Users Table -->
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-gradient-to-r from-slate-50 to-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">User</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Books</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Requests</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/50 divide-y divide-slate-200">
                        @forelse($this->users as $user)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center">
                                            <span class="text-white text-sm font-bold">{{ substr($user->name, 0, 1) }}</span>
                                        </div>
                                        <span class="font-bold text-slate-800">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $user->books_count }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $user->borrowing_requests_count + $user->lending_requests_count }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        <button wire:click="toggleRole({{ $user->id }})"
                                            class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl text-sm font-bold transition-all">
                                            {{ $user->role === 'admin' ? 'Make Member' : 'Make Admin' }}
                                        </button>
                                        <button wire:click="deleteUser({{ $user->id }})"
                                            onclick="return confirm('Are you sure you want to delete this user?')"
                                            class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-bold transition-all">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">No users found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="py-8">
            @if ($this->users->hasPages())
                {{ $this->users->links() }}
            @endif
        </div>
    </div>
</section>

