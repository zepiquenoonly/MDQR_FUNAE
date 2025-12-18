<template>
    <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-4xl h-[80vh] flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-blue-500 to-blue-600">
                <h2 class="flex-1 text-2xl font-bold text-center text-white">
                    Minhas Submissões
                </h2>
                <button @click="$emit('close')" class="ml-4 text-white transition-colors hover:text-gray-200">
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6">
                <div v-if="!submissions || submissions.length === 0" class="text-center py-12">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-lg font-semibold text-gray-800 mb-2">Nenhuma submissão encontrada</p>
                    <p class="text-gray-600">Suas submissões aparecerão aqui quando você fizer uma reclamação.</p>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="submission in submissions" :key="submission.id" class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ submission.title || 'Reclamação' }}</h3>
                                <p class="text-sm text-gray-600">Referência: {{ submission.reference_number || submission.id }}</p>
                            </div>
                            <span :class="getStatusBadgeClass(submission.status)" class="px-3 py-1 text-sm font-semibold rounded-full">
                                {{ submission.status_label || submission.status }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                            <div>
                                <p class="text-sm text-gray-600">Categoria</p>
                                <p class="font-medium">{{ submission.category || 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Data de Submissão</p>
                                <p class="font-medium">{{ formatDate(submission.created_at || submission.submitted_at) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Prioridade</p>
                                <p class="font-medium">{{ submission.priority_label || submission.priority || 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Última Atualização</p>
                                <p class="font-medium">{{ formatDate(submission.updated_at) }}</p>
                            </div>
                        </div>

                        <div v-if="submission.description" class="mb-3">
                            <p class="text-sm text-gray-600 mb-1">Descrição</p>
                            <p class="text-sm bg-gray-50 p-3 rounded-lg">{{ submission.description }}</p>
                        </div>

                        <div class="flex justify-end">
                            <button @click="viewDetails(submission)"
                                class="px-4 py-2 bg-gradient-to-r from-primary-500 to-orange-600 text-white font-semibold rounded-lg hover:from-primary-600 hover:to-orange-700 transition-all duration-300 hover:scale-105">
                                Ver Detalhes Completos
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineEmits } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    submissions: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['close'])

const viewDetails = (submission) => {
    // Aqui podemos implementar navegação para detalhes completos
    // Por enquanto, apenas fecha o modal
    emit('close')
    // TODO: Implementar navegação para página de detalhes
}

const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('pt-PT', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
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
</script>