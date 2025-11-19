<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Detalhes da Reclamação</h3>

        <div v-if="complaint" class="space-y-6">
            <!-- Complaint Info -->
            <div>
                <h4 class="font-semibold text-gray-800 text-lg">{{ complaint.title }}</h4>
                <p class="text-gray-600 text-sm mt-1 flex items-center space-x-2">
                    <UserIcon class="w-4 h-4" />
                    <span>Por: {{ complaint.user?.name || 'Utente' }}</span>
                    <HashtagIcon class="w-4 h-4 ml-2" />
                    <span>ID: #{{ complaint.id }}</span>
                </p>
                <p class="text-gray-700 mt-3">{{ complaint.description }}</p>
                <div class="flex flex-wrap gap-4 mt-4 text-sm text-gray-600">
                    <span class="flex items-center space-x-1">
                        <TagIcon class="w-4 h-4" />
                        <span><strong>Categoria:</strong> {{ complaint.category }}</span>
                    </span>
                    <span class="flex items-center space-x-1">
                        <DocumentTextIcon class="w-4 h-4" />
                        <span><strong>Tipo:</strong> {{ getTypeText(complaint.type) }}</span>
                    </span>
                    <span class="flex items-center space-x-1">
                        <FlagIcon class="w-4 h-4" />
                        <span><strong>Status:</strong> {{ getStatusText(complaint.status) }}</span>
                    </span>
                    <span class="flex items-center space-x-1">
                        <UserIcon class="w-4 h-4" />
                        <span><strong>Técnico:</strong> {{ complaint.technician?.name || 'Não atribuído' }}</span>
                    </span>
                </div>
            </div>

            <!-- Actions -->
            <div>
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center space-x-2">
                    <Cog6ToothIcon class="w-5 h-5" />
                    <span>Ações</span>
                </h4>
                <div class="flex flex-wrap gap-2">
                    <button @click="openPriorityModal"
                        class="flex items-center space-x-2 px-3 py-2 bg-orange-500 text-white text-sm rounded-lg font-medium hover:bg-orange-600 transition-all duration-200">
                        <FlagIcon class="w-4 h-4" />
                        <span>Definir Prioridade</span>
                    </button>
                    <button @click="openReassignModal"
                        class="flex items-center space-x-2 px-3 py-2 bg-white text-gray-700 text-sm rounded-lg font-medium border border-gray-300 hover:bg-gray-50 transition-all duration-200">
                        <UserGroupIcon class="w-4 h-4" />
                        <span>Reatribuir Técnico</span>
                    </button>
                    <button @click="sendToDirector"
                        class="flex items-center space-x-2 px-3 py-2 bg-white text-gray-700 text-sm rounded-lg font-medium border border-gray-300 hover:bg-gray-50 transition-all duration-200">
                        <PaperAirplaneIcon class="w-4 h-4" />
                        <span>Enviar ao Director</span>
                    </button>
                    <button @click="markComplete"
                        class="flex items-center space-x-2 px-3 py-2 bg-orange-500 text-white text-sm rounded-lg font-medium hover:bg-orange-600 transition-all duration-200">
                        <CheckIcon class="w-4 h-4" />
                        <span>Marcar como Concluído</span>
                    </button>
                </div>
            </div>

            <!-- Attachments -->
            <div>
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center space-x-2">
                    <PaperClipIcon class="w-5 h-5" />
                    <span>Anexos</span>
                </h4>
                <div class="flex flex-wrap gap-3">
                    <div v-for="attachment in complaint.attachments" :key="attachment.id"
                        class="w-20 h-14 bg-gray-100 border border-gray-300 rounded-lg flex items-center justify-center text-gray-600 text-xs hover:bg-gray-200 transition-all duration-200 cursor-pointer">
                        <DocumentIcon class="w-4 h-4 mr-1" />
                        {{ attachment.name }}
                    </div>
                    <div v-if="!complaint.attachments?.length" class="w-full text-center py-4 text-gray-500">
                        <PaperClipIcon class="w-8 h-8 mx-auto mb-2" />
                        <p>Sem anexos</p>
                    </div>
                </div>
            </div>

            <!-- Activity Log -->
            <div>
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center space-x-2">
                    <ClockIcon class="w-5 h-5" />
                    <span>Histórico de Atividades</span>
                </h4>
                <div class="border border-gray-200 rounded-lg max-h-48 overflow-y-auto">
                    <div v-for="activity in complaint.activities" :key="activity.id"
                        class="p-3 border-b border-gray-100 last:border-b-0">
                        <div class="font-semibold text-orange-500 text-sm">{{ formatDate(activity.created_at) }}</div>
                        <div class="text-gray-700 text-sm">{{ activity.description }}</div>
                        <div class="text-gray-500 text-xs mt-1">Por: {{ activity.user?.name || 'Sistema' }}</div>
                    </div>
                    <div v-if="!complaint.activities?.length" class="p-4 text-center text-gray-500">
                        <ClockIcon class="w-6 h-6 mx-auto mb-2" />
                        <p>Nenhuma atividade registada</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-8 text-gray-500">
            <InformationCircleIcon class="w-12 h-12 mx-auto mb-4 text-orange-300" />
            <p>Selecione uma reclamação para ver detalhes.</p>
        </div>

        <!-- Modals -->
        <PriorityModal v-if="showPriorityModal" :complaint="complaint" @close="showPriorityModal = false"
            @update="updatePriority" />

        <ReassignModal v-if="showReassignModal" :complaint="complaint" :technicians="technicians"
            @close="showReassignModal = false" @update="reassignTechnician" />
    </div>
</template>

<script setup>
import { ref } from 'vue'
import {
    UserIcon,
    HashtagIcon,
    TagIcon,
    DocumentTextIcon,
    FlagIcon,
    Cog6ToothIcon,
    UserGroupIcon,
    PaperAirplaneIcon,
    CheckIcon,
    PaperClipIcon,
    DocumentIcon,
    ClockIcon,
    InformationCircleIcon
} from '@heroicons/vue/24/outline'
import PriorityModal from './PriorityModal.vue'
import ReassignModal from './ReassignModal.vue'

const props = defineProps({
    complaint: Object,
    technicians: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits([
    'update-priority',
    'reassign-technician',
    'send-to-director',
    'mark-complete'
])

const showPriorityModal = ref(false)
const showReassignModal = ref(false)

const getTypeText = (type) => {
    const types = {
        complaint: 'Reclamação',
        suggestion: 'Sugestão'
    }
    return types[type] || type
}

const getStatusText = (status) => {
    const statusTexts = {
        open: 'Aberto',
        in_progress: 'Em Progresso',
        pending_completion: 'Solicitado Conclusão',
        closed: 'Concluído'
    }
    return statusTexts[status] || status
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const openPriorityModal = () => {
    if (!props.complaint) return
    showPriorityModal.value = true
}

const openReassignModal = () => {
    if (!props.complaint) return
    showReassignModal.value = true
}

const updatePriority = (priority) => {
    emit('update-priority', {
        complaintId: props.complaint.id,
        priority
    })
    showPriorityModal.value = false
}

const reassignTechnician = (technicianId) => {
    emit('reassign-technician', {
        complaintId: props.complaint.id,
        technicianId
    })
    showReassignModal.value = false
}

const sendToDirector = () => {
    if (!props.complaint) return
    emit('send-to-director', props.complaint.id)
}

const markComplete = () => {
    if (!props.complaint) return
    emit('mark-complete', props.complaint.id)
}
</script>