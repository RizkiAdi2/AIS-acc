<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

Route::post('/logout', function () {
    auth()->logout();
    return redirect('/');
})->name('logout');

// Dashboard route with admin middleware
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
