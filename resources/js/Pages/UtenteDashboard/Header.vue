<template>
  <header class="px-6 py-4 bg-white border-b border-gray-200">
    <div class="flex items-center justify-between">
      <!-- Left Section -->
      <div class="flex items-center gap-4">
        <!-- Botão para abrir sidebar quando fechada -->
        <button v-if="sidebarCollapsed" @click="$emit('toggle-sidebar')"
          class="transition-colors hover:text-orange-600">
          <Bars3Icon class="w-5 h-5" />
        </button>

        <!-- Search Bar -->

      </div>

      <!-- Right Section -->
      <div class="flex items-center gap-4">
        <button class="p-2 text-gray-600 transition-colors hover:text-brand">
          <MoonIcon class="w-5 h-5" />
        </button>

        <button class="relative p-2 text-gray-600 transition-colors hover:text-brand">
          <BellIcon class="w-5 h-5" />
          <span
            class="absolute flex items-center justify-center w-4 h-4 text-xs font-bold text-white rounded-full -top-1 -right-1 bg-brand">
            1
          </span>
        </button>

        <!-- User Profile -->
        <UserDropdown :user="safeUser" />
      </div>
    </div>

    <!-- Search Bar para mobile -->

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
