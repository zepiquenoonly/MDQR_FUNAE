<template>
    <Layout role="admin">
        <div class="p-6 max-w-3xl mx-auto">
            <div class="mb-8">
                <Link href="/admin/departments" class="inline-flex items-center text-gray-600 hover:text-emerald-600 font-medium mb-4 transition-colors group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar aos Departamentos
                </Link>
                <div class="relative overflow-hidden rounded-2xl p-6 shadow-lg border border-primary-400/30">
                    <div class="absolute inset-0 bg-gradient-to-r from-primary-500 to-orange-600"></div>
                    <div class="relative z-10">
                        <h1 class="text-3xl font-bold text-white drop-shadow-lg">Novo Departamento</h1>
                        <p class="text-orange-50 mt-1">Preencha os dados para criar um novo departamento</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nome do Departamento *</label>
                            <input v-model="form.name" type="text" id="name" required
                                class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all py-3 px-4"
                                placeholder="Ex: Recursos Humanos">
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ form.errors.name }}
                            </div>
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Descrição</label>
                            <textarea v-model="form.description" id="description" rows="4"
                                class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all py-3 px-4"
                                placeholder="Descreva as responsabilidades e funções do departamento..."></textarea>
                        </div>
                        <div>
                            <label for="manager_id" class="block text-sm font-semibold text-gray-700 mb-2">Director/Gestor</label>
                            <select v-model="form.manager_id" id="manager_id"
                                class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all py-3 px-4">
                                <option value="">Selecione um gestor</option>
                                <option v-for="manager in managers" :key="manager.id" :value="manager.id">
                                    {{ manager.name }}
                                </option>
                            </select>
                            <p class="text-xs text-gray-500 mt-2">Opcional: Atribua um responsável pelo departamento</p>
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex justify-end gap-4">
                    <Link href="/admin/departments" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-8 rounded-xl transition-all duration-300 shadow-sm hover:shadow">
                        Cancelar
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="bg-gradient-to-r from-primary-500 to-orange-600 hover:from-primary-600 hover:to-orange-700 text-white font-semibold py-3 px-8 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <svg v-if="form.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ form.processing ? 'A criar...' : 'Criar Departamento' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </Layout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import Layout from '@/Layouts/UnifiedLayout.vue';
import { useNotification } from '@/Composables/useNotification';

const { success, error } = useNotification();

const props = defineProps({
    managers: Array,
});

const form = useForm({
    name: '',
    description: '',
    manager_id: '',
});

function submit() {
    form.post('/admin/departments', {
        onSuccess: () => {
            success('Departamento criado com sucesso!');
        },
        onError: () => {
            error('Erro ao criar departamento. Tente novamente.');
        }
    });
}
</script>
