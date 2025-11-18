<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GrievanceTrackingController;
use App\Http\Controllers\ProfileController;
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

    // Tracking de reclamação - acessível sem autenticação
    Route::get('/track', [GrievanceTrackingController::class, 'index'])->name('grievance.track');
    Route::post('/track', [GrievanceTrackingController::class, 'track'])->name('grievance.track.search');
    
    // Rota de teste (remover em produção)
    Route::get('/test-track', function () {
        return view('test-tracking');
    })->name('test.track');
});

Route::middleware('auth')->group(function () {
    Route::get('/home', [AuthController::class, 'home'])->name('home');

    // Rotas específicas por role - todas apontam para o método home
    Route::get('/admin/dashboard', [AuthController::class, 'home'])->name('admin.dashboard');
    Route::get('/gestor/dashboard', [AuthController::class, 'home'])->name('manager.dashboard');
    Route::get('/tecnico/dashboard', [AuthController::class, 'home'])->name('technician.dashboard');
    Route::get('/utente/dashboard', [AuthController::class, 'home'])->name('user.dashboard');
    Route::get('/project/{projectId}', [AuthController::class, 'showProject'])->name('project.details');

    // Rotas do Perfil com prefixo /perfil
    Route::prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/info', [ProfileController::class, 'edit'])->name('profile.info');
    Route::get('/security', [ProfileController::class, 'edit'])->name('profile.security');
    Route::get('/notifications', [ProfileController::class, 'edit'])->name('profile.notifications');
    Route::get('/preferences', [ProfileController::class, 'edit'])->name('profile.preferences');

    // Rotas de ação - CORRIGIDAS
    Route::patch('/info', [ProfileController::class, 'update'])->name('profile.update');
    // Remover a rota duplicada
    // Route::patch('/info/extended', [ProfileController::class, 'updateExtended'])->name('profile.update.extended');
    Route::patch('/password', [ProfileController::class, 'updatePassword'])->name('profile.update.password');
    Route::delete('/account', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
