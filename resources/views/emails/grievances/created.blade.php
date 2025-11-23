<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reclamação Recebida</title>
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
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin: 20px 0;
            text-align: center;
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
        <h1>📝 {{ $grievance->type_label }} Recebida</h1>
    </div>

    <div class="content">
        <p>Prezado(a),</p>

        <p>A sua {{ $grievance->type_label_lowercase }} foi recebida com sucesso pelo sistema de gestão de reclamações da FUNAE.</p>

        <div class="reference">
            {{ $grievance->reference_number }}
        </div>

        <div class="info-box">
            <strong>Categoria:</strong> {{ $grievance->category }}<br>
            <strong>Data de Submissão:</strong> {{ $grievance->submitted_at->format('d/m/Y H:i') }}<br>
            <strong>Estado Atual:</strong> {{ $grievance->status_label }}
        </div>

        <p>
            Guarde este número de referência para poder acompanhar o progresso da sua reclamação.
        </p>

        <p style="text-align: center;">
            <a href="{{ route('grievance.track') }}?ref={{ $grievance->reference_number }}" class="button">
                Acompanhar {{ $grievance->type_label }}
            </a>
        </p>

        <div class="info-box">
            <strong>Próximos Passos:</strong>
            <ul>
                <li>A sua {{ $grievance->type_label_lowercase }} será analisada pela nossa equipa</li>
                <li>Receberá notificações por email sobre cada atualização</li>
                <li>Pode acompanhar o progresso online a qualquer momento</li>
            </ul>
        </div>

        <p>
            Se tiver alguma dúvida, pode entrar em contacto connosco através dos canais habituais.
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
