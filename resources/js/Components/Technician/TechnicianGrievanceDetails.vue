<template>
    <div v-if="grievance" class="space-y-8">
        <!-- Header - Glassmorphism Hero -->
        <div class="relative overflow-hidden rounded-3xl">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-600 via-orange-600 to-amber-700"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent"></div>

            <!-- Floating Elements -->
            <div class="absolute top-4 left-6 w-16 h-16 bg-white/10 rounded-full blur-xl animate-pulse"></div>
            <div class="absolute bottom-4 right-6 w-20 h-20 bg-orange-300/20 rounded-full blur-2xl animate-pulse delay-1000"></div>

            <div class="relative p-6 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 bg-white/10 backdrop-blur-xl rounded-2xl flex items-center justify-center border border-white/20 shadow-2xl shadow-black/10">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-2xl sm:text-3xl font-bold text-white drop-shadow-2xl">
                                    {{ grievance.reference_number }}
                                </h2>
                                <div class="w-20 h-1 bg-gradient-to-r from-orange-300 to-amber-300 rounded-full mt-2"></div>
                            </div>
                        </div>
                        <p class="text-white/90 text-base sm:text-lg mb-4 drop-shadow-lg">
                            {{ grievance.title }}
                        </p>
                        <div class="flex flex-wrap gap-3 text-sm text-white/80">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Submetida: {{ formatDate(grievance.submitted_at) }}
                            </span>
                            <span class="text-white/60">•</span>
                            <span>Atualizada: {{ formatRelative(grievance.updated_at) }}</span>
                        </div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3 border border-white/20 shadow-lg">
                        <StatusBadge :status="grievance.status" :label="grievance.status_label" />
                    </div>
                </div>
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

        <!-- Pending Approval Notice - Glassmorphism -->
        <div v-if="grievance.is_pending_approval" class="bg-gradient-to-r from-orange-50 to-amber-50 backdrop-blur-xl rounded-3xl shadow-2xl p-6 border border-orange-200/50 shadow-lg shadow-orange-500/5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-orange-500/20 to-amber-600/20 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/30 shadow-lg shadow-orange-500/10">
                    <ClockIcon class="w-6 h-6 text-orange-600" />
                </div>
                <div class="flex-1">
                    <h4 class="text-lg font-semibold text-orange-900 mb-1">Aguardando Aprovação</h4>
                    <p class="text-orange-800 text-sm leading-relaxed">
                        Esta reclamação aguarda validação do Gestor. Será notificada quando aprovada.
                    </p>
                </div>
            </div>
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

        <!-- Description Section - Glassmorphism -->
        <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-6 border border-white/30 shadow-lg shadow-purple-500/5">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500/20 to-pink-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-800">Descrição</h4>
            </div>
            <div class="bg-white/50 backdrop-blur-sm rounded-2xl p-6 border border-white/40">
                <div class="text-sm text-gray-700 leading-relaxed prose prose-sm max-w-none" v-html="grievance.description" />
            </div>
        </div>

        <!-- Timeline de Atualizações -->
        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary">Histórico de Atualizações</h4>
            <UpdatesTimeline :updates="grievance.updates ?? []" />
        </div>

        <!-- Attachments Section - Glassmorphism -->
        <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-6 border border-white/30 shadow-lg shadow-green-500/5">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500/20 to-emerald-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-800">Anexos</h4>
            </div>
            <div class="bg-white/50 backdrop-blur-sm rounded-2xl p-6 border border-white/40">
                <AttachmentsGallery :attachments="grievance.attachments ?? []" title="" />
            </div>
        </div>

        <!-- Quick Actions - Glassmorphism -->
        <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-6 border border-white/30 shadow-lg shadow-orange-500/5">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-orange-500/20 to-red-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                    <BoltIcon class="w-5 h-5 text-orange-600" />
                </div>
                <h4 class="text-lg font-semibold text-gray-800">Ações Rápidas</h4>
            </div>

            <div class="bg-white/50 backdrop-blur-sm rounded-2xl p-6 border border-white/40">
                <button
                    v-if="grievance.can_start"
                    type="button"
                    class="w-full bg-gradient-to-r from-orange-500 to-red-600 text-white font-semibold py-4 px-6 rounded-2xl shadow-lg hover:shadow-xl hover:shadow-orange-500/25 transition-all duration-300 hover:-translate-y-0.5 flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0"
                    :disabled="isProcessing"
                    @click="handleStartWork">
                    <RocketLaunchIcon class="h-5 w-5" />
                    <span>Iniciar Trabalho</span>
                </button>
                <div v-else class="flex items-center gap-3 p-4 bg-emerald-50/80 backdrop-blur-sm rounded-2xl border border-emerald-200/50">
                    <CheckIcon class="h-6 w-6 text-emerald-600" />
                    <p class="text-sm text-emerald-800 font-medium">
                        Trabalho já foi iniciado nesta reclamação.
                    </p>
                </div>
            </div>
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

        <!-- Request Completion - Glassmorphism -->
        <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-6 border border-white/30 shadow-lg shadow-green-500/5">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500/20 to-emerald-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                    <CheckBadgeIcon class="w-5 h-5 text-green-600" />
                </div>
                <h4 class="text-lg font-semibold text-gray-800">Solicitar Conclusão ao Gestor</h4>
            </div>

            <form class="bg-white/50 backdrop-blur-sm rounded-2xl p-6 border border-white/40 space-y-6" @submit.prevent="handleRequestCompletion">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Resumo da resolução</label>
                    <textarea
                        v-model="completionData.resolution_summary"
                        rows="4"
                        :disabled="!grievance.can_request_completion"
                        placeholder="Descreva as ações finais realizadas e resultados obtidos..."
                        class="w-full px-4 py-3 bg-white/80 backdrop-blur-sm border border-white/50 rounded-xl text-sm focus:border-green-400 focus:ring-2 focus:ring-green-200/50 transition-all duration-200 resize-none disabled:bg-gray-100 disabled:cursor-not-allowed"
                    ></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Anexos finais (opcional)</label>
                    <input
                        ref="completionFilesInput"
                        type="file"
                        multiple
                        :disabled="!grievance.can_request_completion"
                        class="w-full px-4 py-3 bg-white/80 backdrop-blur-sm border border-dashed border-green-300/50 rounded-xl text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 transition-all duration-200 disabled:bg-gray-100 disabled:cursor-not-allowed"
                    />
                </div>

                <div v-if="grievance.can_request_completion" class="flex items-center gap-3 p-4 bg-green-50/50 backdrop-blur-sm rounded-xl border border-green-200/50">
                    <input
                        v-model="completionData.notify_user"
                        type="checkbox"
                        class="w-4 h-4 text-green-600 bg-white border-gray-300 rounded focus:ring-green-500 focus:ring-2"
                    />
                    <label class="text-sm text-green-800 font-medium">Notificar utente sobre a conclusão</label>
                </div>

                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold py-4 px-6 rounded-2xl shadow-lg hover:shadow-xl hover:shadow-green-500/25 transition-all duration-300 hover:-translate-y-0.5 flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0"
                    :disabled="!grievance.can_request_completion || isProcessing">
                    <span v-if="isProcessing">
                        <ClockIcon class="h-5 w-5 animate-spin" />
                        Enviando...
                    </span>
                    <span v-else>
                        <CheckIcon class="h-5 w-5" />
                        Solicitar Conclusão
                    </span>
                </button>

                <div v-if="!grievance.can_request_completion" class="flex items-start gap-3 p-4 bg-amber-50/80 backdrop-blur-sm rounded-xl border border-amber-200/50">
                    <InformationCircleIcon class="h-5 w-5 text-amber-600 flex-shrink-0 mt-0.5" />
                    <p class="text-sm text-amber-800 font-medium leading-relaxed">
                        Complete o trabalho antes de solicitar conclusão.
                    </p>
                </div>
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
