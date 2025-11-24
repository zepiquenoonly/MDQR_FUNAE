<template>
    <div class="flex items-center justify-between p-4 bg-gradient-to-br rounded-lg shadow-sm"
        :class="gradientClass">
        <div class="flex-1">
            <p class="text-sm font-medium opacity-90">{{ title }}</p>
            <div class="flex items-baseline gap-2 mt-2">
                <p class="text-3xl font-bold">{{ formattedValue }}</p>
                <span v-if="percentage" class="text-sm font-medium opacity-75">
                    ({{ percentage }}%)
                </span>
            </div>
        </div>
        <div class="flex-shrink-0">
            <component :is="iconComponent" class="w-10 h-10 opacity-80" />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import {
    ClipboardDocumentListIcon,
    CheckCircleIcon,
    ClockIcon,
    ArrowPathIcon,
    ChartBarIcon,
    FireIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    value: {
        type: [Number, String],
        required: true
    },
    icon: {
        type: String,
        default: 'clipboard'
    },
    color: {
        type: String,
        default: 'blue'
    },
    percentage: {
        type: Number,
        default: null
    }
})

const colorMap = {
    blue: 'from-blue-500 to-blue-600 text-white',
    green: 'from-green-500 to-green-600 text-white',
    yellow: 'from-yellow-500 to-yellow-600 text-white',
    purple: 'from-purple-500 to-purple-600 text-white',
    orange: 'from-orange-500 to-orange-600 text-white',
    teal: 'from-teal-500 to-teal-600 text-white',
}

const iconMap = {
    clipboard: ClipboardDocumentListIcon,
    check: CheckCircleIcon,
    clock: ClockIcon,
    progress: ArrowPathIcon,
    timer: FireIcon,
    chart: ChartBarIcon,
}

const gradientClass = computed(() => colorMap[props.color] || colorMap.blue)
const iconComponent = computed(() => iconMap[props.icon] || iconMap.clipboard)
const formattedValue = computed(() => {
    if (typeof props.value === 'number') {
        return props.value.toLocaleString('pt-MZ')
    }
    return props.value
})
</script>
