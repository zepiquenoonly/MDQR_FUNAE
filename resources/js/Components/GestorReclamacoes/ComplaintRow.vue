<template>
    <div :class="[
        'bg-white border rounded-xl p-4 transition-all duration-200 cursor-pointer',
        selected
            ? 'border-orange-500 bg-orange-50 shadow-md'
            : 'border-gray-200 hover:border-orange-300 hover:shadow-sm'
    ]" @click="handleRowClick">
        <div class="flex items-start space-x-4">
            <!-- User Avatar -->
            <div
                class="w-10 h-10 bg-brand rounded-lg flex items-center justify-center text-white font-semibold flex-shrink-0">
                {{ getUserInitials(complaint.user?.name || 'Utente') }}
            </div>

            <!-- Complaint Content -->
            <div class="flex-1 min-w-0">
                <!-- Header -->
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <h4 class="font-semibold text-gray-800 text-sm truncate">
                        {{ complaint.title }} — {{ complaint.user?.name || 'Utente' }}
                    </h4>
                    <PriorityBadge :priority="complaint.priority" />
                    <StatusBadge :status="complaint.status" />
                </div>

                <!-- Description -->
                <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                    {{ complaint.description }}
                </p>

                <!-- Metadata -->
                <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500">
                    <span class="flex items-center space-x-1">
                        <HashtagIcon class="w-3 h-3" />
                        <span>#{{ complaint.id }}</span>
                    </span>
                    <span class="flex items-center space-x-1">
                        <TagIcon class="w-3 h-3" />
                        <span>{{ complaint.category }}</span>
                    </span>
                    <span class="flex items-center space-x-1">
                        <DocumentTextIcon class="w-3 h-3" />
                        <span>{{ getTypeText(complaint.type) }}</span>
                    </span>
                    <span class="flex items-center space-x-1">
                        <CalendarIcon class="w-3 h-3" />
                        <span>{{ formatDate(complaint.created_at) }}</span>
                    </span>
                    <span class="flex items-center space-x-1">
                        <UserIcon class="w-3 h-3" />
                        <span>{{ complaint.technician?.name || 'Não atribuído' }}</span>
                    </span>
                </div>

                <!-- Actions -->
                <div class="mt-3">
                    <button @click.stop="handleDetailsClick"
                        class="flex items-center space-x-1 px-3 py-1.5 bg-brand text-white text-xs rounded-lg font-medium hover:bg-orange-600 transition-all duration-200 shadow-sm hover:shadow-md">
                        <EyeIcon class="w-3 h-3" />
                        <span>Ver detalhes</span>
                    </button>
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