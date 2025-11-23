<template>
    <Layout @change-view="handleViewChange">
        <div class="p-6">
            <!-- Breadcrumb dinâmico -->
            <Breadcrumb :active-view="activePanel" />

            <!-- Renderizar ProjectDetails se houver projectId -->
            <ProjectDetails v-if="selectedProjectId" :project-id="selectedProjectId"
                @back="handleBackFromProjectDetails" />

            <!-- Renderizar conteúdo baseado na view ativa -->
            <div v-else>
                <!-- Default Dashboard Content -->
                <div v-if="activePanel === 'dashboard'">
                    <!-- Welcome Message -->
                    <div class="mb-6 bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-6 text-white shadow-lg">
                        <h1 class="text-2xl font-bold mb-2">Bem-vindo(a), {{ user.name }}! 👋</h1>
                        <p class="text-orange-100">Acompanhe suas reclamações e submissões em tempo real</p>
                    </div>

                    <!-- Notifications Widget -->
                    <NotificationWidget />

                    <!-- Quick Actions -->
                    <QuickActions />

                    <!-- Stats Grid -->
                    <StatsGrid />

                    <!-- Recent Submissions -->
                    <SubmissionsSection />
                </div>

                <!-- Projectos View -->
                <div v-if="activePanel === 'projectos'">
                    <TabSection @view-project-details="handleViewProjectDetails" />
                </div>

                <!-- MDQR Views -->
                <div v-if="activePanel === 'mdqr'">
                    <Suggestions v-if="activeDropdown === 'sugestoes'" />
                    <Claims v-else-if="activeDropdown === 'queixas'" />
                    <Complaints v-else-if="activeDropdown === 'reclamacoes'" />
                    <div v-else>
                        <div class="text-center py-12">
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">MDQR Dashboard</h2>
                            <p class="text-gray-600">Select an option from the MDQR menu to get started.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useDashboardState } from '@/Components/UtenteDashboard/Composables/useDashboardState.js'
import Layout from '@/Layouts/UtenteDashboardLayout.vue'
import StatsGrid from '@/Components/UtenteDashboard/StatsGrid.vue'
import Breadcrumb from '@/Components/UtenteDashboard/Breadcrumb.vue'
import SubmissionsSection from '@/Components/UtenteDashboard/SubmissionsSection.vue'
import NotificationWidget from '@/Components/UtenteDashboard/NotificationWidget.vue'
import QuickActions from '@/Components/UtenteDashboard/QuickActions.vue'
import ProjectDetails from '@/Components/UtenteDashboard/ProjectDetails.vue'
import TabSection from '@/Components/UtenteDashboard/TabSection.vue'
import Suggestions from '@/Components/UtenteDashboard/Suggestions.vue'
import Claims from '@/Components/UtenteDashboard/Claims.vue'
import Complaints from '@/Components/UtenteDashboard/Complaints.vue'

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