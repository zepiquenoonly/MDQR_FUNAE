<template>
    <Layout :role="'pca'">
        <div class="space-y-6 sm:space-y-8 px-3 sm:px-0">
            <!-- Welcome Message - Glassmorphism Hero -->
            <div class="relative overflow-hidden rounded-3xl mb-8">
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-2 text-white drop-shadow-2xl">
                                Bem-vindo(a), <span class="bg-gradient-to-r from-purple-200 to-indigo-200 bg-clip-text text-transparent">{{ $page.props.auth?.user?.name }}</span>!
                            </h1>
                            <div class="w-24 h-1 bg-gradient-to-r from-purple-300 to-indigo-300 rounded-full mb-4"></div>
                        </div>
                    </div>
                    <p class="text-white/90 text-base sm:text-lg lg:text-xl leading-relaxed max-w-4xl drop-shadow-lg">
                        Painel de Controlo e Estatísticas Globais
                    </p>
                </div>
            </div>

            <!-- Header Section -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">

                <!-- Filters -->
                <div class="flex flex-wrap gap-3">
                    <select v-model="filters.start_date" @change="applyFilters"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand">
                        <option value="">Período</option>
                        <option :value="getDateString(7)">Últimos 7 dias</option>
                        <option :value="getDateString(30)">Últimos 30 dias</option>
                        <option :value="getDateString(90)">Últimos 3 meses</option>
                        <option :value="getDateString(180)">Últimos 6 meses</option>
                    </select>

                    <button @click="exportReport"
                        class="px-4 py-2 bg-brand hover:bg-brand-dark text-white rounded-lg transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Exportar
                    </button>
                </div>
            </div>

            <!-- Global Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <StatCard title="Total Reclamações" :value="globalStats.total_complaints" icon="clipboard"
                    color="blue" />
                <StatCard title="Resolvidas" :value="globalStats.resolved_complaints" icon="check" color="green"
                    :percentage="globalStats.resolution_rate" />
                <StatCard title="Pendentes" :value="globalStats.pending_complaints" icon="clock" color="yellow" />
                <StatCard title="Em Progresso" :value="globalStats.in_progress_complaints" icon="progress"
                    color="purple" />
                <StatCard title="Tempo Médio (dias)" :value="globalStats.average_resolution_time" icon="timer"
                    color="orange" />
                <StatCard title="Taxa de Resolução" :value="`${globalStats.resolution_rate}%`" icon="chart"
                    color="teal" />
            </div>

            <!-- Submission Types Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Tipos de Submissões
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 border-l-4 border-red-500">
                        <p class="text-sm text-red-600 dark:text-red-400 font-medium">Reclamações</p>
                        <p class="text-2xl font-bold text-red-700 dark:text-red-300 mt-1">
                            {{ complaintsByType.complaint || 0 }}
                        </p>
                    </div>
                    <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4 border-l-4 border-orange-500">
                        <p class="text-sm text-orange-600 dark:text-orange-400 font-medium">Queixas</p>
                        <p class="text-2xl font-bold text-orange-700 dark:text-orange-300 mt-1">
                            {{ complaintsByType.grievance || 0 }}
                        </p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border-l-4 border-green-500">
                        <p class="text-sm text-green-600 dark:text-green-400 font-medium">Sugestões</p>
                        <p class="text-2xl font-bold text-green-700 dark:text-green-300 mt-1">
                            {{ complaintsByType.suggestion || 0 }}
                        </p>
                    </div>
                </div>
                <TypeChart :data="complaintsByType" />
            </div>

            <!-- Trend Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Tendência de Submissões por Tipo (Últimos 6 Meses)
                </h3>
                <div class="mb-4">
                    <div class="grid grid-cols-4 gap-4 text-center">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                            <p class="text-2xl font-bold text-brand">{{ getTotalFromTrend() }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Total</p>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3">
                            <p class="text-2xl font-bold text-red-600">{{ getTypeTotal('complaint') }}</p>
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">Reclamações</p>
                        </div>
                        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-3">
                            <p class="text-2xl font-bold text-orange-600">{{ getTypeTotal('grievance') }}</p>
                            <p class="text-xs text-orange-600 dark:text-orange-400 mt-1">Queixas</p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                            <p class="text-2xl font-bold text-green-600">{{ getTypeTotal('suggestion') }}</p>
                            <p class="text-xs text-green-600 dark:text-green-400 mt-1">Sugestões</p>
                        </div>
                    </div>
                </div>
                <TrendChart :data="trendData" />
            </div>

            <!-- Project Insights -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Submissions by Project -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Submissões por Projeto (Top 10)
                    </h3>
                    <div class="space-y-4">
                        <div v-for="project in submissionsByProject" :key="project.id"
                            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">{{ project.name }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ project.province }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-brand">{{ project.total_submissions }}</p>
                                <p class="text-sm text-green-600">{{ project.resolution_rate }}% resolvidas</p>
                            </div>
                        </div>
                        <div v-if="submissionsByProject.length === 0" class="text-center py-8 text-gray-500">
                            Nenhum projeto com submissões no período selecionado
                        </div>
                    </div>
                </div>

                <!-- Projects with Technicians -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Projetos com Técnicos Disponíveis
                    </h3>
                    <div class="space-y-4">
                        <div v-for="project in projectsWithTechnicians" :key="project.id"
                            class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <p class="font-medium text-gray-900 dark:text-white">{{ project.name }}</p>
                                <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs rounded-full">
                                    {{ project.technicians_count }} técnicos
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ project.province }}</p>
                            <div class="flex flex-wrap gap-1">
                                <span v-for="tech in project.technicians.slice(0, 3)" :key="tech.id"
                                    class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs rounded">
                                    {{ tech.name }}
                                </span>
                                <span v-if="project.technicians.length > 3" class="px-2 py-1 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs rounded">
                                    +{{ project.technicians.length - 3 }} mais
                                </span>
                            </div>
                            <p class="text-sm text-orange-600 mt-2">
                                {{ project.active_grievances }} reclamações ativas
                            </p>
                        </div>
                        <div v-if="projectsWithTechnicians.length === 0" class="text-center py-8 text-gray-500">
                            Nenhum projeto com técnicos atribuídos
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Performance Metrics -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Métricas de Projetos
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-brand">{{ projectPerformance.total_projects }}</p>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">Total de Projetos</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-green-600">{{ projectPerformance.projects_with_technicians }}</p>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">Com Técnicos</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-purple-600">{{ projectPerformance.projects_with_submissions }}</p>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">Com Submissões</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-orange-600">{{ projectPerformance.average_submissions_per_project }}</p>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">Média Submissões/Projeto</p>
                    </div>
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Technicians Performance -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Desempenho de Técnicos (Top 10)
                    </h3>
                    <TechniciansTable :technicians="topTechnicians" />
                </div>

                <!-- Recent Activities -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Atividades Recentes
                    </h3>
                    <ActivitiesList :activities="recentActivities" />
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Métricas de Desempenho
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-brand">{{ performanceMetrics.total_technicians }}</p>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">Total de Técnicos</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-green-600">{{ performanceMetrics.active_technicians }}</p>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">Técnicos Ativos</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-purple-600">{{
                            performanceMetrics.average_complaints_per_technician }}</p>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">Média de Reclamações/Técnico</p>
                    </div>
                </div>
            </div>

                        <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Priority Distribution -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Distribuição por Prioridade
                    </h3>
                    <PriorityChart :data="complaintsByPriority" />
                </div>

                <!-- Status Distribution by Type -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Distribuição por Estado e Tipo
                    </h3>
                    <div class="space-y-4">
                        <div v-for="(data, status) in complaintsByStatus" :key="status"
                            class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-gray-900 dark:text-white capitalize">
                                    {{ getStatusLabel(status) }}
                                </span>
                                <span class="text-lg font-bold text-brand">{{ data.total }}</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-sm">
                                <div class="flex items-center justify-between px-2 py-1 bg-red-50 dark:bg-red-900/20 rounded">
                                    <span class="text-red-600 dark:text-red-400">Recl.</span>
                                    <span class="font-semibold text-red-700 dark:text-red-300">{{ data.complaint }}</span>
                                </div>
                                <div class="flex items-center justify-between px-2 py-1 bg-orange-50 dark:bg-orange-900/20 rounded">
                                    <span class="text-orange-600 dark:text-orange-400">Queixa</span>
                                    <span class="font-semibold text-orange-700 dark:text-orange-300">{{ data.grievance }}</span>
                                </div>
                                <div class="flex items-center justify-between px-2 py-1 bg-green-50 dark:bg-green-900/20 rounded">
                                    <span class="text-green-600 dark:text-green-400">Sugest.</span>
                                    <span class="font-semibold text-green-700 dark:text-green-300">{{ data.suggestion }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '@/Layouts/UnifiedLayout.vue'
import StatCard from '@/Components/PCA/StatCard.vue'
import StatusChart from '@/Components/PCA/StatusChart.vue'
import PriorityChart from '@/Components/PCA/PriorityChart.vue'
import TypeChart from '@/Components/PCA/TypeChart.vue'
import TrendChart from '@/Components/PCA/TrendChart.vue'
import TechniciansTable from '@/Components/PCA/TechniciansTable.vue'
import ActivitiesList from '@/Components/PCA/ActivitiesList.vue'

const props = defineProps({
    globalStats: {
        type: Object,
        default: () => ({
            total_complaints: 0,
            resolved_complaints: 0,
            pending_complaints: 0,
            in_progress_complaints: 0,
            average_resolution_time: 0,
            resolution_rate: 0,
        })
    },
    complaintsByStatus: {
        type: Object,
        default: () => ({})
    },
    complaintsByPriority: {
        type: Object,
        default: () => ({})
    },
    complaintsByType: {
        type: Object,
        default: () => ({})
    },
    performanceMetrics: {
        type: Object,
        default: () => ({
            total_technicians: 0,
            active_technicians: 0,
            average_complaints_per_technician: 0,
        })
    },
    trendData: {
        type: Object,
        default: () => ({ labels: [], data: [] })
    },
    topTechnicians: {
        type: Array,
        default: () => []
    },
    recentActivities: {
        type: Array,
        default: () => []
    },
    submissionsByProject: {
        type: Array,
        default: () => []
    },
    projectsWithTechnicians: {
        type: Array,
        default: () => []
    },
    projectPerformance: {
        type: Object,
        default: () => ({
            total_projects: 0,
            projects_with_technicians: 0,
            projects_with_submissions: 0,
            average_submissions_per_project: 0,
        })
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const filters = ref({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    department: props.filters.department || '',
    complaint_type: props.filters.complaint_type || '',
})

const getDateString = (daysAgo) => {
    const date = new Date()
    date.setDate(date.getDate() - daysAgo)
    return date.toISOString().split('T')[0]
}

const applyFilters = () => {
    router.get('/pca/dashboard', filters.value, {
        preserveState: true,
        preserveScroll: true,
    })
}

const exportReport = () => {
    // TODO: Implementar exportação de relatório
    console.log('Funcionalidade de exportação será implementada')
}

const getStatusLabel = (status) => {
    const labels = {
        'submitted': 'Submetida',
        'under_review': 'Em Análise',
        'in_progress': 'Em Progresso',
        'resolved': 'Resolvida',
        'closed': 'Encerrada',
        'rejected': 'Rejeitada'
    }
    return labels[status] || status
}

const getTotalFromTrend = () => {
    if (!props.trendData?.data) return 0
    return props.trendData.data.reduce((sum, month) => sum + month.total, 0)
}

const getTypeTotal = (type) => {
    if (!props.trendData?.data) return 0
    return props.trendData.data.reduce((sum, month) => sum + (month[type] || 0), 0)
}
</script>
