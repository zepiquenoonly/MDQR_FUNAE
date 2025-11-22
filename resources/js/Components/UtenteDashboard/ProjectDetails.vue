<template>
    <div class="min-h-screen bg-gray-50 p-4 -mt-6 relative">
        <div class="mb-4">
            <button @click="handleBack"
                class="flex items-center gap-2 text-gray-600 hover:text-gray-800 transition-colors">
                <ArrowLeftIcon class="w-5 h-5" />
                <span>Voltar para Projectos</span>
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand"></div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-20">
            <p class="text-red-500 text-lg mb-4">Erro ao carregar detalhes do projeto: {{ error }}</p>
            <button @click="fetchProjectDetails"
                class="bg-brand text-white px-6 py-2 rounded-lg hover:bg-orange-600 transition-colors">
                Tentar Novamente
            </button>
        </div>

        <!-- Success State -->
        <div v-else-if="project.id">
            <!-- Formulário de Reclamação (Overlay) -->
            <div v-if="showComplaintForm"
                class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                <ComplaintForm :project="project" @close="showComplaintForm = false" @submit="handleComplaintSubmit" />
            </div>

            <!-- Formulário de Queixa (Overlay) -->
            <div v-if="showComplaintRegister"
                class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                <ComplaintRegister :project="project" @close="showComplaintRegister = false"
                    @submit="handleComplaintRegisterSubmit" />
            </div>

            <!-- Conteúdo Principal -->
            <div class="bg-white rounded-xl shadow-sm grid grid-cols-1 lg:grid-cols-4">
                <!-- Sidebar Tabs -->
                <div class="border-r border-gray-200 p-4">
                    <div class="w-72 h-72 flex items-center justify-center">
                        <img :src="project.image_url || '/images/Emblem_of_Mozambique.svg-2.png'" :alt="project.name"
                            class="w-72 h-72 object-contain" />
                    </div>
                    <nav class="flex flex-col gap-2 mt-8">
                        <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[
                            'text-left px-4 py-3 rounded-md font-semibold transition m-2',
                            activeTab === tab.id
                                ? 'bg-brand text-white'
                                : 'bg-gray-100 hover:bg-gray-200 text-gray-700'
                        ]">
                            {{ tab.name }}
                        </button>
                    </nav>
                </div>

                <!-- Content -->
                <div class="p-6 lg:col-span-3">
                    <div class="flex gap-0 justify-end mb-6">
                        <button @click="showComplaintForm = true"
                            class="bg-brand-blue text-white px-6 py-2 rounded-l font-semibold hover:bg-blue-600 transition-colors">
                            Registar Reclamação
                        </button>
                        <button @click="showComplaintRegister = true"
                            class="bg-brand-green text-white px-6 py-2 rounded-r font-semibold hover:bg-green-600 transition-colors">
                            Registar Queixa
                        </button>
                    </div>

                    <!-- Informações Gerais do Projeto -->
                    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="flex-1">
                                    <h1
                                        class="text-3xl font-bold text-gray-800 text-center lg:text-left uppercase tracking-wide">
                                        {{ project.name }}
                                    </h1>
                                    <p class="text-gray-700 leading-relaxed mt-2">
                                        {{ project.description }}
                                    </p>

                                    <!-- Status Bar -->
                                    <div :class="[
                                        'w-full text-gray-800 font-medium rounded-md py-2 text-center mt-3 text-sm',
                                        getStatusClass(project.category)
                                    ]">
                                        {{ getStatusText(project.category) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- OBJECTIVOS -->
                    <div v-if="activeTab === 'objectivos'" class="space-y-6">
                        <div class="space-y-4 ml-6">
                            <div v-if="project.objectives && project.objectives.length > 0" class="space-y-6">
                                <div v-for="(objective, index) in project.objectives" :key="index">
                                    <h3 class="font-semibold text-gray-800 mb-2">{{ objective.title }}</h3>
                                    <p class="text-sm text-gray-600">{{ objective.description }}</p>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500">
                                Nenhum objetivo definido para este projeto.
                            </div>
                        </div>
                    </div>

                    <!-- FINANCIAMENTO -->
                    <div v-if="activeTab === 'financiamento'" class="space-y-6">
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="font-semibold text-gray-800 mb-4">Informações do Financiamento</h3>
                            <div class="space-y-3">
                                <div v-for="(detail, index) in financingDetails" :key="index"
                                    class="flex justify-between">
                                    <span class="text-gray-600">{{ detail.label }}:</span>
                                    <span class="font-semibold text-gray-800">{{ detail.value }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PRAZOS -->
                    <div v-if="activeTab === 'prazos'" class="space-y-6">
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="font-semibold text-gray-800 mb-4">Cronograma do Projeto</h3>
                            <div class="space-y-4 ml-6">
                                <div v-for="(date, index) in projectDates" :key="index" class="flex items-center gap-4">
                                    <div class="w-3 h-3 bg-brand rounded-full flex-shrink-0"></div>
                                    <div>
                                        <div class="text-sm text-gray-500 tracking-wide">{{ date.label }}:</div>
                                        <div class="font-semibold text-gray-800">{{ date.value }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Not Found -->
        <div v-else-if="!loading" class="text-center py-20">
            <p class="text-gray-500 text-lg mb-4">Projeto não encontrado.</p>
            <button @click="handleBack"
                class="bg-brand text-white px-6 py-2 rounded-lg hover:bg-orange-600 transition-colors">
                Voltar para Projetos
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import ComplaintForm from './ComplaintForm.vue'
import ComplaintRegister from './ComplaintRegister.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

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
    { id: 'objectivos', name: 'Objectivos' },
    { id: 'financiamento', name: 'Financiamento' },
    { id: 'prazos', name: 'Prazos' }
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
        { label: 'Localização', value: getLocation() },
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
        case 'andamento': return 'bg-yellow-200'
        case 'parados': return 'bg-red-200'
        case 'finalizados': return 'bg-green-200'
        default: return 'bg-yellow-200'
    }
}

const getLocation = () => {
    const { provincia, distrito, bairro } = project.value
    if (provincia && distrito && bairro) {
        return `${bairro}, ${distrito}, ${provincia}`
    }
    return 'Localização não disponível'
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