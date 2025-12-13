<template>
    <Layout role="admin">
        <div class="p-6">
            <!-- Header with Gradient -->
            <div class="relative overflow-hidden rounded-2xl p-6 mb-6 shadow-lg border border-primary-400/30">
                <div class="absolute inset-0 bg-gradient-to-r from-primary-500 to-primary-600"></div>
                <div class="absolute inset-0 backdrop-blur-sm bg-white/10"></div>
                <div class="relative z-10 flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-white drop-shadow-lg">Gestão de Utilizadores</h1>
                        <p class="text-primary-50 mt-1">Controle e administre todas as contas do sistema</p>
                    </div>
                    <Link href="/admin/users/create" class="bg-white hover:bg-primary-50 text-primary-600 font-semibold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Novo Utilizador
                    </Link>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-xl shadow-sm p-4 mb-6 border border-gray-100">
                <div class="flex flex-wrap gap-4">
                    <div class="relative flex-1 min-w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Pesquisar utilizadores..."
                            class="w-full pl-10 pr-4 py-3 border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                        >
                    </div>
                    <select
                        v-model="roleFilter"
                        class="border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 py-3 px-4 transition-all"
                    >
                        <option value="">Todos os Roles</option>
                        <option v-for="role in roles" :key="role" :value="role">
                            {{ role }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Tabela Moderna -->
            <div class="bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-primary-50 to-primary-100/50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-primary-800 uppercase tracking-wider">Utilizador</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-primary-800 uppercase tracking-wider">Email</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-primary-800 uppercase tracking-wider">Role</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-primary-800 uppercase tracking-wider">Telefone</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-primary-800 uppercase tracking-wider">Data</th>
                                <th scope="col" class="relative px-6 py-4">
                                    <span class="sr-only">Ações</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-primary-50/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white font-bold shadow-md">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="font-semibold text-gray-900">{{ user.name }}</div>
                                            <div class="text-xs text-gray-500">@{{ user.username }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        {{ user.email }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getRoleBadgeClass(user.role)" class="px-3 py-1.5 text-xs font-semibold rounded-full shadow-sm">
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        {{ user.phone || 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ user.created_at }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link :href="`/admin/users/${user.id}/edit`" class="p-2 rounded-lg text-primary-600 hover:bg-primary-50 transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>
                                        <button @click="deleteUser(user.id)" class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!users.data || users.data.length === 0">
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="h-16 w-16 rounded-full bg-primary-100 flex items-center justify-center mb-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </div>
                                        <p class="text-gray-500 font-medium">Nenhum utilizador encontrado</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Paginação -->
            <div class="mt-4" v-if="users.links">
                <Pagination :links="users.links" />
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/UnifiedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    users: Object,
    roles: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const roleFilter = ref(props.filters?.role || '');

let debounceTimer = null;

watch([search, roleFilter], ([searchVal, roleVal]) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/admin/users', {
            search: searchVal,
            role: roleVal
        }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

function getRoleBadgeClass(role) {
    const classes = {
        'Admin': 'bg-purple-100 text-purple-800 border border-purple-200',
        'Super Admin': 'bg-red-100 text-red-800 border border-red-200',
        'Director': 'bg-blue-100 text-blue-800 border border-blue-200',
        'Gestor': 'bg-emerald-100 text-emerald-800 border border-emerald-200',
        'Técnico': 'bg-amber-100 text-amber-800 border border-amber-200',
        'Utente': 'bg-gray-100 text-gray-800 border border-gray-200',
        'PCA': 'bg-indigo-100 text-indigo-800 border border-indigo-200',
    };
    return classes[role] || 'bg-gray-100 text-gray-800 border border-gray-200';
}

function deleteUser(id) {
    if (confirm('Tem a certeza que quer apagar este utilizador?')) {
        router.delete(`/admin/users/${id}`);
    }
}
</script>
