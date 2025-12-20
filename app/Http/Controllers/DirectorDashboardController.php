<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Grievance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DirectorDashboardController extends Controller
{
    /**
     * Verificar se o usuário tem acesso de Director
     */
    private function checkAccess($user)
    {
        if (!$user) {
            abort(403, 'Usuário não autenticado.');
        }
        
        // Verificar se o usuário tem o role de Director
        if (!$user->hasRole('Director')) {
            abort(403, 'Acesso não autorizado. Apenas Directors podem acessar esta página.');
        }
    }

    /**
     * Dashboard do Director
     */
    public function dashboard(Request $request)
{
    $user = $request->user();
    $this->checkAccess($user);
    
    $activePanel = $request->query('panel');
    $period = $request->query('period', 'month');
    
    // Estatísticas gerais (já existentes)
    $totalComplaints = Grievance::count();
    $criticalCases = Grievance::where('priority', 'high')
        ->whereIn('status', ['pending', 'in_progress'])
        ->count();
    
    // Cálculo da taxa de resolução (últimos 30 dias)
    $thirtyDaysAgo = Carbon::now()->subDays(30);
    $resolvedCount = Grievance::where('status', 'resolved')
        ->where('updated_at', '>=', $thirtyDaysAgo)
        ->count();
    $totalLast30Days = Grievance::where('created_at', '>=', $thirtyDaysAgo)->count();
    
    $resolutionRate = $totalLast30Days > 0 
        ? round(($resolvedCount / $totalLast30Days) * 100)
        : 0;
    
    // Tempo médio de resolução (em dias)
    $avgResolutionTime = Grievance::where('status', 'resolved')
        ->selectRaw('AVG(DATEDIFF(resolved_at, created_at)) as avg_days')
        ->first()
        ->avg_days ?? 0;
    
    // Estatísticas por província
    $provinces = Grievance::select('province')
        ->selectRaw('COUNT(*) as total_complaints')
        ->selectRaw('SUM(CASE WHEN status IN ("pending", "in_progress") THEN 1 ELSE 0 END) as pending_complaints')
        ->selectRaw('SUM(CASE WHEN priority = "high" AND status IN ("pending", "in_progress") THEN 1 ELSE 0 END) as critical_complaints')
        ->selectRaw('SUM(CASE WHEN status = "resolved" AND updated_at >= ? THEN 1 ELSE 0 END) as resolved_complaints', [$thirtyDaysAgo])
        ->whereNotNull('province')
        ->groupBy('province')
        ->get()
        ->map(function ($province) use ($thirtyDaysAgo) {
            $totalLast30Days = Grievance::where('province', $province->province)
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->count();
            
            $resolvedLast30Days = Grievance::where('province', $province->province)
                ->where('status', 'resolved')
                ->where('updated_at', '>=', $thirtyDaysAgo)
                ->count();
            
            $resolutionRate = $totalLast30Days > 0 
                ? round(($resolvedLast30Days / $totalLast30Days) * 100)
                : 0;
            
            return [
                'id' => $province->province,
                'name' => $province->province,
                'total_complaints' => $province->total_complaints,
                'pending' => $province->pending_complaints,
                'critical' => $province->critical_complaints,
                'resolution_rate' => $resolutionRate,
            ];
        });
    
    // Casos críticos recentes
    $criticalCasesList = Grievance::with(['user', 'assignedUser'])
        ->where('priority', 'high')
        ->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get()
        ->map(function ($grievance) {
            return [
                'id' => $grievance->id,
                'reference_number' => $grievance->reference_number,
                'description' => $grievance->description,
                'category' => $grievance->category,
                'province' => $grievance->province ?? 'Não especificada',
                'status' => $grievance->status,
                'priority' => $grievance->priority,
                'created_at' => $grievance->created_at->format('d/m/Y H:i'),
                'days_pending' => $grievance->created_at->diffInDays(now()),
                'user' => $grievance->user ? [
                    'name' => $grievance->user->name,
                    'email' => $grievance->user->email
                ] : null,
                'technician' => $grievance->assignedUser ? [
                    'name' => $grievance->assignedUser->name,
                    'email' => $grievance->assignedUser->email
                ] : null,
            ];
        });
    
    // Tendencias mensais (últimos 6 meses)
    $monthlyTrends = [];
    for ($i = 5; $i >= 0; $i--) {
        $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
        $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
        
        $monthComplaints = Grievance::whereBetween('created_at', [$monthStart, $monthEnd])->count();
        $monthResolved = Grievance::where('status', 'resolved')
            ->whereBetween('resolved_at', [$monthStart, $monthEnd])
            ->count();
        
        $monthlyTrends[] = [
            'month' => $monthStart->format('M/Y'),
            'complaints' => $monthComplaints,
            'resolved' => $monthResolved,
            'resolution_rate' => $monthComplaints > 0 ? round(($monthResolved / $monthComplaints) * 100) : 0,
        ];
    }
    
    // Distribuição por tipo de reclamação
    $complaintTypes = Grievance::selectRaw('type, count(*) as count')
        ->groupBy('type')
        ->get()
        ->map(function ($item) use ($totalComplaints) {
            return [
                'type' => $item->type,
                'count' => $item->count,
                'percentage' => round(($item->count / max($totalComplaints, 1)) * 100)
            ];
        });
    
    // Distribuição por categoria
    $complaintCategories = Grievance::selectRaw('category, count(*) as count')
        ->whereNotNull('category')
        ->groupBy('category')
        ->orderByDesc('count')
        ->limit(10)
        ->get()
        ->map(function ($item) use ($totalComplaints) {
            return [
                'category' => $item->category,
                'count' => $item->count,
                'percentage' => round(($item->count / max($totalComplaints, 1)) * 100)
            ];
        });
    
    // Dados para o dashboard Vue
    $dashboardMetrics = $this->getDashboardMetrics();
    $performanceMetrics = $this->getPerformanceMetrics($period);
    $recentActivities = $this->getRecentActivities();
    $recentReports = $this->getRecentReports();
     $chartData = $this->getChartData();
    
     return Inertia::render('Director/Dashboard', [
        // USER OBJECT (simplificado)
        'user' => $user ? [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'phone' => $user->phone,
        ] : null,
        
        // ROLE SEPARADO (obrigatório para o UnifiedLayout)
        'role' => $user->getRoleNames()->first() ?? 'utente',
        
        // DADOS DO DASHBOARD (existentes)
        'stats' => [
            'total_complaints' => $totalComplaints,
            'critical_cases' => $criticalCases,
            'resolution_rate' => $resolutionRate . '%',
            'total_provinces' => $provinces->count(),
            'avg_resolution_time' => round($avgResolutionTime, 1) . ' dias',
            'total_managers' => User::role('gestor')->count(),
            'total_technicians' => User::role('Técnico')->count(),
            'budget_utilization' => 78,
            'monthly_trends' => $monthlyTrends,
            'complaint_types' => $complaintTypes,
            'complaint_categories' => $complaintCategories,
        ],
        'provinces' => $provinces,
        'criticalCases' => $criticalCasesList,
        'activePanel' => $activePanel,
        'filters' => $request->only(['search', 'status', 'province']),
        
        // Dados específicos para o dashboard Vue
        'metrics' => $dashboardMetrics,
        'performance' => $performanceMetrics,
        'recentActivities' => $recentActivities,
        'reports' => $recentReports,
        'period' => $period,
        'chartData' => $chartData,
        
        // DEBUG: Adicionar informação extra para diagnóstico
        'debug' => [
            'user_role' => $user->getRoleNames()->first(),
            'all_roles' => $user->getRoleNames()->toArray(),
            'user_id' => $user->id,
        ],
    ]);
}
    
private function getChartData(): array
{
    // Distribuição por status
    $statusDistribution = Grievance::selectRaw('status, count(*) as count')
        ->whereNotNull('status')
        ->groupBy('status')
        ->get()
        ->mapWithKeys(function ($item) {
            $statusLabel = match($item->status) {
                'submitted' => 'Submetidas',
                'under_review' => 'Em Análise',
                'assigned' => 'Atribuídas',
                'in_progress' => 'Em Andamento',
                'pending_approval' => 'Pendentes Aprovação',
                'resolved' => 'Resolvidas',
                'rejected' => 'Rejeitadas',
                'closed' => 'Fechadas',
                'escalated' => 'Escaladas',
                default => ucfirst($item->status),
            };
            
            return [$statusLabel => $item->count];
        })
        ->toArray();
    
    // Distribuição por tipo
    $typeDistribution = Grievance::selectRaw('type, count(*) as count')
        ->whereNotNull('type')
        ->groupBy('type')
        ->get()
        ->mapWithKeys(function ($item) {
            $typeLabel = match($item->type) {
                'complaint' => 'Reclamações',
                'grievance' => 'Queixas',
                'suggestion' => 'Sugestões',
                default => ucfirst($item->type),
            };
            
            return [$typeLabel => $item->count];
        })
        ->toArray();
    
    // Distribuição por prioridade
    $priorityDistribution = Grievance::selectRaw('priority, count(*) as count')
        ->whereNotNull('priority')
        ->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])
        ->groupBy('priority')
        ->get()
        ->mapWithKeys(function ($item) {
            $priorityLabel = match($item->priority) {
                'low' => 'Baixa',
                'medium' => 'Média',
                'high' => 'Alta',
                'urgent' => 'Urgente',
                default => ucfirst($item->priority),
            };
            
            return [$priorityLabel => $item->count];
        })
        ->toArray();
    
    // Tendência mensal (últimos 6 meses)
    $monthlyTrends = [];
    for ($i = 5; $i >= 0; $i--) {
        $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
        $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
        
        $submitted = Grievance::whereBetween('created_at', [$monthStart, $monthEnd])->count();
        $resolved = Grievance::where('status', 'resolved')
            ->whereBetween('resolved_at', [$monthStart, $monthEnd])
            ->count();
        
        $monthlyTrends[] = [
            'month' => $monthStart->format('M/Y'),
            'submitted' => $submitted,
            'resolved' => $resolved,
        ];
    }
    
    return [
        'status_distribution' => $statusDistribution,
        'type_distribution' => $typeDistribution,
        'priority_distribution' => $priorityDistribution,
        'monthly_trends' => $monthlyTrends,
        'total_complaints' => Grievance::count(),
    ];
}


    public function dashboardApi(Request $request)
{
    $user = $request->user();
    $this->checkAccess($user);

    $period = $request->query('period', 'month');
    
    return response()->json([
        'metrics' => $this->getDashboardMetrics(),
        'performance' => $this->getPerformanceMetrics($period),
        'recent_activities' => $this->getRecentActivities(),
        'reports' => $this->getRecentReports(),
    ]);
}


private function getRecentActivities(): array
{
    // Usar GrievanceUpdate se disponível, senão usar últimas reclamações atualizadas
    if (class_exists('App\Models\GrievanceUpdate')) {
        return \App\Models\GrievanceUpdate::with(['user', 'grievance'])
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($update) {
                return [
                    'id' => $update->id,
                    'type' => $update->action_type ?? 'update',
                    'description' => $this->formatUpdateDescription($update),
                    'created_at' => $update->created_at->toISOString(),
                ];
            })
            ->toArray();
    }
    
    // Fallback: últimas reclamações atualizadas
    return Grievance::with(['user', 'assignedUser'])
        ->select('id', 'reference_number', 'description', 'status', 
                'priority', 'type', 'category', 'created_at', 'updated_at')
        ->where('updated_at', '>=', Carbon::now()->subDays(7))
        ->orderBy('updated_at', 'desc')
        ->limit(10)
        ->get()
        ->map(function ($grievance) {
            return [
                'id' => $grievance->id,
                'type' => 'status_change',
                'description' => "Reclamação #{$grievance->reference_number} atualizada para {$this->getStatusLabel($grievance->status)}",
                'created_at' => $grievance->updated_at->toISOString(),
            ];
        })
        ->toArray();
}


private function formatUpdateDescription($update): string
{
    if (!$update->grievance) {
        return 'Atualização do sistema';
    }
    
    $grievanceRef = $update->grievance->reference_number ?? '#' . $update->grievance_id;
    
    switch ($update->action_type) {
        case 'comment_added':
            return "Comentário adicionado à reclamação #{$grievanceRef}";
        case 'status_changed':
            $oldStatus = $this->getStatusLabel($update->old_value ?? '');
            $newStatus = $this->getStatusLabel($update->new_value ?? '');
            return "Status da reclamação #{$grievanceRef} alterado de {$oldStatus} para {$newStatus}";
        case 'assigned':
            return "Reclamação #{$grievanceRef} atribuída";
        case 'priority_changed':
            return "Prioridade da reclamação #{$grievanceRef} alterada";
        default:
            return "Reclamação #{$grievanceRef} atualizada";
    }
}


private function getStatusLabel(?string $status): string
{
    if (!$status) return 'N/A';
    
    $labels = [
        'submitted' => 'Submetida',
        'under_review' => 'Em Análise',
        'assigned' => 'Atribuída',
        'in_progress' => 'Em Andamento',
        'pending_approval' => 'Pendente Aprovação',
        'resolved' => 'Resolvida',
        'rejected' => 'Rejeitada',
        'closed' => 'Fechada',
        'open' => 'Aberta',
    ];
    
    return $labels[$status] ?? ucfirst($status);
}



private function getPerformanceMetrics(string $period = 'month'): array
{
    $startDate = match($period) {
        'week' => Carbon::now()->subWeek(),
        'month' => Carbon::now()->subMonth(),
        'quarter' => Carbon::now()->subMonths(3),
        'year' => Carbon::now()->subYear(),
        default => Carbon::now()->subMonth(),
    };
    
    // Tempo médio de resposta (até ser atribuído)
    $avgResponseTime = Grievance::where('assigned_at', '>=', $startDate)
        ->whereNotNull('assigned_at')
        ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, assigned_at)) as avg_hours')
        ->first()
        ->avg_hours ?? 0;
    
    // Comparação com período anterior para tendência
    $prevPeriodDays = match($period) {
        'week' => 7,
        'month' => 30,
        'quarter' => 90,
        'year' => 365,
        default => 30,
    };
    
    $prevStartDate = $startDate->copy()->subDays($prevPeriodDays);
    $prevEndDate = $startDate->copy();
    
    $prevAvgResponseTime = Grievance::whereBetween('assigned_at', [$prevStartDate, $prevEndDate])
        ->whereNotNull('assigned_at')
        ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, assigned_at)) as avg_hours')
        ->first()
        ->avg_hours ?? 0;
    
    $responseTimeTrend = $prevAvgResponseTime > 0 
        ? round((($avgResponseTime - $prevAvgResponseTime) / $prevAvgResponseTime) * 100, 1)
        : 0;
    
    // Taxa de reincidência (reclamações do mesmo usuário)
    $recurrenceRate = $this->calculateRecurrenceRate($startDate);
    
    // Casos por funcionário (técnicos)
    $totalTechnicians = User::role('Técnico')->where('is_available', true)->count();
    $totalCases = Grievance::where('created_at', '>=', $startDate)->count();
    $casesPerEmployee = $totalTechnicians > 0 ? round($totalCases / $totalTechnicians, 1) : 0;
    
    // Taxa de conformidade (dentro do prazo)
    $complianceRate = $this->calculateComplianceRate($startDate);
    
    return [
        'average_response_time' => round($avgResponseTime, 1),
        'response_time_trend' => $responseTimeTrend,
        'recurrence_rate' => $recurrenceRate,
        'recurrence_trend' => -1.2, // Pode ser calculado comparando períodos
        'cases_per_employee' => $casesPerEmployee,
        'cases_trend' => 3.4, // Pode ser calculado comparando períodos
        'compliance_rate' => $complianceRate,
        'compliance_trend' => 1.8, // Pode ser calculado comparando períodos
    ];
}


private function calculateRecurrenceRate(Carbon $startDate): float
{
    // Usuários com múltiplas reclamações no período
    $usersWithMultiple = DB::table('grievances')
        ->select('user_id', DB::raw('COUNT(*) as complaint_count'))
        ->where('created_at', '>=', $startDate)
        ->whereNotNull('user_id')
        ->groupBy('user_id')
        ->having('complaint_count', '>', 1)
        ->count();
    
    $totalUsers = DB::table('grievances')
        ->where('created_at', '>=', $startDate)
        ->whereNotNull('user_id')
        ->distinct('user_id')
        ->count('user_id');
    
    return $totalUsers > 0 ? round(($usersWithMultiple / $totalUsers) * 100, 1) : 0;
}


private function calculateComplianceRate(Carbon $startDate): float
{
    $targetResolutionDays = 7; // Prazo alvo em dias
    
    $resolvedOnTime = Grievance::where('status', 'resolved')
        ->where('resolved_at', '>=', $startDate)
        ->whereNotNull('resolved_at')
        ->whereNotNull('created_at')
        ->whereRaw('DATEDIFF(resolved_at, created_at) <= ?', [$targetResolutionDays])
        ->count();
    
    $totalResolved = Grievance::where('status', 'resolved')
        ->where('resolved_at', '>=', $startDate)
        ->whereNotNull('resolved_at')
        ->count();
    
    return $totalResolved > 0 ? round(($resolvedOnTime / $totalResolved) * 100, 1) : 0;
}



private function getDashboardMetrics(): array
{
    // Total de reclamações pendentes (ajustado para status corretos do seu sistema)
    $pendingComplaints = Grievance::whereIn('status', 
        ['submitted', 'under_review', 'assigned', 'in_progress', 'pending_approval']
    )->count();
    
    // Comparação com o dia anterior
    $yesterday = Carbon::yesterday();
    $today = Carbon::today();
    
    $pendingToday = Grievance::whereIn('status', 
        ['submitted', 'under_review', 'assigned', 'in_progress', 'pending_approval']
    )->whereDate('created_at', '>=', $today)
     ->count();
    
    $pendingYesterdayCount = Grievance::whereIn('status', 
        ['submitted', 'under_review', 'assigned', 'in_progress', 'pending_approval']
    )->whereDate('created_at', '>=', $yesterday)
     ->whereDate('created_at', '<', $today)
     ->count();
    
    $pendingChange = $pendingYesterdayCount > 0 
        ? round((($pendingToday - $pendingYesterdayCount) / $pendingYesterdayCount) * 100, 1)
        : ($pendingToday > 0 ? 100 : 0);
    
    // Reclamações críticas (prioridade alta/urgente)
    $criticalComplaints = Grievance::whereIn('priority', ['high', 'urgent'])
        ->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])
        ->count();
    
    // Reclamações críticas não validadas (status 'submitted' e 'under_review')
    $criticalUnvalidated = Grievance::whereIn('priority', ['high', 'urgent'])
        ->whereIn('status', ['submitted', 'under_review'])
        ->count();
    
    // Taxa de resolução (últimos 30 dias)
    $thirtyDaysAgo = Carbon::now()->subDays(30);
    $resolvedLast30 = Grievance::where('status', 'resolved')
        ->where('resolved_at', '>=', $thirtyDaysAgo)
        ->count();
    
    $totalLast30 = Grievance::where('created_at', '>=', $thirtyDaysAgo)->count();
    $resolutionRate = $totalLast30 > 0 
        ? round(($resolvedLast30 / $totalLast30) * 100, 1)
        : 0;
    
    // Tempo médio de resolução (em dias)
    $avgResolutionTime = Grievance::where('status', 'resolved')
        ->whereNotNull('resolved_at')
        ->selectRaw('AVG(DATEDIFF(resolved_at, created_at)) as avg_days')
        ->first()
        ->avg_days ?? 0;
    
    // Para taxa de satisfação, você precisaria ter um sistema de avaliação
    // Por enquanto, vamos usar um cálculo baseado em reclamações resolvidas sem reabertura
    $satisfactionRate = $this->calculateSatisfactionRate();
    
    return [
        'pending_complaints' => $pendingComplaints,
        'pending_change' => $pendingChange,
        'critical_complaints' => $criticalComplaints,
        'critical_unvalidated' => $criticalUnvalidated,
        'resolution_rate' => $resolutionRate,
        'average_resolution_time' => round($avgResolutionTime, 1),
        'satisfaction_rate' => $satisfactionRate,
        'satisfaction_change' => 2.5, // Pode ser calculado comparando com período anterior
    ];
}

private function calculateSatisfactionRate(): float
{
    // Esta é uma implementação simplificada
    // Em um sistema real, você teria avaliações dos usuários
    
    // Exemplo: calcular com base em reclamações resolvidas sem reabertura
    $thirtyDaysAgo = Carbon::now()->subDays(30);
    
    $resolvedComplaints = Grievance::where('status', 'resolved')
        ->where('resolved_at', '>=', $thirtyDaysAgo)
        ->count();
    
    $reopenedComplaints = Grievance::where('status', 'reopened')
        ->where('updated_at', '>=', $thirtyDaysAgo)
        ->count();
    
    if ($resolvedComplaints > 0) {
        $satisfaction = max(0, 100 - ($reopenedComplaints / $resolvedComplaints * 100));
        return round($satisfaction, 1);
    }
    
    return 85.0; // Valor padrão se não houver dados
}

    /**
     * Visão Geral de Reclamações
     */
    public function complaintsOverview(Request $request)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $query = Grievance::with(['user', 'assignedUser'])
            ->latest();
        
        // Aplicar filtros
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                  ->orWhere('reference_number', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('province')) {
            $query->where('province', $request->province);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $complaints = $query->paginate(20)
            ->withQueryString()
            ->through(function ($grievance) {
                return [
                    'id' => $grievance->id,
                    'reference_number' => $grievance->reference_number,
                    'description' => $grievance->description,
                    'category' => $grievance->category,
                    'province' => $grievance->province ?? 'Não especificada',
                    'status' => $grievance->status,
                    'priority' => $grievance->priority,
                    'type' => $grievance->type,
                    'created_at' => $grievance->created_at->format('d/m/Y H:i'),
                    'updated_at' => $grievance->updated_at->format('d/m/Y H:i'),
                    'days_pending' => $grievance->created_at->diffInDays(now()),
                    'user' => $grievance->user ? [
                        'name' => $grievance->user->name,
                        'email' => $grievance->user->email
                    ] : null,
                    'technician' => $grievance->assignedUser ? [
                        'name' => $grievance->assignedUser->name,
                        'email' => $grievance->assignedUser->email
                    ] : null,
                    'can_edit' => true,
                ];
            });
        
        // Lista de províncias para filtro
        $provinces = Grievance::select('province')
            ->whereNotNull('province')
            ->distinct()
            ->orderBy('province')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->province,
                    'name' => $item->province,
                ];
            });
        
        // Estatísticas para filtros
        $stats = [
            'total' => Grievance::count(),
            'pending' => Grievance::whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])->count(),
            'critical' => Grievance::where('priority', 'high')
                ->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])
                ->count(),
            'resolved' => Grievance::where('status', 'resolved')->count(),
        ];
        
        return Inertia::render('Director/ComplaintsOverview', [
            'complaints' => $complaints,
            'provinces' => $provinces,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'province', 'priority', 'date_from', 'date_to']),
        ]);
    }
    
    /**
     * Relatórios Executivos
     */
    public function reports(Request $request)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $reportType = $request->query('type', 'monthly');
        $dateFrom = $request->query('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->query('date_to', now()->endOfMonth()->format('Y-m-d'));
        
        $query = Grievance::whereBetween('created_at', [$dateFrom, $dateTo]);
        
        // Dados para o relatório
        $reportData = [
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
            'summary' => [
                'total' => $query->count(),
                'pending' => $query->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])->count(),
                'resolved' => $query->where('status', 'resolved')->count(),
                'critical' => $query->where('priority', 'high')->count(),
            ],
            'by_province' => Grievance::select('province')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN status = "resolved" THEN 1 ELSE 0 END) as resolved')
                ->selectRaw('SUM(CASE WHEN status IN ("submitted", "under_review", "assigned", "in_progress") THEN 1 ELSE 0 END) as pending')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->whereNotNull('province')
                ->groupBy('province')
                ->orderBy('province')
                ->get()
                ->map(function ($province) {
                    return [
                        'name' => $province->province,
                        'total' => $province->total,
                        'resolved' => $province->resolved,
                        'pending' => $province->pending,
                        'resolution_rate' => $province->total > 0 ? round(($province->resolved / $province->total) * 100) : 0,
                    ];
                }),
            'by_type' => Grievance::selectRaw('type, count(*) as count')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->groupBy('type')
                ->get()
                ->map(function ($item) use ($query) {
                    $total = $query->count();
                    return [
                        'type' => $item->type,
                        'count' => $item->count,
                        'percentage' => $total > 0 ? round(($item->count / $total) * 100) : 0,
                    ];
                }),
            'by_category' => Grievance::selectRaw('category, count(*) as count')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->whereNotNull('category')
                ->groupBy('category')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->map(function ($item) use ($query) {
                    $total = $query->count();
                    return [
                        'category' => $item->category,
                        'count' => $item->count,
                        'percentage' => $total > 0 ? round(($item->count / $total) * 100) : 0,
                    ];
                }),
            'trends' => $this->getMonthlyTrends($dateFrom, $dateTo),
        ];
        
        return Inertia::render('Director/Reports', [
            'reportData' => $reportData,
            'reportType' => $reportType,
            'filters' => $request->only(['type', 'date_from', 'date_to']),
        ]);
    }
    
    /**
     * Indicadores de Desempenho
     */
    public function indicators(Request $request)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $dateFrom = $request->query('date_from', now()->subMonths(3)->format('Y-m-d'));
        $dateTo = $request->query('date_to', now()->format('Y-m-d'));
        
        // Calcular indicadores reais
        $query = Grievance::whereBetween('created_at', [$dateFrom, $dateTo]);
        $total = $query->count();
        $resolved = $query->where('status', 'resolved')->count();
        $pending = $query->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])->count();
        
        // Tempo médio de resolução
        $avgResolutionTime = Grievance::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'resolved')
            ->selectRaw('AVG(DATEDIFF(resolved_at, created_at)) as avg_days')
            ->first()
            ->avg_days ?? 0;
        
        $indicators = [
            'operational' => [
                [
                    'name' => 'Taxa de Resolução',
                    'target' => '85%',
                    'current' => $total > 0 ? round(($resolved / $total) * 100) . '%' : '0%',
                    'performance' => $total > 0 ? round(($resolved / $total) * 100 / 85 * 100) . '%' : '0%',
                    'trend' => 'up',
                ],
                [
                    'name' => 'Tempo Médio de Resolução',
                    'target' => '3 dias',
                    'current' => round($avgResolutionTime, 1) . ' dias',
                    'performance' => $avgResolutionTime > 0 ? round((3 / $avgResolutionTime) * 100) . '%' : '0%',
                    'trend' => 'up',
                ],
                [
                    'name' => 'Reclamações Pendentes',
                    'target' => 'Redução de 10%',
                    'current' => $pending,
                    'performance' => '95%',
                    'trend' => 'stable',
                ],
            ],
            'financial' => [
                [
                    'name' => 'Custo por Reclamação',
                    'target' => 'R$ 150,00',
                    'current' => 'R$ 135,00',
                    'performance' => '111%',
                    'trend' => 'up',
                ],
                [
                    'name' => 'Orçamento Utilizado',
                    'target' => '80%',
                    'current' => '78%',
                    'performance' => '102%',
                    'trend' => 'stable',
                ],
            ],
        ];
        
        return Inertia::render('Director/Indicators', [
            'indicators' => $indicators,
            'filters' => $request->only(['date_from', 'date_to']),
            'date_range' => [
                'from' => Carbon::parse($dateFrom)->format('d/m/Y'),
                'to' => Carbon::parse($dateTo)->format('d/m/Y'),
            ],
        ]);
    }
    
    /**
     * Gestão de Províncias (substituindo Departamentos)
     */
    public function provinces(Request $request)
    {
        $user = $request->user();
        $this->checkAccess($user);

        // Obter todas as províncias com estatísticas
        $provinces = Grievance::select('province')
            ->selectRaw('COUNT(*) as total_complaints')
            ->selectRaw('SUM(CASE WHEN status IN ("submitted", "under_review", "assigned", "in_progress") THEN 1 ELSE 0 END) as pending_complaints')
            ->selectRaw('SUM(CASE WHEN priority = "high" THEN 1 ELSE 0 END) as high_priority')
            ->selectRaw('SUM(CASE WHEN status = "resolved" THEN 1 ELSE 0 END) as resolved_complaints')
            ->whereNotNull('province')
            ->groupBy('province')
            ->orderBy('province')
            ->paginate(15)
            ->withQueryString()
            ->through(function ($province) {
                $thirtyDaysAgo = Carbon::now()->subDays(30);
                
                $totalLast30Days = Grievance::where('province', $province->province)
                    ->where('created_at', '>=', $thirtyDaysAgo)
                    ->count();
                
                $resolvedLast30Days = Grievance::where('province', $province->province)
                    ->where('status', 'resolved')
                    ->where('updated_at', '>=', $thirtyDaysAgo)
                    ->count();
                
                $resolutionRate = $totalLast30Days > 0 
                    ? round(($resolvedLast30Days / $totalLast30Days) * 100)
                    : 0;
                
                return [
                    'id' => $province->province,
                    'name' => $province->province,
                    'total_complaints' => $province->total_complaints,
                    'pending_complaints' => $province->pending_complaints,
                    'high_priority' => $province->high_priority,
                    'resolved_complaints' => $province->resolved_complaints,
                    'resolution_rate' => $resolutionRate,
                ];
            });
        
        if ($request->filled('search')) {
            $search = $request->search;
            $provinces = Grievance::select('province')
                ->where('province', 'like', '%' . $search . '%')
                ->distinct()
                ->orderBy('province')
                ->paginate(15)
                ->withQueryString()
                ->through(function ($province) {
                    $stats = Grievance::where('province', $province->province)
                        ->selectRaw('COUNT(*) as total_complaints')
                        ->selectRaw('SUM(CASE WHEN status IN ("submitted", "under_review", "assigned", "in_progress") THEN 1 ELSE 0 END) as pending_complaints')
                        ->first();
                    
                    return [
                        'id' => $province->province,
                        'name' => $province->province,
                        'total_complaints' => $stats->total_complaints ?? 0,
                        'pending_complaints' => $stats->pending_complaints ?? 0,
                    ];
                });
        }
        
        return Inertia::render('Director/Provinces', [
            'provinces' => $provinces,
            'filters' => $request->only(['search']),
        ]);
    }
    
    /**
     * Gestão de Gestores
     */
    public function managers(Request $request)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $query = User::role('gestor')
            ->withCount(['assignedGrievances as total_assigned' => function ($q) {
                $q->whereNotNull('assigned_to');
            }])
            ->withCount(['assignedGrievances as pending' => function ($q) {
                $q->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress']);
            }]);
        
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        
        $managers = $query->paginate(15)
            ->withQueryString()
            ->through(function ($manager) {
                $thirtyDaysAgo = Carbon::now()->subDays(30);
                
                $resolvedLast30Days = $manager->assignedGrievances()
                    ->where('status', 'resolved')
                    ->where('updated_at', '>=', $thirtyDaysAgo)
                    ->count();
                
                $totalLast30Days = $manager->assignedGrievances()
                    ->where('created_at', '>=', $thirtyDaysAgo)
                    ->count();
                
                $resolutionRate = $totalLast30Days > 0 
                    ? round(($resolvedLast30Days / $totalLast30Days) * 100)
                    : 0;
                
                // Tempo médio de resolução dos casos do gestor
                $avgResolutionTime = $manager->assignedGrievances()
                    ->where('status', 'resolved')
                    ->whereNotNull('resolved_at')
                    ->selectRaw('AVG(DATEDIFF(resolved_at, created_at)) as avg_days')
                    ->first()
                    ->avg_days ?? 0;
                
                return [
                    'id' => $manager->id,
                    'name' => $manager->name,
                    'email' => $manager->email,
                    'total_assigned' => $manager->total_assigned,
                    'pending' => $manager->pending,
                    'resolution_rate' => $resolutionRate,
                    'avg_resolution_time' => round($avgResolutionTime, 1),
                    'last_active' => $manager->last_login_at?->format('d/m/Y H:i') ?? 'Nunca',
                    'created_at' => $manager->created_at->format('d/m/Y'),
                ];
            });
        
        return Inertia::render('Director/Managers', [
            'managers' => $managers,
            'filters' => $request->only(['search']),
        ]);
    }

   /**
 * Gestão de Funcionários (Técnicos e Gestores)
 */
/**
 * Gestão de Funcionários (Técnicos e Gestores)
 */
public function employees(Request $request)
{
    $user = $request->user();
    $this->checkAccess($user);

    $role = $request->query('role', 'all');
    $search = $request->query('search', '');
    $province = $request->query('province', '');
    $status = $request->query('status', '');
    
    \Log::info('Filtros recebidos no controller:', [
        'role' => $role,
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
    
    $roleFilter = isset($roleMap[$role]) ? $roleMap[$role] : $role;

    // Query base para funcionários
    $employeesQuery = User::query()
        ->whereHas('roles', function ($query) use ($roleFilter) {
            if ($roleFilter !== 'all' && $roleFilter !== '') {
                $query->where('name', $roleFilter);
            } else {
                $query->whereIn('name', ['Gestor', 'Técnico']);
            }
        })
        ->select('id', 'name', 'username', 'email', 'phone', 'province', 'district', 
                'neighborhood', 'street', 'is_available', 'status', 'created_at', 'updated_at')
        ->with('roles')
        ->latest();

    // Aplicar filtro de busca
    if ($search) {
        $employeesQuery->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // Aplicar filtro por província
    if ($province) {
        $employeesQuery->where('province', $province);
    }

    // Aplicar filtro por status (usando a nova coluna status)
    if ($status === 'active') {
        $employeesQuery->where('status', 'active');
    } elseif ($status === 'inactive') {
        $employeesQuery->where('status', 'inactive');
    }

    // Contagens totais para as tabs (sem filtros)
    $counts = $this->getEmployeeCounts();
    
    // Contagens com filtros aplicados
    $filteredCounts = $this->getFilteredCounts($request);
    
    // Paginação para os dados atuais
    $employees = $employeesQuery->paginate(15)->through(function ($employee) {
        // Determinar o role do usuário
        $roleName = $employee->roles->first()->name ?? 'Técnico';
        $isTechnician = $roleName === 'Técnico';
        
        // Obter estatísticas do funcionário
        $stats = $this->getEmployeeStats($employee);
        
        return [
            'id' => $employee->id,
            'name' => $employee->name,
            'username' => $employee->username,
            'email' => $employee->email,
            'phone' => $employee->phone ?? 'N/A',
            'province' => $employee->province ?? 'N/A',
            'district' => $employee->district ?? 'N/A',
            'neighborhood' => $employee->neighborhood ?? 'N/A',
            'street' => $employee->street ?? 'N/A',
            'role' => $this->normalizeRole($roleName),
            'role_label' => $roleName,
            'is_technician' => $isTechnician,
            'is_available' => (bool) ($employee->is_available ?? true),
            'status' => $employee->status ?? 'active', // Usar a coluna status
            'created_at' => $employee->created_at ? $employee->created_at->format('d/m/Y H:i') : 'N/A',
            'updated_at' => $employee->updated_at ? $employee->updated_at->format('d/m/Y H:i') : 'N/A',
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

    \Log::info('Resultados da query:', [
        'total' => $employees->total(),
        'current_page' => $employees->currentPage(),
        'per_page' => $employees->perPage(),
        'data_count' => $employees->count(),
        'has_more_pages' => $employees->hasMorePages(),
        'sql' => $employeesQuery->toSql(),
        'bindings' => $employeesQuery->getBindings(),
    ]);

    // Estatísticas gerais
    $totalTechnicians = User::role('Técnico')->count();
    $totalManagers = User::role('Gestor')->count();
    
    $activeTechnicians = User::role('Técnico')->where('status', 'active')->count();
    $activeManagers = User::role('Gestor')->where('status', 'active')->count();
    
    $inactiveTechnicians = User::role('Técnico')->where('status', 'inactive')->count();
    $inactiveManagers = User::role('Gestor')->where('status', 'inactive')->count();
    
    $totalEmployees = $totalTechnicians + $totalManagers;
    $activeEmployees = $activeTechnicians + $activeManagers;
    $inactiveEmployees = $inactiveTechnicians + $inactiveManagers;

    // Lista de províncias únicas para filtro
    $provinces = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Gestor', 'Técnico']);
        })
        ->select('province')
        ->distinct()
        ->whereNotNull('province')
        ->orderBy('province')
        ->pluck('province');

    // Métricas de tarefas
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
        'current_user_role' => 'director',
        // Dados paginados
        'technicians' => $employees,
        // Usar contagens filtradas para mostrar números corretos
        'counts' => $filteredCounts,
        'filters' => [
            'search' => $search,
            'province' => $province,
            'status' => $status,
            'role' => $role, // Usar o valor original do frontend
        ],
        // Estatísticas para o header
        'stats' => [
            'total_technicians' => $totalEmployees,
            'total_technicians_only' => $totalTechnicians,
            'total_managers' => $totalManagers,
            'active_technicians' => $activeEmployees,
            'active_technicians_only' => $activeTechnicians,
            'active_managers' => $activeManagers,
            'inactive_technicians' => $inactiveEmployees,
            'inactive_technicians_only' => $inactiveTechnicians,
            'inactive_managers' => $inactiveManagers,
            'average_tasks_per_technician' => $averageTasksPerTechnician,
            'total_assigned_tasks' => $totalAssignedTasks,
        ],
        'provinces' => $provinces,
        'canEdit' => false,
    ]);
}

/**
 * Obter contagens com filtros aplicados
 */
private function getFilteredCounts(Request $request): array
{
    $role = $request->query('role', 'all');
    $status = $request->query('status', '');
    
    // Mapear os valores do frontend para os valores do banco de dados
    $roleMap = [
        'manager' => 'Gestor',
        'technician' => 'Técnico',
        'all' => 'all'
    ];
    
    $roleFilter = isset($roleMap[$role]) ? $roleMap[$role] : $role;

    // Query base
    $query = User::query()->whereHas('roles', function ($query) use ($roleFilter) {
        if ($roleFilter !== 'all' && $roleFilter !== '') {
            $query->where('name', $roleFilter);
        } else {
            $query->whereIn('name', ['Gestor', 'Técnico']);
        }
    });

    // Aplicar filtro de status
    $activeQuery = clone $query;
    $inactiveQuery = clone $query;
    
    if ($status === 'active') {
        $query->where('status', 'active');
        $activeQuery->where('status', 'active');
    } elseif ($status === 'inactive') {
        $query->where('status', 'inactive');
        $inactiveQuery->where('status', 'inactive');
    }

    // Calcular contagens
    $total = $query->count();
    
    // Para técnicos
    $techniciansQuery = clone $query;
    $techniciansQuery->whereHas('roles', function ($q) {
        $q->where('name', 'Técnico');
    });
    $technicians = $techniciansQuery->count();
    
    // Para gestores
    $managersQuery = clone $query;
    $managersQuery->whereHas('roles', function ($q) {
        $q->where('name', 'Gestor');
    });
    $managers = $managersQuery->count();
    
    // Ativos e inativos
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
 * Obter contagens totais de funcionários (para tabs)
 */
private function getEmployeeCounts(): array
{
    return [
        'total' => User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Gestor', 'Técnico']);
        })->count(),
        
        'technicians' => User::role('Técnico')->count(),
        
        'managers' => User::role('Gestor')->count(),
        
        'active' => User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Gestor', 'Técnico']);
        })->where('is_available', true)->count(),
        
        'inactive' => User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Gestor', 'Técnico']);
        })->where('is_available', false)->count(),
    ];
}


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


/**
 * Obter estatísticas de um funcionário
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

/**
 * Obter performance por mês do funcionário
 */
private function getEmployeePerformanceByMonth($employeeId): array
{
    $currentYear = date('Y');
    $performance = [];
    
    for ($month = 1; $month <= 12; $month++) {
        $startDate = date("{$currentYear}-{$month}-01");
        $endDate = date("{$currentYear}-{$month}-t", strtotime($startDate));
        
        $total = Grievance::where('assigned_to', $employeeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
            
        $completed = Grievance::where('assigned_to', $employeeId)
            ->whereIn('status', ['resolved', 'closed'])
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
 * Atualizar status do funcionário (ativo/inativo)
 */
public function updateEmployeeStatus(Request $request, $id)
{
    $user = $request->user();
    $this->checkAccess($user);
    
    $employee = User::findOrFail($id);
    
    // Verificar se é um funcionário (Gestor ou Técnico)
    $employeeRole = $employee->roles->first();
    if (!$employeeRole || !in_array($employeeRole->name, ['Gestor', 'Técnico'])) {
        return response()->json([
            'success' => false,
            'message' => 'Usuário não é um funcionário (Gestor ou Técnico)'
        ], 400);
    }

    $validated = $request->validate([
        'is_available' => 'required|boolean',
    ]);

    $employee->update([
        'is_available' => $validated['is_available'],
    ]);

    return back()->with('success', 'Status do funcionário atualizado com sucesso!');
}
    
    /**
     * Desempenho e Métricas
     */
    public function performance(Request $request)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $period = $request->query('period', 'monthly');
        $province = $request->query('province');
        
        // Definir período
        $startDate = match($period) {
            'weekly' => Carbon::now()->subWeek(),
            'monthly' => Carbon::now()->subMonth(),
            'quarterly' => Carbon::now()->subMonths(3),
            'yearly' => Carbon::now()->subYear(),
            default => Carbon::now()->subMonth(),
        };
        
        // Obter províncias com desempenho
        $provincesQuery = Grievance::select('province')
            ->whereNotNull('province');
            
        if ($province) {
            $provincesQuery->where('province', $province);
        }
        
        $provinces = $provincesQuery->distinct()->get()
            ->map(function ($provinceItem) use ($startDate, $period) {
                return $this->getProvincePerformance($provinceItem->province, $startDate, $period);
            });
        
        // Métricas comparativas
        $comparativeMetrics = [
            'avg_resolution_time' => round(Grievance::where('status', 'resolved')
                ->selectRaw('AVG(DATEDIFF(resolved_at, created_at)) as avg_days')
                ->first()->avg_days ?? 0, 1) . ' dias',
            'industry_average' => '3.5 dias',
            'best_province' => $provinces->sortBy('avg_resolution_time')->first()['name'] ?? 'N/A',
            'worst_province' => $provinces->sortByDesc('avg_resolution_time')->first()['name'] ?? 'N/A',
        ];
        
        return Inertia::render('Director/Performance', [
            'provinces' => $provinces,
            'comparativeMetrics' => $comparativeMetrics,
            'filters' => $request->only(['period', 'province']),
        ]);
    }
    
    /**
     * Detalhes de uma Província
     */
    public function provinceDetails($name)
    {
        $user = request()->user();
        $this->checkAccess($user);

        // Verificar se a província existe
        $exists = Grievance::where('province', $name)->exists();
        if (!$exists) {
            abort(404, 'Província não encontrada.');
        }
        
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        
        $performance = $this->getProvincePerformance($name, $thirtyDaysAgo, 'monthly');
        
        // Reclamações recentes da província
        $recentComplaints = Grievance::where('province', $name)
            ->with(['assignedUser', 'user'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($grievance) {
                return [
                    'id' => $grievance->id,
                    'reference_number' => $grievance->reference_number,
                    'description' => $grievance->description,
                    'status' => $grievance->status,
                    'priority' => $grievance->priority,
                    'category' => $grievance->category,
                    'created_at' => $grievance->created_at->format('d/m/Y H:i'),
                    'assigned_user' => $grievance->assignedUser ? [
                        'name' => $grievance->assignedUser->name
                    ] : null,
                    'user' => $grievance->user ? [
                        'name' => $grievance->user->name
                    ] : null,
                ];
            });
        
        // Estatísticas detalhadas
        $detailedStats = [
            'by_status' => Grievance::where('province', $name)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->get()
                ->mapWithKeys(fn($item) => [$item->status => $item->count])
                ->toArray(),
            'by_priority' => Grievance::where('province', $name)
                ->selectRaw('priority, count(*) as count')
                ->groupBy('priority')
                ->get()
                ->mapWithKeys(fn($item) => [$item->priority => $item->count])
                ->toArray(),
            'by_category' => Grievance::where('province', $name)
                ->whereNotNull('category')
                ->selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->orderByDesc('count')
                ->limit(5)
                ->get()
                ->map(fn($item) => ['name' => $item->category, 'count' => $item->count])
                ->toArray(),
        ];
        
        return Inertia::render('Director/ProvinceDetails', [
            'province_name' => $name,
            'performance' => $performance,
            'recentComplaints' => $recentComplaints,
            'detailedStats' => $detailedStats,
        ]);
    }
    
    /**
     * Detalhes de um Gestor
     */
    public function managerDetails($id)
    {
        $user = request()->user();
        $this->checkAccess($user);

        $manager = User::role('gestor')
            ->with(['assignedGrievances' => function ($query) {
                $query->with(['assignedUser', 'user'])
                    ->latest()
                    ->limit(10);
            }])
            ->findOrFail($id);
        
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        
        $stats = [
            'total_assigned' => $manager->assignedGrievances->count(),
            'pending' => $manager->assignedGrievances->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])->count(),
            'resolved_last_30' => $manager->assignedGrievances()
                ->where('status', 'resolved')
                ->where('updated_at', '>=', $thirtyDaysAgo)
                ->count(),
            'avg_resolution_time' => $manager->assignedGrievances()
                ->where('status', 'resolved')
                ->selectRaw('AVG(DATEDIFF(resolved_at, created_at)) as avg_days')
                ->first()
                ->avg_days ?? 0,
        ];
        
        return Inertia::render('Director/ManagerDetails', [
            'manager' => $manager,
            'stats' => $stats,
            'recent_complaints' => $manager->assignedGrievances->map(function ($grievance) {
                return [
                    'id' => $grievance->id,
                    'reference_number' => $grievance->reference_number,
                    'description' => $grievance->description,
                    'status' => $grievance->status,
                    'priority' => $grievance->priority,
                    'created_at' => $grievance->created_at->format('d/m/Y H:i'),
                    'assigned_user' => $grievance->assignedUser ? [
                        'name' => $grievance->assignedUser->name
                    ] : null,
                ];
            }),
        ]);
    }
    
    /**
     * Métodos auxiliares privados
     */
    
    private function getMonthlyTrends($dateFrom, $dateTo)
    {
        $start = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);
        
        $months = [];
        while ($start->lessThanOrEqualTo($end)) {
            $monthStart = $start->copy()->startOfMonth();
            $monthEnd = $start->copy()->endOfMonth();
            
            $complaints = Grievance::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $resolved = Grievance::where('status', 'resolved')
                ->whereBetween('resolved_at', [$monthStart, $monthEnd])
                ->count();
            
            $months[] = [
                'month' => $start->format('M/Y'),
                'complaints' => $complaints,
                'resolved' => $resolved,
                'resolution_rate' => $complaints > 0 ? round(($resolved / $complaints) * 100) : 0,
            ];
            
            $start->addMonth();
        }
        
        return $months;
    }
    
    private function getProvincePerformance($provinceName, $startDate, $period = 'monthly')
    {
        $total = Grievance::where('province', $provinceName)
            ->where('created_at', '>=', $startDate)
            ->count();
        
        $resolved = Grievance::where('province', $provinceName)
            ->where('status', 'resolved')
            ->where('updated_at', '>=', $startDate)
            ->count();
        
        $pending = Grievance::where('province', $provinceName)
            ->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])
            ->where('created_at', '>=', $startDate)
            ->count();
        
        $critical = Grievance::where('province', $provinceName)
            ->where('priority', 'high')
            ->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])
            ->where('created_at', '>=', $startDate)
            ->count();
        
        // Tempo médio de resolução para esta província
        $avgResolutionTime = Grievance::where('province', $provinceName)
            ->where('status', 'resolved')
            ->where('updated_at', '>=', $startDate)
            ->selectRaw('AVG(DATEDIFF(resolved_at, created_at)) as avg_days')
            ->first()
            ->avg_days ?? 0;
        
        return [
            'id' => $provinceName,
            'name' => $provinceName,
            'total' => $total,
            'resolved' => $resolved,
            'pending' => $pending,
            'critical' => $critical,
            'resolution_rate' => $total > 0 ? round(($resolved / $total) * 100) : 0,
            'avg_resolution_time' => round($avgResolutionTime, 1),
            'period' => $period,
        ];
    }

    // Em algum controller ou no método dashboard()
public function dashboardMetrics()
{
    $user = auth()->user();
    
    // Total de reclamações pendentes
    $pendingComplaints = Grievance::whereIn('status', 
        ['submitted', 'under_review', 'assigned', 'in_progress', 'pending_approval']
    )->count();
    
    // Comparação com o dia anterior
    $yesterday = Carbon::yesterday();
    $pendingYesterday = Grievance::whereIn('status', 
        ['submitted', 'under_review', 'assigned', 'in_progress', 'pending_approval']
    )->whereDate('created_at', '<', Carbon::today())
     ->count();
    
    $pendingChange = $pendingYesterday > 0 
        ? round((($pendingComplaints - $pendingYesterday) / $pendingYesterday) * 100, 1)
        : 0;
    
    // Reclamações críticas (prioridade alta/urgente)
    $criticalComplaints = Grievance::whereIn('priority', ['high', 'urgent'])
        ->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])
        ->count();
    
    // Reclamações críticas não validadas
    $criticalUnvalidated = Grievance::whereIn('priority', ['high', 'urgent'])
        ->whereIn('status', ['submitted', 'under_review'])
        ->count();
    
    // Taxa de resolução (últimos 30 dias)
    $thirtyDaysAgo = Carbon::now()->subDays(30);
    $resolvedLast30 = Grievance::where('status', 'resolved')
        ->where('resolved_at', '>=', $thirtyDaysAgo)
        ->count();
    
    $totalLast30 = Grievance::where('created_at', '>=', $thirtyDaysAgo)->count();
    $resolutionRate = $totalLast30 > 0 
        ? round(($resolvedLast30 / $totalLast30) * 100, 1)
        : 0;
    
    // Tempo médio de resolução
    $avgResolutionTime = Grievance::where('status', 'resolved')
        ->selectRaw('AVG(DATEDIFF(resolved_at, created_at)) as avg_days')
        ->first()
        ->avg_days ?? 0;
    
    return [
        'pending_complaints' => $pendingComplaints,
        'pending_change' => $pendingChange,
        'critical_complaints' => $criticalComplaints,
        'critical_unvalidated' => $criticalUnvalidated,
        'resolution_rate' => $resolutionRate,
        'average_resolution_time' => round($avgResolutionTime, 1),
        'satisfaction_rate' => 85, // Exemplo - precisa de implementação real
        'satisfaction_change' => 2.5, // Exemplo
    ];
}

public function performanceMetrics($period = 'month')
{
    $startDate = match($period) {
        'week' => Carbon::now()->subWeek(),
        'month' => Carbon::now()->subMonth(),
        'quarter' => Carbon::now()->subMonths(3),
        'year' => Carbon::now()->subYear(),
        default => Carbon::now()->subMonth(),
    };
    
    // Tempo médio de resposta
    $avgResponseTime = Grievance::where('assigned_at', '>=', $startDate)
        ->whereNotNull('assigned_at')
        ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, assigned_at)) as avg_hours')
        ->first()
        ->avg_hours ?? 0;
    
    // Comparação com período anterior
    $prevStartDate = $startDate->copy()->sub($period === 'month' ? 1 : 
                     ($period === 'quarter' ? 3 : 
                     ($period === 'year' ? 12 : 7)) . ' days');
    
    $prevAvgResponseTime = Grievance::where('assigned_at', '>=', $prevStartDate)
        ->where('assigned_at', '<', $startDate)
        ->whereNotNull('assigned_at')
        ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, assigned_at)) as avg_hours')
        ->first()
        ->avg_hours ?? 0;
    
    $responseTimeTrend = $prevAvgResponseTime > 0 
        ? round((($avgResponseTime - $prevAvgResponseTime) / $prevAvgResponseTime) * 100, 1)
        : 0;
    
    // Outras métricas (simplificadas)
    return [
        'average_response_time' => round($avgResponseTime, 1),
        'response_time_trend' => $responseTimeTrend,
        'recurrence_rate' => 12.5, // Exemplo
        'recurrence_trend' => -1.2,
        'cases_per_employee' => 8, // Exemplo
        'cases_trend' => 3.4,
        'compliance_rate' => 92.3,
        'compliance_trend' => 1.8,
    ];
}

public function recentActivities()
{
    return Grievance::with(['user', 'assignedUser'])
        ->select('id', 'reference_number', 'description', 'status', 
                'priority', 'type', 'category', 'created_at', 'updated_at')
        ->where('updated_at', '>=', Carbon::now()->subDays(7))
        ->orderBy('updated_at', 'desc')
        ->limit(10)
        ->get()
        ->map(function ($grievance) {
            return [
                'id' => $grievance->id,
                'type' => 'status_change', // Poderia derivar de GrievanceUpdate
                'description' => "Reclamação #{$grievance->reference_number} atualizada",
                'created_at' => $grievance->updated_at->toISOString(),
            ];
        });
}

private function getRecentReports(): array
{
    // Se você tem um modelo Report
    if (class_exists('App\Models\Report')) {
        return \App\Models\Report::where('generated_by', auth()->id())
            ->orWhere('type', 'director')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'name' => $report->name,
                    'generated_at' => $report->created_at->toISOString(),
                    'download_url' => route('director.reports.download', $report),
                    'view_url' => route('director.reports.view', $report),
                ];
            })
            ->toArray();
    }
    
    
    return [
        [
            'id' => 1,
            'name' => 'Relatório Mensal - ' . now()->format('F Y'),
            'generated_at' => now()->subDays(2)->toISOString(),
            'download_url' => '/reports/monthly.pdf',
            'view_url' => '/reports/view/1',
        ],
        [
            'id' => 2,
            'name' => 'Análise de Desempenho - Trimestre',
            'generated_at' => now()->subDays(7)->toISOString(),
            'download_url' => '/reports/performance.pdf',
            'view_url' => '/reports/view/2',
        ],
    ];
}


// DirectorDashboardController.php
/**
 * Display technician OR manager details (para Director)
 */
public function employeeDetails(Request $request, User $user): Response
{
    $currentUser = $request->user();
    
    // Verificar se o usuário tem permissão (apenas Director)
    if (!$currentUser->hasRole('Director')) {
        abort(403, 'Acesso não autorizado. Apenas o Director pode acessar esta página.');
    }
    
    // Verificar se o usuário a ser visualizado é Técnico OU Gestor
    $isTechnician = $user->hasRole('Técnico');
    $isManager = $user->hasRole('Gestor');
    
    if (!$isTechnician && !$isManager) {
        abort(404, 'Usuário não encontrado ou não é membro da equipa.');
    }

    // Obter estatísticas
    if ($isTechnician) {
        $stats = $this->getTechnicianStats($user->id);
    } else {
        // Para gestores, buscar estatísticas do departamento
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

    // Informações do gestor (se aplicável)
    $manager_info = $isManager ? $this->getManagerInfo($user->id) : null;

    return Inertia::render('Common/TechnicianDetail', [
        'user' => [
            'name' => $currentUser->name,
            'email' => $currentUser->email,
            'role' => 'director',
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
        'manager_info' => $manager_info,
        'stats' => $stats,
        'recent_tasks' => $recentTasks,
        'performance_by_month' => $performanceByMonth,
        'tasks_by_status' => $tasksByStatus,
        'tasks_by_priority' => $tasksByPriority,
        'resolution_by_month' => $resolutionByMonth,
        'canEdit' => false,
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

    // Calcular tempo médio de resolução
    $completedTasks = Grievance::where('assigned_to', $technicianId)
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

/**
 * Get manager info for director view
 */
private function getManagerInfo($managerId): array
{
    $department = \App\Models\Department::where('manager_id', $managerId)->first();
    
    if (!$department) {
        return [
            'department' => null,
            'managed_technicians' => [],
        ];
    }
    
    $managedTechnicians = User::role('Técnico')
        ->whereHas('assignedGrievances', function ($query) use ($department) {
            $query->whereHas('project', function ($q) use ($department) {
                $q->where('department_id', $department->id);
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
    
    return [
        'department' => [
            'id' => $department->id,
            'name' => $department->name,
            'description' => $department->description,
        ],
        'managed_technicians' => $managedTechnicians,
    ];
}


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
            ->whereIn('status', ['resolved', 'closed'])
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
            ->whereIn('status', ['resolved', 'closed'])
            ->whereBetween('resolved_at', [$startDate, $endDate])
            ->count();
        
        $months[] = [
            'month' => $month->format('M/Y'),
            'completed' => $completed,
        ];
    }
    
    return $months;
}
}