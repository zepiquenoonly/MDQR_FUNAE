<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true
    },
    label: {
        type: String,
        default: null
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    }
});

const statusConfig = computed(() => {
    const configs = {
        submitted: {
            label: 'Submetida',
            color: 'bg-blue-100 text-blue-800 border-blue-200',
            icon: '📝'
        },
        under_review: {
            label: 'Em Análise',
            color: 'bg-yellow-100 text-yellow-800 border-yellow-200',
            icon: '🔍'
        },
        assigned: {
            label: 'Atribuída',
            color: 'bg-purple-100 text-purple-800 border-purple-200',
            icon: '👤'
        },
        in_progress: {
            label: 'Em Andamento',
            color: 'bg-indigo-100 text-indigo-800 border-indigo-200',
            icon: '⚙️'
        },
        pending_approval: {
            label: 'Pendente de Aprovação',
            color: 'bg-orange-100 text-orange-800 border-orange-200',
            icon: '⏳'
        },
        resolved: {
            label: 'Resolvida',
            color: 'bg-green-100 text-green-800 border-green-200',
            icon: '✅'
        },
        rejected: {
            label: 'Rejeitada',
            color: 'bg-red-100 text-red-800 border-red-200',
            icon: '❌'
        }
    };

    return configs[props.status] || {
        label: props.status,
        color: 'bg-gray-100 text-gray-800 border-gray-200',
        icon: '•'
    };
});

const displayLabel = computed(() => props.label || statusConfig.value.label);

const sizeClasses = computed(() => {
    const sizes = {
        sm: 'text-xs px-2 py-0.5',
        md: 'text-sm px-3 py-1',
        lg: 'text-base px-4 py-1.5'
    };
    return sizes[props.size];
});
</script>

<template>
    <span :class="[
        'inline-flex items-center gap-1.5 rounded-full border font-medium',
        statusConfig.color,
        sizeClasses
    ]">
        <span>{{ statusConfig.icon }}</span>
        <span>{{ displayLabel }}</span>
    </span>
</template>
