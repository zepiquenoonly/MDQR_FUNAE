<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 15px;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        
        /* HEADER */
        .report-header {
            border-bottom: 3px solid #f97316;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #f97316;
            margin-bottom: 5px;
        }
        
        .report-subtitle {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .report-meta {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #555;
        }
        
        /* STATISTICS SECTION */
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #374151;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 3px;
        }
        
        .stat-label {
            font-size: 9px;
            color: #6b7280;
        }
        
        /* TABLES */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 8px;
        }
        
        .data-table th {
            background-color: #f97316;
            color: white;
            padding: 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        
        .data-table td {
            border: 1px solid #ddd;
            padding: 4px;
            vertical-align: top;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        /* CHARTS/DISTRIBUTIONS */
        .distribution-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .distribution-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
        }
        
        .distribution-title {
            font-size: 11px;
            font-weight: bold;
            color: #334155;
            margin-bottom: 8px;
        }
        
        .distribution-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 9px;
        }
        
        /* STATUS BADGES */
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-in_progress { background: #dbeafe; color: #1e40af; }
        .status-resolved { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        
        /* PRIORITY BADGES */
        .priority-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .priority-low { background: #d1fae5; color: #065f46; }
        .priority-medium { background: #fef3c7; color: #92400e; }
        .priority-high { background: #fed7aa; color: #9a3412; }
        .priority-critical { background: #fee2e2; color: #991b1b; }
        
        /* FOOTER */
        .report-footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 8px;
            color: #6b7280;
            text-align: center;
        }
        
        .page-number:after {
            content: "Página " counter(page) " de " counter(pages);
        }
        
        /* UTILITIES */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mb-10 { margin-bottom: 10px; }
        .mt-15 { margin-top: 15px; }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="report-header">
        <div class="report-title">{{ $title }}</div>
        <div class="report-subtitle">{{ $subtitle }}</div>
        <div class="report-meta">
            <div>
                <strong>Gestor:</strong> {{ $user->name ?? $user_name ?? 'Gestor' }}<br>
                <strong>Período:</strong> {{ $period }}
            </div>
            <div class="text-right">
                <strong>Exportado em:</strong> {{ $export_date }}<br>
                <strong>Total de registros:</strong> {{ $total_grievances }}
            </div>
        </div>
    </div>
    
    <!-- FILTROS APLICADOS -->
    @if(!empty($filters_applied))
    <div class="mb-10">
        <div class="section-title">Filtros Aplicados</div>
        <div style="font-size: 9px; color: #6b7280;">
            @foreach($filters_applied as $key => $value)
                <strong>{{ ucfirst($key) }}:</strong> {{ $value }}@if(!$loop->last), @endif
            @endforeach
        </div>
    </div>
    @endif
    
    <!-- ESTATÍSTICAS PRINCIPAIS -->
    <div class="section-title">Resumo Estatístico</div>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $statistics['total'] }}</div>
            <div class="stat-label">Total de Submissões</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $statistics['resolved_count'] }}</div>
            <div class="stat-label">Resolvidas</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $statistics['resolution_rate'] }}%</div>
            <div class="stat-label">Taxa de Resolução</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $statistics['pending_count'] }}</div>
            <div class="stat-label">Pendentes</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $statistics['escalated_count'] }}</div>
            <div class="stat-label">Escaladas</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $statistics['with_director_intervention'] }}</div>
            <div class="stat-label">Com Intervenção do Director</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $statistics['new_last_7_days'] }}</div>
            <div class="stat-label">Novas (7 dias)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $statistics['average_updates_per_grievance'] }}</div>
            <div class="stat-label">Atualizações Média</div>
        </div>
    </div>
    
    <!-- ESTATÍSTICAS DE RESOLUÇÃO -->
    @if($resolution_stats['total_resolved'] > 0)
    <div class="section-title">Estatísticas de Resolução</div>
    <div class="distribution-grid">
        <div class="distribution-box">
            <div class="distribution-title">Tempos de Resolução</div>
            <div class="distribution-item">
                <span>Tempo Médio:</span>
                <span>{{ $resolution_stats['average_resolution_time_days'] }} dias</span>
            </div>
            <div class="distribution-item">
                <span>Mais Rápida:</span>
                <span>{{ round($resolution_stats['fastest_resolution_hours'] / 24, 1) }} dias</span>
            </div>
            <div class="distribution-item">
                <span>Mais Lenta:</span>
                <span>{{ round($resolution_stats['slowest_resolution_hours'] / 24, 1) }} dias</span>
            </div>
            <div class="distribution-item">
                <span>Total Resolvidas:</span>
                <span>{{ $resolution_stats['total_resolved'] }}</span>
            </div>
        </div>
        
        <div class="distribution-box">
            <div class="distribution-title">Distribuição por Tempo</div>
            @foreach($resolution_stats['resolution_time_distribution'] as $range => $count)
            <div class="distribution-item">
                <span>
                    @if($range == 'menos_24h') Menos de 24h
                    @elseif($range == '1_3_dias') 1-3 dias
                    @elseif($range == '3_7_dias') 3-7 dias
                    @elseif($range == 'mais_7_dias') Mais de 7 dias
                    @else {{ $range }}
                    @endif
                </span>
                <span>{{ $count }} ({{ $resolution_stats['total_resolved'] > 0 ? round(($count / $resolution_stats['total_resolved']) * 100, 1) : 0 }}%)</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    <!-- DISTRIBUIÇÕES -->
    <div class="section-title">Distribuições</div>
    <div class="distribution-grid">
        <!-- Por Status -->
        <div class="distribution-box">
            <div class="distribution-title">Por Estado</div>
            @foreach($statistics['by_status'] as $status => $count)
            <div class="distribution-item">
                <span>{{ $status }}</span>
                <span>{{ $count }} ({{ $statistics['total'] > 0 ? round(($count / $statistics['total']) * 100, 1) : 0 }}%)</span>
            </div>
            @endforeach
        </div>
        
        <!-- Por Tipo -->
        <div class="distribution-box">
            <div class="distribution-title">Por Tipo</div>
            @foreach($statistics['by_type'] as $type => $count)
            <div class="distribution-item">
                <span>{{ $type }}</span>
                <span>{{ $count }} ({{ $statistics['total'] > 0 ? round(($count / $statistics['total']) * 100, 1) : 0 }}%)</span>
            </div>
            @endforeach
        </div>
        
        <!-- Por Prioridade -->
        <div class="distribution-box">
            <div class="distribution-title">Por Prioridade</div>
            @foreach($statistics['by_priority'] as $priority => $count)
            <div class="distribution-item">
                <span>{{ $priority }}</span>
                <span>{{ $count }} ({{ $statistics['total'] > 0 ? round(($count / $statistics['total']) * 100, 1) : 0 }}%)</span>
            </div>
            @endforeach
        </div>
        
        <!-- Por Mês -->
        <div class="distribution-box">
            <div class="distribution-title">Evolução Mensal (últimos 6 meses)</div>
            @foreach($distributions['by_month'] as $monthData)
            <div class="distribution-item">
                <span>{{ $monthData['month'] }}</span>
                <span>{{ $monthData['count'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
    
    <!-- TOP TÉCNICOS -->
    @if(!empty($distributions['by_technician']))
    <div class="section-title mt-15">Top 10 Técnicos</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Técnico</th>
                <th>Total Atribuído</th>
                <th>Resolvidas</th>
                <th>Pendentes</th>
                <th>Taxa Resolução</th>
            </tr>
        </thead>
        <tbody>
            @foreach($distributions['by_technician'] as $tech)
            <tr>
                <td>{{ $tech['technician_id'] }}</td>
                <td class="text-center">{{ $tech['count'] }}</td>
                <td class="text-center">{{ $tech['resolved'] }}</td>
                <td class="text-center">{{ $tech['pending'] }}</td>
                <td class="text-center">
                    {{ $tech['count'] > 0 ? round(($tech['resolved'] / $tech['count']) * 100, 1) : 0 }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    
    <!-- LISTA COMPLETA DE SUBMISSÕES -->
    <div class="section-title mt-15">Lista Detalhada de Submissões</div>
    
    @if($total_grievances > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th width="8%">Referência</th>
                <th width="20%">Descrição</th>
                <th width="6%">Tipo</th>
                <th width="8%">Prioridade</th>
                <th width="10%">Estado</th>
                <th width="10%">Categoria</th>
                <th width="12%">Projeto</th>
                <th width="10%">Utente</th>
                <th width="10%">Técnico</th>
                <th width="6%">Actual.</th>
                <th width="10%">Data Submissão</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grievances as $grievance)
            <tr>
                <td>{{ $grievance['reference_number'] }}</td>
                <td>{{ \Illuminate\Support\Str::limit($grievance['description'], 30) }}</td>
                <td>{{ $grievance['type'] }}</td>
                <td>
                    <span class="priority-badge priority-{{ strtolower($grievance['priority']) }}">
                        {{ $grievance['priority'] }}
                    </span>
                </td>
                <td>
                    <span class="status-badge status-{{ str_replace(' ', '_', strtolower($grievance['status'])) }}">
                        {{ $grievance['status'] }}
                    </span>
                </td>
                <td>{{ \Illuminate\Support\Str::limit($grievance['category'], 12) }}</td>
                <td>{{ \Illuminate\Support\Str::limit($grievance['project'], 15) }}</td>
                <td>{{ \Illuminate\Support\Str::limit($grievance['user_name'], 12) }}</td>
                <td>{{ \Illuminate\Support\Str::limit($grievance['technician'], 12) }}</td>
                <td class="text-center">{{ $grievance['updates_count'] }}</td>
                <td>{{ $grievance['submitted_at'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="text-align: center; padding: 20px; color: #6b7280; font-style: italic;">
        Nenhuma submissão encontrada com os filtros aplicados.
    </div>
    @endif
    
    <!-- FOOTER -->
    <div class="report-footer">
        <div class="page-number"></div>
        <div>Sistema de Gestão de Reclamações - {{ date('Y') }}</div>
    </div>
    
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->get_font("Arial, sans-serif");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 25;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>
</html>