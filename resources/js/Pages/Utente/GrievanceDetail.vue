<template>
  <Layout :role="'utente'">
    <div class="min-h-screen bg-gradient-to-br from-orange-50 via-primary-50 to-amber-50">
      <!-- Hero Section -->
      <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-600 via-orange-600 to-amber-700"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent"></div>

        <!-- Floating Elements -->
        <div class="absolute top-20 left-10 w-32 h-32 bg-white/10 rounded-full blur-xl animate-pulse"></div>
        <div class="absolute bottom-20 right-10 w-48 h-48 bg-orange-300/20 rounded-full blur-2xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/3 w-24 h-24 bg-amber-300/15 rounded-full blur-lg animate-pulse delay-500"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
          <div class="text-center">
            <div class="inline-flex items-center justify-center p-4 bg-white/10 backdrop-blur-xl rounded-2xl mb-6 border border-white/20 shadow-2xl shadow-black/10">
              <DocumentTextIcon class="w-12 h-12 text-white drop-shadow-lg" />
            </div>
            <h1 class="text-3xl sm:text-4xl font-bold text-white mb-4 tracking-tight drop-shadow-2xl">
              Detalhes da <span class="bg-gradient-to-r from-orange-200 to-amber-200 bg-clip-text text-transparent">Submissão</span>
            </h1>
            <div class="inline-block px-6 py-3 bg-white/10 backdrop-blur-md rounded-xl border border-white/20 shadow-lg">
              <p class="text-white font-mono text-lg font-semibold drop-shadow-lg">
                {{ grievance.reference_number }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 pb-20 z-10">
        <div class="space-y-8 animate-fade-in">

        <!-- Main Grievance Card - fundo BRANCO SÓLIDO -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 border border-gray-100 relative overflow-hidden group">
          <!-- Header -->
          <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 mb-8">
            <div class="flex-1">
              <div class="flex items-center gap-4 mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-brand to-brand-dark rounded-xl flex items-center justify-center shadow-lg shadow-brand/20">
                  <DocumentTextIcon class="w-7 h-7 text-white" />
                </div>
                <div>
                  <h2 class="text-2xl font-bold font-mono text-gray-900">
                    {{ grievance.reference_number }}
                  </h2>
                  <div class="flex flex-wrap items-center gap-4 mt-2">
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-100 rounded-full text-sm text-gray-700">
                      <ClockIcon class="w-4 h-4 text-brand" />
                      {{ new Date(grievance.submitted_at).toLocaleDateString('pt-PT', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                      }) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <StatusBadge :status="grievance.status" :label="grievance.status_label" size="lg" />
          </div>

          <!-- Stats Grid - Glassmorphism Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8 relative z-10">
            <!-- Type Card -->
            <div v-if="grievance.type" class="bg-white/70 backdrop-blur-md p-5 rounded-2xl border border-white/40 hover:border-purple-300/50 hover:shadow-xl hover:shadow-purple-500/10 transition-all duration-300 group/card hover:-translate-y-1 shadow-lg shadow-purple-500/5 hover:bg-white/80">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500/20 to-pink-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                  <DocumentTextIcon class="w-6 h-6 text-purple-600" />
                </div>
                <div>
                  <p class="text-xs font-medium text-gray-600 uppercase tracking-wider">Tipo</p>
                  <p class="text-sm font-semibold text-gray-900">
                    {{ grievance.type_label || grievance.type }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Priority Card -->
            <div v-if="grievance.priority" class="bg-white/70 backdrop-blur-md p-5 rounded-2xl border border-white/40 hover:border-red-300/50 hover:shadow-xl hover:shadow-red-500/10 transition-all duration-300 group/card hover:-translate-y-1 shadow-lg shadow-red-500/5 hover:bg-white/80">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-red-500/20 to-orange-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                  <ExclamationTriangleIcon class="w-6 h-6 text-red-600" />
                </div>
                <div>
                  <p class="text-xs font-medium text-gray-600 uppercase tracking-wider">Prioridade</p>
                  <p class="text-sm font-semibold text-gray-900">
                    {{ grievance.priority_label || grievance.priority }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Location Card -->
            <div v-if="grievance.province" class="bg-white/70 backdrop-blur-md p-5 rounded-2xl border border-white/40 hover:border-cyan-300/50 hover:shadow-xl hover:shadow-cyan-500/10 transition-all duration-300 group/card hover:-translate-y-1 shadow-lg shadow-cyan-500/5 hover:bg-white/80">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-cyan-500/20 to-blue-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                  <MapIcon class="w-6 h-6 text-cyan-600" />
                </div>
                <div>
                  <p class="text-xs font-medium text-gray-600 uppercase tracking-wider">Localização</p>
                  <p class="text-sm font-semibold text-gray-900">
                    {{ grievance.province }}
                    <span v-if="grievance.district" class="text-gray-600"> • {{ grievance.district }}</span>
                  </p>
                </div>
              </div>
            </div>

            <!-- Technician Card -->
            <div v-if="grievance.assigned_user" class="bg-white/70 backdrop-blur-md p-5 rounded-2xl border border-white/40 hover:border-emerald-300/50 hover:shadow-xl hover:shadow-emerald-500/10 transition-all duration-300 group/card hover:-translate-y-1 shadow-lg shadow-emerald-500/5 hover:bg-white/80">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-500/20 to-green-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                  <UserIcon class="w-6 h-6 text-emerald-600" />
                </div>
                <div>
                  <p class="text-xs font-medium text-gray-600 uppercase tracking-wider">Técnico Responsável</p>
                  <p class="text-sm font-semibold text-gray-900">
                    {{ grievance.assigned_user.name }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Content Sections -->
          <div class="space-y-8">
            <!-- Description -->
            <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-brand/10 to-brand-dark/10 rounded-lg flex items-center justify-center">
                  <DocumentTextIcon class="w-5 h-5 text-brand" />
                </div>
                Descrição da Reclamação
              </h3>
              <div class="prose prose-gray max-w-none text-gray-700 bg-white rounded-lg p-6 border border-gray-200"
                  v-html="grievance.description"></div>
            </div>

            <!-- Project Information Section (if exists) -->
            <div v-if="grievance.project" class="mb-8">
              <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-200">
                <div class="flex items-start gap-4">
                  <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
                    <BuildingOfficeIcon class="w-6 h-6 text-white" />
                  </div>
                  <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Projeto Relacionado</h3>
                    <p class="text-xl font-bold text-blue-900 mb-3">{{ grievance.project.name }}</p>
                    <p v-if="grievance.project.description" class="text-gray-700 mb-4 leading-relaxed bg-white/60 rounded-lg p-4 border border-blue-100">
                      {{ grievance.project.description }}
                    </p>
                    <div v-if="grievance.project.province || grievance.project.district" class="flex flex-wrap items-center gap-3">
                      <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-white rounded-full text-sm text-gray-700 border border-blue-200">
                        <MapPinIcon class="w-4 h-4 text-blue-600" />
                        {{ grievance.project.province }}
                        <span v-if="grievance.project.district" class="text-gray-500">• {{ grievance.project.district }}</span>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          <!-- Two Column Layout - Glassmorphism Cards -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Timeline -->
            <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/30 shadow-lg shadow-primary-500/5 hover:bg-white/80 transition-all duration-300">
              <UpdatesTimeline :updates="grievance.updates" />
            </div>

            <!-- Attachments -->
            <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/30 shadow-lg shadow-orange-500/5 hover:bg-white/80 transition-all duration-300">
              <AttachmentsGallery :attachments="grievance.attachments" />
            </div>
          </div>

          <!-- Resolution Notes -->
          <div v-if="grievance.resolution_notes && grievance.status === 'resolved'"
              class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 border border-green-200">
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center shadow-lg flex-shrink-0">
                <CheckCircleIcon class="w-6 h-6 text-white" />
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Notas de Resolução</h3>
                <p class="text-gray-700 leading-relaxed bg-white rounded-lg p-5 border border-green-200">
                  {{ grievance.resolution_notes }}
                </p>
                <div v-if="grievance.resolved_by"
                    class="flex items-center gap-3 mt-4 pt-4 border-t border-green-200">
                  <UserIcon class="w-5 h-5 text-green-600" />
                  <span class="text-sm text-gray-600">
                    Resolvida por: <strong class="text-gray-900">{{ grievance.resolved_by.name }}</strong>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Button -->
        <div class="flex justify-center pt-8">
          <a href="/utente/dashboard"
            class="group relative px-8 py-4 bg-white text-gray-700 font-semibold rounded-xl hover:shadow-2xl border border-gray-200 transition-all duration-300 flex items-center gap-3 shadow-lg hover:shadow-gray-200/50 transform hover:-translate-y-1 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-gray-100 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>
            <ArrowPathIcon class="w-5 h-5 relative z-10" />
            <span class="relative z-10">Voltar ao Dashboard</span>
            <ChevronRightIcon class="w-5 h-5 relative z-10 opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0 transition-all duration-300" />
          </a>
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
  XMarkIcon,
  DocumentTextIcon,
  BuildingOfficeIcon,
  MapPinIcon,
  UserIcon,
  ArrowPathIcon,
  ChevronRightIcon
} from '@heroicons/vue/24/outline'
import StatusBadge from '@/Components/Grievance/StatusBadge.vue'
import UpdatesTimeline from '@/Components/Grievance/UpdatesTimeline.vue'
import AttachmentsGallery from '@/Components/Grievance/AttachmentsGallery.vue'

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

<style scoped>
.prose img {
  max-width: 100%;
  height: auto;
  border-radius: 0.75rem;
  margin: 1rem 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.prose p {
  margin-bottom: 1rem;
  line-height: 1.7;
  color: #374151;
}

.prose ul,
.prose ol {
  margin: 1.25rem 0;
  padding-left: 1.75rem;
}

.prose li {
  margin-bottom: 0.75rem;
  color: #4b5563;
}

@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
