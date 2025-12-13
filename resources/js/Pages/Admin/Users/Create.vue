<template>
    <Layout role="admin">
        <div class="p-6 max-w-2xl mx-auto">
            <div class="flex items-center mb-6">
                <Link href="/admin/users" class="text-gray-500 hover:text-gray-700 mr-4">
                    ← Voltar
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">Novo Utilizador</h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">Nome Completo *</label>
                            <input v-model="form.name" type="text" id="name" required
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                            <input v-model="form.email" type="email" id="email" required
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
                        </div>
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700">Username *</label>
                            <input v-model="form.username" type="text" id="username" required
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <div v-if="form.errors.username" class="text-red-500 text-sm mt-1">{{ form.errors.username }}</div>
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Telefone</label>
                            <input v-model="form.phone" type="text" id="phone"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700">Role *</label>
                            <select v-model="form.role" id="role" required
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione um role</option>
                                <option v-for="role in roles" :key="role" :value="role">
                                    {{ role }}
                                </option>
                            </select>
                            <div v-if="form.errors.role" class="text-red-500 text-sm mt-1">{{ form.errors.role }}</div>
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Senha *</label>
                            <input v-model="form.password" type="password" id="password" required
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Senha *</label>
                            <input v-model="form.password_confirmation" type="password" id="password_confirmation" required
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex justify-end gap-4">
                    <Link href="/admin/users" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-6 rounded-lg transition-colors">
                        Cancelar
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors disabled:opacity-50">
                        {{ form.processing ? 'A criar...' : 'Criar Utilizador' }}
                    </button>
                </div>
            </form>
        </div>
    </Layout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import Layout from '@/Layouts/UnifiedLayout.vue';

const props = defineProps({
    roles: Array,
});

const form = useForm({
    name: '',
    email: '',
    username: '',
    phone: '',
    role: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/admin/users');
}
</script>
