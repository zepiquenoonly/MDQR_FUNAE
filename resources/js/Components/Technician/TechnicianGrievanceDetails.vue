<template>
    <div v-if="grievance" class="space-y-6">
        <!-- Header -->
        <div class="space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-dark-text-primary">
                    {{ grievance.reference_number }}
                </h2>
                <StatusBadge :status="grievance.status" :label="grievance.status_label" />
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ grievance.title }}
            </p>
            <div class="flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                <span>Submetida: {{ formatDate(grievance.submitted_at) }}</span>
                <span>•</span>
                <span>Atualizada: {{ formatRelative(grievance.updated_at) }}</span>
            </div>
        </div>

        <!-- Badges de Status -->
        <div class="flex flex-wrap gap-2">
            <span :class="priorityBadgeClass(grievance.priority)" class="rounded-full px-3 py-1 text-xs font-semibold">
                {{ priorityLabel(grievance.priority) }}
            </span>
            <span v-if="grievance.district" class="rounded-full bg-gray-100 dark:bg-gray-700 px-3 py-1 text-xs text-gray-600 dark:text-gray-400">
                {{ grievance.district }} / {{ grievance.province }}
            </span>
            <span class="rounded-full bg-blue-100 dark:bg-blue-900/20 px-3 py-1 text-xs text-blue-700 dark:text-blue-300">
                {{ grievance.category }}
            </span>
        </div>

        <!-- Aviso de Pendência -->
        <div v-if="grievance.is_pending_approval" class="rounded-lg border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/10 p-4 text-sm text-orange-900 dark:text-orange-300 flex items-center gap-2">
            <ClockIcon class="h-5 w-5 flex-shrink-0" />
            <span>Esta reclamação aguarda validação do Gestor. Será notificada quando aprovada.</span>
        </div>

        <!-- Informações do Utente -->
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-dark-accent p-4">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary mb-3">Contacto do Utente</h4>
            <div class="space-y-2 text-sm">
                <p class="text-gray-700 dark:text-gray-300"><strong>Nome:</strong> {{ grievance.contact_name || 'Anónimo' }}</p>
                <p v-if="grievance.contact_email" class="text-gray-700 dark:text-gray-300"><strong>Email:</strong> {{ grievance.contact_email }}</p>
                <p v-if="grievance.contact_phone" class="text-gray-700 dark:text-gray-300"><strong>Telefone:</strong> {{ grievance.contact_phone }}</p>
            </div>
        </div>

        <!-- Descrição -->
        <div class="space-y-2">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary">Descrição</h4>
            <div class="rounded-lg bg-gray-50 dark:bg-dark-accent p-4 text-sm text-gray-700 dark:text-gray-300 prose prose-sm dark:prose-invert max-w-none" v-html="grievance.description" />
        </div>

        <!-- Timeline de Atualizações -->
        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary">Histórico de Atualizações</h4>
            <UpdatesTimeline :updates="grievance.updates ?? []" />
        </div>

        <!-- Anexos -->
        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary">Anexos</h4>
            <AttachmentsGallery :attachments="grievance.attachments ?? []" />
        </div>

        <!-- Ações Rápidas -->
        <div class="space-y-3 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary flex items-center gap-2">
                <BoltIcon class="h-5 w-5 text-orange-600" />
                Ações Rápidas
            </h4>
            <button
                v-if="grievance.can_start"
                type="button"
                class="w-full rounded-lg bg-brand px-4 py-3 text-sm font-medium text-white shadow hover:bg-brand/90 disabled:cursor-not-allowed disabled:opacity-70 transition-all"
                :disabled="isProcessing"
                @click="handleStartWork">
                <RocketLaunchIcon class="h-4 w-4 inline-block mr-1" /> Iniciar Trabalho
            </button>
            <p v-else class="text-xs text-gray-500 dark:text-gray-400">
                <CheckIcon class="h-4 w-4 inline-block text-emerald-600 mr-1" /> Trabalho já foi iniciado nesta reclamação.
            </p>
        </div>

        <!-- Formulário de Atualização -->
        <div class="space-y-3 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary flex items-center gap-2">
                <ChatBubbleLeftIcon class="h-5 w-5 text-blue-600" />
                Registar Atualização
            </h4>
            <form class="space-y-3" @submit.prevent="handleSubmitUpdate">
                <div>
                    <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Descrição da ação</label>
                    <input
                        v-model="updateData.description"
                        type="text"
                        placeholder="Ex: Inspeção realizada"
                        class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-accent px-3 py-2 text-sm text-gray-900 dark:text-dark-text-primary focus:border-brand focus:ring-1 focus:ring-brand/20"
                    />
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Comentário detalhado</label>
                    <textarea
                        v-model="updateData.comment"
                        rows="3"
                        placeholder="Detalhe o que foi feito e descoberto..."
                        class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-accent px-3 py-2 text-sm text-gray-900 dark:text-dark-text-primary focus:border-brand focus:ring-1 focus:ring-brand/20"
                    ></textarea>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Anexar evidências</label>
                    <input
                        ref="updateFilesInput"
                        type="file"
                        multiple
                        class="mt-1 block w-full rounded-lg border border-dashed border-gray-300 dark:border-gray-600 px-3 py-2 text-xs text-gray-500 file:mr-3 file:rounded file:border-0 file:bg-orange-100 dark:file:bg-orange-900/20 file:px-2 file:py-1 file:text-xs file:font-medium file:text-orange-700 dark:file:text-orange-400"
                    />
                </div>
                <label class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                    <input
                        v-model="updateData.is_public"
                        type="checkbox"
                        class="rounded border-gray-300 dark:border-gray-600 dark:bg-dark-accent"
                    />
                    Visível ao utente
                </label>
                <button
                    type="submit"
                    class="w-full rounded-lg border border-brand bg-white dark:bg-dark-accent px-4 py-2 text-sm font-medium text-brand transition hover:bg-brand/5 dark:hover:bg-brand/10 disabled:cursor-not-allowed disabled:opacity-70"
                    :disabled="isProcessing">
                    <span v-if="isProcessing" class="flex items-center gap-2"><ClockIcon class="h-4 w-4 animate-spin" /> Registando...</span>
                    <span v-else class="flex items-center gap-2"><CheckIcon class="h-4 w-4" /> Registar Atualização</span>
                </button>
            </form>
        </div>

        <!-- Solicitar Conclusão -->
        <div class="space-y-3 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary flex items-center gap-2">
                <CheckBadgeIcon class="h-5 w-5 text-green-600" />
                Solicitar Conclusão ao Gestor
            </h4>
            <form class="space-y-3" @submit.prevent="handleRequestCompletion">
                <div>
                    <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Resumo da resolução</label>
                    <textarea
                        v-model="completionData.resolution_summary"
                        rows="3"
                        :disabled="!grievance.can_request_completion"
                        placeholder="Descreva as ações finais realizadas e resultados obtidos..."
                        class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-accent px-3 py-2 text-sm text-gray-900 dark:text-dark-text-primary focus:border-brand focus:ring-1 focus:ring-brand/20 disabled:bg-gray-50 dark:disabled:bg-gray-700"
                    ></textarea>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Anexos finais (opcional)</label>
                    <input
                        ref="completionFilesInput"
                        type="file"
                        multiple
                        :disabled="!grievance.can_request_completion"
                        class="mt-1 block w-full rounded-lg border border-dashed border-gray-300 dark:border-gray-600 px-3 py-2 text-xs text-gray-500 file:mr-3 file:rounded file:border-0 file:bg-green-100 dark:file:bg-green-900/20 file:px-2 file:py-1 file:text-xs file:font-medium file:text-green-700 dark:file:text-green-400 disabled:bg-gray-50 dark:disabled:bg-gray-700"
                    />
                </div>
                <label v-if="grievance.can_request_completion" class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                    <input
                        v-model="completionData.notify_user"
                        type="checkbox"
                        class="rounded border-gray-300 dark:border-gray-600 dark:bg-dark-accent"
                    />
                    Notificar utente sobre a conclusão
                </label>
                <button
                    type="submit"
                    class="w-full rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-70"
                    :disabled="!grievance.can_request_completion || isProcessing">
                    <span v-if="isProcessing" class="flex items-center gap-2"><ClockIcon class="h-4 w-4 animate-spin" /> Enviando...</span>
                    <span v-else class="flex items-center gap-2"><CheckIcon class="h-4 w-4" /> Solicitar Conclusão</span>
                </button>
                <p v-if="!grievance.can_request_completion" class="text-xs text-gray-500 dark:text-gray-400 flex items-start gap-2">
                    <InformationCircleIcon class="h-4 w-4 flex-shrink-0 mt-0.5" />
                    <span>Complete o trabalho antes de solicitar conclusão.</span>
                </p>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import StatusBadge from '@/Components/Grievance/StatusBadge.vue'
import UpdatesTimeline from '@/Components/Grievance/UpdatesTimeline.vue'
import AttachmentsGallery from '@/Components/Grievance/AttachmentsGallery.vue'
import { BoltIcon, RocketLaunchIcon, CheckIcon, ClockIcon, ChatBubbleLeftIcon, CheckBadgeIcon, InformationCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    grievance: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close', 'start-work', 'submit-update', 'request-completion'])

const isProcessing = ref(false)
const updateFilesInput = ref(null)
const completionFilesInput = ref(null)

const updateData = ref({
    description: '',
    comment: '',
    is_public: true,
    attachments: []
})

const completionData = ref({
    resolution_summary: '',
    notify_user: true,
    attachments: []
})

const handleStartWork = async () => {
    isProcessing.value = true
    try {
        emit('start-work', props.grievance.id)
    } finally {
        isProcessing.value = false
    }
}

const handleSubmitUpdate = async () => {
    const formData = new FormData()
    formData.append('description', updateData.value.description)
    formData.append('comment', updateData.value.comment)
    formData.append('is_public', updateData.value.is_public)

    Array.from(updateFilesInput.value?.files || []).forEach(file => {
        formData.append('attachments[]', file)
    })

    isProcessing.value = true
    try {
        emit('submit-update', props.grievance.id, formData)
        // Limpar form
        updateData.value.description = ''
        updateData.value.comment = ''
        updateData.value.is_public = true
        if (updateFilesInput.value) updateFilesInput.value.value = null
    } finally {
        isProcessing.value = false
    }
}

const handleRequestCompletion = async () => {
    const formData = new FormData()
    formData.append('resolution_summary', completionData.value.resolution_summary)
    formData.append('notify_user', completionData.value.notify_user)

    Array.from(completionFilesInput.value?.files || []).forEach(file => {
        formData.append('attachments[]', file)
    })

    isProcessing.value = true
    try {
        emit('request-completion', props.grievance.id, formData)
        // Limpar form
        completionData.value.resolution_summary = ''
        completionData.value.notify_user = true
        if (completionFilesInput.value) completionFilesInput.value.value = null
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

const formatRelative = (dateString) => {
    if (!dateString) return 'N/A'
    const date = new Date(dateString)
    const now = new Date()
    const diff = now - date
    const hours = Math.floor(diff / (1000 * 60 * 60))
    if (hours < 1) return 'Agora'
    if (hours < 24) return `${hours}h atrás`
    const days = Math.floor(hours / 24)
    if (days < 7) return `${days}d atrás`
    return date.toLocaleDateString('pt-PT', { month: 'short', day: '2-digit' })
}
</script>
