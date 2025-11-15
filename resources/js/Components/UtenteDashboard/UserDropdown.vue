<template>
  <div class="relative">
    <div class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-gray-50 transition-colors"
      @click="toggleDropdown">
      <div class="w-9 h-9 bg-brand rounded-full flex items-center justify-center text-white font-semibold text-sm">
        {{ userInitials }}
      </div>
      <div class="hidden sm:block text-left">
        <div class="text-sm font-medium text-gray-900"><strong>{{ displayName }}</strong></div>
        <div class="text-xs text-gray-500">{{ safeUser.role }}</div>
      </div>
      <ChevronDownIcon class="text-gray-500 w-4 h-4" />
    </div>

    <!-- Dropdown Menu -->
    <div v-if="isOpen"
      class="absolute top-full right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
      <div class="px-4 py-2 border-b border-gray-100">
        <div class="text-sm font-medium text-gray-900">{{ displayName }}</div>
        <div class="text-xs text-gray-500 truncate">{{ safeUser.email }}</div>
        <div class="text-xs text-gray-400">{{ safeUser.role }}</div>
      </div>

      <a v-for="item in dropdownItems" :key="item.text" :class="[
        'flex items-center gap-3 px-4 py-2 text-sm transition-colors cursor-pointer',
        item.class || 'text-gray-700 hover:bg-gray-50'
      ]" @click="handleItemClick(item)">
        <component :is="item.icon" class="w-4 h-4" />
        <span>{{ item.text }}</span>
      </a>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  UserIcon,
  ChevronDownIcon,
  LockClosedIcon,
  ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

const isOpen = ref(false)

const dropdownItems = [
  { icon: UserIcon, text: 'Perfil' },
  { icon: LockClosedIcon, text: 'Bloqueio de Tela' },
  { icon: ArrowRightOnRectangleIcon, text: 'Sair', class: 'text-red-600 hover:bg-red-50' }
]

// Computed property para garantir dados seguros
const safeUser = computed(() => {
  const userData = {
    name: props.user?.name || 'Usuário',
    email: props.user?.email || '',
    role: props.user?.role || 'Utente',
    ...props.user
  }
  return userData
})

// Nome para exibição
const displayName = computed(() => {
  return safeUser.value.name || 'Usuário'
})

// Computed para as iniciais do usuário - CORRIGIDO
const userInitials = computed(() => {
  const name = displayName.value
  if (!name || name === 'Usuário') return 'U'

  return name
    .split(' ')
    .map(word => word.charAt(0)) // Use charAt em vez de indexação direta
    .join('')
    .toUpperCase()
    .substring(0, 2)
})

console.log('👤 UserDropdown - User data:', safeUser.value)
console.log('👤 UserDropdown - User name:', safeUser.value.name)
console.log('👤 UserDropdown - User initials:', userInitials.value)

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
}

const handleItemClick = (item) => {
  if (item.text === 'Sair') {
    if (confirm('Tem certeza que deseja sair?')) {
      router.post('/logout')
    }
  } else {
    console.log('Action:', item.text)
  }
  isOpen.value = false
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  const dropdown = event.target.closest('.relative')
  if (!dropdown) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>