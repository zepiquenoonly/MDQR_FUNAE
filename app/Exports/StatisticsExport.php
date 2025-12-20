<?php

namespace App\Exports;

use App\Models\Grievance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;

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
                $this->startDate = $now->copy()->startOfWeek();
                $this->endDate = $now->copy()->endOfWeek();
                break;
            case 'month':
                $this->startDate = $now->copy()->startOfMonth();
                $this->endDate = $now->copy()->endOfMonth();
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
                $this->startDate = $now->copy()->startOfYear();
                $this->endDate = $now->copy()->endOfYear();
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
     * Exportar para Excel
     */
    public function exportExcel($filename = null)
    {
        if (!$filename) {
            $filename = 'estatisticas-' . $this->period . '-' . now()->format('Y-m-d');
        }
        
        // Usar o Excel da versão 1.1
        $excel = app('excel');
        
        return $excel->create($filename, function(LaravelExcelWriter $excel) {
            
            // Sheet 1: Resumo Geral
            $excel->sheet('Resumo Geral', function(LaravelExcelWorksheet $sheet) {
                $this->buildSummarySheet($sheet);
            });
            
            // Sheet 2: Submissões
            $excel->sheet('Submissões', function(LaravelExcelWorksheet $sheet) {
                $this->buildSubmissionsSheet($sheet);
            });
            
            // Sheet 3: Desempenho
            $excel->sheet('Desempenho', function(LaravelExcelWorksheet $sheet) {
                $this->buildPerformanceSheet($sheet);
            });
            
        })->export('xlsx');
    }
    
    /**
     * Exportar para CSV
     */
    public function exportCsv($filename = null)
    {
        if (!$filename) {
            $filename = 'estatisticas-' . $this->period . '-' . now()->format('Y-m-d');
        }
        
        $excel = app('excel');
        
        return $excel->create($filename, function(LaravelExcelWriter $excel) {
            $excel->sheet('Estatísticas', function(LaravelExcelWorksheet $sheet) {
                $this->buildSummarySheet($sheet);
            });
        })->export('csv');
    }
    
    /**
     * Construir sheet de resumo
     */
    private function buildSummarySheet(LaravelExcelWorksheet $sheet)
    {
        $data = $this->getSummaryData();
        
        // Título
        $sheet->mergeCells('A1:L1');
        $sheet->cell('A1', function($cell) {
            $cell->setValue('ESTATÍSTICAS GERAIS')
                 ->setFont(['bold' => true, 'size' => 16])
                 ->setAlignment('center');
        });
        
        // Período
        $sheet->mergeCells('A2:L2');
        $sheet->cell('A2', function($cell) use ($data) {
            $cell->setValue('Período: ' . $data['period_label'] . ' (' . $data['start_date_formatted'] . ' - ' . $data['end_date_formatted'] . ')')
                 ->setFont(['bold' => true])
                 ->setAlignment('center');
        });
        
        // Linha em branco
        $sheet->appendRow(['']);
        
        // Cabeçalhos
        $headers = [
            'Métrica',
            'Valor',
            '',
            'Métrica',
            'Valor'
        ];
        
        $sheet->appendRow($headers);
        
        // Estilizar cabeçalhos
        $sheet->row(4, function($row) {
            $row->setFont(['bold' => true])
                ->setBackground('#CCCCCC');
        });
        
        // Dados (em duas colunas)
        $rowData = [
            ['Total de Submissões', $data['total_submissions'], '', 'Resolvidas', $data['total_resolved']],
            ['Taxa de Resolução', $data['resolution_rate'] . '%', '', 'Pendentes', $data['pending_submissions']],
            ['Tempo Médio', $data['avg_resolution_time'] . 'h', '', 'Submissões Hoje', $data['submissions_today']],
            ['Taxa de Crescimento', $data['growth_rate'] . '%', '', '', ''],
            ['', '', '', '', ''],
            ['Exportado por:', $data['exported_by'], '', 'Data Exportação:', $data['export_date']]
        ];
        
        foreach ($rowData as $row) {
            $sheet->appendRow($row);
        }
        
        // Auto dimensionar colunas
        $sheet->setAutoSize(true);
    }
    
    /**
     * Construir sheet de submissões
     */
    private function buildSubmissionsSheet(LaravelExcelWorksheet $sheet)
    {
        $submissions = $this->getSubmissionsData();
        
        if ($submissions->isEmpty()) {
            $sheet->cell('A1', function($cell) {
                $cell->setValue('Nenhuma submissão encontrada no período selecionado.');
            });
            return;
        }
        
        // Cabeçalhos
        $headers = [
            'ID',
            'Nº Referência',
            'Descrição',
            'Tipo',
            'Prioridade',
            'Status',
            'Província',
            'Data Criação',
            'Submetido Por'
        ];
        
        $sheet->appendRow($headers);
        
        // Estilizar cabeçalhos
        $sheet->row(1, function($row) {
            $row->setFont(['bold' => true])
                ->setBackground('#CCCCCC');
        });
        
        // Dados
        foreach ($submissions as $grievance) {
            $row = [
                $grievance->id,
                $grievance->reference_number,
                $grievance->description,
                $this->getTypeLabel($grievance->type),
                $this->getPriorityLabel($grievance->priority),
                $this->getStatusLabel($grievance->status),
                $grievance->province,
                $grievance->created_at->format('d/m/Y H:i'),
                $grievance->user ? $grievance->user->name : 'Anónimo'
            ];
            
            $sheet->appendRow($row);
        }
        
        // Auto dimensionar colunas
        $sheet->setAutoSize(true);
    }
    
    /**
     * Construir sheet de desempenho
     */
    private function buildPerformanceSheet(LaravelExcelWorksheet $sheet)
    {
        $performance = $this->getPerformanceData();
        
        if ($performance->isEmpty()) {
            $sheet->cell('A1', function($cell) {
                $cell->setValue('Nenhum dado de desempenho disponível.');
            });
            return;
        }
        
        // Cabeçalhos
        $headers = [
            'ID',
            'Nome',
            'Total Tarefas',
            'Concluídas',
            'Pendentes',
            'Taxa Conclusão (%)',
            'Tempo Médio (h)'
        ];
        
        $sheet->appendRow($headers);
        
        // Estilizar cabeçalhos
        $sheet->row(1, function($row) {
            $row->setFont(['bold' => true])
                ->setBackground('#CCCCCC');
        });
        
        // Dados
        foreach ($performance as $tech) {
            $row = [
                $tech['id'],
                $tech['name'],
                $tech['total_tasks'],
                $tech['completed_tasks'],
                $tech['pending_tasks'],
                $tech['completion_rate'],
                $tech['avg_resolution_time']
            ];
            
            $sheet->appendRow($row);
        }
        
        // Auto dimensionar colunas
        $sheet->setAutoSize(true);
    }
    
    /**
     * Obter dados de resumo
     */
    private function getSummaryData()
    {
        // Total de submissões no período
        $totalSubmissions = Grievance::where('assigned_to', $this->user->id)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->count();
        
        // Total resolvido
        $totalResolved = Grievance::where('assigned_to', $this->user->id)
            ->where('status', 'resolved')
            ->whereBetween('resolved_at', [$this->startDate, $this->endDate])
            ->count();
        
        // Taxa de resolução
        $resolutionRate = $totalSubmissions > 0 
            ? round(($totalResolved / $totalSubmissions) * 100, 2)
            : 0;
        
        // Tempo médio de resolução (em horas)
        $avgResolutionTime = Grievance::where('assigned_to', $this->user->id)
            ->where('status', 'resolved')
            ->whereBetween('resolved_at', [$this->startDate, $this->endDate])
            ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, resolved_at)'));
        
        // Pendentes
        $pendingSubmissions = Grievance::where('assigned_to', $this->user->id)
            ->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress', 'pending_approval'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->count();
        
        // Submissões hoje
        $submissionsToday = Grievance::where('assigned_to', $this->user->id)
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->count();
        
        // Taxa de crescimento
        $growthRate = $this->calculateGrowthRate();
        
        return [
            'period_label' => $this->getPeriodLabel($this->period),
            'start_date_formatted' => $this->startDate->format('d/m/Y'),
            'end_date_formatted' => $this->endDate->format('d/m/Y'),
            'total_submissions' => $totalSubmissions,
            'total_resolved' => $totalResolved,
            'resolution_rate' => $resolutionRate,
            'avg_resolution_time' => round($avgResolutionTime ?? 0, 1),
            'pending_submissions' => $pendingSubmissions,
            'submissions_today' => $submissionsToday,
            'growth_rate' => $growthRate,
            'exported_by' => $this->user->name,
            'export_date' => now()->format('d/m/Y H:i:s')
        ];
    }
    
    /**
     * Obter dados de submissões
     */
    private function getSubmissionsData()
    {
        return Grievance::where('assigned_to', $this->user->id)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->with(['user'])
            ->limit(1000) // Limitar para performance
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Obter dados de desempenho
     */
    private function getPerformanceData()
    {
        $technicians = User::role('Técnico')->get();
        
        $data = [];
        
        foreach ($technicians as $technician) {
            $totalTasks = Grievance::where('assigned_to', $technician->id)
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->count();
            
            $completedTasks = Grievance::where('assigned_to', $technician->id)
                ->where('status', 'resolved')
                ->whereBetween('resolved_at', [$this->startDate, $this->endDate])
                ->count();
            
            $pendingTasks = Grievance::where('assigned_to', $technician->id)
                ->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress', 'pending_approval'])
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->count();
            
            $completionRate = $totalTasks > 0 
                ? round(($completedTasks / $totalTasks) * 100, 2)
                : 0;
            
            $avgTime = Grievance::where('assigned_to', $technician->id)
                ->where('status', 'resolved')
                ->whereBetween('resolved_at', [$this->startDate, $this->endDate])
                ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, resolved_at)'));
            
            $data[] = [
                'id' => $technician->id,
                'name' => $technician->name,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => $pendingTasks,
                'completion_rate' => $completionRate,
                'avg_resolution_time' => round($avgTime ?? 0, 1),
            ];
        }
        
        // Ordenar por tarefas concluídas
        usort($data, function($a, $b) {
            return $b['completed_tasks'] <=> $a['completed_tasks'];
        });
        
        return collect($data);
    }
    
    /**
     * Calcular taxa de crescimento
     */
    private function calculateGrowthRate()
    {
        // Calcular período anterior
        $daysDiff = $this->startDate->diffInDays($this->endDate);
        $previousStart = $this->startDate->copy()->subDays($daysDiff);
        $previousEnd = $this->startDate->copy()->subDay();
        
        $previousSubmissions = Grievance::where('assigned_to', $this->user->id)
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->count();
        
        $currentSubmissions = Grievance::where('assigned_to', $this->user->id)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->count();
        
        return $previousSubmissions > 0
            ? round((($currentSubmissions - $previousSubmissions) / $previousSubmissions) * 100, 2)
            : ($currentSubmissions > 0 ? 100 : 0);
    }
    
    /**
     * Obter label do período
     */
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
    
    /**
     * Obter label do tipo
     */
    private function getTypeLabel($type)
    {
        $labels = [
            'suggestion' => 'Sugestão',
            'grievance' => 'Queixa',
            'complaint' => 'Reclamação',
        ];
        
        return $labels[$type] ?? $type;
    }
    
    /**
     * Obter label da prioridade
     */
    private function getPriorityLabel($priority)
    {
        $labels = [
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta',
            'critical' => 'Crítica',
            'urgent' => 'Urgente',
        ];
        
        return $labels[$priority] ?? $priority;
    }
    
    /**
     * Obter label do status
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'submitted' => 'Submetido',
            'under_review' => 'Em Análise',
            'assigned' => 'Atribuído',
            'in_progress' => 'Em Progresso',
            'pending_approval' => 'Pendente Aprovação',
            'resolved' => 'Resolvido',
            'rejected' => 'Rejeitado',
            'closed' => 'Fechado',
        ];
        
        return $labels[$status] ?? $status;
    }

    public function store(string $format, string $path): void
{
    $excel = app('excel');

    $excel->create(pathinfo($path, PATHINFO_FILENAME), function (LaravelExcelWriter $excel) {

        $excel->sheet('Resumo Geral', function (LaravelExcelWorksheet $sheet) {
            $this->buildSummarySheet($sheet);
        });

        $excel->sheet('Submissões', function (LaravelExcelWorksheet $sheet) {
            $this->buildSubmissionsSheet($sheet);
        });

        $excel->sheet('Desempenho', function (LaravelExcelWorksheet $sheet) {
            $this->buildPerformanceSheet($sheet);
        });

    })->store($format, dirname($path), 'public');
}
}