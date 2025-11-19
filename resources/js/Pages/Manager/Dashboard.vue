<template>
    <Layout :stats="stats">
        <div class="space-y-6">
            <!-- KPIs Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <KpiCard title="Total em Aberto" :value="stats.pending_complaints || 0"
                    description="Reclamações não resolvidas" icon="ExclamationTriangleIcon" trend="up" />

                <KpiCard title="Em Progresso" :value="stats.in_progress || 0" description="Com técnicos atribuídos"
                    icon="ClockIcon" trend="stable" />

                <KpiCard title="Urgentes (Alta)" :value="stats.high_priority || 0"
                    description="Encaminhar ao Director se crítico" icon="ExclamationCircleIcon" trend="up" />

                <KpiCard title="Solicitações de Conclusão" :value="stats.pending_completion_requests || 0"
                    description="Aguardando aprovação" icon="CheckCircleIcon" trend="down" />
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Complaints -->
                <div class="lg:col-span-2">
                    <ComplaintsList :complaints="complaints.data" :filters="filters" @update:filters="updateFilters"
                        @select-complaint="selectComplaint" />
                </div>

                <!-- Complaint Details & Actions -->
                <div class="lg:col-span-1">
                    <ComplaintDetails :complaint="selectedComplaint" :technicians="technicians"
                        @update-priority="updatePriority" @reassign-technician="reassignTechnician"
                        @send-to-director="sendToDirector" @mark-complete="markComplete" />
                </div>
            </div>

            <!-- Charts & Indicators -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <ChartsSection :stats="stats" />
                <QuickActions :complaint="selectedComplaint" />
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import Layout from '@/Layouts/ManagerLayout.vue'
import KpiCard from '@/Components/GestorReclamacoes/KpiCard.vue'
import ComplaintsList from '@/Components/GestorReclamacoes/ComplaintsList.vue'
import ComplaintDetails from '@/Components/GestorReclamacoes/ComplaintDetails.vue'
import ChartsSection from '@/Components/GestorReclamacoes/ChartsSection.vue'
import QuickActions from '@/Components/GestorReclamacoes/QuickActions.vue'

// Props do backend
const props = defineProps({
    complaints: {
        type: Object,
        default: () => ({ data: [] })
    },
    stats: {
        type: Object,
        default: () => ({})
    },
    technicians: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

// Estado local
const selectedComplaint = ref(null)
const localFilters = ref({ ...props.filters })

// Watcher para filtros
watch(localFilters, (newFilters) => {
    router.reload({
        data: newFilters,
        preserveState: true,
        replace: true
    })
})

// Handlers
const selectComplaint = (complaint) => {
    selectedComplaint.value = complaint
}

const updateFilters = (newFilters) => {
    localFilters.value = { ...localFilters.value, ...newFilters }
}

const updatePriority = async ({ complaintId, priority }) => {
    try {
        await router.patch(route('complaints.update-priority', complaintId), {
            priority
        }, {
            preserveScroll: true,
            preserveState: true
        })
    } catch (error) {
        console.error('Error updating priority:', error)
    }
}

const reassignTechnician = async ({ complaintId, technicianId }) => {
    try {
        await router.patch(route('complaints.reassign', complaintId), {
            technician_id: technicianId
        }, {
            preserveScroll: true,
            preserveState: true
        })
    } catch (error) {
        console.error('Error reassigning technician:', error)
    }
}

const sendToDirector = async (complaintId) => {
    try {
        await router.post(route('complaints.escalate', complaintId), {}, {
            preserveScroll: true,
            preserveState: true
        })
    } catch (error) {
        console.error('Error escalating to director:', error)
    }
}

const markComplete = async (complaintId) => {
    try {
        await router.patch(route('complaints.complete', complaintId), {}, {
            preserveScroll: true,
            preserveState: true
        })
    } catch (error) {
        console.error('Error marking as complete:', error)
    }
}
</script>