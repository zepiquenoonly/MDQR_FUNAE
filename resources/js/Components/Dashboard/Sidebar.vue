<template>
  <aside 
    :class="[
      'bg-white border-r border-gray-200 flex flex-col transition-all duration-300 overflow-hidden',
      isCollapsed ? 'w-20' : 'w-64'
    ]"
  >
    <!-- Sidebar Header -->
    <div class="p-5 flex items-center justify-between min-h-20">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center text-white font-bold text-sm">
          M
        </div>
        <span 
          :class="[
            'text-lg font-bold text-gray-800 transition-opacity duration-300',
            isCollapsed ? 'opacity-0' : 'opacity-100'
          ]"
        >
          MDQR
        </span>
      </div>
      <button 
        @click="$emit('toggle-sidebar')"
        class="text-gray-600 hover:text-gray-800 transition-colors"
      >
        ☰
      </button>
    </div>

    <!-- Menu Items -->
    <div class="flex-1 overflow-y-auto">
      <MenuSection 
        :is-collapsed="isCollapsed"
        @item-clicked="handleMenuItemClick"
      />
    </div>
  </aside>
</template>

<script setup>
import MenuSection from './MenuSection.vue'

defineProps({
  isCollapsed: Boolean
})

defineEmits(['toggle-sidebar'])

const handleMenuItemClick = (item) => {
  // Handle navigation or other actions
  console.log('Menu item clicked:', item)
}
</script>