<template>
  <div 
    :class="[
      'bg-white rounded-xl shadow-sm p-6 border-l-4',
      borderColor,
      totalStyle ? 'bg-gradient-to-r from-blue-50 to-white' : ''
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
  borderColor: String,
  totalStyle: {
    type: Boolean,
    default: false
  }
})
</script>