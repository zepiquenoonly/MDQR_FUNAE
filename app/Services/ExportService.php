<?php

namespace App\Services;

use App\Models\Grievance;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExportService
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
    
    public function exportToXlsx($filename)
    {
        $path = "exports/{$filename}.xlsx";
        $fullPath = storage_path("app/public/{$path}");
        
        // Usar PhpSpreadsheet diretamente
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Remover sheet padrão
        $spreadsheet->removeSheetByIndex(0);
        
        // Sheet 1: Resumo
        $summarySheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Resumo');
        $spreadsheet->addSheet($summarySheet);
        $this->addSummarySheet($summarySheet);
        
        // Sheet 2: Submissões
        $submissionsSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Submissões');
        $spreadsheet->addSheet($submissionsSheet);
        $this->addSubmissionsSheet($submissionsSheet);
        
        // Sheet 3: Distribuições
        $distributionSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Distribuições');
        $spreadsheet->addSheet($distributionSheet);
        $this->addDistributionSheet($distributionSheet);
        
        // Salvar
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($fullPath);
        
        return $path;
    }
    
    public function exportToCsv($filename)
    {
        $path = "exports/{$filename}.csv";
        $fullPath = storage_path("app/public/{$path}");
        
        $csvContent = $this->generateCsvContent();
        
        file_put_contents($fullPath, $csvContent);
        
        return $path;
    }
    
    public function exportToPdf($filename)
    {
        $path = "exports/{$filename}.pdf";
        $fullPath = storage_path("app/public/{$path}");
        
        // Dados para o PDF
        $data = [
            'summary' => $this->getSummaryData(),
            'submissions' => $this->getSubmissionsData(),
            'performance' => $this->getPerformanceData(),
            'charts' => [
                'status' => $this->getStatusDistribution(),
                'type' => $this->getTypeDistribution(),
                'provinces' => $this->getProvinceDistribution(),
            ],
            'period_label' => $this->getPeriodLabel(),
            'start_date' => $this->startDate->format('d/m/Y'),
            'end_date' => $this->endDate->format('d/m/Y'),
            'exported_by' => $this->user->name,
            'export_date' => now()->format('d/m/Y H:i:s'),
        ];
        
        // Gerar PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.statistics-pdf', $data);
        $pdf->save($fullPath);
        
        return $path;
    }
    
    private function addSummarySheet($sheet)
    {
        $summary = $this->getSummaryData();
        
        $row = 1;
        $sheet->setCellValue('A' . $row++, 'ESTATÍSTICAS GERAIS - RELATÓRIO');
        $sheet->setCellValue('A' . $row++, 'Sistema de Gestão de Reclamações');
        $row++;
        $sheet->setCellValue('A' . $row++, 'PERÍODO DO RELATÓRIO');
        $sheet->setCellValue('A' . $row, 'Período:');
        $sheet->setCellValue('B' . $row++, $summary['period_label']);
        $sheet->setCellValue('A' . $row, 'Data Início:');
        $sheet->setCellValue('B' . $row++, $summary['start_date_formatted']);
        $sheet->setCellValue('A' . $row, 'Data Fim:');
        $sheet->setCellValue('B' . $row++, $summary['end_date_formatted']);
        $sheet->setCellValue('A' . $row, 'Exportado por:');
        $sheet->setCellValue('B' . $row++, $summary['exported_by']);
        $sheet->setCellValue('A' . $row, 'Data Exportação:');
        $sheet->setCellValue('B' . $row++, $summary['export_date']);
        $row++;
        $sheet->setCellValue('A' . $row++, 'RESUMO EXECUTIVO');
        $sheet->setCellValue('A' . $row, 'Métrica');
        $sheet->setCellValue('B' . $row++, 'Valor');
        $sheet->setCellValue('A' . $row, 'Total de Submissões');
        $sheet->setCellValue('B' . $row++, $summary['total_submissions']);
        $sheet->setCellValue('A' . $row, 'Submissões Resolvidas');
        $sheet->setCellValue('B' . $row++, $summary['total_resolved']);
        $sheet->setCellValue('A' . $row, 'Taxa de Resolução');
        $sheet->setCellValue('B' . $row++, $summary['resolution_rate'] . '%');
        $sheet->setCellValue('A' . $row, 'Tempo Médio de Resolução');
        $sheet->setCellValue('B' . $row++, $summary['avg_resolution_time'] . 'h');
        $sheet->setCellValue('A' . $row, 'Submissões Pendentes');
        $sheet->setCellValue('B' . $row++, $summary['pending_submissions']);
        $sheet->setCellValue('A' . $row, 'Taxa de Crescimento');
        $sheet->setCellValue('B' . $row++, $summary['growth_rate'] . '%');
        $sheet->setCellValue('A' . $row, 'Funcionários Ativos');
        $sheet->setCellValue('B' . $row++, $summary['active_employees']);
        
        // Estilos
        $sheet->getStyle('A1:B' . ($row-1))->getFont()->setName('Arial');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->getStyle('A' . ($row-20))->getFont()->setBold(true);
        $sheet->getStyle('A' . ($row-19) . ':B' . ($row-19))->getFont()->setBold(true);
        
        // Auto size
        foreach (range('A', 'B') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    private function addSubmissionsSheet($sheet)
    {
        $submissions = $this->getSubmissionsData();
        
        $headers = ['ID', 'Referência', 'Descrição', 'Tipo', 'Prioridade', 'Status', 'Província', 'Data Criação', 'Submetido por', 'Atribuído a'];
        
        $row = 1;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue(chr(65 + $col) . $row, $header);
        }
        
        $row++;
        foreach ($submissions as $submission) {
            $sheet->setCellValue('A' . $row, $submission->id);
            $sheet->setCellValue('B' . $row, $submission->reference_number ?? 'N/A');
            $sheet->setCellValue('C' . $row, $submission->description ? substr($submission->description, 0, 100) . (strlen($submission->description) > 100 ? '...' : '') : 'Sem descrição');
            $sheet->setCellValue('D' . $row, $this->getTypeLabel($submission->type));
            $sheet->setCellValue('E' . $row, $this->getPriorityLabel($submission->priority));
            $sheet->setCellValue('F' . $row, $this->getStatusLabel($submission->status));
            $sheet->setCellValue('G' . $row, $submission->province ?? 'N/A');
            $sheet->setCellValue('H' . $row, $submission->created_at ? $submission->created_at->format('d/m/Y H:i:s') : 'N/A');
            $sheet->setCellValue('I' . $row, $submission->user->name ?? 'Anônimo');
            $sheet->setCellValue('J' . $row, $submission->assignedUser->name ?? 'N/A');
            $row++;
        }
        
        // Estilos
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('A1:J1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
        
        // Auto size
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    private function addDistributionSheet($sheet)
    {
        $statusDistribution = $this->getStatusDistribution();
        $typeDistribution = $this->getTypeDistribution();
        $priorityDistribution = $this->getPriorityDistribution();
        
        $row = 1;
        $sheet->setCellValue('A' . $row++, 'DISTRIBUIÇÃO POR STATUS');
        $sheet->setCellValue('A' . $row, 'Status');
        $sheet->setCellValue('B' . $row++, 'Quantidade');
        
        foreach ($statusDistribution as $status => $count) {
            $sheet->setCellValue('A' . $row, $status);
            $sheet->setCellValue('B' . $row++, $count);
        }
        
        $row++;
        $sheet->setCellValue('A' . $row++, 'DISTRIBUIÇÃO POR TIPO');
        $sheet->setCellValue('A' . $row, 'Tipo');
        $sheet->setCellValue('B' . $row++, 'Quantidade');
        
        foreach ($typeDistribution as $type => $count) {
            $sheet->setCellValue('A' . $row, $type);
            $sheet->setCellValue('B' . $row++, $count);
        }
        
        if (!empty($priorityDistribution)) {
            $row++;
            $sheet->setCellValue('A' . $row++, 'DISTRIBUIÇÃO POR PRIORIDADE');
            $sheet->setCellValue('A' . $row, 'Prioridade');
            $sheet->setCellValue('B' . $row++, 'Quantidade');
            
            foreach ($priorityDistribution as $priority => $count) {
                $sheet->setCellValue('A' . $row, $priority);
                $sheet->setCellValue('B' . $row++, $count);
            }
        }
        
        // Estilos
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A' . (count($statusDistribution) + 4))->getFont()->setBold(true);
        
        // Auto size
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
    }
    
    private function generateCsvContent()
    {
        $summary = $this->getSummaryData();
        $submissions = $this->getSubmissionsData();
        $statusDistribution = $this->getStatusDistribution();
        $typeDistribution = $this->getTypeDistribution();
        
        $csv = "RELATÓRIO DE ESTATÍSTICAS\n";
        $csv .= "Sistema de Gestão de Reclamações\n\n";
        
        $csv .= "PERÍODO DO RELATÓRIO\n";
        $csv .= "Período:," . $summary['period_label'] . "\n";
        $csv .= "Data Início:," . $summary['start_date_formatted'] . "\n";
        $csv .= "Data Fim:," . $summary['end_date_formatted'] . "\n";
        $csv .= "Exportado por:," . $summary['exported_by'] . "\n";
        $csv .= "Data Exportação:," . $summary['export_date'] . "\n\n";
        
        $csv .= "RESUMO EXECUTIVO\n";
        $csv .= "Métrica,Valor\n";
        $csv .= "Total de Submissões," . $summary['total_submissions'] . "\n";
        $csv .= "Submissões Resolvidas," . $summary['total_resolved'] . "\n";
        $csv .= "Taxa de Resolução," . $summary['resolution_rate'] . "%\n";
        $csv .= "Tempo Médio de Resolução," . $summary['avg_resolution_time'] . "h\n";
        $csv .= "Submissões Pendentes," . $summary['pending_submissions'] . "\n";
        $csv .= "Taxa de Crescimento," . $summary['growth_rate'] . "%\n";
        $csv .= "Funcionários Ativos," . $summary['active_employees'] . "\n\n";
        
        $csv .= "DISTRIBUIÇÃO POR STATUS\n";
        $csv .= "Status,Quantidade\n";
        foreach ($statusDistribution as $status => $count) {
            $csv .= $status . "," . $count . "\n";
        }
        $csv .= "\n";
        
        $csv .= "DISTRIBUIÇÃO POR TIPO\n";
        $csv .= "Tipo,Quantidade\n";
        foreach ($typeDistribution as $type => $count) {
            $csv .= $type . "," . $count . "\n";
        }
        $csv .= "\n";
        
        $csv .= "ÚLTIMAS SUBMISSÕES\n";
        $csv .= "ID,Referência,Tipo,Prioridade,Status,Data Criação\n";
        foreach ($submissions->take(50) as $submission) {
            $csv .= $submission->id . ",";
            $csv .= ($submission->reference_number ?? 'N/A') . ",";
            $csv .= $this->getTypeLabel($submission->type) . ",";
            $csv .= $this->getPriorityLabel($submission->priority) . ",";
            $csv .= $this->getStatusLabel($submission->status) . ",";
            $csv .= ($submission->created_at ? $submission->created_at->format('d/m/Y') : 'N/A') . "\n";
        }
        
        return $csv;
    }
    
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