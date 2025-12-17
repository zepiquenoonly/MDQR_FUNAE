<template>
    <div class="bg-gradient-to-br from-gray-50 to-gray-100/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-gray-200/50 shadow-lg shadow-primary-500/5 p-6 sm:p-8">
        <!-- Header - Glassmorphism -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6 sm:mb-8">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-primary-500/20 to-orange-600/20 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/30 shadow-lg shadow-primary-500/10">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Reclamações Atribuídas</h3>
                    <p class="text-sm text-gray-600">Clique para gerenciar detalhes</p>
                </div>
            </div>
            <div class="bg-white/50 backdrop-blur-sm px-4 py-2 rounded-2xl border border-white/40 shadow-lg">
                <p class="text-sm text-gray-600">
                    Total: <span class="font-bold text-gray-900">{{ grievances.length }}</span>
                </p>
            </div>
        </div>

        <!-- Filters - Glassmorphism Cards -->
        <div class="space-y-6 mb-6 sm:mb-8">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-gradient-to-br from-orange-500/20 to-amber-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </div>
                <h4 class="font-bold text-gray-800 text-base">Filtrar Por:</h4>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Priority Filter -->
                <div class="bg-white/70 backdrop-blur-md p-4 rounded-2xl border border-white/40 shadow-lg shadow-orange-500/5 hover:shadow-xl hover:shadow-orange-500/10 transition-all duration-300 hover:-translate-y-0.5">
                    <label for="priority" class="block text-sm font-semibold text-gray-700 mb-2">
                        Prioridade
                    </label>
                    <select id="priority" v-model="localFilters.priority"
                        class="w-full px-3 py-2 bg-white/80 backdrop-blur-sm border border-white/50 rounded-xl text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-200/50 transition-all duration-200 appearance-none cursor-pointer"
                        @change="handleFilterChange">
                        <option value="">Todos</option>
                        <option value="high">Alta</option>
                        <option value="medium">Média</option>
                        <option value="low">Baixa</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="bg-white/70 backdrop-blur-md p-4 rounded-2xl border border-white/40 shadow-lg shadow-blue-500/5 hover:shadow-xl hover:shadow-blue-500/10 transition-all duration-300 hover:-translate-y-0.5">
                    <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                        Estado
                    </label>
                    <select id="status" v-model="localFilters.status"
                        class="w-full px-3 py-2 bg-white/80 backdrop-blur-sm border border-white/50 rounded-xl text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-200/50 transition-all duration-200 appearance-none cursor-pointer"
                        @change="handleFilterChange">
                        <option value="">Todos</option>
                        <option value="open">Aberto</option>
                        <option value="in_progress">Em Progresso</option>
                        <option value="pending_completion">Solicitado Conclusão</option>
                        <option value="closed">Concluído</option>
                    </select>
                </div>

                <!-- Search Filter -->
                <div class="bg-white/70 backdrop-blur-md p-4 rounded-2xl border border-white/40 shadow-lg shadow-purple-500/5 hover:shadow-xl hover:shadow-purple-500/10 transition-all duration-300 hover:-translate-y-0.5">
                    <label for="search" class="block text-sm font-semibold text-gray-700 mb-2">
                        Pesquisar
                    </label>
                    <input id="search" v-model="localFilters.search" type="text" placeholder="Ref., categoria..."
                        class="w-full px-3 py-2 bg-white/80 backdrop-blur-sm border border-white/50 rounded-xl text-sm focus:border-purple-400 focus:ring-2 focus:ring-purple-200/50 transition-all duration-200"
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
                    class="block bg-gradient-to-br from-white to-gray-50/80 backdrop-blur-md rounded-2xl p-4 sm:p-5 text-left shadow-lg border border-gray-200/60 hover:shadow-xl hover:shadow-primary-500/15 transition-all duration-300 hover:-translate-y-1 hover:border-primary-300/60 group">
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

            <!-- Empty State - Glassmorphism -->
            <div v-if="grievances.length === 0" class="text-center py-12">
                <div class="bg-white/50 backdrop-blur-md w-20 h-20 rounded-3xl flex items-center justify-center border border-white/40 shadow-lg shadow-gray-500/5 mx-auto mb-6">
                    <DocumentMagnifyingGlassIcon class="w-10 h-10 text-gray-400" />
                </div>
                <p class="text-gray-500 text-base font-medium">Nenhuma reclamação encontrada</p>
                <p class="text-gray-400 text-sm mt-1">Tente ajustar os filtros de pesquisa</p>
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
