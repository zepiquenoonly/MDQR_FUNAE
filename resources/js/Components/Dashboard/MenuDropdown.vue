<template>
  <div>
    <div
      :class="[
        'flex items-center gap-3 px-5 py-3 text-gray-600 cursor-pointer transition-all duration-200 border-l-3 border-transparent hover:bg-gray-50 hover:text-gray-800',
        isOpen ? 'bg-gray-50' : ''
      ]"
      @click="toggleDropdown"
    >
      <span class="text-lg flex-shrink-0 w-5 text-center">{{ icon }}</span>
      <span 
        :class="[
          'transition-opacity duration-300 flex-1',
          isCollapsed ? 'opacity-0' : 'opacity-100'
        ]"
      >
        {{ text }}
      </span>
      
      <!-- Badge -->
      <span 
        v-if="badge"
        :class="[
          'bg-red-500 text-white rounded-full px-2 py-1 text-xs font-bold transition-opacity duration-300',
          isCollapsed ? 'opacity-0' : 'opacity-100'
        ]"
      >
        {{ badge }}
      </span>
      
      <!-- Arrow -->
      <span 
        :class="[
          'text-gray-400 transition-all duration-300 text-sm',
          isCollapsed ? 'opacity-0' : 'opacity-100',
          isOpen ? 'rotate-90' : ''
        ]"
      >
        ›
      </span>
    </div>

    <!-- Dropdown Items -->
    <div
      :class="[
        'bg-gray-50 overflow-hidden transition-all duration-300',
        isOpen ? 'max-h-48' : 'max-h-0'
      ]"
    >
      <a
        v-for="(item, index) in items"
        :key="index"
        :class="[
          'flex items-center gap-3 py-2.5 px-5 pl-12 text-gray-600 cursor-pointer transition-all duration-200 border-l-3 border-transparent hover:bg-gray-100 hover:text-gray-800'
        ]"
        @click="handleItemClick(item)"
      >
        <span class="text-base flex-shrink-0 w-5 text-center">{{ item.icon }}</span>
        <span 
          :class="[
            'transition-opacity duration-300',
            isCollapsed ? 'opacity-0' : 'opacity-100'
          ]"
        >
          {{ item.text }}
        </span>
      </a>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  icon: String,
  text: String,
  badge: [String, Number],
  isCollapsed: Boolean,
  items: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['item-clicked'])

const isOpen = ref(false)

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
}

const handleItemClick = (item) => {
  emit('item-clicked', item.text)
  isOpen.value = false
}
</script>