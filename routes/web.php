<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\WelcomeController;

// Authentication Routes
// Auth::routes();

// Welcome Page
Route::get('/', [WelcomeController::class, 'index'])->name('home');

// Contact Form Submission
Route::post('/contact', [WelcomeController::class, 'submitContact'])->name('contact.submit');

// Admin SPA Route (Vue Router will handle the rest)
Route::get('/admin/{any?}', function () {
    return view('admin');
})->where('any', '.*')->name('admin.dashboard');

// Dashboard Redirect (for authenticated users)
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware('auth');
