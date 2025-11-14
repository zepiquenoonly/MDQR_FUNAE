<template>
  <header class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
      <!-- Left Section -->
      <div class="flex items-center gap-4 flex-1">
        <button 
          v-if="sidebarCollapsed"
          @click="$emit('toggle-sidebar')"
          class="text-gray-600 hover:text-gray-800 transition-colors lg:hidden"
        >
          ☰
        </button>
        
        <!-- Search Bar -->
        <div class="flex gap-0 max-w-md flex-1">
          <input
            type="text"
            placeholder="Pesquisar..."
            class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent"
          >
          <button class="bg-teal-600 text-white px-4 py-2 rounded-r-lg hover:bg-teal-700 transition-colors">
            🔍
          </button>
        </div>
      </div>

      <!-- Right Section -->
      <div class="flex items-center gap-4 ml-auto">
        <button class="text-gray-600 hover:text-gray-800 transition-colors p-2">
          🌙
        </button>
        
        <button class="text-gray-600 hover:text-gray-800 transition-colors p-2 relative">
          🔔
          <span class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 text-xs flex items-center justify-center font-bold">
            1
          </span>
        </button>

        <!-- User Profile -->
        <UserDropdown />
      </div>
    </div>
  </header>
</template>

<script setup>
import UserDropdown from './UserDropdown.vue'

defineProps({
  sidebarCollapsed: Boolean
})

defineEmits(['toggle-sidebar'])
</script>