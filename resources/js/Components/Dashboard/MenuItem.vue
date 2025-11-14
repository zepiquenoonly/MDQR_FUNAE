<template>
  <a
    :class="[
      'flex items-center gap-3 px-5 py-3 text-gray-600 cursor-pointer transition-all duration-200 border-l-3',
      active 
        ? 'bg-blue-50 text-blue-600 border-blue-600' 
        : 'border-transparent hover:bg-gray-50 hover:text-gray-800'
    ]"
    @click="$emit('click')"
  >
    <span class="text-lg flex-shrink-0 w-5 text-center">{{ icon }}</span>
    <span 
      :class="[
        'transition-opacity duration-300 flex-1',
        isCollapsed ? 'opacity-0' : 'opacity-100'
      ]"
    >
      {{ text }}
    </span>
  </a>
</template>

<script setup>
defineProps({
  active: {
    type: Boolean,
    default: false
  },
  icon: String,
  text: String,
  isCollapsed: Boolean
})

defineEmits(['click'])
</script>