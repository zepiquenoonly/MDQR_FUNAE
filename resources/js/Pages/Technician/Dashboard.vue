<template>
    <Layout :stats="safeStats" :role="'technician'">
        <div class="space-y-3 sm:space-y-6 px-3 sm:px-0">
            <!-- Header com bem-vindo -->
            <div>
                <header class="flex flex-col gap-1 sm:gap-2">
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Bem-vindo(a), {{ props.user?.name }}</p>
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-dark-text-primary">Painel do Técnico</h1>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Acompanhe as reclamações atribuídas, registe intervenções e solicite a conclusão ao gestor.</p>
                </header>
            </div>

            <!-- KPIs Grid - Melhorado para mobile -->
            <div class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4 lg:gap-6">
                <KpiCard
                    title="Atribuídas"
                    :value="safeStats.total_assigned || 0"
                    description="Total em acompanhamento"
                    icon="UserGroupIcon"
                    trend="stable"
                    color="blue"
                />
                <KpiCard
                    title="Em Andamento"
                    :value="safeStats.in_progress || 0"
                    description="Processos ativos"
                    icon="ClockIcon"
                    trend="up"
                    color="orange"
                />
                <KpiCard
                    title="Pendente Aprovação"
                    :value="safeStats.pending_completion_requests || 0"
                    description="Aguardando gestor"
                    icon="CheckCircleIcon"
                    trend="down"
                    color="yellow"
                />
                <KpiCard
                    title="Resolvidas no Mês"
                    :value="safeStats.resolved_month || 0"
                    description="Reconhecidas neste mês"
                    icon="CheckIcon"
                    trend="stable"
                    color="green"
                />
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-3 sm:gap-6">
                <!-- Reclamações Lista -->
                <div>
                    <GrievancesList
                        :grievances="safeGrievances.data || []"
                        :filters="safeFilters"
                        @update:filters="updateFilters"
                        @select-grievance="navigateToGrievance"
                    />
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import Layout from '@/Layouts/UnifiedLayout.vue'
import KpiCard from '@/Components/GestorReclamacoes/KpiCard.vue'
import GrievancesList from '@/Components/Technician/GrievancesList.vue'
import { useCommonSafeProps } from '@/Composables/usePageProps'
import { buildRoute } from '@/utils/routes'

const props = defineProps({
    user: {
        type: Object,
        default: () => ({}),
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
    grievances: {
        type: Object,
        default: () => ({ data: [] }),
    },
    filters: {
        type: Object,
        default: () => ({
            status: null,
            priority: null,
            search: '',
        }),
    },
    statusOptions: {
        type: Array,
        default: () => [],
    },
    priorityOptions: {
        type: Array,
        default: () => [],
    },
})

// Usar composable para safe props - elimina repetição
const { safeStats, safeGrievances, safeFilters } = useCommonSafeProps(props)

// Handlers
const navigateToGrievance = (grievance) => {
    const url = buildRoute('technician.grievances.show', { grievance: grievance.id })
    router.visit(url, {
        method: 'get',
    })
}

const updateFilters = (newFilters) => {
    const url = buildRoute('technician.dashboard')
    router.get(url, newFilters, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}
</script>
