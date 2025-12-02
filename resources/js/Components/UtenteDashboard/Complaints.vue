<template>
    <div class="space-y-6">
        <!-- Cabeçalho -->
        <div class="glass-card p-6 flex items-center justify-between hover:shadow-2xl transition-all duration-300 border border-white/40">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold gradient-text">Minhas Reclamações</h1>
                <p class="text-sm text-gray-600 mt-1">Gerencie todas as suas submissões</p>
            </div>
            <button @click="showComplaintForm = true"
                class="flex items-center gap-2 px-5 py-3 font-bold text-white bg-gradient-to-r from-primary-500 to-orange-600 hover:from-primary-600 hover:to-orange-700 transition-all duration-300 rounded-xl shadow-lg hover:shadow-2xl hover:scale-105 border border-orange-400/30 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                <svg class="w-5 h-5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="relative z-10">Nova Reclamação</span>
            </button>
        </div>

        <!-- Modals -->
        <ComplaintForm :visible="showComplaintForm" @close="closeComplaintForm" @submit="handleNewComplaint" />
        <GrievanceDetails v-if="selectedGrievance" :grievance="selectedGrievance" @close="selectedGrievance = null" />

        <!-- Notificações -->
        <div v-if="notifications && notifications.length > 0" class="glass-card p-6 border-l-4 border-primary-500 hover:shadow-2xl transition-all duration-300 relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-r from-primary-500/5 to-blue-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="flex items-start relative z-10">
                <div class="flex-shrink-0 p-2 bg-primary-50 rounded-xl">
                    <svg class="w-5 h-5 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1 ml-4">
                    <p class="text-sm font-bold text-gray-900">
                        Você tem {{ notifications.length }} notificação(ões) não lida(s)
                    </p>
                    <div class="mt-3 space-y-2">
                        <div v-for="notification in notifications.slice(0, 3)" :key="notification.id"
                            class="text-sm text-gray-700 flex items-center gap-2">
                            <div class="w-1.5 h-1.5 bg-primary-500 rounded-full"></div>
                            {{ notification.subject }}
                        </div>
                    </div>
                    <button @click="markAllNotificationsRead" class="mt-4 px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-primary-500 to-orange-600 hover:from-primary-600 hover:to-orange-700 rounded-lg transition-all duration-300 hover:scale-105 shadow-md hover:shadow-lg">
                        Marcar todas como lidas
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="glass-card p-12 text-center hover:shadow-2xl transition-all duration-300 border border-white/40">
            <div class="w-16 h-16 mx-auto mb-6 border-4 border-primary-500 rounded-full animate-spin border-t-transparent"></div>
            <p class="text-lg font-semibold text-gray-800">Carregando suas reclamações...</p>
            <p class="text-sm text-gray-600 mt-2">Aguarde um momento</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="glass-card p-8 text-center hover:shadow-2xl transition-all duration-300 border border-red-200 relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 to-orange-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative z-10">
                <div class="p-4 bg-red-50 rounded-2xl inline-block mb-4">
                    <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="mb-2 text-xl font-bold text-gray-900">Erro ao carregar reclamações</p>
                <p class="mb-6 text-gray-600">{{ error }}</p>
                <button @click="loadGrievances" class="px-6 py-3 font-semibold text-white bg-gradient-to-r from-primary-500 to-orange-600 hover:from-primary-600 hover:to-orange-700 rounded-xl transition-all duration-300 hover:scale-105 shadow-lg hover:shadow-2xl border border-orange-400/30">
                    Tentar novamente
                </button>
            </div>
        </div>

        <!-- Content -->
        <template v-else>
            <!-- Cartões de Estatísticas -->
            <div class="grid grid-cols-1 gap-4 sm:gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
                <div class="glass-card p-6 transition-all duration-300 hover:shadow-2xl hover:scale-105 group border border-white/40 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-primary-500/5 to-orange-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative z-10">
                        <h3 class="mb-2 text-sm font-semibold text-gray-700 group-hover:text-primary-700 transition-colors">Total de Reclamações</h3>
                        <div class="mb-1 text-4xl font-bold gradient-text group-hover:scale-110 transition-transform origin-left">{{ stats.total || 0 }}</div>
                    </div>
                </div>
                <div class="glass-card p-6 transition-all duration-300 hover:shadow-2xl hover:scale-105 group border border-white/40 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative z-10">
                        <h3 class="mb-2 text-sm font-semibold text-gray-700 group-hover:text-green-700 transition-colors">Resolvidas</h3>
                        <div class="mb-1 text-4xl font-bold text-green-600 group-hover:scale-110 transition-transform origin-left">{{ stats.resolved || 0 }}</div>
                    </div>
                </div>
                <div class="glass-card p-6 transition-all duration-300 hover:shadow-2xl hover:scale-105 group border border-white/40 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-amber-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative z-10">
                        <h3 class="mb-2 text-sm font-semibold text-gray-700 group-hover:text-orange-700 transition-colors">Em Progresso</h3>
                        <div class="mb-1 text-4xl font-bold text-orange-600 group-hover:scale-110 transition-transform origin-left">{{ stats.in_progress || 0 }}</div>
                    </div>
                </div>
                <div class="glass-card p-6 transition-all duration-300 hover:shadow-2xl hover:scale-105 group border border-white/40 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative z-10">
                        <h3 class="mb-2 text-sm font-semibold text-gray-700 group-hover:text-blue-700 transition-colors">Pendentes</h3>
                        <div class="mb-1 text-4xl font-bold text-blue-600 group-hover:scale-110 transition-transform origin-left">{{ stats.submitted || 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="glass-card p-6 mb-6 hover:shadow-2xl transition-all duration-300 border border-white/40">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <h3 class="text-lg font-bold text-gray-900">Filtros</h3>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Status</label>
                        <select v-model="filters.status" @change="applyFilters"
                            class="w-full px-4 py-2.5 bg-white/80 backdrop-blur-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer">
                            <option value="">Todos</option>
                            <option value="submitted">Submetida</option>
                            <option value="in_progress">Em Progresso</option>
                            <option value="resolved">Resolvida</option>
                            <option value="closed">Fechada</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Prioridade</label>
                        <select v-model="filters.priority" @change="applyFilters"
                            class="w-full px-4 py-2.5 bg-white/80 backdrop-blur-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer">
                            <option value="">Todas</option>
                            <option value="low">Baixa</option>
                            <option value="medium">Média</option>
                            <option value="high">Alta</option>
                            <option value="urgent">Urgente</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Categoria</label>
                        <select v-model="filters.category" @change="applyFilters"
                            class="w-full px-4 py-2.5 bg-white/80 backdrop-blur-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer">
                            <option value="">Todas</option>
                            <option value="Serviços Públicos">Serviços Públicos</option>
                            <option value="Infraestrutura">Infraestrutura</option>
                            <option value="Ambiental">Ambiental</option>
                            <option value="Social">Social</option>
                            <option value="Administração">Administração</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Pesquisar</label>
                        <input v-model="filters.search" @input="debouncedSearch"
                            type="text" placeholder="Código ou descrição..."
                            class="w-full px-4 py-2.5 bg-white/80 backdrop-blur-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 shadow-sm hover:shadow-md placeholder:text-gray-400" />
                    </div>
                </div>
            </div>

            <!-- Tabela de Reclamações -->
            <div class="glass-card overflow-hidden hover:shadow-2xl transition-all duration-300 border border-white/40">
                <div v-if="grievances.data && grievances.data.length === 0" class="py-16 text-center">
                    <div class="p-6 bg-gray-50 rounded-2xl inline-block mb-4">
                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-xl font-bold text-gray-900 mb-2">Nenhuma reclamação encontrada</p>
                    <p class="text-gray-600">Clique em "Nova Reclamação" para criar uma.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-primary-50/30">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-left text-gray-700 uppercase">Referência</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-left text-gray-700 uppercase">Categoria</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-left text-gray-700 uppercase">Status</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-left text-gray-700 uppercase">Prioridade</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-left text-gray-700 uppercase">Data</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-left text-gray-700 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white/50 backdrop-blur-sm divide-y divide-gray-100">
                            <tr v-for="grievance in grievances.data" :key="grievance.id" class="transition-all duration-200 hover:bg-primary-50/50 group">
                                <td class="px-6 py-4 text-sm font-bold text-gray-900 whitespace-nowrap">
                                    {{ grievance.reference_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900 group-hover:text-primary-700 transition-colors">{{ grievance.category }}</div>
                                    <div v-if="grievance.subcategory" class="text-xs text-gray-500">{{ grievance.subcategory }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatusBadgeClass(grievance.status)" class="inline-flex px-3 py-1 text-xs font-bold leading-5 rounded-full shadow-sm">
                                        {{ grievance.status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getPriorityBadgeClass(grievance.priority)" class="inline-flex px-3 py-1 text-xs font-bold leading-5 rounded-full shadow-sm">
                                        {{ grievance.priority_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                                    {{ grievance.submitted_at || grievance.created_at }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                    <button @click="viewDetails(grievance)"
                                        class="px-4 py-2 font-semibold text-primary-600 hover:text-white hover:bg-gradient-to-r from-primary-500 to-orange-600 rounded-lg transition-all duration-300 hover:scale-105 border border-primary-200 hover:border-transparent shadow-sm hover:shadow-md">
                                        Ver detalhes
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <div v-if="grievances.data && grievances.data.length > 0" class="px-6 py-5 border-t border-gray-200 bg-gradient-to-r from-gray-50/80 to-primary-50/30 backdrop-blur-sm">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm font-semibold text-gray-700">
                            Mostrando <span class="text-primary-600">{{ grievances.from }}</span> a <span class="text-primary-600">{{ grievances.to }}</span> de <span class="text-primary-600">{{ grievances.total }}</span> resultados
                        </div>
                        <div class="flex flex-wrap gap-2 justify-center">
                            <button v-for="link in grievances.links" :key="link.label"
                                @click="goToPage(link.url)"
                                :disabled="!link.url"
                                :class="[
                                    'px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-300',
                                    link.active ? 'bg-gradient-to-r from-primary-500 to-orange-600 text-white shadow-lg scale-105' : 'bg-white/80 text-gray-700 hover:bg-primary-50 border border-gray-200 hover:border-primary-300 hover:scale-105',
                                    !link.url ? 'opacity-50 cursor-not-allowed hover:scale-100' : 'shadow-sm hover:shadow-md'
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
