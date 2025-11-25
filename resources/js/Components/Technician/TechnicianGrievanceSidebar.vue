<template>
    <div v-if="grievance" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-secondary shadow-sm p-5 space-y-5">
        <!-- Header -->
        <div class="space-y-2 pb-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary">
                {{ grievance.reference_number }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                {{ grievance.title }}
            </p>
        </div>

        <!-- Status Badge -->
        <StatusBadge :status="grievance.status" :label="grievance.status_label" />

        <!-- Info Grid -->
        <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg">
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Categoria</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary">{{ grievance.category }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg">
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Prioridade</span>
                <span :class="priorityBadgeClass(grievance.priority)" class="px-2 py-1 text-xs font-semibold rounded">
                    {{ priorityLabel(grievance.priority) }}
                </span>
            </div>
            <div v-if="grievance.district" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg">
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Localização</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary">{{ grievance.district }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg">
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Submetida</span>
                <span class="text-sm text-gray-700 dark:text-gray-300">{{ formatDate(grievance.submitted_at) }}</span>
            </div>
        </div>

        <!-- Aviso -->
        <div v-if="grievance.is_pending_approval" class="rounded-lg border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/10 p-3 text-xs text-orange-900 dark:text-orange-300">
            ⏳ Pendente de aprovação do Gestor
        </div>

        <!-- Quick Action -->
        <button
            v-if="grievance.can_start"
            type="button"
            class="w-full rounded-lg bg-brand px-4 py-3 text-sm font-medium text-white shadow hover:bg-brand/90 transition-all disabled:opacity-70"
            :disabled="isProcessing"
            @click="handleStartWork">
            {{ isProcessing ? 'Iniciando...' : 'Iniciar Trabalho' }}
        </button>
        <p v-else class="text-xs text-gray-500 dark:text-gray-400 text-center">
            ✓ Trabalho iniciado
        </p>

        <!-- Utente Info -->
        <div class="space-y-2 rounded-lg border border-gray-200 dark:border-gray-700 p-3">
            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Contacto</p>
            <p class="text-sm font-medium text-gray-900 dark:text-dark-text-primary">
                {{ grievance.contact_name || 'Anónimo' }}
            </p>
            <div class="space-y-1 text-xs text-gray-600 dark:text-gray-400">
                <p v-if="grievance.contact_email">📧 {{ grievance.contact_email }}</p>
                <p v-if="grievance.contact_phone">📱 {{ grievance.contact_phone }}</p>
            </div>
        </div>

        <!-- Updates Count -->
        <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/10 rounded-lg">
            <span class="text-xs font-medium text-blue-700 dark:text-blue-400">Atualizações</span>
            <span class="text-lg font-bold text-blue-700 dark:text-blue-400">
                {{ grievance.updates?.length || 0 }}
            </span>
        </div>

        <!-- Attachments Count -->
        <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/10 rounded-lg">
            <span class="text-xs font-medium text-green-700 dark:text-green-400">Anexos</span>
            <span class="text-lg font-bold text-green-700 dark:text-green-400">
                {{ grievance.attachments?.length || 0 }}
            </span>
        </div>

        <!-- Completion Status -->
        <div class="text-xs text-gray-500 dark:text-gray-400 text-center p-3 bg-gray-50 dark:bg-dark-accent rounded-lg">
            <p v-if="grievance.can_request_completion">
                ✅ Pronto para solicitar conclusão
            </p>
            <p v-else>
                ⏳ Continue registando atualizações
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import StatusBadge from '@/Components/Grievance/StatusBadge.vue'

const props = defineProps({
    grievance: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['start-work', 'submit-update', 'request-completion'])

const isProcessing = ref(false)

const handleStartWork = async () => {
    isProcessing.value = true
    try {
        emit('start-work', props.grievance.id)
    } finally {
        isProcessing.value = false
    }
}

const priorityLabel = (priority) => {
    const map = {
        low: 'Baixa',
        medium: 'Média',
        high: 'Alta',
    }
    return map[priority] ?? priority ?? 'N/D'
}

const priorityBadgeClass = (priority) => {
    const map = {
        low: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400',
        medium: 'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
        high: 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400',
    }
    return map[priority] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'
}

const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('pt-PT', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    })
}
</script>
