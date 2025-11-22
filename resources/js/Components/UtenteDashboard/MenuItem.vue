<template>
  <div class="relative">
    <a
      :class="[
        'flex items-center gap-3 px-5 py-3 text-white cursor-pointer transition-all duration-200 border-l-3',
        active 
          ? 'bg-white bg-opacity-20 text-white border-white' 
          : 'border-transparent hover:bg-white hover:bg-opacity-10'
      ]"
      @click="handleClick"
      @mouseenter="onMouseEnter"
      @mouseleave="onMouseLeave"
    >
      <component 
        :is="icon" 
        :class="[
          'flex-shrink-0 w-5 h-5',
          active ? 'text-white' : 'text-white'
        ]" 
      />
      <span 
        :class="[
          'transition-opacity duration-300 flex-1 text-sm font-medium',
          isCollapsed ? 'opacity-0' : 'opacity-100'
        ]"
      >
        {{ text }}
      </span>
    </a>

    <!-- Popup para quando sidebar estiver fechado -->
    <div
      v-if="isCollapsed && showPopup"
      class="absolute left-full top-0 ml-1 bg-orange-400 rounded-lg shadow-lg px-4 py-2 min-w-48 z-50"
      @mouseenter="onPopupEnter"
      @mouseleave="onPopupLeave"
    >
      <span class="text-white font-semibold text-sm">{{ text }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, inject } from 'vue'
import { useDashboardState } from '@/Components/UtenteDashboard/Composables/useDashboardState.js'

const props = defineProps({
  active: {
    type: Boolean,
    default: false
  },
  icon: Object,
  text: String,
  isCollapsed: Boolean
})

const emit = defineEmits(['click'])

const { closeDropdown } = useDashboardState()

// Obter o gerenciador de dropdowns do contexto
const dropdownManager = inject('dropdownManager')

const showPopup = ref(false)
let popupTimer = null

const handleClick = () => {
  console.log('MenuItem clicked:', props.text)

  // Fechar todos os dropdowns e resetar estado ao clicar em um item regular
  if (dropdownManager) {
    dropdownManager.closeDropdown()
  }
  closeDropdown()

  // Emitir o evento click para o MenuSection
  emit('click')
}

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
    }, 300)
  }
}

const onPopupEnter = () => {
  clearTimeout(popupTimer)
}

const onPopupLeave = () => {
  popupTimer = setTimeout(() => {
    showPopup.value = false
  }, 300)
}
</script>