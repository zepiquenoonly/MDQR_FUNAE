<template>
    <div :class="[
        'bg-white dark:bg-dark-secondary border rounded-xl p-3 sm:p-4 transition-all duration-200 cursor-pointer complaint-row',
        selected
            ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20 shadow-md'
            : 'border-gray-200 dark:border-gray-700 hover:border-orange-300 dark:hover:border-orange-500 hover:shadow-sm'
    ]" @click="handleRowClick">
        <div class="flex items-start gap-3 sm:gap-4">
            <!-- User Avatar -->
            <div
                class="w-8 h-8 sm:w-10 sm:h-10 bg-brand rounded-lg flex items-center justify-center text-white font-semibold flex-shrink-0 text-xs sm:text-sm">
                {{ getUserInitials(complaint.user?.name || 'Utente') }}
            </div>

            <!-- Complaint Content -->
            <div class="flex-1 min-w-0 overflow-hidden">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:flex-wrap gap-1 sm:gap-2 mb-2">
                    <h4 class="font-semibold text-gray-800 dark:text-dark-text-primary text-sm truncate flex-1 min-w-0">
                        {{ complaint.title }}
                    </h4>
                    <div class="flex items-center gap-1 sm:gap-2 flex-wrap">
                        <PriorityBadge :priority="complaint.priority" />
                        <StatusBadge :status="complaint.status" />
                    </div>
                </div>

                <!-- User Info -->
                <div class="mb-2">
                    <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">
                        {{ complaint.user?.name || 'Utente' }}
                    </span>
                </div>

                <!-- Description -->
                <p class="text-gray-600 dark:text-gray-400 text-xs sm:text-sm mb-3 line-clamp-2 leading-relaxed">
                    {{ complaint.description }}
                </p>

                <!-- Metadata -->
                <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs text-gray-500 dark:text-gray-400">
                    <span class="flex items-center space-x-1 bg-gray-50 dark:bg-dark-accent px-2 py-1 rounded">
                        <HashtagIcon class="w-3 h-3 flex-shrink-0" />
                        <span class="truncate">#{{ complaint.id }}</span>
                    </span>
                    <span class="flex items-center space-x-1 bg-gray-50 dark:bg-dark-accent px-2 py-1 rounded">
                        <TagIcon class="w-3 h-3 flex-shrink-0" />
                        <span class="truncate">{{ complaint.category }}</span>
                    </span>
                    <span class="flex items-center space-x-1 bg-gray-50 dark:bg-dark-accent px-2 py-1 rounded">
                        <DocumentTextIcon class="w-3 h-3 flex-shrink-0" />
                        <span class="truncate">{{ getTypeText(complaint.type) }}</span>
                    </span>
                    <span class="flex items-center space-x-1 bg-gray-50 dark:bg-dark-accent px-2 py-1 rounded">
                        <CalendarIcon class="w-3 h-3 flex-shrink-0" />
                        <span class="truncate">{{ formatDate(complaint.created_at) }}</span>
                    </span>
                </div>

                <!-- Technician and Actions -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mt-3">
                    <div class="flex items-center space-x-1 text-xs text-gray-500 dark:text-gray-400">
                        <UserIcon class="w-3 h-3 flex-shrink-0" />
                        <span class="truncate">{{ complaint.technician?.name || 'Não atribuído' }}</span>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end">
                        <button @click.stop="handleDetailsClick"
                            class="flex items-center space-x-1 px-2 sm:px-3 py-1 bg-brand text-white text-xs rounded-lg font-medium hover:bg-orange-600 transition-all duration-200 shadow-sm hover:shadow-md flex-shrink-0">
                            <EyeIcon class="w-3 h-3 flex-shrink-0" />
                            <span class="hidden xs:inline">Ver detalhes</span>
                            <span class="xs:hidden">Detalhes</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {
    HashtagIcon,
    TagIcon,
    DocumentTextIcon,
    CalendarIcon,
    UserIcon,
    EyeIcon
} from '@heroicons/vue/24/outline'
import PriorityBadge from './PriorityBadge.vue'
import StatusBadge from './StatusBadge.vue'

const props = defineProps({
    complaint: {
        type: Object,
        required: true
    },
    selected: Boolean
})

const emit = defineEmits(['select', 'show-details'])

const getUserInitials = (user) => {
    if (!user) return 'U'
    return user.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
}

const getTypeText = (type) => {
    const types = {
        complaint: 'Reclamação',
        suggestion: 'Sugestão'
    }
    return types[type] || type
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('pt-BR')
}

const handleRowClick = () => {
    emit('select', props.complaint)
}

const handleDetailsClick = () => {
    emit('show-details', props.complaint)
}
</script>

<style scoped>
.complaint-row {
    min-height: 120px;
}

/* Melhorias de performance para rolagem */
.complaint-row {
    transform: translateZ(0);
    backface-visibility: hidden;
    perspective: 1000;
}

/* Ajustes para telas muito pequenas */
@media (max-width: 380px) {
    .complaint-row {
        min-height: 140px;
    }
}

/* Ajustes para telas médias */
@media (min-width: 768px) {
    .complaint-row {
        min-height: 130px;
    }
}

/* Ajustes para telas grandes */
@media (min-width: 1024px) {
    .complaint-row {
        min-height: 120px;
    }
}
</style>