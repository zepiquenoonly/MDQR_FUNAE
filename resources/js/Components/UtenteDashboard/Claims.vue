<template>
    <div class="space-y-6">
        <!-- Cabeçalho -->
        <div class="glass-card p-6 flex items-center justify-between hover:shadow-2xl transition-all duration-300 border border-white/40">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold gradient-text">Minhas Queixas</h1>
                <p class="text-sm text-gray-600 mt-1">Gerencie todas as suas queixas</p>
            </div>
            <button @click="showComplaintRegister = true"
                class="flex items-center gap-2 px-5 py-3 font-bold text-white bg-gradient-to-r from-primary-500 to-orange-600 hover:from-primary-600 hover:to-orange-700 transition-all duration-300 rounded-xl shadow-lg hover:shadow-2xl hover:scale-105 border border-orange-400/30 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                <svg class="w-5 h-5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="relative z-10">Nova Queixa</span>
            </button>
        </div>

        <ComplaintRegister v-if="showComplaintRegister" @close="showComplaintRegister = false"
            @submit="handleNewClaim" />

        <!-- Cartões de Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
            <div class="glass-card p-6 transition-all duration-300 hover:shadow-2xl hover:scale-105 group border border-white/40 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-primary-500/5 to-orange-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative z-10">
                    <h3 class="mb-2 text-sm font-semibold text-gray-700 group-hover:text-primary-700 transition-colors">Total de Queixas</h3>
                    <div class="mb-1 text-4xl font-bold gradient-text group-hover:scale-110 transition-transform origin-left">15</div>
                </div>
            </div>
            <div class="glass-card p-6 transition-all duration-300 hover:shadow-2xl hover:scale-105 group border border-white/40 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative z-10">
                    <h3 class="mb-2 text-sm font-semibold text-gray-700 group-hover:text-green-700 transition-colors">Validadas</h3>
                    <div class="mb-1 text-4xl font-bold text-green-600 group-hover:scale-110 transition-transform origin-left">8</div>
                </div>
            </div>
            <div class="glass-card p-6 transition-all duration-300 hover:shadow-2xl hover:scale-105 group border border-white/40 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-amber-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative z-10">
                    <h3 class="mb-2 text-sm font-semibold text-gray-700 group-hover:text-orange-700 transition-colors">Em análise</h3>
                    <div class="mb-1 text-4xl font-bold text-orange-600 group-hover:scale-110 transition-transform origin-left">4</div>
                </div>
            </div>
            <div class="glass-card p-6 transition-all duration-300 hover:shadow-2xl hover:scale-105 group border border-white/40 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 to-rose-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative z-10">
                    <h3 class="mb-2 text-sm font-semibold text-gray-700 group-hover:text-red-700 transition-colors">Rejeitadas</h3>
                    <div class="mb-1 text-4xl font-bold text-red-600 group-hover:scale-110 transition-transform origin-left">3</div>
                </div>
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