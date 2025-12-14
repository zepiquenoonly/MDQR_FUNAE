<template>
    <Layout role="admin">
        <div class="p-6">
            <!-- Header with Gradient -->
            <div class="relative overflow-hidden rounded-2xl p-6 mb-6 shadow-lg border border-primary-400/30">
                <div class="absolute inset-0 bg-gradient-to-r from-primary-500 to-orange-600"></div>
                <div class="absolute inset-0 backdrop-blur-sm bg-white/10"></div>
                <div class="relative z-10 flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-white drop-shadow-lg">Gestão de Projectos</h1>
                        <p class="text-orange-50 mt-1">Supervisione e controle todos os projectos em curso</p>
                    </div>
                    <Link href="/admin/projects/create" class="bg-white hover:bg-orange-50 text-primary-600 font-semibold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Novo Projecto
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
                            placeholder="Pesquisar projectos..."
                            class="w-full pl-10 pr-4 py-3 border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                        >
                    </div>
                    <select
                        v-model="departmentFilter"
                        class="border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 py-3 px-4 transition-all"
                    >
                        <option value="">Todos os Departamentos</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                            {{ dept.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Tabela Moderna -->
            <div class="bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-primary-50 to-orange-50/50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-primary-800 uppercase tracking-wider">Projecto</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-primary-800 uppercase tracking-wider">Departamento</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-primary-800 uppercase tracking-wider">Localização</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-primary-800 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-primary-800 uppercase tracking-wider">Data</th>
                                <th scope="col" class="relative px-6 py-4">
                                    <span class="sr-only">Ações</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-for="project in projects.data" :key="project.id" class="hover:bg-primary-50/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-primary-500 to-orange-600 flex items-center justify-center text-white font-bold mr-3 shadow">
                                            {{ project.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-900">{{ project.name }}</div>
                                            <div class="text-xs text-gray-500">{{ project.distrito }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1.5 text-xs font-medium rounded-full bg-primary-100 text-primary-800 shadow-sm">
                                        {{ project.department }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        {{ project.provincia || 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="project.is_active ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-red-100 text-red-800 border-red-200'" class="px-3 py-1.5 text-xs font-semibold rounded-full border inline-flex items-center gap-1.5">
                                        <span :class="project.is_active ? 'bg-emerald-500' : 'bg-red-500'" class="w-1.5 h-1.5 rounded-full"></span>
                                        {{ project.is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ project.created_at }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link :href="`/admin/projects/${project.id}/edit`" class="p-2 rounded-lg text-primary-600 hover:bg-primary-50 transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>
                                        <button @click="openDeleteModal(project.id, project.name)" class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!projects.data || projects.data.length === 0">
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="h-16 w-16 rounded-full bg-purple-100 flex items-center justify-center mb-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                            </svg>
                                        </div>
                                        <p class="text-gray-500 font-medium">Nenhum projecto encontrado</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Paginação -->
            <div class="mt-4" v-if="projects.links">
                <Pagination :links="projects.links" />
            </div>
        </div>

        <!-- Modal de Confirmação de Exclusão -->
        <Modal
            :show="showDeleteModal"
            type="warning"
            title="Confirmar Exclusão"
            :message="`Tem certeza que deseja excluir o projecto &quot;${projectToDelete?.name}&quot;? Esta ação não pode ser desfeita.`"
            @close="closeDeleteModal"
        >
            <div class="flex gap-3 justify-end">
                <button
                    @click="closeDeleteModal"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition-colors"
                >
                    Cancelar
                </button>
                <button
                    @click="confirmDelete"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors"
                >
                    Excluir
                </button>
            </div>
        </Modal>
    </Layout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/UnifiedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useNotification } from '@/Composables/useNotification';

const { success, error } = useNotification();

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

// Modal de exclusão
const showDeleteModal = ref(false);
const projectToDelete = ref(null);

function openDeleteModal(id, name) {
    projectToDelete.value = { id, name };
    showDeleteModal.value = true;
}

function closeDeleteModal() {
    showDeleteModal.value = false;
    projectToDelete.value = null;
}

function confirmDelete() {
    if (!projectToDelete.value) return;

    router.delete(`/admin/projects/${projectToDelete.value.id}`, {
        onSuccess: () => {
            success(`Projecto "${projectToDelete.value.name}" foi excluído com sucesso!`);
            closeDeleteModal();
        },
        onError: () => {
            error('Erro ao excluir o projecto. Tente novamente.');
        }
    });
}
</script>
