<?php

namespace App\Http\Controllers;

use App\Models\Grievance;
use App\Models\User;
use App\Models\Indicator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use PDF;

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

     private function checkAccess($user)
    {
        if (!$user) {
            abort(403, 'Usuário não autenticado.');
        }
        
        if (!$user->hasRole('Gestor')) {
            abort(403, 'Acesso não autorizado. Apenas Gestores podem acessar esta página.');
        }
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

    // ============ NOVOS DADOS PARA INDICADORES ============
    
    // Obter estatísticas de resolução para indicadores
    $resolutionStats = $this->getResolutionStats($managedDepartment);
    
    // Obter performance dos técnicos
    $technicianPerformance = $this->getTechnicianPerformanceData($managedDepartment);
    
    // Obter distribuição por categoria
    $categoryDistribution = $this->getCategoryDistributionData($managedDepartment);
    
    // Obter linha temporal de resolução
    $resolutionTimeline = $this->getResolutionTimelineData($managedDepartment);
    
    // Obter indicadores básicos (estatísticas gerais)
    $indicators = $this->getBasicIndicators($managedDepartment);
    
    // Estatísticas para o dashboard
    $grievanceStats = $this->getGrievanceStats($managedDepartment);
    // ======================================================

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
        // ============ NOVAS PROPRIEDADES ============
        'grievanceStats' => $grievanceStats,
        'technicianPerformance' => $technicianPerformance,
        'categoryDistribution' => $categoryDistribution,
        'resolutionTimeline' => $resolutionTimeline,
        'indicators' => $indicators,
        // ============================================
    ]);
}



private function getResolutionStats($managedDepartment = null)
{
    $query = Grievance::query();
    
    if ($managedDepartment) {
        $query->whereHas('project', function ($q) use ($managedDepartment) {
            $q->where('department_id', $managedDepartment->id);
        });
    }
    
    $total = $query->count();
    $resolved = $query->whereIn('status', ['resolved', 'closed'])->count();
    $pending = $query->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])->count();
    $inProgress = $query->where('status', 'in_progress')->count();
    
    $resolutionRate = $total > 0 ? round(($resolved / $total) * 100, 1) : 0;
    
    // Calcular tempo médio de resolução
    $completedTasks = $query->whereIn('status', ['resolved', 'closed'])
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
    
    // Calcular tendências reais (comparando com mês anterior)
    $currentMonth = Carbon::now()->startOfMonth();
    $previousMonth = Carbon::now()->subMonth()->startOfMonth();
    
    // Taxa de resolução do mês atual
    $currentMonthResolved = (clone $query)->whereIn('status', ['resolved', 'closed'])
        ->whereMonth('resolved_at', $currentMonth->month)
        ->whereYear('resolved_at', $currentMonth->year)
        ->count();
    
    $currentMonthTotal = (clone $query)->whereMonth('created_at', $currentMonth->month)
        ->whereYear('created_at', $currentMonth->year)
        ->count();
    
    $currentMonthRate = $currentMonthTotal > 0 ? round(($currentMonthResolved / $currentMonthTotal) * 100, 1) : 0;
    
    // Taxa de resolução do mês anterior
    $previousMonthResolved = (clone $query)->whereIn('status', ['resolved', 'closed'])
        ->whereMonth('resolved_at', $previousMonth->month)
        ->whereYear('resolved_at', $previousMonth->year)
        ->count();
    
    $previousMonthTotal = (clone $query)->whereMonth('created_at', $previousMonth->month)
        ->whereYear('created_at', $previousMonth->year)
        ->count();
    
    $previousMonthRate = $previousMonthTotal > 0 ? round(($previousMonthResolved / $previousMonthTotal) * 100, 1) : 0;
    
    $resolutionRateTrend = $previousMonthRate > 0 ? round((($currentMonthRate - $previousMonthRate) / $previousMonthRate) * 100, 1) : 0;
    
    // Tendência do tempo de resolução
    $currentMonthTime = $this->getAverageResolutionTimeForMonth($currentMonth, $managedDepartment);
    $previousMonthTime = $this->getAverageResolutionTimeForMonth($previousMonth, $managedDepartment);
    
    $resolutionTimeTrend = $previousMonthTime > 0 ? round((($previousMonthTime - $currentMonthTime) / $previousMonthTime) * 100, 1) : 0;
    
    // Tendência de casos pendentes
    $currentMonthPending = (clone $query)->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])
        ->whereMonth('created_at', $currentMonth->month)
        ->whereYear('created_at', $currentMonth->year)
        ->count();
    
    $previousMonthPending = (clone $query)->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])
        ->whereMonth('created_at', $previousMonth->month)
        ->whereYear('created_at', $previousMonth->year)
        ->count();
    
    $pendingTrend = $previousMonthPending > 0 ? round((($currentMonthPending - $previousMonthPending) / $previousMonthPending) * 100, 1) : 0;
    
    return [
        'total' => $total,
        'resolved' => $resolved,
        'pending' => $pending,
        'in_progress' => $inProgress,
        'resolution_rate' => $resolutionRate,
        'avg_resolution_time' => $averageResolutionTime,
        'resolution_rate_trend' => $resolutionRateTrend,
        'resolution_time_trend' => $resolutionTimeTrend,
        'pending_trend' => $pendingTrend,
        'active_technicians' => $this->getActiveTechniciansCount($managedDepartment),
        'technicians_trend' => $this->calculateTechniciansTrend($managedDepartment),
    ];
}


private function getAverageResolutionTimeForMonth($month, $managedDepartment = null)
{
    $query = Grievance::query();
    
    if ($managedDepartment) {
        $query->whereHas('project', function ($q) use ($managedDepartment) {
            $q->where('department_id', $managedDepartment->id);
        });
    }
    
    $completedTasks = $query->whereIn('status', ['resolved', 'closed'])
        ->whereNotNull('resolved_at')
        ->whereNotNull('submitted_at')
        ->whereMonth('resolved_at', $month->month)
        ->whereYear('resolved_at', $month->year)
        ->get();
    
    if ($completedTasks->count() == 0) {
        return 0;
    }
    
    $totalHours = 0;
    foreach ($completedTasks as $task) {
        if ($task->submitted_at && $task->resolved_at) {
            $hours = $task->submitted_at->diffInHours($task->resolved_at);
            $totalHours += $hours;
        }
    }
    
    return round($totalHours / $completedTasks->count(), 1);
}



private function calculateTechniciansTrend($managedDepartment = null)
{
    $currentMonth = Carbon::now()->startOfMonth();
    $previousMonth = Carbon::now()->subMonth()->startOfMonth();
    
    $currentMonthActive = $this->getActiveTechniciansForMonth($currentMonth, $managedDepartment);
    $previousMonthActive = $this->getActiveTechniciansForMonth($previousMonth, $managedDepartment);
    
    if ($previousMonthActive == 0) {
        return $currentMonthActive > 0 ? 100 : 0;
    }
    
    return round((($currentMonthActive - $previousMonthActive) / $previousMonthActive) * 100, 1);
}

/**
 * Get active technicians count for specific month
 */
private function getActiveTechniciansForMonth($month, $managedDepartment = null)
{
    $query = User::role('Técnico')->where('is_available', true);
    
    if ($managedDepartment) {
        $query->whereHas('assignedGrievances', function ($q) use ($managedDepartment, $month) {
            $q->whereHas('project', function ($q2) use ($managedDepartment) {
                $q2->where('department_id', $managedDepartment->id);
            })
            ->whereMonth('created_at', $month->month)
            ->whereYear('created_at', $month->year);
        });
    } else {
        $query->whereHas('assignedGrievances', function ($q) use ($month) {
            $q->whereMonth('created_at', $month->month)
              ->whereYear('created_at', $month->year);
        });
    }
    
    return $query->count();
}

/**
 * Get basic indicators data
 */
private function getBasicIndicators($managedDepartment = null)
{
    $resolutionRate = $this->getResolutionRate($managedDepartment);
    $avgResolutionTime = $this->getAverageResolutionTime($managedDepartment);
    $pendingCases = $this->getPendingCasesCount($managedDepartment);
    
    return [
        [
            'id' => 1,
            'name' => 'Taxa de Resolução',
            'description' => 'Percentagem de reclamações resolvidas',
            'category' => 'performance',
            'current_value' => $resolutionRate,
            'target_value' => 80, // Meta padrão de 80%
            'measurement_unit' => 'percentage',
            'performance' => $this->calculatePerformance($resolutionRate, 80),
            'trend' => $this->calculateTrendForIndicator(1, $managedDepartment),
            'formatted_value' => number_format($resolutionRate, 1) . '%',
            'records' => $this->getHistoricalResolutionData($managedDepartment),
        ],
        [
            'id' => 2,
            'name' => 'Tempo Médio de Resolução',
            'description' => 'Tempo médio para resolver reclamações',
            'category' => 'efficiency',
            'current_value' => $avgResolutionTime,
            'target_value' => 48, // Meta: 48 horas = 2 dias
            'measurement_unit' => 'hours',
            'performance' => $this->calculatePerformance(48, $avgResolutionTime, true), // Inverso: menor é melhor
            'trend' => $this->calculateTrendForIndicator(2, $managedDepartment),
            'formatted_value' => number_format($avgResolutionTime, 1) . ' horas',
            'records' => $this->getHistoricalTimeData($managedDepartment),
        ],
        [
            'id' => 3,
            'name' => 'Satisfação do Cliente',
            'description' => 'Nível de satisfação reportado',
            'category' => 'satisfaction',
            'target_value' => 4.5, // Meta: 4.5/5
            'measurement_unit' => 'rating',
            'trend' => $this->calculateTrendForIndicator(3, $managedDepartment),
        ],
        [
            'id' => 4,
            'name' => 'Casos Pendentes',
            'description' => 'Reclamações aguardando resolução',
            'category' => 'efficiency',
            'current_value' => $pendingCases,
            'target_value' => 10, // Meta: máximo 10 casos pendentes
            'measurement_unit' => 'count',
            'performance' => $this->calculatePerformance(10, $pendingCases, true), // Inverso: menor é melhor
            'trend' => $this->calculateTrendForIndicator(4, $managedDepartment),
            'formatted_value' => $pendingCases,
            'records' => $this->getHistoricalPendingData($managedDepartment),
        ],
    ];
}



/**
 * Calculate trend for specific indicator
 */
private function calculateTrendForIndicator($indicatorId, $managedDepartment = null)
{
    $currentMonth = Carbon::now()->startOfMonth();
    $previousMonth = Carbon::now()->subMonth()->startOfMonth();
    
    switch ($indicatorId) {
        case 1: // Taxa de resolução
            $currentValue = $this->getResolutionRateForMonth($currentMonth, $managedDepartment);
            $previousValue = $this->getResolutionRateForMonth($previousMonth, $managedDepartment);
            break;
            
        case 2: // Tempo de resolução
            $currentValue = $this->getAverageResolutionTimeForMonth($currentMonth, $managedDepartment);
            $previousValue = $this->getAverageResolutionTimeForMonth($previousMonth, $managedDepartment);
            // Para tempo, menor é melhor, então invertemos a lógica
            return $previousValue > 0 ? round((($previousValue - $currentValue) / $previousValue) * 100, 1) : 0;
        
            
        case 3: // Casos pendentes
            $currentValue = $this->getPendingCasesForMonth($currentMonth, $managedDepartment);
            $previousValue = $this->getPendingCasesForMonth($previousMonth, $managedDepartment);
            // Para casos pendentes, menor é melhor, então invertemos
            return $previousValue > 0 ? round((($previousValue - $currentValue) / $previousValue) * 100, 1) : 0;
            
        default:
            return 0;
    }
    
    if ($previousValue == 0) {
        return $currentValue > 0 ? 100 : 0;
    }
    
    return round((($currentValue - $previousValue) / $previousValue) * 100, 1);
}

/**
 * Get resolution rate for specific month
 */
private function getResolutionRateForMonth($month, $managedDepartment = null)
{
    $query = Grievance::query();
    
    if ($managedDepartment) {
        $query->whereHas('project', function ($q) use ($managedDepartment) {
            $q->where('department_id', $managedDepartment->id);
        });
    }
    
    $total = $query->whereMonth('created_at', $month->month)
        ->whereYear('created_at', $month->year)
        ->count();
        
    $resolved = $query->whereIn('status', ['resolved', 'closed'])
        ->whereMonth('resolved_at', $month->month)
        ->whereYear('resolved_at', $month->year)
        ->count();
    
    return $total > 0 ? round(($resolved / $total) * 100, 1) : 0;
}


/**
 * Get pending cases for specific month
 */
private function getPendingCasesForMonth($month, $managedDepartment = null)
{
    $query = Grievance::query();
    
    if ($managedDepartment) {
        $query->whereHas('project', function ($q) use ($managedDepartment) {
            $q->where('department_id', $managedDepartment->id);
        });
    }
    
    return $query->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])
        ->whereMonth('created_at', $month->month)
        ->whereYear('created_at', $month->year)
        ->count();
}


/**
 * Get technician performance data
 */
private function getTechnicianPerformanceData($managedDepartment = null)
{
    \Log::info('getTechnicianPerformanceData chamado', [
        'has_department' => !is_null($managedDepartment)
    ]);
    
    try {
        $technicians = User::role('Técnico')
            ->select('id', 'name')
            ->get();
        
        \Log::info('Técnicos encontrados', ['count' => $technicians->count()]);
        
        return $technicians->map(function ($technician) use ($managedDepartment) {
            $query = Grievance::where('assigned_to', $technician->id);
            
            if ($managedDepartment) {
                $query->whereHas('project', function ($q) use ($managedDepartment) {
                    $q->where('department_id', $managedDepartment->id);
                });
            }
            
            $totalCases = $query->count();
            $resolvedCases = $query->whereIn('status', ['resolved', 'closed'])->count();
            $resolutionRate = $totalCases > 0 ? round(($resolvedCases / $totalCases) * 100, 1) : 0;
            
            return [
                'id' => $technician->id,
                'name' => $technician->name,
                'total_cases' => $totalCases,
                'resolved_cases' => $resolvedCases,
                'resolution_rate' => $resolutionRate,
                'avg_resolution_time' => 0, // Simplificado para teste
            ];
        })->filter(function ($tech) {
            return $tech['total_cases'] > 0;
        })->values()->toArray();
        
    } catch (\Exception $e) {
        \Log::error('Erro em getTechnicianPerformanceData: ' . $e->getMessage());
        return []; // Retornar array vazio em caso de erro
    }
}

/**
 * Get category distribution data
 */
private function getCategoryDistributionData($managedDepartment = null)
{
    $query = Grievance::query();
    
    if ($managedDepartment) {
        $query->whereHas('project', function ($q) use ($managedDepartment) {
            $q->where('department_id', $managedDepartment->id);
        });
    }
    
    $categories = $query->select('category', \DB::raw('COUNT(*) as count'))
        ->whereNotNull('category')
        ->groupBy('category')
        ->get();
    
    $total = $categories->sum('count');
    
    return $categories->map(function ($item) use ($total) {
        $percentage = $total > 0 ? round(($item->count / $total) * 100, 1) : 0;
        
        return [
            'category' => $item->category ?: 'Não Categorizado',
            'count' => $item->count,
            'percentage' => $percentage,
        ];
    })->toArray();
}



/**
 * Get grievance stats for dashboard
 */
private function getGrievanceStats($managedDepartment = null)
{
    $stats = $this->getResolutionStats($managedDepartment);
    
    return [
        'total' => $stats['total'],
        'resolved' => $stats['resolved'],
        'pending' => $stats['pending'],
        'resolution_rate' => $stats['resolution_rate'],
        'avg_resolution_time' => $stats['avg_resolution_time'],
        'resolution_rate_trend' => $stats['resolution_rate_trend'],
        'resolution_time_trend' => $stats['resolution_time_trend'],
        'pending_trend' => $stats['pending_trend'],
        'active_technicians' => $stats['active_technicians'],
        'technicians_trend' => $stats['technicians_trend'],
        'in_progress' => $stats['in_progress'],
    ];
}

/**
 * Helper methods for basic indicators
 */
private function getResolutionRate($managedDepartment = null)
{
    $query = Grievance::query();
    
    if ($managedDepartment) {
        $query->whereHas('project', function ($q) use ($managedDepartment) {
            $q->where('department_id', $managedDepartment->id);
        });
    }
    
    $total = $query->count();
    $resolved = $query->whereIn('status', ['resolved', 'closed'])->count();
    
    return $total > 0 ? round(($resolved / $total) * 100, 1) : 0;
}

private function getAverageResolutionTime($managedDepartment = null)
{
    $query = Grievance::query();
    
    if ($managedDepartment) {
        $query->whereHas('project', function ($q) use ($managedDepartment) {
            $q->where('department_id', $managedDepartment->id);
        });
    }
    
    $completedTasks = $query->whereIn('status', ['resolved', 'closed'])
        ->whereNotNull('resolved_at')
        ->whereNotNull('submitted_at')
        ->get();
    
    if ($completedTasks->count() == 0) {
        return 0;
    }
    
    $totalHours = 0;
    foreach ($completedTasks as $task) {
        if ($task->submitted_at && $task->resolved_at) {
            $hours = $task->submitted_at->diffInHours($task->resolved_at);
            $totalHours += $hours;
        }
    }
    
    return round($totalHours / $completedTasks->count(), 1);
}

private function getPendingCasesCount($managedDepartment = null)
{
    $query = Grievance::query();
    
    if ($managedDepartment) {
        $query->whereHas('project', function ($q) use ($managedDepartment) {
            $q->where('department_id', $managedDepartment->id);
        });
    }
    
    return $query->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])->count();
}

private function getActiveTechniciansCount($managedDepartment = null)
{
    $query = User::role('Técnico')->where('is_available', true);
    
    if ($managedDepartment) {
        $query->whereHas('assignedGrievances', function ($q) use ($managedDepartment) {
            $q->whereHas('project', function ($q2) use ($managedDepartment) {
                $q2->where('department_id', $managedDepartment->id);
            });
        });
    }
    
    return $query->count();
}

private function calculatePerformance($current, $target, $inverse = false)
{
    if ($target == 0) return 0;
    
    $performance = ($current / $target) * 100;
    
    // Para métricas onde menor é melhor (como tempo de resolução)
    if ($inverse) {
        $performance = ($target / $current) * 100;
    }
    
    return min(round($performance, 1), 100); // Máximo 100%
}

private function getHistoricalResolutionData($managedDepartment = null)
{
    $now = Carbon::now();
    $data = [];
    
    for ($i = 6; $i >= 0; $i--) {
        $date = $now->copy()->subMonths($i);
        $query = Grievance::query();
        
        if ($managedDepartment) {
            $query->whereHas('project', function ($q) use ($managedDepartment) {
                $q->where('department_id', $managedDepartment->id);
            });
        }
        
        $total = $query->whereMonth('submitted_at', $date->month)
            ->whereYear('submitted_at', $date->year)
            ->count();
            
        $resolved = $query->whereIn('status', ['resolved', 'closed'])
            ->whereMonth('resolved_at', $date->month)
            ->whereYear('resolved_at', $date->year)
            ->count();
            
        $rate = $total > 0 ? round(($resolved / $total) * 100, 1) : 0;
        
        $data[] = [
            'date' => $date->format('Y-m'),
            'value' => $rate,
        ];
    }
    
    return $data;
}

private function getHistoricalTimeData($managedDepartment = null)
{
    $now = Carbon::now();
    $data = [];
    
    for ($i = 6; $i >= 0; $i--) {
        $date = $now->copy()->subMonths($i);
        $query = Grievance::query();
        
        if ($managedDepartment) {
            $query->whereHas('project', function ($q) use ($managedDepartment) {
                $q->where('department_id', $managedDepartment->id);
            });
        }
        
        $completedTasks = $query->whereIn('status', ['resolved', 'closed'])
            ->whereNotNull('resolved_at')
            ->whereNotNull('submitted_at')
            ->whereMonth('resolved_at', $date->month)
            ->whereYear('resolved_at', $date->year)
            ->get();
            
        if ($completedTasks->count() == 0) {
            $data[] = [
                'date' => $date->format('Y-m'),
                'value' => 0,
            ];
            continue;
        }
        
        $totalHours = 0;
        foreach ($completedTasks as $task) {
            if ($task->submitted_at && $task->resolved_at) {
                $hours = $task->submitted_at->diffInHours($task->resolved_at);
                $totalHours += $hours;
            }
        }
        
        $data[] = [
            'date' => $date->format('Y-m'),
            'value' => round($totalHours / $completedTasks->count(), 1),
        ];
    }
    
    return $data;
}


private function getHistoricalPendingData($managedDepartment = null)
{
    $now = Carbon::now();
    $data = [];
    
    for ($i = 6; $i >= 0; $i--) {
        $date = $now->copy()->subMonths($i);
        $query = Grievance::query();
        
        if ($managedDepartment) {
            $query->whereHas('project', function ($q) use ($managedDepartment) {
                $q->where('department_id', $managedDepartment->id);
            });
        }
        
        $pending = $query->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])
            ->whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->count();
            
        $data[] = [
            'date' => $date->format('Y-m'),
            'value' => $pending,
        ];
    }
    
    return $data;
}



 /* Display all technicians AND managers (for directors)
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

    /**
     * Export employees to PDF
     */
    public function exportEmployeesToPdf(Request $request)
    {
        $user = $request->user();
        $this->checkAccess($user);

        // Obter filtros - como gestor, só ve TÉCNICOS
        $search = $request->query('search', '');
        $province = $request->query('province', '');
        $status = $request->query('status', '');
        
        // Query para dados - APENAS TÉCNICOS (gestor não vê outros gestores)
        $employeesQuery = User::query()
            ->whereHas('roles', function ($query) {
                // Gestor só vê Técnicos
                $query->where('name', 'Técnico');
            })
            ->with(['roles'])
            ->orderBy('name');

        // Aplicar filtros
        if ($search) {
            $employeesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($province) {
            $employeesQuery->where('province', $province);
        }

        if ($status === 'active') {
            $employeesQuery->where('status', 'active');
        } elseif ($status === 'inactive') {
            $employeesQuery->where('status', 'inactive');
        }

        // Obter dados
        $employees = $employeesQuery->get()->map(function ($employee) {
            $roleName = $employee->roles->first()->name ?? 'Técnico';
            $stats = $this->getEmployeeStats($employee);
            
            return [
                'name' => $employee->name ?? 'N/A',
                'username' => $employee->username ?? 'N/A',
                'email' => $employee->email ?? 'N/A',
                'phone' => $employee->phone ?: '--',
                'province' => $employee->province ?: '--',
                'district' => $employee->district ?: '--',
                'neighborhood' => $employee->neighborhood ?: '--',
                'role' => $roleName === 'Técnico' ? 'technician' : 'technician',
                'status' => $employee->status ?? 'active',
                'tasks_assigned' => $stats['total_assigned'],
                'tasks_pending' => $stats['pending'],
                'tasks_completed' => $stats['completed'],
                'tasks_in_progress' => $stats['in_progress'],
                'tasks_cancelled' => $stats['cancelled'],
                'completion_rate' => $stats['completion_rate'],
                'average_resolution_time' => $stats['average_resolution_time']
            ];
        });

        $fileName = 'relatorio-tecnicos-' . now()->format('Y-m-d-H-i') . '.pdf';
        
        // Gerar PDF
        $pdf = \PDF::loadView('exports.employees', [
            'employees' => $employees,
            'user' => $user,
            'generated_by' => $user->name,
        ]);

        $pdf->setPaper('A3', 'landscape')
            ->setOptions([
                'defaultFont' => 'dejavusanscondensed',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'dpi' => 120,
                'margin_top' => 10,
                'margin_right' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10,
                'fontHeightRatio' => 0.8,
            ]);

        return $pdf->download($fileName);
    }

  /**
 * Export indicators to PDF
 */
/**
 * Export indicators to PDF - VERSÃO CORRIGIDA PARA WINDOWS
 */
public function exportIndicatorsToPdf(Request $request)
{
    $user = $request->user();
    
    if (!$user || !$user->hasRole('Gestor')) {
        \Log::error('Acesso negado para exportar PDF', [
            'user_id' => $user ? $user->id : 'null',
            'roles' => $user ? $user->getRoleNames() : []
        ]);
        
        abort(403, 'Acesso não autorizado. Apenas Gestores podem exportar PDF.');
    }

    \Log::info('=== INICIANDO EXPORTAÇÃO PDF FUNCIONAL ===', [
        'user_id' => $user->id,
        'user_name' => $user->name,
        'params' => $request->all()
    ]);

    // Obter departamento do gestor
    $managedDepartment = \App\Models\Department::where('manager_id', $user->id)->first();
    
    try {
        // Obter dados do dashboard (usando os mesmos métodos do __invoke)
        \Log::info('Obtendo dados para PDF...');
        
        $indicators = $this->getBasicIndicators($managedDepartment);
        $grievanceStats = $this->getGrievanceStats($managedDepartment);
        $technicianPerformance = $this->getTechnicianPerformanceData($managedDepartment);
        $categoryDistribution = $this->getCategoryDistributionData($managedDepartment);
        $resolutionTimeline = $this->getResolutionTimelineData($managedDepartment);
        
        \Log::info('Dados obtidos:', [
            'indicators' => count($indicators),
            'technicians' => count($technicianPerformance),
            'categories' => count($categoryDistribution),
            'timeline' => count($resolutionTimeline)
        ]);

        // Preparar dados para a view
        $data = [
            'generatedBy' => $user->name,
            'date' => Carbon::now()->format('d/m/Y'),
            'time' => Carbon::now()->format('H:i'),
            'period' => $this->getPeriodLabel($request->input('time_range', 'month')),
            'department' => $managedDepartment ? $managedDepartment->name : 'Gestor Geral',
            'totalPages' => 1,
            
            // Dados reais
            'indicators' => $this->formatForPdf($indicators, 'indicators'),
            'technicianPerformance' => $this->formatForPdf($technicianPerformance, 'technicians'),
            'categoryDistribution' => $this->formatForPdf($categoryDistribution, 'categories'),
            'resolutionTimeline' => $this->formatForPdf($resolutionTimeline, 'timeline'),
            'grievanceStats' => $grievanceStats,
            'summaryStats' => $this->getSummaryStatsForExport($indicators, $technicianPerformance, $grievanceStats),
            'quickStats' => $this->getQuickStatsForExport($grievanceStats, $indicators),
        ];

        \Log::info('Gerando PDF com dados formatados...');

        // Gerar PDF com configuração CORRETA para Windows
        $pdf = PDF::loadView('exports.indicators', $data)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'defaultFont' => 'helvetica', // Usar fonte que EXISTE
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'dpi' => 96,
                'margin_top' => 15,
                'margin_right' => 15,
                'margin_bottom' => 15,
                'margin_left' => 15,
                'fontDir' => storage_path('fonts'),
                'fontCache' => storage_path('fonts'),
                'enable_font_subsetting' => false, // IMPORTANTE: desabilitar no Windows
                'enable_unicode' => true,
            ]);

        $fileName = 'dashboard-indicadores-' . Carbon::now()->format('Y-m-d-H-i') . '.pdf';
        
        \Log::info('PDF gerado com sucesso!', [
            'filename' => $fileName,
            'user' => $user->name
        ]);

        return $pdf->download($fileName);
        
    } catch (\Throwable $e) {
        \Log::error('ERRO ao gerar PDF: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'user_id' => $user->id
        ]);
        
        // Em desenvolvimento, mostrar erro
        if (app()->environment('local')) {
            return response()->json([
                'error' => 'Erro ao gerar PDF: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
        
        // Em produção, gerar PDF de erro
        return $this->generateErrorPdf($user, 'Ocorreu um erro ao gerar o relatório.');
    }
}

private function formatForPdf($data, $type)
{
    if (empty($data)) {
        return [];
    }
    
    switch ($type) {
        case 'indicators':
            return array_map(function ($indicator) {
                return [
                    'id' => $indicator['id'] ?? null,
                    'name' => $indicator['name'] ?? 'Indicador',
                    'description' => $indicator['description'] ?? '',
                    'category' => $indicator['category'] ?? 'performance',
                    'current_value' => $indicator['current_value'] ?? 0,
                    'target_value' => $indicator['target_value'] ?? 0,
                    'measurement_unit' => $indicator['measurement_unit'] ?? 'percentage',
                    'performance' => $indicator['performance'] ?? 0,
                    'trend' => $indicator['trend'] ?? 0,
                    'formatted_value' => $indicator['formatted_value'] ?? '0',
                ];
            }, $data);
            
        case 'technicians':
            return array_map(function ($tech) {
                return [
                    'id' => $tech['id'] ?? null,
                    'name' => $tech['name'] ?? 'Técnico',
                    'total_cases' => $tech['total_cases'] ?? 0,
                    'resolved_cases' => $tech['resolved_cases'] ?? 0,
                    'resolution_rate' => $tech['resolution_rate'] ?? 0,
                    'avg_resolution_time' => $tech['avg_resolution_time'] ?? 0,
                ];
            }, $data);
            
        case 'categories':
            return array_map(function ($cat) {
                return [
                    'category' => $cat['category'] ?? 'Não Categorizado',
                    'count' => $cat['count'] ?? 0,
                    'percentage' => $cat['percentage'] ?? 0,
                ];
            }, $data);
            
        case 'timeline':
            return array_map(function ($item) {
                return [
                    'period' => $item['period'] ?? '',
                    'date' => $item['date'] ?? '',
                    'submitted' => $item['submitted'] ?? 0,
                    'resolved' => $item['resolved'] ?? 0,
                    'pending' => $item['pending'] ?? 0,
                    'trend' => $item['trend'] ?? 0,
                ];
            }, $data);
            
        default:
            return $data;
    }
}

/**
 * Configurar PDF para Windows
 */
private function configurePdfForWindows()
{
    // Normalizar caminhos para Windows
    $fontDir = str_replace('\\', '/', storage_path('fonts'));
    $tempDir = str_replace('\\', '/', sys_get_temp_dir());
    
    // Garantir diretórios
    if (!file_exists($fontDir)) {
        mkdir($fontDir, 0755, true);
    }
    
    // Definir constantes do DOMPDF
    if (!defined('DOMPDF_FONT_DIR')) {
        define('DOMPDF_FONT_DIR', $fontDir);
    }
    
    if (!defined('DOMPDF_FONT_CACHE')) {
        define('DOMPDF_FONT_CACHE', $fontDir);
    }
    
    if (!defined('DOMPDF_TEMP_DIR')) {
        define('DOMPDF_TEMP_DIR', $tempDir);
    }
    
    if (!defined('DOMPDF_CHROOT')) {
        define('DOMPDF_CHROOT', str_replace('\\', '/', realpath(base_path())));
    }
    
    \Log::info('Configuração do PDF para Windows:', [
        'font_dir' => DOMPDF_FONT_DIR,
        'temp_dir' => DOMPDF_TEMP_DIR,
        'chroot' => DOMPDF_CHROOT
    ]);
}

/**
 * Gerar dados simples para PDF (para teste)
 */
private function getSimpleDataForPdf($user, $managedDepartment)
{
    // Dados básicos para teste - SEM caracteres especiais
    return [
        'generatedBy' => $user->name,
        'date' => Carbon::now()->format('d/m/Y'),
        'time' => Carbon::now()->format('H:i'),
        'period' => 'Último Mês',
        'department' => $managedDepartment ? $managedDepartment->name : 'Gestor Geral',
        'totalPages' => 1,
        
        // Dados simples - evitar "N/A" e caracteres especiais
        'indicators' => [
            [
                'name' => 'Taxa de Resolução',
                'description' => 'Percentagem de reclamações resolvidas',
                'formatted_value' => '75.5%',
                'performance' => 94.4,
                'trend' => 5.2,
            ],
            [
                'name' => 'Tempo Médio de Resolução',
                'description' => 'Tempo medio para resolver reclamações',
                'formatted_value' => '36.2 horas',
                'performance' => 132.6,
                'trend' => -10.5,
            ]
        ],
        
        'technicianPerformance' => [
            [
                'name' => 'Joao Silva',
                'total_cases' => 24,
                'resolved_cases' => 20,
                'resolution_rate' => 83.3,
                'avg_resolution_time' => 28.5,
            ]
        ],
        
        'grievanceStats' => [
            'total' => 156,
            'resolved' => 124,
            'pending' => 32,
            'in_progress' => 18,
            'resolution_rate' => 79.5,
            'avg_resolution_time' => 34.8,
        ],
        
        'summaryStats' => [
            [
                'title' => 'Total Indicadores',
                'value' => '4',
                'trend' => 'up',
            ],
            [
                'title' => 'Taxa de Resolução',
                'value' => '79.5%',
                'trend' => 'up',
            ],
            [
                'title' => 'Tempo Medio',
                'value' => '34.8 horas',
                'trend' => 'down',
            ],
            [
                'title' => 'Técnicos Activos',
                'value' => '8',
                'trend' => 'stable',
            ],
        ],
        
        'quickStats' => [
            ['label' => 'Total de Reclamações', 'value' => '156'],
            ['label' => 'Resolvidas', 'value' => '124'],
            ['label' => 'Em Andamento', 'value' => '32'],
            ['label' => 'Em Progresso', 'value' => '18'],
            ['label' => 'Técnicos Activos', 'value' => '8'],
            ['label' => 'Indicadores', 'value' => '4'],
        ],
    ];
}

public function debugPdfData(Request $request)
{
    $user = $request->user();
    
    if (!$user || !$user->hasRole('Gestor')) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $managedDepartment = \App\Models\Department::where('manager_id', $user->id)->first();
    
    try {
        // Obter dados reais
        $grievanceStats = $this->getGrievanceStats($managedDepartment);
        $indicators = $this->getBasicIndicators($managedDepartment);
        $technicianPerformance = $this->getTechnicianPerformanceData($managedDepartment);
        $categoryDistribution = $this->getCategoryDistributionData($managedDepartment);
        
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'has_department' => !is_null($managedDepartment)
            ],
            'department' => $managedDepartment ? [
                'id' => $managedDepartment->id,
                'name' => $managedDepartment->name
            ] : null,
            'data_summary' => [
                'grievanceStats' => [
                    'total' => $grievanceStats['total'] ?? 0,
                    'resolved' => $grievanceStats['resolved'] ?? 0,
                    'has_data' => !empty($grievanceStats)
                ],
                'indicators_count' => count($indicators),
                'indicators_sample' => array_slice($indicators, 0, 2),
                'technicians_count' => count($technicianPerformance),
                'categories_count' => count($categoryDistribution)
            ],
            'methods_called' => [
                'getGrievanceStats' => !empty($grievanceStats),
                'getBasicIndicators' => !empty($indicators),
                'getTechnicianPerformanceData' => !empty($technicianPerformance),
                'getCategoryDistributionData' => !empty($categoryDistribution)
            ]
        ]);
        
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
}



private function formatIndicatorsForPdf($indicators): array
{
    return array_map(function ($indicator) {
        return [
            'id' => $indicator['id'] ?? null,
            'name' => $indicator['name'] ?? 'Indicador',
            'description' => $indicator['description'] ?? '',
            'category' => $indicator['category'] ?? 'performance',
            'current_value' => $indicator['current_value'] ?? 0,
            'target_value' => $indicator['target_value'] ?? 0,
            'measurement_unit' => $indicator['measurement_unit'] ?? 'percentage',
            'performance' => $indicator['performance'] ?? 0,
            'trend' => $indicator['trend'] ?? 0,
            'formatted_value' => $indicator['formatted_value'] ?? '0',
        ];
    }, $indicators);
}

/**
 * Format technicians for PDF export
 */
private function formatTechniciansForPdf($technicians): array
{
    return array_map(function ($technician) {
        return [
            'id' => $technician['id'] ?? null,
            'name' => $technician['name'] ?? 'Técnico',
            'total_cases' => $technician['total_cases'] ?? 0,
            'resolved_cases' => $technician['resolved_cases'] ?? 0,
            'resolution_rate' => $technician['resolution_rate'] ?? 0,
            'avg_resolution_time' => $technician['avg_resolution_time'] ?? 0,
        ];
    }, $technicians);
}

/**
 * Format categories for PDF export
 */
private function formatCategoriesForPdf($categories): array
{
    return array_map(function ($category) {
        return [
            'category' => $category['category'] ?? 'Não Categorizado',
            'count' => $category['count'] ?? 0,
            'percentage' => $category['percentage'] ?? 0,
        ];
    }, $categories);
}

/**
 * Format timeline for PDF export
 */
private function formatTimelineForPdf($timeline): array
{
    return array_map(function ($item) {
        return [
            'period' => $item['period'] ?? '',
            'date' => $item['date'] ?? '',
            'submitted' => $item['submitted'] ?? 0,
            'resolved' => $item['resolved'] ?? 0,
            'pending' => $item['pending'] ?? 0,
            'trend' => $item['trend'] ?? 0,
        ];
    }, $timeline);
}


private function getSummaryStatsForPdf($indicators, $grievanceStats): array
{
    $totalIndicators = count($indicators);
    $activeIndicators = count(array_filter($indicators, function ($indicator) {
        return isset($indicator['current_value']) && $indicator['current_value'] > 0;
    }));
    
    return [
        [
            'title' => 'Total Indicadores',
            'value' => $totalIndicators,
            'trend' => 'up',
            'icon' => '📊',
        ],
        [
            'title' => 'Taxa de Resolução',
            'value' => number_format($grievanceStats['resolution_rate'] ?? 0, 1) . '%',
            'trend' => isset($grievanceStats['resolution_rate_trend']) && $grievanceStats['resolution_rate_trend'] > 0 ? 'up' : 
                      (isset($grievanceStats['resolution_rate_trend']) && $grievanceStats['resolution_rate_trend'] < 0 ? 'down' : 'stable'),
            'icon' => '✅',
        ],
        [
            'title' => 'Tempo Médio',
            'value' => number_format($grievanceStats['avg_resolution_time'] ?? 0, 1) . ' horas',
            'trend' => isset($grievanceStats['resolution_time_trend']) && $grievanceStats['resolution_time_trend'] < 0 ? 'up' : 
                      (isset($grievanceStats['resolution_time_trend']) && $grievanceStats['resolution_time_trend'] > 0 ? 'down' : 'stable'),
            'icon' => '⏱️',
        ],
        [
            'title' => 'Técnicos Activos',
            'value' => $grievanceStats['active_technicians'] ?? 0,
            'trend' => isset($grievanceStats['technicians_trend']) && $grievanceStats['technicians_trend'] > 0 ? 'up' : 
                      (isset($grievanceStats['technicians_trend']) && $grievanceStats['technicians_trend'] < 0 ? 'down' : 'stable'),
            'icon' => '👥',
        ],
    ];
}

/**
 * Get quick stats for PDF
 */
private function getQuickStatsForPdf($grievanceStats, $indicators): array
{
    return [
        ['label' => 'Total de Reclamações', 'value' => $grievanceStats['total'] ?? 0, 'icon' => '📋'],
        ['label' => 'Resolvidas', 'value' => $grievanceStats['resolved'] ?? 0, 'icon' => '✅'],
        ['label' => 'Em Andamento', 'value' => $grievanceStats['pending'] ?? 0, 'icon' => '🔄'],
        ['label' => 'Em Progresso', 'value' => $grievanceStats['in_progress'] ?? 0, 'icon' => '⚡'],
        ['label' => 'Técnicos Activos', 'value' => $grievanceStats['active_technicians'] ?? 0, 'icon' => '👨‍💻'],
        ['label' => 'Indicadores', 'value' => count($indicators), 'icon' => '📈'],
    ];
}


private function getAllDashboardDataForExport(
    $managedDepartment = null,
    Request $request = null
) {
    \Log::info('getAllDashboardDataForExport chamado', [
        'has_department' => !is_null($managedDepartment),
        'department_id' => $managedDepartment ? $managedDepartment->id : null,
        'department_name' => $managedDepartment ? $managedDepartment->name : null
    ]);

    try {
        // 1. Indicadores básicos - funciona sem departamento
        $indicators = $this->getBasicIndicators($managedDepartment);
        
        \Log::info('Indicadores obtidos', ['count' => count($indicators)]);

        // 2. Filtro por categoria
        $categoryFilter = $request?->input('category', 'all') ?? 'all';
        if ($categoryFilter !== 'all') {
            $indicators = array_values(array_filter(
                $indicators,
                fn ($indicator) =>
                    isset($indicator['category']) &&
                    strtolower($indicator['category']) === strtolower($categoryFilter)
            ));
        }

        // 3. Performance dos técnicos - funciona sem departamento
        $technicianPerformance = $this->getTechnicianPerformanceData($managedDepartment);
        
        \Log::info('Performance dos técnicos obtida', ['count' => count($technicianPerformance)]);

        // 4. Distribuição por categoria - funciona sem departamento
        $categoryDistribution = $this->getCategoryDistributionData($managedDepartment);
        
        \Log::info('Distribuição por categoria obtida', ['count' => count($categoryDistribution)]);

        // 5. Timeline - funciona sem departamento
        $timeRange = $request?->input('time_range', 'month') ?? 'month';
        $resolutionTimeline = $this->getResolutionTimelineData($managedDepartment, $timeRange);
        
        \Log::info('Timeline de resolução obtida', [
            'count' => count($resolutionTimeline),
            'time_range' => $timeRange
        ]);

        // 6. Estatísticas - funciona sem departamento
        $grievanceStats = $this->getGrievanceStats($managedDepartment);
        
        \Log::info('Estatísticas obtidas', ['has_data' => !empty($grievanceStats)]);

        $summaryStats = $this->getSummaryStatsForExport(
            $indicators,
            $technicianPerformance,
            $grievanceStats
        );

        $quickStats = $this->getQuickStatsForExport(
            $grievanceStats,
            $indicators
        );

        \Log::info('Todos os dados obtidos com sucesso');

        return compact(
            'indicators',
            'technicianPerformance',
            'categoryDistribution',
            'resolutionTimeline',
            'summaryStats',
            'quickStats',
            'grievanceStats'
        );
        
    } catch (\Exception $e) {
        \Log::error('Erro em getAllDashboardDataForExport: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'department' => $managedDepartment ? $managedDepartment->id : 'null'
        ]);
        
        throw $e; // Re-lançar para ser capturado no método principal
    }
}


/**
 * Ensure PDF directories exist
 */
private function ensurePdfDirectories()
{
    $directories = [
        storage_path('fonts'),
        storage_path('app/temp'),
    ];
    
    foreach ($directories as $directory) {
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
    }
}

// No Controller, adicione este método:
public function debugPdfRequest(Request $request)
{
    $user = $request->user();
    
    abort_if(!$user || !$user->hasRole('Gestor'), 403);

    $managedDepartment = \App\Models\Department::where('manager_id', $user->id)->first();
    
    \Log::info('Debug PDF Request', [
        'user_id' => $user->id,
        'department' => $managedDepartment ? $managedDepartment->id : null,
        'request_params' => $request->all(),
        'input_data' => $request->input(),
        'query_params' => $request->query()
    ]);

    // Simular o que acontece no método real
    try {
        $data = $this->getAllDashboardDataForExport($managedDepartment, $request);
        
        return response()->json([
            'success' => true,
            'data_summary' => [
                'indicators_count' => count($data['indicators']),
                'technicians_count' => count($data['technicianPerformance']),
                'has_data' => !empty($data['indicators']) || !empty($data['technicianPerformance'])
            ],
            'department' => $managedDepartment ? [
                'id' => $managedDepartment->id,
                'name' => $managedDepartment->name
            ] : null,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'has_manager_role' => $user->hasRole('Gestor')
            ],
            'request_info' => [
                'time_range' => $request->input('time_range', 'month'),
                'category' => $request->input('category', 'all'),
                'date_range' => $request->input('date_range', []),
                'full_url' => $request->fullUrl()
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
}


public function debugExport(Request $request)
{
    $user = $request->user();
    
    if (!$user || !$user->hasRole('Gestor')) {
        return response()->json([
            'error' => 'Acesso não autorizado',
            'user_id' => $user ? $user->id : 'null'
        ], 403);
    }

    $managedDepartment = \App\Models\Department::where('manager_id', $user->id)->first();
    
    // Simular dados simples
    $data = [
        'generatedBy' => $user->name,
        'date' => Carbon::now()->format('d/m/Y'),
        'time' => Carbon::now()->format('H:i'),
        'department' => $managedDepartment ? $managedDepartment->name : 'Gestor Geral',
        'indicators' => [
            [
                'name' => 'Taxa de Resolução',
                'value' => '75.5%',
                'performance' => 94.4
            ]
        ]
    ];

    try {
        // Testar view primeiro
        $viewContent = view('exports.simple_test', $data)->render();
        
        // Testar PDF com configuração mínima
        $pdf = PDF::loadView('exports.simple_test', $data)
            ->setPaper('A4')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => false,
                'dpi' => 96,
            ]);
        
        $pdfContent = $pdf->output();
        
        return response()->json([
            'success' => true,
            'view_rendered' => !empty($viewContent),
            'pdf_generated' => !empty($pdfContent),
            'pdf_size' => strlen($pdfContent),
            'department' => $managedDepartment ? $managedDepartment->name : null,
            'config' => [
                'env' => app()->environment(),
                'debug' => config('app.debug'),
            ]
        ]);
        
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => app()->environment('local') ? $e->getTraceAsString() : null,
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ], 500);
    }
}

private function ensureCompleteData($data)
{
    // Se não há dados, criar estrutura vazia
    if (empty($data)) {
        $data = [];
    }
    
    // Garantir que todos os arrays existem
    $arrays = ['indicators', 'technicianPerformance', 'categoryDistribution', 
               'resolutionTimeline', 'summaryStats', 'quickStats', 'grievanceStats'];
    
    foreach ($arrays as $array) {
        if (!isset($data[$array]) || !is_array($data[$array])) {
            $data[$array] = [];
        }
    }
    
    // Garantir summaryStats mínimo se vazio
    if (empty($data['summaryStats'])) {
        $data['summaryStats'] = [
            [
                'title' => 'Total Indicadores',
                'value' => count($data['indicators']),
                'trend' => 'stable',
            ],
            [
                'title' => 'Taxa de Resolução',
                'value' => isset($data['grievanceStats']['resolution_rate']) 
                    ? number_format($data['grievanceStats']['resolution_rate'], 1) . '%' 
                    : '0.0%',
                'trend' => 'stable',
            ],
            [
                'title' => 'Tempo Médio',
                'value' => isset($data['grievanceStats']['avg_resolution_time']) 
                    ? number_format($data['grievanceStats']['avg_resolution_time'], 1) . ' horas' 
                    : '0.0 horas',
                'trend' => 'stable',
            ],
            [
                'title' => 'Técnicos Activos',
                'value' => isset($data['grievanceStats']['active_technicians']) 
                    ? $data['grievanceStats']['active_technicians'] 
                    : 0,
                'trend' => 'stable',
            ],
        ];
    }
    
    // Garantir grievanceStats mínimo se vazio
    if (empty($data['grievanceStats']) || !is_array($data['grievanceStats'])) {
        $data['grievanceStats'] = [
            'total' => 0,
            'resolved' => 0,
            'pending' => 0,
            'resolution_rate' => 0,
            'avg_resolution_time' => 0,
            'resolution_rate_trend' => 0,
            'resolution_time_trend' => 0,
            'pending_trend' => 0,
            'active_technicians' => 0,
            'technicians_trend' => 0,
            'in_progress' => 0,
        ];
    }
    
    // Garantir quickStats mínimo se vazio
    if (empty($data['quickStats'])) {
        $data['quickStats'] = [
            ['label' => 'Total de Reclamações', 'value' => $data['grievanceStats']['total'] ?? 0],
            ['label' => 'Resolvidas', 'value' => $data['grievanceStats']['resolved'] ?? 0],
            ['label' => 'Em Andamento', 'value' => $data['grievanceStats']['pending'] ?? 0],
            ['label' => 'Em Progresso', 'value' => $data['grievanceStats']['in_progress'] ?? 0],
            ['label' => 'Técnicos Activos', 'value' => $data['grievanceStats']['active_technicians'] ?? 0],
            ['label' => 'Indicadores Ativos', 'value' => count($data['indicators'] ?? [])],
        ];
    }
    
    return $data;
}
/**
 * Get resolution timeline with time filter
 */
private function getResolutionTimelineData($managedDepartment = null, $timeRange = 'month')
{
    $now = Carbon::now();
    $timeline = [];
    
    switch ($timeRange) {
        case 'week':
            // Últimos 7 dias
            $periods = 7;
            for ($i = $periods - 1; $i >= 0; $i--) {
                $date = $now->copy()->subDays($i);
                $data = $this->getResolutionForDate($date, $managedDepartment, 'day');
                $timeline[] = $data;
            }
            break;
            
        case 'month':
            // Últimas 4 semanas
            $periods = 4;
            for ($i = $periods - 1; $i >= 0; $i--) {
                $start = $now->copy()->subWeeks($i)->startOfWeek();
                $end = $now->copy()->subWeeks($i)->endOfWeek();
                $data = $this->getResolutionForPeriod($start, $end, $managedDepartment, 'week', $i);
                $timeline[] = $data;
            }
            break;
            
        case 'quarter':
            // Últimos 3 meses
            $periods = 3;
            for ($i = $periods - 1; $i >= 0; $i--) {
                $month = $now->copy()->subMonths($i);
                $data = $this->getResolutionForMonth($month, $managedDepartment);
                $timeline[] = $data;
            }
            break;
            
        default:
            // Últimos 6 meses (padrão)
            $periods = 6;
            for ($i = $periods - 1; $i >= 0; $i--) {
                $month = $now->copy()->subMonths($i);
                $data = $this->getResolutionForMonth($month, $managedDepartment);
                $timeline[] = $data;
            }
    }
    
    return $timeline;
}

/**
 * Get resolution for specific month
 */
private function getResolutionForMonth($month, $managedDepartment = null)
{
    $startDate = $month->copy()->startOfMonth();
    $endDate = $month->copy()->endOfMonth();
    
    $submittedQuery = Grievance::query();
    $resolvedQuery = Grievance::query();
    
    if ($managedDepartment) {
        $submittedQuery->whereHas('project', function ($q) use ($managedDepartment) {
            $q->where('department_id', $managedDepartment->id);
        });
        $resolvedQuery->whereHas('project', function ($q) use ($managedDepartment) {
            $q->where('department_id', $managedDepartment->id);
        });
    }
    
    $submitted = $submittedQuery->whereBetween('submitted_at', [$startDate, $endDate])->count();
    $resolved = $resolvedQuery->whereIn('status', ['resolved', 'closed'])
        ->whereBetween('resolved_at', [$startDate, $endDate])
        ->count();
    
    $pending = $submitted - $resolved;
    
    // Calcular tendência (simplificado)
    $trend = $this->calculateTrendForTimeline($submitted, $resolved);
    
    return [
        'period' => $month->format('M/Y'),
        'date' => $month->format('M/Y'),
        'submitted' => $submitted,
        'resolved' => $resolved,
        'pending' => $pending > 0 ? $pending : 0,
        'trend' => $trend,
    ];
}

/**
 * Get resolution for specific period
 */
private function getResolutionForPeriod($start, $end, $managedDepartment = null, $type = 'week', $index = 0)
{
    $submittedQuery = Grievance::query();
    $resolvedQuery = Grievance::query();
    
    if ($managedDepartment) {
        $submittedQuery->whereHas('project', function ($q) use ($managedDepartment) {
            $q->where('department_id', $managedDepartment->id);
        });
        $resolvedQuery->whereHas('project', function ($q) use ($managedDepartment) {
            $q->where('department_id', $managedDepartment->id);
        });
    }
    
    $submitted = $submittedQuery->whereBetween('submitted_at', [$start, $end])->count();
    $resolved = $resolvedQuery->whereIn('status', ['resolved', 'closed'])
        ->whereBetween('resolved_at', [$start, $end])
        ->count();
    
    $pending = $submitted - $resolved;
    $trend = $this->calculateTrendForTimeline($submitted, $resolved);
    
    if ($type === 'week') {
        $periodLabel = 'Sem ' . ($index + 1);
    } else {
        $periodLabel = $start->format('d/m') . ' - ' . $end->format('d/m');
    }
    
    return [
        'period' => $periodLabel,
        'date' => $periodLabel,
        'submitted' => $submitted,
        'resolved' => $resolved,
        'pending' => $pending > 0 ? $pending : 0,
        'trend' => $trend,
    ];
}

/**
 * Calculate trend for timeline
 */
private function calculateTrendForTimeline($submitted, $resolved)
{
    if ($submitted > 0) {
        $rate = ($resolved / $submitted) * 100;
        // Simulação de tendência baseada na taxa
        return $rate > 80 ? 5.2 : ($rate > 60 ? 2.1 : ($rate > 40 ? 0.0 : -3.5));
    }
    return 0;
}

/**
 * Get summary stats for export
 */
private function getSummaryStatsForExport($indicators, $technicianPerformance, $grievanceStats)
{
    $totalIndicators = count($indicators);
    $activeIndicators = count(array_filter($indicators, function ($indicator) {
        return isset($indicator['current_value']) && $indicator['current_value'] > 0;
    }));
    
    // Calcular performance média dos indicadores
    $totalPerformance = 0;
    $count = 0;
    foreach ($indicators as $indicator) {
        if (isset($indicator['performance']) && is_numeric($indicator['performance'])) {
            $totalPerformance += $indicator['performance'];
            $count++;
        }
    }
    $averagePerformance = $count > 0 ? $totalPerformance / $count : 0;
    
    return [
        [
            'title' => 'Total Indicadores',
            'value' => $totalIndicators,
            'trend' => 'up',
        ],
        [
            'title' => 'Taxa de Resolução',
            'value' => number_format($grievanceStats['resolution_rate'] ?? 0, 1) . '%',
            'trend' => isset($grievanceStats['resolution_rate_trend']) && $grievanceStats['resolution_rate_trend'] > 0 ? 'up' : 
                      (isset($grievanceStats['resolution_rate_trend']) && $grievanceStats['resolution_rate_trend'] < 0 ? 'down' : 'stable'),
        ],
        [
            'title' => 'Tempo Médio',
            'value' => number_format($grievanceStats['avg_resolution_time'] ?? 0, 1) . ' horas',
            'trend' => isset($grievanceStats['resolution_time_trend']) && $grievanceStats['resolution_time_trend'] < 0 ? 'up' : 
                      (isset($grievanceStats['resolution_time_trend']) && $grievanceStats['resolution_time_trend'] > 0 ? 'down' : 'stable'),
        ],
        [
            'title' => 'Técnicos Activos',
            'value' => $grievanceStats['active_technicians'] ?? 0,
            'trend' => isset($grievanceStats['technicians_trend']) && $grievanceStats['technicians_trend'] > 0 ? 'up' : 
                      (isset($grievanceStats['technicians_trend']) && $grievanceStats['technicians_trend'] < 0 ? 'down' : 'stable'),
        ],
    ];
}

/**
 * Get quick stats for export
 */
private function getQuickStatsForExport($grievanceStats, $indicators)
{
    return [
        ['label' => 'Total de Reclamações', 'value' => $grievanceStats['total'] ?? 0],
        ['label' => 'Resolvidas', 'value' => $grievanceStats['resolved'] ?? 0],
        ['label' => 'Em Andamento', 'value' => $grievanceStats['pending'] ?? 0],
        ['label' => 'Em Progresso', 'value' => $grievanceStats['in_progress'] ?? 0],
        ['label' => 'Técnicos Activos', 'value' => $grievanceStats['active_technicians'] ?? 0],
        ['label' => 'Indicadores Ativos', 'value' => count(array_filter($indicators, function ($indicator) {
            return isset($indicator['current_value']) && $indicator['current_value'] > 0;
        }))],
    ];
}

   
    /**
     * Format indicator value
     */
    private function formatIndicatorValue($value, $unit)
    {
        if (is_numeric($value)) {
            if (strpos($unit, '%') !== false) {
                return number_format($value, 1) . '%';
            }
            return number_format($value, 2);
        }
        return $value;
    }

    /**
     * Calculate average resolution time for technician
     */
    private function calculateAverageResolutionTime($technicianId, $timeRange)
    {
        $resolvedCases = Grievance::where('assigned_to', $technicianId)
            ->whereIn('status', ['resolved', 'closed'])
            ->whereNotNull('resolved_at')
            ->whereNotNull('assigned_at')
            ->get();

        if ($resolvedCases->isEmpty()) {
            return 0;
        }

        $totalHours = 0;
        foreach ($resolvedCases as $case) {
            $hours = $case->assigned_at->diffInHours($case->resolved_at);
            $totalHours += $hours;
        }

        $averageHours = $totalHours / $resolvedCases->count();
        return round($averageHours / 24, 1); // Converter para dias
    }

    /**
     * Get resolution timeline
     */
    private function getResolutionTimeline($timeRange, $department = null)
    {
        $now = Carbon::now();
        $timeline = [];
        
        $query = Grievance::whereIn('status', ['resolved', 'closed']);
        
        if ($department) {
            $query->whereHas('project', function ($q) use ($department) {
                $q->where('department_id', $department->id);
            });
        }
        
        switch ($timeRange) {
            case 'week':
                // Últimos 7 dias
                for ($i = 6; $i >= 0; $i--) {
                    $date = $now->copy()->subDays($i);
                    $resolved = (clone $query)
                        ->whereDate('resolved_at', $date)
                        ->count();
                    
                    $timeline[] = [
                        'period' => $date->format('d/m'),
                        'resolved' => $resolved,
                        'trend' => $this->calculateTrend($timeline, $resolved),
                    ];
                }
                break;
                
            case 'month':
                // Últimas 4 semanas
                for ($i = 3; $i >= 0; $i--) {
                    $start = $now->copy()->subWeeks($i)->startOfWeek();
                    $end = $now->copy()->subWeeks($i)->endOfWeek();
                    
                    $resolved = (clone $query)
                        ->whereBetween('resolved_at', [$start, $end])
                        ->count();
                    
                    $timeline[] = [
                        'period' => 'Sem ' . ($i + 1),
                        'resolved' => $resolved,
                        'trend' => $this->calculateTrend($timeline, $resolved),
                    ];
                }
                break;
                
            default:
                // Últimos 6 meses
                for ($i = 5; $i >= 0; $i--) {
                    $month = $now->copy()->subMonths($i);
                    $resolved = (clone $query)
                        ->whereMonth('resolved_at', $month->month)
                        ->whereYear('resolved_at', $month->year)
                        ->count();
                    
                    $timeline[] = [
                        'period' => $month->format('M/Y'),
                        'resolved' => $resolved,
                        'trend' => $this->calculateTrend($timeline, $resolved),
                    ];
                }
        }
        
        return $timeline;
    }

    /**
     * Calculate trend between periods
     */
    private function calculateTrend(&$timeline, $currentValue)
    {
        if (count($timeline) === 0) {
            return 0;
        }
        
        $previousValue = $timeline[count($timeline) - 1]['resolved'] ?? 0;
        
        if ($previousValue > 0) {
            return round((($currentValue - $previousValue) / $previousValue) * 100, 1);
        }
        
        return 0;
    }

    /**
     * Get summary statistics
     */
    private function getSummaryStats($indicators, $technicianPerformance)
    {
        $totalIndicators = $indicators->count();
        $activeIndicators = $indicators->where('current_value', '>', 0)->count();
        
        $averagePerformance = $this->calculateAveragePerformance($indicators);
        $totalTechnicians = $technicianPerformance->count();
        $averageResolutionRate = $technicianPerformance->avg('resolution_rate') ?? 0;
        
        return [
            [
                'title' => 'Total Indicadores',
                'value' => $totalIndicators,
                'trend' => 'up',
            ],
            [
                'title' => 'Indicadores Ativos',
                'value' => $activeIndicators,
                'trend' => 'stable',
            ],
            [
                'title' => 'Performance Média',
                'value' => number_format($averagePerformance, 1) . '%',
                'trend' => $averagePerformance >= 60 ? 'up' : ($averagePerformance >= 40 ? 'stable' : 'down'),
            ],
            [
                'title' => 'Técnicos Ativos',
                'value' => $totalTechnicians,
                'trend' => 'stable',
            ],
        ];
    }

    /**
     * Calculate average performance
     */
    private function calculateAveragePerformance($indicators)
    {
        $validIndicators = $indicators->filter(function ($indicator) {
            return isset($indicator['performance']) && is_numeric($indicator['performance']);
        });
        
        if ($validIndicators->isEmpty()) {
            return 0;
        }
        
        return $validIndicators->avg('performance');
    }

    /**
     * Translate category names
     */
    private function translateCategory($category)
    {
        $translations = [
            'performance' => 'Performance',
            'satisfaction' => 'Satisfação',
            'efficiency' => 'Eficiência',
            'quality' => 'Qualidade',
            'productivity' => 'Produtividade',
            'reliability' => 'Confiabilidade',
        ];
        
        return $translations[strtolower($category)] ?? ucfirst($category);
    }

    /**
     * Get period label
     */
    private function getPeriodLabel($timeRange)
{
    $labels = [
        'week' => 'Última Semana',
        'month' => 'Último Mês',
        'quarter' => 'Último Trimestre',
        'year' => 'Último Ano',
        'all' => 'Todo o Período',
    ];
    
    return $labels[$timeRange] ?? 'Período Selecionado';
}


    /**
     * Get export indicator data
     */
    private function getExportIndicatorData(Request $request)
    {
        $categoryFilter = $request->input('categoryFilter', 'all');
        $timeRange = $request->input('timeRange', 'month');
        
        $indicatorsQuery = Indicator::query()->with('records');
        
        if ($categoryFilter !== 'all') {
            $indicatorsQuery->where('category', $categoryFilter);
        }
        
        return $indicatorsQuery->get()->map(function ($indicator) {
            $latestRecord = $indicator->records()->latest()->first();
            $previousRecord = $indicator->records()->latest()->skip(1)->first();
            
            $currentValue = $latestRecord ? $latestRecord->value : 0;
            $previousValue = $previousRecord ? $previousRecord->value : $currentValue;
            
            $trend = 0;
            if ($previousValue > 0 && $currentValue > 0) {
                $trend = (($currentValue - $previousValue) / $previousValue) * 100;
            }
            
            $performance = 0;
            if ($indicator->target_value > 0) {
                $performance = ($currentValue / $indicator->target_value) * 100;
            }
            
            return [
                'name' => $indicator->name,
                'description' => $indicator->description,
                'category' => $indicator->category,
                'current_value' => $currentValue,
                'target_value' => $indicator->target_value,
                'performance' => $performance,
                'trend' => $trend,
                'measurement_unit' => $indicator->measurement_unit,
            ];
        });
    }

    /**
     * Get employee stats
     */
    private function getEmployeeStats(User $employee): array
    {
        $employeeRole = $employee->roles->first()->name ?? '';
        
        // Se for gestor, obter estatísticas do departamento
        if ($employeeRole === 'Gestor') {
            $department = $employee->managedDepartment;
            
            if (!$department) {
                return [
                    'total_assigned' => 0,
                    'pending' => 0,
                    'completed' => 0,
                    'cancelled' => 0,
                    'in_progress' => 0,
                    'completion_rate' => 0,
                    'average_resolution_time' => 0,
                ];
            }

            // Total de reclamações no departamento
            $totalAssigned = Grievance::whereHas('project', function ($query) use ($department) {
                $query->where('department_id', $department->id);
            })->count();
            
            // Pendentes: submitted, under_review, assigned, in_progress, pending_approval
            $pending = Grievance::whereHas('project', function ($query) use ($department) {
                $query->where('department_id', $department->id);
            })
            ->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress', 'pending_approval'])
            ->count();
            
            // Concluídas: resolved e closed
            $completed = Grievance::whereHas('project', function ($query) use ($department) {
                $query->where('department_id', $department->id);
            })
            ->whereIn('status', ['resolved', 'closed'])
            ->count();
            
            // Canceladas: rejected
            $cancelled = Grievance::whereHas('project', function ($query) use ($department) {
                $query->where('department_id', $department->id);
            })
            ->where('status', 'rejected')
            ->count();
            
            // Em progresso: apenas in_progress
            $inProgress = Grievance::whereHas('project', function ($query) use ($department) {
                $query->where('department_id', $department->id);
            })
            ->where('status', 'in_progress')
            ->count();

            $completionRate = $totalAssigned > 0 
                ? round(($completed / $totalAssigned) * 100, 2)
                : 0;

            // Calcular tempo médio de resolução
            $completedTasks = Grievance::whereHas('project', function ($query) use ($department) {
                    $query->where('department_id', $department->id);
                })
                ->whereIn('status', ['resolved', 'closed'])
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
        
        // Se for técnico, obter estatísticas das tarefas atribuídas
        $totalAssigned = Grievance::where('assigned_to', $employee->id)->count();
        
        // Pendentes: assigned, in_progress, pending_approval
        $pending = Grievance::where('assigned_to', $employee->id)
            ->whereIn('status', ['assigned', 'in_progress', 'pending_approval'])
            ->count();
        
        // Concluídas: resolved e closed
        $completed = Grievance::where('assigned_to', $employee->id)
            ->whereIn('status', ['resolved', 'closed'])
            ->count();
        
        // Canceladas: rejected
        $cancelled = Grievance::where('assigned_to', $employee->id)
            ->where('status', 'rejected')
            ->count();
        
        // Em progresso: apenas in_progress
        $inProgress = Grievance::where('assigned_to', $employee->id)
            ->where('status', 'in_progress')
            ->count();

        $completionRate = $totalAssigned > 0 
            ? round(($completed / $totalAssigned) * 100, 2)
            : 0;

        // Calcular tempo médio de resolução
        $completedTasks = Grievance::where('assigned_to', $employee->id)
            ->whereIn('status', ['resolved', 'closed'])
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

    private function getDefaultStats(): array
    {
        return [
            'total_assigned' => 0,
            'pending' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'in_progress' => 0,
            'completion_rate' => 0,
            'average_resolution_time' => 0,
        ];
    }


    /**
 * Fix PDF font directory issue for Windows
 */
private function fixPdfFonts()
{
    $fontDir = storage_path('fonts');
    
    // Garantir que o diretório existe
    if (!file_exists($fontDir)) {
        mkdir($fontDir, 0755, true);
    }
    
    // Copiar fontes básicas se necessário
    $dompdfFontDir = base_path('vendor/dompdf/dompdf/lib/fonts');
    
    if (file_exists($dompdfFontDir)) {
        // Lista de fontes básicas necessárias
        $basicFonts = ['Helvetica.afm', 'Helvetica-Bold.afm', 'Helvetica-Oblique.afm'];
        
        foreach ($basicFonts as $font) {
            $source = $dompdfFontDir . '/' . $font;
            $destination = $fontDir . '/' . $font;
            
            if (file_exists($source) && !file_exists($destination)) {
                copy($source, $destination);
            }
        }
    }
    
    // Configurar o DOMPDF para usar o diretório correto
    define('DOMPDF_FONT_DIR', $fontDir);
    define('DOMPDF_FONT_CACHE', $fontDir);
}
}