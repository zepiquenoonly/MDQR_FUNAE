<template>
  <header class="bg-white dark:bg-dark-secondary border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 transition-colors duration-200">
    <div class="flex items-center justify-between">
      <!-- Left Section -->
      <div class="flex items-center gap-4">
        <!-- Botão para abrir sidebar quando fechada -->
        <button v-if="sidebarCollapsed" @click="$emit('toggle-sidebar')"
          class="text-gray-600 dark:text-gray-300 hover:text-orange-600 dark:hover:text-orange-400 transition-colors">
          <Bars3Icon class="w-5 h-5" />
        </button>

        <!-- Search Bar -->
        <div class="hidden md:flex gap-0">
          <input type="text" placeholder="Pesquisar..."
            class="w-64 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-l-lg bg-white dark:bg-dark-primary text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand">
          <button class="bg-brand text-white px-4 py-2 rounded-r-lg hover:bg-orange-600 transition-colors">
            <MagnifyingGlassIcon class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Right Section -->
      <div class="flex items-center gap-4">
        <!-- Theme Toggle -->
        <button @click="toggleTheme" 
          class="text-gray-600 dark:text-gray-300 hover:text-brand dark:hover:text-brand transition-colors p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
          :title="isDark ? 'Mudar para modo claro' : 'Mudar para modo escuro'">
          <SunIcon v-if="isDark" class="w-5 h-5" />
          <MoonIcon v-else class="w-5 h-5" />
        </button>

        <button class="text-gray-600 dark:text-gray-300 hover:text-brand dark:hover:text-brand transition-colors p-2 relative">
          <BellIcon class="w-5 h-5" />
          <span
            class="absolute -top-1 -right-1 bg-brand text-white rounded-full w-4 h-4 text-xs flex items-center justify-center font-bold">
            1
          </span>
        </button>

        <!-- User Profile -->
        <UserDropdown :user="user" />
      </div>
    </div>

    <!-- Search Bar para mobile -->
    <div class="mt-3 md:hidden">
      <div class="flex gap-0">
        <input type="text" placeholder="Pesquisar..."
          class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-l-lg bg-white dark:bg-dark-primary text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
        <button class="bg-brand text-white px-4 py-2 rounded-r-lg hover:bg-orange-600 transition-colors">
          <MagnifyingGlassIcon class="w-4 h-4" />
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import {
  Bars3Icon,
  MagnifyingGlassIcon,
  MoonIcon,
  SunIcon,
  BellIcon
} from '@heroicons/vue/24/outline'
import UserDropdown from './UserDropdown.vue'
import { useTheme } from './Composables/useTheme'

defineProps({
  sidebarCollapsed: Boolean,
  user: {
    type: Object,
    required: true
  }
})

defineEmits(['toggle-sidebar'])

const { isDark, toggleTheme } = useTheme()
</script>