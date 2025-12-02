<template>
    <div class="space-y-6">
        <!-- Cabeçalho -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Minhas Reclamações</h1>
            <button @click="showComplaintForm = true"
                class="flex items-center px-4 py-2 font-semibold text-white transition-colors rounded-lg bg-brand hover:bg-orange-600">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nova Reclamação
            </button>
        </div>

        <!-- Modals -->
        <ComplaintForm :visible="showComplaintForm" @close="closeComplaintForm" @submit="handleNewComplaint" />
        <GrievanceDetails v-if="selectedGrievance" :grievance="selectedGrievance" @close="selectedGrievance = null" />

        <!-- Notificações -->
        <div v-if="notifications && notifications.length > 0" class="p-4 border-l-4 border-blue-500 rounded-lg bg-blue-50">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1 ml-3">
                    <p class="text-sm font-medium text-blue-800">
                        Você tem {{ notifications.length }} notificação(ões) não lida(s)
                    </p>
                    <div class="mt-2 space-y-1">
                        <div v-for="notification in notifications.slice(0, 3)" :key="notification.id"
                            class="text-sm text-blue-700">
                            • {{ notification.subject }}
                        </div>
                    </div>
                    <button @click="markAllNotificationsRead" class="mt-2 text-sm font-medium text-blue-600 hover:text-blue-800">
                        Marcar todas como lidas
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-4 border-4 border-orange-500 rounded-full animate-spin border-t-transparent"></div>
                <p class="text-gray-600">Carregando suas reclamações...</p>
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="p-6 text-center rounded-lg bg-red-50">
            <svg class="w-12 h-12 mx-auto mb-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="mb-4 text-lg font-semibold text-gray-800">Erro ao carregar reclamações</p>
            <p class="mb-4 text-gray-600">{{ error }}</p>
            <button @click="loadGrievances" class="px-4 py-2 text-white bg-orange-500 rounded-lg hover:bg-orange-600">
                Tentar novamente
            </button>
        </div>

        <!-- Content -->
        <template v-else>
            <!-- Cartões de Estatísticas -->
            <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
                <div class="p-6 transition-all duration-300 bg-white shadow-sm rounded-xl hover:shadow-md">
                    <h3 class="mb-2 text-lg font-semibold text-gray-800">Total de Reclamações</h3>
                    <div class="mb-1 text-4xl font-bold text-gray-900">{{ stats.total || 0 }}</div>
                </div>
                <div class="p-6 transition-all duration-300 bg-white shadow-sm rounded-xl hover:shadow-md">
                    <h3 class="mb-2 text-lg font-semibold text-gray-800">Resolvidas</h3>
                    <div class="mb-1 text-4xl font-bold text-green-600">{{ stats.resolved || 0 }}</div>
                </div>
                <div class="p-6 transition-all duration-300 bg-white shadow-sm rounded-xl hover:shadow-md">
                    <h3 class="mb-2 text-lg font-semibold text-gray-800">Em Progresso</h3>
                    <div class="mb-1 text-4xl font-bold text-orange-600">{{ stats.in_progress || 0 }}</div>
                </div>
                <div class="p-6 transition-all duration-300 bg-white shadow-sm rounded-xl hover:shadow-md">
                    <h3 class="mb-2 text-lg font-semibold text-gray-800">Pendentes</h3>
                    <div class="mb-1 text-4xl font-bold text-blue-600">{{ stats.submitted || 0 }}</div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="p-4 mb-6 bg-white rounded-lg shadow-sm">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Status</label>
                        <select v-model="filters.status" @change="applyFilters"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">Todos</option>
                            <option value="submitted">Submetida</option>
                            <option value="in_progress">Em Progresso</option>
                            <option value="resolved">Resolvida</option>
                            <option value="closed">Fechada</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Prioridade</label>
                        <select v-model="filters.priority" @change="applyFilters"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">Todas</option>
                            <option value="low">Baixa</option>
                            <option value="medium">Média</option>
                            <option value="high">Alta</option>
                            <option value="urgent">Urgente</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Categoria</label>
                        <select v-model="filters.category" @change="applyFilters"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">Todas</option>
                            <option value="Serviços Públicos">Serviços Públicos</option>
                            <option value="Infraestrutura">Infraestrutura</option>
                            <option value="Ambiental">Ambiental</option>
                            <option value="Social">Social</option>
                            <option value="Administração">Administração</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Pesquisar</label>
                        <input v-model="filters.search" @input="debouncedSearch"
                            type="text" placeholder="Código ou descrição..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
                    </div>
                </div>
            </div>

            <!-- Tabela de Reclamações -->
            <div class="overflow-hidden bg-white rounded-lg shadow-sm">
                <div v-if="grievances.data && grievances.data.length === 0" class="py-12 text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-lg font-semibold text-gray-800">Nenhuma reclamação encontrada</p>
                    <p class="mt-2 text-gray-600">Clique em "Nova Reclamação" para criar uma.</p>
                </div>

                <table v-else class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Referência</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Categoria</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Prioridade</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Data</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="grievance in grievances.data" :key="grievance.id" class="transition-colors hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                {{ grievance.reference_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ grievance.category }}</div>
                                <div v-if="grievance.subcategory" class="text-xs text-gray-500">{{ grievance.subcategory }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getStatusBadgeClass(grievance.status)" class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full">
                                    {{ grievance.status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getPriorityBadgeClass(grievance.priority)" class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full">
                                    {{ grievance.priority_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ grievance.submitted_at || grievance.created_at }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                <button @click="viewDetails(grievance)"
                                    class="text-orange-600 hover:text-orange-900">
                                    Ver detalhes
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Paginação -->
                <div v-if="grievances.data && grievances.data.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Mostrando {{ grievances.from }} a {{ grievances.to }} de {{ grievances.total }} resultados
                        </div>
                        <div class="flex space-x-2">
                            <button v-for="link in grievances.links" :key="link.label"
                                @click="goToPage(link.url)"
                                :disabled="!link.url"
                                :class="[
                                    'px-3 py-1 text-sm rounded',
                                    link.active ? 'bg-orange-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                                v-html="link.label">
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import { usePage } from '@inertiajs/vue3'
import ComplaintForm from './ComplaintForm.vue'
import GrievanceDetails from './GrievanceDetails.vue'

const page = usePage()

const loading = ref(false)
const error = ref(null)
const showComplaintForm = ref(false)
const selectedGrievance = ref(null)

const stats = ref({
    total: 0,
    submitted: 0,
    in_progress: 0,
    resolved: 0,
    closed: 0
})

const grievances = ref({
    data: [],
    links: [],
    from: 0,
    to: 0,
    total: 0
})

const notifications = ref([])

const filters = ref({
    status: '',
    priority: '',
    category: '',
    search: ''
})

let searchTimeout = null

const loadGrievances = async () => {
    loading.value = true
    error.value = null

    try {
        // Usar os dados já disponíveis da página ao invés de fazer fetch
        const page = usePage()
        
        if (page.props.stats) {
            stats.value = page.props.stats
        }
        
        if (page.props.grievances) {
            grievances.value = page.props.grievances
        }
        
        if (page.props.notifications) {
            notifications.value = page.props.notifications
        }
        
        loading.value = false
    } catch (err) {
        error.value = err.message
        console.error('Erro ao carregar reclamações:', err)
        loading.value = false
    }
}

const applyFilters = () => {
    loadGrievances()
}

const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        applyFilters()
    }, 500)
}

const goToPage = (url) => {
    if (!url) return
    window.location.href = url
}

const viewDetails = (grievance) => {
    selectedGrievance.value = grievance
}

const closeComplaintForm = () => {
    showComplaintForm.value = false
}

const handleNewComplaint = async (success) => {
    closeComplaintForm()
    if (success) {
        await loadGrievances()
    }
}

const markAllNotificationsRead = async () => {
    try {
        const notificationIds = notifications.value.map(n => n.id)

        const response = await fetch('/utente/notifications/read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ notification_ids: notificationIds })
        })

        if (response.ok) {
            notifications.value = []
        }
    } catch (err) {
        console.error('Erro ao marcar notificações:', err)
    }
}

const getStatusBadgeClass = (status) => {
    const classes = {
        'submitted': 'bg-blue-100 text-blue-800',
        'under_review': 'bg-yellow-100 text-yellow-800',
        'assigned': 'bg-purple-100 text-purple-800',
        'in_progress': 'bg-orange-100 text-orange-800',
        'pending_approval': 'bg-indigo-100 text-indigo-800',
        'resolved': 'bg-green-100 text-green-800',
        'closed': 'bg-gray-100 text-gray-800',
        'rejected': 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPriorityBadgeClass = (priority) => {
    const classes = {
        'low': 'bg-green-100 text-green-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'high': 'bg-orange-100 text-orange-800',
        'urgent': 'bg-red-100 text-red-800'
    }
    return classes[priority] || 'bg-gray-100 text-gray-800'
}

onMounted(() => {
    // Tentar usar dados do Inertia primeiro
    if (page.props.stats) {
        stats.value = page.props.stats
    }
    if (page.props.grievances) {
        grievances.value = page.props.grievances
    }
    if (page.props.notifications) {
        notifications.value = page.props.notifications
    }

    // Se não houver dados, carregar via API
    if (!grievances.value.data || grievances.value.data.length === 0) {
        loadGrievances()
    }
})
</script>

<style scoped>
.badge {
    @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
}

.badge-environmental {
    @apply bg-green-100 text-green-800;
}

.badge-social {
    @apply bg-blue-100 text-blue-800;
}

.badge-economic {
    @apply bg-purple-100 text-purple-800;
}

.badge-resolved {
    @apply bg-green-100 text-green-800;
}

.badge-progress {
    @apply bg-yellow-100 text-yellow-800;
}

.badge-pending {
    @apply bg-red-100 text-red-800;
}
</style>
