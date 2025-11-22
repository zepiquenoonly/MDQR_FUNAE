<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reclamação Não Procedente</title>
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
            background-color: #dc2626;
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
            color: #dc2626;
            margin: 20px 0;
            text-align: center;
        }
        .warning-box {
            background-color: #fee2e2;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid #dc2626;
            margin: 20px 0;
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
        <h1>ℹ️ {{ $grievance->type_label }} Não Procedente</h1>
    </div>

    <div class="content">
        <p>Prezado(a),</p>

        <p>Após análise detalhada, informamos que a sua {{ $grievance->type_label_lowercase }} foi considerada não procedente.</p>

        <div class="reference">
            {{ $grievance->reference_number }}
        </div>

        <div class="warning-box">
            <h3 style="margin-top: 0; color: #dc2626;">Status: Rejeitada</h3>
            <p style="margin: 0;">
                A sua {{ $grievance->type_label_lowercase }} foi analisada pela nossa equipa técnica e considerada não procedente.
            </p>
        </div>

        @if($reason)
        <div class="info-box">
            <strong>Justificativa:</strong>
            <p style="margin: 10px 0 0 0;">{{ $reason }}</p>
        </div>
        @endif

        <p style="text-align: center;">
            <a href="{{ route('grievance.track') }}?ref={{ $grievance->reference_number }}" class="button">
                Ver Detalhes Completos
            </a>
        </p>

        <div class="info-box">
            <p><strong>O que fazer agora?</strong></p>
            <ul>
                <li>Pode rever os detalhes da análise através do link acima</li>
                <li>Se discordar da decisão, pode submeter uma nova {{ $grievance->type_label_lowercase }} com informações adicionais</li>
                <li>Para esclarecimentos, pode entrar em contacto através dos nossos canais habituais</li>
            </ul>
        </div>

        <p>
            Agradecemos a sua compreensão e continuamos ao seu dispor.
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
