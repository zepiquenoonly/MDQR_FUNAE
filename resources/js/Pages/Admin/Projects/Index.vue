<template>
    <Layout role="admin">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Gestão de Projectos</h1>
                <Link href="/admin/projects/create" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    + Novo Projecto
                </Link>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <div class="flex flex-wrap gap-4">
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Pesquisar projectos..." 
                        class="flex-1 min-w-64 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    >
                    <select 
                        v-model="departmentFilter" 
                        class="border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Todos os Departamentos</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                            {{ dept.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Tabela -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Província</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Ações</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="project in projects.data" :key="project.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ project.name }}</div>
                                <div class="text-sm text-gray-500">{{ project.distrito }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                    {{ project.department }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ project.provincia || 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="project.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-xs rounded-full">
                                    {{ project.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ project.created_at }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <Link :href="`/admin/projects/${project.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                <button @click="deleteProject(project.id)" class="text-red-600 hover:text-red-900">Apagar</button>
                            </td>
                        </tr>
                        <tr v-if="!projects.data || projects.data.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                Nenhum projecto encontrado.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="mt-4" v-if="projects.links">
                <Pagination :links="projects.links" />
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    projects: Object,
    departments: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const departmentFilter = ref(props.filters?.department || '');

let debounceTimer = null;

watch([search, departmentFilter], ([searchVal, deptVal]) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/admin/projects', { 
            search: searchVal, 
            department: deptVal 
        }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

function deleteProject(id) {
    if (confirm('Tem a certeza que quer apagar este projecto?')) {
        router.delete(`/admin/projects/${id}`);
    }
}
</script>
