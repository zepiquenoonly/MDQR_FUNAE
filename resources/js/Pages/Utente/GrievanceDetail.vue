<template>
  <Layout :role="'utente'">
    <div class="max-w-4xl mx-auto">
      <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">
            Detalhes da Submissão #{{ grievance.reference_number }}
          </h1>
        </div>

        <div class="space-y-6">
          <!-- Status e Informações Básicas -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Status Card -->
            <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
              <h3 class="mb-4 text-lg font-semibold text-gray-800">Status Actual</h3>
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
                  <span class="text-sm text-gray-600">Tipo:</span>
                  <span class="text-sm text-gray-800">{{ grievance.type_label || grievance.category }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Data de Submissão:</span>
                  <span class="text-sm text-gray-800">{{ formatDate(grievance.submitted_at || grievance.created_at) }}</span>
                </div>
                <div v-if="grievance.assigned_at" class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Atribuído em:</span>
                  <span class="text-sm text-gray-800">{{ formatDate(grievance.assigned_at) }}</span>
                </div>
                <div v-if="grievance.resolved_at" class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Resolvido em:</span>
                  <span class="text-sm text-gray-800">{{ formatDate(grievance.resolved_at) }}</span>
                </div>
              </div>
            </div>

            <!-- Informações do Requerente -->
            <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
              <h3 class="mb-4 text-lg font-semibold text-gray-800">Informações do Requerente</h3>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Nome:</span>
                  <span class="text-sm text-gray-800">{{ grievance.contact_name || user.name }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Email:</span>
                  <span class="text-sm text-gray-800">{{ grievance.contact_email || user.email }}</span>
                </div>
                <div v-if="grievance.contact_phone" class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Telefone:</span>
                  <span class="text-sm text-gray-800">{{ grievance.contact_phone }}</span>
                </div>
                <div v-if="grievance.gender" class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Género:</span>
                  <span class="text-sm text-gray-800">{{ getGenderLabel(grievance.gender) }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Tipo de Submissão:</span>
                  <span :class="grievance.is_anonymous ? 'text-orange-600' : 'text-green-600'" class="text-sm font-medium">
                    {{ grievance.is_anonymous ? 'Anónima' : 'Identificada' }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Descrição da Reclamação -->
          <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-800">Descrição da Reclamação</h3>
            <p class="text-gray-700 whitespace-pre-wrap">{{ grievance.description }}</p>
          </div>

          <!-- Localização -->
          <div v-if="grievance.province || grievance.district || grievance.locality" class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-800">Localização</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-if="grievance.province" class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Província:</span>
                <span class="text-sm text-gray-800">{{ grievance.province }}</span>
              </div>
              <div v-if="grievance.district" class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Distrito:</span>
                <span class="text-sm text-gray-800">{{ grievance.district }}</span>
              </div>
              <div v-if="grievance.municipal_district" class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Posto Administrativo:</span>
                <span class="text-sm text-gray-800">{{ grievance.municipal_district }}</span>
              </div>
              <div v-if="grievance.locality" class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Localidade:</span>
                <span class="text-sm text-gray-800">{{ grievance.locality }}</span>
              </div>
            </div>
            <div v-if="grievance.location_details" class="mt-4">
              <span class="text-sm text-gray-600">Detalhes da Localização:</span>
              <p class="text-sm text-gray-800 mt-1">{{ grievance.location_details }}</p>
            </div>
          </div>

          <!-- Projeto Relacionado -->
          <div v-if="grievance.project" class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-800">Projeto Relacionado</h3>
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Nome do Projeto:</span>
              <span class="text-sm text-blue-600 font-medium">{{ grievance.project.name }}</span>
            </div>
          </div>

          <!-- Responsável pela Atribuição -->
          <div v-if="grievance.assigned_to" class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-800">Responsável pela Atribuição</h3>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Técnico Responsável:</span>
                <span class="text-sm text-gray-800">{{ grievance.assigned_to.name }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Email:</span>
                <span class="text-sm text-gray-800">{{ grievance.assigned_to.email }}</span>
              </div>
            </div>
          </div>

          <!-- Anexos -->
          <div v-if="grievance.attachments && grievance.attachments.length > 0" class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-800">Anexos ({{ grievance.attachments.length }})</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="attachment in grievance.attachments" :key="attachment.id" class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-start space-x-3">
                  <!-- Preview para imagens -->
                  <div v-if="isImage(attachment.mime_type)" class="flex-shrink-0">
                    <img :src="`/storage/${attachment.file_path}`" :alt="attachment.original_filename"
                         class="w-16 h-16 object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-80 transition-opacity"
                         @click="openImageModal(attachment)" />
                  </div>
                  <!-- Ícone para outros tipos de arquivo -->
                  <div v-else class="flex-shrink-0">
                    <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                      <DocumentIcon class="w-8 h-8 text-gray-500" />
                    </div>
                  </div>

                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ attachment.original_filename }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                      {{ formatFileSize(attachment.file_size) }} • {{ getFileTypeLabel(attachment.mime_type) }}
                    </p>
                    <p v-if="attachment.uploaded_at" class="text-xs text-gray-400 mt-1">
                      Enviado em {{ formatDate(attachment.uploaded_at) }}
                    </p>
                    <div class="mt-3 flex space-x-2">
                      <a :href="`/storage/${attachment.file_path}`" target="_blank"
                         class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors">
                        <ArrowDownTrayIcon class="w-3 h-3 mr-1" />
                        Download
                      </a>
                      <button v-if="isImage(attachment.mime_type)" @click="openImageModal(attachment)"
                              class="inline-flex items-center px-3 py-1 text-xs font-medium text-green-600 bg-green-50 rounded-md hover:bg-green-100 transition-colors">
                        <EyeIcon class="w-3 h-3 mr-1" />
                        Visualizar
                      </button>
                    </div>
                  </div>
                </div>
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
            <div class="mt-4 space-y-2">
              <div v-if="grievance.resolved_by" class="flex items-center text-sm text-gray-600">
                <CheckCircleIcon class="w-4 h-4 mr-2 text-green-600" />
                <span>Resolvido por: <strong>{{ grievance.resolved_by.name }}</strong> ({{ grievance.resolved_by.email }})</span>
              </div>
              <div v-if="grievance.resolved_at" class="flex items-center text-sm text-gray-600">
                <ClockIcon class="w-4 h-4 mr-2 text-green-600" />
                <span>Data da resolução: <strong>{{ formatDate(grievance.resolved_at) }}</strong></span>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-8 flex justify-end">
          <a href="/utente/dashboard" class="px-6 py-2 font-semibold text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            Voltar ao Dashboard
          </a>
        </div>

        <!-- Modal de Visualização de Imagem -->
        <div v-if="imageModal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-75" @click="closeImageModal">
          <div class="relative max-w-4xl max-h-full">
            <img :src="imageModal.attachment.url" :alt="imageModal.attachment.original_filename"
                 class="max-w-full max-h-full object-contain rounded-lg" />
            <button @click="closeImageModal" class="absolute top-4 right-4 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-75 transition-colors">
              <XMarkIcon class="w-6 h-6" />
            </button>
            <div class="absolute bottom-4 left-4 right-4 bg-black bg-opacity-50 rounded-lg p-4 text-white">
              <p class="font-medium">{{ imageModal.attachment.original_filename }}</p>
              <p class="text-sm opacity-75">{{ formatFileSize(imageModal.attachment.file_size) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { computed, ref } from 'vue'
import Layout from '@/Layouts/UnifiedLayout.vue'
import {
  PaperClipIcon,
  ArrowDownTrayIcon,
  CheckCircleIcon,
  ClockIcon,
  EyeIcon,
  DocumentIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  user: Object,
  grievance: Object
})

const imageModal = ref({
  open: false,
  attachment: null
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

const getGenderLabel = (gender) => {
  const labels = {
    'male': 'Masculino',
    'female': 'Feminino',
    'other': 'Outro'
  }
  return labels[gender] || gender
}

const isImage = (mimeType) => {
  return mimeType && mimeType.startsWith('image/')
}

const getFileTypeLabel = (mimeType) => {
  if (!mimeType) return 'Arquivo'
  if (mimeType.startsWith('image/')) return 'Imagem'
  if (mimeType.startsWith('audio/')) return 'Áudio'
  if (mimeType.startsWith('video/')) return 'Vídeo'
  if (mimeType.includes('pdf')) return 'PDF'
  if (mimeType.includes('document') || mimeType.includes('word')) return 'Documento'
  return 'Arquivo'
}

const openImageModal = (attachment) => {
  imageModal.value = {
    open: true,
    attachment: attachment
  }
}

const closeImageModal = () => {
  imageModal.value = {
    open: false,
    attachment: null
  }
}
</script>