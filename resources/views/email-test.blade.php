<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Emails - Sistema GRM FUNAE</title>
    @vite(['resources/css/app.css'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
</head>
<body class="font-sans antialiased bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm fixed w-full top-0 z-50 h-20">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full">
            <div class="flex justify-between items-center h-full">
                <div class="flex-shrink-0">
                    <img src="/images/Logotipo-scaled.png" alt="Logo FUNAE" class="h-16 w-32 object-contain" />
                </div>
                <div class="flex items-center gap-4">
                    <a href="/" class="text-gray-700 hover:text-brand font-medium transition-colors duration-200">
                        Voltar ao Início
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="pt-32 pb-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <!-- Page Title -->
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-brand/10 rounded-full mb-4">
                    <svg class="w-8 h-8 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                    Teste de Emails
                </h1>
                <p class="text-gray-600">Sistema GRM FUNAE</p>
            </div>

            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-6 mb-8 shadow-sm animate-fade-in">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-lg font-semibold text-green-900 mb-2">
                                {{ session('success')['message'] }}
                            </h3>
                            <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold mb-3">
                                {{ session('success')['total'] }} emails enviados
                            </span>
                            <ul class="space-y-2 mt-3">
                                @foreach(session('success')['emails'] as $email)
                                    <li class="text-green-800 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $email }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-6 mb-8 shadow-sm animate-fade-in">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-lg font-semibold text-red-900 mb-2">
                                {{ session('error')['message'] }}
                            </h3>
                            @if(isset(session('error')['sent']) && count(session('error')['sent']) > 0)
                                <p class="text-red-800 font-medium mt-3 mb-2">Emails enviados antes do erro:</p>
                                <ul class="space-y-2">
                                    @foreach(session('error')['sent'] as $email)
                                        <li class="text-red-800 flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            {{ $email }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Info Box -->
            <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6 mb-8 shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <h4 class="text-lg font-semibold text-blue-900 mb-3">
                            Templates que serão testados
                        </h4>
                        <ul class="space-y-2 text-blue-800">
                            <li class="flex items-start">
                                <span class="text-brand mr-2">•</span>
                                <span>Reclamação/Queixa/Sugestão Criada</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-brand mr-2">•</span>
                                <span>Status Alterado (5 cenários: Em Análise, Em Andamento, Resolvida, Fechada, Rejeitada)</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-brand mr-2">•</span>
                                <span>Reclamação Atribuída</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-brand mr-2">•</span>
                                <span>Reclamação Resolvida</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-brand mr-2">•</span>
                                <span>Reclamação Rejeitada</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <form method="POST" action="{{ route('email-test.send') }}" class="space-y-6" id="emailTestForm">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-900 mb-2">
                            Endereço de Email
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="seu.email@example.com"
                            required
                            value="{{ old('email') }}"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand transition-all duration-200 text-gray-900 placeholder-gray-400"
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        id="submitBtn"
                        class="w-full bg-brand hover:bg-[#d94f1a] text-white font-bold py-4 px-6 rounded-lg transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5 flex items-center justify-center gap-3 text-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-none disabled:hover:translate-y-0"
                    >
                        <svg id="iconDefault" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <svg id="iconLoading" class="w-6 h-6 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="btnText">Enviar Todos os Emails de Teste</span>
                    </button>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-600 text-sm">
                © {{ date('Y') }} FUNAE - Fundo Nacional de Energia. Todos os direitos reservados.
            </p>
        </div>
    </footer>

    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('emailTestForm');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const iconDefault = document.getElementById('iconDefault');
            const iconLoading = document.getElementById('iconLoading');

            form.addEventListener('submit', function() {
                // Desabilitar botão
                submitBtn.disabled = true;

                // Trocar texto e ícone
                btnText.textContent = 'Enviando emails...';
                iconDefault.classList.add('hidden');
                iconLoading.classList.remove('hidden');
            });
        });
    </script>
</body>
</html>
