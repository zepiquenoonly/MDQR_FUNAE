<template>
  <nav class="py-4 overflow-hidden">
    <!-- Menu Label -->
    <div :class="[
        'px-5 py-4 text-xs text-white font-semibold uppercase tracking-wide transition-opacity duration-300',
        isCollapsed ? 'opacity-0' : 'opacity-100'
      ]">
      Menu
    </div>

    <!-- Menu Items -->
    <MenuItem :active="true" :icon="HomeIcon" :text="'Início'" :is-collapsed="isCollapsed"
      @click="handleItemClick('inicio')" />

    <!-- MDQR Dropdown -->
    <MenuItem id="projectos" :icon="DocumentTextIcon" :text="'Projectos'" :is-collapsed="isCollapsed"
      @click="handleItemClick('projectos')" />

    <MenuDropdown id="mdqr" :icon="PlusIcon" :text="'MDQR'" :is-collapsed="isCollapsed" :items="mdqrItems"
      :dropdown-manager="dropdownManager" @item-clicked="handleItemClick" />
  </nav>
</template>

<script setup>
import { 
  HomeIcon,
  DocumentTextIcon,
  LightBulbIcon,
  ExclamationTriangleIcon,
  PlusIcon
} from '@heroicons/vue/24/outline'
import { useDropdownManager } from './Composables/useDropdownManager.js'
import { provide } from 'vue'
import MenuItem from './MenuItem.vue'
import MenuDropdown from './MenuDropdown.vue'

defineProps({
  isCollapsed: Boolean
})

const mdqrItems = [
  { icon: LightBulbIcon, text: 'Minhas Sugestões', id: 'sugestoes' },
  { icon: ExclamationTriangleIcon, text: 'Minhas Queixas', id: 'queixas' },
  { icon: DocumentTextIcon, text: 'Minhas Reclamações', id: 'reclamacoes' }
]

const emit = defineEmits(['item-clicked'])

// Inicializar o gerenciador de dropdowns
const dropdownManager = useDropdownManager()

// Fornecer o gerenciador para componentes filhos
provide('dropdownManager', dropdownManager)
</script>