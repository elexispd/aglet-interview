<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Home/Browse Page
Volt::route('/', 'browse')->name('home');

// Favorites Page
Volt::route('/favorites', 'favorites')->name('favorites')->middleware('auth');

// Login Page
Volt::route('/login', 'login')->name('login');

// Contact Page
Volt::route('/contact', 'contact')->name('contact');

// Logout Route
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->name('logout');
