<template>
  <nav class="py-4 overflow-hidden">
    <!-- Menu PCA -->
    <template v-if="userRole === 'PCA'">
      <div :class="[
        'px-5 py-4 text-xs text-white font-semibold uppercase tracking-wide transition-opacity duration-300',
        isCollapsed ? 'opacity-0' : 'opacity-100'
      ]">
        Visão Executiva
      </div>

      <MenuItem
        :active="$page.url === '/pca/dashboard'"
        :icon="ChartBarIcon" 
        :text="'Dashboard'" 
        :is-collapsed="isCollapsed"
        href="/pca/dashboard" />

      <MenuItem 
        :active="$page.url === '/track'" 
        :icon="MagnifyingGlassIcon" 
        :text="'Acompanhamento'"
        :is-collapsed="isCollapsed" 
        href="/track" />
    </template>

    <!-- Menu Gestor -->
    <template v-else-if="userRole === 'Gestor'">
      <div :class="[
        'px-5 py-4 text-xs text-white font-semibold uppercase tracking-wide transition-opacity duration-300',
        isCollapsed ? 'opacity-0' : 'opacity-100'
      ]">
        Gestão de Casos
      </div>

      <MenuItem
        :active="$page.url === '/gestor/dashboard' && !$page.url.includes('panel=')"
        :icon="HomeIcon" 
        :text="'Dashboard'" 
        :is-collapsed="isCollapsed"
        href="/gestor/dashboard" />

      <MenuDropdown id="projectos" :icon="FolderIcon" :text="'Projectos'" :is-collapsed="isCollapsed"
        :items="projetosItems" :dropdown-manager="dropdownManager" @item-clicked="handleItemClick" />

      <MenuItem 
        :active="$page.url.includes('panel=tecnicos')" 
        :icon="UserGroupIcon" 
        :text="'Ver Técnicos'"
        :is-collapsed="isCollapsed" 
        href="/gestor/dashboard?panel=tecnicos" />

      <MenuItem 
        :active="$page.url === '/track'" 
        :icon="MagnifyingGlassIcon" 
        :text="'Acompanhamento'"
        :is-collapsed="isCollapsed" 
        href="/track" />
    </template>

    <!-- Menu Técnico -->
    <template v-else-if="userRole === 'Técnico'">
      <div :class="[
        'px-5 py-4 text-xs text-white font-semibold uppercase tracking-wide transition-opacity duration-300',
        isCollapsed ? 'opacity-0' : 'opacity-100'
      ]">
        Minhas Tarefas
      </div>

      <MenuItem
        :active="$page.url === '/tecnico/dashboard'"
        :icon="HomeIcon" 
        :text="'Dashboard'" 
        :is-collapsed="isCollapsed"
        href="/tecnico/dashboard" />

      <MenuItem 
        :active="$page.url === '/track'" 
        :icon="MagnifyingGlassIcon" 
        :text="'Acompanhamento'"
        :is-collapsed="isCollapsed" 
        href="/track" />
    </template>

    <!-- Conta Section (comum para todos) -->
    <div :class="[
      'px-5 py-4 text-xs text-white font-semibold uppercase tracking-wide transition-opacity duration-300 mt-4',
      isCollapsed ? 'opacity-0' : 'opacity-100'
    ]">
      Conta
    </div>

    <!-- Meu Perfil -->
    <MenuItem 
      :active="$page.url.startsWith('/profile')" 
      :icon="UserCircleIcon" 
      :text="'Meu Perfil'"
      :is-collapsed="isCollapsed" 
      href="/profile" />

    <!-- Sair -->
    <MenuItem 
      :active="false" 
      :icon="ArrowRightOnRectangleIcon" 
      :text="'Sair'"
      :is-collapsed="isCollapsed" 
      @click="handleLogout" />
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import {
  HomeIcon,
  ChartBarIcon,
  FolderIcon,
  UserCircleIcon,
  UserGroupIcon,
  MagnifyingGlassIcon,
  ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline'
import { useDropdownManager } from './Composables/useDropdownManager.js'
import MenuItem from './MenuItem.vue'
import MenuDropdown from './MenuDropdown.vue'

const props = defineProps({
  isCollapsed: Boolean,
  user: {
    type: Object,
    required: true
  },
  stats: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['item-clicked'])

// Inicializar o gerenciador de dropdowns
const dropdownManager = useDropdownManager()
const page = usePage()

// Determinar role do usuário
const userRole = computed(() => {
  return props.user?.roles?.[0]?.name || props.user?.role || 'Gestor'
})

// Itens de projetos (apenas para Gestor)
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
    router.visit('/gestor/dashboard?panel=projectos')
    return
  }

  // Se o item tem href, navegar para a URL
  if (item.href && item.href !== '#') {
    router.visit(item.href)
  }

  emit('item-clicked', item)
}

const handleLogout = () => {
  router.post('/logout')
}
</script>