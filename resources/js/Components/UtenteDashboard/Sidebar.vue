<template>
  <aside class="glass rounded-2xl h-full flex flex-col overflow-hidden shadow-glass m-3 backdrop-blur-xl border border-white/40 transition-all duration-300 hover:shadow-2xl">
    <!-- Brand/Logo Section -->
    <div class="p-4 sm:p-6 flex-shrink-0 overflow-hidden bg-gradient-to-br from-primary-500/20 via-orange-500/15 to-primary-500/10 rounded-t-2xl relative border-b border-white/20">
      <!-- Decorative elements -->
      <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-orange-500/10 to-transparent rounded-full blur-2xl"></div>
      <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-primary-500/10 to-transparent rounded-full blur-2xl"></div>

      <div class="flex items-center space-x-3 min-w-0 relative z-10">
        <!-- Botão de fechar para mobile -->
        <button v-if="isMobile" @click="$emit('toggle-sidebar')"
          class="p-2 flex items-center justify-center hover:bg-white/30 transition-all flex-shrink-0 rounded-xl text-primary-700 hover:rotate-90 duration-300 backdrop-blur-sm">
          <XMarkIcon class="w-6 h-6" />
        </button>

        <!-- Logo e Título -->
        <div class="flex items-center space-x-3 flex-1 min-w-0">
          <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg text-white flex-shrink-0 ring-2 ring-white/30 ring-offset-2 ring-offset-transparent transition-transform hover:scale-110 duration-300">
            <img src="/images/Emblem_of_Mozambique.svg-2.png" alt="M" class="w-8 h-8 object-cover" />
          </div>
          <div class="overflow-hidden min-w-0 flex-1">
            <h1 class="font-bold text-lg whitespace-nowrap truncate text-gray-900 drop-shadow-sm">MDQR</h1>
            <p class="text-primary-700 text-sm truncate font-medium">Painel do Utente</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Menu Items -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden scrollbar-thin scrollbar-thumb-primary-300 scrollbar-track-transparent hover:scrollbar-thumb-primary-400 transition-colors">
      <MenuSection @item-clicked="handleMenuItemClick" />
    </div>
  </aside>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import MenuSection from './MenuSection.vue'

const props = defineProps({
  isMobile: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['toggle-sidebar', 'change-view'])

// Detectar se é mobile
const isMobile = ref(props.isMobile)

const checkMobile = () => {
  isMobile.value = window.innerWidth < 640
}

const handleMenuItemClick = (item) => {
  emit('change-view', item)
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})
</script>