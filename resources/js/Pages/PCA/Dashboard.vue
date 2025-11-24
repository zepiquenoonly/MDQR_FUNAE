<template>
    <Layout>
        <div class="p-4 lg:p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
                        Dashboard PCA
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Painel de Controlo e Estatísticas Globais
                    </p>
                </div>

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

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Status Distribution -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Distribuição por Estado
                    </h3>
                    <StatusChart :data="complaintsByStatus" />
                </div>

                <!-- Priority Distribution -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Distribuição por Prioridade
                    </h3>
                    <PriorityChart :data="complaintsByPriority" />
                </div>
            </div>

            <!-- Trend Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Tendência de Reclamações (Últimos 6 Meses)
                </h3>
                <TrendChart :data="trendData" />
            </div>

            <!-- Category Analysis -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Top 10 Categorias de Reclamações
                </h3>
                <CategoryChart :data="complaintsByCategory" />
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
        </div>
    </Layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '@/Layouts/ManagerLayout.vue'
import StatCard from '@/Components/PCA/StatCard.vue'
import StatusChart from '@/Components/PCA/StatusChart.vue'
import PriorityChart from '@/Components/PCA/PriorityChart.vue'
import TrendChart from '@/Components/PCA/TrendChart.vue'
import CategoryChart from '@/Components/PCA/CategoryChart.vue'
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
    complaintsByCategory: {
        type: Array,
        default: () => []
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
    // Implement export functionality
    alert('Funcionalidade de exportação será implementada')
}
</script>
