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
    Route::get('/register/complete', [AuthController::class, 'showCompleteRegistration'])->name('auth.register.complete');

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register/complete', [AuthController::class, 'completeRegistration']);
});

Route::middleware('auth')->group(function () {
    Route::get('/home', [AuthController::class, 'home'])->name('home');
    
    // Rotas específicas por role - todas apontam para o método home
    Route::get('/admin/dashboard', [AuthController::class, 'home'])->name('admin.dashboard');
    Route::get('/gestor/dashboard', [AuthController::class, 'home'])->name('manager.dashboard');
    Route::get('/tecnico/dashboard', [AuthController::class, 'home'])->name('technician.dashboard');
    Route::get('/utente/dashboard', [AuthController::class, 'home'])->name('user.dashboard');
    Route::get('/project/{projectId}', [AuthController::class, 'showProject'])->name('project.details');
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
