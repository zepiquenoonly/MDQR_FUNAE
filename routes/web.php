<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailTestController;
use App\Http\Controllers\GrievanceController;
use App\Http\Controllers\GrievanceTrackingController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\ManagerDashboardController;
use App\Http\Controllers\ManagerGrievanceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TechnicianDashboardController;
use App\Http\Controllers\TechnicianGrievanceController;
use Illuminate\Support\Facades\Route;

/*Route::get('/', function () {
    return inertia('Grievances/Home');
})->name('grievances.home');*/

Route::get('/', [GuestController::class, 'home'])->name('home');

// Rotas de teste de emails (acessível sem autenticação para facilitar testes)
Route::get('/email-test', [EmailTestController::class, 'showForm'])->name('email-test.form');
Route::post('/email-test/send', [EmailTestController::class, 'sendTestEmails'])->name('email-test.send');

// Mantenha a rota antiga com um nome diferente para evitar conflitos
Route::get('/grievances-home', function () {
    return inertia('Grievances/Home');
})->name('grievances.home');

// Tracking de reclamação - acessível para todos (logados ou não)
Route::get('/track', [GrievanceTrackingController::class, 'index'])->name('grievance.track');
Route::post('/track', [GrievanceTrackingController::class, 'track'])->name('grievance.track.search');

Route::middleware('guest')->group(function () {
    Route::get('/auth', [AuthController::class, 'showMain'])->name('auth.main');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
    Route::get('/register/complete', [AuthController::class, 'showCompleteRegistration'])->name('auth.register.complete');

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register/complete', [AuthController::class, 'completeRegistration']);
    
    // Rota de teste
    Route::get('/test-track', function () {
        return view('test-tracking');
    })->name('test.track');
});

Route::middleware('auth')->group(function () {
    Route::get('/home', [AuthController::class, 'home'])->name('home');

    // Rotas específicas por role
    Route::get('/admin/dashboard', [AuthController::class, 'home'])->name('admin.dashboard');
    Route::get('/gestor/dashboard', ManagerDashboardController::class)->name('manager.dashboard');
    
    Route::get('/tecnico/dashboard', TechnicianDashboardController::class)->name('technician.dashboard');
    Route::get('/utente/dashboard', [AuthController::class, 'home'])->name('user.dashboard');
    Route::get('/project/{projectId}', [AuthController::class, 'showProject'])->name('project.details');

    // Fluxo do técnico
    Route::patch('/technician/grievances/{grievance}/start', [TechnicianGrievanceController::class, 'startWork'])
        ->name('technician.grievances.start');
    Route::post('/technician/grievances/{grievance}/updates', [TechnicianGrievanceController::class, 'storeUpdate'])
        ->name('technician.grievances.updates.store');
    Route::post('/technician/grievances/{grievance}/request-completion', [TechnicianGrievanceController::class, 'requestCompletion'])
        ->name('technician.grievances.request-completion');

    // Fluxo do gestor
    Route::prefix('complaints')->name('complaints.')->group(function () {
        Route::patch('/{grievance}/update-priority', [ManagerGrievanceController::class, 'updatePriority'])
            ->name('update-priority');
        Route::patch('/{grievance}/reassign', [ManagerGrievanceController::class, 'reassign'])
            ->name('reassign');
        Route::post('/{grievance}/escalate', [ManagerGrievanceController::class, 'escalate'])
            ->name('escalate');
        Route::patch('/{grievance}/complete', [ManagerGrievanceController::class, 'markComplete'])
            ->name('complete');
        Route::patch('/{grievance}/reject-completion', [ManagerGrievanceController::class, 'rejectCompletion'])
            ->name('reject');
        Route::post('/bulk-assign', [ManagerGrievanceController::class, 'bulkAssign'])
            ->name('bulk-assign');
        Route::get('/export', [ManagerGrievanceController::class, 'export'])
            ->name('export');

            Route::post('/grievances/bulk-assign', [GrievanceController::class, 'bulkAssign'])->name('complaints.bulk-assign');
Route::get('/grievances/export', [GrievanceController::class, 'export'])->name('complaints.export');

    });


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

    // Download seguro de anexos
    Route::get('/attachments/{attachment}/download', [GrievanceController::class, 'downloadAttachment'])
        ->name('attachments.download');

        // routes/web.php - adicione esta rota
// routes/web.php - VERSÃO SIMPLIFICADA
Route::get('/api/tecnicos', function () {
    try {
        // Buscar todos os usuários primeiro (para debug)
        $allUsers = \App\Models\User::all();
        \Log::info('Total de usuários no sistema: ' . $allUsers->count());

        // Tentar buscar técnicos de forma segura
        $tecnicos = \App\Models\User::whereHas('roles', function($query) {
            $query->where('name', 'Técnico');
        })->get();

        \Log::info('Técnicos encontrados: ' . $tecnicos->count());

        $tecnicosData = $tecnicos->map(function ($tecnico) {
            return [
                'id' => $tecnico->id,
                'name' => $tecnico->name,
                'email' => $tecnico->email,
                'username' => $tecnico->username,
                'phone' => $tecnico->phone ?? 'N/A',
                'province' => $tecnico->province ?? 'N/A',
                'district' => $tecnico->district ?? 'N/A',
                'is_active' => true, // Valor padrão
                'active_cases_count' => 0, // Valor padrão
                'total_cases' => 0, // Valor padrão
                'created_at' => $tecnico->created_at,
            ];
        });

        $stats = [
            'totalTecnicos' => $tecnicosData->count(),
            'tecnicosAtivos' => $tecnicosData->count(), // Assumindo que todos estão ativos
            'casosAtribuidos' => 0, // Valor padrão
        ];

        return response()->json([
            'tecnicos' => $tecnicosData,
            'stats' => $stats
        ]);

    } catch (\Exception $e) {
        \Log::error('Erro crítico na API Tecnicos: ' . $e->getMessage());

        // Retornar dados vazios mas sem erro 500
        return response()->json([
            'tecnicos' => [],
            'stats' => [
                'totalTecnicos' => 0,
                'tecnicosAtivos' => 0,
                'casosAtribuidos' => 0
            ]
        ]);
    }
})->name('api.tecnicos.index');

Route::get('/api/tecnicos/{tecnicoId}/desempenho', function ($tecnicoId) {
    try {
        $tecnico = \App\Models\User::findOrFail($tecnicoId);
        
        // Dados de desempenho (exemplo - implemente conforme sua lógica de negócio)
        $desempenho = [
            'estatisticas_gerais' => [
                'total_casos' => 150,
                'casos_resolvidos' => 120,
                'taxa_sucesso' => 80,
                'tempo_medio' => 3.5,
            ],
            'desempenho_mensal' => [
                'casos_atribuidos' => 15,
                'casos_resolvidos' => 12,
                'em_progresso' => 3,
                'taxa_resolucao' => 80,
            ],
            'casos_mensais' => [
                // Array de casos do mês
            ],
            'historico_mensal' => [
                // Array de histórico dos últimos meses
            ]
        ];

        return response()->json($desempenho);

    } catch (\Exception $e) {
        \Log::error('Erro ao carregar desempenho do técnico: ' . $e->getMessage());
        return response()->json(['error' => 'Erro ao carregar dados'], 500);
    }
})->name('api.tecnicos.desempenho');



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