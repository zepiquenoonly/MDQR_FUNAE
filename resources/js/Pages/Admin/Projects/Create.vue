<template>
    <Layout role="admin">
        <div class="p-6 max-w-4xl mx-auto">
            <div class="flex items-center mb-6">
                <Link href="/admin/projects" class="text-gray-500 hover:text-gray-700 mr-4">
                    ← Voltar
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">Novo Projecto</h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Detalhes do Projecto -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Detalhes do Projecto</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">Nome *</label>
                            <input v-model="form.name" type="text" id="name" required
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                        </div>
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700">Departamento</label>
                            <select v-model="form.department_id" id="department_id"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione um departamento</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700">Estado</label>
                            <select v-model="form.category" id="category"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="andamento">Em Andamento</option>
                                <option value="parados">Parado</option>
                                <option value="finalizados">Finalizado</option>
                            </select>
                        </div>
                        <div>
                            <label for="provincia" class="block text-sm font-medium text-gray-700">Província</label>
                            <input v-model="form.provincia" type="text" id="provincia"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="distrito" class="block text-sm font-medium text-gray-700">Distrito</label>
                            <input v-model="form.distrito" type="text" id="distrito"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="bairro" class="block text-sm font-medium text-gray-700">Bairro</label>
                            <input v-model="form.bairro" type="text" id="bairro"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea v-model="form.description" id="description" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex justify-end gap-4">
                    <Link href="/admin/projects" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-6 rounded-lg transition-colors">
                        Cancelar
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors disabled:opacity-50">
                        {{ form.processing ? 'A guardar...' : 'Criar Projecto' }}
                    </button>
                </div>
            </form>
        </div>
    </Layout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';

const props = defineProps({
    departments: Array,
});

const form = useForm({
    name: '',
    description: '',
    department_id: '',
    provincia: '',
    distrito: '',
    bairro: '',
    category: 'andamento',
});

function submit() {
    form.post('/admin/projects');
}
</script>
