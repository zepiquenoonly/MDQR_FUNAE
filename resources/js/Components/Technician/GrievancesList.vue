<template>
    <div
        class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
        <!-- Header compacto -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4 sm:mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary">Reclamações Atribuídas</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Clique para gerenciar detalhes</p>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Total: <span class="font-semibold text-gray-900 dark:text-dark-text-primary">{{ grievances.length }}</span>
            </p>
        </div>

        <!-- Filtros -->
        <div class="space-y-3 mb-4 sm:mb-6">
            <h4 class="font-semibold text-gray-800 dark:text-dark-text-primary text-sm">Filtrar Por:</h4>
            <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:gap-3">
                <div class="flex flex-col">
                    <label for="priority" class="mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                        Prioridade
                    </label>
                    <select id="priority" v-model="localFilters.priority"
                        class="px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-xs focus:border-orange-500 focus:ring-1 focus:ring-orange-200 transition-all duration-200 appearance-none bg-white dark:bg-dark-accent cursor-pointer dark:text-dark-text-primary"
                        @change="handleFilterChange">
                        <option value="">Todos</option>
                        <option value="high">Alta</option>
                        <option value="medium">Média</option>
                        <option value="low">Baixa</option>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label for="status" class="mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                        Estado
                    </label>
                    <select id="status" v-model="localFilters.status"
                        class="px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-xs focus:border-orange-500 focus:ring-1 focus:ring-orange-200 transition-all duration-200 dark:bg-dark-accent dark:text-dark-text-primary"
                        @change="handleFilterChange">
                        <option value="">Todos</option>
                        <option value="open">Aberto</option>
                        <option value="in_progress">Em Progresso</option>
                        <option value="pending_completion">Solicitado Conclusão</option>
                        <option value="closed">Concluído</option>
                    </select>
                </div>

                <div class="flex flex-col col-span-2 sm:col-span-1">
                    <label for="search" class="mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                        Pesquisar
                    </label>
                    <input id="search" v-model="localFilters.search" type="text" placeholder="Ref., categoria..."
                        class="px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-xs focus:border-orange-500 focus:ring-1 focus:ring-orange-200 transition-all duration-200 dark:bg-dark-accent dark:text-dark-text-primary"
                        @keyup.enter="handleFilterChange" />
                </div>
            </div>
        </div>

        <!-- Container com rolagem interna para lista de reclamações -->
        <div class="grievances-scroll-container">
            <!-- Grievances List -->
            <div class="space-y-2">
                <Link
                    v-for="grievance in grievances"
                    :key="grievance.id"
                    :href="grievanceDetailUrl(grievance.id)"
                    class="block rounded-xl border bg-white dark:bg-dark-accent p-3 sm:p-4 text-left shadow-sm transition hover:border-brand hover:shadow-md hover:bg-orange-50 dark:hover:bg-orange-900/5 border-gray-200 dark:border-gray-600">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary">{{ grievance.reference_number }}</p>
                            <StatusBadge :status="grievance.status" :label="grievance.status_label" size="sm" />
                        </div>
                        <span
                            class="rounded-full px-2 py-1 text-xs font-semibold"
                            :class="priorityBadgeClass(grievance.priority)">
                            {{ priorityLabel(grievance.priority) }}
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-2">
                        {{ grievance.excerpt }}
                    </p>
                    <div class="flex flex-wrap items-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                        <span>{{ formatDate(grievance.submitted_at) }}</span>
                        <span v-if="grievance.district">{{ grievance.district }}</span>
                    </div>
                </Link>
            </div>

            <!-- Empty State -->
            <div v-if="grievances.length === 0" class="text-center py-8">
                <DocumentMagnifyingGlassIcon class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4" />
                <p class="text-gray-500 dark:text-gray-400 text-sm">Nenhuma reclamação encontrada</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Link } from '@inertiajs/vue3'
import { DocumentMagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import StatusBadge from '@/Components/Grievance/StatusBadge.vue'

const props = defineProps({
    grievances: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(['update:filters', 'select-grievance'])

const localFilters = ref({
    status: props.filters.status || '',
    priority: props.filters.priority || '',
    search: props.filters.search || '',
})

watch(() => props.filters, (newFilters) => {
    localFilters.value = {
        status: newFilters.status || '',
        priority: newFilters.priority || '',
        search: newFilters.search || '',
    }
}, { deep: true })

const handleFilterChange = () => {
    emit('update:filters', localFilters.value)
}

const grievanceDetailUrl = (grievanceId) => {
    return `/technician/grievances/${grievanceId}`
}

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
        low: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400',
        medium: 'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
        high: 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400',
    }
    return map[priority] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'
}

const formatDate = (dateString) => {
    if (!dateString) return 'N/D'
    return new Date(dateString).toLocaleDateString('pt-PT', {
        day: '2-digit',
        month: 'short',
    })
}
</script>

<style scoped>
.grievances-scroll-container {
    max-height: 60vh;
    overflow-y: auto;
    overflow-x: hidden;
}

.grievances-scroll-container::-webkit-scrollbar {
    width: 6px;
}

.grievances-scroll-container::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.grievances-scroll-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.grievances-scroll-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.dark .grievances-scroll-container::-webkit-scrollbar-track {
    background: #374151;
}

.dark .grievances-scroll-container::-webkit-scrollbar-thumb {
    background: #6b7280;
}

.dark .grievances-scroll-container::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

@media (min-width: 640px) {
    .grievances-scroll-container {
        max-height: 65vh;
    }
}

@media (min-width: 768px) {
    .grievances-scroll-container {
        max-height: 70vh;
    }
}

@media (min-width: 1024px) {
    .grievances-scroll-container {
        max-height: 500px;
    }
}

@media (min-width: 1280px) {
    .grievances-scroll-container {
        max-height: 600px;
    }
}
</style>
