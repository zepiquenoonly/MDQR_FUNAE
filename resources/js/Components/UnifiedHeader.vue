<template>
  <header class="relative px-2 py-2 h-20 transition-all duration-300 bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl">
    <!-- Minimal transparent design -->

    <div class="relative z-10 flex items-center justify-between gap-2">
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
