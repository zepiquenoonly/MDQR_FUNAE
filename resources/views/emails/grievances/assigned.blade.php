<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reclamação Atribuída</title>
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
        .info-box {
            background-color: white;
            padding: 15px;
            border-left: 4px solid #2563eb;
            margin: 20px 0;
        }
        .technician-box {
            background-color: #dbeafe;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
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
        <h1>👤 {{ $grievance->type_label }} Atribuída</h1>
    </div>

    <div class="content">
        <p>Prezado(a),</p>

        <p>A sua {{ $grievance->type_label_lowercase }} foi atribuída a um técnico especializado da nossa equipa.</p>

        <div class="reference">
            {{ $grievance->reference_number }}
        </div>

        <div class="technician-box">
            <h3 style="margin-top: 0;">Técnico Responsável</h3>
            <p style="font-size: 18px; font-weight: bold; margin: 10px 0;">
                {{ $assignedUser->name }}
            </p>
            <p style="color: #6b7280; margin: 0;">
                {{ $assignedUser->email }}
            </p>
        </div>

        <div class="info-box">
            <p>
                <strong>O que isto significa?</strong>
            </p>
            <ul>
                <li>A sua {{ $grievance->type_label_lowercase }} está a ser analisada por um técnico especializado</li>
                <li>O técnico irá investigar a situação e trabalhar na resolução</li>
                <li>Receberá atualizações regulares sobre o progresso</li>
            </ul>
        </div>

        <p style="text-align: center;">
            <a href="{{ route('grievance.track') }}?ref={{ $grievance->reference_number }}" class="button">
                Acompanhar Progresso
            </a>
        </p>

        <p>
            Continuamos comprometidos em resolver a sua {{ $grievance->type_label_lowercase }} no menor tempo possível.
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
