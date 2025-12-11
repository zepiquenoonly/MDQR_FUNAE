<template>
  <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-gray-600 mb-1">{{ title }}</p>
        <p class="text-3xl font-bold" :class="colorClass">{{ value }}</p>
      </div>
      <div class="p-3 rounded-lg" :class="bgColorClass">
        <component :is="iconComponent" class="w-8 h-8" :class="colorClass" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  UserGroupIcon,
  BuildingOfficeIcon,
  FolderIcon,
  UserIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [Number, String],
    required: true
  },
  icon: {
    type: String,
    default: 'users'
  },
  color: {
    type: String,
    default: 'blue'
  }
})

const iconComponent = computed(() => {
  const icons = {
    'users': UserGroupIcon,
    'building': BuildingOfficeIcon,
    'folder': FolderIcon,
    'user-check': UserIcon
  }
  return icons[props.icon] || UserGroupIcon
})

const colorClass = computed(() => {
  const colors = {
    'blue': 'text-blue-600',
    'green': 'text-green-600',
    'purple': 'text-purple-600',
    'orange': 'text-orange-600'
  }
  return colors[props.color] || 'text-blue-600'
})

const bgColorClass = computed(() => {
  const colors = {
    'blue': 'bg-blue-100',
    'green': 'bg-green-100',
    'purple': 'bg-purple-100',
    'orange': 'bg-orange-100'
  }
  return colors[props.color] || 'bg-blue-100'
})
</script>
