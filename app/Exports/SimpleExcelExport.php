<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SimpleExcelExport
{
    use Exportable;

    protected $summaryData;
    protected $submissionsData;
    protected $performanceData;
    protected $chartData;
    
    public function __construct($summaryData, $submissionsData, $performanceData, $chartData)
    {
        $this->summaryData = $summaryData;
        $this->submissionsData = $submissionsData;
        $this->performanceData = $performanceData;
        $this->chartData = $chartData;
    }
    
    public function array()
    {
        // Criar um array com múltiplas abas como arrays separados
        $sheets = [];
        
        // Sheet 1: Resumo Geral
        $sheets['Resumo Geral'] = [
            ['ESTATÍSTICAS GERAIS - RELATÓRIO'],
            ['Sistema de Gestão de Reclamações'],
            [''],
            ['PERÍODO DO RELATÓRIO'],
            ['Período:', $this->summaryData['period_label']],
            ['Data Início:', $this->summaryData['start_date_formatted']],
            ['Data Fim:', $this->summaryData['end_date_formatted']],
            ['Exportado por:', $this->summaryData['exported_by']],
            ['Data Exportação:', $this->summaryData['export_date']],
            [''],
            ['RESUMO EXECUTIVO'],
            ['Métrica', 'Valor'],
            ['Total de Submissões', $this->summaryData['total_submissions']],
            ['Submissões Resolvidas', $this->summaryData['total_resolved']],
            ['Taxa de Resolução', $this->summaryData['resolution_rate'] . '%'],
            ['Tempo Médio de Resolução', $this->summaryData['avg_resolution_time'] . 'h'],
            ['Submissões Pendentes', $this->summaryData['pending_submissions']],
            ['Taxa de Crescimento', $this->summaryData['growth_rate'] . '%'],
            ['Funcionários Ativos', $this->summaryData['active_employees']],
            ['Submissões Hoje', $this->summaryData['submissions_today']],
            ['Submissões Esta Semana', $this->summaryData['submissions_this_week']],
            ['Submissões Este Mês', $this->summaryData['submissions_this_month']],
        ];
        
        // Sheet 2: Submissões
        $submissionsRows = [];
        if ($this->submissionsData && $this->submissionsData->count() > 0) {
            $submissionsRows[] = ['ID', 'Referência', 'Descrição', 'Tipo', 'Prioridade', 'Status', 'Província', 'Data Criação', 'Submetido por', 'Atribuído a'];
            
            foreach ($this->submissionsData as $submission) {
                $submissionsRows[] = [
                    $submission->id,
                    $submission->reference_number ?? 'N/A',
                    $submission->description ? substr($submission->description, 0, 100) . (strlen($submission->description) > 100 ? '...' : '') : 'Sem descrição',
                    $this->getTypeLabel($submission->type),
                    $this->getPriorityLabel($submission->priority),
                    $this->getStatusLabel($submission->status),
                    $submission->province ?? 'N/A',
                    $submission->created_at ? $submission->created_at->format('d/m/Y H:i:s') : 'N/A',
                    $submission->user->name ?? 'Anônimo',
                    $submission->assignedUser->name ?? 'N/A',
                ];
            }
        } else {
            $submissionsRows[] = ['Nenhuma submissão encontrada no período selecionado.'];
        }
        $sheets['Submissões'] = $submissionsRows;
        
        // Sheet 3: Desempenho
        if ($this->performanceData && $this->performanceData->count() > 0) {
            $performanceRows = [];
            $performanceRows[] = ['Nome', 'Email', 'Total Tarefas', 'Concluídas', 'Pendentes', 'Taxa Conclusão', 'Tempo Médio'];
            
            foreach ($this->performanceData as $tech) {
                $performanceRows[] = [
                    $tech['name'] ?? 'N/A',
                    $tech['email'] ?? 'N/A',
                    $tech['total_tasks'] ?? 0,
                    $tech['completed_tasks'] ?? 0,
                    $tech['pending_tasks'] ?? 0,
                    ($tech['completion_rate'] ?? 0) . '%',
                    ($tech['avg_resolution_time'] ?? 0) . 'h',
                ];
            }
            $sheets['Desempenho'] = $performanceRows;
        }
        
        // Sheet 4: Distribuições
        $distributionRows = [
            ['DISTRIBUIÇÃO POR STATUS'],
            ['Status', 'Quantidade'],
        ];
        
        if (isset($this->chartData['status_distribution']) && is_array($this->chartData['status_distribution'])) {
            foreach ($this->chartData['status_distribution'] as $status => $count) {
                $distributionRows[] = [$status, $count];
            }
        } else {
            $distributionRows[] = ['Sem dados disponíveis', 0];
        }
        
        $distributionRows[] = [''];
        $distributionRows[] = ['DISTRIBUIÇÃO POR TIPO'];
        $distributionRows[] = ['Tipo', 'Quantidade'];
        
        if (isset($this->chartData['type_distribution']) && is_array($this->chartData['type_distribution'])) {
            foreach ($this->chartData['type_distribution'] as $type => $count) {
                $distributionRows[] = [$type, $count];
            }
        } else {
            $distributionRows[] = ['Sem dados disponíveis', 0];
        }
        
        if (isset($this->chartData['priority_distribution']) && is_array($this->chartData['priority_distribution'])) {
            $distributionRows[] = [''];
            $distributionRows[] = ['DISTRIBUIÇÃO POR PRIORIDADE'];
            $distributionRows[] = ['Prioridade', 'Quantidade'];
            
            foreach ($this->chartData['priority_distribution'] as $priority => $count) {
                $distributionRows[] = [$priority, $count];
            }
        }
        
        $sheets['Distribuições'] = $distributionRows;
        
        return $sheets;
    }
    
    private function getTypeLabel($type)
    {
        $labels = [
            'grievance' => 'Queixa',
            'complaint' => 'Reclamação', 
            'suggestion' => 'Sugestão'
        ];
        return $labels[$type] ?? $type;
    }
    
    private function getPriorityLabel($priority)
    {
        $labels = [
            'low' => 'Baixa',
            'medium' => 'Média', 
            'high' => 'Alta',
            'critical' => 'Crítica'
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