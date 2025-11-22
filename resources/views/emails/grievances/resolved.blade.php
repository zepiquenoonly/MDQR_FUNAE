<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reclamação Resolvida</title>
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
            background-color: #059669;
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
            color: #059669;
            margin: 20px 0;
            text-align: center;
        }
        .success-box {
            background-color: #d1fae5;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid #059669;
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
            background-color: #059669;
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
        <h1>✅ {{ $grievance->type_label }} Resolvida</h1>
    </div>

    <div class="content">
        <p>Prezado(a),</p>

        <p>Temos o prazer de informar que a sua {{ $grievance->type_label_lowercase }} foi resolvida com sucesso!</p>

        <div class="reference">
            {{ $grievance->reference_number }}
        </div>

        <div class="success-box">
            <h3 style="margin-top: 0; color: #059669;">✓ {{ $grievance->type_label }} Resolvida</h3>
            @if($grievance->resolved_at)
            <p style="margin: 0;">
                <strong>Data de Resolução:</strong> {{ $grievance->resolved_at->format('d/m/Y H:i') }}
            </p>
            @endif
            @if($grievance->resolvedBy)
            <p style="margin: 10px 0 0 0;">
                <strong>Resolvida por:</strong> {{ $grievance->resolvedBy->name }}
            </p>
            @endif
        </div>

        @if($grievance->resolution_notes)
        <div class="info-box">
            <strong>Detalhes da Resolução:</strong>
            <p style="margin: 10px 0 0 0;">{{ $grievance->resolution_notes }}</p>
        </div>
        @endif

        <p style="text-align: center;">
            <a href="{{ route('grievance.track') }}?ref={{ $grievance->reference_number }}" class="button">
                Ver Todos os Detalhes
            </a>
        </p>

        <div class="info-box">
            <p><strong>Agradecemos a sua colaboração!</strong></p>
            <p>
                Se tiver alguma dúvida sobre a resolução ou se o problema persistir,
                não hesite em entrar em contacto connosco.
            </p>
        </div>

        <p>
            Obrigado por utilizar o nosso sistema de reclamações.
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
