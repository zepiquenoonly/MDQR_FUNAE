<template>
    <div class="space-y-3 max-h-96 overflow-y-auto">
        <div v-for="activity in activities" :key="activity.id"
            class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-900 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                :class="getStatusColor(activity.status)">
                <component :is="getStatusIcon(activity.status)" class="w-5 h-5" />
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            {{ activity.reference_number }}
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                            {{ activity.description }}
                        </p>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                        {{ activity.updated_at }}
                    </span>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                        :class="getStatusBadgeClass(activity.status)">
                        {{ getStatusLabel(activity.status) }}
                    </span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                        :class="getPriorityBadgeClass(activity.priority)">
                        {{ getPriorityLabel(activity.priority) }}
                    </span>
                </div>
                <div v-if="activity.technician_name" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Técnico: <span class="font-medium">{{ activity.technician_name }}</span>
                </div>
            </div>
        </div>
        <div v-if="!activities.length" class="text-center py-8 text-gray-500 dark:text-gray-400">
            Nenhuma atividade recente
        </div>
    </div>
</template>

<script setup>
import { CheckCircleIcon, ClockIcon, ArrowPathIcon, XCircleIcon } from '@heroicons/vue/24/solid'

defineProps({
    activities: {
        type: Array,
        default: () => []
    }
})

const statusLabels = {
    'submitted': 'Submetida',
    'under_review': 'Em Análise',
    'in_progress': 'Em Progresso',
    'resolved': 'Resolvida',
    'closed': 'Concluída',
    'rejected': 'Rejeitada',
}

const priorityLabels = {
    'low': 'Baixa',
    'medium': 'Média',
    'high': 'Alta',
    'urgent': 'Urgente',
}

const getStatusLabel = (status) => statusLabels[status] || status
const getPriorityLabel = (priority) => priorityLabels[priority] || priority

const getStatusIcon = (status) => {
    const icons = {
        'submitted': ClockIcon,
        'under_review': ClockIcon,
        'in_progress': ArrowPathIcon,
        'resolved': CheckCircleIcon,
        'closed': CheckCircleIcon,
        'rejected': XCircleIcon,
    }
    return icons[status] || ClockIcon
}

const getStatusColor = (status) => {
    const colors = {
        'submitted': 'bg-blue-100 text-blue-600',
        'under_review': 'bg-yellow-100 text-yellow-600',
        'in_progress': 'bg-purple-100 text-purple-600',
        'resolved': 'bg-green-100 text-green-600',
        'closed': 'bg-green-100 text-green-600',
        'rejected': 'bg-red-100 text-red-600',
    }
    return colors[status] || 'bg-gray-100 text-gray-600'
}

const getStatusBadgeClass = (status) => {
    const classes = {
        'submitted': 'bg-blue-100 text-blue-800',
        'under_review': 'bg-yellow-100 text-yellow-800',
        'in_progress': 'bg-purple-100 text-purple-800',
        'resolved': 'bg-green-100 text-green-800',
        'closed': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPriorityBadgeClass = (priority) => {
    const classes = {
        'low': 'bg-green-50 text-green-700',
        'medium': 'bg-yellow-50 text-yellow-700',
        'high': 'bg-red-50 text-red-700',
        'urgent': 'bg-red-100 text-red-800',
    }
    return classes[priority] || 'bg-gray-50 text-gray-700'
}
</script>
