<?php

use App\Http\Controllers\GrievanceController;
use App\Http\Controllers\LocaleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rotas públicas (não requerem autenticação)
// Note: grievance creation now uses the web route protected by session auth.
// The POST /grievances API route was removed to ensure submissions use web session authentication.
Route::post('/grievances/track', [GrievanceController::class, 'track'])->name('api.grievances.track');

// Rotas auxiliares
Route::get('/grievances/categories', [GrievanceController::class, 'getCategories'])->name('api.grievances.categories');
Route::get('/grievances/locations', [GrievanceController::class, 'getLocations'])->name('api.grievances.locations');
Route::get('/grievances/projects', [GrievanceController::class, 'getProjects'])->name('api.grievances.projects');

// Rotas protegidas (requerem autenticação)
Route::middleware('auth:sanctum')->group(function () {
    // Listar reclamações
    Route::get('/grievances', [GrievanceController::class, 'index'])->name('api.grievances.index');

    // Visualizar reclamação específica
    Route::get('/grievances/{grievance}', [GrievanceController::class, 'show'])->name('api.grievances.show');

    // Download de anexo
    Route::get('/attachments/{attachment}/download', [GrievanceController::class, 'downloadAttachment'])->name('api.attachments.download');

    // Informações do usuário autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::middleware('api')->group(function () {
    Route::post('/locale', [LocaleController::class, 'update']);
    Route::get('/translations', [LocaleController::class, 'translations']);
});
