<template>
    <Layout>
        <div class="space-y-4 sm:space-y-6">
            <!-- Breadcrumb & Header -->
            <div class="flex flex-col gap-3 sm:gap-4">
                <Link href="/technician/dashboard" class="text-sm text-brand hover:text-orange-700 font-medium flex items-center gap-1">
                    ← Voltar ao Painel
                </Link>
                <div class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                        <div class="flex-1">
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-dark-text-primary">
                                {{ grievance.reference_number }}
                            </h1>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ grievance.title }}
                            </p>
                        </div>
                        <StatusBadge :status="grievance.status" :label="grievance.status_label" size="lg" />
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span :class="priorityBadgeClass(grievance.priority)" class="rounded-full px-3 py-1 text-sm font-semibold">
                            {{ priorityLabel(grievance.priority) }}
                        </span>
                        <span class="rounded-full bg-blue-100 dark:bg-blue-900/20 px-3 py-1 text-sm text-blue-700 dark:text-blue-300 font-medium">
                            {{ grievance.category }}
                        </span>
                        <span v-if="grievance.district" class="rounded-full bg-gray-100 dark:bg-gray-700 px-3 py-1 text-sm text-gray-700 dark:text-gray-300">
                            📍 {{ grievance.district }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Main Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                <!-- Left Column - 2/3 -->
                <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                    <!-- Description Card -->
                    <div class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2">
                            <span class="flex items-center justify-center h-5 w-5 rounded-full bg-purple-100 dark:bg-purple-900/20 text-xs">📝</span>
                            Descrição da Reclamação
                        </h2>
                        <div class="prose prose-sm dark:prose-invert max-w-none bg-gray-50 dark:bg-dark-accent rounded-lg p-4"
                            v-html="grievance.description" />
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="text-sm">
                                <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">Submetida</p>
                                <p class="text-gray-900 dark:text-dark-text-primary font-semibold">{{ formatDate(grievance.submitted_at) }}</p>
                            </div>
                            <div class="text-sm">
                                <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">Atualizada</p>
                                <p class="text-gray-900 dark:text-dark-text-primary font-semibold">{{ formatRelative(grievance.updated_at) }}</p>
                            </div>
                            <div class="text-sm">
                                <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">Dias Aberto</p>
                                <p class="text-gray-900 dark:text-dark-text-primary font-semibold">{{ getDaysOpen() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline de Atualizações -->
                    <div class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2">
                            <span class="flex items-center justify-center h-5 w-5 rounded-full bg-blue-100 dark:bg-blue-900/20 text-xs flex-shrink-0">🕐</span>
                            Histórico de Atualizações ({{ grievance.updates?.length || 0 }})
                        </h2>
                        <div v-if="grievance.updates?.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <p class="text-sm">Nenhuma atualização registada ainda</p>
                        </div>
                        <div v-else class="space-y-3 max-h-96 overflow-y-auto pr-2">
                            <div v-for="(update, idx) in grievance.updates" :key="update.id"
                                class="border-l-4 border-brand pl-4 pb-4"
                                :class="{ 'border-gray-300 dark:border-gray-600': idx !== 0 }">
                                <p class="font-semibold text-gray-900 dark:text-dark-text-primary text-sm">
                                    {{ update.description }}
                                </p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                    {{ formatDate(update.created_at) }} • {{ update.user?.name || 'Sistema' }}
                                </p>
                                <p v-if="update.comment" class="text-sm text-gray-700 dark:text-gray-300 mt-2 italic bg-gray-50 dark:bg-dark-accent p-2 rounded">
                                    "{{ update.comment }}"
                                </p>
                                <div v-if="update.attachments?.length" class="mt-2 space-y-1">
                                    <p class="text-xs text-gray-500 dark:text-gray-500 font-medium">Anexos:</p>
                                    <div class="flex flex-wrap gap-1">
                                        <a v-for="attach in update.attachments" :key="attach.id"
                                            :href="attach.url" target="_blank"
                                            class="text-xs bg-orange-100 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300 px-2 py-1 rounded hover:bg-orange-200 dark:hover:bg-orange-900/40">
                                            📎 {{ attach.original_filename }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Anexos -->
                    <div class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2">
                            <span class="flex items-center justify-center h-5 w-5 rounded-full bg-green-100 dark:bg-green-900/20 text-xs flex-shrink-0">📎</span>
                            Anexos ({{ grievance.attachments?.length || 0 }})
                        </h2>
                        <div v-if="grievance.attachments?.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <p class="text-sm">Sem anexos no momento</p>
                        </div>
                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <a v-for="attach in grievance.attachments" :key="attach.id"
                                :href="attach.url" target="_blank"
                                class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-dark-accent border border-gray-200 dark:border-gray-600 hover:border-brand dark:hover:border-orange-500 transition-all group">
                                <div class="text-3xl">📄</div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-dark-text-primary truncate group-hover:text-brand">
                                        {{ attach.original_filename }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ formatFileSize(attach.size) }}
                                    </p>
                                </div>
                                <span class="text-lg group-hover:text-brand">⬇️</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right Column - 1/3 -->
                <div class="space-y-4 sm:space-y-6">
                    <!-- Quick Status -->
                    <div class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4">Status</h2>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Estado</span>
                                <StatusBadge :status="grievance.status" :label="grievance.status_label" size="sm" />
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Prioridade</span>
                                <span :class="priorityBadgeClass(grievance.priority)" class="px-2 py-1 text-xs font-semibold rounded">
                                    {{ priorityLabel(grievance.priority) }}
                                </span>
                            </div>
                            <div v-if="grievance.is_pending_approval" class="rounded-lg border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/10 p-3 text-sm text-orange-900 dark:text-orange-300">
                                ⏳ Aguardando aprovação do Gestor
                            </div>
                        </div>
                    </div>

                    <!-- Utente Info -->
                    <div class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4">Utente</h2>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-gray-600 dark:text-gray-400 uppercase font-medium">Nome</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary">
                                    {{ grievance.contact_name || 'Anónimo' }}
                                </p>
                            </div>
                            <div v-if="grievance.contact_email">
                                <p class="text-xs text-gray-600 dark:text-gray-400 uppercase font-medium">Email</p>
                                <a :href="`mailto:${grievance.contact_email}`"
                                    class="text-sm text-brand hover:text-orange-700 font-medium">
                                    {{ grievance.contact_email }}
                                </a>
                            </div>
                            <div v-if="grievance.contact_phone">
                                <p class="text-xs text-gray-600 dark:text-gray-400 uppercase font-medium">Telefone</p>
                                <a :href="`tel:${grievance.contact_phone}`"
                                    class="text-sm text-brand hover:text-orange-700 font-medium">
                                    {{ grievance.contact_phone }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4">Ações</h2>
                        <div class="space-y-2">
                            <button v-if="grievance.can_start"
                                type="button"
                                @click="startWork"
                                class="w-full bg-brand text-white px-4 py-3 rounded-lg font-semibold hover:bg-orange-700 transition-all shadow-sm text-sm">
                                🚀 Iniciar Trabalho
                            </button>
                            <p v-else class="text-sm text-gray-600 dark:text-gray-400 text-center py-2">
                                ✓ Trabalho iniciado
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Forms Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <!-- Registar Atualização -->
                <div class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2">
                        <span class="flex items-center justify-center h-5 w-5 rounded-full bg-blue-100 dark:bg-blue-900/20 text-xs flex-shrink-0">✍️</span>
                        Registar Atualização
                    </h2>
                    <form @submit.prevent="submitUpdate" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Descrição Curta *
                            </label>
                            <input v-model="updateForm.description"
                                type="text"
                                placeholder="Ex: Inspeção inicial realizada"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary placeholder:text-gray-500 focus:border-brand focus:ring-2 focus:ring-brand/20" />
                            <p v-if="updateForm.errors.description" class="text-xs text-red-600 mt-1">
                                {{ updateForm.errors.description }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Comentário Detalhado *
                            </label>
                            <textarea v-model="updateForm.comment"
                                rows="4"
                                placeholder="Explique o que foi feito, descoberto e os próximos passos..."
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary placeholder:text-gray-500 focus:border-brand focus:ring-2 focus:ring-brand/20"></textarea>
                            <p v-if="updateForm.errors.comment" class="text-xs text-red-600 mt-1">
                                {{ updateForm.errors.comment }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Anexar Evidências (Fotos, PDFs, etc)
                            </label>
                            <input ref="updateFilesInput"
                                type="file"
                                multiple
                                @change="handleUpdateFiles"
                                class="w-full px-4 py-2 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 focus:border-brand focus:ring-2 focus:ring-brand/20 cursor-pointer" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                📁 Máx 10 arquivos, 10MB cada
                            </p>
                            <p v-if="updateForm.errors.attachments" class="text-xs text-red-600 mt-1">
                                {{ updateForm.errors.attachments }}
                            </p>
                            <div v-if="updateForm.attachments.length > 0" class="mt-2 space-y-1">
                                <p class="text-xs font-medium text-gray-700 dark:text-gray-300">Selecionados:</p>
                                <div class="space-y-1">
                                    <p v-for="(file, idx) in updateForm.attachments" :key="idx"
                                        class="text-xs bg-green-50 dark:bg-green-900/10 text-green-700 dark:text-green-400 px-2 py-1 rounded">
                                        ✓ {{ file.name }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="updateForm.is_public"
                                type="checkbox"
                                class="rounded border-gray-300 dark:border-gray-600 dark:bg-dark-accent" />
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Visível ao utente
                            </span>
                        </label>

                        <button type="submit"
                            :disabled="isProcessing"
                            class="w-full bg-blue-600 text-white px-4 py-3 rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50 transition-all text-sm">
                            {{ isProcessing ? '⏳ Registando...' : '✓ Registar Atualização' }}
                        </button>
                    </form>
                </div>

                <!-- Solicitar Conclusão -->
                <div class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2">
                        <span class="flex items-center justify-center h-5 w-5 rounded-full bg-green-100 dark:bg-green-900/20 text-xs flex-shrink-0">✈️</span>
                        Solicitar Conclusão
                    </h2>
                    <form @submit.prevent="requestCompletion" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Resumo da Resolução *
                            </label>
                            <textarea v-model="completionForm.resolution_summary"
                                rows="4"
                                placeholder="Descreva as ações finais realizadas, resultados e motivo da conclusão..."
                                :disabled="!grievance.can_request_completion"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary placeholder:text-gray-500 focus:border-brand focus:ring-2 focus:ring-brand/20 disabled:bg-gray-100 dark:disabled:bg-gray-700"></textarea>
                            <p v-if="completionForm.errors.resolution_summary" class="text-xs text-red-600 mt-1">
                                {{ completionForm.errors.resolution_summary }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Anexos Finais (Comprovantes, Relatórios, etc)
                            </label>
                            <input ref="completionFilesInput"
                                type="file"
                                multiple
                                :disabled="!grievance.can_request_completion"
                                @change="handleCompletionFiles"
                                class="w-full px-4 py-2 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 focus:border-brand focus:ring-2 focus:ring-brand/20 cursor-pointer disabled:bg-gray-100 dark:disabled:bg-gray-700" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                📁 Máx 10 arquivos, 10MB cada
                            </p>
                            <p v-if="completionForm.errors.attachments" class="text-xs text-red-600 mt-1">
                                {{ completionForm.errors.attachments }}
                            </p>
                            <div v-if="completionForm.attachments.length > 0" class="mt-2 space-y-1">
                                <p class="text-xs font-medium text-gray-700 dark:text-gray-300">Selecionados:</p>
                                <div class="space-y-1">
                                    <p v-for="(file, idx) in completionForm.attachments" :key="idx"
                                        class="text-xs bg-green-50 dark:bg-green-900/10 text-green-700 dark:text-green-400 px-2 py-1 rounded">
                                        ✓ {{ file.name }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="completionForm.notify_user"
                                type="checkbox"
                                :disabled="!grievance.can_request_completion"
                                class="rounded border-gray-300 dark:border-gray-600 dark:bg-dark-accent disabled:opacity-50" />
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Notificar utente sobre a conclusão
                            </span>
                        </label>

                        <div v-if="!grievance.can_request_completion" class="rounded-lg border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/10 p-3 text-sm text-orange-900 dark:text-orange-300">
                            ℹ️ Complete o trabalho registando atualizações antes de solicitar conclusão
                        </div>

                        <button type="submit"
                            :disabled="!grievance.can_request_completion || isProcessing"
                            class="w-full bg-green-600 text-white px-4 py-3 rounded-lg font-semibold hover:bg-green-700 disabled:opacity-50 transition-all text-sm">
                            {{ isProcessing ? '⏳ Enviando...' : '✓ Solicitar Conclusão' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import Layout from '@/Layouts/Layout.vue'
import StatusBadge from '@/Components/Grievance/StatusBadge.vue'

const props = defineProps({
    grievance: {
        type: Object,
        required: true
    }
})

const isProcessing = ref(false)
const updateFilesInput = ref(null)
const completionFilesInput = ref(null)

const updateForm = useForm({
    description: '',
    comment: '',
    is_public: true,
    attachments: [],
})

const completionForm = useForm({
    resolution_summary: '',
    notify_user: true,
    attachments: [],
})

const handleUpdateFiles = (event) => {
    updateForm.attachments = Array.from(event.target.files || [])
}

const handleCompletionFiles = (event) => {
    completionForm.attachments = Array.from(event.target.files || [])
}

const startWork = async () => {
    isProcessing.value = true
    try {
        await router.patch(route('technician.grievances.start', props.grievance.id), {}, {
            preserveScroll: true,
        })
    } finally {
        isProcessing.value = false
    }
}

const submitUpdate = async () => {
    isProcessing.value = true
    try {
        await updateForm.post(route('technician.grievances.updates.store', props.grievance.id), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                updateForm.reset()
                if (updateFilesInput.value) updateFilesInput.value.value = null
            },
            onError: () => {
                console.error('Erro ao registar atualização')
            }
        })
    } finally {
        isProcessing.value = false
    }
}

const requestCompletion = async () => {
    isProcessing.value = true
    try {
        await completionForm.post(
            route('technician.grievances.request-completion', props.grievance.id),
            {
                preserveScroll: true,
                forceFormData: true,
                onSuccess: () => {
                    completionForm.reset()
                    if (completionFilesInput.value) completionFilesInput.value.value = null
                },
                onError: () => {
                    console.error('Erro ao solicitar conclusão')
                }
            }
        )
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
        hour: '2-digit',
        minute: '2-digit'
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

const getDaysOpen = () => {
    if (!props.grievance?.created_at) return 0
    const created = new Date(props.grievance.created_at)
    const now = new Date()
    return Math.floor((now - created) / (1000 * 60 * 60 * 24))
}

const formatFileSize = (bytes) => {
    if (!bytes) return 'N/A'
    const k = 1024
    const sizes = ['B', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}
</script>
