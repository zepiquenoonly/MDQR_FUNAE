<template>
    <Layout role="admin">
        <div class="p-6 max-w-2xl mx-auto">
            <div class="flex items-center mb-6">
                <Link href="/admin/departments" class="text-gray-500 hover:text-gray-700 mr-4">
                    ← Voltar
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">Novo Departamento</h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nome do Departamento *</label>
                            <input v-model="form.name" type="text" id="name" required
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea v-model="form.description" id="description" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                        <div>
                            <label for="manager_id" class="block text-sm font-medium text-gray-700">Director/Gestor</label>
                            <select v-model="form.manager_id" id="manager_id"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione um gestor</option>
                                <option v-for="manager in managers" :key="manager.id" :value="manager.id">
                                    {{ manager.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex justify-end gap-4">
                    <Link href="/admin/departments" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-6 rounded-lg transition-colors">
                        Cancelar
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors disabled:opacity-50">
                        {{ form.processing ? 'A criar...' : 'Criar Departamento' }}
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
    managers: Array,
});

const form = useForm({
    name: '',
    description: '',
    manager_id: '',
});

function submit() {
    form.post('/admin/departments');
}
</script>
