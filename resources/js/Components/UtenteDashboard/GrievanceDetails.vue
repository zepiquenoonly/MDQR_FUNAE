<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-[1200px] h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-primary-500 to-primary-600">
                <h2 class="flex-1 text-2xl font-bold text-center text-white">
                    Detalhes da Submissão #{{ grievance.reference_number || grievance.id }}
                </h2>
                <button @click="$emit('close')" class="ml-4 text-white transition-colors hover:text-gray-200">
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto">
                <div v-if="loading" class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 border-4 border-primary-500 rounded-full animate-spin border-t-transparent"></div>
                        <p class="text-gray-600">Carregando detalhes...</p>
                    </div>
                </div>

                <div v-else-if="error" class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <ExclamationCircleIcon class="w-16 h-16 mx-auto mb-4 text-red-500" />
                        <p class="mb-4 text-lg font-semibold text-gray-800">Erro ao carregar detalhes</p>
                        <p class="mb-4 text-gray-600">{{ error }}</p>
                        <button @click="loadDetails" class="px-4 py-2 text-white bg-primary-500 rounded-lg hover:bg-primary-600">
                            Tentar novamente
                        </button>
                    </div>
                </div>

                <div v-else class="p-6 space-y-6">
                    <!-- Status e Informações Básicas -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Status Card -->
                        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-800">Status Actual</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Estado:</span>
                                    <span :class="details ? getStatusBadgeClass(details.status) : ''" class="px-3 py-1 text-sm font-semibold rounded-full">
                                        {{ details ? details.status_label : '' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Prioridade:</span>
                                    <span :class="details ? getPriorityBadgeClass(details.priority) : ''" class="px-3 py-1 text-sm font-semibold rounded-full">
                                        {{ details ? details.priority_label : '' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Tipo:</span>
                                    <span class="text-sm text-gray-800">{{ details ? (details.type_label || details.category) : '' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Data de Submissão:</span>
                                    <span class="text-sm text-gray-800">{{ details ? formatDate(details.submitted_at || details.created_at) : '' }}</span>
                                </div>
                                <div v-if="details && details.assigned_at" class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Atribuído em:</span>
                                    <span class="text-sm text-gray-800">{{ formatDate(details.assigned_at) }}</span>
                                </div>
                                <div v-if="details && details.resolved_at" class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Resolvido em:</span>
                                    <span class="text-sm text-gray-800">{{ formatDate(details.resolved_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Informações da Reclamação -->
                        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-800">Informações do Requerente</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Nome:</span>
                                    <span class="text-sm text-gray-800">{{ details ? (details.contact_name || 'N/A') : 'N/A' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Email:</span>
                                    <span class="text-sm text-gray-800">{{ details ? (details.contact_email || 'N/A') : 'N/A' }}</span>
                                </div>
                                <div v-if="details && details.contact_phone" class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Telefone:</span>
                                    <span class="text-sm text-gray-800">{{ details.contact_phone }}</span>
                                </div>
                                <div v-if="details && details.gender" class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Género:</span>
                                    <span class="text-sm text-gray-800">{{ getGenderLabel(details.gender) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Tipo de Submissão:</span>
                                    <span :class="details && details.is_anonymous ? 'text-orange-600' : 'text-green-600'" class="text-sm font-medium">
                                        {{ details && details.is_anonymous ? 'Anónima' : 'Identificada' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Categoria:</span>
                                    <span class="text-sm text-gray-800">{{ details ? details.category : '' }}</span>
                                </div>
                                <div v-if="details && details.subcategory" class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Subcategoria:</span>
                                    <span class="text-sm text-gray-800">{{ details.subcategory }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Descrição -->
                    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <h3 class="mb-3 text-lg font-semibold text-gray-800">Descrição</h3>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ details ? details.description : '' }}</p>
                    </div>

                    <!-- Projeto Relacionado -->
                    <div v-if="details && details.project" class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Projeto Relacionado</h3>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Nome do Projeto:</span>
                            <span class="text-sm text-primary-600 font-medium">{{ details.project.name }}</span>
                        </div>
                    </div>

                    <!-- Responsável pela Atribuição -->
                    <div v-if="details && details.assigned_to" class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Responsável pela Atribuição</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Técnico Responsável:</span>
                                <span class="text-sm text-gray-800">{{ details.assigned_to.name }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Email:</span>
                                <span class="text-sm text-gray-800">{{ details.assigned_to.email }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Localização -->
                    <div v-if="details && (details.province || details.district || details.locality)" class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <h3 class="mb-3 text-lg font-semibold text-gray-800">Localização</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-if="details.province" class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Província:</span>
                                <span class="text-sm text-gray-800">{{ details.province }}</span>
                            </div>
                            <div v-if="details.district" class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Distrito:</span>
                                <span class="text-sm text-gray-800">{{ details.district }}</span>
                            </div>
                            <div v-if="details.municipal_district" class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Posto Administrativo:</span>
                                <span class="text-sm text-gray-800">{{ details.municipal_district }}</span>
                            </div>
                            <div v-if="details.locality" class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Localidade:</span>
                                <span class="text-sm text-gray-800">{{ details.locality }}</span>
                            </div>
                        </div>
                        <div v-if="details.location_details" class="mt-4">
                            <span class="text-sm text-gray-600">Detalhes da Localização:</span>
                            <p class="text-sm text-gray-800 mt-1">{{ details.location_details }}</p>
                        </div>
                    </div>

                    <!-- Anexos -->
                    <div v-if="details && details.attachments && details.attachments.length > 0" class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Anexos ({{ details.attachments.length }})</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="attachment in details.attachments" :key="attachment.id" class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start space-x-3">
                                    <!-- Preview para imagens -->
                                    <div v-if="isImage(attachment.mime_type)" class="flex-shrink-0">
                                        <img :src="`/storage/${attachment.file_path}`" :alt="attachment.original_filename"
                                             class="w-16 h-16 object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-80 transition-opacity"
                                             @click="openImageModal(attachment)" />
                                    </div>
                                    <!-- Ícone para outros tipos de arquivo -->
                                    <div v-else class="flex-shrink-0">
                                        <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <PaperClipIcon class="w-8 h-8 text-gray-500" />
                                        </div>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ attachment.original_filename }}</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ formatFileSize(attachment.file_size) }} • {{ getFileTypeLabel(attachment.mime_type) }}
                                        </p>
                                        <p v-if="attachment.uploaded_at" class="text-xs text-gray-400 mt-1">
                                            Enviado em {{ formatDate(attachment.uploaded_at) }}
                                        </p>
                                        <div class="mt-3 flex space-x-2">
                                            <a :href="`/storage/${attachment.file_path}`" target="_blank"
                                               class="inline-flex items-center px-3 py-1 text-xs font-medium text-primary-600 bg-primary-50 rounded-md hover:bg-primary-100 transition-colors">
                                                <ArrowDownTrayIcon class="w-3 h-3 mr-1" />
                                                Download
                                            </a>
                                            <button v-if="isImage(attachment.mime_type)" @click="openImageModal(attachment)"
                                                    class="inline-flex items-center px-3 py-1 text-xs font-medium text-green-600 bg-green-50 rounded-md hover:bg-green-100 transition-colors">
                                                <EyeIcon class="w-3 h-3 mr-1" />
                                                Visualizar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline de Atualizações -->
                    <div v-if="details.updates && details.updates.length > 0" class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">
                                Histórico de Atualizações
                            </h3>
                            <button @click="refreshUpdates" class="p-2 text-primary-500 transition-colors rounded-lg hover:bg-primary-50">
                                <ArrowPathIcon class="w-5 h-5" :class="{ 'animate-spin': refreshing }" />
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div v-for="(update, index) in details.updates" :key="update.id"
                                class="relative pl-6 border-l-2 border-gray-200">
                                <div :class="[
                                    'absolute left-0 top-0 -ml-2 w-4 h-4 rounded-full border-2 border-white',
                                    index === 0 ? 'bg-primary-500' : 'bg-gray-400'
                                ]"></div>
                                <div class="pb-4">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-sm font-semibold text-gray-800">{{ update.user?.name || 'Sistema' }}</p>
                                        <p class="text-xs text-gray-500">{{ formatDate(update.created_at) }}</p>
                                    </div>
                                    <p v-if="update.description" class="text-sm text-gray-700">{{ update.description }}</p>
                                    <p v-if="update.comment" class="mt-1 text-sm italic text-gray-600">"{{ update.comment }}"</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notas de Resolução -->
                    <div v-if="details && details.resolution_notes && (details.status === 'resolved' || details.status === 'closed')"
                        class="p-6 border-green-200 rounded-lg shadow-sm bg-green-50">
                        <h3 class="mb-3 text-lg font-semibold text-green-800">Resolução</h3>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ details.resolution_notes }}</p>
                        <div class="mt-4 space-y-2">
                            <div v-if="details.resolved_by" class="flex items-center text-sm text-gray-600">
                                <CheckCircleIcon class="w-4 h-4 mr-2 text-green-600" />
                                <span>Resolvido por: <strong>{{ details.resolved_by.name }}</strong> ({{ details.resolved_by.email }})</span>
                            </div>
                            <div v-if="details.resolved_at" class="flex items-center text-sm text-gray-600">
                                <ClockIcon class="w-4 h-4 mr-2 text-green-600" />
                                <span>Data da resolução: <strong>{{ formatDate(details.resolved_at) }}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end p-6 border-t border-gray-200 bg-gray-50">
                <button @click="$emit('close')"
                    class="px-6 py-2 font-semibold text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Fechar
                </button>
            </div>
        </div>

        <!-- Modal de Visualização de Imagem -->
        <div v-if="imageModal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-75" @click="closeImageModal">
            <div class="relative max-w-4xl max-h-full">
                <img :src="`/storage/${imageModal.attachment.file_path}`" :alt="imageModal.attachment.original_filename"
                     class="max-w-full max-h-full object-contain rounded-lg" />
                <button @click="closeImageModal" class="absolute top-4 right-4 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-75 transition-colors">
                    <XMarkIcon class="w-6 h-6" />
                </button>
                <div class="absolute bottom-4 left-4 right-4 bg-black bg-opacity-50 rounded-lg p-4 text-white">
                    <p class="font-medium">{{ imageModal.attachment.original_filename }}</p>
                    <p class="text-sm opacity-75">{{ formatFileSize(imageModal.attachment.file_size) }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import {
    XMarkIcon,
    ExclamationCircleIcon,
    MapPinIcon,
    PaperClipIcon,
    ArrowDownTrayIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    EyeIcon,
    ClockIcon,
    UserIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    grievance: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close'])

const loading = ref(true)
const error = ref(null)
const details = ref(null)
const refreshing = ref(false)
const imageModal = ref({
    open: false,
    attachment: null
})

const loadDetails = async () => {
    loading.value = true
    error.value = null

    try {
        const response = await fetch(`/utente/grievances/${props.grievance.id}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })

        if (!response.ok) {
            throw new Error('Erro ao carregar detalhes da reclamação')
        }

        const data = await response.json()
        details.value = data.grievance
    } catch (err) {
        error.value = err.message
        console.error('Erro ao carregar detalhes:', err)
    } finally {
        loading.value = false
    }
}

const refreshUpdates = async () => {
    if (refreshing.value) return

    refreshing.value = true

    try {
        const response = await fetch(`/utente/grievances/${props.grievance.id}/status-updates`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })

        if (!response.ok) {
            throw new Error('Erro ao atualizar status')
        }

        const data = await response.json()
        details.value.updates = data.updates
        details.value.status = data.current_status
        details.value.status_label = data.status_label
    } catch (err) {
        console.error('Erro ao atualizar:', err)
    } finally {
        refreshing.value = false
    }
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('pt-PT', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getGenderLabel = (gender) => {
    const labels = {
        'male': 'Masculino',
        'female': 'Feminino',
        'other': 'Outro'
    }
    return labels[gender] || gender
}

const isImage = (mimeType) => {
    return mimeType && mimeType.startsWith('image/')
}

const getFileTypeLabel = (mimeType) => {
    if (!mimeType) return 'Arquivo'
    if (mimeType.startsWith('image/')) return 'Imagem'
    if (mimeType.startsWith('audio/')) return 'Áudio'
    if (mimeType.startsWith('video/')) return 'Vídeo'
    if (mimeType.includes('pdf')) return 'PDF'
    if (mimeType.includes('document') || mimeType.includes('word')) return 'Documento'
    return 'Arquivo'
}

const openImageModal = (attachment) => {
    imageModal.value = {
        open: true,
        attachment: attachment
    }
}

const closeImageModal = () => {
    imageModal.value = {
        open: false,
        attachment: null
    }
}

const getStatusBadgeClass = (status) => {
    const classes = {
        'submitted': 'bg-primary-100 text-primary-800',
        'under_review': 'bg-yellow-100 text-yellow-800',
        'assigned': 'bg-purple-100 text-purple-800',
        'in_progress': 'bg-orange-100 text-orange-800',
        'pending_approval': 'bg-indigo-100 text-indigo-800',
        'resolved': 'bg-green-100 text-green-800',
        'closed': 'bg-gray-100 text-gray-800',
        'rejected': 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPriorityBadgeClass = (priority) => {
    const classes = {
        'low': 'bg-green-100 text-green-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'high': 'bg-orange-100 text-orange-800',
        'urgent': 'bg-red-100 text-red-800'
    }
    return classes[priority] || 'bg-gray-100 text-gray-800'
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
}

onMounted(() => {
    loadDetails()
})
</script>
