<template>
  <nav class="py-4 overflow-hidden">
    <!-- Menu Label -->
    <div 
      :class="[
        'px-5 py-4 text-xs text-white font-semibold uppercase tracking-wide transition-opacity duration-300',
        isCollapsed ? 'opacity-0' : 'opacity-100'
      ]"
    >
      Menu
    </div>

    <!-- Menu Items -->
    <MenuItem
      :active="true"
      :icon="HomeIcon"
      :text="'Início'"
      :is-collapsed="isCollapsed"
      @click="handleItemClick('inicio')"
    />

    <!-- MDQR Dropdown -->
    <MenuDropdown
      id="mdqr"
      :icon="DocumentTextIcon"
      :text="'Projectos'"
      :is-collapsed="isCollapsed"
      @click="handleItemClick('projectos')"
    />

    <MenuItem
      :text="'+ Mdqr'"
      :is-collapsed="isCollapsed"
      @click="handleItemClick('gestao-projetos')"
    />
  </nav>
</template>

<script setup>
import { 
  HomeIcon,
  DocumentTextIcon,
  FolderIcon,
} from '@heroicons/vue/24/outline'
import { useDropdownManager } from './Composables/useDropdownManager.js'
import { provide } from 'vue'
import MenuItem from './MenuItem.vue'
import MenuDropdown from './MenuDropdown.vue'

defineProps({
  isCollapsed: Boolean
})

const emit = defineEmits(['item-clicked'])

// Inicializar o gerenciador de dropdowns
const dropdownManager = useDropdownManager()

// Fornecer o gerenciador para componentes filhos
provide('dropdownManager', dropdownManager)
</script>