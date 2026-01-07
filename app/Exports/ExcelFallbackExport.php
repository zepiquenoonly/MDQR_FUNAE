<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExcelFallbackExport
{
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
    
    public function save($filename, $disk = 'public')
    {
        $spreadsheet = new Spreadsheet();
        
        // Sheet 1: Resumo Geral
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Resumo Geral');
        
        // Adicionar conteúdo ao Sheet 1
        $row = 1;
        $sheet1->setCellValue('A' . $row++, 'ESTATÍSTICAS GERAIS - RELATÓRIO');
        $sheet1->setCellValue('A' . $row++, 'Sistema de Gestão de Reclamações');
        $row++;
        $sheet1->setCellValue('A' . $row++, 'PERÍODO DO RELATÓRIO');
        $sheet1->setCellValue('A' . $row, 'Período:');
        $sheet1->setCellValue('B' . $row, $this->summaryData['period_label']);
        $row++;
        $sheet1->setCellValue('A' . $row, 'Data Início:');
        $sheet1->setCellValue('B' . $row, $this->summaryData['start_date_formatted']);
        $row++;
        $sheet1->setCellValue('A' . $row, 'Data Fim:');
        $sheet1->setCellValue('B' . $row, $this->summaryData['end_date_formatted']);
        $row++;
        $sheet1->setCellValue('A' . $row, 'Exportado por:');
        $sheet1->setCellValue('B' . $row, $this->summaryData['exported_by']);
        $row++;
        $sheet1->setCellValue('A' . $row, 'Data Exportação:');
        $sheet1->setCellValue('B' . $row, $this->summaryData['export_date']);
        $row += 2;
        
        // Adicionar estilo...
        
        // Sheet 2: Submissões
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Submissões');
        // ... adicionar conteúdo ao Sheet 2
        
        // Sheet 3: Distribuições
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Distribuições');
        // ... adicionar conteúdo ao Sheet 3
        
        // Salvar arquivo
        $writer = new Xlsx($spreadsheet);
        $fullPath = storage_path("app/public/exports/{$filename}.xlsx");
        $writer->save($fullPath);
        
        return "exports/{$filename}.xlsx";
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