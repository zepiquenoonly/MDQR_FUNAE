<?php

namespace App\Http\Controllers;

use App\Models\Grievance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ManagerDashboardController extends Controller
{
    /**
     * Display the manager dashboard with team oversight.
     */
 private function normalizeRole($roleName)
    {
        $roleMap = [
            'PCA' => 'pca',
            'Gestor' => 'manager',
            'Técnico' => 'technician',
            'Director' => 'director',
            'Utente' => 'utente',
            'Admin' => 'admin',
        ];
        
        return $roleMap[$roleName] ?? strtolower($roleName);
    }


    public function __invoke(Request $request): Response
    {
        $user = $request->user();
         abort_if(!$user || !$user->hasRole('Gestor'), 403);

        $status = $request->input('status');
        $priority = $request->input('priority');
        $category = $request->input('category');
        $type = $request->input('type');

        $filters = [
            'status' => $status !== null && $status !== '' ? $status : null,
            'priority' => $priority !== null && $priority !== '' ? $priority : null,
            'category' => $category !== null && $category !== '' ? $category : null,
            'type' => $type !== null && $type !== '' ? $type : null,
        ];

        // Query base para reclamações - SEMPRE retornar dados
        $complaintsQuery = Grievance::query()
            ->with(['user:id,name,email', 'assignedUser:id,name', 'attachments'])
            ->latest('submitted_at');

        // Aplicar filtros
        if ($filters['status']) {
            $complaintsQuery->where('status', $filters['status']);
        }
        if ($filters['priority']) {
            $complaintsQuery->where('priority', $filters['priority']);
        }
        if ($filters['category']) {
            $complaintsQuery->where('category', $filters['category']);
        }
        if ($filters['type']) {
            $complaintsQuery->where('type', $filters['type']);
        }

        // Paginação para a lista principal - garantir que sempre retorna pelo menos array vazio
        $complaints = $complaintsQuery->paginate(10)->through(function ($grievance) {
            return [
                'id' => $grievance->id,
                'title' => $grievance->description,
                'description' => $grievance->description,
                'type' => $grievance->type,
                'priority' => $grievance->priority,
                'status' => $grievance->status,
                'category' => $grievance->category,
                'created_at' => $grievance->created_at,
                'submitted_at' => $grievance->submitted_at,
                'reference_number' => $grievance->reference_number,
                'province' => $grievance->province,
                'district' => $grievance->district,
                'user' => $grievance->user ? [
                    'name' => $grievance->user->name,
                ] : null,
                'technician' => $grievance->assignedUser ? [
                    'name' => $grievance->assignedUser->name,
                ] : null,
                'attachments' => $grievance->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'name' => $attachment->original_filename,
                        'size' => $attachment->size,
                    ];
                })->toArray(),
            ];
        });

        // TODAS as reclamações (sem paginação) para a visualização completa
        $allComplaintsQuery = Grievance::query()
            ->with(['user:id,name,email', 'assignedUser:id,name'])
            ->latest('submitted_at');

        $allComplaints = $allComplaintsQuery->get()->map(function ($grievance) {
            return [
                'id' => $grievance->id,
                'title' => $grievance->description,
                'description' => $grievance->description,
                'type' => $grievance->type,
                'priority' => $grievance->priority,
                'status' => $grievance->status,
                'category' => $grievance->category,
                'created_at' => $grievance->created_at,
                'submitted_at' => $grievance->submitted_at,
                'reference_number' => $grievance->reference_number,
                'province' => $grievance->province,
                'district' => $grievance->district,
                'user' => $grievance->user ? [
                    'name' => $grievance->user->name,
                ] : null,
                'technician' => $grievance->assignedUser ? [
                    'name' => $grievance->assignedUser->name,
                ] : null,
                'attachments' => $grievance->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'name' => $attachment->original_filename,
                        'size' => $attachment->size,
                    ];
                })->toArray(),
            ];
        });

        // Estatísticas - garantir valores padrão
        $stats = [
            'pending_complaints' => Grievance::whereIn('status', ['submitted', 'under_review', 'assigned'])->count() ?: 0,
            'in_progress' => Grievance::where('status', 'in_progress')->count() ?: 0,
            'high_priority' => Grievance::where('priority', 'high')->count() ?: 0,
            'pending_completion_requests' => Grievance::where('status', 'pending_approval')->count() ?: 0,
        ];
        // Técnicos disponíveis
        $indexTechnicians = User::role('Técnico')
            ->select('id', 'name', 'email')
            ->get()
            ->map(function ($technician) {
                return [
                    'id' => $technician->id,
                    'name' => $technician->name,
                    'email' => $technician->email,
                ];
            })
            ->toArray();

        return Inertia::render('Manager/Dashboard', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $this->normalizeRole($user->getRoleNames()->first()),
                'created_at' => $user->created_at?->format('d/m/Y'),
            ],
            'complaints' => $complaints,
            'allComplaints' => $allComplaints,
            'stats' => $stats,
            'technicians' => $indexTechnicians,
            'filters' => $filters,
            'canEdit' => $user->hasRole('Gestor'),
        ]);
    }

    /**
     * Display all technicians
     */
   public function indexTechnicians(Request $request): Response
{
    $user = $request->user();
    
    $hasPermission = $user->hasAnyRole(['Gestor', 'Director']);
    
    if (!$hasPermission) {
        abort(403, 'Acesso não autorizado. Apenas gestores podem acessar esta página.');
    }

    // Obter parâmetros de busca e filtro
    $search = $request->input('search', '');
    $province = $request->input('province', '');
    $status = $request->input('status', '');

    // Query base para técnicos - REMOVER last_login_at
    $techniciansQuery = User::role('Técnico')
        ->select('id', 'name', 'username', 'email', 'phone', 'province', 'district', 'neighborhood', 'street', 'is_available', 'created_at', 'updated_at')
        ->latest();

    // Aplicar filtro de busca
    if ($search) {
        $techniciansQuery->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // Aplicar filtro por província
    if ($province) {
        $techniciansQuery->where('province', $province);
    }

    // Aplicar filtro por status (ativo/inativo)
    if ($status === 'active') {
        $techniciansQuery->where('is_available', true);
    } elseif ($status === 'inactive') {
        $techniciansQuery->where('is_available', false);
    }

    // Paginação
    $technicians = $techniciansQuery->paginate(15)->through(function ($technician) {
        // Estatísticas do técnico
        $stats = $this->getTechnicianStats($technician->id);
        
        return [
            'id' => $technician->id,
            'name' => $technician->name,
            'username' => $technician->username,
            'email' => $technician->email,
            'phone' => $technician->phone ?? 'N/A',
            'province' => $technician->province ?? 'N/A',
            'district' => $technician->district ?? 'N/A',
            'neighborhood' => $technician->neighborhood ?? 'N/A',
            'street' => $technician->street ?? 'N/A',
            'is_available' => (bool) ($technician->is_available ?? true),
            'created_at' => $technician->created_at ? $technician->created_at->format('d/m/Y H:i') : 'N/A',
            'updated_at' => $technician->updated_at ? $technician->updated_at->format('d/m/Y H:i') : 'N/A',
            // REMOVER last_login pois não existe
            'tasks_assigned' => $stats['total_assigned'],
            'tasks_completed' => $stats['completed'],
            'tasks_pending' => $stats['pending'],
            'tasks_cancelled' => $stats['cancelled'],
            'tasks_in_progress' => $stats['in_progress'],
            'performance_rate' => $stats['completion_rate'],
            'average_resolution_time' => $stats['average_resolution_time'],
        ];
    });

    // Estatísticas gerais
    $totalTechnicians = User::role('Técnico')->count();
    $activeTechnicians = User::role('Técnico')->where('is_available', true)->count();
    $inactiveTechnicians = User::role('Técnico')->where('is_available', false)->count();
    
    // Técnicos com mais tarefas
    $topTechnicians = User::role('Técnico')
        ->withCount(['assignedGrievances' => function ($query) {
            $query->whereIn('status', ['assigned', 'in_progress']);
        }])
        ->orderBy('assigned_grievances_count', 'desc')
        ->limit(5)
        ->get()
        ->map(function ($technician) {
            return [
                'id' => $technician->id,
                'name' => $technician->name,
                'tasks_count' => $technician->assigned_grievances_count,
            ];
        });

    // Lista de províncias únicas para filtro
    $provinces = User::role('Técnico')
        ->select('province')
        ->distinct()
        ->whereNotNull('province')
        ->orderBy('province')
        ->pluck('province');

    // Calcular média de tarefas por técnico
    $totalAssignedTasks = Grievance::whereNotNull('assigned_to')->count();
    $averageTasksPerTechnician = $totalTechnicians > 0 
        ? round($totalAssignedTasks / $totalTechnicians, 1)
        : 0;

    return Inertia::render('Common/TechnicianPage', [
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->getRoleNames()->first(),
            'created_at' => $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A',
        ],
        'technicians' => $technicians,
        'filters' => [
            'search' => $search,
            'province' => $province,
            'status' => $status,
        ],
        'stats' => [
            'total_technicians' => $totalTechnicians,
            'active_technicians' => $activeTechnicians,
            'inactive_technicians' => $inactiveTechnicians,
            'average_tasks_per_technician' => $averageTasksPerTechnician,
            'total_assigned_tasks' => $totalAssignedTasks,
        ],
        'top_technicians' => $topTechnicians,
        'provinces' => $provinces,
        'canEdit' => $user->hasRole('Gestor'),
    ]);
}

    /**
     * Display technician details
     */
   public function showTechnician(Request $request, User $technician): Response
{
    $user = $request->user();
    
    // Verificar se o usuário tem permissão e se o usuário é técnico
   $hasPermission = $user->hasAnyRole(['Gestor', 'Director']);
    
    if (!$hasPermission) {
        abort(403, 'Acesso não autorizado. Apenas gestores podem acessar esta página.');
    }

    // Estatísticas do técnico
    $technicianStats = $this->getTechnicianStats($technician->id);

    // Tarefas recentes
   $recentTasks = Grievance::where('assigned_to', $technician->id)
    ->with(['user:id,name,email'])
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get()
    ->map(function ($task) {
        return [
            'id' => $task->id,
            'title' => $task->description,
            'status' => $task->status,
            'priority' => $task->priority,
            'type' => $task->type,
            'category' => $task->category,
            'created_at' => $task->created_at->format('d/m/Y H:i'),
            'submitted_at' => $task->submitted_at ? $task->submitted_at->format('d/m/Y H:i') : null,
            'completed_at' => $task->resolved_at ? $task->resolved_at->format('d/m/Y H:i') : null, // USAR resolved_at
            'user' => $task->user ? [
                'name' => $task->user->name,
                'email' => $task->user->email,
            ] : null,
        ];
    });

    // Performance por mês
    $performanceByMonth = $this->getTechnicianPerformanceByMonth($technician->id);

    // Tarefas por status (gráfico)
    $tasksByStatus = [
        ['status' => 'Concluídas', 'count' => $technicianStats['completed'], 'color' => 'bg-green-500'],
        ['status' => 'Pendentes', 'count' => $technicianStats['pending'], 'color' => 'bg-yellow-500'],
        ['status' => 'Canceladas', 'count' => $technicianStats['cancelled'], 'color' => 'bg-red-500'],
        ['status' => 'Em Progresso', 'count' => $technicianStats['in_progress'], 'color' => 'bg-blue-500'],
    ];

    // Tarefas por prioridade
    $tasksByPriority = Grievance::where('assigned_to', $technician->id)
        ->selectRaw('priority, COUNT(*) as count')
        ->groupBy('priority')
        ->get()
        ->map(function ($item) {
            return [
                'priority' => $item->priority ?: 'Não especificada',
                'count' => $item->count,
            ];
        });

    // Resolução por mês (últimos 6 meses)
    $resolutionByMonth = $this->getResolutionByMonth($technician->id);

    return Inertia::render('Common/TechnicianDetail', [
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->getRoleNames()->first(),
            'created_at' => $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A',
        ],
        'technician' => [
            'id' => $technician->id,
            'name' => $technician->name,
            'username' => $technician->username,
            'email' => $technician->email,
            'phone' => $technician->phone ?? 'N/A',
            'province' => $technician->province ?? 'N/A',
            'district' => $technician->district ?? 'N/A',
            'neighborhood' => $technician->neighborhood ?? 'N/A',
            'street' => $technician->street ?? 'N/A',
            'is_available' => (bool) ($technician->is_available ?? true),
            'created_at' => $technician->created_at ? $technician->created_at->format('d/m/Y H:i') : 'N/A',
            'updated_at' => $technician->updated_at ? $technician->updated_at->format('d/m/Y H:i') : 'N/A',
            // REMOVER last_login pois não existe
        ],
        'stats' => $technicianStats,
        'recent_tasks' => $recentTasks,
        'performance_by_month' => $performanceByMonth,
        'tasks_by_status' => $tasksByStatus,
        'tasks_by_priority' => $tasksByPriority,
        'resolution_by_month' => $resolutionByMonth,
        'canEdit' => $user->hasRole('Gestor'),
    ]);
}
    /**
     * Get technician statistics
     */
   /**
 * Get technician statistics
 */
private function getTechnicianStats($technicianId): array
{
    $totalAssigned = Grievance::where('assigned_to', $technicianId)->count();
    
    // Pendentes: assigned, in_progress, pending_approval
    $pending = Grievance::where('assigned_to', $technicianId)
        ->whereIn('status', ['assigned', 'in_progress', 'pending_approval'])
        ->count();
    
    // Concluídas: resolved e closed
    $completed = Grievance::where('assigned_to', $technicianId)
        ->whereIn('status', ['resolved', 'closed'])
        ->count();
    
    // Canceladas: rejected
    $cancelled = Grievance::where('assigned_to', $technicianId)
        ->where('status', 'rejected')
        ->count();
    
    // Em progresso: apenas in_progress
    $inProgress = Grievance::where('assigned_to', $technicianId)
        ->where('status', 'in_progress')
        ->count();

    $completionRate = $totalAssigned > 0 
        ? round(($completed / $totalAssigned) * 100, 2)
        : 0;

    // Calcular tempo médio de resolução - USAR resolved_at para tarefas resolved
    $completedTasks = Grievance::where('assigned_to', $technicianId)
        ->where('status', 'resolved')
        ->whereNotNull('resolved_at')
        ->whereNotNull('assigned_at')
        ->get();

    $averageResolutionTime = 0;
    if ($completedTasks->count() > 0) {
        $totalHours = 0;
        foreach ($completedTasks as $task) {
            if ($task->assigned_at && $task->resolved_at) {
                $hours = $task->assigned_at->diffInHours($task->resolved_at);
                $totalHours += $hours;
            }
        }
        $averageResolutionTime = round($totalHours / $completedTasks->count(), 1);
    }

    return [
        'total_assigned' => $totalAssigned,
        'pending' => $pending,
        'completed' => $completed,
        'cancelled' => $cancelled,
        'in_progress' => $inProgress,
        'completion_rate' => $completionRate,
        'average_resolution_time' => $averageResolutionTime,
    ];
}
    /**
     * Get technician performance by month
     */
    private function getTechnicianPerformanceByMonth($technicianId): array
    {
        $currentYear = date('Y');
        $performance = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $startDate = date("{$currentYear}-{$month}-01");
            $endDate = date("{$currentYear}-{$month}-t", strtotime($startDate));
            
            $total = Grievance::where('assigned_to', $technicianId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
                
            $completed = Grievance::where('assigned_to', $technicianId)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
            
            $performance[] = [
                'month' => date('M', strtotime($startDate)),
                'total' => $total,
                'completed' => $completed,
                'rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            ];
        }
        
        return $performance;
    }

    /**
     * Get resolution by month (last 6 months)
     */
    private function getResolutionByMonth($technicianId): array
{
    $months = [];
    $now = Carbon::now();
    
    for ($i = 5; $i >= 0; $i--) {
        $month = $now->copy()->subMonths($i);
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();
        
        $completed = Grievance::where('assigned_to', $technicianId)
            ->whereIn('status', ['resolved', 'closed']) // USAR status correcto
            ->whereBetween('resolved_at', [$startDate, $endDate]) // USAR resolved_at
            ->count();
        
        $months[] = [
            'month' => $month->format('M/Y'),
            'completed' => $completed,
        ];
    }
    
    return $months;
}

    /**
     * Update technician status (active/inactive)
     */
    public function updateTechnicianStatus(Request $request, User $technician)
    {
        $user = $request->user();
        
        abort_if(!$user || !$user->hasRole('Gestor'), 403);
        abort_if(!$technician->hasRole('Técnico'), 404);

        $validated = $request->validate([
            'is_available' => 'required|boolean',
        ]);

        $technician->update([
            'is_available' => $validated['is_available'],
        ]);

        return back()->with('success', 'Status do técnico atualizado com sucesso!');
    }

    /**
     * Assign task to technician
     */
    public function assignTaskToTechnician(Request $request)
    {
        $user = $request->user();
        
        abort_if(!$user || !$user->hasRole('Gestor'), 403);

        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id',
            'task_id' => 'required|exists:grievances,id',
        ]);

        // Verificar se o usuário é técnico
        $technician = User::find($validated['technician_id']);
        if (!$technician->hasRole('Técnico')) {
            return response()->json(['error' => 'O usuário selecionado não é um técnico'], 400);
        }

        // Atribuir tarefa
        $task = Grievance::find($validated['task_id']);
        $task->update([
            'assigned_to' => $validated['technician_id'],
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tarefa atribuída com sucesso!',
            'technician' => [
                'id' => $technician->id,
                'name' => $technician->name,
            ]
        ]);
    }
}