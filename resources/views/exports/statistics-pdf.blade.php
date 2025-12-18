<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Estatísticas Gerais</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; }
        .subtitle { font-size: 14px; color: #666; }
        .section { margin-bottom: 30px; }
        .section-title { font-size: 16px; font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .stats-grid { display: block; margin-bottom: 20px; }
        .stat-row { margin-bottom: 10px; }
        .stat-label { display: inline-block; width: 200px; font-weight: bold; }
        .stat-value { display: inline-block; }
        .footer { margin-top: 50px; text-align: center; color: #999; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Estatísticas Gerais</div>
        <div class="subtitle">
            Período: {{ $period_label ?? 'N/A' }} 
        </div>
        <div>Exportado por: {{ $user->name ?? 'N/A' }}</div>
        <div>Data da exportação: {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>

    <div class="section">
        <div class="section-title">Informações do Período</div>
        <div class="stats-grid">
            <div class="stat-row">
                <span class="stat-label">Período selecionado:</span>
                <span class="stat-value">{{ $period_label ?? 'N/A' }}</span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Data de exportação:</span>
                <span class="stat-value">{{ now()->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Exportado por:</span>
                <span class="stat-value">{{ $user->name ?? 'N/A' }} ({{ $user->email ?? 'N/A' }})</span>
            </div>
        </div>
    </div>

    <div class="footer">
        Documento gerado automaticamente pelo Sistema de Gestão de Reclamações<br>
        {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>