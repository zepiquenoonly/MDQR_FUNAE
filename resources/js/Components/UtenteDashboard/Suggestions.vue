<template>
    <div class="space-y-6">
        <!-- Cabeçalho -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Minhas Sugestões</h1>
            <button @click="showSuggestionForm = true"
                class="bg-brand hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold transition-colors">
                Nova Sugestão
            </button>
        </div>

        <SuggestionForm v-if="showSuggestionForm" @close="showSuggestionForm = false" @submit="handleNewSuggestion" />

        <!-- Cartões de Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Total de Sugestões</h3>
                <div class="text-4xl font-bold text-gray-900 mb-1">24</div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Aprovadas</h3>
                <div class="text-4xl font-bold text-gray-900 mb-1">12</div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Em análise</h3>
                <div class="text-4xl font-bold text-gray-900 mb-1">8</div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Rejeitadas</h3>
                <div class="text-4xl font-bold text-gray-900 mb-1">4</div>
            </div>
        </div>

        <!-- Apenas a Tabela Condicional -->
        <ConditionalTable type="suggestions" :data="suggestions" @view-details="handleViewDetails"
            @edit-item="handleEditItem" @delete-item="handleDeleteItem" @export-data="handleExportData" />
    </div>
</template>

<script setup>
import { ref } from 'vue'
import SuggestionForm from './SuggestionForm.vue'

const suggestions = ref([
    {
        id: 'SUG-001',
        title: 'Melhoria na Distribuição de Água',
        description: 'Sugestão para melhor sistema de distribuição de água',
        project: 'Sistema de Abastecimento de Água',
        date: '2024-01-15',
        status: 'Aprovada'
    },
    {
        id: 'SUG-002',
        title: 'Implementação de Energia Solar',
        description: 'Proposta para energia solar em edifícios públicos',
        project: 'Projecto de Energia Renovável',
        date: '2024-01-10',
        status: 'Em Análise'
    },
    {
        id: 'SUG-003',
        title: 'Renovação do Centro Comunitário',
        description: 'Proposta de renovação para o centro comunitário',
        project: 'Desenvolvimento Urbano',
        date: '2024-01-05',
        status: 'Rejeitada'
    }
])

const showSuggestionForm = ref(false)

const handleNewSuggestion = (formData) => {
    console.log('Nova sugestão:', formData)
    showSuggestionForm.value = false
}

// Handlers para os eventos da tabela
const handleViewDetails = (item) => {
    console.log('Ver detalhes da sugestão:', item)
}

const handleEditItem = (item) => {
    console.log('Editar sugestão:', item)
}

const handleDeleteItem = (item) => {
    console.log('Eliminar sugestão:', item)
    // Remover da lista
    const index = suggestions.value.findIndex(s => s.id === item.id)
    if (index !== -1) {
        suggestions.value.splice(index, 1)
    }
}

const handleExportData = (exportData) => {
    console.log('Exportar dados:', exportData)
}
</script>

<style scoped>
.badge {
    @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
}

.badge-approved {
    @apply bg-green-100 text-green-800;
}

.badge-pending {
    @apply bg-yellow-100 text-yellow-800;
}

.badge-rejected {
    @apply bg-red-100 text-red-800;
}
</style>