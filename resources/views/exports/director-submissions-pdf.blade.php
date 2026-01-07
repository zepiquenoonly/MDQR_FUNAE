<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Relatório do Director' }}</title>
    <style>
        @page {
            margin: 15px;
            size: {{ isset($is_simple_list) && $is_simple_list ? 'portrait' : 'landscape' }};
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            line-height: 1.4;
            color: #333;
        }
        
        /* HEADER */
        .report-header {
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #3b82f6;
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
            font-size: 8px;
            color: #555;
        }
        
        /* ERROR STATE */
        .error-state {
            text-align: center;
            padding: 50px 20px;
            color: #ef4444;
        }
        
        .error-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .error-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .error-message {
            font-size: 14px;
            color: #6b7280;
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
            grid-template-columns: repeat({{ isset($is_simple_list) && $is_simple_list ? '2' : '4' }}, 1fr);
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
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 3px;
        }
        
        .stat-label {
            font-size: 8px;
            color: #6b7280;
        }
        
        /* TABLES */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 7px;
        }
        
        .data-table th {
            background-color: #3b82f6;
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
            font-size: 10px;
            font-weight: bold;
            color: #334155;
            margin-bottom: 8px;
        }
        
        .distribution-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 8px;
        }
        
        /* STATUS BADGES */
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: bold;
        }
        
        .status-submitted { background: #dbeafe; color: #1e40af; }
        .status-under_review { background: #fef3c7; color: #92400e; }
        .status-assigned { background: #d1fae5; color: #065f46; }
        .status-in_progress { background: #e0e7ff; color: #3730a3; }
        .status-resolved { background: #dcfce7; color: #166534; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-escalated { background: #fce7f3; color: #9d174d; }
        
        /* PRIORITY BADGES */
        .priority-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: bold;
        }
        
        .priority-low { background: #d1fae5; color: #065f46; }
        .priority-medium { background: #fef3c7; color: #92400e; }
        .priority-high { background: #fed7aa; color: #9a3412; }
        .priority-critical { background: #fee2e2; color: #991b1b; }
        .priority-urgent { background: #fecaca; color: #dc2626; }
        
        /* DIRECTOR INTERVENTION BADGE */
        .intervention-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: bold;
            background: #c7d2fe;
            color: #3730a3;
        }
        
        /* FOOTER */
        .report-footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 7px;
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
        .w-100 { width: 100%; }
        
        /* SPECIFIC FOR DIRECTOR */
        .director-badge {
            display: inline-block;
            padding: 3px 8px;
            background: #3b82f6;
            color: white;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- ERROR STATE -->
    @if(isset($is_error) && $is_error)
        <div class="error-state">
            <div class="error-icon">⚠️</div>
            <div class="error-title">{{ $title ?? 'Erro' }}</div>
            <div class="error-message">{{ $error_message ?? 'Ocorreu um erro ao gerar o relatório.' }}</div>
            <div class="report-footer">
                <div>Gerado em: {{ $export_date ?? now()->format('d/m/Y H:i') }}</div>
                <div>Sistema de Gestão de Reclamações - {{ date('Y') }}</div>
            </div>
        </div>
    @else
        <!-- HEADER -->
        <div class="report-header">
            <div class="report-title">{{ $title }}</div>
            <div class="report-subtitle">{{ $subtitle }}</div>
            <div class="report-meta">
                <div>
                    <strong>Director:</strong> {{ $user->name ?? $user_name ?? 'Director' }}<br>
                    <strong>Período:</strong> {{ $period ?? 'Todo o período' }}
                </div>
                <div class="text-right">
                    <strong>Exportado em:</strong> {{ $export_date }}<br>
                    <strong>Total de registros:</strong> {{ $total_grievances ?? 0 }}
                </div>
            </div>
        </div>
        
        <!-- FILTROS APLICADOS -->
        @if(!empty($filters_applied))
        <div class="mb-10">
            <div class="section-title">Filtros Aplicados</div>
            <div style="font-size: 8px; color: #6b7280;">
                @foreach($filters_applied as $key => $value)
                    <strong>{{ ucfirst($key) }}:</strong> {{ $value }}@if(!$loop->last), @endif
                @endforeach
            </div>
        </div>
        @endif
        
        <!-- ESTATÍSTICAS PRINCIPAIS (somente para relatório completo) -->
        @if(!isset($is_simple_list) || !$is_simple_list)
            @if(isset($statistics))
            <div class="section-title">Resumo Estatístico - Director</div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">{{ $statistics['total'] ?? 0 }}</div>
                    <div class="stat-label">Total de Submissões</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $statistics['escalated_count'] ?? 0 }}</div>
                    <div class="stat-label">Escaladas</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $statistics['with_director_intervention'] ?? 0 }}</div>
                    <div class="stat-label">Com Minha Intervenção</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $statistics['assigned_to_director'] ?? 0 }}</div>
                    <div class="stat-label">Atribuídas a Mim</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $statistics['resolved_count'] ?? 0 }}</div>
                    <div class="stat-label">Resolvidas</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $statistics['resolution_rate'] ?? 0 }}%</div>
                    <div class="stat-label">Taxa de Resolução</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $statistics['new_last_7_days'] ?? 0 }}</div>
                    <div class="stat-label">Novas (7 dias)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $statistics['pending_count'] ?? 0 }}</div>
                    <div class="stat-label">Pendentes</div>
                </div>
            </div>
            
            <!-- ESTATÍSTICAS DE RESOLUÇÃO -->
            @if(isset($resolution_stats) && $resolution_stats['total_resolved'] > 0)
            <div class="section-title">Estatísticas de Resolução</div>
            <div class="distribution-grid">
                <div class="distribution-box">
                    <div class="distribution-title">Tempos de Resolução</div>
                    <div class="distribution-item">
                        <span>Tempo Médio:</span>
                        <span>{{ $resolution_stats['average_resolution_time_days'] ?? 0 }} dias</span>
                    </div>
                    <div class="distribution-item">
                        <span>Mais Rápida:</span>
                        <span>{{ isset($resolution_stats['fastest_resolution_hours']) ? round($resolution_stats['fastest_resolution_hours'] / 24, 1) : 0 }} dias</span>
                    </div>
                    <div class="distribution-item">
                        <span>Mais Lenta:</span>
                        <span>{{ isset($resolution_stats['slowest_resolution_hours']) ? round($resolution_stats['slowest_resolution_hours'] / 24, 1) : 0 }} dias</span>
                    </div>
                    <div class="distribution-item">
                        <span>Total Resolvidas:</span>
                        <span>{{ $resolution_stats['total_resolved'] ?? 0 }}</span>
                    </div>
                </div>
                
                <div class="distribution-box">
                    <div class="distribution-title">Distribuição por Tempo</div>
                    @if(isset($resolution_stats['resolution_time_distribution']))
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
                    @endif
                </div>
            </div>
            @endif
            
            <!-- DISTRIBUIÇÕES -->
            @if(isset($distributions))
            <div class="section-title">Distribuições</div>
            <div class="distribution-grid">
                <!-- Por Status -->
                @if(isset($statistics['by_status']))
                <div class="distribution-box">
                    <div class="distribution-title">Por Estado</div>
                    @foreach($statistics['by_status'] as $status => $count)
                    <div class="distribution-item">
                        <span>{{ $status }}</span>
                        <span>{{ $count }} ({{ $statistics['total'] > 0 ? round(($count / $statistics['total']) * 100, 1) : 0 }}%)</span>
                    </div>
                    @endforeach
                </div>
                @endif
                
                <!-- Por Gestor -->
                @if(isset($distributions['by_manager']))
                <div class="distribution-box">
                    <div class="distribution-title">Top 10 Gestores</div>
                    @foreach($distributions['by_manager'] as $manager)
                    <div class="distribution-item">
                        <span>{{ $manager['manager_name'] }}</span>
                        <span>{{ $manager['count'] }} ({{ $manager['resolved'] }} resolvidas)</span>
                    </div>
                    @endforeach
                </div>
                @endif
                
                <!-- Por Mês -->
                @if(isset($distributions['by_month']))
                <div class="distribution-box">
                    <div class="distribution-title">Evolução Mensal (últimos 6 meses)</div>
                    @foreach($distributions['by_month'] as $monthData)
                    <div class="distribution-item">
                        <span>{{ $monthData['month'] }}</span>
                        <span>{{ $monthData['count'] }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
                
                <!-- Por Província -->
                @if(isset($distributions['by_province']))
                <div class="distribution-box">
                    <div class="distribution-title">Distribuição por Província</div>
                    @foreach($distributions['by_province'] as $provinceData)
                    <div class="distribution-item">
                        <span>{{ $provinceData['province'] }}</span>
                        <span>{{ $provinceData['count'] }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endif
            @endif
        @endif
        
        <!-- LISTA COMPLETA DE SUBMISSÕES -->
        <div class="section-title mt-15">
            @if(isset($is_simple_list) && $is_simple_list)
                Lista de Submissões
            @else
                Lista Detalhada de Submissões
            @endif
        </div>
        
        @if(isset($grievances) && count($grievances) > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th width="7%">Referência</th>
                    <th width="15%">Descrição</th>
                    <th width="5%">Tipo</th>
                    <th width="6%">Prioridade</th>
                    <th width="8%">Estado</th>
                    <th width="8%">Categoria</th>
                    <th width="10%">Projecto</th>
                    <th width="8%">Utente</th>
                    <th width="8%">Gestor/Técnico</th>
                    <th width="5%">Int. Dir.</th>
                    <th width="5%">Esc.</th>
                    <th width="8%">Data Submissão</th>
                    @if(!isset($is_simple_list) || !$is_simple_list)
                    <th width="7%">Data Resolução</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($grievances as $grievance)
                <tr>
                    <td>{{ $grievance['reference_number'] ?? '' }}</td>
                    <td>{{ Illuminate\Support\Str::limit($grievance['description'] ?? '', 30) }}</td>
                    <td>{{ $grievance['type'] ?? '' }}</td>
                    <td>
                        @if(isset($grievance['priority']))
                        <span class="priority-badge priority-{{ strtolower($grievance['priority']) }}">
                            {{ $grievance['priority'] }}
                        </span>
                        @endif
                    </td>
                    <td>
                        @if(isset($grievance['status']))
                        <span class="status-badge status-{{ str_replace(' ', '_', strtolower($grievance['status'])) }}">
                            {{ $grievance['status'] }}
                        </span>
                        @endif
                    </td>
                    <td>{{ Illuminate\Support\Str::limit($grievance['category'] ?? '', 12) }}</td>
                    <td>{{ Illuminate\Support\Str::limit($grievance['project'] ?? '', 15) }}</td>
                    <td>{{ Illuminate\Support\Str::limit($grievance['user_name'] ?? '', 12) }}</td>
                    <td>{{ Illuminate\Support\Str::limit($grievance['technician'] ?? '', 12) }}</td>
                    <td class="text-center">
                        @if(isset($grievance['has_director_intervention']) && $grievance['has_director_intervention'])
                            <span class="intervention-badge">Sim</span>
                        @else
                            Não
                        @endif
                    </td>
                    <td class="text-center">
                        @if(isset($grievance['escalated']) && $grievance['escalated'])
                            <span class="status-badge status-escalated">Sim</span>
                        @else
                            Não
                        @endif
                    </td>
                    <td>{{ $grievance['created_at'] ?? '' }}</td>
                    @if(!isset($is_simple_list) || !$is_simple_list)
                    <td>{{ $grievance['resolved_at'] ?? 'N/A' }}</td>
                    @endif
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
            <div class="director-badge">RELATÓRIO DO DIRECTOR</div>
            <div class="page-number"></div>
            <div>Sistema de Gestão de Reclamações - {{ date('Y') }}</div>
        </div>
    @endif
    
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $size = 7;
            $font = $fontMetrics->get_font("Arial, sans-serif");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 25;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>
</html>