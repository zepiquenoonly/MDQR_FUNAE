<template>
  <nav class="py-4 overflow-hidden">
    <!-- Menu Label -->
    <div :class="[
      'px-5 py-4 text-xs text-white font-semibold uppercase tracking-wide transition-opacity duration-300',
      isCollapsed ? 'opacity-0' : 'opacity-100'
    ]">
      Gestão de Casos
    </div>

    <!-- Menu Items -->
    <MenuItem
      :active="$page.url === '/home' || $page.url === '/admin/dashboard' || $page.url === '/gestor/dashboard' || $page.url === '/tecnico/dashboard' || $page.url === '/utente/dashboard'"
      :icon="HomeIcon" :text="'Home'" :is-collapsed="isCollapsed"
      :href="$page.url.startsWith('/home') ? '/home' : '/admin/dashboard'" />

    <!-- Casos Dropdown
    <MenuDropdown id="casos" :icon="DocumentTextIcon" :text="'Casos'" :badge="stats.pending_complaints || 0"
      :is-collapsed="isCollapsed" :items="casosItems" :dropdown-manager="dropdownManager"
      @item-clicked="handleItemClick" /> -->

    <!-- Projetos Dropdown -->
    <MenuDropdown id="projectos" :icon="FolderIcon" :text="'Projectos'" :is-collapsed="isCollapsed"
      :items="projetosItems" :dropdown-manager="dropdownManager" @item-clicked="handleItemClick" />

    <MenuItem :active="$page.url.includes('tecnicos')" :icon="UserGroupIcon" :text="'Ver Técnicos'"
      :is-collapsed="isCollapsed" href="/gestor/dashboard?panel=tecnicos" />

    <MenuItem :active="$page.url.startsWith('/profile')" :icon="UserIcon" :text="'Perfil'" :is-collapsed="isCollapsed"
      href="/profile" />
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import {
  HomeIcon,
  DocumentTextIcon,
  FolderIcon,
  UserIcon,
  ExclamationTriangleIcon,
  LightBulbIcon,
  EyeIcon,
  UserGroupIcon,
  Cog6ToothIcon
} from '@heroicons/vue/24/outline'
import { useDropdownManager } from './Composables/useDropdownManager.js'
import MenuItem from './MenuItem.vue'
import MenuDropdown from './MenuDropdown.vue'

const props = defineProps({
  isCollapsed: Boolean,
  stats: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['item-clicked'])

// Inicializar o gerenciador de dropdowns
const dropdownManager = useDropdownManager()
const page = usePage()

// Itens do menu com base nas suas rotas reais
const casosItems = computed(() => [
  {
    icon: ExclamationTriangleIcon,
    text: 'Nova Reclamação',
    id: 'nova-reclamacao',
    href: '/reclamacoes/nova',
    active: page.url === '/reclamacoes/nova'
  },
  {
    icon: EyeIcon,
    text: 'Acompanhar Reclamação',
    id: 'acompanhar-reclamacao',
    href: '/reclamacoes/acompanhar',
    active: page.url === '/reclamacoes/acompanhar'
  },
  {
    icon: LightBulbIcon,
    text: 'Tracking',
    id: 'tracking',
    href: '/track',
    active: page.url === '/track'
  }
])

// Itens de projetos - CORRIGIDO para usar navigateToProjectos
const projetosItems = computed(() => [
  {
    icon: FolderIcon,
    text: 'Lista de Projectos',
    id: 'lista-projectos',
    href: '/gestor/dashboard?panel=projectos',
    active: window.location.search.includes('panel=projectos')
  }
])

const handleItemClick = (item) => {
  // Fechar todos os dropdowns ao clicar em um item
  dropdownManager.closeDropdown()

  // Navegação especial para "Lista de Projectos"
  if (item.id === 'lista-projectos') {
    navigateToProjectos()
    return
  }

  // Se o item tem href, navegar para a URL
  if (item.href && item.href !== '#') {
    router.visit(item.href)
  }

  emit('item-clicked', item)
}

// Função específica para navegar para a lista de projetos
const navigateToProjectos = () => {
  router.visit('/gestor/dashboard?panel=projectos')
}
</script>