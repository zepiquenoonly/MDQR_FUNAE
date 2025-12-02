<template>
  <div
    :class="[
      'bg-white dark:bg-dark-secondary rounded-xl p-6 shadow-sm hover:shadow-lg transition-all duration-300 border-l-4',
      borderColorClass
    ]"
  >
    <div class="flex items-start justify-between">
      <div class="flex-1">
        <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">{{ title }}</h3>
        <div :class="['text-3xl font-bold mb-1', textColorClass]">{{ value }}</div>
        <div v-if="description" class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ description }}</div>
        <div v-if="trend" class="flex items-center text-xs">
          <component :is="trendIcon" class="h-4 w-4 mr-1" :class="trendColor" />
          <span :class="trendColor">{{ trendText }}</span>
        </div>
      </div>
      <div v-if="icon" :class="['text-3xl', iconBgClass, 'w-12 h-12 rounded-lg flex items-center justify-center']">
        <component :is="dynamicIcon" class="h-6 w-6 text-current" />
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
    trend: String, // 'up', 'down', 'stable'
    color: {
        type: String,
        default: 'blue'
    }
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

const borderColorClass = computed(() => {
  const colors = {
    blue: 'border-blue-500',
    green: 'border-green-500',
    orange: 'border-orange-500',
    yellow: 'border-yellow-500',
    red: 'border-red-500',
    purple: 'border-purple-500'
  }
  return colors[props.color] || colors.blue
})

const textColorClass = computed(() => {
  const colors = {
    blue: 'text-blue-600',
    green: 'text-green-600',
    orange: 'text-orange-600',
    yellow: 'text-yellow-600',
    red: 'text-red-600',
    purple: 'text-purple-600'
  }
  return colors[props.color] || colors.blue
})

const iconBgClass = computed(() => {
  const colors = {
    blue: 'bg-blue-50',
    green: 'bg-green-50',
    orange: 'bg-orange-50',
    yellow: 'bg-yellow-50',
    red: 'bg-red-50',
    purple: 'bg-purple-50'
  }
  return colors[props.color] || colors.blue
})
</script>
