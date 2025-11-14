<template>
  <aside 
    :class="[
      'bg-brand border-r border-brand flex flex-col transition-all duration-300 overflow-hidden z-40',
      isCollapsed ? 'w-20' : 'w-64',
      'fixed lg:relative h-screen lg:h-auto'
    ]"
    :style="sidebarStyle"
  >
    <!-- Sidebar Header -->
    <div class="p-5 flex items-center justify-between min-h-20">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 flex items-center justify-center">
            <img src="/images/Emblem_of_Mozambique.svg-2.png" alt="M" class="w-full h-full object-cover" />
        </div>

        <span 
          :class="[
            'text-lg font-bold text-white transition-opacity duration-300',
            isCollapsed ? 'opacity-0' : 'opacity-100'
          ]"
        >
          MDQR
        </span>
      </div>
      
      <!-- Botão para abrir/fechar menu (sempre visível no Sidebar) -->
      <button 
        @click="$emit('toggle-sidebar')"
        class="text-white hover:text-gray-200 transition-colors"
      >
        <Bars3Icon class="w-5 h-5" />
      </button>
    </div>

    <!-- Menu Items -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden">
      <MenuSection 
        :is-collapsed="isCollapsed"
        @item-clicked="handleMenuItemClick"
      />
    </div>
  </aside>

  <!-- Overlay para mobile -->
  <div 
    v-if="!isCollapsed && isMobile" 
    class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden"
    @click="$emit('toggle-sidebar')"
  ></div>

  <!-- Botão flutuante para abrir sidebar quando fechada em mobile -->
  <button 
    v-if="isCollapsed && isMobile"
    @click="$emit('toggle-sidebar')"
    class="fixed top-4 left-4 z-50 bg-brand text-white p-3 rounded-lg shadow-lg hover:bg-orange-600 transition-colors lg:hidden"
  >
    <Bars3Icon class="w-5 h-5" />
  </button>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Bars3Icon } from '@heroicons/vue/24/outline'
import MenuSection from './MenuSection.vue'

const props = defineProps({
  isCollapsed: Boolean
})

const emit = defineEmits(['toggle-sidebar'])

// Detectar se é mobile
const isMobile = ref(false)

const checkMobile = () => {
  isMobile.value = window.innerWidth < 1024
}

const handleMenuItemClick = (item) => {
  console.log('Menu item clicked:', item)
  // Em mobile, fechar sidebar ao clicar em um item
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