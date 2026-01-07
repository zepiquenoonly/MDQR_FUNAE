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
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

// Public web route for grievance creation (allow logged-in and guest submissions). Keep name for compatibility.
Route::post('/api/grievances', [GrievanceController::class, 'store'])->name('api.grievances.store');

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
// Route::get('/reclamacoes/nova', [GrievanceController::class, 'create'])->name('grievances.create');
// Route::get('/reclamacoes/acompanhar', function () {
//     return inertia('Grievances/Track');
// })->name('grievances.track');

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


    Route::prefix('apiComments')->group(function () {
        // Gestor
        Route::post('/comments/{grievance}/add', [App\Http\Controllers\ManagerGrievanceController::class, 'addSimpleComment'])->name('apiComments.comments.add');
        Route::get('/comments/{grievance}', [App\Http\Controllers\ManagerGrievanceController::class, 'getComments'])->name('apiComments.comments.get');
        
        // Director - ADICIONE A ROTA GET
        Route::post('/director/comments/{grievance}/add', [App\Http\Controllers\DirectorComplaintsController::class, 'addSimpleComment'])->name('apiComments.director.comments.add');
        Route::get('/director/comments/{grievance}', [App\Http\Controllers\DirectorComplaintsController::class, 'getComments'])->name('apiComments.director.comments.get');
        
        // Técnico
        Route::post('/technician/comments/{grievance}/add', [App\Http\Controllers\TechnicianGrievanceController::class, 'addComment'])->name('apiComments.technician.comments.add');
        Route::get('/technician/comments/{grievance}', [App\Http\Controllers\TechnicianGrievanceController::class, 'getComments'])->name('apiComments.technician.comments.get');
    });
   
    // Dashboard do Director
    Route::prefix('director')->name('director.')->group(function () {
        // Dashboard principal
        Route::get('/dashboard', [DirectorDashboardController::class, 'dashboard'])->name('dashboard');

        Route::get('/dashboard/api', [DirectorDashboardController::class, 'dashboardApi'])->name('dashboard.api');
        Route::get('/projects', [ProjectController::class, 'webIndex'])->name('director.projects');
        
        // Submissões (reclamações)
        Route::get('/complaints-overview', [DirectorComplaintsController::class, 'index'])->name('complaints-overview.index');
        Route::post('/complaints-overview/export', [DirectorComplaintsController::class, 'export'])->name('complaints-overview.export');
        
        // ROTAS PARA DETAILS
        Route::get('/grievances/{identifier}', [DirectorComplaintsController::class, 'show'])->name('grievances.show');
        Route::get('/complaints/{identifier}', [DirectorComplaintsController::class, 'show'])->name('complaints.show');
        Route::patch('/{grievance}/reassign', [ManagerGrievanceController::class, 'reassign'])->name('reassign');
        Route::patch('/{grievance}/update-priority', [ManagerGrievanceController::class, 'updatePriority'])->name('update-priority');

        // ROTA PARA MARCAR COMO VISTO
       // Route::post('/grievances/{id}/mark-as-seen', [DirectorComplaintsController::class, 'markAsSeen'])->name('grievances.mark-as-seen');
        
        Route::get('/interventions', [DirectorComplaintsController::class, 'getInterventions'])->name('interventions');
        Route::get('/manager-requests', [DirectorComplaintsController::class, 'getManagerRequests'])->name('manager-requests');

        // Funcionalidades de validação
        Route::post('/complaints/{id}/validate-case', [DirectorComplaintsController::class, 'validateCase'])->name('director.complaints.validate-case');
    
        Route::put('/complaints/{id}/validate/{validationId?}', [DirectorComplaintsController::class, 'updateValidation'])->name('director.complaints.update-validation');
        
        Route::post('/complaints/{id}/comment', [DirectorComplaintsController::class, 'addComment'])->name('complaints.comment');
        //Route::post('/complaints/{id}/reject', [DirectorComplaintsController::class, 'rejectCase'])->name('complaints.reject');
        Route::post('/complaints/{id}/analyze', [DirectorComplaintsController::class, 'analyzeCase'])->name('complaints.analyze');

        // Gestão de funcionários (equipe)
        /*Route::get('/employees', [ManagerDashboardController::class, 'indexTechnicians'])->name('manager.technicians.index');*/
        Route::get('/employees', [DirectorDashboardController::class, 'employees'])->name('employees.show');

         Route::get('/employees/{user}', [DirectorDashboardController::class, 'employeeDetails'])->name('director.employees.show');

        Route::get('/team/{id}', [DirectorTeamController::class, 'show'])->name('team.show');
        Route::get('/team/{id}/edit', [DirectorTeamController::class, 'edit'])->name('team.edit');
        Route::post('/team', [DirectorTeamController::class, 'store'])->name('team.store');
        Route::patch('/team/{id}', [DirectorTeamController::class, 'update'])->name('team.update');


        // Rota para atualizar status de reclamação
        Route::patch('/complaints/{id}/update', [DirectorComplaintsController::class, 'update'])->name('complaints.update');


        // Rota para atribuir reclamação
        Route::post('/complaints/{id}/assign', [DirectorComplaintsController::class, 'assign'])->name('complaints.assign');

        // Rota para toggle status de funcionário
        Route::patch('/team/{id}/toggle-status', [DirectorTeamController::class, 'toggleStatus'])->name('team.toggle-status');

        // Rota para remover funcionário
        Route::delete('/team/{id}', [DirectorTeamController::class, 'destroy'])->name('team.destroy');
        
        // ROTAS API PARA O COMPONENTE VUE
        Route::get('/api/submissions/recent', [DirectorComplaintsController::class, 'getRecentSubmissions'])->name('api.submissions.recent');
        Route::get('/api/submissions/all', [DirectorComplaintsController::class, 'getAllSubmissions'])->name('api.submissions.all');

        Route::get('/api/director-interventions', [ManagerGrievanceController::class, 'getDirectorInterventionsApi'])
        ->name('api.director-interventions');

        // Gestor - API para Minhas Submissões ao Director
        Route::get('/api/my-submissions-to-director', [ManagerGrievanceController::class, 'getMySubmissionsToDirectorApi'])
        ->name('api.my-submissions-to-director');

        // Gestor - API para obter dados específicos das tabs
        Route::get('/api/tab-data', [ManagerGrievanceController::class, 'getTabData'])
        ->name('api.tab-data');

        Route::post('/complaints/{grievance}/reject-approval', [DirectorComplaintsController::class, 'rejectApproval'])
        ->name('complaints.reject-approval');

        Route::post('/api/director/comments/{grievance}/add', [App\Http\Controllers\DirectorComplaintsController::class, 'addSimpleComment']);

        Route::post('/complaints/{grievance}/reject-approval', [DirectorComplaintsController::class, 'rejectApproval'])->name('director.complaints.reject-approval');
        Route::post('/{grievance}/validate', [DirectorComplaintsController::class, 'validateCase'])->name('director.complaints.validate');
        Route::post('/complaints/{grievance}/reject-submission', [DirectorComplaintsController::class, 'rejectSubmission'])->name('director.complaints.reject-submission');

        Route::prefix('/projects')->group(function () {
            Route::get('/', [ProjectController::class, 'webIndex'])->name('director.projects.index');
            Route::post('/', [ProjectController::class, 'store'])->name('director.projects.store');
            Route::get('/{project}', [ProjectController::class, 'show'])->name('director.projects.show');
            Route::put('/{project}', [ProjectController::class, 'update'])->name('director.projects.update');
            Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('director.projects.destroy');
        });

         Route::get('/estatisticas', [StatisticsController::class, 'index'])->name('statistics.page');
         Route::post('/export/async', [StatisticsController::class,'exportAsync'])->name('statistics.export.async');
         Route::get('/export/status', [StatisticsController::class,'exportStatus'])->name('statistics.export.status');
         Route::get('/exports/download/{filename}', [StatisticsController::class, 'downloadExport'])->name('exports.download');
         Route::post('/exports/cleanup', [StatisticsController::class, 'cleanupExports'])->name('exports.cleanup');

        
         Route::get('/export/statistics', [DirectorComplaintsController::class, 'exportStatistics'])->name('director.export.statistics');
    
        Route::get('/export/pdf', [DirectorComplaintsController::class, 'exportToPdf'])->name('director.export.pdf');
    
        Route::get('/export/simple-list', [DirectorComplaintsController::class, 'exportSimpleList'])->name('director.export.simple-list');
    
        Route::get('/export/check', [DirectorComplaintsController::class, 'checkExport'])->name('director.export.check');

        Route::get('/export/complete-report', [DirectorComplaintsController::class, 'exportCompleteReport'])->name('director.export.complete.report');
    
        Route::get('/export/check-export', [DirectorComplaintsController::class, 'checkExportData'])->name('director.export.check');

        Route::get('/employees/export/pdf', [DirectorDashboardController::class, 'exportEmployeesToPdf'])->name('employees.export.pdf');
        
    });

    Route::get('/statistics/export', [ManagerGrievanceController::class, 'exportStatistics'])->name('statistics.export.get');
    
    
    Route::post('/statistics/export', [ManagerGrievanceController::class, 'exportStatistics'])->name('statistics.export.post');
    

    // Dashboard do Gestor
    Route::prefix('gestor')->group(function () {
        Route::get('/dashboard', ManagerDashboardController::class)->name('manager.dashboard');
        Route::get('/dashboard/indicadores', [DepartmentIndicatorController::class, 'dashboard'])->name('dashboard.indicadores');
        Route::get('/technicians', [ManagerDashboardController::class, 'indexTechnicians'])->name('manager.technicians.index');
        Route::patch('/{grievance}/update-priority', [ManagerGrievanceController::class, 'updatePriority'])->name('update-priority');
        
        // ROTAS CORRIGIDAS PARA GESTOR
        Route::get('/grievances/{identifier}', [ManagerGrievanceController::class, 'show'])->name('grievances.show');
        Route::get('/complaints/{identifier}', [ManagerGrievanceController::class, 'show'])->name('complaints.show');
        
        // NOVA ROTA PARA MARCAR COMO VISTO (GESTOR)
        Route::post('/grievances/{id}/mark-as-seen', [ManagerGrievanceController::class, 'markAsSeen'])->name('grievances.mark-as-seen');
        
        //Route::get('/projects', [ProjectController::class, 'webIndex'])->name('manager.projects');
        Route::patch('/{grievance}/reassign', [ManagerGrievanceController::class, 'reassign'])->name('reassign');
        Route::put('/technicians/{technician}/status', [ManagerDashboardController::class, 'updateTechnicianStatus'])->name('manager.technicians.status');
        Route::post('/technicians/assign-task', [ManagerDashboardController::class, 'assignTaskToTechnician'])->name('manager.technicians.assign-task');
        Route::get('/director-interventions', [ManagerGrievanceController::class, 'getDirectorInterventions'])->name('director-interventions');
        
        // NOVA ROTA PARA "MINHAS SUBMISSÕES AO DIRECTOR"
        Route::get('/my-submissions-to-director', [ManagerGrievanceController::class, 'getMySubmissionsToDirector'])->name('my-submissions-to-director');
        
        // ROTAS API PARA O COMPONENTE VUE (GESTOR)
        Route::get('/api/submissions/recent', [ManagerGrievanceController::class, 'getRecentSubmissions'])->name('manager.api.submissions.recent');
        Route::get('/api/submissions/all', [ManagerGrievanceController::class, 'getAllSubmissions'])->name('manager.api.submissions.all');

        Route::get('/api/manager-requests', [DirectorComplaintsController::class, 'getManagerRequests'])
        ->name('api.manager-requests');

        Route::get('/tab-data', [ManagerGrievanceController::class, 'getTabData'])->name('tab-data');
        
        Route::get('/director-interventions', [ManagerGrievanceController::class, 'getDirectorInterventionsApi'])->name('director-interventions');
        
        Route::get('/my-submissions-to-director', [ManagerGrievanceController::class, 'getMySubmissionsToDirectorApi']) ->name('my-submissions-to-director');

        Route::get('/technicians/{user}', [ManagerDashboardController::class, 'showTechnician'])->name('manager.technicians.show');

         // Indicadores
        Route::get('/indicators', [DepartmentIndicatorController::class, 'directorDashboard'])->name('indicators');

        // Director - API para Minhas Intervenções
        Route::get('/api/my-interventions', [DirectorComplaintsController::class, 'getMyInterventions'])
            ->name('api.my-interventions');

        // Director - API para obter dados específicos das tabs
        Route::get('/api/tab-data', [DirectorComplaintsController::class, 'getTabData'])
        ->name('api.tab-data');

        Route::post('/complaints/{grievance}/reject-submission', [ManagerGrievanceController::class, 'rejectSubmission'])->name('manager.complaints.reject-submission');
        Route::post('/{grievance}/reject-approval', [ManagerGrievanceController::class, 'rejectApproval'])->name('manager.complaints.reject-approval');
        Route::patch('/{grievance}/reject-completion', [ManagerGrievanceController::class, 'rejectCompletion'])->name('manager.complaints.reject-completion');
        Route::patch('/{grievance}/complete', [ManagerGrievanceController::class, 'markComplete'])->name('manager.complaints.complete');

        Route::prefix('/projects')->group(function () {
            Route::get('/', [ProjectController::class, 'webIndex'])->name('gestor.projects.index');
            Route::post('/', [ProjectController::class, 'store'])->name('gestor.projects.store');
            Route::get('/{project}', [ProjectController::class, 'show'])->name('gestor.projects.show');
            Route::put('/{project}', [ProjectController::class, 'update'])->name('gestor.projects.update');
            Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('gestor.projects.destroy');
        });

        Route::get('/grievances/export/pdf', [ManagerGrievanceController::class, 'exportToPdf'])->name('manager.grievances.export.pdf');
        Route::post('/api/check-export', [ManagerGrievanceController::class, 'checkExport'])->name('manager.api.check-export');
        Route::get('/debug-export', [ManagerGrievanceController::class, 'debugComplete'])->name('manager.debug-export');



        Route::get('/export-all', [ManagerDashboardController::class, 'exportAllGrievances'])->name('manager.export.all');

        Route::get('/export/complete-report', [ManagerDashboardController::class, 'exportEmployeesToPdf'])->name('manager.export.complete.report');
    
        Route::get('/export/complete-report/{period}', [ManagerDashboardController::class, 'exportEmployeesToPdf'])->name('manager.export.complete.report.period');

        Route::get('/technicians/export/pdf', [ManagerDashboardController::class, 'exportEmployeesToPdf'])->name('manager.technicians.export.pdf');

        
        Route::get('/projects/export/pdf', [ProjectController::class, 'exportProjectsToPDF'])->name('manager.projects.export.pdf');
    
        Route::get('/projects/{project}/export/pdf', [ProjectController::class, 'exportProjectToPDF'])->name('manager.projects.single.export.pdf');

        Route::get('/indicators/export/pdf', [ManagerDashboardController::class, 'exportIndicatorsToPdf'])->name('manager.indicators.export.pdf');

        Route::get('/manager/test-pdf', [ManagerDashboardController::class, 'testPdfGeneration'])->name('manager.test.pdf');
        Route::get('/manager/debug-pdf', [ManagerDashboardController::class, 'debugPdfRequest'])->name('manager.debug.pdf');
        Route::get('/debug-export', [ManagerDashboardController::class, 'debugExport'])->name('manager.debug.export');
        Route::get('/debug-pdf-data', [ManagerDashboardController::class, 'debugPdfData'])->name('manager.debug.pdf.data');
    });

    // Rota para marcar como visto (comum)
    Route::post('/grievances/{id}/mark-as-seen', function ($id, Illuminate\Http\Request $request) {
        $user = $request->user();
        
        if ($user->hasRole('Director')) {
            return app(DirectorComplaintsController::class)->markAsSeen($request, $id);
        } elseif ($user->hasRole('Gestor')) {
            return app(ManagerGrievanceController::class)->markAsSeen($request, $id);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Acesso não autorizado'
        ], 403);
    })->name('api.grievances.mark-as-seen');
    
    // Rota para obter detalhes de submissão
    Route::get('/grievances/{identifier}', function ($identifier, Illuminate\Http\Request $request) {
        $user = $request->user();
        
        if ($user->hasRole('Director')) {
            return app(DirectorComplaintsController::class)->show($identifier);
        } elseif ($user->hasRole('Gestor')) {
            return app(ManagerGrievanceController::class)->show($request, $identifier);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Acesso não autorizado'
        ], 403);
    })->name('api.grievances.show');

    // ROTA QUE ESTÁ FALTANDO - CAUSA DO ERRO 404
   Route::post('/api/complaints/{id}/viewed-by-director', function ($id, Illuminate\Http\Request $request) {
    $user = $request->user();
    
    if (!$user->hasRole('Director')) {
        // Se for requisição Inertia
        if ($request->header('X-Inertia')) {
            return redirect()->back()->withErrors([
                'error' => 'Acesso não autorizado. Apenas o Director pode usar esta rota.'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Acesso não autorizado. Apenas o Director pode usar esta rota.'
        ], 403);
    }
    
    try {
        $grievance = \App\Models\Grievance::findOrFail($id);
        
        $statusChanged = false;
        
        // Atualizar status se for "submitted"
        if ($grievance->status === 'submitted') {
            DB::beginTransaction();
            
            $grievance->update([
                'status' => 'under_review',
                'reviewed_at' => now(),
                'reviewed_by' => $user->id,
            ]);
            
            // Registrar atividade
            \App\Models\GrievanceUpdate::create([
                'grievance_id' => $grievance->id,
                'user_id' => $user->id,
                'action_type' => 'status_changed',
                'description' => 'Submissão visualizada pelo Director e marcada como "Em Análise"',
                'metadata' => [
                    'old_status' => 'submitted',
                    'new_status' => 'under_review',
                    'marked_as_seen' => true,
                    'marked_by' => $user->id,
                    'marked_at' => now()->toISOString(),
                ],
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::commit();
            
            $statusChanged = true;
            
            \Log::info('Submissão marcada como vista pelo Director via API', [
                'grievance_id' => $grievance->id,
                'reference_number' => $grievance->reference_number,
                'director_id' => $user->id,
                'old_status' => 'submitted',
                'new_status' => 'under_review'
            ]);
        }
        
        // Verificar se é uma requisição Inertia
        if ($request->header('X-Inertia')) {
            if ($statusChanged) {
                return redirect()->back()->with([
                    'success' => 'Submissão marcada como "Em Análise" com sucesso!',
                    'updatedGrievance' => [
                        'id' => $grievance->id,
                        'reference_number' => $grievance->reference_number,
                        'status' => $grievance->status,
                        'reviewed_at' => $grievance->reviewed_at
                    ]
                ]);
            } else {
                return redirect()->back()->with('info', 'Submissão já estava em análise.');
            }
        }
        
        // Para requisições API/JSON
        return response()->json([
            'success' => true,
            'message' => 'Submissão marcada como vista com sucesso',
            'status_changed' => $statusChanged,
            'grievance' => [
                'id' => $grievance->id,
                'reference_number' => $grievance->reference_number,
                'status' => $grievance->status,
                'reviewed_at' => $grievance->reviewed_at
            ]
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Erro ao marcar submissão como vista via API', [
            'grievance_id' => $id,
            'user_id' => $user->id,
            'error' => $e->getMessage()
        ]);
        
        // Se for requisição Inertia
        if ($request->header('X-Inertia')) {
            return redirect()->back()->withErrors([
                'error' => 'Erro ao atualizar submissão: ' . $e->getMessage()
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Erro ao atualizar submissão: ' . $e->getMessage()
        ], 500);
    }
})->name('api.complaints.viewed-by-director');
    
    // Rota equivalente para Gestor
    Route::post('/api/complaints/{id}/viewed-by-manager', function ($id, Illuminate\Http\Request $request) {
        $user = $request->user();
        
        if (!$user->hasRole('Gestor')) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso não autorizado. Apenas o Gestor pode usar esta rota.'
            ], 403);
        }
        
        try {
            $grievance = \App\Models\Grievance::findOrFail($id);
            
            // Verificar se a submissão está atribuída a este gestor
            if ($grievance->assigned_to !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para visualizar esta submissão'
                ], 403);
            }
            
            // Atualizar status se for "submitted"
            if ($grievance->status === 'submitted') {
                DB::beginTransaction();
                
                $grievance->update([
                    'status' => 'under_review',
                    'reviewed_at' => now(),
                    'reviewed_by' => $user->id,
                ]);
                
                // Registrar atividade
                \App\Models\GrievanceUpdate::create([
                    'grievance_id' => $grievance->id,
                    'user_id' => $user->id,
                    'action_type' => 'status_changed',
                    'description' => 'Submissão visualizada pelo Gestor e marcada como "Em Análise"',
                    'metadata' => [
                        'old_status' => 'submitted',
                        'new_status' => 'under_review',
                        'marked_as_seen' => true,
                        'marked_by' => $user->id,
                        'marked_at' => now()->toISOString(),
                    ],
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                DB::commit();
                
                \Log::info('Submissão marcada como vista pelo Gestor via API', [
                    'grievance_id' => $grievance->id,
                    'reference_number' => $grievance->reference_number,
                    'manager_id' => $user->id,
                    'old_status' => 'submitted',
                    'new_status' => 'under_review'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Submissão marcada como vista com sucesso',
                'grievance' => [
                    'id' => $grievance->id,
                    'reference_number' => $grievance->reference_number,
                    'status' => $grievance->status,
                    'reviewed_at' => $grievance->reviewed_at
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao marcar submissão como vista pelo Gestor via API', [
                'grievance_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar submissão: ' . $e->getMessage()
            ], 500);
        }
    })->name('api.complaints.viewed-by-manager');

    Route::get('/check-director-interventions', [App\Http\Controllers\ManagerGrievanceController::class, 'checkDirectorInterventions'])
    ->middleware(['auth', 'verified']);
    
    Route::post('/api/technician/comments/{grievance}/add', [App\Http\Controllers\TechnicianGrievanceController::class, 'addComment']);

    // Gestão de reclamações pelo gestor
    Route::prefix('complaints')->name('complaints.')->group(function () {
        Route::get('/grievance/{identifier}', [ManagerGrievanceController::class, 'show'])->name('grievance.show');
        Route::patch('/{grievance}/complete', [ManagerGrievanceController::class, 'markComplete'])->name('complete');
        Route::post('/{grievance}/comment', [ManagerGrievanceController::class, 'addComment'])->name('comment');
        Route::post('/{grievance}/send-to-director',[ManagerGrievanceController::class, 'sendToDirector'])->name('send-to-director');
        Route::post('/complaints/{grievance}/revoke-escalation', [ManagerGrievanceController::class, 'revokeEscalation'])->name('complaints.revoke-escalation');
        Route::patch('/{grievance}/approve-completion', [ManagerGrievanceController::class, 'approveCompletion'])->name('approve-completion');
        //Route::patch('/{grievance}/reject-completion', [ManagerGrievanceController::class, 'rejectCompletion'])->name('reject-completion');
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
    // Route::prefix('profile')->name('profile.')->group(function () {
    //     Route::get('/', [ProfileController::class, 'edit'])->name('edit');
    //     Route::get('/info', [ProfileController::class, 'edit'])->name('info');
    //     Route::get('/security', [ProfileController::class, 'edit'])->name('security');
    //     Route::get('/notifications', [ProfileController::class, 'edit'])->name('notifications');
    //     Route::get('/preferences', [ProfileController::class, 'edit'])->name('preferences');

    //     Route::post('/infoUpdate', [ProfileController::class, 'update'])->name('update');
    //     Route::patch('/password', [ProfileController::class, 'updatePassword'])->name('update.password');
    //     Route::delete('/account', [ProfileController::class, 'destroy'])->name('destroy');

    //     Route::post('/avatar', [ProfileController::class, 'uploadAvatar'])->name('avatar.upload');
    //     Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])->name('avatar.delete');
    //     Route::get('/avatar', [ProfileController::class, 'getAvatar'])->name('avatar.get');
    // });

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

    // Download de anexos
    Route::get('/attachments/{attachment}/download', [GrievanceController::class, 'downloadAttachment'])
        ->name('attachments.download');


    /*Route::prefix('api/projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('api.projects.index');
        Route::get('/{project}', [ProjectController::class, 'showJson'])->name('api.projects.show');
        Route::post('/{project}', [ProjectController::class, 'update'])->name('api.projects.update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('api.projects.destroy');
    });

    // API de projetos
    Route::get('/manager/projects', [ProjectController::class, 'indexPage'])->name('projects');

    Route::prefix('manager/projects')->group(function () {
        Route::get('/', [ProjectController::class, 'indexPage'])->name('projects');
        Route::post('/', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::put('/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    });*/
    Route::prefix('api/projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('api.projects.index');
        Route::get('/{project}', [ProjectController::class, 'showJson'])->name('api.projects.show'); // Use showJson
        Route::post('/{project}', [ProjectController::class, 'update'])->name('api.projects.update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('api.projects.destroy');
    });


    // API de projetos

    // Rotas do GrievanceController (index/show require auth)
    Route::get('/grievances', [GrievanceController::class, 'index'])->name('grievances.index');
    Route::get('/grievances/{grievance}', [GrievanceController::class, 'show'])->name('grievances.show');
    Route::get('/grievances/track', [GrievanceController::class, 'track'])->name('grievances.track.api');

    // API de localizações e categorias
    Route::get('/api/categories', [GrievanceController::class, 'getCategories'])->name('api.categories');

    Route::get('/api/projects-list', [GrievanceController::class, 'getProjects'])->name('api.projects.list');
});

// API de localizações e categorias (públicas)
Route::get('/api/categories', [GrievanceController::class, 'getCategories'])->name('api.categories');
Route::get('/api/locations', [GrievanceController::class, 'getLocations'])->name('api.locations');
Route::get('/api/projects-list', [GrievanceController::class, 'getProjects'])->name('api.projects.list');

// Rotas de reclamações (acessíveis sem autenticação)
Route::get('/reclamacoes/nova', [GrievanceController::class, 'create'])->name('grievances.create');

Route::get('/reclamacoes/acompanhar', function () {
    return inertia('Grievances/Track');
})->name('grievances.track');

// Admin routes
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


Route::middleware('auth')->prefix('api')->group(function () {
    Route::post('/comments/{grievance}/add', [App\Http\Controllers\ManagerGrievanceController::class, 'addSimpleComment'])
        ->name('api.comments.add');
    
    // Obter comentários
    Route::get('/comments/{grievance}', [App\Http\Controllers\ManagerGrievanceController::class, 'getComments'])
        ->name('api.comments.get');
    
    // Comentários específicos para director
    Route::post('/director/comments/{grievance}/add', [App\Http\Controllers\DirectorComplaintsController::class, 'addSimpleComment'])
        ->name('api.director.comments.add');
    
    // Comentários específicos para técnico
    Route::post('/technician/comments/{grievance}/add', [App\Http\Controllers\TechnicianGrievanceController::class, 'addComment'])
        ->name('api.technician.comments.add');
});
Route::post('/locale', [LocaleController::class, 'update'])->name('locale.change');
Route::post('/api/locale', [LocaleController::class, 'updateApi'])->name('locale.update');
Route::get('/api/translations', [LocaleController::class, 'translations'])->name('locale.translations');
Route::get('/debug/locale', [ProfileController::class, 'debug'])->name('debug.locale');


Route::get('/test-locale/{locale}', function ($locale) {
    app()->setLocale($locale);
    session()->put('locale', $locale);
    
    return response()->json([
        'locale' => app()->getLocale(),
        'translation' => __('auth.failed'),
        'session' => session('locale'),
    ]);
    
});Route::get('/test-locale/{locale}', function ($locale) {
    app()->setLocale($locale);
    session()->put('locale', $locale);
    
    return response()->json([
        'locale' => app()->getLocale(),
        'translation' => __('auth.failed'),
        'session' => session('locale'),
    ]);
});