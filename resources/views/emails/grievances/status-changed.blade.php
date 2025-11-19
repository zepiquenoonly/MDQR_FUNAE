<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualização de Status</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2563eb;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .reference {
            font-size: 20px;
            font-weight: bold;
            color: #2563eb;
            margin: 20px 0;
            text-align: center;
        }
        .status-change {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            margin: 0 10px;
        }
        .status-old {
            background-color: #f3f4f6;
            color: #6b7280;
            text-decoration: line-through;
        }
        .status-new {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .arrow {
            font-size: 24px;
            color: #2563eb;
        }
        .info-box {
            background-color: white;
            padding: 15px;
            border-left: 4px solid #2563eb;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔄 Atualização de Status</h1>
    </div>
    
    <div class="content">
        <p>Prezado(a),</p>
        
        <p>O estado da sua reclamação foi atualizado.</p>
        
        <div class="reference">
            {{ $grievance->reference_number }}
        </div>
        
        <div class="status-change">
            <span class="status-badge status-old">{{ ucfirst(str_replace('_', ' ', $oldStatus)) }}</span>
            <span class="arrow">→</span>
            <span class="status-badge status-new">{{ $grievance->status_label }}</span>
        </div>
        
        <div class="info-box">
            @if($newStatus === 'under_review')
                <p>✅ A sua reclamação está a ser analisada pela nossa equipa técnica.</p>
            @elseif($newStatus === 'assigned')
                <p>✅ A sua reclamação foi atribuída a um técnico especializado.</p>
            @elseif($newStatus === 'in_progress')
                <p>✅ O processamento da sua reclamação está em andamento.</p>
            @elseif($newStatus === 'pending_approval')
                <p>✅ A resolução da sua reclamação está pendente de aprovação.</p>
            @elseif($newStatus === 'resolved')
                <p>✅ A sua reclamação foi resolvida com sucesso!</p>
            @elseif($newStatus === 'rejected')
                <p>ℹ️ A sua reclamação foi considerada não procedente após análise.</p>
            @else
                <p>O status da sua reclamação foi atualizado.</p>
            @endif
        </div>
        
        <p style="text-align: center;">
            <a href="{{ route('grievance.track') }}?ref={{ $grievance->reference_number }}" class="button">
                Ver Detalhes Completos
            </a>
        </p>
        
        <p>
            Pode continuar a acompanhar o progresso da sua reclamação online a qualquer momento usando o número de referência acima.
        </p>
        
        <p>
            Atenciosamente,<br>
            <strong>Equipa FUNAE</strong>
        </p>
    </div>
    
    <div class="footer">
        <p>
            Esta é uma mensagem automática. Por favor não responda a este email.<br>
            © {{ date('Y') }} FUNAE - Fundo Nacional de Energia
        </p>
    </div>
</body>
</html>
