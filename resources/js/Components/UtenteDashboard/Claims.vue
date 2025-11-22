<template>
    <div class="space-y-6">
        <!-- Cabeçalho -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Minhas Queixas</h1>
            <button @click="showComplaintRegister = true"
                class="bg-brand hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold transition-colors">
                Nova Queixa
            </button>
        </div>

        <ComplaintRegister v-if="showComplaintRegister" @close="showComplaintRegister = false"
            @submit="handleNewClaim" />

        <!-- Cartões de Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Total de Queixas</h3>
                <div class="text-4xl font-bold text-gray-900 mb-1">15</div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Validadas</h3>
                <div class="text-4xl font-bold text-gray-900 mb-1">8</div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Em análise</h3>
                <div class="text-4xl font-bold text-gray-900 mb-1">4</div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Rejeitadas</h3>
                <div class="text-4xl font-bold text-gray-900 mb-1">3</div>
            </div>
        </div>

        <!-- Apenas a Tabela Condicional -->
        <ConditionalTable type="claims" :data="claims" @view-details="handleViewDetails" @edit-item="handleEditItem"
            @delete-item="handleDeleteItem" @export-data="handleExportData" />
    </div>
</template>

<script setup>
import { ref } from 'vue'
import ComplaintRegister from './ComplaintRegister.vue'

const claims = ref([
    {
        id: 'QUE-001',
        title: 'Queixa de Danos à Propriedade',
        description: 'Danos à propriedade residencial devido à construção',
        project: 'Desenvolvimento de Infraestrutura',
        priority: 'Alta',
        date: '2024-01-14',
        status: 'Em Análise'
    },
    {
        id: 'QUE-002',
        title: 'Queixa de Compensação',
        description: 'Pedido de compensação devido a atrasos no projecto',
        project: 'Obras Públicas',
        priority: 'Média',
        date: '2024-01-09',
        status: 'Validada'
    },
    {
        id: 'QUE-003',
        title: 'Queixa de Impacto Ambiental',
        description: 'Queixa sobre danos ambientais',
        project: 'Operação Mineira',
        priority: 'Urgente',
        date: '2024-01-04',
        status: 'Rejeitada'
    }
])

const showComplaintRegister = ref(false)

const handleNewClaim = (formData) => {
    console.log('Nova queixa submetida:', formData)
    showComplaintRegister.value = false
}

// Handlers para os eventos da tabela
const handleViewDetails = (item) => {
    console.log('Ver detalhes da queixa:', item)
}

const handleEditItem = (item) => {
    console.log('Editar queixa:', item)
}

const handleDeleteItem = (item) => {
    console.log('Eliminar queixa:', item)
    // Remover da lista
    const index = claims.value.findIndex(c => c.id === item.id)
    if (index !== -1) {
        claims.value.splice(index, 1)
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

.badge-high {
    @apply bg-red-100 text-red-800;
}

.badge-medium {
    @apply bg-yellow-100 text-yellow-800;
}

.badge-urgent {
    @apply bg-purple-100 text-purple-800;
}

.badge-validated {
    @apply bg-green-100 text-green-800;
}

.badge-analysis {
    @apply bg-blue-100 text-blue-800;
}

.badge-rejected {
    @apply bg-red-100 text-red-800;
}
</style>