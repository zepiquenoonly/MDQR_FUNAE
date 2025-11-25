<template>
  <header class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
      <!-- Left Section -->
      <div class="flex items-center gap-4">
    
    <!-- Botão para abrir sidebar quando fechada -->
        <button
          v-if="sidebarCollapsed"
          @click="$emit('toggle-sidebar')"
          class="hover:text-orange-600 transition-colors"
        >
          <Bars3Icon class="w-5 h-5" />
        </button>

        <!-- Logo no lugar da search box -->
        <div class="hidden md:flex items-center gap-3">
          <div class="w-16 h-16 flex items-center justify-center">
            <img src="/images/Logotipo-scaled.png" alt="FUNAE" class="w-full h-full object-contain" />
          </div>
        </div>
      </div>

      <!-- Right Section -->
      <div class="flex items-center gap-4">
        <button class="text-gray-600 dark:text-gray-300 hover:text-brand dark:hover:text-orange-400 transition-colors p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
          <MoonIcon class="w-5 h-5" />
        </button>

        <button class="text-gray-600 dark:text-gray-300 hover:text-brand dark:hover:text-orange-400 transition-colors p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 relative">
          <BellIcon class="w-5 h-5" />
          <span class="absolute -top-1 -right-1 bg-brand text-white rounded-full w-4 h-4 text-xs flex items-center justify-center font-bold animate-pulse">
            1
          </span>
        </button>

        <!-- User Profile -->
        <UserDropdown :user="user" />
      </div>
    </div>

    <!-- Espaço reservado (search removida) para mobile -->
    <div class="mt-3 md:hidden">
      <div class="flex items-center">
        <div class="w-10 h-10 flex items-center justify-center">
          <img src="/images/Logotipo-scaled.png" alt="FUNAE" class="w-full h-full object-contain" />
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import {
  Bars3Icon,
  MagnifyingGlassIcon,
  MoonIcon,
  BellIcon
} from '@heroicons/vue/24/outline'
import UserDropdown from './UserDropdown.vue'

defineProps({
  sidebarCollapsed: Boolean,
  user: {
    type: Object,
    required: true
  }
})

defineEmits(['toggle-sidebar'])
</script>
