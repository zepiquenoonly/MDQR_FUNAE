<template>
  <div class="relative">
    <div class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-gray-50 transition-colors"
      @click="toggleDropdown">
      <div class="w-9 h-9 bg-gray-400 rounded-full flex items-center justify-center text-white">
        <UserIcon class="w-5 h-5" />
      </div>
      <span class="text-sm font-medium text-gray-700 hidden sm:block">
        {{ user?.name || 'Usuário' }}
      </span>
      <ChevronDownIcon class="text-gray-500 w-4 h-4" />
    </div>

    <!-- Dropdown Menu -->
    <div v-if="isOpen"
      class="absolute top-full right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
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
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  UserIcon,
  ChevronDownIcon,
  LockClosedIcon,
  ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline'

// Defina um valor padrão para a prop user
const props = defineProps({
  user: {
    type: Object,
    default: () => ({
      name: 'Usuário',
      email: ''
    })
  }
})

const isOpen = ref(false)

const dropdownItems = [
  { icon: UserIcon, text: 'Perfil' },
  { icon: LockClosedIcon, text: 'Bloqueio de Tela' },
  { icon: ArrowRightOnRectangleIcon, text: 'Sair', class: 'text-red-600 hover:bg-red-50' }
]

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
}

const handleItemClick = (item) => {
  if (item.text === 'Sair') {
    if (confirm('Tem certeza que deseja sair?')) {
      router.post(route('logout'))
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