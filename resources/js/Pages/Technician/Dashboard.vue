<template>
    <Layout :stats="safeStats" :role="'technician'">
        <div class="space-y-6 sm:space-y-8 px-3 sm:px-0">
            <!-- Welcome Message - Glassmorphism Hero -->
            <div class="relative overflow-hidden rounded-3xl">
                <div class="absolute inset-0 bg-gradient-to-br from-primary-600 via-orange-600 to-amber-700"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent"></div>

                <!-- Floating Elements -->
                <div class="absolute top-6 left-10 w-20 h-20 bg-white/10 rounded-full blur-xl animate-pulse"></div>
                <div class="absolute bottom-6 right-10 w-32 h-32 bg-orange-300/20 rounded-full blur-2xl animate-pulse delay-1000"></div>
                <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-amber-200/15 rounded-full blur-lg animate-pulse delay-500"></div>

                <div class="relative p-8 sm:p-10">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 bg-white/10 backdrop-blur-xl rounded-2xl flex items-center justify-center border border-white/20 shadow-2xl shadow-black/10">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-2 text-white drop-shadow-2xl">
                                Bem-vindo(a), <span class="bg-gradient-to-r from-orange-200 to-amber-200 bg-clip-text text-transparent">{{ props.user?.name }}</span>!
                            </h1>
                            <div class="w-24 h-1 bg-gradient-to-r from-orange-300 to-amber-300 rounded-full mb-4"></div>
                        </div>
                    </div>
                    <p class="text-white/90 text-base sm:text-lg lg:text-xl leading-relaxed max-w-4xl drop-shadow-lg">
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
