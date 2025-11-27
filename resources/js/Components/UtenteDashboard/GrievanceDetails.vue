<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-[1200px] h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-blue-500 to-blue-600">
                <h2 class="flex-1 text-2xl font-bold text-center text-white">
                    Detalhes da Reclamação #{{ grievance.reference_number }}
                </h2>
                <button @click="$emit('close')" class="ml-4 text-white transition-colors hover:text-gray-200">
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto">
                <div v-if="loading" class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 border-4 border-blue-500 rounded-full animate-spin border-t-transparent"></div>
                        <p class="text-gray-600">Carregando detalhes...</p>
                    </div>
                </div>

                <div v-else-if="error" class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <ExclamationCircleIcon class="w-16 h-16 mx-auto mb-4 text-red-500" />
                        <p class="mb-4 text-lg font-semibold text-gray-800">Erro ao carregar detalhes</p>
                        <p class="mb-4 text-gray-600">{{ error }}</p>
                        <button @click="loadDetails" class="px-4 py-2 text-white bg-blue-500 rounded-lg hover:bg-blue-600">
                            Tentar novamente
                        </button>
                    </div>
                </div>

                <div v-else class="p-6 space-y-6">
                    <!-- Status e Informações Básicas -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Status Card -->
                        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-800">Status Atual</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Estado:</span>
                                    <span :class="getStatusBadgeClass(details.status)" class="px-3 py-1 text-sm font-semibold rounded-full">
                                        {{ details.status_label }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Prioridade:</span>
                                    <span :class="getPriorityBadgeClass(details.priority)" class="px-3 py-1 text-sm font-semibold rounded-full">
                                        {{ details.priority_label }}
                                    </span>
                                </div>
                                <div v-if="details.assigned_to" class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Atribuído a:</span>
                                    <span class="text-sm font-medium text-gray-800">{{ details.assigned_to.name }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Informações da Reclamação -->
                        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-800">Informações</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Categoria:</span>
                                    <span class="font-medium text-gray-800">{{ details.category }}</span>
                                </div>
                                <div v-if="details.subcategory" class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subcategoria:</span>
                                    <span class="font-medium text-gray-800">{{ details.subcategory }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Data de Submissão:</span>
                                    <span class="font-medium text-gray-800">{{ details.submitted_at }}</span>
                                </div>
                                <div v-if="details.resolved_at" class="flex justify-between text-sm">
                                    <span class="text-gray-600">Data de Resolução:</span>
                                    <span class="font-medium text-gray-800">{{ details.resolved_at }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Descrição -->
                    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <h3 class="mb-3 text-lg font-semibold text-gray-800">Descrição</h3>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ details.description }}</p>
                    </div>

                    <!-- Localização -->
                    <div v-if="details.province || details.district" class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <h3 class="mb-3 text-lg font-semibold text-gray-800">Localização</h3>
                        <div class="space-y-2">
                            <div v-if="details.province" class="flex items-center text-sm">
                                <MapPinIcon class="w-4 h-4 mr-2 text-gray-500" />
                                <span class="text-gray-600">Província:</span>
                                <span class="ml-2 font-medium text-gray-800">{{ details.province }}</span>
                            </div>
                            <div v-if="details.district" class="flex items-center text-sm">
                                <MapPinIcon class="w-4 h-4 mr-2 text-gray-500" />
                                <span class="text-gray-600">Distrito:</span>
                                <span class="ml-2 font-medium text-gray-800">{{ details.district }}</span>
                            </div>
                            <div v-if="details.location_details" class="mt-2 text-sm text-gray-700">
                                {{ details.location_details }}
                            </div>
                        </div>
                    </div>

                    <!-- Anexos -->
                    <div v-if="details.attachments && details.attachments.length > 0" class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">
                            Anexos ({{ details.attachments.length }})
                        </h3>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <a v-for="attachment in details.attachments" :key="attachment.id"
                                :href="attachment.download_url" target="_blank"
                                class="flex items-center p-3 transition-all border border-gray-200 rounded-lg hover:border-blue-500 hover:shadow-md">
                                <PaperClipIcon class="flex-shrink-0 w-5 h-5 mr-3 text-gray-400" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ attachment.name }}</p>
                                    <p class="text-xs text-gray-500">{{ formatFileSize(attachment.size) }}</p>
                                </div>
                                <ArrowDownTrayIcon class="flex-shrink-0 w-5 h-5 ml-2 text-blue-500" />
                            </a>
                        </div>
                    </div>

                    <!-- Timeline de Atualizações -->
                    <div v-if="details.updates && details.updates.length > 0" class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">
                                Histórico de Atualizações
                            </h3>
                            <button @click="refreshUpdates" class="p-2 text-blue-500 transition-colors rounded-lg hover:bg-blue-50">
                                <ArrowPathIcon class="w-5 h-5" :class="{ 'animate-spin': refreshing }" />
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div v-for="(update, index) in details.updates" :key="update.id"
                                class="relative pl-6 border-l-2 border-gray-200">
                                <div :class="[
                                    'absolute left-0 top-0 -ml-2 w-4 h-4 rounded-full border-2 border-white',
                                    index === 0 ? 'bg-blue-500' : 'bg-gray-400'
                                ]"></div>
                                <div class="pb-4">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-sm font-semibold text-gray-800">{{ update.updated_by }}</p>
                                        <p class="text-xs text-gray-500">{{ update.created_at }}</p>
                                    </div>
                                    <p v-if="update.description" class="text-sm text-gray-700">{{ update.description }}</p>
                                    <p v-if="update.comment" class="mt-1 text-sm italic text-gray-600">"{{ update.comment }}"</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notas de Resolução -->
                    <div v-if="details.resolution_notes && (details.status === 'resolved' || details.status === 'closed')"
                        class="p-6 border-green-200 rounded-lg shadow-sm bg-green-50">
                        <h3 class="mb-3 text-lg font-semibold text-green-800">Resolução</h3>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ details.resolution_notes }}</p>
                        <div v-if="details.resolved_by" class="flex items-center mt-3 text-sm text-gray-600">
                            <CheckCircleIcon class="w-4 h-4 mr-2 text-green-600" />
                            <span>Resolvido por: <strong>{{ details.resolved_by.name }}</strong></span>
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
    CheckCircleIcon
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

const getStatusBadgeClass = (status) => {
    const classes = {
        'submitted': 'bg-blue-100 text-blue-800',
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
