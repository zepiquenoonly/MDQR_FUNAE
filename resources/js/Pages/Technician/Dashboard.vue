<template>
    <Layout>
        <div class="p-6 space-y-6">
            <header class="flex flex-col gap-2">
                <p class="text-sm text-gray-500">Bem-vindo(a), {{ props.user?.name }}</p>
                <h1 class="text-2xl font-semibold text-gray-900">Painel do Técnico</h1>
                <p class="text-sm text-gray-500">Acompanhe as reclamações atribuídas, registe intervenções e solicite a conclusão ao gestor.</p>
            </header>

            <!-- Stats -->
            <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div v-for="card in statCards" :key="card.label" class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">{{ card.label }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ card.value }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ card.description }}</p>
                </div>
            </section>

            <!-- Filters -->
            <section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <form class="grid gap-4 md:grid-cols-4" @submit.prevent="applyFilters">
                    <div>
                        <label class="text-xs font-medium text-gray-500">Pesquisar</label>
                        <input
                            v-model="localFilters.search"
                            type="text"
                            placeholder="Nº referência, categoria, distrito..."
                            class="mt-1 w-full rounded-lg border-gray-200 text-sm placeholder:text-gray-400 focus:border-brand focus:ring-brand"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Status</label>
                        <select
                            v-model="localFilters.status"
                            class="mt-1 w-full rounded-lg border-gray-200 text-sm focus:border-brand focus:ring-brand"
                            @change="applyFilters"
                        >
                            <option v-for="option in props.statusOptions" :key="option.label" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Prioridade</label>
                        <select
                            v-model="localFilters.priority"
                            class="mt-1 w-full rounded-lg border-gray-200 text-sm focus:border-brand focus:ring-brand"
                            @change="applyFilters"
                        >
                            <option v-for="option in props.priorityOptions" :key="option.label" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                    <div class="flex items-end gap-3">
                        <button
                            type="submit"
                            class="flex-1 rounded-lg bg-brand px-4 py-2 text-sm font-medium text-white shadow focus:outline-none focus:ring focus:ring-brand/50"
                        >
                            Aplicar
                        </button>
                        <button
                            type="button"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:border-gray-400"
                            @click="clearFilters"
                        >
                            Limpar
                        </button>
                    </div>
                </form>
            </section>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Lista -->
                <section class="lg:col-span-2 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Reclamações atribuídas</h2>
                            <p class="text-sm text-gray-500">Selecione uma reclamação para ver detalhes e avançar no fluxo.</p>
                        </div>
                        <p class="text-sm text-gray-500">
                            Total: <span class="font-semibold text-gray-900">{{ props.grievances?.total ?? 0 }}</span>
                        </p>
                    </div>

                    <div
                        v-if="props.grievances?.data?.length === 0"
                        class="rounded-2xl border-2 border-dashed border-gray-200 bg-white p-10 text-center text-gray-500"
                    >
                        Nenhuma reclamação encontrada com os filtros aplicados.
                    </div>

                    <div v-else class="space-y-3">
                        <button
                            v-for="grievance in props.grievances.data"
                            :key="grievance.id"
                            type="button"
                            class="w-full rounded-2xl border bg-white p-4 text-left shadow transition hover:border-brand"
                            :class="[
                                selectedGrievance?.id === grievance.id
                                    ? 'border-brand ring-2 ring-brand/20'
                                    : 'border-gray-200'
                            ]"
                            @click="selectGrievance(grievance)"
                        >
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-gray-900">{{ grievance.reference_number }}</p>
                                    <StatusBadge :status="grievance.status" :label="grievance.status_label" size="sm" />
                                </div>
                                <span
                                    class="rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="priorityBadgeClass(grievance.priority)"
                                >
                                    {{ priorityLabel(grievance.priority) }}
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-gray-600 line-clamp-2">
                                {{ grievance.excerpt }}
                            </p>
                            <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-gray-400">
                                <span>Submetida em {{ formatDate(grievance.submitted_at) }}</span>
                                <span>Última atualização {{ formatRelative(grievance.updated_at) }}</span>
                                <span v-if="grievance.district">Distrito: {{ grievance.district }}</span>
                            </div>
                        </button>
                    </div>

                    <!-- Pagination -->
                    <nav
                        v-if="props.grievances?.links?.length"
                        class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-gray-200 bg-white p-3 shadow-sm"
                    >
                        <p class="text-xs text-gray-500">
                            Página {{ props.grievances.current_page }} de {{ props.grievances.last_page }}
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="link in props.grievances.links"
                                :key="link.label"
                                type="button"
                                class="rounded-lg px-3 py-1 text-xs font-medium"
                                :class="[
                                    link.active
                                        ? 'bg-brand text-white'
                                        : 'border border-gray-200 text-gray-600 hover:border-brand hover:text-brand',
                                    !link.url && 'cursor-not-allowed opacity-40'
                                ]"
                                :disabled="!link.url"
                                v-html="link.label"
                                @click="link.url && goTo(link.url)"
                            />
                        </div>
                    </nav>
                </section>

                <!-- Painel Detalhes -->
                <section class="space-y-4">
                    <div
                        v-if="selectedGrievance"
                        class="rounded-3xl border border-gray-200 bg-white p-5 shadow-lg space-y-6"
                    >
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ selectedGrievance.reference_number }}
                                </h3>
                                <StatusBadge
                                    :status="selectedGrievance.status"
                                    :label="selectedGrievance.status_label"
                                />
                            </div>
                            <p class="text-sm text-gray-500">
                                Categoria: <span class="font-medium text-gray-900">{{ selectedGrievance.category }}</span>
                            </p>
                            <div class="flex flex-wrap gap-2 text-xs text-gray-500">
                                <span>Submetida: {{ formatDateTime(selectedGrievance.submitted_at) }}</span>
                                <span>Atualizada: {{ formatDateTime(selectedGrievance.updated_at) }}</span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span :class="priorityBadgeClass(selectedGrievance.priority)" class="rounded-full px-3 py-1 text-xs font-semibold">
                                    Prioridade {{ priorityLabel(selectedGrievance.priority) }}
                                </span>
                                <span v-if="selectedGrievance.district" class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600">
                                    {{ selectedGrievance.district }} / {{ selectedGrievance.province }}
                                </span>
                            </div>
                        </div>

                        <div v-if="selectedGrievance.is_pending_approval" class="rounded-2xl border border-orange-200 bg-orange-50 p-4 text-sm text-orange-900">
                            Esta reclamação aguarda validação do Gestor. Assim que for aprovada, o utente será notificado.
                        </div>

                        <div class="space-y-1 text-sm text-gray-600">
                            <p class="font-semibold text-gray-900">Utente / Contacto</p>
                            <p>{{ selectedGrievance.contact_name || 'Reclamação Anónima' }}</p>
                            <p v-if="selectedGrievance.contact_email">Email: {{ selectedGrievance.contact_email }}</p>
                            <p v-if="selectedGrievance.contact_phone">Telefone: {{ selectedGrievance.contact_phone }}</p>
                        </div>

                        <div class="space-y-3">
                            <p class="text-sm font-semibold text-gray-900">Descrição</p>
                            <div
                                class="rounded-2xl bg-gray-50 p-3 text-sm text-gray-700"
                                v-html="selectedGrievance.description"
                            />
                        </div>

                        <div class="space-y-4">
                            <UpdatesTimeline :updates="selectedGrievance.updates ?? []" />
                            <AttachmentsGallery :attachments="selectedGrievance.attachments ?? []" />
                        </div>

                        <div class="space-y-3 rounded-2xl border border-gray-200 p-4">
                            <div class="flex items-center gap-2">
                                <PlayIcon class="h-5 w-5 text-brand" />
                                <p class="text-sm font-semibold text-gray-900">Ações Rápidas</p>
                            </div>
                            <button
                                v-if="selectedGrievance.can_start"
                                type="button"
                                class="w-full rounded-xl bg-brand px-4 py-3 text-sm font-medium text-white shadow hover:bg-brand/90 disabled:cursor-not-allowed disabled:opacity-70"
                                :disabled="startWorkForm.processing"
                                @click="startWork"
                            >
                                Iniciar trabalho
                            </button>
                            <p v-else class="text-xs text-gray-500">
                                A reclamação já foi iniciada ou aguarda outro passo.
                            </p>
                        </div>

                        <!-- Update form -->
                        <div class="space-y-3 rounded-2xl border border-gray-200 p-4">
                            <div class="flex items-center gap-2">
                                <ChatBubbleBottomCenterTextIcon class="h-5 w-5 text-brand" />
                                <p class="text-sm font-semibold text-gray-900">Adicionar atualização</p>
                            </div>
                            <form class="space-y-3" @submit.prevent="submitUpdate">
                                <div>
                                    <label class="text-xs font-medium text-gray-500">Descrição curta</label>
                                    <input
                                        v-model="updateForm.description"
                                        type="text"
                                        placeholder="Resumo da intervenção"
                                        class="mt-1 w-full rounded-lg border-gray-200 text-sm focus:border-brand focus:ring-brand"
                                    />
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-gray-500">Comentário</label>
                                    <textarea
                                        v-model="updateForm.comment"
                                        rows="3"
                                        placeholder="Detalhe o que foi feito..."
                                        class="mt-1 w-full rounded-lg border-gray-200 text-sm focus:border-brand focus:ring-brand"
                                    ></textarea>
                                    <p v-if="updateForm.errors.comment" class="mt-1 text-xs text-red-600">
                                        {{ updateForm.errors.comment }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-gray-500">Anexar evidências</label>
                                    <input
                                        ref="updateFilesInput"
                                        type="file"
                                        multiple
                                        class="mt-1 block w-full rounded-lg border border-dashed border-gray-300 text-xs text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-1 file:text-sm file:font-medium file:text-gray-700"
                                        @change="handleUpdateFiles"
                                    />
                                    <p v-if="updateForm.errors.attachments" class="mt-1 text-xs text-red-600">
                                        {{ updateForm.errors.attachments }}
                                    </p>
                                </div>
                                <label class="flex items-center gap-2 text-xs text-gray-600">
                                    <input
                                        v-model="updateForm.is_public"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-brand focus:ring-brand"
                                        :true-value="true"
                                        :false-value="false"
                                    />
                                    Tornar atualização visível ao utente
                                </label>
                                <button
                                    type="submit"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-brand bg-white px-4 py-2 text-sm font-medium text-brand transition hover:bg-brand/5 disabled:cursor-not-allowed disabled:opacity-70"
                                    :disabled="updateForm.processing"
                                >
                                    <ArrowPathIcon
                                        v-if="updateForm.processing"
                                        class="h-4 w-4 animate-spin"
                                    />
                                    Registar atualização
                                </button>
                            </form>
            </div>

                        <!-- Completion -->
                        <div class="space-y-3 rounded-2xl border border-gray-200 p-4">
                            <div class="flex items-center gap-2">
                                <PaperAirplaneIcon class="h-5 w-5 text-brand" />
                                <p class="text-sm font-semibold text-gray-900">Solicitar conclusão ao Gestor</p>
                            </div>
                            <form class="space-y-3" @submit.prevent="requestCompletion">
                                <div>
                                    <label class="text-xs font-medium text-gray-500">Resumo da resolução</label>
                                    <textarea
                                        v-model="completionForm.resolution_summary"
                                        rows="3"
                                        :disabled="!selectedGrievance.can_request_completion"
                                        placeholder="Descreva as ações realizadas e resultados..."
                                        class="mt-1 w-full rounded-lg border-gray-200 text-sm focus:border-brand focus:ring-brand disabled:bg-gray-50"
                                    ></textarea>
                                    <p v-if="completionForm.errors.resolution_summary" class="mt-1 text-xs text-red-600">
                                        {{ completionForm.errors.resolution_summary }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-gray-500">Anexos finais</label>
                                    <input
                                        ref="completionFilesInput"
                                        type="file"
                                        multiple
                                        :disabled="!selectedGrievance.can_request_completion"
                                        class="mt-1 block w-full rounded-lg border border-dashed border-gray-300 text-xs text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-1 file:text-sm file:font-medium file:text-gray-700 disabled:bg-gray-50"
                                        @change="handleCompletionFiles"
                                    />
                                    <p v-if="completionForm.errors.attachments" class="mt-1 text-xs text-red-600">
                                        {{ completionForm.errors.attachments }}
                                    </p>
                                </div>
                                <label class="flex items-center gap-2 text-xs text-gray-600">
                                    <input
                                        v-model="completionForm.notify_user"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-brand focus:ring-brand"
                                        :true-value="true"
                                        :false-value="false"
                                        :disabled="!selectedGrievance.can_request_completion"
                                    />
                                    Notificar o utente sobre esta atualização
                                </label>
                                <button
                                    type="submit"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand px-4 py-2 text-sm font-medium text-white shadow transition hover:bg-brand/90 disabled:cursor-not-allowed disabled:opacity-70"
                                    :disabled="!selectedGrievance.can_request_completion || completionForm.processing"
                                >
                                    <PaperAirplaneIcon class="h-4 w-4" />
                                    Solicitar conclusão
                                </button>
                            </form>
                        </div>
                    </div>

                    <div v-else class="rounded-3xl border-2 border-dashed border-gray-200 bg-white p-10 text-center text-gray-500 shadow">
                        Seleciona uma reclamação na lista para ver os detalhes e agir.
                    </div>
                </section>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import {
    ArrowPathIcon,
    ChatBubbleBottomCenterTextIcon,
    PaperAirplaneIcon,
    PlayIcon,
} from '@heroicons/vue/24/outline'
import Layout from '@/Layouts/Layout.vue'
import StatusBadge from '@/Components/Grievance/StatusBadge.vue'
import UpdatesTimeline from '@/Components/Grievance/UpdatesTimeline.vue'
import AttachmentsGallery from '@/Components/Grievance/AttachmentsGallery.vue'

const props = defineProps({
    user: {
        type: Object,
        default: () => ({}),
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
    grievances: {
        type: Object,
        default: () => ({ data: [] }),
    },
    filters: {
        type: Object,
        default: () => ({
            status: null,
            priority: null,
            search: '',
        }),
    },
    statusOptions: {
        type: Array,
        default: () => [],
    },
    priorityOptions: {
        type: Array,
        default: () => [],
    },
})

const selectedGrievance = ref(props.grievances.data[0] ?? null)
const localFilters = reactive({
    status: props.filters.status ?? '',
    priority: props.filters.priority ?? '',
    search: props.filters.search ?? '',
})

const startWorkForm = useForm({})
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

const updateFilesInput = ref(null)
const completionFilesInput = ref(null)

watch(
    () => props.grievances.data,
    (data) => {
        if (!data?.length) {
            selectedGrievance.value = null
            return
        }

        const currentId = selectedGrievance.value?.id
        selectedGrievance.value = data.find((item) => item.id === currentId) ?? data[0]
    }
)

watch(selectedGrievance, () => {
    updateForm.reset('description', 'comment', 'attachments')
    completionForm.reset('resolution_summary', 'attachments')
    updateForm.clearErrors()
    completionForm.clearErrors()

    if (updateFilesInput.value) updateFilesInput.value.value = null
    if (completionFilesInput.value) completionFilesInput.value.value = null
})

const statCards = computed(() => [
    {
        label: 'Atribuídas',
        value: props.stats.total_assigned ?? 0,
        description: 'Total em acompanhamento',
    },
    {
        label: 'Em andamento',
        value: props.stats.in_progress ?? 0,
        description: 'Processos ativos',
    },
    {
        label: 'Pendente de aprovação',
        value: props.stats.pending_approval ?? 0,
        description: 'Aguardando gestor',
    },
    {
        label: 'Resolvidas no mês',
        value: props.stats.resolved_month ?? 0,
        description: 'Reconhecidas neste mês',
    },
])

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
        low: 'bg-emerald-100 text-emerald-700',
        medium: 'bg-amber-100 text-amber-700',
        high: 'bg-red-100 text-red-700',
    }
    return map[priority] ?? 'bg-gray-100 text-gray-600'
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('pt-PT', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    })
}

const formatDateTime = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleString('pt-PT', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const formatRelative = (date) => {
    if (!date) return '—'
    const diff = Date.now() - Date.parse(date)
    const minutes = Math.floor(diff / (1000 * 60))
    if (minutes < 60) return `${minutes} min atrás`
    const hours = Math.floor(minutes / 60)
    if (hours < 24) return `${hours} h atrás`
    const days = Math.floor(hours / 24)
    return `${days} d atrás`
}

const buildFiltersPayload = () => ({
    status: localFilters.status ?? '',
    priority: localFilters.priority ?? '',
    search: localFilters.search ?? '',
})

const applyFilters = () => {
    router.get(route('technician.dashboard'), buildFiltersPayload(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const clearFilters = () => {
    localFilters.status = ''
    localFilters.priority = ''
    localFilters.search = ''
    applyFilters()
}

const selectGrievance = (grievance) => {
    selectedGrievance.value = grievance
}

const goTo = (url) => {
    router.get(url, {}, { preserveState: true, preserveScroll: true })
}

const startWork = () => {
    if (!selectedGrievance.value) return
    startWorkForm.patch(route('technician.grievances.start', selectedGrievance.value.id), {
        preserveScroll: true,
    })
}

const handleUpdateFiles = (event) => {
    updateForm.attachments = Array.from(event.target.files || [])
}

const submitUpdate = () => {
    if (!selectedGrievance.value) return
    updateForm.post(route('technician.grievances.updates.store', selectedGrievance.value.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            updateForm.reset('description', 'comment', 'attachments')
            if (updateFilesInput.value) updateFilesInput.value.value = null
        },
    })
}

const handleCompletionFiles = (event) => {
    completionForm.attachments = Array.from(event.target.files || [])
}

const requestCompletion = () => {
    if (!selectedGrievance.value) return
    completionForm.post(
        route('technician.grievances.request-completion', selectedGrievance.value.id),
        {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                completionForm.reset('resolution_summary', 'attachments')
                if (completionFilesInput.value) completionFilesInput.value.value = null
            },
        }
    )
}
</script>
