<template>
  <div v-if="hasNotifications" class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 rounded-lg p-4 shadow-sm">
    <div class="flex items-start">
      <div class="flex-shrink-0">
        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
          <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
        </svg>
      </div>
      <div class="ml-3 flex-1">
        <h3 class="text-sm font-semibold text-blue-900 mb-1">
          Você tem {{ notifications.length }} notificação(ões) não lida(s)
        </h3>
        <div class="space-y-2 max-h-40 overflow-y-auto">
          <div v-for="notification in displayNotifications" :key="notification.id" 
            class="text-sm text-blue-800 bg-white rounded p-2 hover:bg-blue-50 transition-colors cursor-pointer"
            @click="viewNotification(notification)">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <p class="font-medium">{{ notification.subject }}</p>
                <p class="text-xs text-blue-600 mt-1">{{ notification.created_at }}</p>
              </div>
              <span v-if="notification.grievance" class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded ml-2">
                {{ notification.grievance.reference_number }}
              </span>
            </div>
          </div>
        </div>
        <div class="mt-3 flex items-center justify-between">
          <button 
            @click="markAllRead"
            class="text-sm font-medium text-blue-700 hover:text-blue-900 transition-colors"
          >
            Marcar todas como lidas
          </button>
          <button 
            v-if="notifications.length > 3"
            @click="showAll = !showAll"
            class="text-sm font-medium text-blue-700 hover:text-blue-900 transition-colors"
          >
            {{ showAll ? 'Mostrar menos' : `Ver todas (${notifications.length})` }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

const page = usePage()
const showAll = ref(false)

const notifications = computed(() => {
  return page.props.notifications || []
})

const hasNotifications = computed(() => {
  return notifications.value.length > 0
})

const displayNotifications = computed(() => {
  if (showAll.value) {
    return notifications.value
  }
  return notifications.value.slice(0, 3)
})

const markAllRead = async () => {
  try {
    const notificationIds = notifications.value.map(n => n.id)
    
    await fetch('/utente/notifications/read', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ notification_ids: notificationIds })
    })

    // Recarregar a página para atualizar as notificações
    router.reload()
  } catch (error) {
    console.error('Erro ao marcar notificações:', error)
  }
}

const viewNotification = (notification) => {
  // Marcar como lida e redirecionar para detalhes
  if (notification.grievance) {
    router.visit('/utente/dashboard')
  }
}
</script>
