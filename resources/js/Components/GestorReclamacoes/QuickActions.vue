<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Ações Rápidas</h3>

        <div class="space-y-4">
            <!-- Quick Filters -->
            <div class="space-y-2">
                <h4 class="font-medium text-gray-700 text-sm">Filtros Rápidos</h4>
                <div class="grid grid-cols-2 gap-2">
                    <button @click="applyQuickFilter('high')"
                        class="flex items-center space-x-2 p-3 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors duration-200">
                        <ExclamationTriangleIcon class="w-4 h-4" />
                        <span class="text-sm">Urgentes</span>
                    </button>
                    <button @click="applyQuickFilter('pending_completion')"
                        class="flex items-center space-x-2 p-3 bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-100 transition-colors duration-200">
                        <ClockIcon class="w-4 h-4" />
                        <span class="text-sm">Pendentes</span>
                    </button>
                    <button @click="applyQuickFilter('open')"
                        class="flex items-center space-x-2 p-3 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors duration-200">
                        <InboxIcon class="w-4 h-4" />
                        <span class="text-sm">Em Aberto</span>
                    </button>
                    <button @click="applyQuickFilter('in_progress')"
                        class="flex items-center space-x-2 p-3 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors duration-200">
                        <Cog6ToothIcon class="w-4 h-4" />
                        <span class="text-sm">Em Progresso</span>
                    </button>
                </div>
            </div>

            <!-- Selected Complaint Actions -->
            <div v-if="complaint" class="space-y-3 pt-4 border-t border-gray-200">
                <h4 class="font-medium text-gray-700 text-sm">Ações para #{{ complaint.id }}</h4>

                <button @click="addNote"
                    class="w-full flex items-center space-x-2 p-3 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <ChatBubbleLeftRightIcon class="w-4 h-4" />
                    <span class="text-sm">Adicionar Nota</span>
                </button>

                <button @click="requestUpdate"
                    class="w-full flex items-center space-x-2 p-3 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <ArrowPathIcon class="w-4 h-4" />
                    <span class="text-sm">Solicitar Atualização</span>
                </button>

                <button @click="generateReport"
                    class="w-full flex items-center space-x-2 p-3 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <DocumentChartBarIcon class="w-4 h-4" />
                    <span class="text-sm">Gerar Relatório</span>
                </button>
            </div>

            <!-- System Actions -->
            <div class="space-y-2 pt-4 border-t border-gray-200">
                <h4 class="font-medium text-gray-700 text-sm">Sistema</h4>

                <button @click="exportData"
                    class="w-full flex items-center space-x-2 p-3 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <ArrowDownTrayIcon class="w-4 h-4" />
                    <span class="text-sm">Exportar Dados</span>
                </button>

                <button @click="refreshData"
                    class="w-full flex items-center space-x-2 p-3 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <ArrowPathIcon class="w-4 h-4" />
                    <span class="text-sm">Atualizar Dados</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import {
    PlusIcon,
    ExclamationTriangleIcon,
    ClockIcon,
    InboxIcon,
    Cog6ToothIcon,
    ChatBubbleLeftRightIcon,
    ArrowPathIcon,
    DocumentChartBarIcon,
    ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    complaint: Object
})

const createNewComplaint = () => {
    router.visit(route('complaints.create'))
}

const applyQuickFilter = (filter) => {
    router.reload({
        data: { status: filter },
        preserveState: true,
        replace: true
    })
}

const addNote = () => {
    if (!props.complaint) return
    // Implementar adição de nota
    console.log('Add note to complaint:', props.complaint.id)
}

const requestUpdate = () => {
    if (!props.complaint) return
    // Implementar solicitação de atualização
    console.log('Request update for complaint:', props.complaint.id)
}

const generateReport = () => {
    if (!props.complaint) return
    window.open(route('complaints.report', props.complaint.id), '_blank')
}

const exportData = () => {
    window.open(route('complaints.export'), '_blank')
}

const refreshData = () => {
    router.reload({
        preserveScroll: true,
        preserveState: true
    })
}
</script>