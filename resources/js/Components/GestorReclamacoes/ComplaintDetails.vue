<template>
    <!-- Overlay de fundo -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <!-- Container principal -->
        <div class="bg-white rounded shadow-2xl border border-gray-200 w-full max-w-6xl max-h-[90vh] flex flex-col">
            <!-- Header fixo -->
            <div
                class="flex items-center justify-between p-8 border-b border-gray-200 bg-white rounded sticky top-0 z-10">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">Detalhes da Reclamação</h3>
                    <div class="flex items-center gap-3 mt-2">
                        <div class="flex items-center gap-1.5 text-sm text-gray-500">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span>Ativo</span>
                        </div>
                        <span class="text-gray-300">•</span>
                        <span class="text-sm text-gray-500">Última atualização: {{ getLastUpdate() }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="handlePrint"
                        class="p-2 text-gray-400 hover:text-gray-600 transition-colors duration-200 rounded-lg hover:bg-gray-50"
                        :disabled="!complaint">
                        <PrinterIcon class="w-5 h-5" />
                    </button>
                    <button @click="handleClose"
                        class="p-2 text-gray-400 hover:text-gray-600 transition-colors duration-200 rounded-lg hover:bg-gray-50">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Conteúdo com scroll -->
            <div class="flex-1 overflow-y-auto p-8">
                <div v-if="complaint" class="space-y-8">
                    <!-- Complaint Header -->
                    <div class="bg-gradient-to-r from-orange-50 to-amber-50 rounded p-6 border border-orange-100">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <h4 class="text-xl font-bold text-gray-900">{{ complaint.title }}</h4>
                                    <span
                                        class="px-3 py-1 bg-white rounded text-xs font-medium border border-orange-200 text-orange-700 shadow-sm">
                                        {{ getTypeText(complaint.type) }}
                                    </span>
                                </div>

                                <p class="text-gray-700 text-lg leading-relaxed mb-4">{{ complaint.description }}</p>

                                <div class="flex flex-wrap items-center gap-4 text-sm">
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <div
                                            class="w-8 h-8 bg-brand rounded flex items-center justify-center text-white text-xs font-bold">
                                            {{ getUserInitials(complaint.user?.name || 'Utente') }}
                                        </div>
                                        <span class="font-medium">{{ complaint.user?.name || 'Utente' }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-gray-500">
                                        <HashtagIcon class="w-4 h-4" />
                                        <span class="font-mono">#{{ complaint.id }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-gray-500">
                                        <CalendarIcon class="w-4 h-4" />
                                        <span>{{ formatDate(complaint.created_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-gray-50 rounded p-4 text-center border border-gray-100">
                            <div class="text-2xl font-bold text-gray-900 mb-1">{{ complaint.activities?.length || 0 }}
                            </div>
                            <div class="text-xs text-gray-500 font-medium">Atividades</div>
                        </div>
                        <div class="bg-gray-50 rounded p-4 text-center border border-gray-100">
                            <div class="text-2xl font-bold text-gray-900 mb-1">{{ complaint.attachments?.length || 0 }}
                            </div>
                            <div class="text-xs text-gray-500 font-medium">Anexos</div>
                        </div>
                        <div class="bg-gray-50 rounded p-4 text-center border border-gray-100">
                            <div class="text-2xl font-bold text-gray-900 mb-1">{{ getDaysOpen() }}</div>
                            <div class="text-xs text-gray-500 font-medium">Dias em aberto</div>
                        </div>
                        <div class="bg-gray-50 rounded p-4 text-center border border-gray-100">
                            <div class="text-2xl font-bold text-gray-900 mb-1">{{ complaint.priority || 'N/A' }}</div>
                            <div class="text-xs text-gray-500 font-medium">Prioridade</div>
                        </div>
                    </div>

                    <!-- Main Content Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Left Column -->
                        <div class="lg:col-span-2 space-y-8">
                            <!-- Actions -->
                            <div class="bg-white rounded border border-gray-200 p-6 shadow-sm">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-2 h-6 bg-gradient-to-b from-orange-500 to-amber-500 rounded-full">
                                    </div>
                                    <h4 class="text-lg font-bold text-gray-900">Ações Rápidas</h4>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <button @click="openPriorityModal"
                                        class="flex items-center gap-3 p-4 bg-brand text-white rounded font-semibold transition-all duration-200 hover:shadow-lg hover:scale-105 transform">
                                        <FlagIcon class="w-5 h-5" />
                                        <span>Definir Prioridade</span>
                                    </button>
                                    <button @click="openReassignModal"
                                        class="flex items-center gap-3 p-4 bg-white text-gray-700 rounded font-semibold border-2 border-gray-200 transition-all duration-200 hover:border-brand hover:shadow-md">
                                        <UserGroupIcon class="w-5 h-5" />
                                        <span>Reatribuir Técnico</span>
                                    </button>
                                    <button @click="sendToDirector"
                                        class="flex items-center gap-3 p-4 bg-white text-gray-700 rounded font-semibold border-2 border-gray-200 transition-all duration-200 hover:border-brand hover:shadow-md">
                                        <PaperAirplaneIcon class="w-5 h-5" />
                                        <span>Enviar ao Director</span>
                                    </button>
                                    <button @click="markComplete"
                                        class="flex items-center gap-3 p-4 bg-green-500 text-white rounded font-semibold transition-all duration-200 hover:shadow-lg hover:scale-105 transform">
                                        <CheckIcon class="w-5 h-5" />
                                        <span>Marcar Concluído</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Activity Log -->
                            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-2 h-6 bg-gradient-to-b from-blue-500 to-cyan-500 rounded-full"></div>
                                    <h4 class="text-lg font-bold text-gray-900">Histórico de Atividades</h4>
                                </div>
                                <div class="space-y-4 max-h-80 overflow-y-auto pr-2 -mr-2">
                                    <div v-for="(activity, index) in complaint.activities" :key="activity.id"
                                        class="flex gap-4 group hover:bg-gray-50 p-3 rounded-xl transition-colors duration-200">
                                        <div class="flex flex-col items-center">
                                            <div class="w-3 h-3 bg-blue-500 rounded-full mt-2"></div>
                                            <div v-if="index !== complaint.activities.length - 1"
                                                class="w-0.5 h-full bg-gray-200 mt-1"></div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-semibold text-gray-900 text-sm">{{
                                                    activity.description
                                                }}</span>
                                                <span class="text-xs text-gray-400">{{
                                                    formatRelativeTime(activity.created_at)
                                                }}</span>
                                            </div>
                                            <div class="text-xs text-gray-500 flex items-center gap-1">
                                                <UserIcon class="w-3 h-3" />
                                                Por: {{ activity.user?.name || 'Sistema' }}
                                            </div>
                                            <div class="text-xs text-gray-400 mt-1">
                                                {{ formatDate(activity.created_at) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="!complaint.activities?.length" class="text-center py-8 text-gray-400">
                                        <ClockIcon class="w-12 h-12 mx-auto mb-3 opacity-50" />
                                        <p class="text-sm">Nenhuma atividade registada</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-8">
                            <!-- Complaint Details -->
                            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-2 h-6 bg-gradient-to-b from-purple-500 to-pink-500 rounded-full">
                                    </div>
                                    <h4 class="text-lg font-bold text-gray-900">Informações</h4>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                        <span class="text-sm font-medium text-gray-600">Status</span>
                                        <span
                                            class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold">
                                            {{ getStatusText(complaint.status) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                        <span class="text-sm font-medium text-gray-600">Categoria</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ complaint.category
                                        }}</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                        <span class="text-sm font-medium text-gray-600">Tipo</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ getTypeText(complaint.type)
                                            }}</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                        <span class="text-sm font-medium text-gray-600">Técnico</span>
                                        <span class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                            {{ complaint.technician?.name || 'Não atribuído' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                        <span class="text-sm font-medium text-gray-600">Prioridade</span>
                                        <span :class="[
                                            'px-3 py-1 rounded-full text-xs font-bold',
                                            complaint.priority === 'high' ? 'bg-red-100 text-red-700' :
                                                complaint.priority === 'medium' ? 'bg-yellow-100 text-yellow-700' :
                                                    'bg-green-100 text-green-700'
                                        ]">
                                            {{ complaint.priority || 'Não definida' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Attachments -->
                            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-2 h-6 bg-gradient-to-b from-green-500 to-emerald-500 rounded-full">
                                    </div>
                                    <h4 class="text-lg font-bold text-gray-900">Anexos</h4>
                                </div>
                                <div class="space-y-3">
                                    <div v-for="attachment in complaint.attachments" :key="attachment.id"
                                        class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200 hover:border-orange-300 transition-all duration-200 cursor-pointer group">
                                        <div
                                            class="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-500 rounded-lg flex items-center justify-center text-white">
                                            <DocumentIcon class="w-5 h-5" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-900 truncate">{{ attachment.name
                                            }}</div>
                                            <div class="text-xs text-gray-500">{{ attachment.size || 'N/A' }}</div>
                                        </div>
                                        <ArrowDownTrayIcon
                                            class="w-5 h-5 text-gray-400 group-hover:text-orange-500 transition-colors" />
                                    </div>
                                    <div v-if="!complaint.attachments?.length" class="text-center py-6 text-gray-400">
                                        <PaperClipIcon class="w-12 h-12 mx-auto mb-3 opacity-50" />
                                        <p class="text-sm">Sem anexos</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-16">
                    <div
                        class="w-24 h-24 bg-gradient-to-br from-orange-100 to-amber-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <InformationCircleIcon class="w-12 h-12 text-orange-300" />
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Nenhuma reclamação selecionada</h4>
                    <p class="text-gray-500 max-w-sm mx-auto">Selecione uma reclamação da lista para visualizar os
                        detalhes
                        completos.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <PriorityModal v-if="showPriorityModal" :complaint="complaint" @close="showPriorityModal = false"
        @update="updatePriority" />

    <ReassignModal v-if="showReassignModal" :complaint="complaint" :technicians="technicians"
        @close="showReassignModal = false" @update="reassignTechnician" />

    <!-- Conteúdo para impressão (oculto na tela) -->
    <div v-if="complaint" ref="printContent" class="hidden">
        <div class="print-container p-8">
            <div class="header mb-8 border-b-2 border-gray-300 pb-4">
                <h1 class="text-2xl font-bold text-gray-900">Detalhes da Reclamação</h1>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-sm text-gray-600">Emitido em: {{ formatDate(new Date().toISOString()) }}</span>
                    <span class="text-sm text-gray-600">ID: #{{ complaint.id }}</span>
                </div>
            </div>

            <div class="complaint-info mb-6">
                <h2 class="text-xl font-bold mb-2">{{ complaint.title }}</h2>
                <p class="text-gray-700 mb-4">{{ complaint.description }}</p>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><strong>Utente:</strong> {{ complaint.user?.name || 'Utente' }}</div>
                    <div><strong>Categoria:</strong> {{ complaint.category }}</div>
                    <div><strong>Tipo:</strong> {{ getTypeText(complaint.type) }}</div>
                    <div><strong>Status:</strong> {{ getStatusText(complaint.status) }}</div>
                    <div><strong>Prioridade:</strong> {{ complaint.priority || 'Não definida' }}</div>
                    <div><strong>Técnico:</strong> {{ complaint.technician?.name || 'Não atribuído' }}</div>
                    <div><strong>Data de criação:</strong> {{ formatDate(complaint.created_at) }}</div>
                    <div><strong>Dias em aberto:</strong> {{ getDaysOpen() }}</div>
                </div>
            </div>

            <div class="activities-section mb-6" v-if="complaint.activities?.length">
                <h3 class="text-lg font-bold mb-3 border-b border-gray-200 pb-2">Histórico de Atividades</h3>
                <div class="space-y-3">
                    <div v-for="activity in complaint.activities" :key="activity.id" class="text-sm">
                        <div class="font-semibold">{{ activity.description }}</div>
                        <div class="text-gray-600">
                            Por: {{ activity.user?.name || 'Sistema' }} em {{ formatDate(activity.created_at) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="attachments-section" v-if="complaint.attachments?.length">
                <h3 class="text-lg font-bold mb-3 border-b border-gray-200 pb-2">Anexos</h3>
                <div class="text-sm">
                    <div v-for="attachment in complaint.attachments" :key="attachment.id" class="mb-1">
                        • {{ attachment.name }} {{ attachment.size ? `(${attachment.size})` : '' }}
                    </div>
                </div>
            </div>

            <div class="footer mt-8 pt-4 border-t-2 border-gray-300 text-center text-sm text-gray-500">
                Documento gerado automaticamente pelo Sistema de Gestão de Reclamações
            </div>
        </div>
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
    InformationCircleIcon,
    CalendarIcon,
    PrinterIcon,
    ArrowDownTrayIcon,
    XMarkIcon
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
    'mark-complete',
    'close'
])

const showPriorityModal = ref(false)
const showReassignModal = ref(false)
const printContent = ref(null)

// Função para fechar o modal
const handleClose = () => {
    emit('close')
}

// Função para imprimir
const handlePrint = () => {
    if (!props.complaint) return

    const printWindow = window.open('', '_blank')
    const printDocument = printWindow.document

    printDocument.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Reclamação #${props.complaint.id}</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 20px; 
                    color: #333;
                    line-height: 1.4;
                }
                .print-container { max-width: 800px; margin: 0 auto; }
                .header { border-bottom: 2px solid #ccc; padding-bottom: 10px; margin-bottom: 20px; }
                .footer { border-top: 2px solid #ccc; padding-top: 10px; margin-top: 30px; }
                h1 { color: #1f2937; margin: 0; }
                h2 { color: #374151; margin-bottom: 10px; }
                h3 { color: #4b5563; margin-bottom: 10px; }
                .complaint-info p { margin-bottom: 15px; }
                .grid { display: grid; gap: 10px; }
                .grid-cols-2 { grid-template-columns: 1fr 1fr; }
                .activities-section .space-y-3 > * + * { margin-top: 12px; }
                .text-sm { font-size: 14px; }
                .text-lg { font-size: 18px; }
                .text-xl { font-size: 20px; }
                .text-2xl { font-size: 24px; }
                .font-bold { font-weight: bold; }
                .mb-2 { margin-bottom: 8px; }
                .mb-3 { margin-bottom: 12px; }
                .mb-4 { margin-bottom: 16px; }
                .mb-6 { margin-bottom: 24px; }
                .mb-8 { margin-bottom: 32px; }
                .mt-2 { margin-top: 8px; }
                .mt-8 { margin-top: 32px; }
                .pb-2 { padding-bottom: 8px; }
                .pb-4 { padding-bottom: 16px; }
                .pt-4 { padding-top: 16px; }
                .border-b { border-bottom: 1px solid #e5e7eb; }
                .border-t-2 { border-top: 2px solid #d1d5db; }
                .border-b-2 { border-bottom: 2px solid #d1d5db; }
                .text-gray-600 { color: #6b7280; }
                .text-gray-700 { color: #374151; }
                .text-gray-900 { color: #1f2937; }
                .text-center { text-align: center; }
                .space-y-3 > * + * { margin-top: 12px; }
                @media print {
                    body { margin: 0; }
                    .print-container { max-width: 100%; }
                }
            </style>
        </head>
        <body>
            ${printContent.value.innerHTML}
        </body>
        </html>
    `)

    printDocument.close()

    // Aguarda o conteúdo carregar antes de imprimir
    printWindow.onload = () => {
        printWindow.print()
        // Fecha a janela após um tempo se o usuário não imprimir
        setTimeout(() => {
            printWindow.close()
        }, 1000)
    }
}

// Função para compartilhar (exemplo básico)
const handleShare = async () => {
    if (!props.complaint) return

    const shareData = {
        title: `Reclamação #${props.complaint.id}`,
        text: `${props.complaint.title} - ${props.complaint.description.substring(0, 100)}...`,
        url: window.location.href
    }

    try {
        if (navigator.share) {
            await navigator.share(shareData)
        } else {
            // Fallback para copiar para a área de transferência
            await navigator.clipboard.writeText(`Reclamação #${props.complaint.id}: ${props.complaint.title}`)
            alert('Link copiado para a área de transferência!')
        }
    } catch (error) {
        console.log('Erro ao compartilhar:', error)
    }
}

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

const formatRelativeTime = (dateString) => {
    const date = new Date(dateString)
    const now = new Date()
    const diffInHours = Math.floor((now - date) / (1000 * 60 * 60))

    if (diffInHours < 1) return 'Agora mesmo'
    if (diffInHours < 24) return `Há ${diffInHours}h`
    if (diffInHours < 168) return `Há ${Math.floor(diffInHours / 24)}d`
    return `Há ${Math.floor(diffInHours / 168)}sem`
}

const getLastUpdate = () => {
    if (!props.complaint?.activities?.length) return 'Nunca'
    const lastActivity = props.complaint.activities[props.complaint.activities.length - 1]
    return formatRelativeTime(lastActivity.created_at)
}

const getDaysOpen = () => {
    if (!props.complaint?.created_at) return 0
    const created = new Date(props.complaint.created_at)
    const now = new Date()
    return Math.floor((now - created) / (1000 * 60 * 60 * 24))
}

const getUserInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
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

<style scoped>
.hidden {
    display: none;
}

@media print {
    .print-container {
        max-width: 100%;
        margin: 0;
        padding: 20px;
    }
}
</style>