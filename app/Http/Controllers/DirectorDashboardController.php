<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
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
}