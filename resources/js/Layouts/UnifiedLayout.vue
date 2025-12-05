<template>
  <div  class="relative flex min-h-screen overflow-hidden" style="background: url('/background.min.svg') center/cover fixed no-repeat; zoom: 90%;">
    <!-- Sidebar Desktop - sempre visível -->
    <div class="fixed top-0 left-0 z-30 hidden w-64 h-full sm:block">
      <Sidebar @change-view="$emit('change-view', $event)" :role="detectedRole" />
    </div>

    <!-- Sidebar Mobile - overlay -->
    <div v-if="sidebarOpen && isMobile" class="fixed inset-0 z-50 sm:hidden">
      <!-- Overlay escuro -->
      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeSidebar"></div>
      <!-- Sidebar -->
      <div class="absolute top-0 left-0 h-full shadow-2xl w-72 animate-slide-in-left">
        <Sidebar :is-mobile="true" @toggle-sidebar="closeSidebar" @change-view="handleMobileMenuClick" :role="detectedRole" />
      </div>
    </div>

    <!-- Main Content -->
    <div class="flex flex-col flex-1 w-full min-w-0 sm:ml-64">
      <!-- Header -->
      <Header :user="safeUser" @toggle-sidebar="openSidebar" class="flex-shrink-0" />

      <!-- Loading Spinner -->
      <div v-if="loading"
        class="fixed inset-0 z-50 flex items-center justify-center bg-white/75 backdrop-blur-sm">
        <div class="text-center">
          <div class="w-12 h-12 mx-auto border-b-2 rounded-full animate-spin border-primary-500"></div>
          <p class="mt-4 text-sm font-medium text-gray-600">A carregar...</p>
        </div>
      </div>

      <!-- Page Content -->
      <main class="flex-1 overflow-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Sidebar from '@/Components/UtenteDashboard/Sidebar.vue'
import Header from '@/Components/UtenteDashboard/Header.vue'

const page = usePage()

const props = defineProps({
  user: {
    type: Object,
    default: null
  },
  role: {
    type: String,
    default: null // Será detectado automaticamente se não fornecido
  },
  stats: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['change-view'])

// Obter user automaticamente se não for passado
const safeUser = computed(() => {
  return props.user || page.props.auth?.user || {}
})

// Detectar role automaticamente do user se não for fornecido
const detectedRole = computed(() => {
  if (props.role) return props.role

  // Tentar detectar do user.roles
  const userRoles = safeUser.value?.roles || []
  if (userRoles.length > 0) {
    const roleName = userRoles[0]?.name?.toLowerCase()
    // Mapear nomes de roles para os valores esperados
    const roleMap = {
      'técnico': 'technician',
      'tecnico': 'technician',
      'gestor': 'manager',
      'pca': 'pca',
      'utente': 'utente',
      'admin': 'admin'
    }
    return roleMap[roleName] || roleName || 'utente'
  }

  // Fallback padrão
  return 'utente'
})

const sidebarOpen = ref(false)
const loading = ref(false)
const isMobile = ref(false)

// Detectar se é mobile
const checkMobile = () => {
  isMobile.value = window.innerWidth < 640
  // Fechar sidebar automaticamente se mudar para desktop
  if (!isMobile.value) {
    sidebarOpen.value = false
  }
}

const openSidebar = () => {
  if (isMobile.value) {
    sidebarOpen.value = true
  }
}

const closeSidebar = () => {
  sidebarOpen.value = false
}

const handleMobileMenuClick = (view) => {
  emit('change-view', view)
  closeSidebar()
}

// Listener para loading state do Inertia
router.on('start', () => {
  loading.value = true
})

router.on('finish', () => {
  loading.value = false
})

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})
</script>
