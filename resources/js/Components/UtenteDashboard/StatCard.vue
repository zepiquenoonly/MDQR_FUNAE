<template>
  <div 
    :class="[
      'bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300',
      totalStyle ? 'bg-gradient-to-r from-blue-50 to-white ring-1 ring-blue-100' : ''
    ]"
  >
    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ title }}</h3>
    <div class="text-4xl font-bold text-gray-900 mb-1">{{ value }}</div>
    <div 
      :class="[
        'text-sm',
        totalStyle ? 'text-blue-600 font-semibold' : 'text-gray-500'
      ]"
    >
      {{ description }}
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  value: [String, Number],
  description: String,
  totalStyle: {
    type: Boolean,
    default: false
  }
})
</script>