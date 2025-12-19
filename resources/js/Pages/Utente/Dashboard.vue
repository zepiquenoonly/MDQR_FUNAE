<template>
    <Layout :user="user" :role="'utente'" @change-view="handleViewChange">
        <!-- Breadcrumb dinâmico -->
        <!-- <div class="p-4 sm:p-6">
            <Breadcrumb :active-view="activePanel" />
        </div> -->

        <!-- Renderizar ProjectDetails se houver projectId -->
        <ProjectDetails v-if="selectedProjectId" :project-id="selectedProjectId"
            @back="handleBackFromProjectDetails" />

        <!-- Renderizar conteúdo baseado na view ativa -->
        <div v-else>
            <!-- Default Dashboard Content -->
            <div v-if="activePanel === 'dashboard'" class="space-y-6 sm:space-y-8 px-3 sm:px-0">
                <!-- Welcome Message - Glassmorphism Hero -->
                <div class="relative overflow-hidden rounded-3xl">
                    <div class="absolute inset-0 bg-gradient-to-br from-primary-600 via-orange-600 to-amber-700"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent"></div>

                    <!-- Floating Elements -->
                    <div class="absolute top-6 left-10 w-20 h-20 bg-white/10 rounded-full blur-xl animate-pulse"></div>
                    <div class="absolute bottom-6 right-10 w-32 h-32 bg-purple-300/20 rounded-full blur-2xl animate-pulse delay-1000"></div>
                    <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-indigo-200/15 rounded-full blur-lg animate-pulse delay-500"></div>

                    <div class="relative p-8 sm:p-10">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-16 h-16 bg-white/10 backdrop-blur-xl rounded-2xl flex items-center justify-center border border-white/20 shadow-2xl shadow-black/10">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-2 text-white drop-shadow-2xl">
                                    Bem-vindo(a), <span class="bg-gradient-to-r from-purple-200 to-indigo-200 bg-clip-text text-transparent">{{ user.name }}</span>!
                                </h1>
                                <div class="w-24 h-1 bg-gradient-to-r from-purple-300 to-indigo-300 rounded-full mb-4"></div>
                            </div>
                        </div>
                        <p class="text-white/90 text-base sm:text-lg lg:text-xl leading-relaxed max-w-4xl drop-shadow-lg">
                            Acompanhe suas reclamações e submissões em tempo real
                        </p>
                    </div>
                </div>

                <!-- Quick Actions -->
                <QuickActions @create-complaint="showComplaintForm = true" @view-submissions="showSubmissionsModal = true" />

                <!-- Stats Grid -->
                <div class="mb-8">
                    <div class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">
                        <h1>Visão Geral das Minhas Submissões</h1>
                    </div>

                    <!-- Estatísticas por Tipo -->
                    <div class="text-lg font-semibold text-gray-800 dark:text-white mb-3">
                        <h2 class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center shadow-lg">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            Estatísticas por Tipo
                        </h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <KpiCard
                            title="Reclamações"
                            :value="statsByType.complaints"
                            description="Total de reclamações submetidas"
                            icon="DocumentTextIcon"
                            trend="up"
                        />
                        <KpiCard
                            title="Queixas"
                            :value="statsByType.grievances"
                            description="Total de queixas submetidas"
                            icon="ExclamationTriangleIcon"
                            trend="stable"
                        />
                        <KpiCard
                            title="Sugestões"
                            :value="statsByType.suggestions"
                            description="Total de sugestões submetidas"
                            icon="PaperAirplaneIcon"
                            trend="up"
                        />
                    </div>

                    <!-- Estatísticas por Status -->
                    <div class="text-lg font-semibold text-gray-800 dark:text-white mb-3 mt-8">
                        <h2 class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-gradient-to-br from-cyan-500 to-teal-600 rounded-lg flex items-center justify-center shadow-lg">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                            Status das Submissões
                        </h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                        <KpiCard
                            title="Total de Submissões"
                            :value="stats.total"
                            description="Todas as submissões"
                            icon="DocumentTextIcon"
                            trend="up"
                        />
                        <KpiCard
                            title="Em Progresso"
                            :value="stats.in_progress"
                            description="Sendo processadas"
                            icon="ClockIcon"
                            trend="stable"
                        />
                        <KpiCard
                            title="Resolvidas"
                            :value="stats.resolved"
                            description="Concluídas com sucesso"
                            icon="CheckCircleIcon"
                            trend="up"
                        />
                        <KpiCard
                            title="Pendentes"
                            :value="stats.submitted"
                            description="Aguardando análise"
                            icon="ExclamationCircleIcon"
                            trend="down"
                        />
                    </div>
                </div>

                <!-- Charts and Tables Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                    <!-- Chart Component -->
                    <ChartBarComponent
                        title="Estatísticas por Tipo"
                        period="Últimos 7 dias"
                        :data="chartDataByType"
                    />

                    <!-- Chart Component 2 -->
                    <ChartBarComponent
                        title="Status das Submissões"
                        period="Últimos 7 dias"
                        :data="chartDataByStatus"
                    />
                </div>

                <!-- Table Component -->
                <TableComponent
                    title="Minhas Submissões"
                    :rows="recentSubmissions"
                    @view-details="handleViewSubmissionDetails"
                />
            </div>

            <!-- Projectos View -->
            <div v-if="activePanel === 'projectos'" class="p-4 sm:p-6">
                <TabSection @view-project-details="handleViewProjectDetails" />
            </div>

            <!-- MDQR Views -->
            <div v-if="activePanel === 'mdqr'" class="p-3 sm:p-4 md:p-6 space-y-4 sm:space-y-6">
                <!-- Welcome Message - Glassmorphism Hero -->
                <div class="relative overflow-hidden rounded-3xl mb-8">
                    <div class="absolute inset-0 bg-gradient-to-br from-violet-600 via-purple-600 to-indigo-700"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent"></div>

                    <!-- Floating Elements -->
                    <div class="absolute top-6 left-10 w-20 h-20 bg-white/10 rounded-full blur-xl animate-pulse"></div>
                    <div class="absolute bottom-6 right-10 w-32 h-32 bg-purple-300/20 rounded-full blur-2xl animate-pulse delay-1000"></div>
                    <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-indigo-200/15 rounded-full blur-lg animate-pulse delay-500"></div>

                    <div class="relative p-8 sm:p-10">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-16 h-16 bg-white/10 backdrop-blur-xl rounded-2xl flex items-center justify-center border border-white/20 shadow-2xl shadow-black/10">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-2 text-white drop-shadow-2xl">
                                    <span class="bg-gradient-to-r from-violet-200 to-indigo-200 bg-clip-text text-transparent">MDQR</span>
                                </h1>
                                <div class="w-24 h-1 bg-gradient-to-r from-violet-300 to-indigo-300 rounded-full mb-4"></div>
                            </div>
                        </div>
                        <p class="text-white/90 text-base sm:text-lg lg:text-xl leading-relaxed max-w-4xl drop-shadow-lg">
                            Gestão de Manifestações, Denúncias, Queixas e Reclamações
                        </p>
                    </div>
                </div>

                <Suggestions
                    v-if="activeDropdown === 'sugestoes'"
                    :suggestions="suggestions"
                    :suggestions-stats="suggestionsStats"
                />
                <Claims
                    v-else-if="activeDropdown === 'queixas'"
                    :claims="claims"
                    :claims-stats="claimsStats"
                />
                <Complaints
                    v-else-if="activeDropdown === 'reclamacoes'"
                    :complaints="complaints"
                    :complaints-stats="complaintsStats"
                />
                <div v-else class="glass-card p-8 sm:p-12">
                    <div class="text-center">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-primary-50 to-orange-50 rounded-2xl flex items-center justify-center mx-auto mb-4 sm:mb-6 border border-primary-200">
                            <svg class="w-8 h-8 sm:w-10 sm:h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold gradient-text mb-3 sm:mb-4">MDQR Dashboard</h2>
                        <p class="text-gray-600 text-sm sm:text-base">Selecione uma opção do menu MDQR para começar.</p>
                    </div>
                </div>
            </div>
        </div>
        <ComplaintForm :visible="showComplaintForm" @close="showComplaintForm = false" @success="handleComplaintSuccess" />
        <SubmissionsModal :visible="showSubmissionsModal" @close="showSubmissionsModal = false" :submissions="recentSubmissions" />
        <GrievanceDetails v-if="selectedGrievance" :grievance="selectedGrievance" @close="selectedGrievance = null" />
    </Layout>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { useDashboard } from '@/Composables/useDashboard'
import { usePageProps } from '@/Composables/usePageProps'
import Layout from '@/Layouts/UnifiedLayout.vue'
import KpiCard from '@/Components/GestorReclamacoes/KpiCard.vue'
import Breadcrumb from '@/Components/UtenteDashboard/Breadcrumb.vue'
import NotificationWidget from '@/Components/UtenteDashboard/NotificationWidget.vue'
import QuickActions from '@/Components/UtenteDashboard/QuickActions.vue'
import ProjectDetails from '@/Components/UtenteDashboard/ProjectDetails.vue'
import TabSection from '@/Components/UtenteDashboard/TabSection.vue'
import Suggestions from '@/Components/UtenteDashboard/Suggestions.vue'
import Claims from '@/Components/UtenteDashboard/Claims.vue'
import Complaints from '@/Components/UtenteDashboard/Complaints.vue'
import ChartBarComponent from '@/Components/UtenteDashboard/ChartBarComponent.vue'
import TableComponent from '@/Components/UtenteDashboard/TableComponent.vue'
import ComplaintForm from '@/Components/UtenteDashboard/ComplaintForm.vue'
import SubmissionsModal from '@/Components/UtenteDashboard/SubmissionsModal.vue'
import GrievanceDetails from '@/Components/UtenteDashboard/GrievanceDetails.vue'

const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    projectId: {
        type: Number,
        default: null
    },
    stats: {
        type: Object,
        default: () => ({})
    },
    grievances: {
        type: Object,
        default: () => ({ data: [], total: 0 })
    },
    notifications: {
        type: Array,
        default: () => []
    },
    complaints: {
        type: Array,
        default: () => []
    },
    claims: {
        type: Array,
        default: () => []
    },
    suggestions: {
        type: Array,
        default: () => []
    },
    complaintsStats: {
        type: Object,
        default: () => ({})
    },
    claimsStats: {
        type: Object,
        default: () => ({})
    },
    suggestionsStats: {
        type: Object,
        default: () => ({})
    },
    chartDataByType: {
        type: Object,
        default: () => ({})
    },
    chartDataByStatus: {
        type: Object,
        default: () => ({})
    },
    recentSubmissions: {
        type: Array,
        default: () => []
    }
})

const page = usePage()
const { getSafeProp } = usePageProps()

const stats = computed(() => {
  return getSafeProp('stats', {
    total: 0,
    submitted: 0,
    in_progress: 0,
    resolved: 0,
    closed: 0,
    rejected: 0
  })
})

const statsByType = computed(() => {
  return getSafeProp('statsByType', {
    complaints: 0,
    grievances: 0,
    suggestions: 0
  })
})

// Dados computados para gráficos
const chartDataByType = computed(() => {
    const data = props.chartDataByType || page.props.chartDataByType || {}
    const total = (data.complaints || 0) + (data.grievances || 0) + (data.suggestions || 0)

    if (total === 0) {
        return [
            { label: 'Reclamações', value: 0, percentage: 0, color: 'bg-gradient-to-r from-primary-500 to-orange-600' },
            { label: 'Queixas', value: 0, percentage: 0, color: 'bg-gradient-to-r from-blue-500 to-indigo-600' },
            { label: 'Sugestões', value: 0, percentage: 0, color: 'bg-gradient-to-r from-green-500 to-emerald-600' }
        ]
    }

    return [
        {
            label: 'Reclamações',
            value: data.complaints || 0,
            percentage: Math.round(((data.complaints || 0) / total) * 100),
            color: 'bg-gradient-to-r from-primary-500 to-orange-600'
        },
        {
            label: 'Queixas',
            value: data.grievances || 0,
            percentage: Math.round(((data.grievances || 0) / total) * 100),
            color: 'bg-gradient-to-r from-blue-500 to-indigo-600'
        },
        {
            label: 'Sugestões',
            value: data.suggestions || 0,
            percentage: Math.round(((data.suggestions || 0) / total) * 100),
            color: 'bg-gradient-to-r from-green-500 to-emerald-600'
        }
    ]
})

const chartDataByStatus = computed(() => {
    const data = props.chartDataByStatus || page.props.chartDataByStatus || {}
    const total = (data.resolved || 0) + (data.in_progress || 0) + (data.pending || 0)

    if (total === 0) {
        return [
            { label: 'Resolvidas', value: 0, percentage: 0, color: 'bg-gradient-to-r from-green-500 to-emerald-600' },
            { label: 'Em Análise', value: 0, percentage: 0, color: 'bg-gradient-to-r from-yellow-500 to-amber-600' },
            { label: 'Pendentes', value: 0, percentage: 0, color: 'bg-gradient-to-r from-gray-400 to-gray-600' }
        ]
    }

    return [
        {
            label: 'Resolvidas',
            value: data.resolved || 0,
            percentage: Math.round(((data.resolved || 0) / total) * 100),
            color: 'bg-gradient-to-r from-green-500 to-emerald-600'
        },
        {
            label: 'Em Análise',
            value: data.in_progress || 0,
            percentage: Math.round(((data.in_progress || 0) / total) * 100),
            color: 'bg-gradient-to-r from-yellow-500 to-amber-600'
        },
        {
            label: 'Pendentes',
            value: data.pending || 0,
            percentage: Math.round(((data.pending || 0) / total) * 100),
            color: 'bg-gradient-to-r from-gray-400 to-gray-600'
        }
    ]
})

const recentSubmissions = computed(() => {
    return props.recentSubmissions || page.props.recentSubmissions || []
})

const { activePanel, setActivePanel, activeDropdown } = useDashboard()
const selectedProjectId = ref(null)
const selectedGrievance = ref(null)

// Watch para mudanças no activePanel - IMPORTANTE: Fechar ProjectDetails quando o painel mudar
watch(activePanel, (newPanel) => {
    console.log('Active panel changed to:', newPanel)
    // Se o painel mudar e não for 'projectos', fechar os detalhes do projeto
    if (newPanel !== 'projectos' && selectedProjectId.value) {
        selectedProjectId.value = null
    }
})

// Watch para mudanças no activeDropdown - Fechar ProjectDetails quando dropdown mudar
watch(activeDropdown, (newDropdown) => {
    console.log('Active dropdown changed to:', newDropdown)
    if (selectedProjectId.value) {
        selectedProjectId.value = null
    }
})

// Função para lidar com mudanças de view do menu
const handleViewChange = (view) => {
    console.log('Dashboard recebeu change-view:', view)
    setActivePanel(view)
    // Fechar ProjectDetails quando qualquer view for selecionada
    selectedProjectId.value = null
}

// Função para lidar com visualização de detalhes do projeto
const handleViewProjectDetails = (projectId) => {
    console.log('Visualizar detalhes do projeto:', projectId)
    selectedProjectId.value = projectId
}

const handleBackFromProjectDetails = () => {
    console.log('Voltando dos detalhes do projeto')
    selectedProjectId.value = null
}

const handleViewSubmissionDetails = (submission) => {
    console.log('Visualizar detalhes da submissão:', submission)
    selectedGrievance.value = submission
}

const showComplaintForm = ref(false)
const showSubmissionsModal = ref(false)

const handleComplaintSuccess = () => {
    showComplaintForm.value = false
    router.reload({ only: ['recentSubmissions', 'stats', 'chartDataByType', 'chartDataByStatus'] })
}
</script>
