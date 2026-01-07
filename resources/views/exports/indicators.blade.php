<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard de Indicadores - {{ $department }}</title>
    <style>
        /* ESTILOS SIMPLES E SEGUROS */
        body { 
            font-family: Helvetica, Arial, sans-serif;
            font-size: 11px;
            margin: 15px;
            color: #000000;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #2c5282;
        }
        
        .title {
            color: #2c5282;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .subtitle {
            color: #4a5568;
            font-size: 12px;
            margin: 3px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        th {
            background: #2c5282;
            color: white;
            text-align: left;
            padding: 8px;
            font-size: 10px;
            font-weight: bold;
        }
        
        td {
            padding: 8px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
        }
        
        tr:nth-child(even) {
            background: #f7fafc;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 15px 0;
        }
        
        .stat-box {
            background: #edf2f7;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #cbd5e0;
            text-align: center;
        }
        
        .stat-label {
            font-size: 9px;
            color: #4a5568;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 14px;
            font-weight: bold;
            color: #2d3748;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 9px;
            color: #718096;
        }
        
        /* Utilitários */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .mb-10 { margin-bottom: 10px; }
        .mt-10 { margin-top: 10px; }
        
        /* Cores para tendências */
        .trend-up { color: #38a169; }
        .trend-down { color: #e53e3e; }
        .trend-stable { color: #d69e2e; }
        
        /* Badges simples */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success { background: #c6f6d5; color: #22543d; }
        .badge-warning { background: #feebc8; color: #744210; }
        .badge-danger { background: #fed7d7; color: #742a2a; }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <div class="header">
        <h1 class="title">Dashboard de Indicadores</h1>
        <p class="subtitle">{{ $department }}</p>
        <p class="subtitle">Gerado por: {{ $generatedBy }}</p>
        <p class="subtitle">{{ $date }} às {{ $time }} | Período: {{ $period }}</p>
    </div>
    
    <!-- Estatísticas Rápidas -->
    @if(!empty($quickStats))
    <h2 style="color: #2d3748; font-size: 14px; margin-bottom: 10px;">Estatísticas Rápidas</h2>
    <div class="stats-grid">
        @foreach($quickStats as $stat)
        <div class="stat-box">
            <div class="stat-label">{{ $stat['label'] }}</div>
            <div class="stat-value">{{ $stat['value'] }}</div>
        </div>
        @endforeach
    </div>
    @endif
    
    <!-- Indicadores -->
    @if(!empty($indicators) && count($indicators) > 0)
    <h2 style="color: #2d3748; font-size: 14px; margin: 20px 0 10px 0;">Indicadores de Performance</h2>
    <table>
        <thead>
            <tr>
                <th>Indicador</th>
                <th>Valor Actual</th>
                <th>Meta</th>
                <th>Performance</th>
                <th>Tendência</th>
            </tr>
        </thead>
        <tbody>
            @foreach($indicators as $indicator)
            @php
                $performance = $indicator['performance'] ?? 0;
                $badgeClass = 'badge-warning';
                if ($performance >= 90) $badgeClass = 'badge-success';
                elseif ($performance < 70) $badgeClass = 'badge-danger';
                
                $trend = $indicator['trend'] ?? 0;
                $trendClass = 'trend-stable';
                if ($trend > 0) $trendClass = 'trend-up';
                elseif ($trend < 0) $trendClass = 'trend-down';
            @endphp
            <tr>
                <td>
                    <div class="bold">{{ $indicator['name'] }}</div>
                    <small>{{ $indicator['description'] }}</small>
                </td>
                <td class="bold">{{ $indicator['formatted_value'] }}</td>
                <td>{{ $indicator['target_value'] }} {{ $indicator['measurement_unit'] === 'percentage' ? '%' : '' }}</td>
                <td>
                    <span class="badge {{ $badgeClass }}">{{ number_format($performance, 1) }}%</span>
                </td>
                <td class="{{ $trendClass }}">
                    @if($trend > 0)
                        ↑ {{ number_format(abs($trend), 1) }}%
                    @elseif($trend < 0)
                        ↓ {{ number_format(abs($trend), 1) }}%
                    @else
                        → 0.0%
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    
    <!-- Performance dos Técnicos -->
    @if(!empty($technicianPerformance) && count($technicianPerformance) > 0)
    <h2 style="color: #2d3748; font-size: 14px; margin: 20px 0 10px 0;">Performance dos Técnicos</h2>
    <table>
        <thead>
            <tr>
                <th>Técnico</th>
                <th class="text-right">Total Casos</th>
                <th class="text-right">Resolvidos</th>
                <th class="text-right">Taxa de Resolução</th>
                <th class="text-right">Tempo Médio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($technicianPerformance as $tech)
            @php
                $rate = $tech['resolution_rate'] ?? 0;
                $rateBadgeClass = 'badge-warning';
                if ($rate >= 80) $rateBadgeClass = 'badge-success';
                elseif ($rate < 60) $rateBadgeClass = 'badge-danger';
            @endphp
            <tr>
                <td>{{ $tech['name'] }}</td>
                <td class="text-right">{{ $tech['total_cases'] }}</td>
                <td class="text-right">{{ $tech['resolved_cases'] }}</td>
                <td class="text-right">
                    <span class="badge {{ $rateBadgeClass }}">{{ number_format($rate, 1) }}%</span>
                </td>
                <td class="text-right">{{ number_format($tech['avg_resolution_time'] ?? 0, 1) }}h</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    
    <!-- Estatísticas de Reclamações -->
    @if(!empty($grievanceStats))
    <h2 style="color: #2d3748; font-size: 14px; margin: 20px 0 10px 0;">Estatísticas de Reclamações</h2>
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-label">Total de Reclamações</div>
            <div class="stat-value">{{ $grievanceStats['total'] ?? 0 }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Resolvidas</div>
            <div class="stat-value">{{ $grievanceStats['resolved'] ?? 0 }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Pendentes</div>
            <div class="stat-value">{{ $grievanceStats['pending'] ?? 0 }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Em Progresso</div>
            <div class="stat-value">{{ $grievanceStats['in_progress'] ?? 0 }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Taxa de Resolução</div>
            <div class="stat-value">{{ number_format($grievanceStats['resolution_rate'] ?? 0, 1) }}%</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Tempo Médio</div>
            <div class="stat-value">{{ number_format($grievanceStats['avg_resolution_time'] ?? 0, 1) }}h</div>
        </div>
    </div>
    @endif
    
    <!-- Rodapé -->
    <div class="footer">
        <p>Documento gerado automaticamente pelo Sistema de Gestão de Reclamações</p>
        <p>Página 1 de {{ $totalPages }}</p>
    </div>
</body>
</html>