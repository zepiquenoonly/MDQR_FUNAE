<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Teste PDF</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        h1 { color: #333; }
        p { color: #666; }
        .content { margin-top: 30px; }
    </style>
</head>
<body>
    <h1>Teste de PDF - Dashboard</h1>
    <p>Gerado por: {{ $generatedBy }}</p>
    <p>Data: {{ $date }} às {{ $time }}</p>
    <p>Departamento: {{ $department }}</p>
    
    <div class="content">
        <h2>Indicadores</h2>
        @if(!empty($indicators))
            <table border="1" cellspacing="0" cellpadding="5" width="100%">
                <thead>
                    <tr>
                        <th>Indicador</th>
                        <th>Valor</th>
                        <th>Performance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($indicators as $indicator)
                    <tr>
                        <td>{{ $indicator['name'] ?? 'N/A' }}</td>
                        <td>{{ $indicator['value'] ?? 'N/A' }}</td>
                        <td>{{ $indicator['performance'] ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Nenhum indicador disponível.</p>
        @endif
    </div>
</body>
</html>