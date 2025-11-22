<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Emails - Sistema GRM FUNAE</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #2d3748;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .header p {
            color: #718096;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input[type="email"] {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }

        input[type="email"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .alert h3 {
            margin-bottom: 12px;
            font-size: 18px;
        }

        .email-list {
            list-style: none;
            margin-top: 12px;
        }

        .email-list li {
            padding: 8px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .email-list li:last-child {
            border-bottom: none;
        }

        .badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 12px;
        }

        .info-box {
            background: #e6fffa;
            border-left: 4px solid #38b2ac;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .info-box h4 {
            color: #234e52;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .info-box ul {
            margin-left: 20px;
            color: #2c7a7b;
        }

        .info-box li {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Teste de Emails</h1>
            <p>Sistema GRM FUNAE</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <h3>✅ {{ session('success')['message'] }}</h3>
                <span class="badge">{{ session('success')['total'] }} emails enviados</span>
                <ul class="email-list">
                    @foreach(session('success')['emails'] as $email)
                        <li>{{ $email }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                <h3>❌ {{ session('error')['message'] }}</h3>
                @if(isset(session('error')['sent']) && count(session('error')['sent']) > 0)
                    <p><strong>Emails enviados antes do erro:</strong></p>
                    <ul class="email-list">
                        @foreach(session('error')['sent'] as $email)
                            <li>{{ $email }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        <div class="info-box">
            <h4>📧 Templates que serão testados:</h4>
            <ul>
                <li>Reclamação/Queixa/Sugestão Criada</li>
                <li>Status Alterado (5 cenários: Em Análise, Em Andamento, Resolvida, Fechada, Rejeitada)</li>
                <li>Reclamação Atribuída</li>
                <li>Reclamação Resolvida</li>
                <li>Reclamação Rejeitada</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('email-test.send') }}">
            @csrf
            <div class="form-group">
                <label for="email">📩 Endereço de Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="seu.email@example.com" 
                    required
                    value="{{ old('email') }}"
                >
                @error('email')
                    <span style="color: #e53e3e; font-size: 14px; margin-top: 4px; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn">
                🚀 Enviar Todos os Emails de Teste
            </button>
        </form>
    </div>
</body>
</html>
