<template>
    <Layout role="admin">
        <div class="p-6 max-w-4xl mx-auto">
            <div class="mb-8">
                <Link href="/admin/projects" class="inline-flex items-center text-gray-600 hover:text-primary-600 font-medium mb-4 transition-colors group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar aos Projectos
                </Link>
                <div class="relative overflow-hidden rounded-2xl p-6 shadow-lg border border-primary-400/30">
                    <div class="absolute inset-0 bg-gradient-to-r from-primary-500 to-orange-600"></div>
                    <div class="relative z-10">
                        <h1 class="text-3xl font-bold text-white drop-shadow-lg">Editar Projecto</h1>
                        <p class="text-orange-50 mt-1">Atualize as informações do projecto</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Detalhes do Projecto -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">Detalhes do Projecto</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nome *</label>
                            <input v-model="form.name" type="text" id="name" required
                                class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all py-3 px-4"
                                placeholder="Nome do projecto">
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ form.errors.name }}
                            </div>
                        </div>
                        <div>
                            <label for="department_id" class="block text-sm font-semibold text-gray-700 mb-2">Departamento</label>
                            <select v-model="form.department_id" id="department_id"
                                class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all py-3 px-4">
                                <option value="">Selecione um departamento</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                            <select v-model="form.category" id="category"
                                class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all py-3 px-4">
                                <option value="andamento">Em Andamento</option>
                                <option value="parados">Parado</option>
                                <option value="finalizados">Finalizado</option>
                            </select>
                        </div>
                        <div>
                            <label for="provincia" class="block text-sm font-semibold text-gray-700 mb-2">Província</label>
                            <input v-model="form.provincia" type="text" id="provincia"
                                class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all py-3 px-4"
                                placeholder="Ex: Maputo">
                        </div>
                        <div>
                            <label for="distrito" class="block text-sm font-semibold text-gray-700 mb-2">Distrito</label>
                            <input v-model="form.distrito" type="text" id="distrito"
                                class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all py-3 px-4"
                                placeholder="Ex: KaMpfumu">
                        </div>
                        <div>
                            <label for="bairro" class="block text-sm font-semibold text-gray-700 mb-2">Bairro</label>
                            <input v-model="form.bairro" type="text" id="bairro"
                                class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all py-3 px-4"
                                placeholder="Ex: Centro">
                        </div>
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Descrição</label>
                            <textarea v-model="form.description" id="description" rows="4"
                                class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all py-3 px-4"
                                placeholder="Descreva os detalhes do projecto..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex justify-end gap-4">
                    <Link href="/admin/projects" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-8 rounded-xl transition-all duration-300 shadow-sm hover:shadow">
                        Cancelar
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="bg-gradient-to-r from-primary-500 to-orange-600 hover:from-primary-600 hover:to-orange-700 text-white font-semibold py-3 px-8 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <svg v-if="form.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ form.processing ? 'A guardar...' : 'Guardar Alterações' }}</span>
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
    project: Object,
    departments: Array,
});

const form = useForm({
    name: props.project.name || '',
    description: props.project.description || '',
    department_id: props.project.department_id || '',
    provincia: props.project.provincia || '',
    distrito: props.project.distrito || '',
    bairro: props.project.bairro || '',
    category: props.project.category || 'andamento',
});

function submit() {
    form.put(`/admin/projects/${props.project.id}`, {
        onSuccess: () => {
            success('Projecto atualizado com sucesso!');
        },
        onError: () => {
            error('Erro ao atualizar projecto. Tente novamente.');
        }
    });
}
</script>
