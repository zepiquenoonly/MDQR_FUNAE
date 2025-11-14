<template>
  <div class="relative">
    <div
      class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-gray-50 transition-colors"
      @click="toggleDropdown"
    >
      <div class="w-9 h-9 bg-gray-400 rounded-full flex items-center justify-center text-white">
        👤
      </div>
      <span class="text-sm font-medium text-gray-700 hidden sm:block">
        Hélio Mocumbi
      </span>
      <span class="text-gray-500 text-sm">▼</span>
    </div>

    <!-- Dropdown Menu -->
    <div
      v-if="isOpen"
      class="absolute top-full right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
    >
      <a
        v-for="item in dropdownItems"
        :key="item.text"
        :class="[
          'flex items-center gap-3 px-4 py-2 text-sm transition-colors',
          item.class || 'text-gray-700 hover:bg-gray-50'
        ]"
        @click="handleItemClick(item)"
      >
        <span class="text-base">{{ item.icon }}</span>
        <span>{{ item.text }}</span>
      </a>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const isOpen = ref(false)

const dropdownItems = [
  { icon: '👤', text: 'Perfil' },
  { icon: '🔒', text: 'Bloqueio de Tela' },
  { icon: '🚪', text: 'Sair', class: 'text-red-600 hover:bg-red-50' }
]

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
}

const handleItemClick = (item) => {
  if (item.text === 'Sair') {
    if (confirm('Tem certeza que deseja sair?')) {
      // Logout logic here
      console.log('Logging out...')
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

/*onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})*/

/*onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})*/
</script>