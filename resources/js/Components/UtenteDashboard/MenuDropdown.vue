<template>
  <div>
    <div :class="[
      'flex items-center gap-3 px-5 py-3 cursor-pointer transition-all duration-200 border-l-4',
      isActive || isOpen
        ? 'bg-gradient-to-r from-primary-50 to-orange-50 text-primary-700 border-primary-500'
        : 'border-transparent hover:bg-primary-50/50 text-gray-700 hover:text-primary-600'
    ]" @click="handleClick">
      <component :is="icon" :class="['flex-shrink-0 w-5 h-5', isActive || isOpen ? 'text-primary-600' : 'text-gray-600']" />
      <span class="flex-1 text-sm font-medium">
        {{ text }}
      </span>

      <!-- Badge -->
      <span v-if="badge" class="bg-gradient-to-r from-primary-500 to-orange-600 text-white rounded-full px-2 py-1 text-xs font-bold">
        {{ badge }}
      </span>

      <!-- Arrow -->
      <ChevronRightIcon :class="[
        'transition-all duration-300 w-4 h-4',
        isOpen ? 'rotate-90' : '',
        isActive || isOpen ? 'text-primary-600' : 'text-gray-500'
      ]" />
    </div>

    <!-- Dropdown Items -->
    <div v-if="isOpen" class="bg-primary-50/30 overflow-hidden transition-all duration-300">
      <a v-for="(item, index) in items" :key="index" :class="[
        'flex items-center gap-3 py-2.5 px-5 pl-12 cursor-pointer transition-all duration-200 border-l-4',
        isItemActive(item)
          ? 'bg-primary-100 text-primary-700 border-primary-500'
          : 'border-transparent hover:bg-primary-50 text-gray-700 hover:text-primary-600'
      ]" @click="handleItemClick(item)">
        <component :is="item.icon" :class="['flex-shrink-0 w-4 h-4', isItemActive(item) ? 'text-primary-600' : 'text-gray-600']" />
        <span class="text-sm">
          {{ item.text }}
        </span>
      </a>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { ChevronRightIcon } from '@heroicons/vue/24/outline'
import { useDashboard } from '@/composables/useDashboard'

const props = defineProps({
  icon: Object,
  text: String,
  badge: [String, Number],
  isActive: {
    type: Boolean,
    default: false
  },
  items: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['item-clicked'])

const { activeDropdown } = useDashboard()

const isOpen = ref(false)

// Verificar se um item específico está ativo
const isItemActive = (item) => {
  return activeDropdown.value === item.id
}

// Toggle dropdown
const handleClick = () => {
  isOpen.value = !isOpen.value
}

// Handle item click
const handleItemClick = (item) => {
  emit('item-clicked', item.id)
  isOpen.value = false
}
</script>