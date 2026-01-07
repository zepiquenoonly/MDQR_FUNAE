<?php

namespace App\Services;

use App\Models\Grievance;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExcelExportService
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
    
    public function exportXlsx($filename)
    {
        Log::info("Iniciando exportXlsx com filename: {$filename}");
        
        $path = "exports/{$filename}.xlsx";
        $fullPath = storage_path("app/public/{$path}");
        
        // Criar diretório se não existir
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            Log::info("Diretório criado: {$dir}");
        }
        
        try {
            Log::info("Verificando se PhpSpreadsheet está disponível...");
            
            // Verificar se a classe existe
            if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                Log::error("PhpSpreadsheet não encontrado!");
                throw new \Exception("Biblioteca PhpSpreadsheet não encontrada. Execute: composer require phpoffice/phpspreadsheet");
            }
            
            Log::info("PhpSpreadsheet encontrado. Criando novo spreadsheet...");
            
            // Criar novo spreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            
            // Sheet 1: Resumo Geral
            Log::info("Criando sheet de Resumo...");
            $this->createSummarySheet($spreadsheet);
            
            // Sheet 2: Submissões
            Log::info("Criando sheet de Submissões...");
            $this->createSubmissionsSheet($spreadsheet);
            
            // Sheet 3: Distribuições
            Log::info("Criando sheet de Distribuições...");
            $this->createDistributionSheet($spreadsheet);
            
            // Sheet 4: Desempenho
            Log::info("Criando sheet de Desempenho...");
            $this->createPerformanceSheet($spreadsheet);
            
            // Salvar arquivo
            Log::info("Salvando arquivo Excel...");
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($fullPath);
            
            Log::info("Arquivo Excel criado com sucesso: {$fullPath}, tamanho: " . filesize($fullPath) . " bytes");
            return $path;
            
        } catch (\Exception $e) {
            Log::error("Erro ao criar Excel: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            // Tentar criar um CSV como fallback
            Log::info("Tentando fallback para CSV...");
            return $this->exportCsv($filename);
        }
    }
    
    public function exportCsv($filename)
    {
        $path = "exports/{$filename}.csv";
        $fullPath = storage_path("app/public/{$path}");
        
        // Criar diretório se não existir
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $csvContent = $this->generateCsvContent();
        
        file_put_contents($fullPath, $csvContent);
        
        \Log::info("Arquivo CSV criado: {$fullPath}");
        return $path;
    }
    
    private function createSummarySheet($spreadsheet)
    {
        $summary = $this->getSummaryData();
        
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resumo');
        
        $row = 1;
        $sheet->setCellValue('A' . $row++, 'RELATÓRIO DE ESTATÍSTICAS');
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
        
        $metrics = [
            'Total de Submissões' => $summary['total_submissions'],
            'Submissões Resolvidas' => $summary['total_resolved'],
            'Taxa de Resolução' => $summary['resolution_rate'] . '%',
            'Tempo Médio de Resolução' => $summary['avg_resolution_time'] . 'h',
            'Submissões Pendentes' => $summary['pending_submissions'],
            'Taxa de Crescimento' => $summary['growth_rate'] . '%',
            'Funcionários Ativos' => $summary['active_employees'],
            'Submissões Hoje' => $summary['submissions_today'],
            'Submissões Esta Semana' => $summary['submissions_this_week'],
            'Submissões Este Mês' => $summary['submissions_this_month'],
        ];
        
        foreach ($metrics as $label => $value) {
            $sheet->setCellValue('A' . $row, $label);
            $sheet->setCellValue('B' . $row, $value);
            $row++;
        }
        
        // Estilos
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->getStyle('A' . ($row - count($metrics) - 2))->getFont()->setBold(true);
        $sheet->getStyle('A' . ($row - count($metrics) - 1) . ':B' . ($row - count($metrics) - 1))->getFont()->setBold(true);
        
        // Auto size
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
    }
    
    private function createSubmissionsSheet($spreadsheet)
    {
        $submissions = Grievance::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->with(['user', 'assignedUser'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
        
        $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Submissões');
        $spreadsheet->addSheet($sheet, 1);
        
        $headers = ['ID', 'Referência', 'Descrição', 'Tipo', 'Prioridade', 'Status', 'Província', 'Data Criação', 'Submetido por', 'Atribuído a'];
        
        $row = 1;
        $col = 0;
        foreach ($headers as $header) {
            $sheet->setCellValue(chr(65 + $col) . $row, $header);
            $col++;
        }
        
        $row++;
        foreach ($submissions as $submission) {
            $sheet->setCellValue('A' . $row, $submission->id);
            $sheet->setCellValue('B' . $row, $submission->reference_number ?? 'N/A');
            $desc = $submission->description ? substr($submission->description, 0, 100) : 'Sem descrição';
            if (strlen($submission->description) > 100) {
                $desc .= '...';
            }
            $sheet->setCellValue('C' . $row, $desc);
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
        $fill = new \PhpOffice\PhpSpreadsheet\Style\Fill();
        $fill->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $fill->getStartColor()->setARGB('FFE0E0E0');
        $sheet->getStyle('A1:J1')->getFill()->applyFromArray($fill->getFillArray());
        
        // Auto size
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    private function createDistributionSheet($spreadsheet)
    {
        $statusData = Grievance::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
        
        $typeData = Grievance::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();
        
        $priorityData = Grievance::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->get();
        
        $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Distribuições');
        $spreadsheet->addSheet($sheet, 2);
        
        $row = 1;
        $sheet->setCellValue('A' . $row++, 'DISTRIBUIÇÃO POR STATUS');
        $sheet->setCellValue('A' . $row, 'Status');
        $sheet->setCellValue('B' . $row++, 'Quantidade');
        
        foreach ($statusData as $item) {
            $sheet->setCellValue('A' . $row, $this->getStatusLabel($item->status));
            $sheet->setCellValue('B' . $row++, $item->count);
        }
        
        $row++;
        $sheet->setCellValue('A' . $row++, 'DISTRIBUIÇÃO POR TIPO');
        $sheet->setCellValue('A' . $row, 'Tipo');
        $sheet->setCellValue('B' . $row++, 'Quantidade');
        
        foreach ($typeData as $item) {
            $sheet->setCellValue('A' . $row, $this->getTypeLabel($item->type));
            $sheet->setCellValue('B' . $row++, $item->count);
        }
        
        if ($priorityData->count() > 0) {
            $row++;
            $sheet->setCellValue('A' . $row++, 'DISTRIBUIÇÃO POR PRIORIDADE');
            $sheet->setCellValue('A' . $row, 'Prioridade');
            $sheet->setCellValue('B' . $row++, 'Quantidade');
            
            foreach ($priorityData as $item) {
                $sheet->setCellValue('A' . $row, $this->getPriorityLabel($item->priority));
                $sheet->setCellValue('B' . $row++, $item->count);
            }
        }
        
        // Estilos
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A' . (count($statusData) + 4))->getFont()->setBold(true);
        
        // Auto size
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
    }
    
    private function createPerformanceSheet($spreadsheet)
    {
        $performance = User::role('Técnico')
            ->where('is_available', true)
            ->withCount([
                'assignedGrievances as total_tasks',
                'assignedGrievances as completed_tasks' => function ($query) {
                    $query->where('status', 'resolved')
                        ->whereBetween('resolved_at', [$this->startDate, $this->endDate]);
                },
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
                    'completion_rate' => $technician->total_tasks > 0 
                        ? round(($technician->completed_tasks / $technician->total_tasks) * 100, 2)
                        : 0,
                    'avg_resolution_time' => round($avgResolutionTime, 1),
                ];
            })
            ->sortByDesc('completion_rate')
            ->values();
        
        if ($performance->count() === 0) {
            return;
        }
        
        $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Desempenho');
        $spreadsheet->addSheet($sheet, 3);
        
        $headers = ['Nome', 'Email', 'Total Tarefas', 'Concluídas', 'Taxa Conclusão', 'Tempo Médio (h)'];
        
        $row = 1;
        $col = 0;
        foreach ($headers as $header) {
            $sheet->setCellValue(chr(65 + $col) . $row, $header);
            $col++;
        }
        
        $row++;
        foreach ($performance as $tech) {
            $sheet->setCellValue('A' . $row, $tech['name']);
            $sheet->setCellValue('B' . $row, $tech['email']);
            $sheet->setCellValue('C' . $row, $tech['total_tasks']);
            $sheet->setCellValue('D' . $row, $tech['completed_tasks']);
            $sheet->setCellValue('E' . $row, $tech['completion_rate'] . '%');
            $sheet->setCellValue('F' . $row, $tech['avg_resolution_time']);
            $row++;
        }
        
        // Estilos
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $fill = new \PhpOffice\PhpSpreadsheet\Style\Fill();
        $fill->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $fill->getStartColor()->setARGB('FFE0E0E0');
        $sheet->getStyle('A1:F1')->getFill()->applyFromArray($fill->getFillArray());
        
        // Auto size
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    private function generateCsvContent()
    {
        $summary = $this->getSummaryData();
        
        $submissions = Grievance::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->with(['user', 'assignedUser'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        
        $statusData = Grievance::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
        
        $typeData = Grievance::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();
        
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
        $csv .= "Funcionários Ativos," . $summary['active_employees'] . "\n";
        $csv .= "Submissões Hoje," . $summary['submissions_today'] . "\n";
        $csv .= "Submissões Esta Semana," . $summary['submissions_this_week'] . "\n";
        $csv .= "Submissões Este Mês," . $summary['submissions_this_month'] . "\n\n";
        
        $csv .= "DISTRIBUIÇÃO POR STATUS\n";
        $csv .= "Status,Quantidade\n";
        foreach ($statusData as $item) {
            $csv .= $this->getStatusLabel($item->status) . "," . $item->count . "\n";
        }
        $csv .= "\n";
        
        $csv .= "DISTRIBUIÇÃO POR TIPO\n";
        $csv .= "Tipo,Quantidade\n";
        foreach ($typeData as $item) {
            $csv .= $this->getTypeLabel($item->type) . "," . $item->count . "\n";
        }
        $csv .= "\n";
        
        $csv .= "ÚLTIMAS SUBMISSÕES\n";
        $csv .= "ID,Referência,Tipo,Prioridade,Status,Província,Data Criação\n";
        foreach ($submissions as $submission) {
            $csv .= $submission->id . ",";
            $csv .= ($submission->reference_number ?? 'N/A') . ",";
            $csv .= $this->getTypeLabel($submission->type) . ",";
            $csv .= $this->getPriorityLabel($submission->priority) . ",";
            $csv .= $this->getStatusLabel($submission->status) . ",";
            $csv .= ($submission->province ?? 'N/A') . ",";
            $csv .= ($submission->created_at ? $submission->created_at->format('d/m/Y H:i:s') : 'N/A') . "\n";
        }
        
        return $csv;
    }
    
    private function getSummaryData()
    {
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
    }
    
    private function getPeriodLabel($period)
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
    
    private function getTypeLabel($type)
    {
        $labels = [
            'grievance' => 'Queixa',
            'complaint' => 'Reclamação',
            'suggestion' => 'Sugestão',
        ];
        
        return $labels[$type] ?? $type;
    }
    
    private function getPriorityLabel($priority)
    {
        $labels = [
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta',
            'critical' => 'Crítica',
        ];
        
        return $labels[$priority] ?? $priority;
    }
    
    private function getStatusLabel($status)
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
}