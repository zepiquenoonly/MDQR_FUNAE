<?php

namespace App\Http\Controllers;

use App\Models\DepartmentIndicator;
use App\Jobs\GenerateReportJob;
use App\Models\IndicatorRecord;
use App\Models\Grievance;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentIndicatorController extends Controller
{
    /**
     * Verifica se o usuário tem acesso aos indicadores
     */
   private function checkAccess($user)
    {
        if (!$user) {
            abort(403, 'Usuário não autenticado.');
        }
        
        // VERIFICAÇÃO COM SPATIE PERMISSION
        if (!$user->hasAnyRole(['Gestor', 'PCA', 'Director'])) {
            abort(403, 'Acesso não autorizado aos indicadores.');
        }
    }
    
    /**
     * Display department indicators dashboard
     */
   public function dashboard(Request $request): Response
{
    $user = $request->user();
    $this->checkAccess($user);

    $timeRange = $request->input('time_range', 'month');
    $category = $request->input('category', 'all');

    // Calculate date range
    $endDate = Carbon::now();
    $startDate = match($timeRange) {
        'week' => $endDate->copy()->subWeek(),
        'month' => $endDate->copy()->subMonth(),
        'quarter' => $endDate->copy()->subMonths(3),
        'year' => $endDate->copy()->subYear(),
        default => $endDate->copy()->subMonth(),
    };

    // Get indicators
    $indicatorsQuery = DepartmentIndicator::where('is_active', true);
    
    if ($category !== 'all') {
        $indicatorsQuery->where('category', $category);
    }

    $indicators = $indicatorsQuery->orderBy('display_order')->get();

    // Calculate values for each indicator
    $indicatorsData = $indicators->map(function ($indicator) use ($startDate, $endDate) {
        $records = $indicator->records()
            ->whereBetween('record_date', [$startDate, $endDate])
            ->orderBy('record_date')
            ->get();

        // Calculate current value
        $currentValue = $this->calculateIndicatorValue($indicator, $endDate);

        // Calculate trend
        $previousStartDate = $startDate->copy()->sub($endDate->diff($startDate));
        $previousEndDate = $startDate->copy();
        $previousValue = $this->calculateIndicatorValue($indicator, $previousEndDate);
        
        $trend = $previousValue > 0 
            ? (($currentValue - $previousValue) / $previousValue) * 100 
            : null;

        // Performance against target
        $performance = $indicator->target_value > 0
            ? ($currentValue / $indicator->target_value) * 100
            : null;

        return [
            'id' => $indicator->id,
            'name' => $indicator->name,
            'description' => $indicator->description,
            'category' => $indicator->category,
            'current_value' => $currentValue,
            'formatted_value' => $this->formatValue($currentValue, $indicator->measurement_unit),
            'target_value' => $indicator->target_value,
            'trend' => $trend,
            'performance' => $performance,
            'measurement_unit' => $indicator->measurement_unit,
            'is_active' => $indicator->is_active,
            'records' => $records->map(function ($record) use ($indicator) {
                return [
                    'date' => $record->record_date->format('Y-m-d'),
                    'value' => $record->value,
                    'formatted_value' => $this->formatValue($record->value, $indicator->measurement_unit),
                    'breakdown' => $record->breakdown
                ];
            })
        ];
    });

    // Get grievance statistics for charts WITH TRENDS
    $grievanceStats = $this->getGrievanceStatistics($startDate, $endDate);

    // Get technician performance
    $technicianPerformance = $this->getTechnicianPerformance($startDate, $endDate);

    // Get category distribution
    $categoryDistribution = $this->getCategoryDistribution($startDate, $endDate);

    // Get resolution timeline
    $resolutionTimeline = $this->getResolutionTimeline($startDate, $endDate);

    // RENDERIZE DIRETAMENTE A PÁGINA CORRETA
    return Inertia::render('Manager/IndicatorDashboard', [
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->first()->name ?? $user->role ?? 'Gestor',
            'created_at' => $user->created_at?->format('d/m/Y'),
        ],
        'indicators' => $indicatorsData,
        'grievanceStats' => $grievanceStats,
        'technicianPerformance' => $technicianPerformance,
        'categoryDistribution' => $categoryDistribution,
        'resolutionTimeline' => $resolutionTimeline,
        'timeRange' => $timeRange,
        'categoryFilter' => $category,
        'dateRange' => [
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate->format('Y-m-d'),
        ],
        'stats' => [
            'pending_complaints' => $grievanceStats['pending'] ?? 0,
            'in_progress' => $grievanceStats['in_progress'] ?? 0,
            'high_priority' => $grievanceStats['high_priority'] ?? 0,
            'pending_completion_requests' => $grievanceStats['pending_completion_requests'] ?? 0,
        ]
    ]);
}

public function directorDashboard(Request $request): Response
{
    $user = $request->user();
    
    // Verificar se é Director
    if (!$user->hasRole(['Gestor', 'PCA', 'Director'])) {
        abort(403, 'Acesso não autorizado.');
    }

    $timeRange = $request->input('time_range', 'month');
    $category = $request->input('category', 'all');

    // Calculate date range
    $endDate = Carbon::now();
    $startDate = match($timeRange) {
        'week' => $endDate->copy()->subWeek(),
        'month' => $endDate->copy()->subMonth(),
        'quarter' => $endDate->copy()->subMonths(3),
        'year' => $endDate->copy()->subYear(),
        default => $endDate->copy()->subMonth(),
    };

    // Get ALL indicators (Director vê todos os departamentos)
    $indicatorsQuery = DepartmentIndicator::where('is_active', true);
    
    if ($category !== 'all') {
        $indicatorsQuery->where('category', $category);
    }

    $indicators = $indicatorsQuery->orderBy('display_order')->get();

    // Calculate values for each indicator
    $indicatorsData = $indicators->map(function ($indicator) use ($startDate, $endDate) {
        $records = $indicator->records()
            ->whereBetween('record_date', [$startDate, $endDate])
            ->orderBy('record_date')
            ->get();

        // Calculate current value
        $currentValue = $this->calculateIndicatorValue($indicator, $endDate);

        // Calculate trend
        $previousStartDate = $startDate->copy()->sub($endDate->diff($startDate));
        $previousEndDate = $startDate->copy();
        $previousValue = $this->calculateIndicatorValue($indicator, $previousEndDate);
        
        $trend = $previousValue > 0 
            ? (($currentValue - $previousValue) / $previousValue) * 100 
            : null;

        // Performance against target
        $performance = $indicator->target_value > 0
            ? ($currentValue / $indicator->target_value) * 100
            : null;

        return [
            'id' => $indicator->id,
            'name' => $indicator->name,
            'description' => $indicator->description,
            'category' => $indicator->category,
            'current_value' => $currentValue,
            'formatted_value' => $this->formatValue($currentValue, $indicator->measurement_unit),
            'target_value' => $indicator->target_value,
            'trend' => $trend,
            'performance' => $performance,
            'measurement_unit' => $indicator->measurement_unit,
            'is_active' => $indicator->is_active,
            'records' => $records->map(function ($record) use ($indicator) {
                return [
                    'date' => $record->record_date->format('Y-m-d'),
                    'value' => $record->value,
                    'formatted_value' => $this->formatValue($record->value, $indicator->measurement_unit),
                    'breakdown' => $record->breakdown
                ];
            })
        ];
    });

    // Get grievance statistics for ALL departments
    $grievanceStats = $this->getDirectorGrievanceStatistics($startDate, $endDate);

    // Get technician performance from ALL departments
    $technicianPerformance = $this->getDirectorTechnicianPerformance($startDate, $endDate);

    // Get category distribution from ALL departments
    $categoryDistribution = $this->getDirectorCategoryDistribution($startDate, $endDate);

    // Get resolution timeline from ALL departments
    $resolutionTimeline = $this->getDirectorResolutionTimeline($startDate, $endDate);

    // Get department performance comparison
    $departmentPerformance = $this->getDepartmentPerformance($startDate, $endDate);

    // Renderize a mesma view do Manager, mas com dados de todos os departamentos
    return Inertia::render('Manager/IndicatorDashboard', [
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'Director',
            'created_at' => $user->created_at?->format('d/m/Y'),
        ],
        'indicators' => $indicatorsData,
        'grievanceStats' => $grievanceStats,
        'technicianPerformance' => $technicianPerformance,
        'categoryDistribution' => $categoryDistribution,
        'resolutionTimeline' => $resolutionTimeline,
        'departmentPerformance' => $departmentPerformance,
        'timeRange' => $timeRange,
        'categoryFilter' => $category,
        'dateRange' => [
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate->format('Y-m-d'),
        ],
        'stats' => [
            'pending_complaints' => $grievanceStats['pending'] ?? 0,
            'in_progress' => $grievanceStats['in_progress'] ?? 0,
            'high_priority' => $grievanceStats['high_priority'] ?? 0,
            'pending_completion_requests' => $grievanceStats['pending_completion_requests'] ?? 0,
        ],
        'isDirectorView' => true,
        'totalDepartments' => count($departmentPerformance),
        'companyWideStats' => [
            'total_indicators' => count($indicatorsData),
            'total_technicians' => count($technicianPerformance),
            'average_performance' => $indicatorsData->avg('performance') ?? 0,
        ]
    ]);
}


private function getEmployeeStats(): array
{
    // Total de funcionários por role (usando Spatie Permission)
    $byRole = [
        'Gestor' => User::role('Gestor')->count(),
        'Director' => User::role('Director')->count(),
        'PCA' => User::role('PCA')->count(),
        'Técnico' => User::role('Técnico')->count(),
        'Utente' => User::role('Utente')->count(),
    ];

    // Técnicos ativos vs inativos
    $technicians = User::role('Técnico')->get();
    $activeTechnicians = $technicians->where('is_available', true)->count();
    $inactiveTechnicians = $technicians->where('is_available', false)->count();

    // Média de tarefas por técnico (baseado nas reclamações atribuídas)
    $totalTasksAssigned = Grievance::whereNotNull('assigned_to')
        ->whereHas('assignedTo', function ($query) {
            $query->role('Técnico');
        })
        ->count();
    
    $avgTasksPerTechnician = $activeTechnicians > 0 
        ? round($totalTasksAssigned / $activeTechnicians, 1)
        : 0;

    // Novos funcionários (últimos 30 dias) - apenas Técnicos
    $newTechnicians = User::role('Técnico')
        ->where('created_at', '>=', now()->subDays(30))
        ->count();

    // Funcionários ativos (apenas Técnicos)
    $onlineTechnicians = User::role('Técnico')
        ->where('is_available', true)
        ->where('updated_at', '>=', now()->subHours(2))
        ->count();

    return [
        'by_role' => $byRole,
        'total_employees' => array_sum($byRole),
        'technicians' => [
            'total' => $technicians->count(),
            'active' => $activeTechnicians,
            'inactive' => $inactiveTechnicians,
            'availability_rate' => $technicians->count() > 0 
                ? round(($activeTechnicians / $technicians->count()) * 100, 2)
                : 0,
        ],
        'avg_tasks_per_technician' => $avgTasksPerTechnician,
        'new_employees' => $newTechnicians,
        'online_employees' => $onlineTechnicians,
        'employee_growth' => $this->getEmployeeGrowth(),
    ];
}


private function getEmployeeGrowth(): array
{
    $growth = [];
    $now = now();
    
    for ($i = 5; $i >= 0; $i--) {
        $month = $now->copy()->subMonths($i);
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();
        
        // Apenas novos técnicos
        $newTechnicians = User::role('Técnico')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        
        // Total de técnicos até o final do mês
        $totalTechnicians = User::role('Técnico')
            ->where('created_at', '<=', $endDate)
            ->count();
        
        $growth[] = [
            'month' => $month->format('M/Y'),
            'new_employees' => $newTechnicians,
            'total_employees' => $totalTechnicians,
        ];
    }
    
    return $growth;
}


private function getDirectorGrievanceStatistics(Carbon $startDate, Carbon $endDate): array
{
    return $this->getGrievanceStatistics($startDate, $endDate);
}

private function getDirectorTechnicianPerformance(Carbon $startDate, Carbon $endDate): array
{
    return $this->getTechnicianPerformance($startDate, $endDate);
}

/**
 * Get category distribution from ALL departments (Director view)
 */
private function getDirectorCategoryDistribution(Carbon $startDate, Carbon $endDate): array
{
    return $this->getCategoryDistribution($startDate, $endDate);
}

/**
 * Get resolution timeline from ALL departments (Director view)
 */
private function getDirectorResolutionTimeline(Carbon $startDate, Carbon $endDate): array
{
    return $this->getResolutionTimeline($startDate, $endDate);
}


private function getDepartmentPerformance(Carbon $startDate, Carbon $endDate): array
{
    return [];
    
}
    /**
     * Generate report
     */
    public function generateReport(Request $request)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $validated = $request->validate([
            'report_type' => 'required|in:monthly,quarterly,annual,custom',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,excel,html',
            'indicators' => 'nullable|array',
            'sections' => 'nullable|array',
            'filters' => 'nullable|array'
        ]);

        // Create report record
        $report = Report::create([
            'name' => $this->generateReportName($validated),
            'type' => $validated['report_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'format' => $validated['format'],
            'parameters' => [
                'indicators' => $validated['indicators'] ?? [],
                'sections' => $validated['sections'] ?? ['summary', 'charts', 'detailed'],
                'filters' => $validated['filters'] ?? []
            ],
            'status' => 'generating',
            'generated_by' => $user->id
        ]);

        // Queue report generation
        dispatch(new GenerateReportJob($report->id, $validated));

        return response()->json([
            'success' => true,
            'message' => 'Relatório em geração. Você será notificado quando estiver pronto.',
            'report_id' => $report->id
        ]);
    }

private function formatIndicatorValue($value, $unit): string
{
    if (!is_numeric($value)) {
        return 'N/A';
    }
    
    $numValue = (float) $value;
    
    return match($unit) {
        'percentage' => number_format($numValue, 1) . '%',
        'days' => number_format($numValue, 1) . ' dias',
        'count' => number_format($numValue, 0),
        'rating' => number_format($numValue, 1) . '/5',
        default => number_format($numValue, 2) . ' ' . $unit
    };
}



    private function prepareIndicatorsForExportView(array $indicators): array
{
    return array_map(function ($indicator) {
        return [
            'name' => $indicator['name'] ?? 'Indicador sem nome',
            'category' => $indicator['category'] ?? 'uncategorized',
            'formatted_value' => $this->formatIndicatorValue($indicator['current_value'] ?? 0, $indicator['measurement_unit'] ?? 'count'),
            'target_value' => $indicator['target_value'] ?? null,
            'performance' => $indicator['performance'] ?? null,
            'trend' => $indicator['trend'] ?? null,
            'measurement_unit' => $indicator['measurement_unit'] ?? 'count',
            'is_active' => $indicator['is_active'] ?? true
        ];
    }, $indicators);
}



    private function generatePdfReport(Report $report, array $data): string
{
    // Usar a view específica para relatórios completos
    $viewName = 'reports.department-indicators';
    
    // Verificar se a view existe
    if (!view()->exists($viewName)) {
        // Fallback para a view de exportação
        $viewName = 'exports.indicators-pdf';
        
        // Preparar dados para a view de exportação
        $data['indicators'] = $this->prepareIndicatorsForExportView($data['indicators']);
        $data['total_indicators'] = count($data['indicators']);
    }
    
    $pdf = Pdf::loadView($viewName, $data);
    
    $fileName = 'relatorio-' . $report->id . '-' . now()->format('Y-m-d-H-i-s') . '.pdf';
    $filePath = 'reports/' . $fileName;
    
    Storage::put('public/' . $filePath, $pdf->output());
    
    return $filePath;
}

    /**
     * Export indicators data
     */
   /**
 * Export indicators data - Suporta GET e POST
 */
public function exportData(Request $request)
{
    $user = $request->user();
    $this->checkAccess($user);

    try {
        $format = $request->input('format', 'excel');
        $indicatorIds = $request->input('indicators', []);
        $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        Log::info('ExportData chamado', [
            'format' => $format,
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'user_agent' => $request->header('User-Agent')
        ]);

        // Validar indicadores
        if (empty($indicatorIds)) {
            $indicatorIds = DepartmentIndicator::where('is_active', true)
                ->pluck('id')
                ->toArray();
        }

        if (empty($indicatorIds)) {
            return response()->json([
                'error' => 'Nenhum indicador ativo encontrado para exportação'
            ], 404);
        }

        if ($format === 'excel') {
            return $this->exportToExcel($indicatorIds, $startDate, $endDate);
        } elseif ($format === 'pdf') {
            return $this->exportToPdf($indicatorIds, $startDate, $endDate);
        }
        
        return response()->json(['error' => 'Formato não suportado'], 400);
        
    } catch (\Exception $e) {
        Log::error('Erro em exportData: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'error' => 'Erro interno: ' . $e->getMessage(),
            'type' => get_class($e)
        ], 500);
    }
}


    private function getIndicatorsForExport($indicatorIds, $startDate, $endDate): array
{
    try {
        Log::info('Buscando indicadores para exportação', [
            'indicator_ids' => $indicatorIds,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        
        $indicators = DepartmentIndicator::whereIn('id', $indicatorIds)->get();
        
        if ($indicators->isEmpty()) {
            Log::warning('Nenhum indicador encontrado com os IDs fornecidos');
            return [];
        }
        
        $result = $indicators->map(function ($indicator) use ($startDate, $endDate) {
            try {
                // Obter registros do período
                $records = $indicator->records()
                    ->whereBetween('record_date', [$startDate, $endDate])
                    ->orderBy('record_date', 'desc')
                    ->get();

                // Calcular valor atual (último registro ou 0)
                $currentValue = $records->isNotEmpty() ? (float) $records->first()->value : 0.0;
                
                // Formatar valor
                $formattedValue = $this->formatValue($currentValue, $indicator->measurement_unit ?? 'count');
                
                // Calcular tendência se houver registros suficientes
                $trend = null;
                if ($records->count() > 1) {
                    $firstValue = (float) $records->last()->value;
                    $lastValue = (float) $records->first()->value;
                    if ($firstValue > 0) {
                        $trend = (($lastValue - $firstValue) / $firstValue) * 100;
                    }
                }

                // Calcular desempenho contra meta
                $performance = null;
                if ($indicator->target_value > 0) {
                    $performance = ($currentValue / $indicator->target_value) * 100;
                }

                return [
                    'id' => $indicator->id,
                    'name' => $indicator->name ?? 'Indicador sem nome',
                    'description' => $indicator->description ?? '',
                    'category' => $indicator->category ?? 'uncategorized',
                    'current_value' => $currentValue,
                    'formatted_value' => $formattedValue,
                    'target_value' => $indicator->target_value ?? null,
                    'trend' => $trend,
                    'performance' => $performance,
                    'measurement_unit' => $indicator->measurement_unit ?? 'count',
                    'is_active' => $indicator->is_active ?? false,
                    'records_count' => $records->count(),
                    'calculation_formula' => $indicator->calculation_formula ?? ''
                ];
            } catch (\Exception $e) {
                Log::error('Erro ao processar indicador ' . $indicator->id . ': ' . $e->getMessage());
                // Retornar indicador com valores padrão
                return [
                    'id' => $indicator->id,
                    'name' => $indicator->name ?? 'Indicador sem nome',
                    'description' => $indicator->description ?? '',
                    'category' => $indicator->category ?? 'uncategorized',
                    'current_value' => 0,
                    'formatted_value' => '0',
                    'target_value' => $indicator->target_value ?? null,
                    'trend' => null,
                    'performance' => null,
                    'measurement_unit' => $indicator->measurement_unit ?? 'count',
                    'is_active' => $indicator->is_active ?? false,
                    'records_count' => 0,
                    'calculation_formula' => $indicator->calculation_formula ?? ''
                ];
            }
        })->filter()->toArray(); // Remove null values
        
        Log::info('Indicadores processados para exportação', ['count' => count($result)]);
        
        return $result;
        
    } catch (\Exception $e) {
        Log::error('Erro ao obter indicadores para exportação: ' . $e->getMessage());
        return [];
    }
}



public function downloadExport(Request $request)
{
    $user = $request->user();
    $this->checkAccess($user);

    // Validação para parâmetros GET
    $validator = Validator::make($request->all(), [
        'format' => 'required|in:excel,pdf',
        'indicators' => 'nullable|string',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    $format = $request->query('format', 'excel');
    $indicatorIdsStr = $request->query('indicators', '');
    $startDate = $request->query('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
    $endDate = $request->query('end_date', Carbon::now()->format('Y-m-d'));

    // Converter string de indicadores para array
    $indicatorIds = $indicatorIdsStr ? explode(',', $indicatorIdsStr) : [];

    Log::info('Download de exportação via GET', [
        'format' => $format,
        'indicator_ids' => $indicatorIds,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'user_id' => $user->id,
    ]);

    try {
        // Se não especificou indicadores, pega todos ativos
        if (empty($indicatorIds)) {
            $indicatorIds = DepartmentIndicator::where('is_active', true)
                ->pluck('id')
                ->toArray();
        }

        if ($format === 'excel') {
            return $this->exportToExcel($indicatorIds, $startDate, $endDate);
        } elseif ($format === 'pdf') {
            return $this->exportToPdf($indicatorIds, $startDate, $endDate);
        }
        
        return redirect()->back()->with('error', 'Formato não suportado');
        
    } catch (\Exception $e) {
        Log::error('Erro no download de exportação: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->back()->with('error', 'Erro ao baixar: ' . $e->getMessage());
    }
}


     public function downloadReport(Report $report)
    {
        $user = auth()->user();
        $this->checkAccess($user);
        
        // Verificar se o usuário tem permissão para baixar este relatório
        if ($report->generated_by !== $user->id && !$user->hasRole('Gestor')) {
            abort(403, 'Você não tem permissão para baixar este relatório.');
        }
        
        if (!$report->file_path || $report->status !== 'completed') {
            abort(404, 'Relatório não disponível ou ainda em geração.');
        }
        
        $filePath = storage_path('app/public/' . $report->file_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'Arquivo não encontrado.');
        }
        
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $fileName = $report->name . '.' . $extension;
        
        return response()->download($filePath, $fileName);
    }

     private function exportToPdf($indicatorIds, $startDate, $endDate)
{
    try {
        Log::info('=== INICIANDO EXPORTAÇÃO PDF ===', [
            'indicator_ids' => $indicatorIds,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user' => auth()->user()->name
        ]);
        
        $indicators = $this->getIndicatorsForExport($indicatorIds, $startDate, $endDate);
        
        Log::info('Indicadores obtidos', [
            'count' => count($indicators),
            'indicators' => array_map(function($ind) {
                return ['name' => $ind['name'] ?? 'sem nome', 'id' => $ind['id'] ?? null];
            }, $indicators)
        ]);
        
        if (empty($indicators)) {
            Log::warning('Nenhum indicador encontrado para exportação');
            throw new \Exception('Nenhum indicador encontrado');
        }
        
        // Preparar dados para view
        $data = [
            'indicators' => $indicators,
            'period' => [
                'start' => Carbon::parse($startDate)->format('d/m/Y'),
                'end' => Carbon::parse($endDate)->format('d/m/Y'),
            ],
            'generated_at' => Carbon::now()->format('d/m/Y H:i'),
            'total_indicators' => count($indicators)
        ];
        
        Log::info('Dados preparados para view', ['data_keys' => array_keys($data)]);
        
        // Testar se a view existe
        if (!view()->exists('exports.indicators-pdf')) {
            Log::error('View exports.indicators-pdf não encontrada');
            throw new \Exception('Template de PDF não encontrado');
        }
        
        // Renderizar a view para debug
        $html = view('exports.indicators-pdf', $data)->render();
        Log::info('View renderizada', ['html_length' => strlen($html)]);
        
        // Configurar DomPDF
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('tempDir', sys_get_temp_dir()); // Usar diretório temporário do sistema
        
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        
        Log::info('Renderizando PDF...');
        $dompdf->render();
        
        $output = $dompdf->output();
        
        Log::info('PDF renderizado', [
            'output_size' => strlen($output),
            'has_output' => !empty($output)
        ]);
        
        if (empty($output)) {
            Log::error('PDF vazio após renderização');
            // Tentar método alternativo
            $dompdf->stream();
            $output = $dompdf->output();
            
            if (empty($output)) {
                throw new \Exception('Falha crítica: PDF vazio após renderização');
            }
        }
        
        $fileName = 'exportacao_indicadores_' . Carbon::now()->format('Y-m-d_H-i-s') . '.pdf';
        
        Log::info('Retornando resposta PDF', [
            'file_name' => $fileName,
            'content_length' => strlen($output)
        ]);
        
        return response($output, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Content-Length', strlen($output))
            ->header('Cache-Control', 'no-cache, private')
            ->header('Pragma', 'public')
            ->header('X-Content-Type-Options', 'nosniff');
            
    } catch (\Exception $e) {
        Log::error('ERRO CRÍTICO exportToPdf: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        // Retornar erro em JSON para o frontend
        return response()->json([
            'error' => 'Erro ao gerar PDF: ' . $e->getMessage(),
            'details' => 'Verifique os logs do servidor'
        ], 500);
    }
}
    

private function exportToExcel($indicatorIds, $startDate, $endDate)
{
    try {
        $indicators = $this->getIndicatorsForExport($indicatorIds, $startDate, $endDate);
        
        if (empty($indicators)) {
            throw new \Exception('Nenhum indicador encontrado para exportação');
        }
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Indicadores');
        
        // Configurar metadados
        $spreadsheet->getProperties()
            ->setCreator(auth()->user()->name)
            ->setTitle('Relatório de Indicadores')
            ->setDescription('Relatório gerado automaticamente pelo Sistema de Gestão');
        
        // Cabeçalho do relatório
        $sheet->setCellValue('A1', 'RELATÓRIO DE INDICADORES DO DEPARTAMENTO');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        
        $sheet->setCellValue('A2', 'Período: ' . Carbon::parse($startDate)->format('d/m/Y') . ' a ' . Carbon::parse($endDate)->format('d/m/Y'));
        $sheet->setCellValue('A3', 'Gerado em: ' . Carbon::now()->format('d/m/Y H:i'));
        $sheet->setCellValue('A4', 'Gerado por: ' . auth()->user()->name);
        
        // Cabeçalho da tabela (linha 6)
        $headers = ['Indicador', 'Categoria', 'Valor Actual', 'Meta', 'Desempenho', 'Tendência', 'Unidade', 'Status'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '6', $header);
            $col++;
        }
        
        // Estilo do cabeçalho
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '3B82F6']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A6:H6')->applyFromArray($headerStyle);
        
        // Dados dos indicadores
        $row = 7;
        foreach ($indicators as $indicator) {
            $sheet->setCellValue('A' . $row, $indicator['name'] ?? '');
            $sheet->setCellValue('B' . $row, $indicator['category'] ?? '');
            $sheet->setCellValue('C' . $row, $indicator['current_value'] ?? 0);
            $sheet->setCellValue('D' . $row, $indicator['target_value'] ?? 'N/A');
            
            // Desempenho
            if (isset($indicator['performance']) && is_numeric($indicator['performance'])) {
                $sheet->setCellValue('E' . $row, $indicator['performance']);
                $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('0.00"%"');
            } else {
                $sheet->setCellValue('E' . $row, 'N/A');
            }
            
            // Tendência
            if (isset($indicator['trend']) && is_numeric($indicator['trend'])) {
                $sheet->setCellValue('F' . $row, $indicator['trend']);
                $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('0.00"%"');
            } else {
                $sheet->setCellValue('F' . $row, 'N/A');
            }
            
            $sheet->setCellValue('G' . $row, $indicator['measurement_unit'] ?? '');
            $sheet->setCellValue('H' . $row, !empty($indicator['is_active']) ? 'Ativo' : 'Inativo');
            
            // Aplicar bordas
            $sheet->getStyle('A' . $row . ':H' . $row)->getBorders()
                ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            
            $row++;
        }
        
        // Auto-dimensionar colunas
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Adicionar formatação condicional para desempenho
        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $conditional->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_GREATERTHANOREQUAL);
        $conditional->addCondition('90');
        $conditional->getStyle()->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKGREEN);
        
        $conditional2 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional2->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $conditional2->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_BETWEEN);
        $conditional2->addCondition('70');
        $conditional2->addCondition('89.99');
        $conditional2->getStyle()->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKYELLOW);
        
        $conditional3 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional3->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $conditional3->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHAN);
        $conditional3->addCondition('70');
        $conditional3->getStyle()->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED);
        
        $conditionalStyles = $sheet->getStyle('E7:E' . ($row-1))->getConditionalStyles();
        $conditionalStyles[] = $conditional;
        $conditionalStyles[] = $conditional2;
        $conditionalStyles[] = $conditional3;
        $sheet->getStyle('E7:E' . ($row-1))->setConditionalStyles($conditionalStyles);
        
        // Gerar nome do arquivo
        $fileName = 'relatorio_indicadores_' . Carbon::now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        // Configurar headers
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        header('Expires: 0');
        header('Pragma: public');
        
        // Criar writer
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        // Salvar em buffer de saída
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        
        return response($content)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Content-Length', strlen($content))
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
            
    } catch (\Exception $e) {
        Log::error('Erro na exportação Excel: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        
        throw $e;
    }
}


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

    // Desempenho por técnico - FILTRANDO APENAS TÉCNICOS
    $technicianPerformance = User::role('Técnico')
        ->where('is_available', true)
        ->withCount([
            'assignedGrievances as total_tasks' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            },
            'assignedGrievances as completed_tasks' => function ($query) use ($startDate, $endDate) {
                $query->where('status', 'resolved')
                    ->whereBetween('resolved_at', [$startDate, $endDate]);
            },
            'assignedGrievances as pending_tasks' => function ($query) {
                $query->whereIn('status', ['assigned', 'in_progress']);
            }
        ])
        ->get()
        ->map(function ($technician) use ($startDate, $endDate) {
            $avgResolutionTime = Grievance::where('assigned_to', $technician->id)
                ->where('status', 'resolved')
                ->whereBetween('resolved_at', [$startDate, $endDate])
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

    // Taxa de satisfação real (se tiver avaliações)
    $totalRatings = DB::table('grievances')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->whereNotNull('rating')
        ->count();
    
    $averageRating = DB::table('grievances')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->whereNotNull('rating')
        ->avg('rating') ?? 0;
    
    $satisfactionRate = $averageRating > 0 ? ($averageRating / 5) * 100 : 0;

    return [
        'monthly_resolution_rates' => $monthlyResolutionRates,
        'technician_performance' => $technicianPerformance,
        'satisfaction_rate' => round($satisfactionRate, 1),
        'total_ratings' => $totalRatings,
        'average_rating' => round($averageRating, 1),
        'first_response_time' => $this->getAverageFirstResponseTime($startDate, $endDate),
        'escalation_rate' => $this->getEscalationRate($startDate, $endDate),
    ];
}


    private function generatePdfHtml($indicators, $startDate, $endDate)
{
    $start = Carbon::parse($startDate)->format('d/m/Y');
    $end = Carbon::parse($endDate)->format('d/m/Y');
    $generated = Carbon::now()->format('d/m/Y H:i');
    
    // Construir a tabela de indicadores
    $indicatorsHtml = '';
    foreach ($indicators as $indicator) {
        // Formatar valor atual
        $currentValue = $indicator['formatted_value'] ?? 'N/A';
        
        // Formatar meta
        $targetValue = 'N/A';
        if (isset($indicator['target_value']) && $indicator['target_value'] !== null) {
            if (($indicator['measurement_unit'] ?? '') === 'percentage') {
                $targetValue = number_format($indicator['target_value'], 1) . '%';
            } elseif (($indicator['measurement_unit'] ?? '') === 'days') {
                $targetValue = number_format($indicator['target_value'], 1) . ' dias';
            } else {
                $targetValue = $indicator['target_value'];
            }
        }
        
        // Formatar desempenho
        $performanceText = 'N/A';
        if (isset($indicator['performance']) && $indicator['performance'] !== null) {
            $performanceText = number_format($indicator['performance'], 1) . '%';
        }
        
        // Formatar tendência
        $trendText = 'N/A';
        if (isset($indicator['trend']) && $indicator['trend'] !== null) {
            $trendText = ($indicator['trend'] > 0 ? '+' : '') . number_format($indicator['trend'], 1) . '%';
        }
        
        // Determinar classe de cor para desempenho
        $performanceClass = '';
        if (isset($indicator['performance']) && $indicator['performance'] !== null) {
            if ($indicator['performance'] >= 90) $performanceClass = 'good';
            elseif ($indicator['performance'] >= 70) $performanceClass = 'average';
            else $performanceClass = 'poor';
        }
        
        // Determinar classe de cor para tendência
        $trendClass = '';
        if (isset($indicator['trend']) && $indicator['trend'] !== null) {
            if ($indicator['trend'] > 0) $trendClass = 'trend-up';
            elseif ($indicator['trend'] < 0) $trendClass = 'trend-down';
        }
        
        $indicatorsHtml .= '<tr>
            <td style="padding: 8px; border: 1px solid #ddd;"><strong>' . htmlspecialchars($indicator['name'] ?? 'N/A') . '</strong></td>
            <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($indicator['category'] ?? 'N/A') . '</td>
            <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($currentValue) . '</td>
            <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($targetValue) . '</td>
            <td style="padding: 8px; border: 1px solid #ddd; color: ' . ($performanceClass === 'good' ? '#10B981' : ($performanceClass === 'average' ? '#F59E0B' : '#EF4444')) . '">' . htmlspecialchars($performanceText) . '</td>
            <td style="padding: 8px; border: 1px solid #ddd; color: ' . ($trendClass === 'trend-up' ? '#10B981' : ($trendClass === 'trend-down' ? '#EF4444' : '#6B7280')) . '">' . htmlspecialchars($trendText) . '</td>
            <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($indicator['measurement_unit'] ?? 'N/A') . '</td>
            <td style="padding: 8px; border: 1px solid #ddd;">' . (($indicator['is_active'] ?? false) ? 'Ativo' : 'Inativo') . '</td>
        </tr>';
    }
    
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Exportação de Indicadores</title>
        <style>
            body { 
                font-family: "DejaVu Sans", "Helvetica", Arial, sans-serif; 
                font-size: 11px; 
                margin: 20px;
                color: #333;
            }
            .header { 
                text-align: center; 
                margin-bottom: 30px; 
                border-bottom: 2px solid #3B82F6;
                padding-bottom: 15px;
            }
            .header h1 { 
                color: #333; 
                font-size: 18px; 
                margin: 0; 
                font-weight: bold;
            }
            .header p { 
                color: #666; 
                margin: 5px 0; 
            }
            table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-top: 20px;
                font-size: 10px;
            }
            th { 
                background-color: #f8fafc; 
                border: 1px solid #e2e8f0; 
                padding: 8px; 
                text-align: left; 
                font-weight: bold;
            }
            td { 
                border: 1px solid #e2e8f0; 
                padding: 6px; 
            }
            .good { color: #10B981; }
            .average { color: #F59E0B; }
            .poor { color: #EF4444; }
            .trend-up { color: #10B981; }
            .trend-down { color: #EF4444; }
            .footer { 
                margin-top: 40px; 
                text-align: center; 
                color: #666; 
                font-size: 9px; 
                border-top: 1px solid #e2e8f0;
                padding-top: 10px;
            }
            .summary { 
                margin-top: 20px; 
                padding: 10px; 
                background-color: #f1f5f9; 
                border-left: 4px solid #3B82F6; 
                border-radius: 4px;
                font-size: 10px;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Exportação de Indicadores do Departamento</h1>
            <p>Período: ' . $start . ' a ' . $end . '</p>
            <p>Gerado em: ' . $generated . '</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Indicador</th>
                    <th>Categoria</th>
                    <th>Valor Actual</th>
                    <th>Meta</th>
                    <th>Desempenho</th>
                    <th>Tendência</th>
                    <th>Unidade</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                ' . $indicatorsHtml . '
            </tbody>
        </table>

        <div class="summary">
            <p><strong>Resumo:</strong> ' . count($indicators) . ' indicadores exportados</p>
            <p><strong>Filtros aplicados:</strong> Período de ' . $start . ' a ' . $end . '</p>
        </div>

        <div class="footer">
            <p>Exportação gerada automaticamente pelo Sistema de Gestão</p>
            <p>Página 1 de 1</p>
        </div>
    </body>
    </html>';
    
    return $html;
}


    /**
     * Get indicator history
     */
    public function getIndicatorHistory(Request $request, DepartmentIndicator $indicator)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $period = $request->input('period', 'year');
        
        $endDate = Carbon::now();
        $startDate = match($period) {
            'week' => $endDate->copy()->subWeek(),
            'month' => $endDate->copy()->subMonth(),
            'quarter' => $endDate->copy()->subMonths(3),
            'year' => $endDate->copy()->subYear(),
            default => $endDate->copy()->subMonth(),
        };

        $records = $indicator->records()
            ->whereBetween('record_date', [$startDate, $endDate])
            ->orderBy('record_date')
            ->get();

        // Calculate statistics
        $stats = [
            'average' => $records->avg('value'),
            'min' => $records->min('value'),
            'max' => $records->max('value'),
            'last_value' => $records->last()?->value,
            'trend' => $this->calculateTrend($records),
            'target_achievement' => $indicator->target_value > 0 
                ? ($records->last()?->value / $indicator->target_value) * 100 
                : null
        ];

        return response()->json([
            'indicator' => $indicator->only(['id', 'name', 'description', 'target_value', 'measurement_unit']),
            'records' => $records->map(function ($record) use ($indicator) {
                return [
                    'date' => $record->record_date->format('Y-m-d'),
                    'value' => $record->value,
                    'formatted_value' => $this->formatValue($record->value, $indicator->measurement_unit),
                    'breakdown' => $record->breakdown
                ];
            }),
            'statistics' => $stats,
            'period' => $period,
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ]
        ]);
    }

     public function checkReportStatus(Report $report)
    {
        $user = auth()->user();
        $this->checkAccess($user);
        
        // Verificar permissão
        if ($report->generated_by !== $user->id && !$user->hasRole('Gestor')) {
            abort(403, 'Você não tem permissão para ver este relatório.');
        }
        
        return response()->json([
            'status' => $report->status,
            'file_path' => $report->file_path,
            'file_size' => $report->file_size_formatted,
            'completed_at' => $report->completed_at ? $report->completed_at->format('d/m/Y H:i') : null,
            'download_url' => $report->status === 'completed' 
                ? route('gestor.dashboard.relatorios.download', ['report' => $report->id])
                : null
        ]);
    }

    /**
     * Update indicator records (cron job)
     */
    public function updateIndicatorRecords()
    {
        $indicators = DepartmentIndicator::where('is_active', true)->get();
        $today = Carbon::today();

        foreach ($indicators as $indicator) {
            // Check if record already exists for today
            $existing = IndicatorRecord::where('indicator_id', $indicator->id)
                ->whereDate('record_date', $today)
                ->first();

            if (!$existing) {
                $value = $this->calculateIndicatorValue($indicator, $today);
                
                IndicatorRecord::create([
                    'indicator_id' => $indicator->id,
                    'record_date' => $today,
                    'value' => $value,
                    'breakdown' => []
                ]);
            }
        }

        return response()->json(['updated' => count($indicators)]);
    }

    /**
     * Helper methods
     */
   private function calculateIndicatorValue($indicator, $date)
{
    // Use cálculos reais baseados nos dados do sistema
    return match($indicator->calculation_formula) {
        'resolution_rate' => $this->calculateResolutionRate($date),
        'avg_resolution_time' => $this->calculateAvgResolutionTime($date),
        'total_complaints' => $this->calculateTotalComplaints($date),
        'technician_performance' => $this->calculateTechnicianPerformance($date),
        'satisfaction_rate' => $this->calculateSatisfactionRate($date),
        default => 0 // Não retorne valores randômicos
    };
}

// Adicione estas novas funções de cálculo
private function calculateTechnicianPerformance($date)
{
    $startDate = Carbon::parse($date)->subMonth();
    
    $avgCompletionRate = DB::table('users')
        ->join('grievances', 'grievances.assigned_to', '=', 'users.id')
        ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->where('roles.name', 'Técnico')
        ->whereBetween('grievances.created_at', [$startDate, $date])
        ->selectRaw('
            AVG(
                CASE 
                    WHEN grievances.status = "resolved" THEN 100
                    ELSE 0
                END
            ) as avg_performance
        ')
        ->first()
        ->avg_performance ?? 0;
    
    return $avgCompletionRate;
}

private function calculateSatisfactionRate($date)
{
    $startDate = Carbon::parse($date)->subMonth();
    
    $avgRating = DB::table('grievances')
        ->whereBetween('created_at', [$startDate, $date])
        ->whereNotNull('rating')
        ->avg('rating') ?? 0;
    
    return ($avgRating / 5) * 100;
}

    private function calculateResolutionRate($date)
    {
        $startDate = Carbon::parse($date)->subMonth();
        
        $total = Grievance::whereBetween('created_at', [$startDate, $date])->count();
        $resolved = Grievance::whereBetween('resolved_at', [$startDate, $date])
            ->whereNotNull('resolved_at')
            ->count();
            
        return $total > 0 ? ($resolved / $total) * 100 : 0;
    }

    private function calculateAvgResolutionTime($date)
    {
        $startDate = Carbon::parse($date)->subMonth();
        
        return Grievance::whereBetween('resolved_at', [$startDate, $date])
            ->whereNotNull('resolved_at')
            ->selectRaw('AVG(DATEDIFF(resolved_at, created_at)) as avg_days')
            ->first()
            ->avg_days ?? 0;
    }

    private function calculateTotalComplaints($date)
    {
        $startDate = Carbon::parse($date)->subMonth();
        
        return Grievance::whereBetween('created_at', [$startDate, $date])->count();
    }

    private function getGrievanceStatistics(Carbon $startDate, Carbon $endDate): array
    {
        // Período atual
        $total = Grievance::whereBetween('created_at', [$startDate, $endDate])->count();
        $resolved = Grievance::whereBetween('resolved_at', [$startDate, $endDate])->count();
        $pending = Grievance::whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $avgResolutionTime = Grievance::whereBetween('resolved_at', [$startDate, $endDate])
            ->whereNotNull('resolved_at')
            ->selectRaw('AVG(DATEDIFF(resolved_at, created_at)) as avg_days')
            ->first()
            ->avg_days ?? 0;

        // Período anterior (para calcular tendências)
        $previousStartDate = $startDate->copy()->sub($endDate->diff($startDate));
        $previousEndDate = $startDate->copy();
        
        $previousTotal = Grievance::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        $previousResolved = Grievance::whereBetween('resolved_at', [$previousStartDate, $previousEndDate])->count();
        $previousPending = Grievance::whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->count();
            
        $previousAvgResolutionTime = Grievance::whereBetween('resolved_at', [$previousStartDate, $previousEndDate])
            ->whereNotNull('resolved_at')
            ->selectRaw('AVG(DATEDIFF(resolved_at, created_at)) as avg_days')
            ->first()
            ->avg_days ?? 0;

        // Calcular tendências
        $trend = $previousTotal > 0 ? (($total - $previousTotal) / $previousTotal) * 100 : 0;
        $resolutionRateTrend = $previousResolved > 0 ? (($resolved - $previousResolved) / $previousResolved) * 100 : 0;
        $pendingTrend = $previousPending > 0 ? (($pending - $previousPending) / $previousPending) * 100 : 0;
        $resolutionTimeTrend = $previousAvgResolutionTime > 0 ? 
            (($avgResolutionTime - $previousAvgResolutionTime) / $previousAvgResolutionTime) * 100 : 0;

        // Contar técnicos ativos
        $activeTechnicians = DB::table('grievances')
            ->join('users', 'grievances.assigned_to', '=', 'users.id')
            ->whereBetween('grievances.created_at', [$startDate, $endDate])
            ->whereNotNull('grievances.assigned_to')
            ->distinct('users.id')
            ->count('users.id');
            
        $previousActiveTechnicians = DB::table('grievances')
            ->join('users', 'grievances.assigned_to', '=', 'users.id')
            ->whereBetween('grievances.created_at', [$previousStartDate, $previousEndDate])
            ->whereNotNull('grievances.assigned_to')
            ->distinct('users.id')
            ->count('users.id');
            
        $techniciansTrend = $previousActiveTechnicians > 0 ? 
            (($activeTechnicians - $previousActiveTechnicians) / $previousActiveTechnicians) * 100 : 0;

        return [
            'total' => $total,
            'resolved' => $resolved,
            'pending' => $pending,
            'resolution_rate' => $total > 0 ? ($resolved / $total) * 100 : 0,
            'avg_resolution_time' => round($avgResolutionTime, 1),
            'active_technicians' => $activeTechnicians,
            // Tendências
            'trend' => round($trend, 1),
            'resolution_rate_trend' => round($resolutionRateTrend, 1),
            'pending_trend' => round($pendingTrend, 1),
            'resolution_time_trend' => round($resolutionTimeTrend, 1),
            'technicians_trend' => round($techniciansTrend, 1),
        ];
    }

    private function getTechnicianPerformance(Carbon $startDate, Carbon $endDate): array
{
    // Filtrar apenas usuários com role "Técnico"
    return DB::table('grievances')
        ->join('users', 'grievances.assigned_to', '=', 'users.id')
        ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->select(
            'users.id',
            'users.name',
            DB::raw('COUNT(grievances.id) as total_cases'),
            DB::raw('SUM(CASE WHEN grievances.status = "resolved" THEN 1 ELSE 0 END) as resolved_cases'),
            DB::raw('AVG(DATEDIFF(grievances.resolved_at, grievances.created_at)) as avg_resolution_time')
        )
        ->whereBetween('grievances.created_at', [$startDate, $endDate])
        ->whereNotNull('grievances.assigned_to')
        ->where('roles.name', 'Técnico') // Filtra apenas Técnicos
        ->groupBy('users.id', 'users.name')
        ->orderByDesc('total_cases')
        ->limit(10)
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'total_cases' => $item->total_cases,
                'resolved_cases' => $item->resolved_cases,
                'resolution_rate' => $item->total_cases > 0 
                    ? ($item->resolved_cases / $item->total_cases) * 100 
                    : 0,
                'avg_resolution_time' => round($item->avg_resolution_time ?? 0, 1)
            ];
        })
        ->toArray();
}
    private function getCategoryDistribution(Carbon $startDate, Carbon $endDate): array
    {
        return Grievance::whereBetween('created_at', [$startDate, $endDate])
            ->select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category ?? 'Não categorizado',
                    'count' => $item->count
                ];
            })
            ->toArray();
    }

    private function getResolutionTimeline(Carbon $startDate, Carbon $endDate): array
    {
        $dates = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $dates[$current->format('Y-m-d')] = [
                'date' => $current->format('d/m'),
                'submitted' => 0,
                'resolved' => 0,
                'pending' => 0
            ];
            $current->addDay();
        }

        // Get submitted counts
        $submitted = Grievance::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Get resolved counts
        $resolved = Grievance::whereBetween('resolved_at', [$startDate, $endDate])
            ->selectRaw('DATE(resolved_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        foreach ($dates as $date => &$data) {
            $dateObj = Carbon::createFromFormat('Y-m-d', $date);
            $formattedDate = $dateObj->format('Y-m-d');
            
            $data['submitted'] = $submitted[$formattedDate]->count ?? 0;
            $data['resolved'] = $resolved[$formattedDate]->count ?? 0;
            $data['pending'] = $data['submitted'] - $data['resolved'];
        }

        return array_values($dates);
    }



    private function getTopPerformers(Carbon $startDate, Carbon $endDate): array
{
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
}



    private function formatValue($value, $unit): string
    {
        // Verifica se o valor é numérico
        if (!is_numeric($value)) {
            return 'N/A';
        }
        
        // Converte para float
        $numericValue = (float) $value;
        
        return match($unit) {
            'percentage' => number_format($numericValue, 1) . '%',
            'days' => number_format($numericValue, 1) . ' dias',
            'count' => number_format($numericValue, 0),
            'rating' => number_format($numericValue, 1) . '/5',
            default => number_format($numericValue, 2) . ' ' . $unit
        };
    }

    private function generateReportName(array $data): string
    {
        $typeMap = [
            'monthly' => 'Mensal',
            'quarterly' => 'Trimestral',
            'annual' => 'Anual',
            'custom' => 'Personalizado'
        ];

        $type = $typeMap[$data['report_type']] ?? $data['report_type'];
        $start = Carbon::parse($data['start_date'])->format('d-m-Y');
        $end = Carbon::parse($data['end_date'])->format('d-m-Y');

        return "Relatório {$type} {$start} a {$end}";
    }

    private function calculateTrend($records)
    {
        if ($records->count() < 2) {
            return null;
        }
        
        $first = $records->first()->value;
        $last = $records->last()->value;
        
        return $first > 0 ? (($last - $first) / $first) * 100 : null;
    }
    
    public function testPdfView()
    {
        $indicators = [
            [
                'name' => 'Taxa de Resolução',
                'category' => 'performance',
                'current_value' => 85.5,
                'formatted_value' => '85.5%',
                'target_value' => 90.0,
                'performance' => 95.0,
                'trend' => 2.5,
                'measurement_unit' => 'percentage',
                'is_active' => true
            ]
        ];
        
        $data = [
            'indicators' => $indicators,
            'period' => [
                'start' => '01/12/2024',
                'end' => '05/12/2024',
            ],
            'generated_at' => now()->format('d/m/Y H:i'),
            'total_indicators' => count($indicators)
        ];
        
        return view('exports.indicators-pdf', $data);
    }
}