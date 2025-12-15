<template>
    <Layout role="admin">
        <div class="p-6 max-w-4xl mx-auto">
            <div class="mb-8">
                <Link href="/admin/users" class="inline-flex items-center text-gray-600 hover:text-primary-600 font-medium mb-4 transition-colors group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar aos Usuários
                </Link>
                <div class="relative overflow-hidden rounded-2xl p-6 shadow-lg border border-primary-400/30">
                    <div class="absolute inset-0 bg-gradient-to-r from-primary-500 to-primary-600"></div>
                    <div class="relative z-10">
                        <h1 class="text-3xl font-bold text-white drop-shadow-lg">Novo Usuário</h1>
                        <p class="text-primary-50 mt-1">Preencha os dados para criar uma nova conta</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nome Completo *</label>
                            <input v-model="form.name" type="text" id="name" required
                                class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all py-3 px-4"
                                placeholder="Ex: João Silva">
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ form.errors.name }}
                            </div>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                            <input v-model="form.email" type="email" id="email" required
                                class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all py-3 px-4"
                                placeholder="email@exemplo.com">
                            <div v-if="form.errors.email" class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ form.errors.email }}
                            </div>
                        </div>
                        <div>
                            <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">Username *</label>
                            <input v-model="form.username" type="text" id="username" required
                                class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all py-3 px-4"
                                placeholder="joaosilva">
                            <div v-if="form.errors.username" class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ form.errors.username }}
                            </div>
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Telefone</label>
                            <input v-model="form.phone" type="text" id="phone"
                                class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all py-3 px-4"
                                placeholder="+258 84 123 4567">
                        </div>
                        <div>
                            <label for="role" class="block text-sm font-semibold text-gray-700 mb-2">Role *</label>
                            <select v-model="form.role" id="role" required
                                class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all py-3 px-4">
                                <option value="">Selecione um role</option>
                                <option v-for="role in roles" :key="role" :value="role">
                                    {{ role }}
                                </option>
                            </select>
                            <div v-if="form.errors.role" class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ form.errors.role }}
                            </div>
                        </div>

                        <!-- Campo de Departamento - Mostra apenas para roles específicos -->
                        <div v-if="shouldShowDepartmentField">
                            <label for="department_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Departamento *
                                <span class="text-xs font-normal text-gray-500">(Obrigatório para este role)</span>
                            </label>
                            <select v-model="form.department_id" id="department_id" :required="shouldShowDepartmentField"
                                class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all py-3 px-4">
                                <option value="">Selecione um departamento</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </option>
                            </select>
                            <p class="text-xs text-gray-500 mt-2">Atribua o usuário a um departamento específico</p>
                            <div v-if="form.errors.department_id" class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ form.errors.department_id }}
                            </div>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Senha *</label>
                            <input v-model="form.password" type="password" id="password" required
                                class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all py-3 px-4"
                                placeholder="Mínimo 8 caracteres">
                            <div v-if="form.errors.password" class="text-red-500 text-sm mt-2 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ form.errors.password }}
                            </div>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Confirmar Senha *</label>
                            <input v-model="form.password_confirmation" type="password" id="password_confirmation" required
                                class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all py-3 px-4"
                                placeholder="Repita a senha">
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex justify-end gap-4">
                    <Link href="/admin/users" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-8 rounded-xl transition-all duration-300 shadow-sm hover:shadow">
                        Cancelar
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold py-3 px-8 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <svg v-if="form.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ form.processing ? 'A criar...' : 'Criar Usuário' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </Layout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import Layout from '@/Layouts/UnifiedLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    roles: Array,
    departments: Array,
});

const form = useForm({
    name: '',
    email: '',
    username: '',
    phone: '',
    role: '',
    department_id: '',
    password: '',
    password_confirmation: '',
});

// Roles que requerem departamento
const rolesWithDepartment = ['Técnico', 'Gestor'];

const shouldShowDepartmentField = computed(() => {
    return rolesWithDepartment.includes(form.role);
});

function submit() {
    form.post('/admin/users');
}
</script>
