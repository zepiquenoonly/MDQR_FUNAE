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
            <div v-if="activePanel === 'dashboard'" class="p-3 sm:p-4 md:p-6 space-y-4 sm:space-y-6">
                <!-- Welcome Message - Transparent -->
                <div class="mb-4">
                    <div class="py-4">
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-2 text-gray-900 dark:text-white">
                            Bem-vindo(a), {{ user.name }}!
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400 text-sm sm:text-base lg:text-lg">
                            Acompanhe suas reclamações e submissões em tempo real
                        </p>
                    </div>
                </div>

                <!-- Quick Actions -->
                <QuickActions @create-complaint="showComplaintForm = true" @view-submissions="showSubmissionsModal = true" />

                <!-- Stats Grid -->
                <StatsGrid />

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
                    title="Minhas Submissões Recentes" 
                    :rows="recentSubmissions"
                />
            </div>

            <!-- Projectos View -->
            <div v-if="activePanel === 'projectos'" class="p-4 sm:p-6">
                <TabSection @view-project-details="handleViewProjectDetails" />
            </div>

            <!-- MDQR Views -->
            <div v-if="activePanel === 'mdqr'" class="p-3 sm:p-4 md:p-6">
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
    </Layout>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { useDashboard } from '@/Composables/useDashboard'
import { usePageProps } from '@/Composables/usePageProps'
import Layout from '@/Layouts/UnifiedLayout.vue'
import StatsGrid from '@/Components/UtenteDashboard/StatsGrid.vue'
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
const { statsByType } = usePageProps()

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

const showComplaintForm = ref(false)
const showSubmissionsModal = ref(false)

const handleComplaintSuccess = () => {
    showComplaintForm.value = false
    router.reload({ only: ['recentSubmissions', 'stats', 'chartDataByType', 'chartDataByStatus'] })
}
</script>
