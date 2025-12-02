<template>
  <div class="glass-card hover:scale-[1.03] cursor-pointer transition-all duration-300 hover:shadow-2xl group relative overflow-hidden border border-white/40">
    <!-- Decorative gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-primary-500/5 via-transparent to-orange-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

    <div class="flex items-center justify-between relative z-10">
      <div class="flex-1">
        <h3 class="text-gray-700 text-sm font-semibold mb-2 group-hover:text-primary-700 transition-colors">{{ title }}</h3>
        <div class="text-3xl font-bold gradient-text mt-2 group-hover:scale-110 transition-transform origin-left duration-300">{{ value }}</div>
        <p class="text-gray-600 text-xs sm:text-sm mt-1">{{ description }}</p>
      </div>

      <!-- Icon with Gradient Background -->
      <div class="flex flex-col items-end">
        <div :class="['w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center shadow-glass border transition-all duration-300 group-hover:scale-110 group-hover:rotate-6', iconBgClass, iconBorderClass]">
          <component v-if="isComponent && icon" :is="icon" :class="['w-6 h-6 sm:w-7 sm:h-7 transition-transform group-hover:scale-110', iconColorClass]" />
          <span v-else-if="icon" :class="['text-lg sm:text-xl transition-transform group-hover:scale-110', iconColorClass]">{{ icon }}</span>
        </div>
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
  icon: { type: [String, Object], default: null },
  color: {
    type: String,
    default: 'blue'
  }
})

const isComponent = computed(() => typeof props.icon === 'object' || typeof props.icon === 'function')

const iconBgClass = computed(() => {
  const colors = {
    blue: 'bg-gradient-to-br from-blue-50 to-indigo-50',
    green: 'bg-gradient-to-br from-green-50 to-emerald-50',
    orange: 'bg-gradient-to-br from-primary-50 to-orange-50',
    yellow: 'bg-gradient-to-br from-yellow-50 to-amber-50',
    red: 'bg-gradient-to-br from-red-50 to-rose-50',
    purple: 'bg-gradient-to-br from-purple-50 to-violet-50'
  }
  return colors[props.color] || colors.orange
})

const iconBorderClass = computed(() => {
  const colors = {
    blue: 'border-blue-200',
    green: 'border-green-200',
    orange: 'border-primary-200',
    yellow: 'border-yellow-200',
    red: 'border-red-200',
    purple: 'border-purple-200'
  }
  return colors[props.color] || colors.orange
})

const iconColorClass = computed(() => {
  const colors = {
    blue: 'text-blue-600',
    green: 'text-green-600',
    orange: 'text-primary-600',
    yellow: 'text-yellow-600',
    red: 'text-red-600',
    purple: 'text-purple-600'
  }
  return colors[props.color] || colors.orange
})
</script>
