<template>
  <a
    :class="[
      'flex items-center gap-3 px-5 py-3 cursor-pointer transition-all duration-200 border-l-4',
      active
        ? 'bg-gradient-to-r from-primary-50 to-orange-50 text-primary-700 border-primary-500'
        : 'border-transparent hover:bg-primary-50/50 text-gray-700 hover:text-primary-600'
    ]"
    @click="handleClick"
  >
    <component
      :is="icon"
      :class="[
        'flex-shrink-0 w-5 h-5',
        active ? 'text-primary-600' : 'text-gray-600'
      ]"
    />
    <span class="flex-1 text-sm font-medium">
      {{ text }}
    </span>
  </a>
</template>

<script setup>
import { inject } from 'vue'
import { useDashboard } from '@/composables/useDashboard'

const props = defineProps({
  active: {
    type: Boolean,
    default: false
  },
  icon: Object,
  text: String
})

const emit = defineEmits(['click'])

const { closeDropdown } = useDashboard()

// Obter o gerenciador de dropdowns do contexto
const dropdownManager = inject('dropdownManager')

const handleClick = () => {
  // Fechar todos os dropdowns e resetar estado ao clicar em um item regular
  if (dropdownManager) {
    dropdownManager.closeDropdown()
  }
  closeDropdown()

  // Emitir o evento click para o MenuSection
  emit('click')
}
</script>