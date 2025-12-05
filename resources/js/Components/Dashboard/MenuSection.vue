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
    <MenuItem :active="false" :icon="HomeIcon" :text="'Início'" :is-collapsed="isCollapsed"
      @click="handleItemClick('dashboard')" />

    <!-- MDQR Dropdown - Only for Technician -->
    <MenuDropdown v-if="role === 'technician'" id="mdqr" :icon="DocumentTextIcon" :text="'MDQR'" :badge="5" :is-collapsed="isCollapsed"
      :items="mdqrItems" :dropdown-manager="dropdownManager" @item-clicked="handleItemClick" />

    <!-- Projects Management - For Manager and PCA -->
    <MenuItem v-if="role === 'manager' || role === 'pca'" :icon="FolderIcon" :text="'Gestão dos Projectos'" :is-collapsed="isCollapsed"
      @click="handleItemClick('projectos')" />

    <!-- Technicians Management - For Manager -->
    <MenuItem v-if="role === 'manager'" :icon="UserGroupIcon" :text="'Gestão dos Técnicos'" :is-collapsed="isCollapsed"
      @click="handleItemClick('tecnicos')" />

    <!-- Statistics Dropdown - For Manager and PCA -->
    <MenuDropdown v-if="role === 'manager' || role === 'pca'" id="estatisticas" :icon="ChartBarSquareIcon" :text="'Estatísticas'" :is-collapsed="isCollapsed"
      :items="estatisticasItems" :dropdown-manager="dropdownManager" @item-clicked="handleItemClick" />

    <!-- Users Management - For PCA -->
    <MenuItem v-if="role === 'pca'" :icon="UserGroupIcon" :text="'Gestão dos Usuários'" :is-collapsed="isCollapsed"
      @click="handleItemClick('gestao-usuarios')" />
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
import MenuItem from './MenuItem.vue'
import MenuDropdown from './MenuDropdown.vue'

defineProps({
  isCollapsed: Boolean,
  role: {
    type: String,
    default: 'technician' // technician, manager, pca
  }
})

const emit = defineEmits(['item-clicked'])

// Inicializar o gerenciador de dropdowns
const dropdownManager = useDropdownManager()

const mdqrItems = [
  { icon: LightBulbIcon, text: 'Sugestões', id: 'sugestoes' },
  { icon: ExclamationTriangleIcon, text: 'Queixas', id: 'queixas' },
  { icon: DocumentTextIcon, text: 'Reclamações', id: 'reclamacoes' }
]

const estatisticasItems = [
  { icon: EyeIcon, text: 'Visão geral de utilização' },
  { icon: ChartBarIcon, text: 'Gráficos de utilização' }
]

const handleItemClick = (item) => {
  // Fechar todos os dropdowns ao clicar em um item
  dropdownManager.closeDropdown()

  // Emitir evento para o componente pai tratar
  emit('item-clicked', item)
}
</script>
