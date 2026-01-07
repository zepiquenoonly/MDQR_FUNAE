<?php

namespace App\Exports;

use App\Models\Grievance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class StatisticsExport
{
    protected $period;
    protected $user;
    protected $startDate;
    protected $endDate;
    
    public function __construct($period, $user)
    {
        $this->period = $period;
        $this->user = $user;
        $this->setDateRange($period);
    }
    
    private function setDateRange($period)
    {
        $now = now();
        
        switch ($period) {
            case 'today':
                $this->startDate = $now->startOfDay();
                $this->endDate = $now->endOfDay();
                break;
            case 'week':
                $this->startDate = $now->copy()->subWeek()->startOfDay();
                $this->endDate = $now->copy()->endOfDay();
                break;
            case 'month':
                $this->startDate = $now->copy()->subMonth()->startOfDay();
                $this->endDate = $now->copy()->endOfDay();
                break;
            case '3months':
                $this->startDate = $now->copy()->subMonths(3)->startOfDay();
                $this->endDate = $now->copy()->endOfDay();
                break;
            case '6months':
                $this->startDate = $now->copy()->subMonths(6)->startOfDay();
                $this->endDate = $now->copy()->endOfDay();
                break;
            case 'year':
                $this->startDate = $now->copy()->subYear()->startOfDay();
                $this->endDate = $now->copy()->endOfDay();
                break;
            case '12months':
                $this->startDate = $now->copy()->subMonths(12)->startOfDay();
                $this->endDate = $now->copy()->endOfDay();
                break;
            default:
                $this->startDate = $now->copy()->subMonths(12)->startOfDay();
                $this->endDate = $now->copy()->endOfDay();
        }
    }
    
    /**
     * Método store unificado para todos os formatos
     */
    public function store(string $format, string $path, string $disk = 'public'): string
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);
        
        \Log::info("Exportando para formato: {$format}, filename: {$filename}");
        
        switch ($format) {
            case 'xlsx':
                return $this->exportExcel($filename, $disk);
                
            case 'csv':
                return $this->exportCsv($filename, $disk);
                
            case 'pdf':
                return $this->exportPdf($filename, $disk);
                
            default:
                throw new \Exception("Formato não suportado: {$format}");
        }
    }
    
    /**
     * Exportar para Excel
     */
    private function exportExcel($filename, $disk): string
{
    \Log::info("Iniciando exportação Excel: {$filename}");
    
    try {
        if (!class_exists('\Maatwebsite\Excel\Facades\Excel')) {
            throw new \Exception("Biblioteca Maatwebsite Excel não encontrada");
        }
        
        $path = "exports/{$filename}.xlsx";
        
        // Criar dados para múltiplas sheets
        $summaryData = $this->getSummaryData();
        $submissionsData = $this->getSubmissionsDataForExport();
        $performanceData = $this->getPerformanceDataForExport();
        $chartData = $this->getChartDataForExport();
        
        // Usar SimpleExcelExport corrigido
        $export = new SimpleExcelExport($summaryData, $submissionsData, $performanceData, $chartData);
        
        $path = "exports/{$filename}.xlsx";
        
        \Log::info("Excel exportado com sucesso para: {$path}");
        return $path;
        
    } catch (\Exception $e) {
        \Log::error("Erro ao exportar Excel: " . $e->getMessage());
        \Log::error($e->getTraceAsString());
        throw $e;
    }
}
    
    /**
     * Exportar para CSV
     */
    private function exportCsv($filename, $disk): string
    {
        \Log::info("Iniciando exportação CSV: {$filename}");
        
        try {
            $data = $this->getSummaryData();
            $submissions = $this->getSubmissionsDataForExport();
            
            // Criar conteúdo CSV
            $csvContent = "RELATÓRIO DE ESTATÍSTICAS\n";
            $csvContent .= "Sistema de Gestão de Reclamações\n\n";
            $csvContent .= "PERÍODO DO RELATÓRIO\n";
            $csvContent .= "Período:," . $data['period_label'] . "\n";
            $csvContent .= "Data Início:," . $data['start_date_formatted'] . "\n";
            $csvContent .= "Data Fim:," . $data['end_date_formatted'] . "\n";
            $csvContent .= "Exportado por:," . $data['exported_by'] . "\n";
            $csvContent .= "Data Exportação:," . $data['export_date'] . "\n\n";
            
            $csvContent .= "RESUMO EXECUTIVO\n";
            $csvContent .= "Métrica,Valor\n";
            $csvContent .= "Total de Submissões," . $data['total_submissions'] . "\n";
            $csvContent .= "Submissões Resolvidas," . $data['total_resolved'] . "\n";
            $csvContent .= "Taxa de Resolução," . $data['resolution_rate'] . "%\n";
            $csvContent .= "Tempo Médio de Resolução," . $data['avg_resolution_time'] . "h\n";
            $csvContent .= "Submissões Pendentes," . $data['pending_submissions'] . "\n";
            $csvContent .= "Taxa de Crescimento," . $data['growth_rate'] . "%\n";
            $csvContent .= "Funcionários Ativos," . $data['active_employees'] . "\n";
            $csvContent .= "Submissões Hoje," . $data['submissions_today'] . "\n\n";
            
            $csvContent .= "DISTRIBUIÇÃO POR STATUS\n";
            $csvContent .= "Status,Quantidade,Percentagem\n";
            
            $statusData = $this->getStatusDistribution();
            $totalStatus = array_sum($statusData);
            
            foreach ($statusData as $status => $count) {
                $percentage = $totalStatus > 0 ? round(($count / $totalStatus) * 100, 1) : 0;
                $csvContent .= $status . "," . $count . "," . $percentage . "%\n";
            }
            $csvContent .= "TOTAL," . $totalStatus . ",100%\n\n";
            
            $csvContent .= "DISTRIBUIÇÃO POR TIPO\n";
            $csvContent .= "Tipo,Quantidade,Percentagem\n";
            
            $typeData = $this->getTypeDistribution();
            $totalType = array_sum($typeData);
            
            foreach ($typeData as $type => $count) {
                $percentage = $totalType > 0 ? round(($count / $totalType) * 100, 1) : 0;
                $csvContent .= $type . "," . $count . "," . $percentage . "%\n";
            }
            $csvContent .= "TOTAL," . $totalType . ",100%\n\n";
            
            $csvContent .= "TOP 10 SUBMISSÕES RECENTES\n";
            $csvContent .= "ID,Referência,Tipo,Prioridade,Status,Data Criação\n";
            
            foreach ($submissions->take(10) as $submission) {
                $csvContent .= $submission->id . ",";
                $csvContent .= $submission->reference_number . ",";
                $csvContent .= $this->getTypeLabel($submission->type) . ",";
                $csvContent .= $this->getPriorityLabel($submission->priority) . ",";
                $csvContent .= $this->getStatusLabel($submission->status) . ",";
                $csvContent .= $submission->created_at->format('d/m/Y') . "\n";
            }
            
            $path = "exports/{$filename}.csv";
            $fullPath = storage_path("app/public/{$path}");
            
            // Garantir diretório
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Salvar arquivo
            file_put_contents($fullPath, $csvContent);
            
            \Log::info("CSV salvo com sucesso: {$fullPath}, Tamanho: " . filesize($fullPath) . " bytes");
            return $path;
            
        } catch (\Exception $e) {
            \Log::error("Erro ao exportar CSV: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            throw $e;
        }
    }
    
    /**
     * Exportar para PDF
     */
    private function exportPdf($filename, $disk): string
    {
        \Log::info("Iniciando exportação PDF: {$filename}");
        
        try {
            if (!class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
                throw new \Exception("Biblioteca DomPDF não encontrada");
            }
            
            // Obter dados completos para o PDF
            $data = [
                'summary' => $this->getSummaryData(),
                'submissions' => $this->getSubmissionsDataForExport()->take(50),
                'performance' => $this->getPerformanceDataForExport()->take(10),
                'charts' => [
                    'status' => $this->getStatusDistribution(),
                    'type' => $this->getTypeDistribution(),
                    'provinces' => $this->getProvinceDistribution()->take(5),
                ],
                'period_label' => $this->getPeriodLabel($this->period),
                'start_date' => $this->startDate->format('d/m/Y'),
                'end_date' => $this->endDate->format('d/m/Y'),
                'exported_by' => $this->user->name,
                'export_date' => now()->format('d/m/Y H:i:s'),
                'logo' => $this->getLogoBase64(),
                'PAGE_NUM' => '{PAGE_NUM}',
                'PAGE_COUNT' => '{PAGE_COUNT}'
            ];
            
            \Log::info("Dados para PDF preparados");
            
            // Garantir que a view existe
            if (!view()->exists('exports.statistics-pdf')) {
                \Log::error("View exports.statistics-pdf não encontrada");
                throw new \Exception("Template PDF não encontrado");
            }
            
            // Gerar PDF
            $pdf = Pdf::loadView('exports.statistics-pdf', $data);
            
            // Configurar papel
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);
            
            // Salvar PDF
            $path = "exports/{$filename}.pdf";
            $fullPath = storage_path("app/public/{$path}");
            
            // Garantir que o diretório existe
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Salvar arquivo
            $pdf->save($fullPath);
            
            // Verificar se foi salvo
            if (file_exists($fullPath)) {
                \Log::info("PDF salvo com sucesso: {$fullPath}, Tamanho: " . filesize($fullPath) . " bytes");
            } else {
                throw new \Exception("PDF não foi salvo: {$fullPath}");
            }
            
            return $path;
            
        } catch (\Exception $e) {
            \Log::error("Erro ao exportar PDF: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            throw $e;
        }
    }
    
    /**
     * Obter dados de resumo
     */
    private function getSummaryData(): array
    {
        try {
            \Log::info("Calculando dados de resumo para período: {$this->period}");
            
            $totalSubmissions = Grievance::whereBetween('created_at', [$this->startDate, $this->endDate])->count();
            $totalResolved = Grievance::where('status', 'resolved')
                ->whereBetween('resolved_at', [$this->startDate, $this->endDate])
                ->count();
            
            $resolutionRate = $totalSubmissions > 0 
                ? round(($totalResolved / $totalSubmissions) * 100, 2)
                : 0;
            
            $avgResolutionTime = Grievance::where('status', 'resolved')
                ->whereNotNull('resolved_at')
                ->whereNotNull('assigned_at')
                ->whereBetween('resolved_at', [$this->startDate, $this->endDate])
                ->avg(DB::raw('TIMESTAMPDIFF(HOUR, assigned_at, resolved_at)')) ?? 0;
            
            $pendingSubmissions = Grievance::whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress', 'pending_approval'])
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->count();
            
            // Calcular taxa de crescimento
            $previousPeriodStart = $this->startDate->copy()->sub($this->endDate->diff($this->startDate));
            $previousPeriodEnd = $this->startDate->copy();
            
            $previousPeriodSubmissions = Grievance::whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->count();
            
            $growthRate = $previousPeriodSubmissions > 0
                ? round((($totalSubmissions - $previousPeriodSubmissions) / $previousPeriodSubmissions) * 100, 2)
                : ($totalSubmissions > 0 ? 100 : 0);
            
            return [
                'period_label' => $this->getPeriodLabel($this->period),
                'start_date_formatted' => $this->startDate->format('d/m/Y'),
                'end_date_formatted' => $this->endDate->format('d/m/Y'),
                'total_submissions' => $totalSubmissions,
                'total_resolved' => $totalResolved,
                'resolution_rate' => $resolutionRate,
                'avg_resolution_time' => round($avgResolutionTime, 1),
                'pending_submissions' => $pendingSubmissions,
                'growth_rate' => $growthRate,
                'submissions_today' => Grievance::whereDate('created_at', today())->count(),
                'submissions_this_week' => Grievance::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'submissions_this_month' => Grievance::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
                'active_employees' => User::role(['Gestor', 'Técnico'])->where('is_available', true)->count(),
                'exported_by' => $this->user->name,
                'export_date' => now()->format('d/m/Y H:i:s')
            ];
            
        } catch (\Exception $e) {
            \Log::error("Erro em getSummaryData: " . $e->getMessage());
            return [
                'period_label' => 'Período',
                'start_date_formatted' => $this->startDate->format('d/m/Y'),
                'end_date_formatted' => $this->endDate->format('d/m/Y'),
                'total_submissions' => 0,
                'total_resolved' => 0,
                'resolution_rate' => 0,
                'avg_resolution_time' => 0,
                'pending_submissions' => 0,
                'growth_rate' => 0,
                'submissions_today' => 0,
                'submissions_this_week' => 0,
                'submissions_this_month' => 0,
                'active_employees' => 0,
                'exported_by' => $this->user->name,
                'export_date' => now()->format('d/m/Y H:i:s')
            ];
        }
    }
    
    /**
     * Obter dados de submissões para exportação
     */
    private function getSubmissionsDataForExport()
{
    \Log::info("getSubmissionsDataForExport - Iniciando busca");
    
    try {
        $query = Grievance::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->with(['user', 'assignedUser'])
            ->orderBy('created_at', 'desc')
            ->limit(100);
        
        $results = $query->get();
        
        // Aplicar traduções
        $results = $results->map(function ($submission) {
            $submission->type_translated = $this->getTypeLabel($submission->type);
            $submission->priority_translated = $this->getPriorityLabel($submission->priority);
            $submission->status_translated = $this->getStatusLabel($submission->status);
            return $submission;
        });
        
        \Log::info("getSubmissionsDataForExport - Encontrados: " . $results->count() . " registros");
        
        return $results;
        
    } catch (\Exception $e) {
        \Log::error("Erro em getSubmissionsDataForExport: " . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return collect([]);
    }
}


private function getTypeLabel($type): string
{
    $labels = [
        'grievance' => 'Queixa',
        'complaint' => 'Reclamação',
        'suggestion' => 'Sugestão',
    ];
    
    return $labels[$type] ?? $type;
}

private function getPriorityLabel($priority): string
{
    $labels = [
        'low' => 'Baixa',
        'medium' => 'Média',
        'high' => 'Alta',
        'critical' => 'Crítica',
    ];
    
    return $labels[$priority] ?? $priority;
}

private function getStatusLabel($status): string
{
    $labels = [
        'submitted' => 'Submetida',
        'under_review' => 'Em Revisão',
        'assigned' => 'Atribuída',
        'in_progress' => 'Em Progresso',
        'pending_approval' => 'Aprovação Pendente',
        'resolved' => 'Resolvida',
        'rejected' => 'Rejeitada',
        'cancelled' => 'Cancelada',
    ];
    
    return $labels[$status] ?? $status;
}
    
    /**
     * Obter dados de desempenho para exportação
     */
private function getPerformanceDataForExport()
{
    return User::role('Técnico')
        ->where('is_available', true)
        ->withCount([
            'assignedGrievances as total_tasks',
            'assignedGrievances as completed_tasks' => function ($query) {
                $query->where('status', 'resolved')
                    ->whereBetween('resolved_at', [$this->startDate, $this->endDate]);
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
                'email' => $technician->email,
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
        ->values();
}
    
    /**
     * Obter dados de gráficos para exportação
     */
    private function getChartDataForExport(): array
    {
        return [
            'status_distribution' => $this->getStatusDistribution(),
            'type_distribution' => $this->getTypeDistribution(),
            'province_distribution' => $this->getProvinceDistribution(),
            'priority_distribution' => $this->getPriorityDistribution(),
        ];
    }
    
    /**
     * Obter distribuição por status
     */
    private function getStatusDistribution(): array
    {
        $data = Grievance::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
        
        $result = [];
        foreach ($data as $item) {
            $result[$this->getStatusLabel($item->status)] = $item->count;
        }
        
        return $result;
    }
    
    /**
     * Obter distribuição por tipo
     */
    private function getTypeDistribution(): array
    {
        $data = Grievance::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();
        
        $result = [];
        foreach ($data as $item) {
            $result[$this->getTypeLabel($item->type)] = $item->count;
        }
        
        return $result;
    }
    
    /**
     * Obter distribuição por província
     */
    private function getProvinceDistribution()
    {
        return Grievance::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotNull('province')
            ->selectRaw('province, COUNT(*) as count')
            ->groupBy('province')
            ->orderByDesc('count')
            ->get();
    }
    
    /**
     * Obter distribuição por prioridade
     */
    private function getPriorityDistribution(): array
    {
        $data = Grievance::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->get();
        
        $result = [];
        foreach ($data as $item) {
            $result[$this->getPriorityLabel($item->priority)] = $item->count;
        }
        
        return $result;
    }
    
    /**
     * Obter logo em base64
     */
    private function getLogoBase64(): ?string
    {
        try {
            // Tente vários caminhos possíveis para o logo
            $possiblePaths = [
                public_path('images/logo.png'),
                public_path('logo.png'),
                public_path('assets/images/logo.png'),
                storage_path('app/public/logo.png'),
            ];
            
            foreach ($possiblePaths as $logoPath) {
                if (file_exists($logoPath)) {
                    $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                    $data = file_get_contents($logoPath);
                    return 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            }
            
            \Log::info("Logo não encontrado em nenhum dos caminhos");
            return null;
            
        } catch (\Exception $e) {
            \Log::error("Erro ao obter logo: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Helper methods
     */
    private function getPeriodLabel($period): string
    {
        $labels = [
            'today' => 'Hoje',
            'week' => 'Esta Semana',
            'month' => 'Este Mês',
            '3months' => 'Últimos 3 Meses',
            '6months' => 'Últimos 6 Meses',
            'year' => 'Este Ano',
            '12months' => 'Últimos 12 Meses',
        ];
        
        return $labels[$period] ?? 'Período Desconhecido';
    }
    
}