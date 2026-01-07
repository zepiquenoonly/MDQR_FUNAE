<!-- resources/views/exports/employees.blade.php -->
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>RELATÓRIO DE TÉCNICOS - ESTATÍSTICAS</title>
    <style>
        /* Configuração ultra compacta */
        @page {
            size: A3 landscape;
            margin: 0.5cm;
        }
        
        body {
            font-family: "DejaVu Sans Condensed", Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.1;
            color: #000;
            margin: 0;
            padding: 5px;
            width: 42cm; /* Largura A3 landscape */
            height: 29.7cm; 
        }
        
        /* Cabeçalho simples */
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #2c5282;
        }
        
        .title {
            font-size: 14pt;
            font-weight: bold;
            color: #2c5282;
            margin-bottom: 3px;
        }
        
        .subtitle {
            font-size: 8pt;
            color: #666;
        }
        
        /* Container de estatísticas */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 12px;
            padding: 8px;
            background: #f8fafc;
            border-radius: 5px;
            border: 1px solid #e2e8f0;
        }
        
        .stat-card {
            background: white;
            border: 1px solid #cbd5e0;
            border-radius: 5px;
            padding: 8px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .stat-value {
            font-size: 12pt;
            font-weight: bold;
            color: #2c5282;
            margin-bottom: 2px;
        }
        
        .stat-label {
            font-size: 7pt;
            color: #718096;
            text-transform: uppercase;
        }
        
        /* Filtros aplicados */
        .filters-info {
            margin-bottom: 10px;
            padding: 6px;
            background: #edf2f7;
            border-radius: 4px;
            font-size: 7pt;
        }
        
        .filter-item {
            display: inline-block;
            margin-right: 10px;
            padding: 2px 6px;
            background: #c6f6d5;
            border-radius: 3px;
            color: #22543d;
            font-weight: bold;
        }
        
        /* Informações de emissão */
        .emission-info {
            text-align: center;
            font-size: 7pt;
            color: #4a5568;
            margin-top: 5px;
            padding: 3px;
            background: #e6fffa;
            border-radius: 3px;
            margin-bottom: 8px;
        }
        
        /* Tabela principal - 15 colunas */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            table-layout: fixed;
        }
        
        .data-table th {
            background-color: #2c5282;
            color: white;
            padding: 6px 3px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #1a365d;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .data-table td {
            padding: 5px 3px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            word-wrap: break-word;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        /* Larguras específicas para cada coluna */
        .col-1 { width: 30px; }   /* # */
        .col-2 { width: 140px; }  /* Nome */
        .col-3 { width: 80px; }   /* Username */
        .col-4 { width: 160px; }  /* Email */
        .col-5 { width: 80px; }   /* Telefone */
        .col-6 { width: 70px; }   /* Província */
        .col-7 { width: 70px; }   /* Distrito */
        .col-8 { width: 80px; }   /* Bairro */
        .col-9 { width: 65px; }   /* Cargo */
        .col-10 { width: 55px; }  /* Status */
        .col-11 { width: 45px; text-align: center; } /* T. Atrib */
        .col-12 { width: 45px; text-align: center; } /* Pendentes */
        .col-13 { width: 45px; text-align: center; } /* Concluídas */
        .col-14 { width: 45px; text-align: center; } /* Em Progresso */
        .col-15 { width: 45px; text-align: center; } /* Canceladas */
        .col-16 { width: 55px; text-align: center; } /* % Conclusão */
        .col-17 { width: 65px; text-align: center; } /* Tempo Médio */
        
        /* Badges compactos */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 6.5pt;
            font-weight: bold;
            text-align: center;
        }
        
        .badge-technician {
            background-color: #4299e1;
            color: white;
        }
        
        .badge-manager {
            background-color: #9f7aea;
            color: white;
        }
        
        .status-badge {
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 6.5pt;
            font-weight: bold;
        }
        
        .status-active {
            background-color: #c6f6d5;
            color: #22543d;
        }
        
        .status-inactive {
            background-color: #fed7d7;
            color: #742a2a;
        }
        
        /* Indicadores numéricos */
        .number {
            font-family: "DejaVu Sans Mono", monospace;
            font-weight: bold;
        }
        
        /* Rodapé */
        .footer {
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 7pt;
            color: #718096;
        }
        
        /* Para texto longo */
        .truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Performance indicator */
        .performance-bar {
            height: 6px;
            background-color: #e2e8f0;
            border-radius: 3px;
            margin: 2px 0;
            overflow: hidden;
        }
        
        .performance-fill {
            height: 100%;
            border-radius: 3px;
        }
        
        .performance-high { background-color: #48bb78; }
        .performance-medium { background-color: #ed8936; }
        .performance-low { background-color: #f56565; }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <div class="header">
        <div class="title">RELATÓRIO DE TÉCNICOS COM ESTATÍSTICAS</div>
        <div class="subtitle">Gerado em: {{ date('d/m/Y H:i:s') }}</div>
        <div class="subtitle">Total de técnicos: {{ count($employees) }}</div>
    </div>
    
    <!-- Informações de quem gerou -->
    <div class="emission-info">
        <strong>EMITIDO POR:</strong> {{ $generated_by ?? 'Sistema Automático' }} | 
        <strong>DATA:</strong> {{ date('d/m/Y') }}
    </div>
    
    <!-- ESTATÍSTICAS GERAIS -->
    @if(isset($stats))
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['geral']['total_funcionarios'] ?? 0 }}</div>
            <div class="stat-label">TOTAL TÉCNICOS</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value">{{ $stats['geral']['ativos'] ?? 0 }}</div>
            <div class="stat-label">ATIVOS</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value">{{ $stats['geral']['inativos'] ?? 0 }}</div>
            <div class="stat-label">INATIVOS</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value">{{ $stats['geral']['taxa_ativos'] ?? 0 }}%</div>
            <div class="stat-label">TAXA ATIVOS</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value">{{ $stats['tarefas']['total_atribuidas'] ?? 0 }}</div>
            <div class="stat-label">TAREFAS ATRIB.</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value">{{ $stats['tarefas']['total_concluidas'] ?? 0 }}</div>
            <div class="stat-label">TAREFAS CONCL.</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value">{{ $stats['tarefas']['taxa_conclusao'] ?? 0 }}%</div>
            <div class="stat-label">TAXA CONCLUSÃO</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value">{{ $stats['performance']['media_tempo_resolucao'] ?? '0h' }}</div>
            <div class="stat-label">MÉDIA TEMPO</div>
        </div>
    </div>
    @endif
    
    <!-- Filtros aplicados -->
    @if(isset($filters_applied))
    <div class="filters-info">
        <strong>FILTROS APLICADOS:</strong>
        @foreach($filters_applied as $key => $value)
            @if($value && $value != 'Nenhuma' && $value != 'Todas' && $value != 'Todos')
                <span class="filter-item">{{ ucfirst($key) }}: {{ $value }}</span>
            @endif
        @endforeach
        @if(!array_filter($filters_applied))
            <span class="filter-item">Nenhum filtro aplicado</span>
        @endif
    </div>
    @endif
    
    <!-- Tabela com 15 colunas -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-1">#</th>
                <th class="col-2">NOME</th>
                <th class="col-3">USERNAME</th>
                <th class="col-4">EMAIL</th>
                <th class="col-5">TELEFONE</th>
                <th class="col-6">PROVÍNCIA</th>
                <th class="col-7">DISTRITO</th>
                <th class="col-8">BAIRRO</th>
                <th class="col-9">CARGO</th>
                <th class="col-10">STATUS</th>
                <th class="col-11">T. ATRIB</th>
                <th class="col-12">PEND</th>
                <th class="col-13">CONCL</th>
                <th class="col-14">PROG</th>
                <th class="col-15">CANC</th>
                <th class="col-16">%</th>
                <th class="col-17">T. MÉDIO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $index => $employee)
            @php
                // Calcular classe de performance
                $completionRate = $employee['completion_rate'] ?? 0;
                $performanceClass = 'performance-low';
                if ($completionRate >= 80) {
                    $performanceClass = 'performance-high';
                } elseif ($completionRate >= 60) {
                    $performanceClass = 'performance-medium';
                }
            @endphp
            <tr>
                <!-- Índice -->
                <td class="col-1">{{ $index + 1 }}</td>
                
                <!-- Nome -->
                <td class="col-2 truncate" title="{{ $employee['name'] ?? '' }}">
                    {{ $employee['name'] ?? 'N/A' }}
                </td>
                
                <!-- Username -->
                <td class="col-3">{{ $employee['username'] ?? 'N/A' }}</td>
                
                <!-- Email -->
                <td class="col-4 truncate" title="{{ $employee['email'] ?? '' }}">
                    {{ $employee['email'] ?? 'N/A' }}
                </td>
                
                <!-- Telefone -->
                <td class="col-5">{{ $employee['phone'] ?? '--' }}</td>
                
                <!-- Província -->
                <td class="col-6">{{ $employee['province'] ?? '--' }}</td>
                
                <!-- Distrito -->
                <td class="col-7">{{ $employee['district'] ?? '--' }}</td>
                
                <!-- Bairro -->
                <td class="col-8">{{ $employee['neighborhood'] ?? '--' }}</td>
                
                <!-- Cargo -->
                <td class="col-9">
                    <span class="badge badge-technician">
                        Técnico
                    </span>
                </td>
                
                <!-- Status -->
                <td class="col-10">
                    <span class="status-badge {{ $employee['status'] === 'active' ? 'status-active' : 'status-inactive' }}">
                        {{ $employee['status'] === 'active' ? 'Ativo' : 'Inativo' }}
                    </span>
                </td>
                
                <!-- Tarefas Atribuídas -->
                <td class="col-11 number">{{ $employee['tasks_assigned'] ?? 0 }}</td>
                
                <!-- Pendentes -->
                <td class="col-12 number">{{ $employee['tasks_pending'] ?? 0 }}</td>
                
                <!-- Concluídas -->
                <td class="col-13 number">{{ $employee['tasks_completed'] ?? 0 }}</td>
                
                <!-- Em Progresso -->
                <td class="col-14 number">{{ $employee['tasks_in_progress'] ?? 0 }}</td>
                
                <!-- Canceladas -->
                <td class="col-15 number">{{ $employee['tasks_cancelled'] ?? 0 }}</td>
                
                <!-- % Conclusão -->
                <td class="col-16">
                    <div class="number">{{ round($employee['completion_rate'] ?? 0, 1) }}%</div>
                    <div class="performance-bar">
                        <div class="performance-fill {{ $performanceClass }}" 
                             style="width: {{ min($completionRate, 100) }}%"></div>
                    </div>
                </td>
                
                <!-- Tempo Médio -->
                <td class="col-17 number">{{ $employee['average_resolution_time'] ?? 0 }}h</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- RESUMO FINAL -->
    @if(isset($stats))
    <div style="margin-top: 15px; padding: 8px; background: #f0fff4; border-radius: 5px; border: 1px solid #9ae6b4;">
        <div style="font-size: 8pt; font-weight: bold; color: #22543d; margin-bottom: 5px;">
            📊 RESUMO ESTATÍSTICO
        </div>
        <div style="font-size: 7pt; color: #2d3748;">
            • <strong>Média de tarefas por técnico:</strong> 
            {{ $stats['geral']['total_funcionarios'] > 0 ? round($stats['tarefas']['total_atribuidas'] / $stats['geral']['total_funcionarios'], 1) : 0 }}
            <br>
            • <strong>Taxa média de conclusão:</strong> {{ $stats['performance']['media_conclusao'] ?? '0%' }}
            <br>
            • <strong>Tempo médio de resolução:</strong> {{ $stats['performance']['media_tempo_resolucao'] ?? '0h' }}
            <br>
            • <strong>Técnicos por província:</strong> {{ $stats['distribuicao']['total_provincias'] ?? 0 }} províncias
        </div>
    </div>
    @endif
    
    <!-- Rodapé -->
    <div class="footer">
        Documento confidencial - Uso interno | 
        Emitido por: {{ $generated_by ?? 'Sistema' }} | 
        Data/Hora: {{ date('d/m/Y H:i:s') }} | 
        Total: {{ count($employees) }} técnicos | 
        Página <span class="pageNumber"></span>
    </div>
    
    <!-- Script para numeração de páginas -->
    <script type="text/php">
        if (isset($pdf)) {
            // Número da página no rodapé
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $size = 7;
            $font = $fontMetrics->get_font("DejaVu Sans Condensed");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 12;
            $pdf->page_text($x, $y, $text, $font, $size);
            
            // Quem gerou no rodapé esquerdo
            $generatorText = "Gerado por: {{ $generated_by ?? 'Sistema' }}";
            $pdf->page_text(10, $y, $generatorText, $font, $size);
            
            // Data no rodapé direito
            $dateText = date('d/m/Y H:i');
            $dateWidth = $fontMetrics->get_text_width($dateText, $font, $size);
            $pdf->page_text($pdf->get_width() - $dateWidth - 10, $y, $dateText, $font, $size);
        }
    </script>
</body>
</html>