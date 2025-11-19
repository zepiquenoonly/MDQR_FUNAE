<template>
    <div class="space-y-6">
        <!-- Cabeçalho -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Minhas Reclamações</h1>
            <button @click="showComplaintForm = true"
                class="bg-brand hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold transition-colors">
                Nova Reclamação
            </button>
        </div>

        <ComplaintForm v-if="showComplaintForm" @close="showComplaintForm = false" @submit="handleNewComplaint" />

        <!-- Cartões de Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Total de Reclamações</h3>
                <div class="text-4xl font-bold text-gray-900 mb-1">18</div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Resolvidas</h3>
                <div class="text-4xl font-bold text-gray-900 mb-1">10</div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Em Progresso</h3>
                <div class="text-4xl font-bold text-gray-900 mb-1">5</div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Pendentes</h3>
                <div class="text-4xl font-bold text-gray-900 mb-1">3</div>
            </div>
        </div>

        <!-- Apenas a Tabela Condicional -->
        <ConditionalTable type="complaints" :data="complaints" @view-details="handleViewDetails"
            @edit-item="handleEditItem" @delete-item="handleDeleteItem" @export-data="handleExportData" />
    </div>
</template>

<script setup>
import { ref } from 'vue'
import ComplaintForm from './ComplaintForm.vue'

const complaints = ref([
    {
        id: 'REC-001',
        title: 'Danos Ambientais',
        description: 'Reclamação sobre impacto ambiental da construção',
        project: 'Projecto de Energia Eólica',
        impactType: 'Ambiental',
        date: '2024-01-12',
        status: 'Em Progresso'
    },
    {
        id: 'REC-002',
        title: 'Poluição Sonora',
        description: 'Ruído excessivo durante a noite',
        project: 'Desenvolvimento Urbano',
        impactType: 'Social',
        date: '2024-01-08',
        status: 'Resolvida'
    },
    {
        id: 'REC-003',
        title: 'Bloqueio de Estrada',
        description: 'Construção a bloquear estrada principal de acesso',
        project: 'Projecto de Infraestrutura',
        impactType: 'Económico',
        date: '2024-01-03',
        status: 'Aberta'
    }
])

const showComplaintForm = ref(false)

const handleNewComplaint = (formData) => {
    console.log('Nova reclamação submetida:', formData)
    showComplaintForm.value = false
}

// Handlers para os eventos da tabela
const handleViewDetails = (item) => {
    console.log('Ver detalhes da reclamação:', item)
}

const handleEditItem = (item) => {
    console.log('Editar reclamação:', item)
}

const handleDeleteItem = (item) => {
    console.log('Eliminar reclamação:', item)
    // Remover da lista
    const index = complaints.value.findIndex(c => c.id === item.id)
    if (index !== -1) {
        complaints.value.splice(index, 1)
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

.badge-environmental {
    @apply bg-green-100 text-green-800;
}

.badge-social {
    @apply bg-blue-100 text-blue-800;
}

.badge-economic {
    @apply bg-purple-100 text-purple-800;
}

.badge-resolved {
    @apply bg-green-100 text-green-800;
}

.badge-progress {
    @apply bg-yellow-100 text-yellow-800;
}

.badge-pending {
    @apply bg-red-100 text-red-800;
}
</style>