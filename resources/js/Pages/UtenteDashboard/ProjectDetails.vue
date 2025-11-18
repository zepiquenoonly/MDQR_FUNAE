<template>
    <div class="min-h-screen bg-gray-50 p-4 -mt-6 relative">
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

        <!-- Conteúdo Principal (sempre visível) -->
        <!-- Main Layout: Sidebar Tabs + Content -->
        <div class="bg-white rounded-xl shadow-sm grid grid-cols-1 lg:grid-cols-4">
            <!-- Sidebar Tabs -->
            <div class="border-r border-gray-200 p-4">
                <div class="w-72 h-72 flex items-center justify-center">
                    <img :src="project.image" alt="project image" class="w-72 h-72 object-contain" />
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

                <!-- Resto do conteúdo permanece igual -->
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
                                <div
                                    class="w-full bg-yellow-200 text-gray-800 font-medium rounded-md py-2 text-center mt-3 text-sm">
                                    {{ getStatusText(project.category) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OBJECTIVOS -->
                <div v-if="activeTab === 'objectivos'" class="space-y-6">
                    <div class="space-y-4 ml-6">
                        <div class="space-y-6">
                            <div v-for="(objective, index) in project.objectives" :key="index">
                                <h3 class="font-semibold text-gray-800 mb-2">{{ objective.title }}</h3>
                                <p class="text-sm text-gray-600">{{ objective.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FINANCIAMENTO -->
                <div v-if="activeTab === 'financiamento'" class="space-y-6">
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Informações do Financiamento</h3>
                        <div class="space-y-3">
                            <div v-for="(detail, index) in project.financingDetails" :key="index"
                                class="flex justify-between">
                                <span class="text-gray-600">{{ detail.label }}:</span>
                                <span class="font-semibold text-gray-800">{{ detail.value }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PRAZOS -->
                <div v-if="activeTab === 'prazos'" class="space-y-6">
                    <div class="space-y-4 ml-6">
                        <div v-for="(date, index) in project.dates" :key="index">
                            <div class="text-sm text-gray-500 tracking-wide mb-1">{{ date.label }}:
                                <strong>{{ date.value }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import ComplaintForm from './ComplaintForm.vue'
import ComplaintRegister from './ComplaintRegister.vue'

const props = defineProps({
    projectId: {
        type: Number,
        required: true
    }
})

const activeTab = ref('objectivos')
const project = ref({})
const showComplaintForm = ref(false)
const showComplaintRegister = ref(false)

const tabs = [
    { id: 'objectivos', name: 'Objectivos' },
    { id: 'financiamento', name: 'Financiamento' },
    { id: 'prazos', name: 'Prazos' }
]

// Dados completos do projeto
const projectData = {
    1: {
        id: 1,
        image: '/images/Emblem_of_Mozambique.svg-2.png',
        name: 'PROJETO PARQUE EÓLICO DE PEMBA',
        category: 'andamento',
        description: 'O projecto Parque Eólico de Pemba, uma iniciativa inovadora implementada pelo Fundo de Energia (FUNAE FP), representa um marco no compromisso de Moçambique com a transição energética sustentável e a redução da dependência de fontes de energia convencionais. Este projeto ambicioso é um testemunho da visão do país em explorar o vasto potencial de recursos renováveis disponíveis para atender às necessidades energéticas das comunidades locais e impulsionar o desenvolvimento socioeconômico sustentável.',
        responsible: 'FUNAE, FP',
        detailedLocation: 'Bairro Zimpeto, Pemba',
        detailedBudget: 'USD 25,5 Milhões',
        objectives: [
            {
                title: 'Produção de Energia Renovável',
                description: 'Aproveitar o potencial eólico da região de Pemba para gerar eletricidade limpa e renovável, contribuindo para a diversificação da matriz energética de Moçambique.'
            },
            {
                title: 'Redução da Dependência de Combustíveis Fósseis',
                description: 'Diminuir a dependência de fontes de energia convencionais, como carvão e petróleo, alinhando-se aos objetivos globais de redução de emissões de carbono.'
            },
            {
                title: 'Acesso à Energia para Comunidades Locais',
                description: 'Melhorar o acesso à energia elétrica em comunidades remotas, promovendo o desenvolvimento social e econômico na região.'
            },
            {
                title: 'Fomento da Sustentabilidade Ambiental',
                description: 'Reduzir o impacto ambiental causado pela geração de energia a partir de fontes não renováveis, protegendo os ecossistemas locais.'
            }
        ],
        financingDetails: [
            { label: 'Financiador', value: 'Enabel' },
            { label: 'Província', value: 'Cabo Delgado' },
            { label: 'Beneficiário', value: 'Município de Pemba' },
            { label: 'Responsável', value: 'FUNAE, FP' },
            { label: 'Valor Financiado', value: 'USD 25,5 Milhões' },
            { label: 'Código', value: '#2024/ENABEL/FP' },
            { label: 'Localização', value: 'Bairro Zimpeto, Pemba' }
        ],
        dates: [
            { label: 'Data de Aprovação', value: '2024-05-25', period: 'Ano 2024' },
            { label: 'Data de Início', value: '2024-10-25', period: 'Ano 2024' },
            { label: 'Data de Inspeção', value: '2025-05-25', period: 'Ano 2025' },
            { label: 'Data de Finalização', value: '2026-05-25', period: 'Ano 2026' },
            { label: 'Data de Inauguração', value: '2026-06-25', period: 'Ano 2026' }
        ],
        milestones: [
            {
                title: 'Aprovação do Projecto',
                date: '25 de Maio de 2024',
                description: 'Aprovação final do projeto e liberação de recursos'
            },
            {
                title: 'Início das Obras',
                date: '25 de Outubro de 2024',
                description: 'Início da construção da infraestrutura básica'
            },
            {
                title: 'Instalação das Turbinas',
                date: '15 de Março de 2025',
                description: 'Montagem e instalação das turbinas eólicas'
            },
            {
                title: 'Inspeção Técnica',
                date: '25 de Maio de 2025',
                description: 'Vistoria técnica e verificação de conformidade'
            },
            {
                title: 'Testes de Operação',
                date: '15 de Janeiro de 2026',
                description: 'Testes operacionais e ajustes finais'
            },
            {
                title: 'Finalização do Projecto',
                date: '25 de Maio de 2026',
                description: 'Conclusão de todas as etapas do projeto'
            },
            {
                title: 'Inauguração Oficial',
                date: '25 de Junho de 2026',
                description: 'Cerimônia oficial de inauguração'
            }
        ]
    }
}

// Computed properties
const financingInfo = computed(() => [
    { label: 'Valor Total', value: project.value.detailedBudget || 'USD 25,5 Milhões' },
    { label: 'Financiador', value: 'Enabel' },
    { label: 'Status', value: getStatusText(project.value.category) },
    { label: 'Localização', value: project.value.detailedLocation || 'Bairro Zimpeto, Pemba' },
    { label: 'Responsável', value: project.value.responsible || 'FUNAE, FP' },
    { label: 'Código', value: '#2024/ENABEL/FP' }
])

// Methods
const getStatusText = (category) => {
    switch (category) {
        case 'andamento': return 'Em andamento'
        case 'parados': return 'Parado'
        case 'finalizados': return 'Finalizado'
        default: return 'Em andamento'
    }
}

const getEntityInitials = (entityName) => {
    if (!entityName) return 'FP'
    return entityName
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2)
}

const handleComplaintSubmit = (formData) => {
    console.log('Reclamação submetida:', formData)
    showComplaintForm.value = false
}

const handleComplaintRegisterSubmit = (formData) => {
    console.log('Queixa submetida:', formData)
    showComplaintRegister.value = false
}

// Lifecycle
onMounted(() => {
    project.value = projectData[props.projectId] || projectData[1]
    console.log('Project details loaded:', project.value)
})
</script>

<style scoped>
/* Estilos adicionais se necessário */
</style>