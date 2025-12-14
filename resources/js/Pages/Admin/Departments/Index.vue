<template>
    <Layout role="admin">
        <div class="p-6">
            <!-- Header with Gradient -->
            <div class="relative overflow-hidden rounded-2xl p-6 mb-6 shadow-lg border border-primary-400/30">
                <div class="absolute inset-0 bg-gradient-to-r from-primary-500 to-orange-600"></div>
                <div class="absolute inset-0 backdrop-blur-sm bg-white/10"></div>
                <div class="relative z-10 flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-white drop-shadow-lg">Gestão de Departamentos</h1>
                        <p class="text-orange-50 mt-1">Organize e gerencie a estrutura organizacional</p>
                    </div>
                    <Link href="/admin/departments/create" class="bg-white hover:bg-orange-50 text-primary-600 font-semibold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Novo Departamento
                    </Link>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-xl shadow-sm p-4 mb-6 border border-gray-100">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Pesquisar departamentos..."
                        class="w-full pl-10 pr-4 py-3 border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                    >
                </div>
            </div>

            <!-- Cards de Departamentos -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="department in departments.data" :key="department.id"
                    class="group bg-white rounded-xl shadow-sm hover:shadow-xl border border-gray-100 hover:border-primary-300 p-6 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex justify-between items-start mb-4">
                        <div class="h-14 w-14 rounded-xl bg-gradient-to-br from-primary-500 to-orange-600 flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="flex gap-2">
                            <Link :href="`/admin/departments/${department.id}/edit`" class="p-2 rounded-lg text-gray-400 hover:text-primary-600 hover:bg-primary-50 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </Link>
                            <button @click="openDeleteModal(department.id, department.name)" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-primary-600 transition-colors">{{ department.name }}</h3>
                    <p class="text-sm text-gray-500 mb-4 line-clamp-2 min-h-[40px]">{{ department.description || 'Sem descrição disponível' }}</p>
                    <div class="border-t border-gray-100 pt-4 space-y-2">
                        <div class="flex items-center text-sm text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="font-medium">{{ department.manager || 'Sem director' }}</span>
                        </div>
                        <div class="flex items-center text-xs text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ department.created_at }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!departments.data || departments.data.length === 0" class="bg-gradient-to-br from-gray-50 to-white rounded-2xl shadow-sm border-2 border-dashed border-gray-200 p-16 text-center">
                <div class="max-w-md mx-auto">
                    <div class="h-20 w-20 mx-auto mb-6 rounded-full bg-emerald-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Nenhum departamento encontrado</h3>
                    <p class="text-gray-500 mb-8 text-lg">Comece a organizar sua estrutura criando o primeiro departamento.</p>
                    <Link href="/admin/departments/create" class="inline-flex items-center gap-2 bg-gradient-to-r from-primary-500 to-orange-600 hover:from-primary-600 hover:to-orange-700 text-white font-semibold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Criar Primeiro Departamento
                    </Link>
                </div>
            </div>

            <!-- Paginação -->
            <div class="mt-6" v-if="departments.links">
                <Pagination :links="departments.links" />
            </div>
        </div>

        <!-- Modal de Confirmação de Exclusão -->
        <Modal
            :show="showDeleteModal"
            type="warning"
            title="Confirmar Exclusão"
            :message="`Tem certeza que deseja excluir o departamento &quot;${departmentToDelete?.name}&quot;? Esta ação não pode ser desfeita.`"
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
    departments: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');

let debounceTimer = null;

watch(search, (value) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/admin/departments', { search: value }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

// Modal de exclusão
const showDeleteModal = ref(false);
const departmentToDelete = ref(null);

function openDeleteModal(id, name) {
    departmentToDelete.value = { id, name };
    showDeleteModal.value = true;
}

function closeDeleteModal() {
    showDeleteModal.value = false;
    departmentToDelete.value = null;
}

function confirmDelete() {
    if (!departmentToDelete.value) return;

    router.delete(`/admin/departments/${departmentToDelete.value.id}`, {
        onSuccess: () => {
            success(`Departamento "${departmentToDelete.value.name}" foi excluído com sucesso!`);
            closeDeleteModal();
        },
        onError: () => {
            error('Erro ao excluir o departamento. Tente novamente.');
        }
    });
}
</script>
