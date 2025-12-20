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

        // Filtrar por departamento do gestor
        $managedDepartment = \App\Models\Department::where('manager_id', $user->id)->first();
        if ($managedDepartment) {
            $complaintsQuery->whereHas('project', function ($query) use ($managedDepartment) {
                $query->where('department_id', $managedDepartment->id);
            });
        }

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
                        'url' => $attachment->url,
                    ];
                })->toArray(),
            ];
        });

        // TODAS as reclamações (sem paginação) para a visualização completa
        $allComplaintsQuery = Grievance::query()
            ->with(['user:id,name,email', 'assignedUser:id,name'])
            ->latest('submitted_at');

        if ($managedDepartment) {
            $allComplaintsQuery->whereHas('project', function ($query) use ($managedDepartment) {
                $query->where('department_id', $managedDepartment->id);
            });
        }

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
                'url' => $attachment->url,
            ];
        })->toArray(),
    ];
});

        // Estatísticas - garantir valores padrão
        $statsQuery = Grievance::query();
        if ($managedDepartment) {
            $statsQuery->whereHas('project', function ($query) use ($managedDepartment) {
                $query->where('department_id', $managedDepartment->id);
            });
        }

        $stats = [
            'pending_complaints' => (clone $statsQuery)->whereIn('status', ['submitted', 'under_review', 'assigned'])->count() ?: 0,
            'in_progress' => (clone $statsQuery)->where('status', 'in_progress')->count() ?: 0,
            'high_priority' => (clone $statsQuery)->where('priority', 'high')->count() ?: 0,
            'pending_completion_requests' => (clone $statsQuery)->where('status', 'pending_approval')->count() ?: 0,
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
   /**
 * Display all technicians AND managers (for directors)
 */
/**
 * Display all technicians AND managers (for directors)
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
    $roleFilter = $request->input('role', '');

    \Log::info('Filtros recebidos no controller do gestor:', [
        'role' => $roleFilter,
        'search' => $search,
        'province' => $province,
        'status' => $status,
        'all_params' => $request->all()
    ]);

    // Mapear os valores do frontend para os valores do banco de dados
    $roleMap = [
        'manager' => 'Gestor',
        'technician' => 'Técnico',
        'all' => 'all'
    ];
    
    $roleValue = isset($roleMap[$roleFilter]) ? $roleMap[$roleFilter] : $roleFilter;

    // Determinar quais roles incluir baseado no role do usuário atual
    $rolesToInclude = ['Técnico']; // Por padrão, sempre incluir técnicos
    
    // Se o usuário atual for Director, incluir também Gestores
    if ($user->hasRole('Director')) {
        $rolesToInclude[] = 'Gestor';
    }

    // Query base - agora incluindo múltiplos roles
    $usersQuery = User::whereHas('roles', function ($query) use ($rolesToInclude, $roleValue) {
        if ($roleValue && $roleValue !== 'all') {
            $query->where('name', $roleValue);
        } else {
            $query->whereIn('name', $rolesToInclude);
        }
    })
    ->select('id', 'name', 'username', 'email', 'phone', 'province', 'district', 'neighborhood', 'street', 'is_available', 'created_at', 'updated_at')
    ->latest();

    // Aplicar filtro de busca
    if ($search) {
        $usersQuery->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // Aplicar filtro por província
    if ($province) {
        $usersQuery->where('province', $province);
    }

    // Aplicar filtro por status (ativo/inativo)
    if ($status === 'active') {
        $usersQuery->where('is_available', true);
    } elseif ($status === 'inactive') {
        $usersQuery->where('is_available', false);
    }

    // Contagens totais para as tabs (com filtros aplicados)
    $counts = $this->getUserCounts($user, $roleValue, $status);
    
    // Paginação
    $users = $usersQuery->paginate(15)->through(function ($userItem) {
        // Determinar o role do usuário
        $roleName = $userItem->getRoleNames()->first() ?? 'Técnico';
        $isTechnician = $roleName === 'Técnico';
        
        // Obter estatísticas apenas para técnicos
        $stats = $isTechnician ? $this->getTechnicianStats($userItem->id) : [
            'total_assigned' => 0,
            'completed' => 0,
            'pending' => 0,
            'cancelled' => 0,
            'in_progress' => 0,
            'completion_rate' => 0,
            'average_resolution_time' => 0,
        ];
        
        return [
            'id' => $userItem->id,
            'name' => $userItem->name,
            'username' => $userItem->username,
            'email' => $userItem->email,
            'phone' => $userItem->phone ?? 'N/A',
            'province' => $userItem->province ?? 'N/A',
            'district' => $userItem->district ?? 'N/A',
            'neighborhood' => $userItem->neighborhood ?? 'N/A',
            'street' => $userItem->street ?? 'N/A',
            'role' => $this->normalizeRole($roleName),
            'role_label' => $roleName,
            'is_technician' => $isTechnician,
            'is_available' => (bool) ($userItem->is_available ?? true),
            'created_at' => $userItem->created_at ? $userItem->created_at->format('d/m/Y H:i') : 'N/A',
            'updated_at' => $userItem->updated_at ? $userItem->updated_at->format('d/m/Y H:i') : 'N/A',
            // Estatísticas
            'tasks_assigned' => $stats['total_assigned'],
            'tasks_completed' => $stats['completed'],
            'tasks_pending' => $stats['pending'],
            'tasks_cancelled' => $stats['cancelled'],
            'tasks_in_progress' => $stats['in_progress'],
            'performance_rate' => $stats['completion_rate'],
            'average_resolution_time' => $stats['average_resolution_time'],
        ];
    });

    \Log::info('Resultados da query do gestor:', [
        'total' => $users->total(),
        'current_page' => $users->currentPage(),
        'per_page' => $users->perPage(),
        'data_count' => $users->count(),
        'has_more_pages' => $users->hasMorePages(),
    ]);

    // Estatísticas gerais - separar por role
    $totalTechnicians = User::role('Técnico')->count();
    $totalManagers = User::role('Gestor')->count();
    
    $activeTechnicians = User::role('Técnico')->where('is_available', true)->count();
    $activeManagers = User::role('Gestor')->where('is_available', true)->count();
    
    $inactiveTechnicians = User::role('Técnico')->where('is_available', false)->count();
    $inactiveManagers = User::role('Gestor')->where('is_available', false)->count();
    
    // Para Director: total de todos
    // Para Gestor: apenas técnicos
    $totalUsers = $user->hasRole('Director') 
        ? $totalTechnicians + $totalManagers 
        : $totalTechnicians;
    
    $activeUsers = $user->hasRole('Director')
        ? $activeTechnicians + $activeManagers
        : $activeTechnicians;
        
    $inactiveUsers = $user->hasRole('Director')
        ? $inactiveTechnicians + $inactiveManagers
        : $inactiveTechnicians;

    // Calcular média de tarefas por técnico (apenas para técnicos)
    $totalAssignedTasks = Grievance::whereNotNull('assigned_to')->count();
    $averageTasksPerTechnician = $totalTechnicians > 0 
        ? round($totalAssignedTasks / $totalTechnicians, 1)
        : 0;
        
    // Para gestores, usar estatísticas diferentes
    $averageTasksPerUser = $user->hasRole('Director')
        ? ($totalUsers > 0 ? round($totalAssignedTasks / $totalUsers, 1) : 0)
        : $averageTasksPerTechnician;

    // Lista de províncias únicas para filtro
    $provinces = User::whereHas('roles', function ($query) use ($rolesToInclude) {
        $query->whereIn('name', $rolesToInclude);
    })
    ->select('province')
    ->distinct()
    ->whereNotNull('province')
    ->orderBy('province')
    ->pluck('province');

    return Inertia::render('Common/TechnicianPage', [
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->getRoleNames()->first(),
            'created_at' => $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A',
        ],
        'current_user_role' => $this->normalizeRole($user->getRoleNames()->first()),
        'technicians' => $users,
        // Adicionar contagens totais para as tabs
        'counts' => $counts,
        'filters' => [
            'search' => $search,
            'province' => $province,
            'status' => $status,
            'role' => $roleFilter,
        ],
        'stats' => [
            'total_technicians' => $totalUsers,
            'total_technicians_only' => $totalTechnicians,
            'total_managers' => $totalManagers,
            'active_technicians' => $activeUsers,
            'active_technicians_only' => $activeTechnicians,
            'active_managers' => $activeManagers,
            'inactive_technicians' => $inactiveUsers,
            'inactive_technicians_only' => $inactiveTechnicians,
            'inactive_managers' => $inactiveManagers,
            'average_tasks_per_technician' => $averageTasksPerUser,
            'total_assigned_tasks' => $totalAssignedTasks,
        ],
        'provinces' => $provinces,
        'canEdit' => $user->hasRole('Gestor'),
    ]);
}

/**
 * Obter contagens totais de usuários com filtros (para tabs)
 */
private function getUserCounts($currentUser, $roleValue, $status): array
{
    $rolesToInclude = ['Técnico'];
    if ($currentUser->hasRole('Director')) {
        $rolesToInclude[] = 'Gestor';
    }

    $query = User::whereHas('roles', function ($query) use ($rolesToInclude, $roleValue) {
        if ($roleValue && $roleValue !== 'all') {
            $query->where('name', $roleValue);
        } else {
            $query->whereIn('name', $rolesToInclude);
        }
    });

    // Aplicar filtro de status (usando a nova coluna status)
    if ($status === 'active') {
        $query->where('status', 'active');
    } elseif ($status === 'inactive') {
        $query->where('status', 'inactive');
    }

    $total = $query->count();
    
    // Para técnicos
    $techniciansQuery = clone $query;
    $techniciansQuery->whereHas('roles', function ($q) {
        $q->where('name', 'Técnico');
    });
    $technicians = $techniciansQuery->count();
    
    // Para gestores (apenas se for director)
    $managers = 0;
    if ($currentUser->hasRole('Director')) {
        $managersQuery = clone $query;
        $managersQuery->whereHas('roles', function ($q) {
            $q->where('name', 'Gestor');
        });
        $managers = $managersQuery->count();
    }
    
    // Ativos e inativos (baseado no status já aplicado)
    $activeQuery = clone $query;
    $inactiveQuery = clone $query;
    
    $active = $activeQuery->where('status', 'active')->count();
    $inactive = $inactiveQuery->where('status', 'inactive')->count();

    return [
        'all' => $total,
        'technicians' => $technicians,
        'managers' => $managers,
        'active' => $active,
        'inactive' => $inactive,
    ];
}
    /**
     * Display technician details
     */
   /**
 * Display technician OR manager details
 */
public function showTechnician(Request $request, User $user): Response
{
    $currentUser = $request->user();
    
    // Verificar se o usuário tem permissão
    $hasPermission = $currentUser->hasAnyRole(['Gestor', 'Director']);
    
    if (!$hasPermission) {
        abort(403, 'Acesso não autorizado. Apenas gestores e diretores podem acessar esta página.');
    }
    
    // Verificar se o usuário a ser visualizado é Técnico OU Gestor
    $isTechnician = $user->hasRole('Técnico');
    $isManager = $user->hasRole('Gestor');
    
    if (!$isTechnician && !$isManager) {
        abort(404, 'Usuário não encontrado ou não é membro da equipa.');
    }

    // Se o usuário atual é Gestor, só pode ver Técnicos
    if ($currentUser->hasRole('Gestor') && $isManager) {
        abort(403, 'Gestores não podem visualizar detalhes de outros gestores.');
    }

    // Estatísticas diferentes para técnicos e gestores
    if ($isTechnician) {
        $stats = $this->getTechnicianStats($user->id);
    } else {
        // Estatísticas específicas para gestores
        $stats = $this->getManagerStats($user->id);
    }

    // Tarefas recentes (apenas para técnicos)
    $recentTasks = $isTechnician 
        ? Grievance::where('assigned_to', $user->id)
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
                    'completed_at' => $task->resolved_at ? $task->resolved_at->format('d/m/Y H:i') : null,
                    'user' => $task->user ? [
                        'name' => $task->user->name,
                        'email' => $task->user->email,
                    ] : null,
                ];
            })
        : [];

    // Performance por mês (apenas para técnicos)
    $performanceByMonth = $isTechnician 
        ? $this->getTechnicianPerformanceByMonth($user->id)
        : [];

    // Tarefas por status (apenas para técnicos)
    $tasksByStatus = $isTechnician 
        ? [
            ['status' => 'Concluídas', 'count' => $stats['completed'], 'color' => 'bg-green-500'],
            ['status' => 'Pendentes', 'count' => $stats['pending'], 'color' => 'bg-yellow-500'],
            ['status' => 'Canceladas', 'count' => $stats['cancelled'], 'color' => 'bg-red-500'],
            ['status' => 'Em Progresso', 'count' => $stats['in_progress'], 'color' => 'bg-blue-500'],
        ]
        : [];

    // Tarefas por prioridade (apenas para técnicos)
    $tasksByPriority = $isTechnician 
        ? Grievance::where('assigned_to', $user->id)
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->get()
            ->map(function ($item) {
                return [
                    'priority' => $item->priority ?: 'Não especificada',
                    'count' => $item->count,
                ];
            })
        : [];

    // Resolução por mês (apenas para técnicos)
    $resolutionByMonth = $isTechnician 
        ? $this->getResolutionByMonth($user->id)
        : [];

    // Se for gestor, buscar informações do departamento
    $managerDepartment = null;
    $managedTechnicians = [];
    
    if ($isManager) {
        $managerDepartment = \App\Models\Department::where('manager_id', $user->id)->first();
        
        if ($managerDepartment) {
            // Buscar técnicos gerenciados por este gestor
            $managedTechnicians = User::role('Técnico')
                ->whereHas('assignedGrievances', function ($query) use ($managerDepartment) {
                    $query->whereHas('project', function ($q) use ($managerDepartment) {
                        $q->where('department_id', $managerDepartment->id);
                    });
                })
                ->select('id', 'name', 'email')
                ->limit(5)
                ->get()
                ->map(function ($technician) {
                    return [
                        'id' => $technician->id,
                        'name' => $technician->name,
                        'email' => $technician->email,
                    ];
                });
        }
    }

    return Inertia::render('Common/TechnicianDetail', [
        'user' => [
            'name' => $currentUser->name,
            'email' => $currentUser->email,
            'role' => $this->normalizeRole($currentUser->getRoleNames()->first()),
            'created_at' => $currentUser->created_at ? $currentUser->created_at->format('d/m/Y') : 'N/A',
        ],
        'technician' => [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone ?? 'N/A',
            'province' => $user->province ?? 'N/A',
            'district' => $user->district ?? 'N/A',
            'neighborhood' => $user->neighborhood ?? 'N/A',
            'street' => $user->street ?? 'N/A',
            'role' => $this->normalizeRole($user->getRoleNames()->first()),
            'role_label' => $user->getRoleNames()->first(),
            'is_technician' => $isTechnician,
            'is_available' => (bool) ($user->is_available ?? true),
            'created_at' => $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A',
            'updated_at' => $user->updated_at ? $user->updated_at->format('d/m/Y H:i') : 'N/A',
        ],
        'manager_info' => $isManager ? [
            'department' => $managerDepartment ? [
                'id' => $managerDepartment->id,
                'name' => $managerDepartment->name,
                'description' => $managerDepartment->description,
            ] : null,
            'managed_technicians' => $managedTechnicians,
        ] : null,
        'stats' => $stats,
        'recent_tasks' => $recentTasks,
        'performance_by_month' => $performanceByMonth,
        'tasks_by_status' => $tasksByStatus,
        'tasks_by_priority' => $tasksByPriority,
        'resolution_by_month' => $resolutionByMonth,
        'canEdit' => $currentUser->hasRole('Gestor'),
    ]);
}


private function getManagerStats($managerId): array
{
    // Buscar departamento do gestor
    $department = \App\Models\Department::where('manager_id', $managerId)->first();
    
    if (!$department) {
        return [
            'total_assigned' => 0,
            'pending' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'in_progress' => 0,
            'completion_rate' => 0,
            'average_resolution_time' => 0,
            'total_reclamacoes_departamento' => 0,
            'tecnicos_ativos' => 0,
            'tecnicos_inativos' => 0,
            'tempo_medio_resolucao_departamento' => 0,
        ];
    }
    
    // Total de reclamações no departamento
    $totalReclamacoes = Grievance::whereHas('project', function ($query) use ($department) {
        $query->where('department_id', $department->id);
    })->count();
    
    // Reclamações pendentes no departamento
    $pendingReclamacoes = Grievance::whereHas('project', function ($query) use ($department) {
        $query->where('department_id', $department->id);
    })->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress', 'pending_approval'])->count();
    
    // Reclamações concluídas no departamento
    $completedReclamacoes = Grievance::whereHas('project', function ($query) use ($department) {
        $query->where('department_id', $department->id);
    })->whereIn('status', ['resolved', 'closed'])->count();
    
    // Reclamações canceladas no departamento
    $cancelledReclamacoes = Grievance::whereHas('project', function ($query) use ($department) {
        $query->where('department_id', $department->id);
    })->where('status', 'rejected')->count();
    
    // Reclamações em progresso no departamento
    $inProgressReclamacoes = Grievance::whereHas('project', function ($query) use ($department) {
        $query->where('department_id', $department->id);
    })->where('status', 'in_progress')->count();
    
    // Taxa de conclusão do departamento
    $completionRate = $totalReclamacoes > 0 
        ? round(($completedReclamacoes / $totalReclamacoes) * 100, 2)
        : 0;
    
    // Técnicos ativos e inativos no departamento
    $tecnicosAtivos = User::role('Técnico')
        ->where('is_available', true)
        ->whereHas('assignedGrievances', function ($query) use ($department) {
            $query->whereHas('project', function ($q) use ($department) {
                $q->where('department_id', $department->id);
            });
        })
        ->count();
    
    $tecnicosInativos = User::role('Técnico')
        ->where('is_available', false)
        ->whereHas('assignedGrievances', function ($query) use ($department) {
            $query->whereHas('project', function ($q) use ($department) {
                $q->where('department_id', $department->id);
            });
        })
        ->count();
    
    // Tempo médio de resolução do departamento
    $completedTasks = Grievance::whereHas('project', function ($query) use ($department) {
            $query->where('department_id', $department->id);
        })
        ->whereIn('status', ['resolved', 'closed'])
        ->whereNotNull('resolved_at')
        ->whereNotNull('submitted_at')
        ->get();
    
    $averageResolutionTime = 0;
    if ($completedTasks->count() > 0) {
        $totalHours = 0;
        foreach ($completedTasks as $task) {
            if ($task->submitted_at && $task->resolved_at) {
                $hours = $task->submitted_at->diffInHours($task->resolved_at);
                $totalHours += $hours;
            }
        }
        $averageResolutionTime = round($totalHours / $completedTasks->count(), 1);
    }
    
    return [
        'total_assigned' => $totalReclamacoes,
        'pending' => $pendingReclamacoes,
        'completed' => $completedReclamacoes,
        'cancelled' => $cancelledReclamacoes,
        'in_progress' => $inProgressReclamacoes,
        'completion_rate' => $completionRate,
        'average_resolution_time' => $averageResolutionTime,
        'total_reclamacoes_departamento' => $totalReclamacoes,
        'tecnicos_ativos' => $tecnicosAtivos,
        'tecnicos_inativos' => $tecnicosInativos,
        'tempo_medio_resolucao_departamento' => $averageResolutionTime,
    ];
}


    /**
     * Get technician statistics
     */
   /**
 * Get technician statistics
 */
public function getTechnicianStats($technicianId): array
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