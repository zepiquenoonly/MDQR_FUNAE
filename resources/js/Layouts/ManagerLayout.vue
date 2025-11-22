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

      <!-- Loading Spinner Global -->
      <div v-if="loading" class="fixed inset-0 bg-white bg-opacity-75 flex items-center justify-center z-50">
        <div class="text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand mx-auto"></div>
          <p class="text-gray-600 mt-2">A carregar...</p>
        </div>
      </div>

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
import { ref, provide, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
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

// Estado da sidebar e loading
const sidebarCollapsed = ref(false)
const loading = ref(false)

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

let loadingTimeout = null

const startLoading = () => {
  console.log('🔄 Loading STARTED - Navigation beginning')
  // Só mostra o spinner se a navegação demorar mais de 300ms
  loadingTimeout = setTimeout(() => {
    loading.value = true
  }, 300)
}

const finishLoading = () => {
  console.log('✅ Loading FINISHED - Navigation complete')
  if (loadingTimeout) {
    clearTimeout(loadingTimeout)
    loadingTimeout = null
  }
  loading.value = false
}

// Abordagem com eventos globais do Inertia
onMounted(() => {
  const removeStartListener = router.on('start', startLoading)
  const removeFinishListener = router.on('finish', finishLoading)
})

// Cleanup do timeout apenas
onUnmounted(() => {
  if (loadingTimeout) {
    clearTimeout(loadingTimeout)
  }
})
</script>