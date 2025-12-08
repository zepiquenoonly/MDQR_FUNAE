<?php

namespace Database\Seeders;

use App\Models\DepartmentIndicator;
use App\Models\IndicatorRecord;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class IndicatorRecordsSeeder extends Seeder
{
    public function run(): void
    {
        // Primeiro, garanta que existem indicadores
        $indicators = DepartmentIndicator::all();
        
        if ($indicators->isEmpty()) {
            $this->call(DepartmentIndicatorsSeeder::class);
            $indicators = DepartmentIndicator::all();
        }
        
        // Para cada indicador, crie registros dos últimos 30 dias
        foreach ($indicators as $indicator) {
            $this->createRecordsForIndicator($indicator);
        }
        
        $this->command->info('Registros de indicadores criados com sucesso!');
        $this->command->info('Total de registros: ' . IndicatorRecord::count());
    }
    
    private function createRecordsForIndicator(DepartmentIndicator $indicator): void
    {
        $baseValues = $this->getBaseValuesForIndicator($indicator);
        $today = Carbon::now();
        
        // Cria registros para os últimos 30 dias
        for ($i = 0; $i < 30; $i++) {
            $date = $today->copy()->subDays($i);
            
            // Valor base com variação aleatória para simular tendência
            $baseValue = $baseValues[$indicator->slug] ?? 75;
            $variation = rand(-5, 5) / 10; // Variação de -0.5 a +0.5
            $value = max(0, $baseValue + ($i * $variation));
            
            // Garante que o valor está dentro dos limites do indicador
            if ($indicator->min_value !== null) {
                $value = max($indicator->min_value, $value);
            }
            if ($indicator->max_value !== null) {
                $value = min($indicator->max_value, $value);
            }
            
            // Cria ou atualiza o registro
            IndicatorRecord::updateOrCreate(
                [
                    'indicator_id' => $indicator->id,
                    'record_date' => $date->format('Y-m-d')
                ],
                [
                    'value' => round($value, 2),
                    'breakdown' => $this->generateBreakdownData($indicator, $value),
                    'notes' => 'Dado gerado automaticamente para demonstração'
                ]
            );
        }
    }
    
    private function getBaseValuesForIndicator(DepartmentIndicator $indicator): array
    {
        return [
            'resolution_rate' => 85.0,
            'avg_resolution_time' => 7.5,
            'total_complaints' => 150,
            'recurrence_rate' => 5.0,
            'customer_satisfaction' => 4.3,
            'first_response_time' => 24.0,
            'sla_compliance_rate' => 92.0,
        ];
    }
    
    private function generateBreakdownData(DepartmentIndicator $indicator, $value): array
    {
        return match($indicator->slug) {
            'resolution_rate' => [
                'resolved' => round(($value / 100) * rand(100, 200)),
                'total' => rand(100, 200),
                'by_category' => [
                    'Água' => rand(70, 95),
                    'Energia' => rand(75, 90),
                    'Infraestrutura' => rand(65, 85)
                ]
            ],
            'avg_resolution_time' => [
                'fastest' => rand(1, 3),
                'slowest' => rand(10, 15),
                'by_priority' => [
                    'Alta' => rand(2, 5),
                    'Média' => rand(5, 8),
                    'Baixa' => rand(8, 12)
                ]
            ],
            'total_complaints' => [
                'by_status' => [
                    'Resolvido' => round($value * 0.7),
                    'Em Progresso' => round($value * 0.2),
                    'Pendente' => round($value * 0.1)
                ],
                'by_province' => [
                    'Maputo' => round($value * 0.4),
                    'Gaza' => round($value * 0.2),
                    'Inhambane' => round($value * 0.15),
                    'Sofala' => round($value * 0.25)
                ]
            ],
            'customer_satisfaction' => [
                'by_rating' => [
                    '5 estrelas' => rand(40, 60),
                    '4 estrelas' => rand(20, 35),
                    '3 estrelas' => rand(10, 20),
                    '2 estrelas' => rand(3, 10),
                    '1 estrela' => rand(1, 5)
                ],
                'feedback_count' => rand(50, 150)
            ],
            default => [
                'source' => 'Sistema Automático',
                'calculated_at' => now()->toDateTimeString(),
                'sample_size' => rand(100, 1000)
            ]
        };
    }
}