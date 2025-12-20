<?php

namespace App\Http\Controllers;

use App\Models\Grievance;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Jobs\ExportStatisticsJob;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Log;


class StatisticsController extends Controller
{
    /**
     * Display general statistics dashboard
     */
    // StatisticsController.php - Atualize o método index
    public function index(Request $request): Response
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasAnyRole(['Director', 'Gestor', 'PCA'])) {
                abort(403, 'Acesso não autorizado');
            }

            Log::info('Acessando estatísticas - Usuário: ' . $user->id . ', Role: ' . $user->getRoleNames()->first());

            $period = $request->input('period', '12months');
            $validPeriods = ['today', 'week', 'month', '3months', '6months', 'year', '12months'];
            if (!in_array($period, $validPeriods)) $period = '12months';

            $startDate = $this->getStartDate($period);
            $endDate = now();

            $generalStats = $this->getGeneralStats($startDate, $endDate);
            $employeeStats = $this->getEmployeeStats();
            $submissionStats = $this->getSubmissionStats($startDate, $endDate);

            try {
                $chartData = $this->getChartData($startDate, $endDate);
                $topPerformers = $this->getTopPerformers($startDate, $endDate);
                $geographicDistribution = $this->getGeographicDistribution();
                $timeSeriesData = $this->getTimeSeriesData($startDate, $endDate);
                $performanceStats = $this->getPerformanceStats($startDate, $endDate);
            } catch (\Exception $e) {
                Log::warning('Erro ao calcular dados adicionais: ' . $e->getMessage());
                $chartData = [];
                $topPerformers = [];
                $geographicDistribution = [];
                $timeSeriesData = [];
                $performanceStats = ['technician_performance' => []];
            }

            return Inertia::render('Common/StatisticsPage', [
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->getRoleNames()->first(),
                ],
                'period' => $period,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'general_stats' => $generalStats,
                'employee_stats' => $employeeStats,
                'submission_stats' => $submissionStats,
                'chart_data' => $chartData,
                'top_performers' => $topPerformers,
                'geographic_distribution' => $geographicDistribution,
                'time_series_data' => $timeSeriesData,
                'performance_stats' => $performanceStats,
                'canExport' => $user->hasAnyRole(['Gestor', 'Director', 'PCA']),
            ]);

        } catch (\Exception $e) {
            Log::error('Erro no StatisticsController: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return Inertia::render('Error', [
                'message' => 'Erro ao carregar estatísticas',
                'error' => $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Get start date based on period
     */
   private function getStartDate(string $period): Carbon
    {
        return match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->subWeek()->startOfDay(),
            'month' => now()->subMonth()->startOfDay(),
            '3months' => now()->subMonths(3)->startOfDay(),
            '6months' => now()->subMonths(6)->startOfDay(),
            'year' => now()->subYear()->startOfDay(),
            default => now()->subMonths(12)->startOfDay(),
        };
    }

    /**
     * Get general statistics
     */
     private function getGeneralStats(Carbon $startDate, Carbon $endDate): array
    {
        try {
            $totalSubmissions = Grievance::whereBetween('created_at', [$startDate, $endDate])->count();
            $totalResolved = Grievance::whereBetween('resolved_at', [$startDate, $endDate])
                ->where('status', 'resolved')
                ->count();
            $resolutionRate = $totalSubmissions ? round(($totalResolved / $totalSubmissions) * 100, 2) : 0;

            $avgResolutionTime = Grievance::where('status', 'resolved')
                ->whereNotNull('resolved_at')
                ->whereNotNull('assigned_at')
                ->whereBetween('resolved_at', [$startDate, $endDate])
                ->avg(DB::raw('TIMESTAMPDIFF(HOUR, assigned_at, resolved_at)')) ?? 0;

            $pendingSubmissions = Grievance::whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress', 'pending_approval'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $previousPeriod = clone $startDate;
            $previousStartDate = $previousPeriod->sub($endDate->diff($startDate));
            $previousEndDate = $startDate->copy();
            $previousPeriodSubmissions = Grievance::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();

            $growthRate = $previousPeriodSubmissions
                ? round((($totalSubmissions - $previousPeriodSubmissions) / $previousPeriodSubmissions) * 100, 2)
                : ($totalSubmissions ? 100 : 0);

            return [
                'total_submissions' => $totalSubmissions,
                'total_resolved' => $totalResolved,
                'resolution_rate' => $resolutionRate,
                'avg_resolution_time' => round($avgResolutionTime, 1),
                'pending_submissions' => $pendingSubmissions,
                'growth_rate' => $growthRate,
                'submissions_today' => Grievance::whereDate('created_at', today())->count(),
                'submissions_this_week' => Grievance::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'submissions_this_month' => Grievance::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Erro em getGeneralStats: ' . $e->getMessage());
            return array_fill_keys([
                'total_submissions','total_resolved','resolution_rate','avg_resolution_time','pending_submissions','growth_rate',
                'submissions_today','submissions_this_week','submissions_this_month'
            ], 0);
        }
    }


    /**
     * Get submission statistics
     */
     private function getSubmissionStats(Carbon $startDate, Carbon $endDate): array
    {
        $byType = Grievance::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('type, COUNT(*) as count')->groupBy('type')->pluck('count','type')->toArray();

        $byStatus = Grievance::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count','status')->toArray();

        $byPriority = Grievance::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('priority, COUNT(*) as count')->groupBy('priority')->pluck('count','priority')->toArray();

        $byCategory = Grievance::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('category, COUNT(*) as count')->groupBy('category')->orderByDesc('count')->limit(10)
            ->get()->map(fn($item) => ['category'=>$item->category,'count'=>$item->count])->toArray();

        $anonymousCount = Grievance::whereBetween('created_at', [$startDate, $endDate])->where('is_anonymous', true)->count();
        $identifiedCount = Grievance::whereBetween('created_at', [$startDate, $endDate])->where('is_anonymous', false)->count();

        return [
            'by_type' => $byType,
            'by_status' => $byStatus,
            'by_priority' => $byPriority,
            'by_category' => $byCategory,
            'anonymous_vs_identified' => [
                'anonymous' => $anonymousCount,
                'identified' => $identifiedCount,
                'total' => $anonymousCount + $identifiedCount,
            ],
            'escalated_count' => Grievance::whereBetween('created_at', [$startDate, $endDate])->where('escalated', true)->count(),
            'high_priority_count' => Grievance::whereBetween('created_at', [$startDate, $endDate])->where('priority', 'high')->count(),
        ];
    }

    /**
     * Get employee statistics
     */
    
    private function getEmployeeStats(): array
    {
        try {
            $roles = ['Gestor','Técnico'];
            $managers = User::role('Gestor')->get();
            $technicians = User::role('Técnico')->get();
            $activeManagers = $managers->where('is_available', true)->count();
            $activeTechnicians = $technicians->where('is_available', true)->count();
            $avgTasksPerTechnician = $activeTechnicians ? round(Grievance::whereNotNull('assigned_to')->count() / $activeTechnicians,1):0;

            return [
                'by_role'=>['Gestor'=>$managers->count(),'Técnico'=>$technicians->count()],
                'total_employees'=>$managers->count()+$technicians->count(),
                'managers'=>['total'=>$managers->count(),'active'=>$activeManagers,'inactive'=>$managers->count()-$activeManagers,'availability_rate'=>$managers->count()?round($activeManagers/$managers->count()*100,2):0],
                'technicians'=>['total'=>$technicians->count(),'active'=>$activeTechnicians,'inactive'=>$technicians->count()-$activeTechnicians,'availability_rate'=>$technicians->count()?round($activeTechnicians/$technicians->count()*100,2):0],
                'avg_tasks_per_technician'=>$avgTasksPerTechnician,
                'new_employees'=>User::role($roles)->where('created_at','>=',now()->subDays(30))->count(),
                'online_employees'=>User::role($roles)->where('is_available',true)->count(),
                'employee_growth'=>$this->getEmployeeGrowth($roles),
            ];
        } catch (\Exception $e) {
            Log::error('Erro em getEmployeeStats: '.$e->getMessage());
            return [
                'by_role'=>['Gestor'=>0,'Técnico'=>0],
                'total_employees'=>0,
                'managers'=>['total'=>0,'active'=>0,'inactive'=>0,'availability_rate'=>0],
                'technicians'=>['total'=>0,'active'=>0,'inactive'=>0,'availability_rate'=>0],
                'avg_tasks_per_technician'=>0,
                'new_employees'=>0,
                'online_employees'=>0,
                'employee_growth'=>[],
            ];
        }
    }


    /**
     * Get employee growth trend
     */
    private function getEmployeeGrowth(array $roles): array
    {
        $growth=[];
        $now=now();
        for($i=5;$i>=0;$i--){
            $month=$now->copy()->subMonths($i);
            $start=$month->copy()->startOfMonth();
            $end=$month->copy()->endOfMonth();
            $growth[]= [
                'month'=>$month->format('M/Y'),
                'new_employees'=>User::role($roles)->whereBetween('created_at',[$start,$end])->count(),
                'total_employees'=>User::role($roles)->where('created_at','<=',$end)->count()
            ];
        }
        return $growth;
    }
    /**
     * Get performance statistics
     */
    private function getPerformanceStats(Carbon $startDate, Carbon $endDate): array
    {
        // Taxa de resolução por mês
        $monthlyResolutionRates = [];
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();
            
            $monthSubmissions = Grievance::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $monthResolved = Grievance::whereBetween('resolved_at', [$monthStart, $monthEnd])
                ->where('status', 'resolved')
                ->count();
            
            $monthlyResolutionRates[] = [
                'month' => $current->format('M/Y'),
                'submissions' => $monthSubmissions,
                'resolved' => $monthResolved,
                'rate' => $monthSubmissions > 0 ? round(($monthResolved / $monthSubmissions) * 100, 2) : 0,
            ];
            
            $current->addMonth();
        }

        // Desempenho por técnico
        $technicianPerformance = User::role('Técnico')
            ->where('is_available', true)
            ->withCount([
                'assignedGrievances as total_tasks',
                'assignedGrievances as completed_tasks' => function ($query) use ($startDate, $endDate) {
                    $query->where('status', 'resolved')
                        ->whereBetween('resolved_at', [$startDate, $endDate]);
                },
                'assignedGrievances as pending_tasks' => function ($query) {
                    $query->whereIn('status', ['assigned', 'in_progress']);
                }
            ])
            ->get()
            ->map(function ($technician) {
                $avgResolutionTime = Grievance::where('assigned_to', $technician->id)
                    ->where('status', 'resolved')
                    ->whereNotNull('resolved_at')
                    ->whereNotNull('assigned_at')
                    ->avg(DB::raw('TIMESTAMPDIFF(HOUR, assigned_at, resolved_at)')) ?? 0;
                
                return [
                    'id' => $technician->id,
                    'name' => $technician->name,
                    'total_tasks' => $technician->total_tasks,
                    'completed_tasks' => $technician->completed_tasks,
                    'pending_tasks' => $technician->pending_tasks,
                    'completion_rate' => $technician->total_tasks > 0 
                        ? round(($technician->completed_tasks / $technician->total_tasks) * 100, 2)
                        : 0,
                    'avg_resolution_time' => round($avgResolutionTime, 1),
                ];
            })
            ->sortByDesc('completion_rate')
            ->values()
            ->take(10)
            ->toArray();

        // Taxa de satisfação (se tiver avaliações)
        $satisfactionRate = 0;
        $totalRatings = 0;
        $averageRating = 0;

        return [
            'monthly_resolution_rates' => $monthlyResolutionRates,
            'technician_performance' => $technicianPerformance,
            'satisfaction_rate' => $satisfactionRate,
            'total_ratings' => $totalRatings,
            'average_rating' => $averageRating,
            'first_response_time' => $this->getAverageFirstResponseTime($startDate, $endDate),
            'escalation_rate' => $this->getEscalationRate($startDate, $endDate),
        ];
    }

    /**
     * Get average first response time
     */
    private function getAverageFirstResponseTime(Carbon $startDate, Carbon $endDate): float
    {
        // Calcular tempo médio até a primeira resposta/comentário
        $firstResponseTimes = DB::table('grievance_updates')
            ->join('grievances', 'grievance_updates.grievance_id', '=', 'grievances.id')
            ->whereBetween('grievances.created_at', [$startDate, $endDate])
            ->whereNotNull('grievance_updates.created_at')
            ->selectRaw('TIMESTAMPDIFF(HOUR, grievances.created_at, grievance_updates.created_at) as response_time')
            ->orderBy('grievance_updates.created_at')
            ->get()
            ->pluck('response_time');
        
        if ($firstResponseTimes->count() > 0) {
            return round($firstResponseTimes->avg(), 1);
        }
        
        return 2.5; // Valor padrão se não houver dados
    }

    /**
     * Get escalation rate
     */
    private function getEscalationRate(Carbon $startDate, Carbon $endDate): float
    {
        $totalSubmissions = Grievance::whereBetween('created_at', [$startDate, $endDate])->count();
        $escalatedSubmissions = Grievance::whereBetween('created_at', [$startDate, $endDate])
            ->where('escalated', true)
            ->count();
        
        return $totalSubmissions > 0 ? round(($escalatedSubmissions / $totalSubmissions) * 100, 2) : 0;
    }

    /**
     * Get chart data
     */
    private function getChartData(Carbon $startDate, Carbon $endDate): array
    {
        // Paleta de cores para gráficos
        $colorPalette = [
            'primary' => 'rgb(59, 130, 246)',    // blue-500
            'secondary' => 'rgb(139, 92, 246)',  // violet-500
            'success' => 'rgb(34, 197, 94)',     // green-500
            'warning' => 'rgb(245, 158, 11)',    // yellow-500
            'danger' => 'rgb(239, 68, 68)',      // red-500
            'info' => 'rgb(6, 182, 212)',        // cyan-500
        ];
        
        // 1. GRÁFICO DE LINHA: Submissões por Dia
        $dailyData = [];
        $dailyLabels = [];
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $count = Grievance::whereDate('created_at', $current)->count();
            $dailyData[] = $count;
            $dailyLabels[] = $current->format('d/m');
            $current->addDay();
        }
        
        $dailySubmissionsChart = [
            'labels' => $dailyLabels,
            'datasets' => [
                [
                    'label' => 'Submissões',
                    'data' => $dailyData,
                    'borderColor' => $colorPalette['primary'],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.3,
                ]
            ]
        ];
        
        // 2. GRÁFICO DE PIZZA: Distribuição por Status
        $statusData = Grievance::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
        
        $statusLabels = [];
        $statusValues = [];
        $statusColors = [
            'submitted' => $colorPalette['primary'],      // Azul
            'under_review' => $colorPalette['warning'],   // Amarelo
            'assigned' => $colorPalette['secondary'],     // Violeta
            'in_progress' => $colorPalette['info'],       // Ciano
            'pending_approval' => 'rgb(249, 115, 22)',    // Laranja
            'resolved' => $colorPalette['success'],       // Verde
            'rejected' => $colorPalette['danger'],        // Vermelho
            'cancelled' => 'rgb(107, 114, 128)',          // Cinza
        ];
        
        $statusBackgroundColors = [];
        
        foreach ($statusData as $item) {
            $statusLabels[] = $this->getStatusLabel($item->status);
            $statusValues[] = $item->count;
            $statusBackgroundColors[] = $statusColors[$item->status] ?? $colorPalette['primary'];
        }
        
        $statusDistributionChart = [
            'labels' => $statusLabels,
            'datasets' => [
                [
                    'label' => 'Distribuição por Status',
                    'data' => $statusValues,
                    'backgroundColor' => $statusBackgroundColors,
                    'borderColor' => array_map(function($color) {
                        return str_replace('rgb', 'rgba', $color) . ', 0.8)';
                    }, $statusBackgroundColors),
                    'borderWidth' => 1,
                ]
            ]
        ];
        
        // 3. GRÁFICO DE BARRAS: Distribuição por Tipo
        $typeData = Grievance::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();
        
        $typeLabels = [];
        $typeValues = [];
        
        foreach ($typeData as $item) {
            $typeLabels[] = $this->getTypeLabel($item->type);
            $typeValues[] = $item->count;
        }
        
        $typeDistributionChart = [
            'labels' => $typeLabels,
            'datasets' => [
                [
                    'label' => 'Submissões por Tipo',
                    'data' => $typeValues,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.7)',  // Queixa
                        'rgba(239, 68, 68, 0.7)',   // Reclamação
                        'rgba(34, 197, 94, 0.7)',   // Sugestão
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(239, 68, 68)',
                        'rgb(34, 197, 94)',
                    ],
                    'borderWidth' => 1,
                ]
            ]
        ];
        
        // 4. GRÁFICO DONUT: Resolvidas vs Pendentes
        $resolvedCount = Grievance::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'resolved')
            ->count();
        
        $pendingCount = Grievance::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress', 'pending_approval'])
            ->count();
        
        $cancelledCount = Grievance::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['rejected', 'cancelled'])
            ->count();
        
        $resolvedVsPendingChart = [
            'labels' => ['Resolvidas', 'Pendentes', 'Canceladas'],
            'datasets' => [
                [
                    'label' => 'Status de Submissões',
                    'data' => [$resolvedCount, $pendingCount, $cancelledCount],
                    'backgroundColor' => [
                        $colorPalette['success'],      // Verde para resolvidas
                        $colorPalette['warning'],      // Amarelo para pendentes
                        $colorPalette['danger'],       // Vermelho para canceladas
                    ],
                    'borderColor' => [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                    ],
                    'borderWidth' => 2,
                ]
            ]
        ];
        
        // 5. GRÁFICO DE BARRAS HORIZONTAIS: Top Províncias
        $provinceData = Grievance::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('province')
            ->selectRaw('province, COUNT(*) as count')
            ->groupBy('province')
            ->orderByDesc('count')
            ->limit(8)
            ->get();
        
        $provinceLabels = [];
        $provinceValues = [];
        
        foreach ($provinceData as $item) {
            $provinceLabels[] = $item->province;
            $provinceValues[] = $item->count;
        }
        
        $provinceDistributionChart = [
            'labels' => $provinceLabels,
            'datasets' => [
                [
                    'label' => 'Submissões por Província',
                    'data' => $provinceValues,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.7)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 1,
                ]
            ]
        ];
        
        return [
            'daily_submissions_chart' => $dailySubmissionsChart,
            'status_distribution_chart' => $statusDistributionChart,
            'type_distribution_chart' => $typeDistributionChart,
            'resolved_vs_pending_chart' => $resolvedVsPendingChart,
            'province_distribution_chart' => $provinceDistributionChart,
            
            // Mantenha os dados originais para compatibilidade
            'daily_submissions' => $this->getDailySubmissionsArray($startDate, $endDate),
            'status_distribution' => $statusData->pluck('count', 'status')->toArray(),
            'type_distribution' => $typeData->pluck('count', 'type')->toArray(),
        ];
    }

    /**
     * Get daily submissions array
     */
    private function getDailySubmissionsArray(Carbon $startDate, Carbon $endDate): array
    {
        $data = [];
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $data[] = [
                'date' => $current->format('Y-m-d'),
                'count' => Grievance::whereDate('created_at', $current)->count(),
            ];
            $current->addDay();
        }
        
        return $data;
    }

    /**
     * Get status label in Portuguese
     */
    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'submitted' => 'Submetida',
            'under_review' => 'Em Revisão',
            'assigned' => 'Atribuída',
            'in_progress' => 'Em Progresso',
            'pending_approval' => 'Aprovação Pendente',
            'resolved' => 'Resolvida',
            'rejected' => 'Rejeitada',
            'cancelled' => 'Cancelada',
            default => ucfirst($status),
        };
    }

    /**
     * Get type label in Portuguese
     */
    private function getTypeLabel(string $type): string
    {
        return match($type) {
            'grievance' => 'Queixa',
            'complaint' => 'Reclamação',
            'suggestion' => 'Sugestão',
            default => ucfirst($type),
        };
    }

    /**
     * Get top performers
     */
     private function getTopPerformers(Carbon $startDate, Carbon $endDate): array
    {
        try {
            return User::role('Técnico')
                ->where('is_available', true)
                ->withCount([
                    'assignedGrievances as resolved_count' => function ($query) use ($startDate, $endDate) {
                        $query->where('status', 'resolved')
                            ->whereBetween('resolved_at', [$startDate, $endDate]);
                    }
                ])
                ->orderByDesc('resolved_count')
                ->limit(5)
                ->get()
                ->map(function ($technician) {
                    return [
                        'id' => $technician->id,
                        'name' => $technician->name,
                        'resolved_count' => $technician->resolved_count,
                        'avatar' => $technician->avatar_url ?? null,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            \Log::error('Erro em getTopPerformers: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get geographic distribution
     */
   private function getGeographicDistribution(): array
    {
        try {
            return Grievance::selectRaw('province, COUNT(*) as count')
                ->whereNotNull('province')
                ->groupBy('province')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'province' => $item->province,
                        'count' => $item->count,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            \Log::error('Erro em getGeographicDistribution: ' . $e->getMessage());
            return [];
        }
    }


    /**
     * Get time series data
     */
   private function getTimeSeriesData(Carbon $startDate, Carbon $endDate): array
    {
        try {
            $data = [];
            $current = $startDate->copy();
            
            while ($current <= $endDate) {
                $weekStart = $current->copy()->startOfWeek();
                $weekEnd = $current->copy()->endOfWeek();
                
                $submissions = Grievance::whereBetween('created_at', [$weekStart, $weekEnd])->count();
                $resolved = Grievance::whereBetween('resolved_at', [$weekStart, $weekEnd])
                    ->where('status', 'resolved')
                    ->count();
                
                $data[] = [
                    'week' => $weekStart->format('d/m'),
                    'submissions' => $submissions,
                    'resolved' => $resolved,
                ];
                
                $current->addWeek();
            }
            
            return $data;
        } catch (\Exception $e) {
            \Log::error('Erro em getTimeSeriesData: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Export statistics to PDF/Excel
     */
    public function export(Request $request)
    {
        $user = $request->user();
        abort_if(!$user || !$user->hasAnyRole(['Gestor', 'Director', 'PCA']),403);
        return response()->json(['message'=>'Export functionality to be implemented']);
    }

    /**
     * Get statistics for API/Widgets
     */
    public function apiStats(Request $request)
    {
        $period = $request->input('period','month');
        $startDate=$this->getStartDate($period);
        $endDate=now();
        $stats=[
            'general'=>$this->getGeneralStats($startDate,$endDate),
            'submissions'=>$this->getSubmissionStats($startDate,$endDate),
            'employees'=>$this->getEmployeeStats(),
            'period'=>$period,
        ];
        return response()->json($stats);
    }


     public function exportAsync(Request $request)
    {
        $request->validate(['period'=>'required|string','format'=>'required|in:xlsx,csv']);
        ExportStatisticsJob::dispatch($request->period,$request->format,auth()->id());
        return response()->json(['status'=>'queued','message'=>'Exportação enviada para fila']);
    }

    // ✔ NOVO — listar exportações prontas
    public function exportStatus()
    {
        $files=Storage::disk('public')->files('exports');
        return collect($files)->map(fn($file)=>[
            'filename'=>basename($file),
            'url'=>Storage::disk('public')->url($file),
            'created_at'=>Storage::disk('public')->lastModified($file),
        ])->sortByDesc('created_at')->values();
    }
}