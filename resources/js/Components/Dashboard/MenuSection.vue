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
      :text="'MDQR'"
      :badge="5"
      :is-collapsed="isCollapsed"
      :items="mdqrItems"
      :dropdown-manager="dropdownManager"
      @item-clicked="handleItemClick"
    />

    <MenuItem
      :icon="FolderIcon"
      :text="'Gestão dos Projectos'"
      :is-collapsed="isCollapsed"
      @click="handleItemClick('gestao-projetos')"
    />

    <!-- Estatísticas Dropdown -->
    <MenuDropdown
      id="estatisticas"
      :icon="ChartBarSquareIcon"
      :text="'Estatísticas'"
      :is-collapsed="isCollapsed"
      :items="estatisticasItems"
      :dropdown-manager="dropdownManager"
      @item-clicked="handleItemClick"
    />

    <MenuItem
      :icon="UserGroupIcon"
      :text="'Gestão dos Usuários'"
      :is-collapsed="isCollapsed"
      @click="handleItemClick('gestao-usuarios')"
    />
  </nav>
</template>

<script setup>
import { 
  HomeIcon,
  DocumentTextIcon,
  FolderIcon,
  ChartBarSquareIcon,
  UserGroupIcon,
  LightBulbIcon,
  ExclamationTriangleIcon,
  ChartBarIcon,
  EyeIcon
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

const mdqrItems = [
  { icon: LightBulbIcon, text: 'Sugestões' },
  { icon: ExclamationTriangleIcon, text: 'Queixas' },
  { icon: DocumentTextIcon, text: 'Reclamações' }
]

const estatisticasItems = [
  { icon: EyeIcon, text: 'Visão geral de utilização' },
  { icon: ChartBarIcon, text: 'Gráficos de utilização' }
]

const handleItemClick = (item) => {
  // Fechar todos os dropdowns ao clicar em um item
  dropdownManager.closeDropdown()
  emit('item-clicked', item)
}
</script>