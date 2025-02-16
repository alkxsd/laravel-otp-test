<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
});

// Public routes
Volt::route('/login', 'login')
    ->name('login')
    ->middleware('guest');

// OTP verification route
Volt::route('/verify-otp', 'otp-input')
    ->name('verify')
    ->middleware(['auth']);

// Protected routes
Volt::route('/', 'welcome')
    ->name('welcome')
    ->middleware(['auth', 'otp.verify']);

// Handle logout
Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/login');
})->name('logout');
