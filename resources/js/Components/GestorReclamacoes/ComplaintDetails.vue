<template>
    <!-- Overlay de fundo melhorado para mobile -->
    <div
        class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-start sm:items-center justify-center p-0 sm:p-4 overflow-y-auto">
        <!-- Container principal com altura full no mobile -->
        <div
            class="bg-white dark:bg-dark-secondary rounded-none sm:rounded shadow-2xl border-0 sm:border border-gray-200 dark:border-gray-700 w-full h-full sm:max-w-6xl sm:max-h-[90vh] sm:h-auto flex flex-col">
            <!-- Header fixo otimizado para mobile -->
            <div
                class="flex items-center justify-between p-4 sm:p-8 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-secondary sticky top-0 z-10">
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg sm:text-2xl font-bold text-gray-900 dark:text-dark-text-primary truncate">
                        Detalhes da Reclamação</h3>
                    <div class="flex items-center gap-2 mt-1 sm:mt-2 flex-wrap">
                        <div class="flex items-center gap-1 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span>Ativo</span>
                        </div>
                        <span class="text-gray-300 dark:text-gray-600 text-xs">•</span>
                        <span class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 truncate">Última: {{
                            getLastUpdate() }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-1 ml-2">
                    <button @click="handleClose" :disabled="loading.any"
                        class="p-1 sm:p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200 rounded-lg hover:bg-gray-50 dark:hover:bg-dark-accent disabled:opacity-50 disabled:cursor-not-allowed">
                        <XMarkIcon class="w-4 h-4 sm:w-5 sm:h-5" />
                    </button>
                </div>
            </div>

            <!-- Conteúdo com scroll otimizado -->
            <div class="flex-1 overflow-y-auto p-3 sm:p-8">
                <div v-if="complaint" class="space-y-4 sm:space-y-8">
                    <!-- Complaint Header melhorado para mobile -->
                    <div
                        class="bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-900/10 dark:to-amber-900/10 rounded p-3 sm:p-6 border border-orange-100 dark:border-orange-800">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 mb-2 sm:mb-3">
                                    <h4
                                        class="text-base sm:text-xl font-bold text-gray-900 dark:text-dark-text-primary truncate">
                                        {{ complaint.title }}
                                    </h4>
                                    <span
                                        class="px-2 py-1 bg-white dark:bg-dark-secondary rounded text-xs font-medium border border-orange-200 dark:border-orange-700 text-orange-700 dark:text-orange-300 shadow-sm self-start">
                                        {{ getTypeText(complaint.type) }}
                                    </span>
                                </div>

                                <p
                                    class="text-gray-700 dark:text-gray-300 text-sm sm:text-lg leading-relaxed mb-3 sm:mb-4 line-clamp-3">
                                    {{ complaint.description }}
                                </p>

                                <div
                                    class="flex flex-col sm:flex-row sm:flex-wrap items-start sm:items-center gap-2 sm:gap-4 text-xs sm:text-sm">
                                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                        <div
                                            class="w-6 h-6 sm:w-8 sm:h-8 bg-brand rounded flex items-center justify-center text-white text-xs font-bold">
                                            {{ getUserInitials(complaint.user?.name || 'Utente') }}
                                        </div>
                                        <span class="font-medium truncate">{{ complaint.user?.name || 'Utente' }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                                        <HashtagIcon class="w-3 h-3 sm:w-4 sm:h-4" />
                                        <span class="font-mono">#{{ complaint.id }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                                        <CalendarIcon class="w-3 h-3 sm:w-4 sm:h-4" />
                                        <span class="truncate">{{ formatDate(complaint.created_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats - Grid responsivo -->
                    <div class="grid grid-cols-2 gap-2 sm:gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div
                            class="bg-gray-50 dark:bg-dark-accent rounded p-2 sm:p-4 text-center border border-gray-100 dark:border-gray-700">
                            <div class="text-lg sm:text-2xl font-bold text-gray-900 dark:text-dark-text-primary mb-1">{{
                                complaint.activities?.length || 0 }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Atividades</div>
                        </div>
                        <div
                            class="bg-gray-50 dark:bg-dark-accent rounded p-2 sm:p-4 text-center border border-gray-100 dark:border-gray-700">
                            <div class="text-lg sm:text-2xl font-bold text-gray-900 dark:text-dark-text-primary mb-1">{{
                                complaint.attachments?.length || 0 }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Anexos</div>
                        </div>
                        <div
                            class="bg-gray-50 dark:bg-dark-accent rounded p-2 sm:p-4 text-center border border-gray-100 dark:border-gray-700">
                            <div class="text-lg sm:text-2xl font-bold text-gray-900 dark:text-dark-text-primary mb-1">{{
                                getDaysOpen() }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Dias em aberto</div>
                        </div>
                        <div
                            class="bg-gray-50 dark:bg-dark-accent rounded p-2 sm:p-4 text-center border border-gray-100 dark:border-gray-700">
                            <div class="text-lg sm:text-2xl font-bold text-gray-900 dark:text-dark-text-primary mb-1">{{
                                complaint.priority || 'N/A' }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Prioridade</div>
                        </div>
                    </div>

                    <!-- Main Content Grid - Stack no mobile -->
                    <div class="grid grid-cols-1 gap-4 sm:gap-8 lg:grid-cols-3">
                        <!-- Left Column -->
                        <div class="lg:col-span-2 space-y-4 sm:space-y-8">
                            <!-- Actions - Botões empilhados no mobile -->
                            <div
                                class="bg-white dark:bg-dark-secondary rounded border border-gray-200 dark:border-gray-700 p-3 sm:p-6 shadow-sm">
                                <div class="flex items-center gap-3 mb-4 sm:mb-6">
                                    <div
                                        class="w-2 h-4 sm:h-6 bg-gradient-to-b from-orange-500 to-amber-500 rounded-full">
                                    </div>
                                    <h4
                                        class="text-base sm:text-lg font-bold text-gray-900 dark:text-dark-text-primary">
                                        Ações
                                        Rápidas</h4>
                                </div>
                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 sm:gap-3">
                                    <!-- Botões com texto menor no mobile -->
                                    <button @click="openPriorityModal" :disabled="loading.priority"
                                        class="flex items-center justify-center gap-2 p-3 sm:p-4 bg-brand text-white rounded font-semibold transition-all duration-200 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base">
                                        <template v-if="loading.priority">
                                            <div
                                                class="animate-spin rounded-full h-4 w-4 sm:h-5 sm:w-5 border-b-2 border-white">
                                            </div>
                                            <span>Processando...</span>
                                        </template>
                                        <template v-else>
                                            <FlagIcon class="w-4 h-4 sm:w-5 sm:h-5" />
                                            <span class="truncate">Definir Prioridade</span>
                                        </template>
                                    </button>

                                    <button @click="openReassignModal" :disabled="loading.reassign"
                                        class="flex items-center justify-center gap-2 p-3 sm:p-4 bg-white dark:bg-dark-accent text-gray-700 dark:text-gray-300 rounded font-semibold border-2 border-gray-200 dark:border-gray-600 transition-all duration-200 hover:border-brand hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base">
                                        <template v-if="loading.reassign">
                                            <div
                                                class="animate-spin rounded-full h-4 w-4 sm:h-5 sm:w-5 border-b-2 border-brand">
                                            </div>
                                            <span>Processando...</span>
                                        </template>
                                        <template v-else>
                                            <UserGroupIcon class="w-4 h-4 sm:w-5 sm:h-5" />
                                            <span class="truncate">Reatribuir Técnico</span>
                                        </template>
                                    </button>

                                    <button @click="sendToDirector" :disabled="loading.sendToDirector"
                                        class="flex items-center justify-center gap-2 p-3 sm:p-4 bg-white dark:bg-dark-accent text-gray-700 dark:text-gray-300 rounded font-semibold border-2 border-gray-200 dark:border-gray-600 transition-all duration-200 hover:border-brand hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base">
                                        <template v-if="loading.sendToDirector">
                                            <div
                                                class="animate-spin rounded-full h-4 w-4 sm:h-5 sm:w-5 border-b-2 border-brand">
                                            </div>
                                            <span>Enviando...</span>
                                        </template>
                                        <template v-else>
                                            <PaperAirplaneIcon class="w-4 h-4 sm:w-5 sm:h-5" />
                                            <span class="truncate">Enviar ao Director</span>
                                        </template>
                                    </button>

                                    <button @click="markComplete" :disabled="loading.markComplete"
                                        class="flex items-center justify-center gap-2 p-3 sm:p-4 bg-green-500 text-white rounded font-semibold transition-all duration-200 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base">
                                        <template v-if="loading.markComplete">
                                            <div
                                                class="animate-spin rounded-full h-4 w-4 sm:h-5 sm:w-5 border-b-2 border-white">
                                            </div>
                                            <span>Processando...</span>
                                        </template>
                                        <template v-else>
                                            <CheckIcon class="w-4 h-4 sm:w-5 sm:h-5" />
                                            <span class="truncate">Marcar Concluído</span>
                                        </template>
                                    </button>
                                </div>
                            </div>

                            <!-- Activity Log com altura reduzida no mobile -->
                            <div
                                class="bg-white dark:bg-dark-secondary rounded-xl border border-gray-200 dark:border-gray-700 p-3 sm:p-6 shadow-sm">
                                <div class="flex items-center gap-3 mb-4 sm:mb-6">
                                    <div class="w-2 h-4 sm:h-6 bg-gradient-to-b from-blue-500 to-cyan-500 rounded-full">
                                    </div>
                                    <h4
                                        class="text-base sm:text-lg font-bold text-gray-900 dark:text-dark-text-primary">
                                        Histórico de
                                        Atividades</h4>
                                </div>
                                <div class="space-y-3 max-h-48 sm:max-h-80 overflow-y-auto pr-1 -mr-1">
                                    <!-- Atividades com texto menor -->
                                    <div v-for="(activity, index) in complaint.activities" :key="activity.id"
                                        class="flex gap-3 group hover:bg-gray-50 dark:hover:bg-dark-accent p-2 sm:p-3 rounded-xl transition-colors duration-200">
                                        <div class="flex flex-col items-center">
                                            <div class="w-2 h-2 sm:w-3 sm:h-3 bg-blue-500 rounded-full mt-1 sm:mt-2">
                                            </div>
                                            <div v-if="index !== complaint.activities.length - 1"
                                                class="w-0.5 h-full bg-gray-200 dark:bg-gray-600 mt-1"></div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 mb-1">
                                                <span
                                                    class="font-semibold text-gray-900 dark:text-dark-text-primary text-xs sm:text-sm truncate">
                                                    {{ activity.description }}
                                                </span>
                                                <span class="text-xs text-gray-400 dark:text-gray-500 flex-shrink-0">
                                                    {{ formatRelativeTime(activity.created_at) }}
                                                </span>
                                            </div>
                                            <div
                                                class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                                <UserIcon class="w-3 h-3" />
                                                <span class="truncate">Por: {{ activity.user?.name || 'Sistema'
                                                    }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="!complaint.activities?.length"
                                        class="text-center py-4 sm:py-8 text-gray-400 dark:text-gray-500">
                                        <ClockIcon class="w-8 h-8 sm:w-12 sm:h-12 mx-auto mb-2 sm:mb-3 opacity-50" />
                                        <p class="text-xs sm:text-sm">Nenhuma atividade registada</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4 sm:space-y-8">
                            <!-- Complaint Details compacto no mobile -->
                            <div
                                class="bg-white dark:bg-dark-secondary rounded-xl border border-gray-200 dark:border-gray-700 p-3 sm:p-6 shadow-sm">
                                <div class="flex items-center gap-3 mb-4 sm:mb-6">
                                    <div
                                        class="w-2 h-4 sm:h-6 bg-gradient-to-b from-purple-500 to-pink-500 rounded-full">
                                    </div>
                                    <h4
                                        class="text-base sm:text-lg font-bold text-gray-900 dark:text-dark-text-primary">
                                        Informações
                                    </h4>
                                </div>
                                <div class="space-y-2 sm:space-y-4">
                                    <!-- Itens de informação mais compactos -->
                                    <div
                                        class="flex items-center justify-between p-2 sm:p-3 bg-gray-50 dark:bg-dark-accent rounded-lg sm:rounded-xl">
                                        <span
                                            class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400">Status</span>
                                        <span
                                            class="px-2 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300 rounded-full text-xs font-bold">
                                            {{ getStatusText(complaint.status) }}
                                        </span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-2 sm:p-3 bg-gray-50 dark:bg-dark-accent rounded-lg sm:rounded-xl">
                                        <span
                                            class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400">Categoria</span>
                                        <span
                                            class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-dark-text-primary truncate ml-2">{{
                                                complaint.category
                                            }}</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-2 sm:p-3 bg-gray-50 dark:bg-dark-accent rounded-lg sm:rounded-xl">
                                        <span
                                            class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400">Tipo</span>
                                        <span
                                            class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-dark-text-primary truncate ml-2">{{
                                                getTypeText(complaint.type)
                                            }}</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-2 sm:p-3 bg-gray-50 dark:bg-dark-accent rounded-lg sm:rounded-xl">
                                        <span
                                            class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400">Técnico</span>
                                        <span
                                            class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-dark-text-primary flex items-center gap-1 truncate ml-2">
                                            <div class="w-1.5 h-1.5 bg-green-500 rounded-full flex-shrink-0"></div>
                                            <span class="truncate">{{ complaint.technician?.name || 'Não atribuído'
                                                }}</span>
                                        </span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-2 sm:p-3 bg-gray-50 dark:bg-dark-accent rounded-lg sm:rounded-xl">
                                        <span
                                            class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400">Prioridade</span>
                                        <span :class="[
                                            'px-2 py-1 rounded-full text-xs font-bold truncate',
                                            complaint.priority === 'high' ? 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300' :
                                                complaint.priority === 'medium' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300' :
                                                    'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300'
                                        ]">
                                            {{ complaint.priority || 'Não definida' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Attachments compacto -->
                            <div
                                class="bg-white dark:bg-dark-secondary rounded-xl border border-gray-200 dark:border-gray-700 p-3 sm:p-6 shadow-sm">
                                <div class="flex items-center gap-3 mb-4 sm:mb-6">
                                    <div
                                        class="w-2 h-4 sm:h-6 bg-gradient-to-b from-green-500 to-emerald-500 rounded-full">
                                    </div>
                                    <h4
                                        class="text-base sm:text-lg font-bold text-gray-900 dark:text-dark-text-primary">
                                        Anexos</h4>
                                </div>
                                <div class="space-y-2">
                                    <div v-for="attachment in complaint.attachments" :key="attachment.id"
                                        class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-dark-accent rounded-lg border border-gray-200 dark:border-gray-600 hover:border-orange-300 dark:hover:border-orange-500 transition-all duration-200 cursor-pointer group">
                                        <div
                                            class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-orange-500 to-amber-500 rounded-lg flex items-center justify-center text-white flex-shrink-0">
                                            <DocumentIcon class="w-4 h-4 sm:w-5 sm:h-5" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div
                                                class="text-xs sm:text-sm font-medium text-gray-900 dark:text-dark-text-primary truncate">
                                                {{
                                                    attachment.name
                                                }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ attachment.size ||
                                                'N/A' }}</div>
                                        </div>
                                        <ArrowDownTrayIcon
                                            class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 dark:text-gray-500 group-hover:text-orange-500 dark:group-hover:text-orange-400 transition-colors flex-shrink-0" />
                                    </div>
                                    <div v-if="!complaint.attachments?.length"
                                        class="text-center py-4 sm:py-6 text-gray-400 dark:text-gray-500">
                                        <PaperClipIcon
                                            class="w-8 h-8 sm:w-12 sm:h-12 mx-auto mb-2 sm:mb-3 opacity-50" />
                                        <p class="text-xs sm:text-sm">Sem anexos</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-8 sm:py-16">
                    <div
                        class="w-16 h-16 sm:w-24 sm:h-24 bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900/10 dark:to-amber-900/10 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-6">
                        <InformationCircleIcon class="w-8 h-8 sm:w-12 sm:h-12 text-orange-300 dark:text-orange-600" />
                    </div>
                    <h4 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-dark-text-primary mb-2">
                        Nenhuma reclamação
                        selecionada</h4>
                    <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto text-sm">Selecione uma reclamação da
                        lista para
                        visualizar os detalhes completos.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification posicionado para mobile -->
    <div v-if="toast.show" :class="[
        'fixed left-2 right-2 sm:left-auto sm:right-4 top-4 z-50 p-3 sm:p-4 rounded-lg shadow-lg border transform transition-all duration-300 max-w-full',
        toast.type === 'success' ? 'bg-green-50 border-green-200 text-green-800 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300' :
            toast.type === 'error' ? 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300' :
                'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-300'
    ]">
        <div class="flex items-center gap-2 sm:gap-3">
            <CheckCircleIcon v-if="toast.type === 'success'"
                class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 dark:text-green-400 flex-shrink-0" />
            <ExclamationTriangleIcon v-else-if="toast.type === 'error'"
                class="w-4 h-4 sm:w-5 sm:h-5 text-red-600 dark:text-red-400 flex-shrink-0" />
            <InformationCircleIcon v-else
                class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 dark:text-blue-400 flex-shrink-0" />
            <span class="font-medium text-sm sm:text-base flex-1">{{ toast.message }}</span>
            <button @click="toast.show = false"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex-shrink-0">
                <XMarkIcon class="w-4 h-4" />
            </button>
        </div>
    </div>

    <!-- Modals -->
    <PriorityModal v-if="showPriorityModal" :complaint="complaint" @close="showPriorityModal = false"
        @update="updatePriority" />

    <ReassignModal v-if="showReassignModal" :complaint="complaint" :technicians="technicians"
        @close="showReassignModal = false" @update="reassignTechnician" />
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import {
    UserIcon,
    HashtagIcon,
    TagIcon,
    DocumentTextIcon,
    FlagIcon,
    Cog6ToothIcon,
    UserGroupIcon,
    PaperAirplaneIcon,
    CheckIcon,
    PaperClipIcon,
    DocumentIcon,
    ClockIcon,
    InformationCircleIcon,
    CalendarIcon,
    PrinterIcon,
    ArrowDownTrayIcon,
    XMarkIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'
import PriorityModal from './PriorityModal.vue'
import ReassignModal from './ReassignModal.vue'

const props = defineProps({
    complaint: Object,
    technicians: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits([
    'update-priority',
    'reassign-technician',
    'send-to-director',
    'mark-complete',
    'close'
])

const showPriorityModal = ref(false)
const showReassignModal = ref(false)
const printContent = ref(null)

// Estados de loading
const loading = reactive({
    global: false,
    priority: false,
    reassign: false,
    sendToDirector: false,
    markComplete: false,
    print: false,
    any: computed(() =>
        loading.priority ||
        loading.reassign ||
        loading.sendToDirector ||
        loading.markComplete ||
        loading.print
    )
})

// Toast notification
const toast = reactive({
    show: false,
    message: '',
    type: 'success' // 'success', 'error', 'info'
})

// Função para mostrar toast
const showToast = (message, type = 'success') => {
    toast.message = message
    toast.type = type
    toast.show = true

    // Auto-hide após 5 segundos
    setTimeout(() => {
        toast.show = false
    }, 5000)
}

// Função para fechar o modal
const handleClose = () => {
    if (loading.any) {
        showToast('Aguarde a conclusão da operação atual', 'error')
        return
    }
    emit('close')
}


const getTypeText = (type) => {
    const types = {
        complaint: 'Reclamação',
        suggestion: 'Sugestão'
    }
    return types[type] || type
}

const getStatusText = (status) => {
    const statusTexts = {
        open: 'Aberto',
        in_progress: 'Em Progresso',
        pending_completion: 'Solicitado Conclusão',
        closed: 'Concluído'
    }
    return statusTexts[status] || status
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatRelativeTime = (dateString) => {
    const date = new Date(dateString)
    const now = new Date()
    const diffInHours = Math.floor((now - date) / (1000 * 60 * 60))

    if (diffInHours < 1) return 'Agora mesmo'
    if (diffInHours < 24) return `Há ${diffInHours}h`
    if (diffInHours < 168) return `Há ${Math.floor(diffInHours / 24)}d`
    return `Há ${Math.floor(diffInHours / 168)}sem`
}

const getLastUpdate = () => {
    if (!props.complaint?.activities?.length) return 'Nunca'
    const lastActivity = props.complaint.activities[props.complaint.activities.length - 1]
    return formatRelativeTime(lastActivity.created_at)
}

const getDaysOpen = () => {
    if (!props.complaint?.created_at) return 0
    const created = new Date(props.complaint.created_at)
    const now = new Date()
    return Math.floor((now - created) / (1000 * 60 * 60 * 24))
}

const getUserInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
}

const openPriorityModal = () => {
    if (!props.complaint) return
    showPriorityModal.value = true
}

const openReassignModal = () => {
    if (!props.complaint) return
    showReassignModal.value = true
}

const updatePriority = async (priority) => {
    loading.priority = true
    try {
        // Simula uma chamada API
        await new Promise(resolve => setTimeout(resolve, 1500))

        emit('update-priority', {
            complaintId: props.complaint.id,
            priority
        })
        showPriorityModal.value = false
        showToast('Prioridade atualizada com sucesso!', 'success')
    } catch (error) {
        showToast('Erro ao atualizar prioridade', 'error')
    } finally {
        loading.priority = false
    }
}

const reassignTechnician = async (technicianId) => {
    loading.reassign = true
    try {
        // Simula uma chamada API
        await new Promise(resolve => setTimeout(resolve, 1500))

        emit('reassign-technician', {
            complaintId: props.complaint.id,
            technicianId
        })
        showReassignModal.value = false
        showToast('Técnico reatribuído com sucesso!', 'success')
    } catch (error) {
        showToast('Erro ao reatribuir técnico', 'error')
    } finally {
        loading.reassign = false
    }
}

const sendToDirector = async () => {
    if (!props.complaint) return

    loading.sendToDirector = true
    try {
        // Simula uma chamada API
        await new Promise(resolve => setTimeout(resolve, 2000))

        emit('send-to-director', props.complaint.id)
        showToast('Reclamação enviada ao director com sucesso!', 'success')
    } catch (error) {
        showToast('Erro ao enviar reclamação ao director', 'error')
    } finally {
        loading.sendToDirector = false
    }
}

const markComplete = async () => {
    if (!props.complaint) return

    loading.markComplete = true
    try {
        // Simula uma chamada API
        await new Promise(resolve => setTimeout(resolve, 1500))

        emit('mark-complete', props.complaint.id)
        showToast('Reclamação marcada como concluída!', 'success')
    } catch (error) {
        showToast('Erro ao marcar reclamação como concluída', 'error')
    } finally {
        loading.markComplete = false
    }
}
</script>

<style scoped>
.hidden {
    display: none;
}

@media print {
    .print-container {
        max-width: 100%;
        margin: 0;
        padding: 20px;
    }
}
</style>