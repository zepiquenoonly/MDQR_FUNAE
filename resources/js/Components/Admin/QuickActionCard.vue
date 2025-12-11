<template>
  <a
    :href="href"
    class="block bg-gradient-to-br from-white to-gray-50 rounded-lg p-6 border border-gray-200 hover:border-brand hover:shadow-lg transition-all duration-200 group"
  >
    <div class="flex items-start space-x-4">
      <div class="p-3 bg-brand/10 rounded-lg group-hover:bg-brand/20 transition-colors">
        <component :is="iconComponent" class="w-6 h-6 text-brand" />
      </div>
      <div class="flex-1">
        <h3 class="font-semibold text-gray-900 mb-1 group-hover:text-brand transition-colors">
          {{ title }}
        </h3>
        <p class="text-sm text-gray-600">{{ description }}</p>
      </div>
    </div>
  </a>
</template>

<script setup>
import { computed } from 'vue'
import {
  UserGroupIcon,
  BuildingOfficeIcon,
  FolderIcon,
  Cog6ToothIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    required: true
  },
  icon: {
    type: String,
    default: 'users'
  },
  href: {
    type: String,
    required: true
  }
})

const iconComponent = computed(() => {
  const icons = {
    'users': UserGroupIcon,
    'building': BuildingOfficeIcon,
    'folder': FolderIcon,
    'cog': Cog6ToothIcon
  }
  return icons[props.icon] || UserGroupIcon
})
</script>
