<template>
    <Layout>
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-900">Gestão de Projectos</h1>

            <div class="mt-6">
                <div class="flex justify-between items-center mb-4">
                    <input v-model="search" type="text" placeholder="Pesquisar..." class="border-gray-300 rounded-md shadow-sm">
                    <a href="/admin/projects/create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Novo Projecto
                    </a>
                </div>

                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Província</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data de Criação</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Ações</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="project in filteredProjects" :key="project.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ project.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ project.category }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ project.provincia }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ project.data_criacao }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a :href="`/admin/projects/${project.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                    <button @click="deleteProject(project.id)" class="text-red-600 hover:text-red-900 ml-4">Apagar</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import Layout from '@/Layouts/Layout.vue';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { Inertia } from '@inertiajs/inertia';

const projects = ref([]);
const search = ref('');

onMounted(async () => {
    try {
        const response = await axios.get(route('api.projects.index'));
        projects.value = response.data;
    } catch (error) {
        console.error('Erro ao carregar projectos:', error);
    }
});

const filteredProjects = computed(() => {
    if (!search.value) {
        return projects.value;
    }
    return projects.value.filter(project =>
        project.name.toLowerCase().includes(search.value.toLowerCase()) ||
        project.category.toLowerCase().includes(search.value.toLowerCase()) ||
        project.provincia.toLowerCase().includes(search.value.toLowerCase())
    );
});

async function deleteProject(id) {
    if (confirm('Tem a certeza que quer apagar este projecto?')) {
        try {
            await axios.delete(route('api.projects.destroy', id));
            projects.value = projects.value.filter(p => p.id !== id);
        } catch (error) {
            console.error('Erro ao apagar projecto:', error);
        }
    }
}
</script>
