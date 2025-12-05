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
                <!-- Welcome Message with Glass Effect -->
                <div class="relative overflow-hidden rounded-2xl p-6 sm:p-8 lg:p-10 shadow-2xl border border-orange-400/30 group transition-all duration-500 hover:shadow-3xl">
                    <!-- Animated Gradient Background -->
                    <div class="absolute inset-0 bg-gradient-to-r from-primary-500 via-orange-500 to-orange-600"></div>
                    <!-- Glass Layer -->
                    <div class="absolute inset-0 backdrop-blur-sm bg-white/10"></div>
                    <!-- Content -->
                    <div class="relative z-10">
                        <h1 class="text-xl sm:text-2xl lg:text-3xl xl:text-4xl font-bold mb-2 text-white drop-shadow-lg">
                            Bem-vindo(a), {{ user.name }}!
                        </h1>
                        <p class="text-orange-50 text-sm sm:text-base lg:text-lg font-medium drop-shadow">Acompanhe suas reclamações e submissões em tempo real</p>
                    </div>
                    <!-- Decorative Elements -->
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-700"></div>
                    <div class="absolute -left-10 -top-10 w-32 h-32 bg-orange-300/10 rounded-full blur-2xl group-hover:scale-125 transition-transform duration-700"></div>
                    <!-- Floating particles -->
                    <div class="absolute top-1/4 right-1/4 w-2 h-2 bg-white/30 rounded-full animate-pulse"></div>
                    <div class="absolute top-3/4 right-1/3 w-2 h-2 bg-white/20 rounded-full animate-pulse"></div>
                    <div class="absolute top-1/2 right-1/2 w-1 h-1 bg-white/40 rounded-full animate-pulse"></div>
                </div>

                <!-- Notifications Widget -->
                <NotificationWidget />

                <!-- Quick Actions -->
                <QuickActions />

                <!-- Stats Grid -->
                <StatsGrid />

                <!-- Charts and Tables Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                    <!-- Chart Component -->
                    <ChartBarComponent
                        title="Estatísticas por Tipo"
                        period="Este Mês"
                        :data="[
                            { label: 'Reclamações', value: 45, percentage: 75, color: 'bg-gradient-to-r from-primary-500 to-orange-600' },
                            { label: 'Queixas', value: 32, percentage: 53, color: 'bg-gradient-to-r from-blue-500 to-indigo-600' },
                            { label: 'Sugestões', value: 18, percentage: 30, color: 'bg-gradient-to-r from-green-500 to-emerald-600' }
                        ]"
                    />

                    <!-- Chart Component 2 -->
                    <ChartBarComponent
                        title="Status das Submissões"
                        period="Últimos 7 dias"
                        :data="[
                            { label: 'Resolvidas', value: 28, percentage: 70, color: 'bg-gradient-to-r from-green-500 to-emerald-600' },
                            { label: 'Em Análise', value: 15, percentage: 37, color: 'bg-gradient-to-r from-yellow-500 to-amber-600' },
                            { label: 'Pendentes', value: 12, percentage: 30, color: 'bg-gradient-to-r from-gray-400 to-gray-600' }
                        ]"
                    />
                </div>

                <!-- Table Component -->
                <TableComponent title="Minhas Submissões Recentes" />
            </div>

            <!-- Projectos View -->
            <div v-if="activePanel === 'projectos'" class="p-4 sm:p-6">
                <TabSection @view-project-details="handleViewProjectDetails" />
            </div>

            <!-- MDQR Views -->
            <div v-if="activePanel === 'mdqr'" class="p-3 sm:p-4 md:p-6">
                <Suggestions v-if="activeDropdown === 'sugestoes'" />
                <Claims v-else-if="activeDropdown === 'queixas'" />
                <Complaints v-else-if="activeDropdown === 'reclamacoes'" />
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
    </Layout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useDashboardState } from '@/Components/UtenteDashboard/Composables/useDashboardState.js'
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
    }
})

const { activePanel, setActivePanel, activeDropdown } = useDashboardState()
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

// Função para lidar com o botão "voltar" do ProjectDetails
const handleBackFromProjectDetails = () => {
    console.log('Voltando dos detalhes do projeto')
    selectedProjectId.value = null
}
</script>
