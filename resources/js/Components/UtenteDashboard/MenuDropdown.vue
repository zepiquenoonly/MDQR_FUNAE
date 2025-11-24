<template>
  <div class="relative">
    <div :class="[
      'flex items-center gap-3 px-5 py-3 text-white cursor-pointer transition-all duration-200 border-l-3 border-transparent hover:bg-white hover:bg-opacity-10',
      isActive || isOpen ? 'bg-white bg-opacity-20' : ''
    ]" @click="handleClick" @mouseenter="onMouseEnter" @mouseleave="onMouseLeave">
      <component :is="icon" class="flex-shrink-0 w-5 h-5 text-white" />
      <span :class="[
        'transition-opacity duration-300 flex-1 text-sm font-medium',
        isCollapsed ? 'opacity-0' : 'opacity-100'
      ]">
        {{ text }}
      </span>

      <!-- Badge -->
      <span v-if="badge" :class="[
        'bg-white text-brand rounded-full px-2 py-1 text-xs font-bold transition-opacity duration-300',
        isCollapsed ? 'opacity-0' : 'opacity-100'
      ]">
        {{ badge }}
      </span>

      <!-- Arrow -->
      <ChevronRightIcon :class="[
        'text-white text-opacity-70 transition-all duration-300 w-4 h-4',
        isCollapsed ? 'opacity-0' : 'opacity-100',
        isOpen ? 'rotate-90' : ''
      ]" />
    </div>

    <!-- Dropdown Items - Normal (quando sidebar aberta) -->
    <div v-if="!isCollapsed && isOpen" :class="[
      'bg-white bg-opacity-10 overflow-hidden transition-all duration-300'
    ]">
      <a v-for="(item, index) in items" :key="index" :class="[
        'flex items-center gap-3 py-2.5 px-5 pl-12 text-white cursor-pointer transition-all duration-200 border-l-3',
        isItemActive(item) ? 'bg-white bg-opacity-20 border-white' : 'border-transparent hover:bg-white hover:bg-opacity-20'
      ]" @click="handleItemClick(item)">
        <component :is="item.icon" class="flex-shrink-0 w-4 h-4 text-white" />
        <span class="text-sm">
          {{ item.text }}
        </span>
      </a>
    </div>

    <!-- Dropdown Items - Popup (quando sidebar fechada) -->
    <div v-if="isCollapsed && showPopup"
      class="absolute left-full top-0 ml-1 bg-orange-400 rounded-lg shadow-lg py-2 min-w-48 z-50"
      @mouseenter="onPopupEnter" @mouseleave="onPopupLeave">
      <div class="px-4 py-2 border-b border-orange-300">
        <span class="text-white font-semibold text-sm">{{ text }}</span>
      </div>
      <a v-for="(item, index) in items" :key="index" :class="[
        'flex items-center gap-3 px-4 py-2 text-white cursor-pointer transition-colors duration-200',
        isItemActive(item) ? 'bg-orange-500' : 'hover:bg-orange-500'
      ]" @click="handleItemClick(item)">
        <component :is="item.icon" class="flex-shrink-0 w-4 h-4 text-white" />
        <span class="text-sm">
          {{ item.text }}
        </span>
      </a>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { ChevronRightIcon } from '@heroicons/vue/24/outline'
import { useDashboardState } from '@/Components/UtenteDashboard/Composables/useDashboardState.js'

const props = defineProps({
  icon: Object,
  text: String,
  badge: [String, Number],
  isCollapsed: Boolean,
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

const { activeDropdown } = useDashboardState()

const showPopup = ref(false)
const isOpen = ref(false)
let popupTimer = null

// Verificar se um item específico está ativo
const isItemActive = (item) => {
  return activeDropdown.value === item.id
}

// Toggle dropdown
const handleClick = () => {
  if (!props.isCollapsed) {
    isOpen.value = !isOpen.value
  }
}

// Handle item click
const handleItemClick = (item) => {
  console.log('MenuDropdown - item clicked:', item.id)
  emit('item-clicked', item.id)
  isOpen.value = false
  showPopup.value = false
}

// Mouse handlers para popup quando collapsed
const onMouseEnter = () => {
  if (props.isCollapsed) {
    clearTimeout(popupTimer)
    popupTimer = setTimeout(() => {
      showPopup.value = true
    }, 200)
  }
}

const onMouseLeave = () => {
  if (props.isCollapsed) {
    clearTimeout(popupTimer)
    popupTimer = setTimeout(() => {
      showPopup.value = false
    }, 200)
  }
}

const onPopupEnter = () => {
  clearTimeout(popupTimer)
}

const onPopupLeave = () => {
  clearTimeout(popupTimer)
  showPopup.value = false
}
</script>