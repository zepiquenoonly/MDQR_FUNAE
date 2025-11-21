<template>
    <div class="min-h-screen bg-gray-50 p-4 -mt-6 relative">
        <!-- Overlay do Formulário -->
        <div v-if="showRegisterForm && canEdit"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl max-h-[95vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold">Registar Novo Projecto</h2>
                        <button @click="closeRegisterForm" class="text-gray-500 hover:text-gray-700">
                            <XMarkIcon class="w-6 h-6" />
                        </button>
                    </div>
                    <ProjectRegister @project-created="handleProjectCreated" @cancel="closeRegisterForm" />
                </div>
            </div>
        </div>

        <!-- CARDS SUPERIORES -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
            <!-- Card Finalizados -->
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-lg font-bold text-green-700">Finalizados</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ stats.finished }}</p>
                        <p class="text-gray-600 mt-1">Projectos Finalizados</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <CheckCircleIcon class="w-6 h-6 text-green-600" />
                    </div>
                </div>
            </div>

            <!-- Card Em Andamento -->
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-lg font-bold text-yellow-700">Em Andamento</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ stats.progress }}</p>
                        <p class="text-gray-600 mt-1">Projectos em Andamento</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <ClockIcon class="w-6 h-6 text-yellow-600" />
                    </div>
                </div>
            </div>

            <!-- Card Parados -->
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-lg font-bold text-red-700">Parados</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ stats.suspended }}</p>
                        <p class="text-gray-600 mt-1">Projectos Parados</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <PauseCircleIcon class="w-6 h-6 text-red-600" />
                    </div>
                </div>
            </div>

            <!-- Card Total -->
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-lg font-bold text-blue-700">Total</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ stats.total }}</p>
                        <p class="text-gray-600 mt-1">Todos Projectos</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <FolderIcon class="w-6 h-6 text-blue-600" />
                    </div>
                </div>
            </div>
        </div>

        <ProjectDetailsPopup :show="showDetailsPopup" :projectId="selectedProjectId" @close="closeProjectDetails"
            @project-updated="handleProjectUpdated" @project-deleted="handleProjectDeleted" />

        <!-- LISTAGEM DOS PROJECTOS -->
        <div class="bg-white rounded shadow-sm p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-6">Listagem de Projectos</h3>

            <!-- Pesquisa + Botão -->
            <div class="flex justify-between items-center mb-4">
                <div class="w-72 relative">
                    <input v-model="search" type="text" placeholder="Pesquisar"
                        class="w-full border border-gray-300 rounded py-2 pl-4 pr-10 focus:ring-0 focus:ring-brand focus:outline-none focus:border-brand" />
                    <MagnifyingGlassIcon class="w-5 h-5 text-gray-500 absolute right-3 top-2.5" />
                </div>

                <button v-if="canEdit" @click="openRegisterForm"
                    class="bg-brand hover:bg-orange-600 text-white px-6 py-2 rounded font-semibold flex items-center gap-2 transition-colors">
                    <PlusIcon class="w-5 h-5" />
                    Registar
                </button>
            </div>

            <!-- TABELA -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-200 text-left">
                            <th class="py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                            <th class="py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Responsável</th>
                            <th class="py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nome do
                                Projecto</th>
                            <th class="py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Bairro
                            </th>
                            <th class="py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado
                            </th>
                            <th class="py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Reclamações</th>
                            <th class="py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Sugestões
                            </th>
                            <th class="py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Ações
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="(project, index) in filteredProjects" :key="project.id"
                            class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="py-4 px-4 text-sm text-gray-800">{{ index + 1 }}</td>

                            <td class="py-4 px-4 text-sm font-semibold text-gray-800 flex items-center gap-3">
                                <img :src="project.image_url || '/images/default-project.png'"
                                    class="w-12 h-12 rounded object-cover" />
                                {{ project.finance?.responsavel || 'N/A' }}
                            </td>

                            <td class="py-4 px-4 text-sm text-gray-800">{{ project.name }}</td>
                            <td class="py-4 px-4 text-sm text-gray-600">{{ project.bairro }}</td>

                            <td class="py-4 px-4">
                                <span
                                    :class="['px-3 py-1 text-xs font-semibold rounded-full', getStatusClass(project.category)]">
                                    {{ getStatusLabel(project.category) }}
                                </span>
                            </td>

                            <td class="py-4 px-4 text-center text-sm font-bold text-gray-700">0</td>
                            <td class="py-4 px-4 text-center text-sm font-bold text-gray-700">0</td>

                            <td class="py-4 px-4">
                                <button @click="openProjectDetails(project.id)"
                                    class="bg-brand hover:bg-orange-600 text-white px-4 py-2 rounded text-sm font-semibold flex items-center gap-2">
                                    <EyeIcon class="w-4 h-4" />
                                    Detalhes
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="text-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand mx-auto"></div>
                <p class="text-gray-600 mt-2">A carregar projectos...</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="projects.length === 0" class="text-center py-8">
                <p class="text-gray-600">Nenhum projecto encontrado.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import {
    MagnifyingGlassIcon,
    EyeIcon,
    PlusIcon,
    XMarkIcon,
    CheckCircleIcon,
    ClockIcon,
    PauseCircleIcon,
    FolderIcon
} from '@heroicons/vue/24/outline'
import ProjectRegister from './ProjectRegister.vue'
import ProjectDetailsPopup from './ProjectDetailsPopup.vue'

const props = defineProps({
    canEdit: {
        type: Boolean,
        default: false
    }
})

const showRegisterForm = ref(false)
const showDetailsPopup = ref(false)
const selectedProjectId = ref(null)
const loading = ref(false)
const projects = ref([])

const openProjectDetails = (projectId) => {
    selectedProjectId.value = projectId
    showDetailsPopup.value = true
}

const closeProjectDetails = () => {
    showDetailsPopup.value = false
    selectedProjectId.value = null
}

const handleProjectUpdated = (updatedProject) => {
    console.log('Projecto actualizado recebido:', updatedProject)

    // Verificar se o updatedProject é válido
    if (!updatedProject || !updatedProject.id) {
        console.error('Projecto actualizado inválido:', updatedProject)
        // Recarregar a lista para garantir dados consistentes
        loadProjects()
        return
    }

    // Atualizar o projeto na lista
    const index = projects.value.findIndex(p => p.id === updatedProject.id)
    if (index !== -1) {
        // Atualizar o projeto existente mantendo referências
        projects.value[index] = { ...projects.value[index], ...updatedProject }
    } else {
        // Se não encontrou, adicionar no início
        projects.value.unshift(updatedProject)
    }
}

const handleProjectDeleted = (projectId) => {
    console.log('Eliminando projecto:', projectId)
    // Remover o projeto da lista
    projects.value = projects.value.filter(p => p.id !== projectId)
}

// Estatísticas dos cards - CORRIGIDO
const stats = computed(() => {
    const finished = projects.value.filter(p => p.category === 'finalizados').length
    const progress = projects.value.filter(p => p.category === 'andamento').length
    const suspended = projects.value.filter(p => p.category === 'parados').length
    const total = projects.value.length

    return {
        finished,
        progress,
        suspended,
        total
    }
})

// Pesquisa
const search = ref('')

// Carregar projectos da API
const loadProjects = async () => {
    loading.value = true
    try {
        const response = await axios.get('/api/projects')
        projects.value = response.data
        console.log('Projectos carregados:', projects.value)
    } catch (error) {
        console.error('Erro ao carregar projectos:', error)
        alert('Erro ao carregar projectos')
    } finally {
        loading.value = false
    }
}

// Funções para controlar o formulário
const openRegisterForm = () => {
    showRegisterForm.value = true
}

const closeRegisterForm = () => {
    showRegisterForm.value = false
}

const handleProjectCreated = (projectData) => {
    console.log('Novo projecto criado:', projectData)
    // Adicionar o novo projecto ao início da lista
    if (projectData && projectData.id) {
        projects.value.unshift(projectData)
    }
    closeRegisterForm()
    // Recarregar para garantir dados consistentes
    loadProjects()
}

// Filtro da pesquisa
const filteredProjects = computed(() => {
    if (!search.value) return projects.value

    return projects.value.filter(project =>
        project.name.toLowerCase().includes(search.value.toLowerCase()) ||
        (project.finance?.responsavel?.toLowerCase().includes(search.value.toLowerCase())) ||
        project.bairro.toLowerCase().includes(search.value.toLowerCase())
    )
})

// Badge de estado
const getStatusClass = (category) => {
    switch (category) {
        case 'finalizados':
            return 'bg-green-100 text-green-700'
        case 'andamento':
            return 'bg-yellow-100 text-yellow-700'
        case 'parados':
            return 'bg-red-100 text-red-700'
        default:
            return 'bg-gray-200 text-gray-600'
    }
}

const getStatusLabel = (category) => {
    const labels = {
        'finalizados': 'Finalizado',
        'andamento': 'Em Andamento',
        'parados': 'Parado'
    }
    return labels[category] || category
}

// Carregar projectos quando o componente for montado
onMounted(() => {
    loadProjects()
})
</script>

<style scoped>
/* Estilos adicionais se necessário */
</style>