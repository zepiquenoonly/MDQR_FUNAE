<template>
  <header class="relative px-3 py-3 m-3 h-32 transition-all duration-300 border glass-nav shadow-glass sm:px-6 sm:py-4 rounded-2xl backdrop-blur-xl border-white/20 hover:shadow-xl">
    <!-- Decorative gradient overlay -->
    <div class="absolute top-0 right-0 w-64 h-full pointer-events-none bg-gradient-to-l from-primary-500/5 to-transparent"></div>

    <div class="relative z-10 flex items-center justify-between gap-3">
      <!-- Left Section -->
      <div class="flex items-center flex-1 min-w-0 gap-2 sm:gap-4">
        <!-- Botão Menu Mobile -->
        <button @click="$emit('toggle-sidebar')" class="flex-shrink-0 p-2 text-gray-700 transition-all duration-200 sm:hidden hover:text-primary-600 rounded-xl hover:bg-primary-50 hover:scale-110">
          <Bars3Icon class="w-6 h-6" />
        </button>

        <!-- Logo simples e grande -->
        <!-- <div class="flex items-center gap-3">
          <div class="w-28 h-28 flex items-center justify-center p-1">
            <img src="/images/Logotipo-scaled.png" alt="FUNAE" class="w-full h-full object-contain filter drop-shadow-lg" />
          </div>
          <div class="hidden md:block border-l border-gray-200 pl-4">
            <h2 class="text-lg font-semibold text-gray-800">Dashboard</h2>
            <p class="text-sm text-gray-500">Painel de Usuário</p>
          </div>
        </div> -->
      </div>

      <!-- Right Section -->
      <div class="flex items-center flex-shrink-0 gap-2 sm:gap-3">
        <!-- Notifications
        <button class="relative flex-shrink-0 p-2 text-gray-700 transition-all hover:text-primary-600 rounded-xl hover:bg-primary-50 group">
          <BellIcon class="w-5 h-5 group-hover:animate-pulse" />
          <span
            class="absolute flex items-center justify-center w-5 h-5 text-xs font-bold text-white rounded-full shadow-lg -top-1 -right-1 bg-gradient-to-r from-primary-500 to-orange-600 animate-bounce">
            1
          </span>
        </button> -->

        <!-- User Profile -->
        <UserDropdown :user="user" />
      </div>
    </div>
  </header>
</template>

<script setup>
import {
  Bars3Icon,
  BellIcon
} from '@heroicons/vue/24/outline'
import UserDropdown from '@/Components/UtenteDashboard/UserDropdown.vue'

defineProps({
  user: {
    type: Object,
    required: true
  }
})

defineEmits(['toggle-sidebar'])
</script>
