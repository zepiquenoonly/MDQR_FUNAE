<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GrievanceTrackingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GrievanceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\GuestController;

/*Route::get('/', function () {
    return inertia('Grievances/Home');
})->name('grievances.home');*/

Route::get('/', [GuestController::class, 'home'])->name('home');

// Mantenha a rota antiga com um nome diferente para evitar conflitos
Route::get('/grievances-home', function () {
    return inertia('Grievances/Home');
})->name('grievances.home');

Route::middleware('guest')->group(function () {
    Route::get('/auth', [AuthController::class, 'showMain'])->name('auth.main');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
    Route::get('/register/complete', [AuthController::class, 'showCompleteRegistration'])->name('auth.register.complete');

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register/complete', [AuthController::class, 'completeRegistration']);

    // Tracking de reclamação
    Route::get('/track', [GrievanceTrackingController::class, 'index'])->name('grievance.track');
    Route::post('/track', [GrievanceTrackingController::class, 'track'])->name('grievance.track.search');
    
    // Rota de teste
    Route::get('/test-track', function () {
        return view('test-tracking');
    })->name('test.track');
});

Route::middleware('auth')->group(function () {
    Route::get('/home', [AuthController::class, 'home'])->name('home');

    // Rotas específicas por role
    Route::get('/admin/dashboard', [AuthController::class, 'home'])->name('admin.dashboard');
    Route::get('/gestor/dashboard', [AuthController::class, 'home'])->name('manager.dashboard');
    Route::get('/tecnico/dashboard', [AuthController::class, 'home'])->name('technician.dashboard');
    Route::get('/utente/dashboard', [AuthController::class, 'home'])->name('user.dashboard');
    Route::get('/project/{projectId}', [AuthController::class, 'showProject'])->name('project.details');

    // Rotas do Perfil
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::get('/info', [ProfileController::class, 'edit'])->name('profile.info');
        Route::get('/security', [ProfileController::class, 'edit'])->name('profile.security');
        Route::get('/notifications', [ProfileController::class, 'edit'])->name('profile.notifications');
        Route::get('/preferences', [ProfileController::class, 'edit'])->name('profile.preferences');

        Route::patch('/info', [ProfileController::class, 'update'])->name('profile.update');
        Route::patch('/password', [ProfileController::class, 'updatePassword'])->name('profile.update.password');
        Route::delete('/account', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Rotas de Projects (API)
Route::get('/api/projects', [ProjectController::class, 'index'])->name('api.projects.index');
Route::get('/api/projects/{project}', [ProjectController::class, 'show'])->name('api.projects.show');
Route::post('/api/projects', [ProjectController::class, 'store'])->name('api.projects.store');
Route::post('/api/projects/{project}', [ProjectController::class, 'update'])->name('api.projects.update');
Route::delete('/api/projects/{project}', [ProjectController::class, 'destroy'])->name('api.projects.destroy');

// Rotas de reclamações
Route::get('/reclamacoes/nova', [GrievanceController::class, 'create'])->name('grievances.create');
Route::get('/reclamacoes/acompanhar', function () {
    return inertia('Grievances/Track');
})->name('grievances.track');