<template>
    <Layout role="admin">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Gestão de Utilizadores</h1>
                <Link href="/admin/users/create" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    + Novo Utilizador
                </Link>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <div class="flex flex-wrap gap-4">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Pesquisar utilizadores..."
                        class="flex-1 min-w-64 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    >
                    <select
                        v-model="roleFilter"
                        class="border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todos os Roles</option>
                        <option v-for="role in roles" :key="role" :value="role">
                            {{ role }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Tabela -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilizador</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Ações</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="font-medium text-gray-900">{{ user.name }}</div>
                                        <div class="text-sm text-gray-500">@{{ user.username }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ user.email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getRoleBadgeClass(user.role)" class="px-2 py-1 text-xs rounded-full">
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ user.phone || 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ user.created_at }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <Link :href="`/admin/users/${user.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                <button @click="deleteUser(user.id)" class="text-red-600 hover:text-red-900">Apagar</button>
                            </td>
                        </tr>
                        <tr v-if="!users.data || users.data.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                Nenhum utilizador encontrado.
                            </td>
                        </tr>
                    </tbody>
                </table>
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
        'Admin': 'bg-purple-100 text-purple-800',
        'Super Admin': 'bg-red-100 text-red-800',
        'Director': 'bg-blue-100 text-blue-800',
        'Gestor': 'bg-green-100 text-green-800',
        'Técnico': 'bg-yellow-100 text-yellow-800',
        'Utente': 'bg-gray-100 text-gray-800',
        'PCA': 'bg-indigo-100 text-indigo-800',
    };
    return classes[role] || 'bg-gray-100 text-gray-800';
}

function deleteUser(id) {
    if (confirm('Tem a certeza que quer apagar este utilizador?')) {
        router.delete(`/admin/users/${id}`);
    }
}
</script>
