<template>
    <Layout>
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-900">Novo Departamento</h1>

            <form @submit.prevent="submit" class="mt-6 max-w-lg">
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nome</label>
                        <input v-model="form.name" type="text" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                        <textarea v-model="form.description" id="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        <div v-if="form.errors.description" class="text-sm text-red-600 mt-1">{{ form.errors.description }}</div>
                    </div>
                    <div>
                        <label for="manager_id" class="block text-sm font-medium text-gray-700">Gestor</label>
                        <select v-model="form.manager_id" id="manager_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Nenhum</option>
                            <option v-for="manager in managers" :key="manager.id" :value="manager.id">{{ manager.name }}</option>
                        </select>
                        <div v-if="form.errors.manager_id" class="text-sm text-red-600 mt-1">{{ form.errors.manager_id }}</div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit" :disabled="form.processing" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Criar Departamento
                    </button>
                </div>
            </form>
        </div>
    </Layout>
</template>

<script setup>
import Layout from '@/Layouts/Layout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    managers: Array,
});

const form = useForm({
    name: '',
    description: '',
    manager_id: '',
});

const submit = () => {
    form.post(route('admin.departments.store'));
};
</script>
