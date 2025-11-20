<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar Fixo -->
    <Sidebar :user="$page.props.auth.user" :stats="stats" :is-collapsed="sidebarCollapsed"
      @toggle-sidebar="handleSidebarToggle" />

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Header Fixo no Topo -->
      <Header :sidebar-collapsed="sidebarCollapsed" :user="$page.props.auth.user" @toggle-sidebar="handleSidebarToggle"
        class="flex-shrink-0 sticky top-0 z-30" />

      <!-- Page Content Scrollável -->
      <main class="flex-1 overflow-auto">
        <div class="p-6">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, provide, computed } from 'vue'
import { useDropdownManager } from '@/Components/GestorReclamacoes/Composables/useDropdownManager'
import { useDashboardState } from '@/Components/GestorReclamacoes/Composables/useDashboardState'
import Sidebar from '@/Components/GestorReclamacoes/Sidebar.vue'
import Header from '@/Components/GestorReclamacoes/Header.vue'

// Props
const props = defineProps({
  stats: {
    type: Object,
    default: () => ({})
  }
})

// Estado da sidebar
const sidebarCollapsed = ref(false)

// Inicializar managers
const dropdownManager = useDropdownManager()
const dashboardState = useDashboardState()

// Computed para estatísticas
const stats = computed(() => props.stats)

// Fornecer estado para componentes filhos
provide('dropdownManager', dropdownManager)
provide('dashboardState', dashboardState)
provide('sidebarState', {
  isCollapsed: sidebarCollapsed,
  toggle: handleSidebarToggle
})

// Handler para alternar sidebar
function handleSidebarToggle(isCollapsed = null) {
  if (isCollapsed !== null) {
    sidebarCollapsed.value = isCollapsed
  } else {
    sidebarCollapsed.value = !sidebarCollapsed.value
  }

  // Fechar todos os dropdowns quando a sidebar é recolhida/expandida
  dropdownManager.closeDropdown()

  console.log('Sidebar collapsed:', sidebarCollapsed.value)
}
</script>