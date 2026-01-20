<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::get('/books', function () {
    return view('pages.book-index');
})->name('books.index');

// Specific routes must come BEFORE parameterized routes
Route::get('/books/create', function () {
    return view('pages.book-create');
})->name('books.create');

Route::get('/books/{book}', function () {
    return view('pages.book-show');
})->name('books.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('pages.login');
    })->name('login');

    Route::get('/register', function () {
        return view('pages.register');
    })->name('register');
});

Route::middleware('auth')->group(function () {
    Route::get('/my-books', function () {
        return view('pages.my-books');
    })->name('books.my-books');

    Route::get('/books/{book}/edit', function () {
        return view('pages.book-edit');
    })->name('books.edit');

    Route::get('/requests', function () {
        return view('pages.request-index');
    })->name('requests.index');

    Route::get('/profile', function () {
        return view('pages.profile');
    })->name('profile');

    // Messaging routes
    Route::get('/messages', function () {
        return view('pages.messages');
    })->name('messages.index');

    // Ratings routes
    Route::get('/ratings', function () {
        return view('pages.ratings');
    })->name('ratings.index');

    // Notifications routes
    Route::get('/notifications', function () {
        return view('pages.notifications');
    })->name('notifications.index');

    // Disputes routes
    Route::post('/disputes', function () {
        // This route will be handled by Livewire
        return redirect()->back();
    })->name('disputes.store');

    // Book request routes
    Route::post('/books/{book}/request', function () {
        // This route will be handled by Livewire, but we need it defined
        return redirect()->back();
    })->name('books.request');

    Route::post('/logout', function () {
        auth()->logout();
        return redirect()->route('home');
    })->name('logout');

    // Admin routes
    Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('pages.admin.dashboard');
        })->name('dashboard');

        Route::get('/users', function () {
            return view('pages.admin.users');
        })->name('users.index');

        Route::get('/books', function () {
            return view('pages.admin.books');
        })->name('books.index');

        Route::get('/disputes', function () {
            return view('pages.admin.disputes');
        })->name('disputes.index');

        Route::get('/disputes/{dispute}', function () {
            return view('pages.admin.disputes-show');
        })->name('disputes.show');
    });
});
