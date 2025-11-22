<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Reclamações Recentes</h3>
            <button @click="showAllComplaints = true"
                class="px-4 py-2 bg-brand text-white rounded text-sm font-medium hover:bg-orange-600 transition-all duration-200 shadow-md hover:shadow-lg">
                Ver Todos
            </button>
        </div>

        <!-- Filtros e lista normal (quando não está na visualização completa) -->
        <div v-if="!showAllComplaints">
            <!-- Filters -->
            <div class="flex flex-wrap gap-3 mb-6">
                <h3 class="font-semibold text-gray-800 mt-5">Filtrar Por:</h3>
                <div class="flex flex-col">
                    <label for="priority" class="mb-1 text-sm font-medium text-gray-700">
                        Prioridade
                    </label>
                    <select id="priority" v-model="localFilters.priority"
                        class="px-3 py-2 border border-gray-300 rounded text-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200 appearance-none bg-white cursor-pointer">
                        <option value="" disabled hidden class="text-gray-400">Prioridade: Todos</option>
                        <option value="high" class="text-gray-900">Alta</option>
                        <option value="medium" class="text-gray-900">Média</option>
                        <option value="low" class="text-gray-900">Baixa</option>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label for="priority" class="mb-1 text-sm font-medium text-gray-700">
                        Estado
                    </label>
                    <select v-model="localFilters.status"
                        class="px-3 py-2 border border-gray-300 rounded text-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200">
                        <option value="">Todos</option>
                        <option value="open">Aberto</option>
                        <option value="in_progress">Em Progresso</option>
                        <option value="pending_completion">Solicitado Conclusão</option>
                        <option value="closed">Concluído</option>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label for="priority" class="mb-1 text-sm font-medium text-gray-700">
                        Categoria
                    </label>
                    <select v-model="localFilters.category"
                        class="px-3 py-2 border border-gray-300 rounded text-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200">
                        <option value="">Todas</option>
                        <option value="infra">Infraestrutura</option>
                        <option value="service">Serviço</option>
                        <option value="attendance">Atendimento</option>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label for="priority" class="mb-1 text-sm font-medium text-gray-700">
                        Tipo
                    </label>
                    <select v-model="localFilters.type"
                        class="px-3 py-2 border border-gray-300 rounded text-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200">
                        <option value="">Todos</option>
                        <option value="complaint">Reclamação</option>
                        <option value="suggestion">Sugestão</option>
                    </select>
                </div>
            </div>

            <!-- Complaints List -->
            <div class="space-y-3">
                <ComplaintRow v-for="complaint in complaints" :key="complaint.id" :complaint="complaint"
                    :selected="selectedComplaint?.id === complaint.id" @select="handleSelectComplaint"
                    @show-details="handleShowDetails" />
            </div>

            <!-- Empty State -->
            <div v-if="complaints.length === 0" class="text-center py-8">
                <DocumentMagnifyingGlassIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-500">Nenhuma reclamação encontrada</p>
            </div>

            <!-- Footer -->
            <div class="flex justify-between items-center mt-6">
                <span class="text-gray-500 text-sm">
                    Mostrando {{ complaints.length }} reclamações
                </span>
                <div class="flex gap-3">
                    <button @click="handleBulkAssign"
                        class="px-4 py-2 border border-gray-300 rounded text-gray-700 text-sm font-medium hover:bg-gray-50 transition-all duration-200">
                        Atribuição Automática
                    </button>
                    <button @click="handleExport"
                        class="px-4 py-2 bg-brand text-white rounded text-sm font-medium hover:bg-orange-600 transition-all duration-200 shadow-md hover:shadow-lg">
                        Exportar Dados
                    </button>
                </div>
            </div>
        </div>

        <!-- Visualização Completa com Tabs -->
        <div v-else class="space-y-6">
            <!-- Header da visualização completa -->
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-800">Todas as Reclamações e Sugestões</h3>
                <button @click="showAllComplaints = false"
                    class="px-4 py-2 border border-gray-300 rounded text-gray-700 text-sm font-medium hover:bg-gray-50 transition-all duration-200">
                    Voltar
                </button>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button @click="activeTab = 'complaints'" :class="[
                        activeTab === 'complaints'
                            ? 'border-brand text-brand'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
                    ]">
                        Reclamações
                        <span class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2 rounded-full text-xs">
                            {{ complaintsCount }}
                        </span>
                    </button>
                    <button @click="activeTab = 'suggestions'" :class="[
                        activeTab === 'suggestions'
                            ? 'border-brand text-brand'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
                    ]">
                        Sugestões
                        <span class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2 rounded-full text-xs">
                            {{ suggestionsCount }}
                        </span>
                    </button>
                </nav>
            </div>

            <!-- Tabela de Dados -->
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Título
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tipo
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Prioridade
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Categoria
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="item in currentTabData" :key="item.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #{{ item.id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 max-w-xs truncate">
                                {{ item.title }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="[
                                    item.type === 'complaint'
                                        ? 'bg-red-100 text-red-800'
                                        : 'bg-blue-100 text-blue-800',
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'
                                ]">
                                    {{ item.type === 'complaint' ? 'Reclamação' : 'Sugestão' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="[
                                    item.priority === 'high'
                                        ? 'bg-red-100 text-red-800'
                                        : item.priority === 'medium'
                                            ? 'bg-yellow-100 text-yellow-800'
                                            : 'bg-green-100 text-green-800',
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'
                                ]">
                                    {{ getPriorityLabel(item.priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="[
                                    item.status === 'open'
                                        ? 'bg-blue-100 text-blue-800'
                                        : item.status === 'in_progress'
                                            ? 'bg-yellow-100 text-yellow-800'
                                            : item.status === 'closed'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-gray-100 text-gray-800',
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'
                                ]">
                                    {{ getStatusLabel(item.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ getCategoryLabel(item.category) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatDate(item.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="handleShowDetails(item)" class="text-brand hover:text-orange-700 mr-3">
                                    Detalhes
                                </button>
                                <button @click="handleSelectComplaint(item)" class="text-gray-600 hover:text-gray-900">
                                    Selecionar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty State para tabela -->
                <div v-if="currentTabData.length === 0" class="text-center py-8">
                    <DocumentMagnifyingGlassIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                    <p class="text-gray-500">Nenhum dado encontrado</p>
                </div>
            </div>

            <!-- Paginação (opcional) -->
            <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                <div class="flex flex-1 justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-700">
                            Mostrando
                            <span class="font-medium">{{ currentTabData.length }}</span>
                            resultados
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="handleExport"
                            class="px-4 py-2 bg-brand text-white rounded text-sm font-medium hover:bg-orange-600 transition-all duration-200">
                            Exportar {{ activeTab === 'complaints' ? 'Reclamações' : 'Sugestões' }}
                        </button>
                        <button @click="handleBulkAssign"
                            class="px-4 py-2 border border-gray-300 rounded text-gray-700 text-sm font-medium hover:bg-gray-50 transition-all duration-200">
                            Atribuição Automática
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { DocumentMagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import ComplaintRow from './ComplaintRow.vue'

const props = defineProps({
    complaints: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    allComplaints: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['update:filters', 'select-complaint', 'show-details'])

const localFilters = ref({ ...props.filters })
const selectedComplaint = ref(null)
const showAllComplaints = ref(false)
const activeTab = ref('complaints')

// Computed properties para os dados das tabs
const complaintsData = computed(() =>
    props.allComplaints.filter(item => item.type === 'complaint')
)

const suggestionsData = computed(() =>
    props.allComplaints.filter(item => item.type === 'suggestion')
)

const currentTabData = computed(() => {
    return activeTab.value === 'complaints' ? complaintsData.value : suggestionsData.value
})

const complaintsCount = computed(() => complaintsData.value.length)
const suggestionsCount = computed(() => suggestionsData.value.length)

// Watcher para filtros
watch(localFilters, (newFilters) => {
    emit('update:filters', newFilters)
}, { deep: true })

// Métodos auxiliares
const getPriorityLabel = (priority) => {
    const labels = {
        high: 'Alta',
        medium: 'Média',
        low: 'Baixa'
    }
    return labels[priority] || priority
}

const getStatusLabel = (status) => {
    const labels = {
        open: 'Aberto',
        in_progress: 'Em Progresso',
        pending_completion: 'Solicitado Conclusão',
        closed: 'Concluído'
    }
    return labels[status] || status
}

const getCategoryLabel = (category) => {
    const labels = {
        infra: 'Infraestrutura',
        service: 'Serviço',
        attendance: 'Atendimento'
    }
    return labels[category] || category
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('pt-BR')
}

// Métodos existentes
const handleSelectComplaint = (complaint) => {
    selectedComplaint.value = complaint
    emit('select-complaint', complaint)
}

const handleShowDetails = (complaint) => {
    emit('show-details', complaint)
}

const handleBulkAssign = async () => {
    try {
        await router.post(route('complaints.bulk-assign'), {}, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                // Feedback será mostrado via notificação do backend
            }
        })
    } catch (error) {
        console.error('Error in bulk assign:', error)
    }
}

const handleExport = () => {
    const queryParams = new URLSearchParams({
        ...localFilters.value,
        type: showAllComplaints.value ? activeTab.value === 'complaints' ? 'complaint' : 'suggestion' : localFilters.value.type
    }).toString()

    window.open(route('complaints.export') + '?' + queryParams, '_blank')
}
</script>