<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-t-2xl">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-white mb-2">Status da Reclamação</h2>
                        <p class="text-blue-100 font-mono text-sm">{{ grievance.reference_number }}</p>
                    </div>
                    <button @click="$emit('close')" class="text-white hover:text-gray-200 transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-6">
                <!-- Status Badge -->
                <div class="flex justify-center">
                    <div :class="[
                        'px-6 py-3 rounded-full font-semibold text-lg flex items-center space-x-2',
                        getStatusClass(grievance.status)
                    ]">
                        <component :is="getStatusIcon(grievance.status)" class="w-6 h-6" />
                        <span>{{ getStatusText(grievance.status) }}</span>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                        <ClockIcon class="w-5 h-5 mr-2 text-gray-500" />
                        Cronologia
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">Submetida</p>
                                <p class="text-xs text-gray-500">{{ formatDate(grievance.submitted_at) }}</p>
                            </div>
                        </div>
                        <div v-if="grievance.updated_at !== grievance.submitted_at" class="flex items-start space-x-3">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">Última Atualização</p>
                                <p class="text-xs text-gray-500">{{ formatDate(grievance.updated_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Details -->
                <div class="space-y-4">
                    <div class="flex items-start space-x-3 pb-4 border-b border-gray-200">
                        <FolderIcon class="w-5 h-5 text-gray-400 mt-0.5" />
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 mb-1">Categoria</p>
                            <p class="font-semibold text-gray-900">{{ grievance.category }}</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3 pb-4 border-b border-gray-200">
                        <CalendarIcon class="w-5 h-5 text-gray-400 mt-0.5" />
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 mb-1">Data de Submissão</p>
                            <p class="font-semibold text-gray-900">{{ formatDate(grievance.submitted_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <InformationCircleIcon class="w-5 h-5 text-blue-500 mt-0.5" />
                        <div class="flex-1">
                            <p class="text-sm text-blue-800">
                                <strong>Importante:</strong> Guarde este número de referência para acompanhamento futuro.
                                {{ getStatusMessage(grievance.status) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-200 p-6 bg-gray-50 rounded-b-2xl">
                <button @click="$emit('close')"
                    class="w-full px-6 py-3 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors font-medium">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import {
    XMarkIcon,
    ClockIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
    ArrowPathIcon,
    FolderIcon,
    CalendarIcon,
    InformationCircleIcon
} from '@heroicons/vue/24/outline'

defineProps({
    grievance: {
        type: Object,
        required: true
    }
})

defineEmits(['close'])

const getStatusClass = (status) => {
    const classes = {
        'submitted': 'bg-blue-100 text-blue-800',
        'under_review': 'bg-yellow-100 text-yellow-800',
        'in_progress': 'bg-orange-100 text-orange-800',
        'resolved': 'bg-green-100 text-green-800',
        'closed': 'bg-gray-100 text-gray-800',
        'rejected': 'bg-red-100 text-red-800'
    }
    return classes[status] || classes['submitted']
}

const getStatusIcon = (status) => {
    const icons = {
        'submitted': ClockIcon,
        'under_review': ArrowPathIcon,
        'in_progress': ArrowPathIcon,
        'resolved': CheckCircleIcon,
        'closed': CheckCircleIcon,
        'rejected': ExclamationCircleIcon
    }
    return icons[status] || ClockIcon
}

const getStatusText = (status) => {
    const texts = {
        'submitted': 'Submetida',
        'under_review': 'Em Análise',
        'in_progress': 'Em Andamento',
        'resolved': 'Resolvida',
        'closed': 'Encerrada',
        'rejected': 'Rejeitada'
    }
    return texts[status] || 'Desconhecido'
}

const getStatusMessage = (status) => {
    const messages = {
        'submitted': 'Sua reclamação foi recebida e está aguardando análise.',
        'under_review': 'Nossa equipe está analisando sua reclamação.',
        'in_progress': 'Estamos trabalhando ativamente na resolução da sua reclamação.',
        'resolved': 'Sua reclamação foi resolvida com sucesso.',
        'closed': 'Este caso foi encerrado.',
        'rejected': 'Sua reclamação foi rejeitada. Entre em contato para mais informações.'
    }
    return messages[status] || ''
}

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleString('pt-MZ', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>
