<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Reclamações Recentes</h3>
            <span class="text-gray-500 text-sm">Ordenado por mais recente</span>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3 mb-6">
            <select v-model="localFilters.priority"
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200">
                <option value="">Prioridade: Todos</option>
                <option value="high">Alta</option>
                <option value="medium">Média</option>
                <option value="low">Baixa</option>
            </select>

            <select v-model="localFilters.status"
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200">
                <option value="">Status: Todos</option>
                <option value="open">Aberto</option>
                <option value="in_progress">Em Progresso</option>
                <option value="pending_completion">Solicitado Conclusão</option>
                <option value="closed">Concluído</option>
            </select>

            <select v-model="localFilters.category"
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200">
                <option value="">Categoria: Todas</option>
                <option value="infra">Infraestrutura</option>
                <option value="service">Serviço</option>
                <option value="attendance">Atendimento</option>
            </select>

            <select v-model="localFilters.type"
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200">
                <option value="">Tipo: Todos</option>
                <option value="complaint">Reclamação</option>
                <option value="suggestion">Sugestão</option>
            </select>
        </div>

        <!-- Complaints List -->
        <div class="space-y-3">
            <ComplaintRow v-for="complaint in complaints" :key="complaint.id" :complaint="complaint"
                :selected="selectedComplaint?.id === complaint.id" @select="handleSelectComplaint" />
        </div>

        <!-- Empty State -->
        <div v-if="complaints.length === 0" class="text-center py-8">
            <DocumentMagnifyingGlassIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <p class="text-gray-500">Nenhuma reclamação encontrada</p>
        </div>

        <!-- Footer -->
        <div class="flex justify-between items-center mt-6">
            <span class="text-gray-500 text-sm">
                Mostrando {{ complaints.length }} reclamações
            </span>
            <div class="flex gap-3">
                <button @click="handleBulkAssign"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 text-sm font-medium hover:bg-gray-50 transition-all duration-200">
                    Atribuição Automática
                </button>
                <button @click="handleExport"
                    class="px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600 transition-all duration-200 shadow-md hover:shadow-lg">
                    Exportar Selecionados
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { DocumentMagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import ComplaintRow from './ComplaintRow.vue'

const props = defineProps({
    complaints: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(['update:filters', 'select-complaint'])

const localFilters = ref({ ...props.filters })
const selectedComplaint = ref(null)

// Watcher para filtros
watch(localFilters, (newFilters) => {
    emit('update:filters', newFilters)
}, { deep: true })

const handleSelectComplaint = (complaint) => {
    selectedComplaint.value = complaint
    emit('select-complaint', complaint)
}

const handleBulkAssign = async () => {
    try {
        await router.post(route('complaints.bulk-assign'), {}, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                // Feedback será mostrado via notificação do backend
            }
        })
    } catch (error) {
        console.error('Error in bulk assign:', error)
    }
}

const handleExport = () => {
    // Implementar exportação
    const queryParams = new URLSearchParams(localFilters.value).toString()
    window.open(route('complaints.export') + '?' + queryParams, '_blank')
}
</script>