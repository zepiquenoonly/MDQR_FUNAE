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
    <MenuItem 
      :active="activePanel === 'dashboard'" 
      :icon="HomeIcon" 
      :text="'Início'" 
      :is-collapsed="isCollapsed"
      @click="handleItemClick('dashboard')" 
    />

    <!-- Projectos Item -->
    <MenuItem 
      :active="activePanel === 'projectos'" 
      :icon="DocumentTextIcon" 
      :text="'Projectos'" 
      :is-collapsed="isCollapsed"
      @click="handleItemClick('projectos')" 
    />

    <!-- MDQR Dropdown -->
    <MenuDropdown 
      id="mdqr" 
      :icon="PlusIcon" 
      :text="'MDQR'" 
      :is-collapsed="isCollapsed" 
      :items="mdqrItems"
      :dropdown-manager="dropdownManager"
      :is-active="activePanel === 'mdqr'"
      @item-clicked="handleDropdownItemClick" 
    />
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
import { useDropdownManager } from '@/Components/UtenteDashboard/Composables/useDropdownManager.js'
import { useDashboardState } from '@/Components/UtenteDashboard/Composables/useDashboardState.js'
import { provide } from 'vue'
import MenuItem from './MenuItem.vue'
import MenuDropdown from './MenuDropdown.vue'

defineProps({
  isCollapsed: Boolean
})

const { activePanel, setActivePanel, setActiveDropdown } = useDashboardState()

const mdqrItems = [
  { icon: ExclamationTriangleIcon, text: 'Minhas Queixas', id: 'queixas' },
  { icon: LightBulbIcon, text: 'Minhas Sugestões', id: 'sugestoes' },
  { icon: DocumentTextIcon, text: 'Minhas Reclamações', id: 'reclamacoes' }
]

// Inicializar o gerenciador de dropdowns
const dropdownManager = useDropdownManager()

// Fornecer o gerenciador para componentes filhos
provide('dropdownManager', dropdownManager)

// Função para lidar com cliques nos itens do menu principal
const handleItemClick = (panel) => {
  console.log('MenuSection - Item clicked:', panel)
  // Fechar dropdowns primeiro
  dropdownManager.closeDropdown()
  // Depois mudar o painel
  setActivePanel(panel)
}

// Função para lidar com cliques nos itens do dropdown
const handleDropdownItemClick = (itemText) => {
  console.log('MenuSection - Dropdown item clicked:', itemText)

  // Mapear o texto do item para o ID do painel
  const panelMap = {
    'Minhas Sugestões': 'sugestoes',
    'Minhas Queixas': 'queixas',
    'Minhas Reclamações': 'reclamacoes'
  }

  const panelId = panelMap[itemText] || 'mdqr'

  // Fechar dropdown primeiro
  dropdownManager.closeDropdown()
  // Depois mudar os estados
  setActivePanel('mdqr')
  setActiveDropdown(panelId)
}
</script>