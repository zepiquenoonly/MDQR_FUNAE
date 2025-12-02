<template>
  <div class="glass-card p-6 hover:shadow-2xl transition-all duration-300 border border-white/40">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-bold text-gray-900">{{ title }}</h3>
      <div class="px-3 py-1 text-xs font-semibold bg-primary-50 text-primary-700 rounded-full">
        {{ period }}
      </div>
    </div>

    <div class="space-y-4">
      <div v-for="(item, index) in data" :key="index" class="group">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-700 group-hover:text-primary-700 transition-colors">
            {{ item.label }}
          </span>
          <span class="text-sm font-bold text-gray-900">
            {{ item.value }}
          </span>
        </div>
        <div class="relative h-8 bg-gray-100 rounded-lg overflow-hidden">
          <div
            :class="['h-full rounded-lg transition-all duration-700 ease-out', item.color || 'bg-gradient-to-r from-primary-500 to-orange-600']"
            :style="{ width: `${item.percentage}%` }"
          >
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer"></div>
          </div>
          <div class="absolute inset-0 flex items-center px-3">
            <span class="text-xs font-semibold text-white drop-shadow-lg">{{ item.percentage }}%</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: 'Estatísticas'
  },
  period: {
    type: String,
    default: 'Este Mês'
  },
  data: {
    type: Array,
    default: () => [
      { label: 'Reclamações', value: 45, percentage: 75, color: 'bg-gradient-to-r from-primary-500 to-orange-600' },
      { label: 'Queixas', value: 32, percentage: 53, color: 'bg-gradient-to-r from-blue-500 to-indigo-600' },
      { label: 'Sugestões', value: 18, percentage: 30, color: 'bg-gradient-to-r from-green-500 to-emerald-600' }
    ]
  }
})
</script>

<style scoped>
@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}

.animate-shimmer {
  animation: shimmer 2s infinite;
}
</style>
