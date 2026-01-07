<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relatório de Projectos</title>
    <style type="text/css">
        /* Estilos básicos e seguros */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000000;
        }
        
        .container {
            width: 100%;
            padding: 10px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #dc2626;
        }
        
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #000000;
            margin-bottom: 5px;
        }
        
        .subtitle {
            font-size: 12px;
            color: #666666;
        }
        
        /* Tabela segura */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
        }
        
        th, td {
            border: 1px solid #cccccc;
            padding: 5px 8px;
            text-align: left;
            word-wrap: break-word;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #000000;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        /* Status badges simples */
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .finalizado {
            background-color: #d4edda;
            color: #155724;
        }
        
        .andamento {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .parado {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #cccccc;
            text-align: center;
            font-size: 10px;
            color: #666666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">RELATÓRIO DE PROJECTOS</h1>
            <div class="subtitle">Exportado em: {{ $export_date }}</div>
            <div class="subtitle">Exportado por: {{ $user->name }}</div>
        </div>
        
        <!-- Informações de filtro -->
        @if($filters['search'] || $filters['category'] || $filters['bairro'] || $filters['date_from'] || $filters['date_to'])
        <div style="margin-bottom: 15px; padding: 10px; background: #f5f5f5; border-radius: 4px;">
            <strong>Filtros Aplicados:</strong><br>
            @if($filters['search'])
                Busca: "{{ $filters['search'] }}"<br>
            @endif
            @if($filters['category'])
                Categoria: {{ ucfirst($filters['category']) }}<br>
            @endif
            @if($filters['bairro'])
                Bairro: {{ $filters['bairro'] }}<br>
            @endif
            @if($filters['date_from'] || $filters['date_to'])
                Período: {{ $filters['date_from'] ?? 'Início' }} a {{ $filters['date_to'] ?? 'Hoje' }}<br>
            @endif
        </div>
        @endif
        
        <!-- Estatísticas -->
        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
            <div style="text-align: center; padding: 10px; background: #e3f2fd; border-radius: 4px; min-width: 120px;">
                <div style="font-size: 20px; font-weight: bold;">{{ $total_projects }}</div>
                <div>TOTAL</div>
            </div>
            <div style="text-align: center; padding: 10px; background: #d4edda; border-radius: 4px; min-width: 120px;">
                <div style="font-size: 20px; font-weight: bold;">{{ $stats['finalizados'] }}</div>
                <div>FINALIZADOS</div>
            </div>
            <div style="text-align: center; padding: 10px; background: #fff3cd; border-radius: 4px; min-width: 120px;">
                <div style="font-size: 20px; font-weight: bold;">{{ $stats['andamento'] }}</div>
                <div>EM ANDAMENTO</div>
            </div>
            <div style="text-align: center; padding: 10px; background: #f8d7da; border-radius: 4px; min-width: 120px;">
                <div style="font-size: 20px; font-weight: bold;">{{ $stats['parados'] }}</div>
                <div>PARADOS</div>
            </div>
        </div>
        
        <!-- Tabela -->
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th style="width: 200px;">NOME DO PROJECTO</th>
                    <th style="width: 150px;">RESPONSÁVEL</th>
                    <th style="width: 100px;">BAIRRO</th>
                    <th style="width: 100px;">ESTADO</th>
                    <th style="width: 50px;">RECL.</th>
                    <th style="width: 50px;">SUG.</th>
                    <th style="width: 50px;">QUEIXAS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $index => $project)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $project->name ?? 'N/A' }}</td>
                    <td>{{ $project->finance->responsavel ?? 'N/A' }}</td>
                    <td>{{ $project->bairro ?? 'N/A' }}</td>
                    <td>
                        @php
                            $statusClass = '';
                            $statusText = '';
                            
                            if (isset($project->category)) {
                                switch($project->category) {
                                    case 'finalizados':
                                        $statusClass = 'finalizado';
                                        $statusText = 'Finalizado';
                                        break;
                                    case 'andamento':
                                        $statusClass = 'andamento';
                                        $statusText = 'Em Andamento';
                                        break;
                                    case 'parados':
                                        $statusClass = 'parado';
                                        $statusText = 'Parado';
                                        break;
                                    default:
                                        $statusClass = '';
                                        $statusText = $project->category;
                                }
                            } else {
                                $statusText = 'N/A';
                            }
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                    </td>
                    <td style="text-align: center;">{{ $project->reclamacoes_count ?? 0 }}</td>
                    <td style="text-align: center;">{{ $project->sugestoes_count ?? 0 }}</td>
                    <td style="text-align: center;">
                        {{ ($project->reclamacoes_count ?? 0) + ($project->sugestoes_count ?? 0) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="footer">
            Página 1 de 1 | MDQR - FUNAE | {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>
</html>