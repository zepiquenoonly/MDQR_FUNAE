<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar (Fixed) -->
    <Sidebar :is-collapsed="sidebarCollapsed" @toggle-sidebar="toggleSidebar" />

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-screen">
      <!-- Header (Fixed) -->
      <Header :sidebar-collapsed="sidebarCollapsed" @toggle-sidebar="toggleSidebar" :user="user" />

      <!-- Page Content (Scrollable) -->
      <main class="flex-1 overflow-y-auto px-2 sm:px-3 lg:px-4 py-4 sm:py-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import Sidebar from '@/Components/Dashboard/Sidebar.vue'
import Header from '@/Components/Dashboard/Header.vue'

defineProps({
  user: {
    type: Object,
    required: true
  }
})

const sidebarCollapsed = ref(false)

// Detectar se é mobile e definir estado inicial
const checkMobile = () => {
  const isMobile = window.innerWidth < 1024
  sidebarCollapsed.value = isMobile // Fechado em mobile, aberto em desktop
}

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})
</script>
