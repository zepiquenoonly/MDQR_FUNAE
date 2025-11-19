<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <Sidebar :user="$page.props.auth.user" :stats="stats" @toggle-sidebar="handleSidebarToggle" />

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Header -->
      <Header :sidebar-collapsed="sidebarCollapsed" :user="$page.props.auth.user"
        @toggle-sidebar="handleSidebarToggle" />

      <!-- Page Content -->
      <main class="flex-1 p-6 overflow-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, provide, computed } from 'vue'
import { useDashboardState } from '@/Components/GestorReclamacoes/Composables/useDashboardState'
import Sidebar from '@/Components/GestorReclamacoes/Sidebar.vue'
import Header from '@/Components/Dashboard/Header.vue'

// Props
const props = defineProps({
  stats: {
    type: Object,
    default: () => ({})
  }
})

// Estado da sidebar
const sidebarCollapsed = ref(false)

// Estado do dashboard
const dashboardState = useDashboardState()

// Computed para estatísticas
const stats = computed(() => props.stats)

// Fornecer estado do dashboard para componentes filhos
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
}
</script>