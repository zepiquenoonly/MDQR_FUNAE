<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <Sidebar 
      :is-collapsed="sidebarCollapsed" 
      @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed"
    />
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <Header 
        :sidebar-collapsed="sidebarCollapsed"
        @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed"
        :user="user"
      />
      
      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Sidebar from '@/Components/UtenteDashboard/Sidebar.vue'
import Header from '@/Components/UtenteDashboard/Header.vue'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

console.log('📦 Layout - User data received:', props.user)
console.log('📦 Layout - User name:', props.user?.name)
console.log('📦 Layout - User email:', props.user?.email)

const sidebarCollapsed = ref(false)
</script>