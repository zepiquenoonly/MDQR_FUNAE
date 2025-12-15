<template>
    <Layout :stats="safeStats" :role="'technician'">
        <div class="space-y-3 sm:space-y-6 px-3 sm:px-0">
            <!-- Welcome Message - Transparent -->
            <div class="mb-4">
                <div class="py-4">
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-2 text-gray-900 dark:text-white">
                        Bem-vindo(a), {{ props.user?.name }}!
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 text-sm sm:text-base lg:text-lg">
                        Painel do Técnico - Acompanhe as reclamações atribuídas, registe intervenções e solicite a conclusão ao gestor
                    </p>
                </div>
            </div>

            <!-- KPIs Grid - Melhorado para mobile -->
            <div class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4 lg:gap-6">
                <KpiCard
                    title="Atribuídas"
                    :value="safeStats.total_assigned || 0"
                    description="Total em acompanhamento"
                    icon="UserGroupIcon"
                    trend="stable"
                />
                <KpiCard
                    title="Em Andamento"
                    :value="safeStats.in_progress || 0"
                    description="Processos ativos"
                    icon="ClockIcon"
                    trend="up"
                />
                <KpiCard
                    title="Pendente Aprovação"
                    :value="safeStats.pending_completion_requests || 0"
                    description="Aguardando gestor"
                    icon="CheckCircleIcon"
                    trend="down"
                />
                <KpiCard
                    title="Resolvidas no Mês"
                    :value="safeStats.resolved_month || 0"
                    description="Reconhecidas neste mês"
                    icon="CheckIcon"
                    trend="stable"
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
            status: '',
            priority: '',
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
