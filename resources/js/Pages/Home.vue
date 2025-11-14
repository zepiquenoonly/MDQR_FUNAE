<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Página Inicial</h1>
                        <p class="text-gray-600">Bem-vindo, {{ $page.props.auth.user.name }}!</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-700">{{ $page.props.auth.user.email }}</span>
                        <button @click="logout"
                            class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition duration-200">
                            Sair
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Bem-vindo ao Sistema!</h2>
                <p class="text-gray-600 mb-4">
                    Você fez login com sucesso! Esta é a página inicial protegida.
                </p>

                <div class="mt-6">
                    <h3 class="text-lg font-medium mb-2">Informações da sua conta:</h3>
                    <ul class="list-disc list-inside text-gray-600 space-y-1">
                        <li>Nome: {{ $page.props.auth.user.name }}</li>
                        <li>E-mail: {{ $page.props.auth.user.email }}</li>
                        <li>Data de registro: {{ new Date($page.props.auth.user.created_at).toLocaleDateString('pt-BR')
                            }}</li>
                    </ul>
                </div>

                <!-- Exemplo de formulário -->
                <div class="mt-8 border-t pt-6">
                    <h3 class="text-lg font-medium mb-4">Formulário de Exemplo</h3>
                    <form @submit.prevent="submitForm" class="space-y-4 max-w-md">
                        <div>
                            <label for="exampleField" class="block text-sm font-medium text-gray-700">
                                Campo de exemplo
                            </label>
                            <input id="exampleField" v-model="form.exampleField" type="text"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Digite algo..." />
                        </div>
                        <button type="submit"
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                            Enviar
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const form = ref({
    exampleField: ''
})

const submitForm = () => {
    console.log('Formulário enviado:', form.value)
    // Aqui você pode fazer uma requisição para o backend
    alert('Formulário enviado com sucesso!')
}

const logout = () => {
    if (confirm('Tem certeza que deseja sair?')) {
        router.post('/logout')
    }
}
</script>