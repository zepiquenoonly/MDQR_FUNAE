<template>
  <div style="zoom: 90%;" class="flex min-h-screen bg-gray-50">
    <!-- Sidebar (Fixed) -->
    <Sidebar :is-collapsed="sidebarCollapsed" @toggle-sidebar="toggleSidebar" />

    <!-- Main Content -->
    <div class="flex flex-col flex-1 h-screen">
      <!-- Header (Fixed) -->
      <Header :sidebar-collapsed="sidebarCollapsed" @toggle-sidebar="toggleSidebar" :user="user" />

      <!-- Page Content (Scrollable) -->
      <main style="zoom: 90%;"  class="flex-1 px-2 py-4 overflow-y-auto sm:px-3 lg:px-4 sm:py-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Sidebar from '@/Components/Dashboard/Sidebar.vue'
import Header from '@/Components/Dashboard/Header.vue'

defineProps({
  user: {
    type: Object,
    required: true
  }
})

const sidebarCollapsed = ref(false)

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}
</script>
