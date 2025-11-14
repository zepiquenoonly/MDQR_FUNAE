<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() 
        ? redirect()->route('home')
        : redirect()->route('auth.login');
});

Route::middleware('guest')->group(function () {
    Route::get('/auth', [AuthController::class, 'showMain'])->name('auth.main');
    
    Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
    

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::get('/home', [AuthController::class, 'home'])->name('home');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});