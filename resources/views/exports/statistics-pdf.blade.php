<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Estatísticas</title>
    <style>
        /* Reset e configurações gerais */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 13px;
            line-height: 1.6;
            color: #2c3e50;
            background-color: #ffffff;
            padding: 20px;
        }
        
        /* Cabeçalho */
        .cabecalho {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 25px;
            border-bottom: 3px solid #2c3e50;
            position: relative;
        }
        
        .cabecalho::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 25%;
            width: 50%;
            height: 3px;
            background: linear-gradient(90deg, #3498db, #2ecc71);
        }
        
        .logo {
            max-height: 90px;
            margin-bottom: 15px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }
        
        h1 {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin: 15px 0 8px;
            letter-spacing: 0.5px;
        }
        
        .subtitulo {
            font-size: 16px;
            color: #7f8c8d;
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        /* Informações do período */
        .info-periodo {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 10px;
            margin: 25px 0 35px;
            border-left: 5px solid #3498db;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .info-linha {
            display: flex;
            margin-bottom: 8px;
            align-items: center;
        }
        
        .info-label {
            font-weight: 600;
            color: #2c3e50;
            min-width: 140px;
        }
        
        .info-valor {
            color: #34495e;
            font-weight: 500;
        }
        
        /* Seções */
        h2 {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            margin: 35px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
            position: relative;
        }
        
        h2::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 80px;
            height: 2px;
            background: #3498db;
        }
        
        h3 {
            font-size: 16px;
            font-weight: 600;
            color: #34495e;
            margin: 25px 0 15px;
        }
        
        /* KPI Cards */
        .grid-kpi {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }
        
        .card-kpi {
            background: white;
            border: 1px solid #e1e8ed;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .card-kpi:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        
        .valor-kpi {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin: 12px 0;
            line-height: 1.2;
        }
        
        .label-kpi {
            font-size: 13px;
            color: #7f8c8d;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Tabelas */
        .container-tabela {
            margin: 25px 0;
            page-break-inside: avoid;
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 15px 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        thead {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }
        
        th {
            color: white;
            padding: 14px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-right: 1px solid rgba(255,255,255,0.1);
        }
        
        th:last-child {
            border-right: none;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #ecf0f1;
            color: #2c3e50;
        }
        
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        tr:hover {
            background-color: #f1f8ff;
        }
        
        /* Barras de gráfico */
        .container-grafico {
            margin: 25px 0;
        }
        
        .titulo-grafico {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #2c3e50;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .contador-grafico {
            font-size: 12px;
            color: #7f8c8d;
            font-weight: 500;
        }
        
        .barra-grafico {
            background: linear-gradient(90deg, #3498db, #3dc2ff);
            height: 24px;
            margin: 6px 0;
            border-radius: 12px;
            color: white;
            padding: 0 15px;
            display: flex;
            align-items: center;
            font-size: 11px;
            font-weight: 600;
            position: relative;
            overflow: hidden;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .barra-grafico::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: brilho 3s infinite;
        }
        
        @keyframes brilho {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        /* Números de ranking */
        .numero-ranking {
            display: inline-block;
            width: 28px;
            height: 28px;
            background: #3498db;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 28px;
            font-weight: 700;
            margin-right: 10px;
            font-size: 12px;
        }
        
        .numero-ranking.primeiro { background: linear-gradient(135deg, #f39c12, #e67e22); }
        .numero-ranking.segundo { background: linear-gradient(135deg, #95a5a6, #7f8c8d); }
        .numero-ranking.terceiro { background: linear-gradient(135deg, #d35400, #e74c3c); }
        
        /* Quebra de página */
        .quebra-pagina {
            page-break-before: always;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px dashed #ddd;
        }
        
        /* Rodapé */
        .rodape {
            margin-top: 60px;
            padding-top: 25px;
            border-top: 2px solid #ecf0f1;
            font-size: 11px;
            color: #7f8c8d;
            text-align: center;
        }
        
        .assinatura {
            margin-top: 50px;
            text-align: center;
        }
        
        .linha-assinatura {
            width: 300px;
            border-top: 2px solid #2c3e50;
            margin: 40px auto 10px;
        }
        
        .texto-assinatura {
            font-size: 12px;
            color: #7f8c8d;
            font-style: italic;
        }
        
        /* Responsividade para impressão */
        @media print {
            body {
                padding: 15px;
                font-size: 12px;
            }
            
            .card-kpi {
                break-inside: avoid;
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            table {
                break-inside: avoid;
            }
            
            .quebra-pagina {
                page-break-before: always;
            }
            
            .barra-grafico::before {
                animation: none;
            }
        }
        
        /* Melhorias para cores */
        .destaque {
            color: #e74c3c;
            font-weight: 700;
        }
        
        .positivo {
            color: #27ae60;
            font-weight: 600;
        }
        
        .negativo {
            color: #e74c3c;
            font-weight: 600;
        }
        
        .neutral {
            color: #f39c12;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <div class="cabecalho">
        @if($logo)
            <img src="{{ $logo }}" alt="Logo MDQR" class="logo">
        @endif
        <h1>RELATÓRIO DE ESTATÍSTICAS</h1>
        <div class="subtitulo">MDQR</div>
        <div style="font-size: 14px; color: #3498db; font-weight: 500;">MDQR - Monitoramento de Desempenho e Qualidade</div>
    </div>
    
    <!-- Informações do Período -->
    <div class="info-periodo">
        <div class="info-linha">
            <span class="info-label">Período Analisado:</span>
            <span class="info-valor">{{ $period_label }}</span>
        </div>
        <div class="info-linha">
            <span class="info-label">Intervalo Temporal:</span>
            <span class="info-valor">{{ $start_date }} até {{ $end_date }}</span>
        </div>
        <div class="info-linha">
            <span class="info-label">Responsável pela Exportação:</span>
            <span class="info-valor">{{ $exported_by }}</span>
        </div>
        <div class="info-linha">
            <span class="info-label">Data da Geração:</span>
            <span class="info-valor destaque">{{ $export_date }}</span>
        </div>
    </div>
    
    <!-- Resumo Executivo -->
    <h2>RESUMO EXECUTIVO</h2>
    
    <div class="grid-kpi">
        <div class="card-kpi">
            <div class="label-kpi">Total de Submissões</div>
            <div class="valor-kpi">{{ number_format($summary['total_submissions'], 0, ',', '.') }}</div>
            <div style="font-size: 11px; color: #7f8c8d; margin-top: 5px;">Todas as solicitações recebidas</div>
        </div>
        
        <div class="card-kpi">
            <div class="label-kpi">Taxa de Resolução</div>
            <div class="valor-kpi @if($summary['resolution_rate'] >= 80) positivo @elseif($summary['resolution_rate'] >= 60) neutral @else negativo @endif">
                {{ $summary['resolution_rate'] }}%
            </div>
            <div style="font-size: 11px; color: #7f8c8d; margin-top: 5px;">Casos concluídos com sucesso</div>
        </div>
        
        <div class="card-kpi">
            <div class="label-kpi">Tempo Médio de Resolução</div>
            <div class="valor-kpi @if($summary['avg_resolution_time'] <= 24) positivo @elseif($summary['avg_resolution_time'] <= 48) neutral @else negativo @endif">
                {{ $summary['avg_resolution_time'] }}h
            </div>
            <div style="font-size: 11px; color: #7f8c8d; margin-top: 5px;">Desde atribuição até conclusão</div>
        </div>
        
        <div class="card-kpi">
            <div class="label-kpi">Submissões Pendentes</div>
            <div class="valor-kpi">{{ $summary['pending_submissions'] }}</div>
            <div style="font-size: 11px; color: #7f8c8d; margin-top: 5px;">Aguardando processamento</div>
        </div>
        
        <div class="card-kpi">
            <div class="label-kpi">Taxa de Crescimento</div>
            <div class="valor-kpi @if($summary['growth_rate'] >= 0) positivo @else negativo @endif">
                {{ $summary['growth_rate'] >= 0 ? '+' : '' }}{{ $summary['growth_rate'] }}%
            </div>
            <div style="font-size: 11px; color: #7f8c8d; margin-top: 5px;">Variação em relação ao período anterior</div>
        </div>
        
        <div class="card-kpi">
            <div class="label-kpi">Funcionários Ativos</div>
            <div class="valor-kpi">{{ $summary['active_employees'] }}</div>
            <div style="font-size: 11px; color: #7f8c8d; margin-top: 5px;">Equipe disponível para atendimento</div>
        </div>
    </div>
    
    <!-- Distribuição por Status -->
    <div class="container-tabela">
        <h2>DISTRIBUIÇÃO POR STATUS</h2>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Quantidade</th>
                    <th>Percentagem</th>
                    <th>Proporção</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalStatus = array_sum($charts['status']);
                    $statusColors = [
                        'Submetida' => '#3498db',
                        'Em Revisão' => '#f39c12',
                        'Atribuída' => '#9b59b6',
                        'Em Progresso' => '#1abc9c',
                        'Aprovação Pendente' => '#e67e22',
                        'Resolvida' => '#27ae60',
                        'Rejeitada' => '#e74c3c',
                        'Cancelada' => '#95a5a6'
                    ];
                @endphp
                @foreach($charts['status'] as $status => $count)
                    @php
                        $percentage = $totalStatus > 0 ? round(($count / $totalStatus) * 100, 1) : 0;
                        $color = $statusColors[$status] ?? '#3498db';
                    @endphp
                    <tr>
                        <td style="font-weight: 600;">
                            <span style="display: inline-block; width: 12px; height: 12px; background: {{ $color }}; border-radius: 50%; margin-right: 8px; vertical-align: middle;"></span>
                            {{ $status }}
                        </td>
                        <td style="font-weight: 600;">{{ number_format($count, 0, ',', '.') }}</td>
                        <td style="font-weight: 700; color: {{ $color }};">{{ $percentage }}%</td>
                        <td>
                            <div style="width: 100px; height: 6px; background: #ecf0f1; border-radius: 3px; overflow: hidden;">
                                <div style="width: {{ $percentage }}%; height: 100%; background: {{ $color }};"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                <tr style="background: linear-gradient(135deg, #2c3e50, #34495e); color: white; font-weight: 700;">
                    <td>TOTAL GERAL</td>
                    <td>{{ number_format($totalStatus, 0, ',', '.') }}</td>
                    <td>100%</td>
                    <td>
                        <div style="width: 100px; height: 8px; background: rgba(255,255,255,0.2); border-radius: 4px; overflow: hidden;">
                            <div style="width: 100%; height: 100%; background: white;"></div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Distribuição por Tipo -->
    <div class="container-grafico">
        <h2>DISTRIBUIÇÃO POR TIPO DE SOLICITAÇÃO</h2>
        @php
            $totalType = array_sum($charts['type']);
            $typeColors = [
                'Queixa' => '#e74c3c',
                'Reclamação' => '#3498db',
                'Sugestão' => '#2ecc71'
            ];
        @endphp
        @foreach($charts['type'] as $type => $count)
            @php
                $percentage = $totalType > 0 ? round(($count / $totalType) * 100, 1) : 0;
                $width = min($percentage * 2, 95);
                $color = $typeColors[$type] ?? '#3498db';
                // Função helper para clarear cores
                function adjustBrightness($hex, $percent) {
                    $hex = str_replace('#', '', $hex);
                    $r = hexdec(substr($hex, 0, 2));
                    $g = hexdec(substr($hex, 2, 2));
                    $b = hexdec(substr($hex, 4, 2));
                    
                    $r = min(255, max(0, $r + $r * $percent / 100));
                    $g = min(255, max(0, $g + $g * $percent / 100));
                    $b = min(255, max(0, $b + $b * $percent / 100));
                    
                    return sprintf('#%02x%02x%02x', $r, $g, $b);
                }
            @endphp
            <div class="titulo-grafico">
                <span>{{ $type }}</span>
                <span class="contador-grafico">{{ number_format($count, 0, ',', '.') }} solicitações</span>
            </div>
            <div class="barra-grafico" style="width: {{ $width }}%; background: linear-gradient(90deg, {{ $color }}, {{ adjustBrightness($color, 15) }});">
                {{ $percentage }}%
            </div>
        @endforeach
    </div>
    
    <!-- Top Províncias -->
    @if($charts['provinces'] && $charts['provinces']->count() > 0)
    <div class="container-tabela">
        <h2>TOP 5 PROVÍNCIAS COM MAIS SUBMISSÕES</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">Posição</th>
                    <th>Província</th>
                    <th style="width: 120px;">Submissões</th>
                    <th style="width: 100px;">Percentagem</th>
                    <th style="width: 150px;">Distribuição</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $provincesArray = $charts['provinces']->toArray();
                    $totalProvinces = array_sum(array_column($provincesArray, 'count'));
                    $rank = 1;
                @endphp
                @foreach($charts['provinces'] as $index => $province)
                    @php
                        $percentage = $totalProvinces > 0 ? round(($province['count'] / $totalProvinces) * 100, 1) : 0;
                        $rankClass = $rank === 1 ? 'primeiro' : ($rank === 2 ? 'segundo' : ($rank === 3 ? 'terceiro' : ''));
                    @endphp
                    <tr>
                        <td style="text-align: center;">
                            <span class="numero-ranking {{ $rankClass }}">{{ $rank }}</span>
                        </td>
                        <td style="font-weight: 600;">{{ $province['province'] ?: 'Não Especificada' }}</td>
                        <td style="font-weight: 700; text-align: center;">{{ number_format($province['count'], 0, ',', '.') }}</td>
                        <td style="font-weight: 700; text-align: center; color: #3498db;">{{ $percentage }}%</td>
                        <td>
                            <div style="width: 100%; height: 8px; background: #ecf0f1; border-radius: 4px; overflow: hidden;">
                                <div style="width: {{ $percentage }}%; height: 100%; background: linear-gradient(90deg, #3498db, #3dc2ff);"></div>
                            </div>
                        </td>
                    </tr>
                    @php $rank++; @endphp
                @endforeach
                <tr style="background: #f8f9fa; font-weight: 700;">
                    <td colspan="2" style="text-align: right;">Total Geral:</td>
                    <td style="text-align: center;">{{ number_format($totalProvinces, 0, ',', '.') }}</td>
                    <td style="text-align: center;">100%</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif
    
    <!-- Lista de Submissões -->
<div class="quebra-pagina">
    <h2>ÚLTIMAS SUBMISSÕES REGISTRADAS</h2>
    @if($submissions->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th>Número de Referência</th>
                    <th style="width: 100px;">Tipo</th>
                    <th style="width: 100px;">Prioridade</th>
                    <th style="width: 120px;">Status</th>
                    <th style="width: 100px;">Data de Criação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($submissions as $submission)
                    @php
                        // FUNÇÕES DE TRADUÇÃO E CAPITALIZAÇÃO
                        // Traduzir e capitalizar TIPO
                        $tipoTraduzido = match($submission->type) {
                            'grievance' => 'Queixa',
                            'complaint' => 'Reclamação',
                            'suggestion' => 'Sugestão',
                            default => ucfirst($submission->type)
                        };
                        
                        // Traduzir e capitalizar PRIORIDADE
                        $prioridadeTraduzida = match(strtolower($submission->priority)) {
                            'low' => 'Baixa',
                            'medium' => 'Média',
                            'high' => 'Alta',
                            'critical' => 'Crítica',
                            default => ucfirst($submission->priority)
                        };
                        
                        // Traduzir e capitalizar STATUS
                        $statusTraduzido = match(strtolower($submission->status)) {
                            'submitted' => 'Submetida',
                            'under_review' => 'Em Revisão',
                            'assigned' => 'Atribuída',
                            'in_progress' => 'Em Progresso',
                            'pending_approval' => 'Aprovação Pendente',
                            'resolved' => 'Resolvida',
                            'rejected' => 'Rejeitada',
                            'cancelled' => 'Cancelada',
                            default => ucfirst($submission->status)
                        };
                        
                        // Cores para prioridades
                        $priorityColors = [
                            'Baixa' => '#27ae60',
                            'Média' => '#f39c12',
                            'Alta' => '#e74c3c',
                            'Crítica' => '#c0392b'
                        ];
                        
                        // Cores para status
                        $statusColors = [
                            'Submetida' => '#3498db',
                            'Em Revisão' => '#f39c12',
                            'Atribuída' => '#9b59b6',
                            'Em Progresso' => '#1abc9c',
                            'Aprovação Pendente' => '#e67e22',
                            'Resolvida' => '#27ae60',
                            'Rejeitada' => '#e74c3c',
                            'Cancelada' => '#95a5a6'
                        ];
                        
                        $priorityColor = $priorityColors[$prioridadeTraduzida] ?? '#95a5a6';
                        $statusColor = $statusColors[$statusTraduzido] ?? '#95a5a6';
                    @endphp
                    <tr>
                        <td style="text-align: center; font-weight: 600; color: #7f8c8d;">#{{ $submission->id }}</td>
                        <td style="font-weight: 600;">{{ $submission->reference_number }}</td>
                        <td style="text-align: center;">
                            <span style="display: inline-block; padding: 3px 8px; background: #f8f9fa; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                {{ $submission->type_translated ?? $tipoTraduzido }}
                            </span>
                        </td>
                        <td style="text-align: center; color: {{ $priorityColor }}; font-weight: 700;">
                            {{ $submission->priority_translated ?? $prioridadeTraduzida }}
                        </td>
                        <td style="text-align: center;">
                            <span style="display: inline-block; padding: 3px 10px; background: {{ $statusColor }}; color: white; border-radius: 15px; font-size: 11px; font-weight: 600;">
                                {{ $submission->status_translated ?? $statusTraduzido }}
                            </span>
                        </td>
                        <td style="text-align: center; font-weight: 500;">{{ $submission->created_at->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="text-align: center; margin-top: 15px; font-size: 11px; color: #7f8c8d;">
            Mostrando {{ $submissions->count() }} registros mais recentes
        </div>
    @else
        <div style="text-align: center; padding: 30px; background: #f8f9fa; border-radius: 8px; color: #7f8c8d;">
            <div style="font-size: 16px; margin-bottom: 10px;">📭</div>
            <div style="font-weight: 600;">Nenhuma submissão encontrada no período selecionado</div>
        </div>
    @endif
</div>
    
    <!-- Desempenho dos Técnicos -->
    @if($performance->count() > 0)
        <div class="quebra-pagina">
            <h2>DESEMPENHO DA EQUIPE TÉCNICA</h2>
            <table>
                <thead>
                    <tr>
                        <th>Técnico</th>
                        <th style="width: 100px;">Total de Tarefas</th>
                        <th style="width: 100px;">Concluídas</th>
                        <th style="width: 100px;">Pendentes</th>
                        <th style="width: 120px;">Taxa de Conclusão</th>
                        <th style="width: 120px;">Tempo Médio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($performance as $tech)
                        @php
                            $completionRate = $tech['completion_rate'];
                            $rateColor = $completionRate >= 90 ? '#27ae60' : ($completionRate >= 70 ? '#f39c12' : '#e74c3c');
                            $timeColor = $tech['avg_resolution_time'] <= 24 ? '#27ae60' : ($tech['avg_resolution_time'] <= 48 ? '#f39c12' : '#e74c3c');
                        @endphp
                        <tr>
                            <td style="font-weight: 600;">{{ $tech['name'] }}</td>
                            <td style="text-align: center; font-weight: 700;">{{ $tech['total_tasks'] }}</td>
                            <td style="text-align: center; font-weight: 700; color: #27ae60;">{{ $tech['completed_tasks'] }}</td>
                            <td style="text-align: center; font-weight: 700; color: #e74c3c;">{{ $tech['total_tasks'] - $tech['completed_tasks'] }}</td>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <div style="flex: 1; height: 8px; background: #ecf0f1; border-radius: 4px; overflow: hidden; margin-right: 10px;">
                                        <div style="width: {{ $completionRate }}%; height: 100%; background: {{ $rateColor }};"></div>
                                    </div>
                                    <span style="font-weight: 700; color: {{ $rateColor }}; min-width: 40px;">{{ $completionRate }}%</span>
                                </div>
                            </td>
                            <td style="text-align: center; font-weight: 700; color: {{ $timeColor }};">
                                {{ $tech['avg_resolution_time'] }}h
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top: 20px; padding: 15px; background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 8px;">
                <div style="display: flex; justify-content: space-between; font-size: 12px;">
                    <div>
                        <span style="font-weight: 600;">Média de Conclusão:</span> 
                        {{ round($performance->avg('completion_rate'), 1) }}%
                    </div>
                    <div>
                        <span style="font-weight: 600;">Tempo Médio Geral:</span> 
                        {{ round($performance->avg('avg_resolution_time'), 1) }}h
                    </div>
                    <div>
                        <span style="font-weight: 600;">Total de Técnicos:</span> 
                        {{ $performance->count() }}
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Rodapé -->
    <div class="rodape">
        <div style="margin-bottom: 10px;">
            <div style="font-weight: 600; color: #2c3e50;">Documento gerado automaticamente pelo Sistema MDQR</div>
            <div style="font-size: 10px; color: #95a5a6;">Sistema de Monitoramento de Desempenho e Qualidade de Respostas</div>
        </div>
        
        <div class="assinatura">
            <div class="linha-assinatura"></div>
            <div class="texto-assinatura">Relatório validado e autorizado para distribuição</div>
        </div>
    </div>
</body>
</html>