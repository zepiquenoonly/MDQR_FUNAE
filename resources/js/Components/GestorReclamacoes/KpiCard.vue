<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-gray-500 text-sm font-medium">{{ title }}</h3>
                <div class="text-3xl font-bold text-black mt-2">{{ value }}</div>
                <p class="text-gray-400 text-sm mt-1">{{ description }}</p>
            </div>

            <!-- Icon & Trend -->
            <div class="flex flex-col items-end">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <component :is="dynamicIcon" class="w-6 h-6 text-orange-500" />
                </div>

                <!-- Trend Indicator -->
                <div v-if="trend" :class="[
                    'flex items-center gap-1 mt-2 text-xs font-medium',
                    trend === 'up' ? 'text-green-500' :
                        trend === 'down' ? 'text-red-500' : 'text-gray-500'
                ]">
                    <ArrowTrendingUpIcon v-if="trend === 'up'" class="w-4 h-4" />
                    <ArrowTrendingDownIcon v-else-if="trend === 'down'" class="w-4 h-4" />
                    <MinusIcon v-else class="w-4 h-4" />
                    <span>{{ trendText }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import {
    ExclamationTriangleIcon,
    ClockIcon,
    ExclamationCircleIcon,
    CheckCircleIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    MinusIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    title: String,
    value: [String, Number],
    description: String,
    icon: String,
    trend: String // 'up', 'down', 'stable'
})

// Mapeamento de ícones
const iconMap = {
    ExclamationTriangleIcon,
    ClockIcon,
    ExclamationCircleIcon,
    CheckCircleIcon
}

const dynamicIcon = computed(() => {
    return iconMap[props.icon] || ExclamationTriangleIcon
})

const trendText = computed(() => {
    const texts = {
        up: '+12%',
        down: '-5%',
        stable: '0%'
    }
    return texts[props.trend] || ''
})
</script>