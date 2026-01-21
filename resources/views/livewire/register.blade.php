<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\User;


new class extends Component {
    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('required|string|email|max:255|unique:users')]
    public $email = '';

    #[Rule('required|string|min:8|confirmed')]
    public $password = '';

    public $password_confirmation = '';

    #[Rule('required|string|in:admin,member')]
    public $role = '';

    public function signup()
    {
        $this->validate();
        $account = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            'role' => $this->role,
        ]);

        auth()->login($account);
        session()->flash('success', 'Welcome to BookSwap! Your account has been created successfully.');
        return redirect()->route('home');
    }
};

?>

<section>
    <div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Header -->
            <div class="text-center mb-10">
                <div
                    class="w-20 h-20 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl">
                    <span class="text-white text-3xl">🚀</span>
                </div>
                <h2 class="text-4xl font-bold text-slate-800 mb-3">
                    Join BookSwap
                </h2>
                <p class="text-slate-600 text-lg">
                    Create your account and start sharing the joy of reading
                </p>
            </div>

            <!-- Registration Form -->
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-10">
                <form wire:submit="signup" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-bold text-slate-700 mb-3">
                            Full Name
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <input wire:model="name" type="text" id="name" name="name" required
                                class="block w-full pl-12 pr-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm"
                                placeholder="Enter your full name">
                        </div>
                        @error('name')
                            <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-bold text-slate-700 mb-3">
                            Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207">
                                    </path>
                                </svg>
                            </div>
                            <input wire:model="email" type="email" id="email" name="email" required
                                class="block w-full pl-12 pr-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm"
                                placeholder="Enter your email address">
                        </div>
                        @error('email')
                            <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-bold text-slate-700 mb-3">
                            Role
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <select wire:model="role" id="role" name="role" required
                                class="block w-full pl-12 pr-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm">
                                <option value="">Select your role</option>
                                <option value="member">Member</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        @error('role')
                            <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-bold text-slate-700 mb-3">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </div>
                            <input wire:model="password" type="password" id="password" name="password" required
                                class="block w-full pl-12 pr-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm"
                                placeholder="Create a strong password">
                        </div>
                        @error('password')
                            <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-bold text-slate-700 mb-3">
                            Confirm Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <input wire:model="password_confirmation" type="password" id="password_confirmation"
                                name="password_confirmation" required
                                class="block w-full pl-12 pr-4 py-4 border border-slate-300 rounded-2xl text-slate-900 bg-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 shadow-sm"
                                placeholder="Confirm your password">
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit"
                            class="group relative w-full flex justify-center py-4 px-6 border border-transparent text-sm font-bold rounded-2xl text-white bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 hover:from-indigo-600 hover:via-purple-600 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform hover:-translate-y-1 transition-all duration-300 shadow-xl hover:shadow-2xl">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-4">
                                <svg class="h-5 w-5 text-indigo-300 group-hover:text-indigo-200 transition-colors"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                                    </path>
                                </svg>
                            </span>
                            Create Account
                        </button>
                    </div>
                </form>

                <!-- Divider -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white/80 text-slate-500 font-medium">Already have an account?</span>
                    </div>
                </div>

                <!-- Sign in link -->
                <div class="text-center">
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center px-6 py-3 border border-slate-300 rounded-2xl text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 hover:border-indigo-400 hover:text-indigo-600 transition-all duration-300 shadow-sm hover:shadow-md">
                        Sign in to your account
                        <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Additional info -->
            <div class="mt-8 text-center">
                <p class="text-sm text-slate-500">
                    By creating an account, you agree to our
                    <a href="#" class="text-indigo-600 hover:text-indigo-700 hover:underline font-medium">Terms
                        of Service</a>
                    and
                    <a href="#"
                        class="text-indigo-600 hover:text-indigo-700 hover:underline font-medium">Privacy Policy</a>
                </p>
            </div>
        </div>
    </div>
</section>
