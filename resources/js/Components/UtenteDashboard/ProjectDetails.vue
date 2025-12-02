<template>
    <div class="relative min-h-screen p-4 -mt-6 bg-gray-50">
        <!-- Back Button -->
        <div class="mt-5 mb-6">
            <button @click="handleBack"
                class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 transition-all duration-200 border border-transparent rounded-lg hover:text-gray-900 hover:bg-white hover:border-gray-200 hover:shadow-sm">
                <ArrowLeft :size="20" />
                <span class="font-medium">Voltar para Projectos</span>
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-20">
            <Loader2 :size="48" class="mb-4 animate-spin text-primary-600" />
            <p class="text-gray-600">Carregando detalhes do projeto...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="py-20 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-red-50">
                <AlertCircle :size="32" class="text-red-600" />
            </div>
            <p class="mb-2 text-lg font-medium text-red-600">Erro ao carregar detalhes do projeto</p>
            <p class="mb-6 text-gray-600">{{ error }}</p>
            <button @click="fetchProjectDetails"
                class="inline-flex items-center gap-2 px-6 py-3 font-medium text-white transition-colors rounded-lg shadow-sm bg-primary-600 hover:bg-primary-700">
                <RefreshCw :size="18" />
                Tentar Novamente
            </button>
        </div>

        <!-- Success State -->
        <div v-else-if="project.id" class="space-y-6">
            <!-- Formulário de Reclamação (Overlay) -->
            <div v-if="showComplaintForm"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm">
                <ComplaintForm :project="project" @close="showComplaintForm = false" @submit="handleComplaintSubmit" />
            </div>

            <!-- Formulário de Queixa (Overlay) -->
            <div v-if="showComplaintRegister"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm">
                <ComplaintRegister :project="project" @close="showComplaintRegister = false"
                    @submit="handleComplaintRegisterSubmit" />
            </div>

            <!-- Header Card with Project Info -->
            <div class="overflow-hidden bg-white border border-gray-200 shadow-lg rounded-xl">
                <!-- Decorative Banner -->
                <div class="relative h-40 overflow-hidden bg-gradient-to-br from-primary-600 via-primary-500 to-orange-500">
                    <!-- Decorative Patterns -->
                    <div class="absolute inset-0">
                        <div class="absolute top-0 right-0 w-64 h-64 -mt-32 -mr-32 bg-white rounded-full opacity-5"></div>
                        <div class="absolute bottom-0 left-0 w-48 h-48 -mb-24 -ml-24 bg-white rounded-full opacity-5"></div>
                        <div class="absolute w-32 h-32 bg-white rounded-full top-1/2 left-1/3 opacity-5"></div>
                    </div>

                    <!-- Decorative Grid Pattern -->
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute inset-0" style="background-image: linear-gradient(rgba(255,255,255,.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.1) 1px, transparent 1px); background-size: 20px 20px;"></div>
                    </div>

                    <!-- Content Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent"></div>
                </div>

                <div class="px-6 pb-6 mt-15">
                    <div class="flex flex-col gap-6 lg:flex-row">
                        <!-- Project Image -->
                        <div class="flex-shrink-0">
                            <div class="w-40 h-40 overflow-hidden transition-shadow duration-300 bg-white border-4 border-white shadow-2xl rounded-2xl group hover:shadow-3xl">
                                <img v-if="project.image_url"
                                     :src="project.image_url"
                                     :alt="project.name"
                                     class="object-contain w-full h-full p-3 transition-transform duration-300 group-hover:scale-105" />
                                <div  class="flex items-center justify-center w-full h-full bg-gradient-to-br from-gray-100 to-gray-200">
                                    <ImageOff :size="64" class="text-gray-400" />
                                </div>
                            </div>
                        </div>

                        <!-- Project Info -->
                        <div class="flex-1 pt-8">
                            <div class="flex flex-col gap-4 mb-6 lg:flex-row lg:items-start lg:justify-between">
                                <div class="flex-1">
                                    <div class="flex items-start gap-3 mb-3">
                                        <h1 class="text-3xl font-bold leading-tight text-gray-900 lg:text-4xl">{{ project.name }}</h1>
                                    </div>
                                    <p class="text-base leading-relaxed text-gray-600">{{ project.description }}</p>
                                </div>

                                <!-- Status Badge -->
                                <div class="flex-shrink-0">
                                    <span :class="[
                                        'inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-xl border-2 shadow-sm',
                                        getStatusClass(project.category)
                                    ]">
                                        <component :is="getStatusIcon(project.category)" :size="18" />
                                        {{ getStatusText(project.category) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Quick Info Cards -->
                            <div class="grid grid-cols-1 gap-4 mt-6 sm:grid-cols-2 lg:grid-cols-4">
                                <!-- Location Card -->
                                <div class="relative p-4 transition-all duration-300 border border-blue-200 cursor-default group bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-xl hover:shadow-lg hover:scale-105">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 transition-transform duration-300 bg-blue-600 shadow-lg rounded-xl group-hover:scale-110">
                                            <MapPin :size="22" class="text-white" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-blue-700 font-semibold uppercase tracking-wide mb-0.5">Localização</p>
                                            <p class="text-sm font-bold text-blue-900 truncate">{{ getLocation() }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Responsible Card -->
                                <div class="relative p-4 transition-all duration-300 border border-green-200 cursor-default group bg-gradient-to-br from-green-50 to-green-100/50 rounded-xl hover:shadow-lg hover:scale-105">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 transition-transform duration-300 bg-green-600 shadow-lg rounded-xl group-hover:scale-110">
                                            <User :size="22" class="text-white" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-green-700 font-semibold uppercase tracking-wide mb-0.5">Responsável</p>
                                            <p class="text-sm font-bold text-green-900 truncate">{{ project.finance?.responsavel || 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Budget Card -->
                                <div class="relative p-4 transition-all duration-300 border border-orange-200 cursor-default group bg-gradient-to-br from-orange-50 to-orange-100/50 rounded-xl hover:shadow-lg hover:scale-105">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 transition-transform duration-300 bg-orange-600 shadow-lg rounded-xl group-hover:scale-110">
                                            <Coins :size="22" class="text-white" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-orange-700 font-semibold uppercase tracking-wide mb-0.5">Orçamento</p>
                                            <p class="text-sm font-bold text-orange-900 truncate">{{ project.finance?.valor_financiado || 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Financier Card -->
                                <div class="relative p-4 transition-all duration-300 border border-purple-200 cursor-default group bg-gradient-to-br from-purple-50 to-purple-100/50 rounded-xl hover:shadow-lg hover:scale-105">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 transition-transform duration-300 bg-purple-600 shadow-lg rounded-xl group-hover:scale-110">
                                            <Building2 :size="22" class="text-white" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-purple-700 font-semibold uppercase tracking-wide mb-0.5">Financiador</p>
                                            <p class="text-sm font-bold text-purple-900 truncate">{{ project.finance?.financiador || 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Info Row -->
                            <div class="grid grid-cols-1 gap-3 pt-4 mt-4 border-t border-gray-200 sm:grid-cols-3">
                                <div class="flex items-center gap-2 text-sm">
                                    <div class="flex items-center justify-center w-8 h-8 bg-indigo-100 rounded-lg">
                                        <Hash :size="16" class="text-indigo-600" />
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Código</p>
                                        <p class="text-sm font-semibold text-gray-900">{{ project.finance?.codigo || 'N/A' }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 text-sm">
                                    <div class="flex items-center justify-center w-8 h-8 bg-pink-100 rounded-lg">
                                        <Calendar :size="16" class="text-pink-600" />
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Início</p>
                                        <p class="text-sm font-semibold text-gray-900">{{ formatDate(project.deadline?.data_inicio) }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 text-sm">
                                    <div class="flex items-center justify-center w-8 h-8 bg-teal-100 rounded-lg">
                                        <Clock :size="16" class="text-teal-600" />
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Conclusão</p>
                                        <p class="text-sm font-semibold text-gray-900">{{ formatDate(project.deadline?.data_finalizacao) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3">
                <button @click="showComplaintForm = true"
                    class="inline-flex items-center gap-2 px-6 py-3 font-medium text-white transition-all duration-200 bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 hover:shadow-md">
                    <AlertCircle :size="18" />
                    Registar Reclamação
                </button>
                <button @click="showComplaintRegister = true"
                    class="inline-flex items-center gap-2 px-6 py-3 font-medium text-white transition-all duration-200 bg-green-600 rounded-lg shadow-sm hover:bg-green-700 hover:shadow-md">
                    <MessageSquare :size="18" />
                    Registar Queixa
                </button>
            </div>

            <!-- Tabs Navigation -->
            <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
                <div class="border-b border-gray-200">
                    <nav class="flex">
                        <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[
                            'flex-1 px-6 py-4 text-sm font-medium transition-all duration-200 border-b-2 flex items-center justify-center gap-2',
                            activeTab === tab.id
                                ? 'border-primary-600 text-primary-600 bg-primary-50'
                                : 'border-transparent text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                        ]">
                            <component :is="tab.icon" :size="18" />
                            {{ tab.name }}
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- OBJECTIVOS -->
                    <div v-if="activeTab === 'objectivos'" class="space-y-4">
                        <div v-if="project.objectives && project.objectives.length > 0" class="space-y-4">
                            <div v-for="(objective, index) in project.objectives" :key="index"
                                class="p-5 transition-shadow duration-200 border border-gray-200 rounded-lg bg-gradient-to-r from-gray-50 to-white hover:shadow-md">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <Target :size="20" class="text-primary-600" />
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="mb-2 text-lg font-semibold text-gray-900">{{ objective.title }}</h3>
                                        <p class="leading-relaxed text-gray-600">{{ objective.description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="py-12 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 mb-4 bg-gray-100 rounded-full">
                                <Target :size="32" class="text-gray-400" />
                            </div>
                            <p class="font-medium text-gray-500">Nenhum objetivo definido para este projeto</p>
                        </div>
                    </div>

                    <!-- FINANCIAMENTO -->
                    <div v-if="activeTab === 'financiamento'" class="space-y-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div v-for="(detail, index) in financingDetails" :key="index"
                                class="p-4 transition-all duration-200 border border-gray-200 rounded-lg bg-gradient-to-br from-gray-50 to-white hover:shadow-md">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg">
                                        <component :is="getFinanceIcon(detail.label)" :size="20" class="text-blue-600" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="mb-1 text-xs font-medium text-gray-500">{{ detail.label }}</p>
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ detail.value }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PRAZOS -->
                    <div v-if="activeTab === 'prazos'" class="space-y-4">
                        <div class="relative">
                            <!-- Timeline Line -->
                            <div class="absolute left-5 top-0 bottom-0 w-0.5 bg-gradient-to-b from-primary-600 to-primary-200"></div>

                            <div class="space-y-6">
                                <div v-for="(date, index) in projectDates" :key="index"
                                    class="relative pb-6 pl-14 group">
                                    <!-- Timeline Dot -->
                                    <div class="absolute left-0 flex items-center justify-center w-10 h-10 transition-transform duration-200 bg-white border-4 rounded-full shadow-sm border-primary-600 group-hover:scale-110">
                                        <Calendar :size="16" class="text-primary-600" />
                                    </div>

                                    <!-- Timeline Content -->
                                    <div class="p-4 transition-all duration-200 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <div>
                                                <p class="mb-1 text-sm font-medium text-gray-500">{{ date.label }}</p>
                                                <p class="text-base font-semibold text-gray-900">{{ date.value }}</p>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs text-gray-500 bg-gray-50 px-3 py-1.5 rounded-full">
                                                <Clock :size="14" />
                                                <span>{{ getDateStatus(date.value) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Not Found -->
        <div v-else-if="!loading" class="py-20 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 mb-4 bg-gray-100 rounded-full">
                <FileQuestion :size="40" class="text-gray-400" />
            </div>
            <p class="mb-2 text-xl font-semibold text-gray-900">Projeto não encontrado</p>
            <p class="mb-6 text-gray-500">O projeto que procura não existe ou foi removido</p>
            <button @click="handleBack"
                class="inline-flex items-center gap-2 px-6 py-3 font-medium text-white transition-colors rounded-lg shadow-sm bg-primary-600 hover:bg-primary-700">
                <ArrowLeft :size="18" />
                Voltar para Projetos
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import ComplaintForm from './ComplaintForm.vue'
import ComplaintRegister from './ComplaintRegister.vue'
import {
    ArrowLeft,
    Loader2,
    AlertCircle,
    RefreshCw,
    MapPin,
    User,
    Coins,
    MessageSquare,
    Target,
    Calendar,
    Clock,
    FileQuestion,
    Building2,
    DollarSign,
    Hash,
    CheckCircle2,
    XCircle,
    PlayCircle,
    ImageOff
} from 'lucide-vue-next'

const props = defineProps({
    projectId: {
        type: Number,
        required: true
    }
})

const activeTab = ref('objectivos')
const project = ref({})
const loading = ref(false)
const error = ref(null)
const showComplaintForm = ref(false)
const showComplaintRegister = ref(false)

const tabs = [
    { id: 'objectivos', name: 'Objectivos', icon: Target },
    { id: 'financiamento', name: 'Financiamento', icon: Coins },
    { id: 'prazos', name: 'Prazos', icon: Calendar }
]

const emit = defineEmits(['back'])

// Computed properties
const financingDetails = computed(() => {
    if (!project.value.finance) return []

    return [
        { label: 'Financiador', value: project.value.finance.financiador || 'N/A' },
        { label: 'Beneficiário', value: project.value.finance.beneficiario || 'N/A' },
        { label: 'Responsável', value: project.value.finance.responsavel || 'N/A' },
        { label: 'Valor Financiado', value: project.value.finance.valor_financiado || 'N/A' },
        { label: 'Código', value: project.value.finance.codigo || 'N/A' },
        { label: 'Província', value: project.value.provincia || 'N/A' },
        { label: 'Distrito', value: project.value.distrito || 'N/A' },
        { label: 'Bairro', value: project.value.bairro || 'N/A' }
    ]
})

const projectDates = computed(() => {
    if (!project.value.deadline) return []

    return [
        {
            label: 'Data de Aprovação',
            value: formatDate(project.value.deadline.data_aprovacao)
        },
        {
            label: 'Data de Início',
            value: formatDate(project.value.deadline.data_inicio)
        },
        {
            label: 'Data de Inspeção',
            value: formatDate(project.value.deadline.data_inspecao)
        },
        {
            label: 'Data de Finalização',
            value: formatDate(project.value.deadline.data_finalizacao)
        },
        {
            label: 'Data de Inauguração',
            value: formatDate(project.value.deadline.data_inauguracao)
        }
    ]
})

// Methods
const handleBack = () => {
    emit('back')
}

const getStatusText = (category) => {
    switch (category) {
        case 'andamento': return 'Em andamento'
        case 'parados': return 'Parado'
        case 'finalizados': return 'Finalizado'
        default: return 'Em andamento'
    }
}

const getStatusClass = (category) => {
    switch (category) {
        case 'andamento': return 'bg-primary-50 text-primary-700 border-primary-200'
        case 'parados': return 'bg-red-50 text-red-700 border-red-200'
        case 'finalizados': return 'bg-green-50 text-green-700 border-green-200'
        default: return 'bg-primary-50 text-primary-700 border-primary-200'
    }
}

const getStatusIcon = (category) => {
    switch (category) {
        case 'andamento': return PlayCircle
        case 'parados': return XCircle
        case 'finalizados': return CheckCircle2
        default: return PlayCircle
    }
}

const getFinanceIcon = (label) => {
    if (label.includes('Financiador') || label.includes('Beneficiário')) return Building2
    if (label.includes('Responsável')) return User
    if (label.includes('Valor')) return DollarSign
    if (label.includes('Código')) return Hash
    if (label.includes('Província') || label.includes('Distrito') || label.includes('Bairro')) return MapPin
    return Coins
}

const getLocation = () => {
    const { provincia, distrito, bairro } = project.value
    if (provincia && distrito && bairro) {
        return `${bairro}, ${distrito}, ${provincia}`
    }
    if (provincia) return provincia
    return 'N/A'
}

const formatDate = (dateString) => {
    if (!dateString) return 'N/A'

    try {
        const date = new Date(dateString)
        return date.toLocaleDateString('pt-MZ', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        })
    } catch {
        return dateString
    }
}

const getDateStatus = (dateString) => {
    if (dateString === 'N/A') return 'Não definido'

    try {
        const date = new Date(dateString)
        const now = new Date()

        if (date > now) return 'Previsto'
        if (date < now) return 'Concluído'
        return 'Em curso'
    } catch {
        return 'Data inválida'
    }
}

const handleComplaintSubmit = (formData) => {
    console.log('Reclamação submetida:', formData)
    showComplaintForm.value = false
}

const handleComplaintRegisterSubmit = (formData) => {
    console.log('Queixa submetida:', formData)
    showComplaintRegister.value = false
}

// Fetch project details from API
const fetchProjectDetails = async () => {
    loading.value = true
    error.value = null

    try {
        const response = await fetch(`/api/projects/${props.projectId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })

        if (!response.ok) {
            throw new Error(`Erro ${response.status}: ${response.statusText}`)
        }

        const data = await response.json()
        project.value = data
        console.log('Detalhes do projeto carregados:', data)
    } catch (err) {
        console.error('Erro ao buscar detalhes do projeto:', err)
        error.value = err.message
    } finally {
        loading.value = false
    }
}

// Lifecycle
onMounted(() => {
    fetchProjectDetails()
})
</script>

<style scoped>
/* Estilos adicionais se necessário */
</style>
