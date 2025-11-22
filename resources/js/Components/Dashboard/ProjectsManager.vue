<template>
    <div class="min-h-screen bg-gray-50 dark:bg-dark-primary p-4 -mt-6 relative">
        <!-- Overlay do Formulário -->
        <div v-if="showRegisterForm && canEdit"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div
                class="bg-white dark:bg-dark-secondary rounded-lg shadow-xl w-full max-w-6xl max-h-[95vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-dark-text-primary">Registar Novo Projecto
                        </h2>
                        <button @click="closeRegisterForm"
                            class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            <XMarkIcon class="w-6 h-6" />
                        </button>
                    </div>
                    <ProjectRegister @project-created="handleProjectCreated" @cancel="closeRegisterForm" />
                </div>
            </div>
        </div>

        <!-- CARDS SUPERIORES - Layout responsivo -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
            <!-- Card Finalizados -->
            <div
                class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm sm:text-lg font-bold text-green-700 dark:text-green-400 truncate">Finalizados
                        </p>
                        <p
                            class="text-2xl sm:text-4xl font-bold text-gray-900 dark:text-dark-text-primary mt-1 sm:mt-2">
                            {{ stats.finished
                            }}</p>
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 truncate">Projectos
                            Finalizados</p>
                    </div>
                    <div
                        class="w-8 h-8 sm:w-12 sm:h-12 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center flex-shrink-0 ml-3">
                        <CheckCircleIcon class="w-4 h-4 sm:w-6 sm:h-6 text-green-600 dark:text-green-400" />
                    </div>
                </div>
            </div>

            <!-- Card Em Andamento -->
            <div
                class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm sm:text-lg font-bold text-yellow-700 dark:text-yellow-400 truncate">Em
                            Andamento</p>
                        <p
                            class="text-2xl sm:text-4xl font-bold text-gray-900 dark:text-dark-text-primary mt-1 sm:mt-2">
                            {{ stats.progress
                            }}</p>
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 truncate">Projectos em
                            Andamento</p>
                    </div>
                    <div
                        class="w-8 h-8 sm:w-12 sm:h-12 bg-yellow-100 dark:bg-yellow-900/20 rounded-full flex items-center justify-center flex-shrink-0 ml-3">
                        <ClockIcon class="w-4 h-4 sm:w-6 sm:h-6 text-yellow-600 dark:text-yellow-400" />
                    </div>
                </div>
            </div>

            <!-- Card Parados -->
            <div
                class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm sm:text-lg font-bold text-red-700 dark:text-red-400 truncate">Parados</p>
                        <p
                            class="text-2xl sm:text-4xl font-bold text-gray-900 dark:text-dark-text-primary mt-1 sm:mt-2">
                            {{ stats.suspended
                            }}</p>
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 truncate">Projectos Parados
                        </p>
                    </div>
                    <div
                        class="w-8 h-8 sm:w-12 sm:h-12 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center flex-shrink-0 ml-3">
                        <PauseCircleIcon class="w-4 h-4 sm:w-6 sm:h-6 text-red-600 dark:text-red-400" />
                    </div>
                </div>
            </div>

            <!-- Card Total -->
            <div
                class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm sm:text-lg font-bold text-blue-700 dark:text-blue-400 truncate">Total</p>
                        <p
                            class="text-2xl sm:text-4xl font-bold text-gray-900 dark:text-dark-text-primary mt-1 sm:mt-2">
                            {{ stats.total }}
                        </p>
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 truncate">Todos Projectos</p>
                    </div>
                    <div
                        class="w-8 h-8 sm:w-12 sm:h-12 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center flex-shrink-0 ml-3">
                        <FolderIcon class="w-4 h-4 sm:w-6 sm:h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>
        </div>

        <ProjectDetailsPopup :show="showDetailsPopup" :projectId="selectedProjectId" @close="closeProjectDetails"
            @project-updated="handleProjectUpdated" @project-deleted="handleProjectDeleted" />

        <!-- LISTAGEM DOS PROJECTOS -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-4 sm:mb-6">
                <h3 class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-dark-text-primary">Listagem de
                    Projectos</h3>

                <!-- Pesquisa + Botão - Layout responsivo -->
                <div class="flex flex-col xs:flex-row gap-3 w-full sm:w-auto">
                    <div class="relative flex-1 xs:flex-none xs:w-64">
                        <input v-model="search" type="text" placeholder="Pesquisar projectos..."
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 pl-4 pr-10 focus:ring-2 focus:ring-brand focus:outline-none focus:border-brand dark:bg-dark-accent dark:text-dark-text-primary text-sm" />
                        <MagnifyingGlassIcon
                            class="w-4 h-4 text-gray-500 dark:text-gray-400 absolute right-3 top-2.5" />
                    </div>

                    <button v-if="canEdit" @click="openRegisterForm"
                        class="bg-brand hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold flex items-center justify-center gap-2 transition-colors text-sm whitespace-nowrap">
                        <PlusIcon class="w-4 h-4" />
                        <span class="hidden xs:inline">Registar</span>
                        <span class="xs:hidden">Novo</span>
                    </button>
                </div>
            </div>

            <!-- CONTAINER DE ROLAGEM PARA A TABELA -->
            <div class="table-scroll-container">
                <!-- TABELA RESPONSIVA -->
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px]">
                        <thead>
                            <tr class="border-b-2 border-gray-200 dark:border-gray-700 text-left">
                                <th
                                    class="py-3 px-2 sm:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">
                                    #
                                </th>
                                <th
                                    class="py-3 px-2 sm:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[120px]">
                                    Responsável
                                </th>
                                <th
                                    class="py-3 px-2 sm:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[150px]">
                                    Nome do Projecto
                                </th>
                                <th
                                    class="py-3 px-2 sm:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[100px]">
                                    Bairro
                                </th>
                                <th
                                    class="py-3 px-2 sm:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">
                                    Estado
                                </th>
                                <th
                                    class="py-3 px-2 sm:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-20 text-center">
                                    Recl.
                                </th>
                                <th
                                    class="py-3 px-2 sm:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-20 text-center">
                                    Sug.
                                </th>
                                <th
                                    class="py-3 px-2 sm:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-28">
                                    Ações
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(project, index) in filteredProjects" :key="project.id"
                                class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-dark-accent transition-colors">
                                <td
                                    class="py-3 px-2 sm:px-4 text-sm text-gray-800 dark:text-dark-text-primary font-medium">
                                    {{ index + 1 }}
                                </td>

                                <td
                                    class="py-3 px-2 sm:px-4 text-sm font-semibold text-gray-800 dark:text-dark-text-primary">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <img :src="project.image_url || '/images/default-project.png'"
                                            class="w-8 h-8 sm:w-10 sm:h-10 rounded object-cover flex-shrink-0" />
                                        <span class="truncate">{{ project.finance?.responsavel || 'N/A' }}</span>
                                    </div>
                                </td>

                                <td class="py-3 px-2 sm:px-4 text-sm text-gray-800 dark:text-dark-text-primary">
                                    <span class="truncate-2-lines block">{{ project.name }}</span>
                                </td>

                                <td class="py-3 px-2 sm:px-4 text-sm text-gray-600 dark:text-gray-400">
                                    <span class="truncate block">{{ project.bairro }}</span>
                                </td>

                                <td class="py-3 px-2 sm:px-4">
                                    <span
                                        :class="['px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap', getStatusClass(project.category)]">
                                        {{ getStatusLabel(project.category) }}
                                    </span>
                                </td>

                                <td
                                    class="py-3 px-2 sm:px-4 text-center text-sm font-bold text-gray-700 dark:text-dark-text-primary">
                                    0
                                </td>

                                <td
                                    class="py-3 px-2 sm:px-4 text-center text-sm font-bold text-gray-700 dark:text-dark-text-primary">
                                    0
                                </td>

                                <td class="py-3 px-2 sm:px-4">
                                    <button @click="openProjectDetails(project.id)"
                                        class="bg-brand hover:bg-orange-600 text-white px-3 py-1.5 rounded text-xs font-semibold flex items-center gap-1 w-full justify-center whitespace-nowrap">
                                        <EyeIcon class="w-3 h-3" />
                                        Detalhes
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="text-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand mx-auto"></div>
                    <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm">A carregar projectos...</p>
                </div>

                <!-- Empty State -->
                <div v-else-if="projects.length === 0" class="text-center py-8">
                    <FolderIcon class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-3" />
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Nenhum projecto encontrado.</p>
                    <button v-if="canEdit" @click="openRegisterForm"
                        class="text-brand hover:text-orange-600 text-sm font-medium mt-2">
                        Criar primeiro projecto
                    </button>
                </div>

                <!-- No Results State -->
                <div v-else-if="filteredProjects.length === 0 && search" class="text-center py-8">
                    <MagnifyingGlassIcon class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-3" />
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Nenhum projecto encontrado para "{{ search }}"
                    </p>
                    <button @click="search = ''" class="text-brand hover:text-orange-600 text-sm font-medium mt-2">
                        Limpar pesquisa
                    </button>
                </div>
            </div>

            <!-- Informação de resultados -->
            <div v-if="filteredProjects.length > 0"
                class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    Mostrando <span class="font-semibold">{{ filteredProjects.length }}</span>
                    de <span class="font-semibold">{{ projects.length }}</span> projectos
                    <span v-if="search"> para "{{ search }}"</span>
                </p>

                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <span class="hidden sm:inline">Dica:</span>
                    <span>Deslize horizontalmente para ver mais dados</span>
                </div>
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

    if (!updatedProject || !updatedProject.id) {
        console.error('Projecto actualizado inválido:', updatedProject)
        loadProjects()
        return
    }

    const index = projects.value.findIndex(p => p.id === updatedProject.id)
    if (index !== -1) {
        projects.value[index] = { ...projects.value[index], ...updatedProject }
    } else {
        projects.value.unshift(updatedProject)
    }
}

const handleProjectDeleted = (projectId) => {
    console.log('Eliminando projecto:', projectId)
    projects.value = projects.value.filter(p => p.id !== projectId)
}

// Estatísticas dos cards
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
    if (projectData && projectData.id) {
        projects.value.unshift(projectData)
    }
    closeRegisterForm()
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
            return 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300'
        case 'andamento':
            return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300'
        case 'parados':
            return 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300'
        default:
            return 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300'
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
/* Container de rolagem para a tabela */
.table-scroll-container {
    max-height: calc(100vh - 300px);
    overflow-y: auto;
    overflow-x: auto;
}

/* Scrollbar personalizada */
.table-scroll-container::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.table-scroll-container::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.table-scroll-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.table-scroll-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Dark mode para scrollbar */
.dark .table-scroll-container::-webkit-scrollbar-track {
    background: #374151;
}

.dark .table-scroll-container::-webkit-scrollbar-thumb {
    background: #6b7280;
}

.dark .table-scroll-container::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Truncate para 2 linhas */
.truncate-2-lines {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Breakpoint personalizado para telas muito pequenas */
@media (max-width: 475px) {
    .xs\:flex-row {
        flex-direction: row;
    }

    .xs\:w-64 {
        width: 16rem;
    }

    .xs\:flex-none {
        flex: none;
    }

    .xs\:inline {
        display: inline;
    }

    .xs\:hidden {
        display: none;
    }
}

/* Ajustes para diferentes tamanhos de tela */
@media (max-width: 640px) {
    .table-scroll-container {
        max-height: calc(100vh - 250px);
    }
}

@media (min-width: 641px) and (max-width: 1024px) {
    .table-scroll-container {
        max-height: calc(100vh - 280px);
    }
}

@media (min-width: 1025px) {
    .table-scroll-container {
        max-height: 600px;
    }
}
</style>