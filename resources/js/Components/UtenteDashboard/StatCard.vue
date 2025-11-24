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
        <div class="text-xs text-gray-500 dark:text-gray-400">{{ description }}</div>
      </div>
      <div v-if="icon" :class="['text-3xl', iconBgClass, 'w-12 h-12 rounded-lg flex items-center justify-center']">
        {{ icon }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: String,
  value: [String, Number],
  description: String,
  icon: String,
  color: {
    type: String,
    default: 'blue'
  }
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