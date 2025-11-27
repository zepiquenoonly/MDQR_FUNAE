<template>
  <aside class="bg-brand dark:bg-dark-secondary text-white h-full flex flex-col overflow-hidden">
    <!-- Brand/Logo Section -->
    <div class="p-4 sm:p-6 flex-shrink-0 overflow-hidden">
      <div class="flex items-center space-x-2 sm:space-x-3 min-w-0">
        <!-- Botão de fechar para mobile (apenas no overlay) -->
        <button v-if="isMobile && !isCollapsed" @click="$emit('toggle-sidebar')"
          class="p-2 flex items-center justify-center hover:bg-white/30 transition-colors flex-shrink-0 rounded">
          <XMarkIcon class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
        </button>

        <!-- Logo quando em mobile overlay -->
        <div v-if="isMobile && !isCollapsed" class="flex items-center space-x-2 sm:space-x-3 flex-1 min-w-0">
          <div class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center flex-shrink-0">
            <img src="/images/Emblem_of_Mozambique.svg-2.png" alt="M" class="w-full h-full object-cover" />
          </div>
          <div class="transition-all duration-300 overflow-hidden min-w-0 flex-1">
            <h1 class="font-semibold text-base sm:text-lg whitespace-nowrap truncate">MDQR</h1>
            <p class="text-orange-100 dark:text-orange-200 text-xs sm:text-sm truncate">Painel do Utente</p>
          </div>
        </div>

        <!-- Logo normal para desktop -->
        <div v-else class="flex items-center space-x-2 sm:space-x-3 flex-1 min-w-0">
          <div class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center flex-shrink-0">
            <img src="/images/Emblem_of_Mozambique.svg-2.png" alt="M" class="w-full h-full object-cover" />
          </div>
          <div v-if="!isCollapsed" class="transition-all duration-300 overflow-hidden min-w-0 flex-1">
            <h1 class="font-semibold text-base sm:text-lg whitespace-nowrap truncate">MDQR</h1>
            <p class="text-orange-100 dark:text-orange-200 text-xs sm:text-sm truncate">Painel do Utente</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Menu Items -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden">
      <MenuSection :is-collapsed="isCollapsed && !isMobile" @item-clicked="handleMenuItemClick" />
    </div>
  </aside>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import MenuSection from './MenuSection.vue'

const props = defineProps({
  isCollapsed: Boolean
})

const emit = defineEmits(['toggle-sidebar', 'change-view'])

// Detectar se é mobile
const isMobile = ref(false)

const checkMobile = () => {
  isMobile.value = window.innerWidth < 640
}

const handleMenuItemClick = (item) => {
  console.log('Menu item clicked:', item)

  // Mapeamento direto de ids do MenuSection para views/rotas
  const map = {
    dashboard: 'dashboard',
    reclamacoes: 'reclamacoes',
    perfil: 'profile'
  }

  const view = map[item] || item

  // Emitir para o layout/página
  emit('change-view', view)

  // Tentar navegação via Inertia/route se disponíveis (não obrigatório)
  try {
    if (typeof route === 'function') {
      const nameMap = {
        dashboard: 'dashboard.index',
        reclamacoes: 'complaints.index',
        perfil: 'profile.show'
      }
      const routeName = nameMap[view]
      if (routeName) {
        if (window.Inertia && typeof window.Inertia.visit === 'function') {
          window.Inertia.visit(route(routeName))
        } else if (typeof window.visit === 'function') {
          window.visit(route(routeName))
        } else {
          window.location.href = route(routeName)
        }
      }
    }
  } catch (e) {
    console.warn('Routing helper not available or navigation failed', e)
  }

  // Fechar sidebar em mobile
  if (isMobile.value) {
    emit('toggle-sidebar')
  }
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})
</script>