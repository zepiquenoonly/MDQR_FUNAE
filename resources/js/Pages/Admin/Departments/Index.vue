<template>
    <Layout>
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-900">Gestão de Departamentos</h1>

            <div class="mt-6">
                <div class="flex justify-between items-center mb-4">
                    <input v-model="search" type="text" placeholder="Pesquisar..." class="border-gray-300 rounded-md shadow-sm">
                    <a :href="route('admin.departments.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Novo Departamento
                    </a>
                </div>

                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gestor</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data de Criação</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Ações</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="department in departments.data" :key="department.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ department.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ department.manager }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ department.created_at }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a :href="route('admin.departments.edit', department.id)" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                    <button @click="deleteDepartment(department.id)" class="text-red-600 hover:text-red-900 ml-4">Apagar</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <Pagination :links="departments.links" />
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import Layout from '@/Layouts/Layout.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, watch } from 'vue';
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({
    departments: Object,
    filters: Object,
});

const search = ref(props.filters.search);

watch(search, (value) => {
    Inertia.get(route('admin.departments.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
});

function deleteDepartment(id) {
    if (confirm('Tem a certeza que quer apagar este departamento?')) {
        Inertia.delete(route('admin.departments.destroy', id));
    }
}
</script>
