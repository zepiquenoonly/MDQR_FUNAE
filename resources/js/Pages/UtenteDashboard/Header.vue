<template>
  <header class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
      <!-- Left Section -->
      <div class="flex items-center gap-4">
        <!-- Botão para abrir sidebar quando fechada -->
        <button v-if="sidebarCollapsed" @click="$emit('toggle-sidebar')"
          class="hover:text-orange-600 transition-colors">
          <Bars3Icon class="w-5 h-5" />
        </button>

        <!-- Search Bar -->
        <div class="hidden md:flex gap-0">
          <input type="text" placeholder="Pesquisar..."
            class="w-64 px-4 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
          <button class="bg-brand text-white px-4 py-2 rounded-r-lg hover:bg-orange-600 transition-colors">
            <MagnifyingGlassIcon class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Right Section -->
      <div class="flex items-center gap-4">
        <button class="text-gray-600 hover:text-brand transition-colors p-2">
          <MoonIcon class="w-5 h-5" />
        </button>

        <button class="text-gray-600 hover:text-brand transition-colors p-2 relative">
          <BellIcon class="w-5 h-5" />
          <span
            class="absolute -top-1 -right-1 bg-brand text-white rounded-full w-4 h-4 text-xs flex items-center justify-center font-bold">
            1
          </span>
        </button>

        <!-- User Profile -->
        <UserDropdown :user="safeUser" />
      </div>
    </div>

    <!-- Search Bar para mobile -->
    <div class="mt-3 md:hidden">
      <div class="flex gap-0">
        <input type="text" placeholder="Pesquisar..."
          class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
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
  BellIcon
} from '@heroicons/vue/24/outline'
import UserDropdown from './UserDropdown.vue'
import { computed } from 'vue'

const props = defineProps({
  sidebarCollapsed: Boolean,
  user: {
    type: Object,
    required: true
  }
})

defineEmits(['toggle-sidebar'])

// Computed property - COM FALLBACK SEGURO
const safeUser = computed(() => {
  const userData = {
    name: props.user?.name || 'Usuário', // ← Fallback adicionado
    email: props.user?.email || '',
    role: props.user?.role || 'Utente',
    ...props.user
  }
  return userData
})

console.log('Header - User data received:', props.user)
console.log('Header - Safe user:', safeUser.value)
console.log('Header - User name:', safeUser.value.name)
</script>
