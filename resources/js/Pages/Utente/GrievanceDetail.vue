<template>
  <Layout :role="'utente'">
    <div class="max-w-4xl mx-auto">
      <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">
            Detalhes da Reclamação #{{ grievance.reference_number }}
          </h1>
        </div>

        <div class="space-y-6">
          <!-- Status e Informações Básicas -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Status Card -->
            <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
              <h3 class="mb-4 text-lg font-semibold text-gray-800">Status Atual</h3>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Estado:</span>
                  <span :class="getStatusBadgeClass(grievance.status)" class="px-3 py-1 text-sm font-semibold rounded-full">
                    {{ grievance.status_label }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Prioridade:</span>
                  <span :class="getPriorityBadgeClass(grievance.priority)" class="px-3 py-1 text-sm font-semibold rounded-full">
                    {{ grievance.priority_label }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Data de Submissão:</span>
                  <span class="text-sm text-gray-800">{{ formatDate(grievance.created_at) }}</span>
                </div>
              </div>
            </div>

            <!-- Informações do Usuário -->
            <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
              <h3 class="mb-4 text-lg font-semibold text-gray-800">Informações do Requerente</h3>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Nome:</span>
                  <span class="text-sm text-gray-800">{{ user.name }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Email:</span>
                  <span class="text-sm text-gray-800">{{ user.email }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Descrição da Reclamação -->
          <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-800">Descrição da Reclamação</h3>
            <p class="text-gray-700 whitespace-pre-wrap">{{ grievance.description }}</p>
          </div>

          <!-- Anexos -->
          <div v-if="grievance.attachments && grievance.attachments.length > 0" class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-800">Anexos</h3>
            <div class="space-y-2">
              <div v-for="attachment in grievance.attachments" :key="attachment.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                  <PaperClipIcon class="w-5 h-5 text-gray-500" />
                  <span class="text-sm text-gray-700">{{ attachment.original_filename }}</span>
                  <span class="text-xs text-gray-500">({{ formatFileSize(attachment.file_size) }})</span>
                </div>
                <a :href="`/storage/${attachment.file_path}`" target="_blank" class="text-blue-600 hover:text-blue-800">
                  <ArrowDownTrayIcon class="w-5 h-5" />
                </a>
              </div>
            </div>
          </div>

          <!-- Atualizações -->
          <div v-if="grievance.updates && grievance.updates.length > 0" class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-800">Histórico de Atualizações</h3>
            <div class="space-y-4">
              <div v-for="update in grievance.updates" :key="update.id" class="border-l-4 border-blue-500 pl-4 py-2">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-semibold text-gray-800">{{ update.action_label }}</span>
                  <span class="text-xs text-gray-500">{{ formatDate(update.created_at) }}</span>
                </div>
                <p v-if="update.description" class="text-sm text-gray-700">{{ update.description }}</p>
                <p v-if="update.comment" class="mt-1 text-sm italic text-gray-600">"{{ update.comment }}"</p>
                <p v-if="update.user" class="mt-1 text-xs text-gray-500">Por: {{ update.user.name }}</p>
              </div>
            </div>
          </div>

          <!-- Resolução -->
          <div v-if="grievance.resolution_notes && (grievance.status === 'resolved' || grievance.status === 'closed')" class="p-6 border-green-200 rounded-lg shadow-sm bg-green-50">
            <h3 class="mb-3 text-lg font-semibold text-green-800">Resolução</h3>
            <p class="text-gray-700 whitespace-pre-wrap">{{ grievance.resolution_notes }}</p>
            <div v-if="grievance.resolved_by" class="flex items-center mt-3 text-sm text-gray-600">
              <CheckCircleIcon class="w-4 h-4 mr-2 text-green-600" />
              <span>Resolvido por: <strong>{{ grievance.resolved_by.name }}</strong></span>
            </div>
          </div>
        </div>

        <div class="mt-8 flex justify-end">
          <a href="/utente/dashboard" class="px-6 py-2 font-semibold text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            Voltar ao Dashboard
          </a>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { computed } from 'vue'
import Layout from '@/Layouts/Layout.vue'
import {
  PaperClipIcon,
  ArrowDownTrayIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  user: Object,
  grievance: Object
})

const getStatusBadgeClass = (status) => {
  const classes = {
    'submitted': 'bg-blue-100 text-blue-800',
    'under_review': 'bg-yellow-100 text-yellow-800',
    'assigned': 'bg-purple-100 text-purple-800',
    'in_progress': 'bg-orange-100 text-orange-800',
    'pending_approval': 'bg-indigo-100 text-indigo-800',
    'resolved': 'bg-green-100 text-green-800',
    'closed': 'bg-gray-100 text-gray-800',
    'rejected': 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPriorityBadgeClass = (priority) => {
  const classes = {
    'low': 'bg-green-100 text-green-800',
    'medium': 'bg-yellow-100 text-yellow-800',
    'high': 'bg-orange-100 text-orange-800',
    'urgent': 'bg-red-100 text-red-800'
  }
  return classes[priority] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('pt-PT', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
}
</script>