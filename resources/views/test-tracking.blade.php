<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Teste de Tracking - Debug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        #result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
            display: none;
        }
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        pre {
            background-color: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>Teste de Tracking - Debug</h1>

    <div class="form-group">
        <label for="reference">Código de Rastreamento:</label>
        <input type="text" id="reference" placeholder="GRM-2025-97TBKWOP" value="GRM-2025-97TBKWOP">
    </div>

    <button onclick="searchGrievance()">Buscar</button>

    <div id="result"></div>

    <script>
        async function searchGrievance() {
            const referenceNumber = document.getElementById('reference').value;
            const resultDiv = document.getElementById('result');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            resultDiv.style.display = 'none';

            console.log('Buscando:', referenceNumber);
            console.log('CSRF Token:', csrfToken);
            console.log('URL:', '{{ route("grievance.track.search") }}');

            try {
                const response = await fetch('{{ route("grievance.track.search") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        reference_number: referenceNumber
                    })
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                const data = await response.json();
                console.log('Response data:', data);

                resultDiv.style.display = 'block';

                if (data.success) {
                    resultDiv.className = 'success';
                    resultDiv.innerHTML = `
                        <h3>Reclamação Encontrada!</h3>
                        <p><strong>Código:</strong> ${data.grievance.reference_number}</p>
                        <p><strong>Status:</strong> ${data.grievance.status_label}</p>
                        <p><strong>Prioridade:</strong> ${data.grievance.priority}</p>
                        <p><strong>Província:</strong> ${data.grievance.province}</p>
                        <p><strong>Atualizações:</strong> ${data.grievance.updates.length}</p>
                        <h4>Resposta Completa:</h4>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                } else {
                    resultDiv.className = 'error';
                    resultDiv.innerHTML = `
                        <h3>Erro</h3>
                        <p>${data.message}</p>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                }
            } catch (error) {
                console.error('Error:', error);
                resultDiv.style.display = 'block';
                resultDiv.className = 'error';
                resultDiv.innerHTML = `
                    <h3>Erro de Conexão</h3>
                    <p>${error.message}</p>
                `;
            }
        }
    </script>
</body>
</html>
