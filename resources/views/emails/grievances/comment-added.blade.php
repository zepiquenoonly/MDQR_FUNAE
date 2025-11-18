<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Atualização</title>
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
        .comment-box {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid #2563eb;
            margin: 20px 0;
        }
        .comment-meta {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .comment-text {
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
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
        <h1>💬 Nova Atualização</h1>
    </div>
    
    <div class="content">
        <p>Prezado(a),</p>
        
        <p>Foi adicionado um novo comentário à sua reclamação.</p>
        
        <div class="reference">
            {{ $grievance->reference_number }}
        </div>
        
        <div class="comment-box">
            <div class="comment-meta">
                <strong>{{ $update->user ? $update->user->name : 'Sistema' }}</strong>
                • {{ $update->created_at->format('d/m/Y H:i') }}
            </div>
            
            @if($update->comment)
            <div class="comment-text">
                {{ $update->comment }}
            </div>
            @else
            <div class="comment-text">
                {{ $update->description }}
            </div>
            @endif
        </div>
        
        <p style="text-align: center;">
            <a href="{{ route('grievance.track') }}?ref={{ $grievance->reference_number }}" class="button">
                Ver Todas as Atualizações
            </a>
        </p>
        
        <p>
            Continue a acompanhar o progresso da sua reclamação através do link acima.
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
