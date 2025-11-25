<template>
  <div class="fixed top-4 right-4 z-50 pointer-events-none">
    <transition-group name="notification" tag="div" class="space-y-2">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        :class="[
          'pointer-events-auto rounded-lg shadow-lg px-4 py-3 flex items-center gap-3 animate-in fade-in slide-in-from-top-2 duration-300',
          notificationClasses(notification.type)
        ]"
      >
        <component :is="getIcon(notification.type)" class="h-5 w-5 flex-shrink-0" />
        <span class="flex-1 text-sm font-medium">{{ notification.message }}</span>
        <button
          @click="removeNotification(notification.id)"
          class="text-opacity-70 hover:text-opacity-100 transition-opacity flex-shrink-0 ml-2"
        >
          <XMarkIcon class="h-4 w-4" />
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  CheckCircleIcon,
  ExclamationCircleIcon,
  InformationCircleIcon,
  XCircleIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'
import { useNotification } from '@/Composables/useNotification'

const { notifications, removeNotification } = useNotification()

const notificationClasses = (type) => {
  const baseClasses = 'text-white'
  switch (type) {
    case 'success':
      return `${baseClasses} bg-emerald-500 dark:bg-emerald-600`
    case 'error':
      return `${baseClasses} bg-red-500 dark:bg-red-600`
    case 'warning':
      return `${baseClasses} bg-amber-500 dark:bg-amber-600`
    case 'info':
    default:
      return `${baseClasses} bg-blue-500 dark:bg-blue-600`
  }
}

const getIcon = (type) => {
  switch (type) {
    case 'success':
      return CheckCircleIcon
    case 'error':
      return XCircleIcon
    case 'warning':
      return ExclamationCircleIcon
    case 'info':
    default:
      return InformationCircleIcon
  }
}
</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
  transition: all 300ms ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

.notification-move {
  transition: transform 300ms ease;
}
</style>
