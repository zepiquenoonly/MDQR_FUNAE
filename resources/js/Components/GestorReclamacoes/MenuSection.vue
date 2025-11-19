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
      :icon="HomeIcon" :text="'Dashboard'" :is-collapsed="isCollapsed"
      :href="$page.url.startsWith('/home') ? '/home' : '/admin/dashboard'" />

    <!-- Casos Dropdown -->
    <MenuDropdown id="casos" :icon="DocumentTextIcon" :text="'Casos'" :badge="stats.pending_complaints || 0"
      :is-collapsed="isCollapsed" :items="casosItems" :dropdown-manager="dropdownManager"
      @item-clicked="handleItemClick" />

    <!-- Projetos Dropdown -->
    <MenuDropdown id="projetos" :icon="FolderIcon" :text="'Projetos'" :is-collapsed="isCollapsed" :items="projetosItems"
      :dropdown-manager="dropdownManager" @item-clicked="handleItemClick" />

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

// Função helper para gerar URLs
const generateUrl = (path) => {
  return path
}

// Itens do menu com base nas suas rotas reais
const casosItems = computed(() => [
  {
    icon: ExclamationTriangleIcon,
    text: 'Nova Reclamação',
    id: 'nova-reclamacao',
    href: generateUrl('/reclamacoes/nova'),
    active: page.url === '/reclamacoes/nova'
  },
  {
    icon: EyeIcon,
    text: 'Acompanhar Reclamação',
    id: 'acompanhar-reclamacao',
    href: generateUrl('/reclamacoes/acompanhar'),
    active: page.url === '/reclamacoes/acompanhar'
  },
  {
    icon: LightBulbIcon,
    text: 'Tracking',
    id: 'tracking',
    href: generateUrl('/track'),
    active: page.url === '/track'
  }
])

// Itens de projetos
const projetosItems = computed(() => [
  {
    icon: FolderIcon,
    text: 'Lista de Projetos',
    id: 'lista-projetos',
    href: generateUrl('/api/projects'),
    active: page.url.startsWith('/api/projects')
  },
  {
    icon: Cog6ToothIcon,
    text: 'Gestão de Projetos',
    id: 'gestao-projetos',
    href: generateUrl('/projects'), // URL placeholder
    active: page.url.startsWith('/project/')
  }
])

const handleItemClick = (item) => {
  // Fechar todos os dropdowns ao clicar em um item
  dropdownManager.closeDropdown()

  // Se o item tem href, navegar para a URL
  if (item.href && item.href !== '#') {
    router.visit(item.href)
  }

  emit('item-clicked', item)
}
</script>