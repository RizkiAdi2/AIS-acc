<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


use App\Http\Controllers\DashboardController;


Route::middleware(['auth', 'admin'])->group(function () {
    // Ganti controller menjadi DashboardController
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
