<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;

new class extends Component {
    #[Rule('required|email')]
    public $email = '';

    #[Rule('required')]
    public $password = '';

    public function login()
    {
        $this->validate();

        if (auth()->attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->regenerate();
            
            // Redirect based on user role
            if (auth()->user()->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            
            return redirect()->route('home');
        }

        $this->addError('email', 'The provided credentials do not match our records.');
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
                    <span class="text-white text-3xl">🔐</span>
                </div>
                <h2 class="text-4xl font-bold text-slate-800 mb-3">
                    Welcome back
                </h2>
                <p class="text-slate-600 text-lg">
                    Sign in to your BookSwap account
                </p>
            </div>

            <!-- Login Form -->
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-10">
                <form wire:submit="login" class="space-y-8">
                    <div>
                        <label for="email" class="block text-sm font-bold text-slate-700 mb-3">
                            Email address
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
                                placeholder="Enter your email">
                        </div>
                        @error('email')
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
                                placeholder="Enter your password">
                        </div>
                        @error('password')
                            <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <button type="submit"
                            class="group relative w-full flex justify-center py-4 px-6 border border-transparent text-sm font-bold rounded-2xl text-white bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 hover:from-indigo-600 hover:via-purple-600 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform hover:-translate-y-1 transition-all duration-300 shadow-xl hover:shadow-2xl">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-4">
                                <svg class="h-5 w-5 text-indigo-300 group-hover:text-indigo-200 transition-colors"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                                    </path>
                                </svg>
                            </span>
                            Sign in
                        </button>
                    </div>
                </form>

                <!-- Divider -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white/80 text-slate-500 font-medium">New to BookSwap?</span>
                    </div>
                </div>

                <!-- Sign up link -->
                <div class="text-center">
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center px-6 py-3 border border-slate-300 rounded-2xl text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 hover:border-indigo-400 hover:text-indigo-600 transition-all duration-300 shadow-sm hover:shadow-md">
                        Create your account
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
                    By signing in, you agree to our
                    <a href="#" class="text-indigo-600 hover:text-indigo-700 hover:underline font-medium">Terms of
                        Service</a>
                    and
                    <a href="#" class="text-indigo-600 hover:text-indigo-700 hover:underline font-medium">Privacy
                        Policy</a>
                </p>
            </div>
        </div>
    </div>
</section>
