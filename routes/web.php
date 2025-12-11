<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailTestController;
use App\Http\Controllers\GrievanceController;
use App\Http\Controllers\GrievanceTrackingController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\ManagerDashboardController;
use App\Http\Controllers\ManagerGrievanceController;
use App\Http\Controllers\DepartmentIndicatorController;
use App\Http\Controllers\PCADashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TechnicianDashboardController;
use App\Http\Controllers\TechnicianGrievanceController;
use App\Http\Controllers\UtenteDashboardController;
use App\Http\Controllers\DirectorDashboardController;
use App\Http\Controllers\DirectorComplaintsController;
use App\Http\Controllers\DirectorTeamController;
use Illuminate\Support\Facades\Route;

// Rotas públicas
Route::get('/', [GuestController::class, 'home'])->name('home.public');

Route::get('/ping', function () {
    return response()->json([
        'status' => 'ok',
        'time' => now()->toDateTimeString(),
        'app' => config('app.name'),
        'env' => config('app.env')
    ]);
});

// Testes de email (apenas para desenvolvimento)
Route::get('/email-test', [EmailTestController::class, 'showForm'])->name('email-test.form');
Route::post('/email-test/send', [EmailTestController::class, 'sendTestEmails'])->name('email-test.send');

// Rota antiga para compatibilidade
Route::get('/grievances-home', function () {
    return inertia('Grievances/Home');
})->name('grievances.home');

// Tracking de reclamações (público)
Route::get('/track', [GrievanceTrackingController::class, 'index'])->name('grievance.track');
Route::middleware('api')->post('/track', [GrievanceTrackingController::class, 'track'])->name('grievance.track.search');

// Locations API
Route::get('/api/locations', [GrievanceController::class, 'getLocations'])->name('api.locations');

// Visualização pública de anexos (com restrições)
Route::get('/attachments/{attachment}/view', [GrievanceController::class, 'viewAttachmentPublic'])
    ->name('attachments.view');

// Teste de tracking
Route::get('/test-track', function () {
    return view('test-tracking');
})->name('test.track');

// Rotas de criação e acompanhamento de reclamações
Route::get('/reclamacoes/nova', [GrievanceController::class, 'create'])->name('grievances.create');
Route::get('/reclamacoes/acompanhar', function () {
    return inertia('Grievances/Track');
})->name('grievances.track');

// Rotas para convidados (não autenticados)
Route::middleware('guest')->group(function () {
    Route::get('/auth', [AuthController::class, 'showMain'])->name('auth.main');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
    Route::get('/register/complete', [AuthController::class, 'showCompleteRegistration'])->name('auth.register.complete');

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register/complete', [AuthController::class, 'completeRegistration']);
});

// Rotas para usuários autenticados
Route::middleware('auth')->group(function () {
    // Redirecionamento inicial
    Route::get('/home', [AuthController::class, 'home'])->name('home');
    Route::get('/dashboard', [AuthController::class, 'home'])->name('dashboard'); // Rota genérica para compatibilidade
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboards por role
    Route::get('/pca/dashboard', PCADashboardController::class)->name('pca.dashboard');
    Route::get('/tecnico/dashboard', TechnicianDashboardController::class)->name('technician.dashboard');

    // Dashboard do Director
   Route::prefix('director')->name('director.')->group(function () {
    // Dashboard principal
    Route::get('/dashboard', [DirectorDashboardController::class, 'dashboard'])->name('dashboard');
    
    Route::get('/dashboard/api', [DirectorDashboardController::class, 'dashboardApi'])->name('dashboard.api');

    // Submissões (reclamações)
    Route::get('/complaints-overview', [DirectorComplaintsController::class, 'index'])->name('complaints-overview.index');
    Route::post('/complaints-overview/export', [DirectorComplaintsController::class, 'export'])->name('complaints-overview.export');
    Route::get('/complaints-overview/{grievance}', [DirectorComplaintsController::class, 'show'])->name('complaints-overview.show');

    // Funcionalidades de validação
    Route::post('/complaints/{id}/validate', [DirectorComplaintsController::class, 'validateCase'])->name('complaints.validate');
    Route::post('/complaints/{id}/comment', [DirectorComplaintsController::class, 'addComment'])->name('complaints.comment');
    Route::post('/complaints/{id}/reject', [DirectorComplaintsController::class, 'rejectCase'])->name('complaints.reject');
    Route::post('/complaints/{id}/analyze', [DirectorComplaintsController::class, 'analyzeCase'])->name('complaints.analyze');
    
    // Gestão de funcionários (equipe)
    Route::get('/managers', [DirectorTeamController::class, 'index'])->name('managers.index');
    Route::get('/team/{id}', [DirectorTeamController::class, 'show'])->name('team.show');
    Route::get('/team/{id}/edit', [DirectorTeamController::class, 'edit'])->name('team.edit');
    Route::post('/team', [DirectorTeamController::class, 'store'])->name('team.store');
    Route::patch('/team/{id}', [DirectorTeamController::class, 'update'])->name('team.update');
    
    // Indicadores
    Route::get('/indicators', [DepartmentIndicatorController::class, 'directorDashboard'])->name('indicators');
    
    // Rota para adicionar comentário a uma reclamação
    Route::post('/complaints/{id}/comment', [DirectorComplaintsController::class, 'addComment'])->name('complaints.comment');
    
    // Rota para atualizar status de reclamação
    Route::patch('/complaints/{id}/update', [DirectorComplaintsController::class, 'update'])->name('complaints.update');
    
    // Rota para atribuir reclamação
    Route::post('/complaints/{id}/assign', [DirectorComplaintsController::class, 'assign'])->name('complaints.assign');
    
    // Rota para toggle status de funcionário
    Route::patch('/team/{id}/toggle-status', [DirectorTeamController::class, 'toggleStatus'])->name('team.toggle-status');
    
    // Rota para remover funcionário
    Route::delete('/team/{id}', [DirectorTeamController::class, 'destroy'])->name('team.destroy');

});

    // Dashboard do Gestor
    Route::prefix('gestor')->name('gestor.')->group(function () {
        Route::get('/dashboard', ManagerDashboardController::class)->name('manager.dashboard');
        Route::get('/dashboard/indicadores', [DepartmentIndicatorController::class, 'dashboard'])->name('dashboard.indicadores');
    });

    // Gestão de reclamações pelo gestor
    Route::prefix('complaints')->name('complaints.')->group(function () {
        Route::get('/{grievance}', [ManagerGrievanceController::class, 'show'])->name('grievance.show');
        Route::patch('/{grievance}/update-priority', [ManagerGrievanceController::class, 'updatePriority'])->name('update-priority');
        Route::patch('/{grievance}/reassign', [ManagerGrievanceController::class, 'reassign'])->name('reassign');
        Route::post('/{grievance}/escalate', [ManagerGrievanceController::class, 'escalate'])->name('escalate');
        Route::patch('/{grievance}/complete', [ManagerGrievanceController::class, 'markComplete'])->name('complete');
        Route::post('/{grievance}/comment', [ManagerGrievanceController::class, 'addComment'])->name('comment');
        Route::post('/{grievance}/send-to-director', [ManagerGrievanceController::class, 'sendToDirector'])->name('send-to-director');
        Route::patch('/{grievance}/approve-completion', [ManagerGrievanceController::class, 'approveCompletion'])->name('approve-completion');
        Route::patch('/{grievance}/reject-completion', [ManagerGrievanceController::class, 'rejectCompletion'])->name('reject-completion');
        Route::post('/{grievance}/save-manager-comment', [ManagerGrievanceController::class, 'saveComment'])->name('save-manager-comment');
        Route::post('/bulk-assign', [ManagerGrievanceController::class, 'bulkAssign'])->name('bulk-assign');
        Route::get('/export', [ManagerGrievanceController::class, 'export'])->name('export');
    });

    // Dashboard do Utente
    Route::prefix('utente')->name('user.')->group(function () {
        Route::get('/dashboard', [UtenteDashboardController::class, 'index'])->name('dashboard');
        Route::get('/grievances/history', [UtenteDashboardController::class, 'history'])->name('grievances.history');
        Route::get('/grievances/{grievance}', [UtenteDashboardController::class, 'show'])->name('grievances.show');
        Route::get('/grievances/{grievance}/status-updates', [UtenteDashboardController::class, 'getStatusUpdates'])->name('grievances.status-updates');
        Route::post('/notifications/read', [UtenteDashboardController::class, 'markNotificationsRead'])->name('notifications.read');
    });

    // Fluxo do técnico
    Route::prefix('technician')->name('technician.')->group(function () {
        Route::get('/grievances/{grievance}', [TechnicianGrievanceController::class, 'show'])->name('grievances.show');
        Route::patch('/grievances/{grievance}/start', [TechnicianGrievanceController::class, 'startWork'])->name('grievances.start');
        Route::post('/grievances/{grievance}/updates', [TechnicianGrievanceController::class, 'storeUpdate'])->name('grievances.updates.store');
        Route::post('/grievances/{grievance}/request-completion', [TechnicianGrievanceController::class, 'requestCompletion'])->name('grievances.request-completion');
    });

    // Rotas de perfil
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::get('/info', [ProfileController::class, 'edit'])->name('info');
        Route::get('/security', [ProfileController::class, 'edit'])->name('security');
        Route::get('/notifications', [ProfileController::class, 'edit'])->name('notifications');
        Route::get('/preferences', [ProfileController::class, 'edit'])->name('preferences');

        Route::patch('/info', [ProfileController::class, 'update'])->name('update');
        Route::patch('/password', [ProfileController::class, 'updatePassword'])->name('update.password');
        Route::delete('/account', [ProfileController::class, 'destroy'])->name('destroy');

        Route::post('/avatar', [ProfileController::class, 'uploadAvatar'])->name('avatar.upload');
        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])->name('avatar.delete');
        Route::get('/avatar', [ProfileController::class, 'getAvatar'])->name('avatar.get');
    });

    // Download de anexos
    Route::get('/attachments/{attachment}/download', [GrievanceController::class, 'downloadAttachment'])
        ->name('attachments.download');

    // API para técnicos
    Route::get('/api/tecnicos', function () {
        try {
            $tecnicos = \App\Models\User::whereHas('roles', function($query) {
                $query->where('name', 'Técnico');
            })->get();

            $tecnicosData = $tecnicos->map(function ($tecnico) {
                return [
                    'id' => $tecnico->id,
                    'name' => $tecnico->name,
                    'email' => $tecnico->email,
                    'username' => $tecnico->username,
                    'phone' => $tecnico->phone ?? 'N/A',
                    'province' => $tecnico->province ?? 'N/A',
                    'district' => $tecnico->district ?? 'N/A',
                    'is_active' => true,
                    'active_cases_count' => 0,
                    'total_cases' => 0,
                    'created_at' => $tecnico->created_at,
                ];
            });

            $stats = [
                'totalTecnicos' => $tecnicosData->count(),
                'tecnicosAtivos' => $tecnicosData->count(),
                'casosAtribuidos' => 0,
            ];

            return response()->json([
                'tecnicos' => $tecnicosData,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro na API Tecnicos: ' . $e->getMessage());

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
                'casos_mensais' => [],
                'historico_mensal' => []
            ];

            return response()->json($desempenho);

        } catch (\Exception $e) {
            \Log::error('Erro ao carregar desempenho do técnico: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao carregar dados'], 500);
        }
    })->name('api.tecnicos.desempenho');

    // API de projetos
    Route::prefix('api/projects')->name('api.projects.')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::post('/{project}', [ProjectController::class, 'update'])->name('update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('destroy');
    });

// Rotas de reclamações (acessíveis sem autenticação)
Route::get('/reclamacoes/nova', [GrievanceController::class, 'create'])->name('grievances.create');
    Route::get('/reclamacoes/acompanhar', function () {
        return inertia('Grievances/Track');
    })->name('grievances.track');
});

Route::middleware(['auth', 'can:manage-users'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\AdminDashboardController::class, 'dashboard'])->name('dashboard');
    
    // Users CRUD
    Route::resource('users', \App\Http\Controllers\UserController::class);
    
    // Departments CRUD
    Route::resource('departments', \App\Http\Controllers\DepartmentController::class);
    
    // Projects CRUD
    Route::get('projects', [\App\Http\Controllers\AdminDashboardController::class, 'indexProjects'])->name('projects.index');
    Route::get('projects/create', [\App\Http\Controllers\AdminDashboardController::class, 'createProject'])->name('projects.create');
    Route::post('projects', [\App\Http\Controllers\AdminDashboardController::class, 'storeProject'])->name('projects.store');
    Route::get('projects/{project}/edit', [\App\Http\Controllers\AdminDashboardController::class, 'editProject'])->name('projects.edit');
    Route::put('projects/{project}', [\App\Http\Controllers\AdminDashboardController::class, 'updateProject'])->name('projects.update');
    Route::delete('projects/{project}', [\App\Http\Controllers\AdminDashboardController::class, 'destroyProject'])->name('projects.destroy');
});

