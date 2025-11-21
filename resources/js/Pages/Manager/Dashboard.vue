<template>
    <Layout :stats="stats">
        <!-- Renderizar ProjectsManager quando o panel for 'projectos' -->
        <ProjectsManager v-if="activePanel === 'projectos'" :can-edit="canEdit" />

        <!-- Renderizar TecnicoList quando o panel for 'tecnicos' -->
        <TecnicoList v-else-if="activePanel === 'tecnicos'" />

        <!-- Conteúdo normal do dashboard para outros casos -->
        <div v-else class="space-y-6">
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
                <div class="lg:col-span-3">
                    <ComplaintsList :complaints="complaints.data" :filters="filters" @update:filters="updateFilters"
                        @select-complaint="selectComplaint" @show-details="showDetailsModal" />
                </div>
            </div>

            <!-- Complaint Details Modal -->
            <div v-if="showModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                <div class="bg-white rounded-xl shadow-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                    <div
                        class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Detalhes da Reclamação</h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <XMarkIcon class="w-6 h-6" />
                        </button>
                    </div>
                    <div class="p-6">
                        <!-- Passar prop para ocultar botões no ComplaintDetails -->
                        <ComplaintDetails :complaint="selectedComplaint" :technicians="technicians" :hide-buttons="true"
                            @close="showModal = false" @update-priority="updatePriority"
                            @reassign-technician="reassignTechnician" @send-to-director="sendToDirector"
                            @mark-complete="markComplete" />
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import Layout from '@/Layouts/ManagerLayout.vue'
import KpiCard from '@/Components/GestorReclamacoes/KpiCard.vue'
import ComplaintsList from '@/Components/GestorReclamacoes/ComplaintsList.vue'
import ComplaintDetails from '@/Components/GestorReclamacoes/ComplaintDetails.vue'
import ProjectsManager from '@/Components/Dashboard/ProjectsManager.vue'
import TecnicoList from '@/Components/GestorReclamacoes/TecnicoList.vue' // ← CORRIGIDO

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
    },
    canEdit: {
        type: Boolean,
        default: false
    }
})

// Determinar o panel ativo baseado APENAS na URL
const activePanel = computed(() => {
    const urlParams = new URLSearchParams(window.location.search)
    const panelFromUrl = urlParams.get('panel')
    console.log('🔍 Panel ativo:', panelFromUrl) // Debug

    // Suportar múltiplos painéis
    if (panelFromUrl === 'projectos') return 'projectos'
    if (panelFromUrl === 'tecnicos') return 'tecnicos'
    return 'dashboard'
})

// Estado local
const selectedComplaint = ref(props.complaints.data?.[0] ?? null)
const localFilters = ref({ ...props.filters })
const showModal = ref(false)

// Watcher para filtros
watch(localFilters, (newFilters) => {
    router.reload({
        data: newFilters,
        preserveState: true,
        replace: true
    })
})

watch(
    () => props.complaints.data,
    (data) => {
        if (!data?.length) {
            selectedComplaint.value = null
            return
        }

        const currentId = selectedComplaint.value?.id
        selectedComplaint.value = data.find((item) => item.id === currentId) ?? data[0]
    }
)

// Handlers
const selectComplaint = (complaint) => {
    selectedComplaint.value = complaint
}

const showDetailsModal = (complaint) => {
    selectedComplaint.value = complaint
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
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