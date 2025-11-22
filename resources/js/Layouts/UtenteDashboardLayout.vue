<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <Sidebar :is-collapsed="sidebarCollapsed" @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed"
      @change-view="$emit('change-view', $event)" />

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <Header :sidebar-collapsed="sidebarCollapsed" @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed"
        :user="user" />

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

defineEmits(['change-view'])

const sidebarCollapsed = ref(false)

</script>