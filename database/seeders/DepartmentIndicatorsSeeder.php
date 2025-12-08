<?php

namespace Database\Seeders;

use App\Models\DepartmentIndicator;
use Illuminate\Database\Seeder;

class DepartmentIndicatorsSeeder extends Seeder
{
    public function run(): void
    {
        $indicators = [
            [
                'name' => 'Taxa de Resolução',
                'slug' => 'resolution_rate',
                'category' => 'performance',
                'description' => 'Percentual de reclamações resolvidas em relação ao total',
                'calculation_formula' => 'resolved_rate',
                'measurement_unit' => 'percentage',
                'target_value' => 85.00,
                'min_value' => 0.00,
                'max_value' => 100.00,
                'display_order' => 1,
                'is_active' => true,
                'metadata' => [
                    'benchmark' => 'Indústria: 80%',
                    'importance' => 'high',
                    'update_frequency' => 'daily'
                ]
            ],
            [
                'name' => 'Tempo Médio de Resolução',
                'slug' => 'avg_resolution_time',
                'category' => 'efficiency',
                'description' => 'Tempo médio (em dias) para resolver uma reclamação',
                'calculation_formula' => 'avg_resolution_time',
                'measurement_unit' => 'days',
                'target_value' => 7.00,
                'min_value' => 0.00,
                'max_value' => 30.00,
                'display_order' => 2,
                'is_active' => true,
                'metadata' => [
                    'benchmark' => 'SLA: 10 dias',
                    'importance' => 'high',
                    'update_frequency' => 'daily'
                ]
            ],
            [
                'name' => 'Volume Total de Reclamações',
                'slug' => 'total_complaints',
                'category' => 'volume',
                'description' => 'Número total de reclamações recebidas',
                'calculation_formula' => 'total_complaints',
                'measurement_unit' => 'count',
                'target_value' => null,
                'min_value' => 0.00,
                'max_value' => null,
                'display_order' => 3,
                'is_active' => true,
                'metadata' => [
                    'importance' => 'medium',
                    'update_frequency' => 'daily'
                ]
            ],
            [
                'name' => 'Taxa de Reincidência',
                'slug' => 'recurrence_rate',
                'category' => 'quality',
                'description' => 'Percentual de reclamações repetidas do mesmo utente',
                'calculation_formula' => 'custom',
                'measurement_unit' => 'percentage',
                'target_value' => 5.00,
                'min_value' => 0.00,
                'max_value' => 100.00,
                'display_order' => 4,
                'is_active' => true,
                'metadata' => [
                    'benchmark' => 'Meta: < 5%',
                    'importance' => 'medium',
                    'update_frequency' => 'weekly'
                ]
            ],
            [
                'name' => 'Satisfação do Utente',
                'slug' => 'customer_satisfaction',
                'category' => 'satisfaction',
                'description' => 'Nível de satisfação medido através de feedback',
                'calculation_formula' => 'satisfaction_rate',
                'measurement_unit' => 'rating',
                'target_value' => 4.50,
                'min_value' => 1.00,
                'max_value' => 5.00,
                'display_order' => 5,
                'is_active' => true,
                'metadata' => [
                    'benchmark' => 'Meta: > 4.0',
                    'importance' => 'high',
                    'update_frequency' => 'monthly'
                ]
            ],
            [
                'name' => 'Tempo de Primeira Resposta',
                'slug' => 'first_response_time',
                'category' => 'responsiveness',
                'description' => 'Tempo médio (em horas) para primeira resposta',
                'calculation_formula' => 'custom',
                'measurement_unit' => 'hours',
                'target_value' => 24.00,
                'min_value' => 0.00,
                'max_value' => 168.00,
                'display_order' => 6,
                'is_active' => true,
                'metadata' => [
                    'benchmark' => 'SLA: 24 horas',
                    'importance' => 'medium',
                    'update_frequency' => 'daily'
                ]
            ],
            [
                'name' => 'Taxa de Conformidade com SLA',
                'slug' => 'sla_compliance_rate',
                'category' => 'compliance',
                'description' => 'Percentual de casos resolvidos dentro do SLA',
                'calculation_formula' => 'custom',
                'measurement_unit' => 'percentage',
                'target_value' => 95.00,
                'min_value' => 0.00,
                'max_value' => 100.00,
                'display_order' => 7,
                'is_active' => true,
                'metadata' => [
                    'benchmark' => 'SLA: > 95%',
                    'importance' => 'high',
                    'update_frequency' => 'weekly'
                ]
            ],
        ];

        foreach ($indicators as $indicator) {
            DepartmentIndicator::firstOrCreate(
                ['slug' => $indicator['slug']],
                $indicator
            );
        }
    }
}