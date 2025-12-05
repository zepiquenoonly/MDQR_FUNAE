<template>
  <header class="relative px-3 py-3 m-3 overflow-hidden transition-all duration-300 border glass-nav shadow-glass sm:px-6 sm:py-4 rounded-2xl backdrop-blur-xl border-white/20 hover:shadow-xl">
    <!-- Decorative gradient overlay -->
    <div class="absolute top-0 right-0 w-64 h-full pointer-events-none bg-gradient-to-l from-primary-500/5 to-transparent"></div>

    <div class="relative z-10 flex items-center justify-between gap-3">
      <!-- Left Section -->
      <div class="flex items-center flex-1 min-w-0 gap-2 sm:gap-4">
        <!-- Botão Menu Mobile -->
        <button @click="$emit('toggle-sidebar')" class="flex-shrink-0 p-2 text-gray-700 transition-all duration-200 sm:hidden hover:text-primary-600 rounded-xl hover:bg-primary-50 hover:scale-110">
          <Bars3Icon class="w-6 h-6" />
        </button>

        <!-- Logo FUNAE -->
        <button
          @click="navigateToDashboard"
          class="flex items-center gap-2 sm:gap-3 hover:opacity-80 transition-opacity cursor-pointer group"
        >
          <img
            src="/images/Logotipo-scaled.png"
            alt="FUNAE Logo"
            class="h-8 sm:h-10 lg:h-12 w-auto object-contain flex-shrink-0 transition-transform group-hover:scale-105"
          />
          
        </button>
      </div>

      <!-- Right Section -->
      <div class="flex items-center flex-shrink-0 gap-2 sm:gap-3">
        <!-- Notifications -->
        <button class="relative flex-shrink-0 p-2 text-gray-700 transition-all hover:text-primary-600 rounded-xl hover:bg-primary-50 group">
          <BellIcon class="w-5 h-5 group-hover:animate-pulse" />
          <span
            class="absolute flex items-center justify-center w-5 h-5 text-xs font-bold text-white rounded-full shadow-lg -top-1 -right-1 bg-gradient-to-r from-primary-500 to-orange-600 animate-bounce">
            1
          </span>
        </button>

        <!-- User Profile -->
        <UserDropdown :user="user" />
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  Bars3Icon,
  BellIcon
} from '@heroicons/vue/24/outline'
import UserDropdown from './UserDropdown.vue'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

defineEmits(['toggle-sidebar'])

// Função para obter a rota do dashboard baseado no role
const getDashboardRoute = () => {
  const roles = props.user?.roles || []
  const roleNames = new Set(roles.map(r => r.name.toLowerCase()))

  // PCA
  if (roleNames.has('pca')) {
    return route('pca.dashboard')
  }
  // Gestor
  if (roleNames.has('gestor')) {
    return route('manager.dashboard')
  }
  // Técnico
  if (roleNames.has('técnico') || roleNames.has('tecnico')) {
    return route('technician.dashboard')
  }
  // Utente (padrão)
  return route('user.dashboard')
}

// Navegar para o dashboard apropriado
const navigateToDashboard = () => {
  router.visit(getDashboardRoute())
}
</script>
