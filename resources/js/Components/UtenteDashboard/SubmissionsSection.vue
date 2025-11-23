<template>
  <div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <!-- Section Header -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-orange-50 to-white">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">Minhas Submissões Recentes</h2>
        <span v-if="grievances.data" class="text-sm text-gray-600 bg-white px-3 py-1 rounded-full">
          {{ grievances.total }} total
        </span>
      </div>
    </div>

    <!-- Tabs por Tipo -->
    <div class="border-b border-gray-200">
      <div class="flex flex-wrap">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          :class="[
            'flex-1 min-w-[120px] px-6 py-4 text-sm font-semibold transition-colors border-b-2',
            activeTab === tab.id
              ? 'border-orange-500 bg-orange-50 text-orange-700'
              : 'border-transparent bg-white text-gray-600 hover:bg-gray-50'
          ]"
          @click="activeTab = tab.id"
        >
          <span class="mr-2">{{ tab.icon }}</span>
          {{ tab.name }}
          <span v-if="tab.count > 0" class="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-200">
            {{ tab.count }}
          </span>
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="p-12 text-center">
      <div class="inline-block w-8 h-8 border-4 border-orange-500 border-t-transparent rounded-full animate-spin mb-4"></div>
      <p class="text-gray-600">Carregando suas submissões...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="!filteredGrievances || filteredGrievances.length === 0" class="p-12 text-center">
      <div class="text-5xl mb-4">{{ getEmptyIcon }}</div>
      <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ getEmptyTitle }}</h3>
      <p class="text-gray-600 mb-4">{{ getEmptyMessage }}</p>
      <button 
        @click="goToSubmissions(activeTab)"
        class="inline-flex items-center px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Criar {{ getEmptyActionLabel }}
      </button>
    </div>

    <!-- Table Content -->
    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Referência
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Tipo
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Categoria
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Data
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Ações
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="grievance in filteredGrievances" :key="grievance.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ grievance.reference_number }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getTypeBadgeClass(grievance.type)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                {{ getTypeLabel(grievance.type) }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm text-gray-900">{{ grievance.category }}</div>
              <div v-if="grievance.subcategory" class="text-xs text-gray-500">{{ grievance.subcategory }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getStatusBadgeClass(grievance.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                {{ grievance.status_label }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ grievance.submitted_at || grievance.created_at }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <button 
                @click="viewDetails(grievance)"
                class="text-orange-600 hover:text-orange-900 font-medium"
              >
                Ver detalhes
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- View All Button -->
      <div v-if="grievances.total > 5" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
        <button 
          @click="goToSubmissions(activeTab)"
          class="w-full text-center py-2 text-orange-600 hover:text-orange-700 font-medium transition-colors"
        >
          Ver todas as {{ getTypeLabel(activeTab) }} →
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useDashboardState } from './Composables/useDashboardState.js'

const page = usePage()
const { setActivePanel, setActiveDropdown } = useDashboardState()
const loading = ref(false)
const selectedGrievance = ref(null)
const activeTab = ref('all')

const grievances = computed(() => {
  return page.props.grievances || { data: [], total: 0 }
})

const statsByType = computed(() => {
  return page.props.statsByType || {
    complaints: 0,
    grievances: 0,
    suggestions: 0
  }
})

const tabs = computed(() => [
  { id: 'all', name: 'Todas', icon: '📊', count: grievances.value.total },
  { id: 'complaint', name: 'Reclamações', icon: '📋', count: statsByType.value.complaints },
  { id: 'grievance', name: 'Queixas', icon: '⚠️', count: statsByType.value.grievances },
  { id: 'suggestion', name: 'Sugestões', icon: '💡', count: statsByType.value.suggestions }
])

const filteredGrievances = computed(() => {
  if (!grievances.value.data) return []
  
  if (activeTab.value === 'all') {
    return grievances.value.data.slice(0, 5)
  }
  
  return grievances.value.data
    .filter(g => g.type === activeTab.value)
    .slice(0, 5)
})

const getEmptyIcon = computed(() => {
  const icons = {
    'all': '📊',
    'complaint': '📋',
    'grievance': '⚠️',
    'suggestion': '💡'
  }
  return icons[activeTab.value] || '📊'
})

const getEmptyTitle = computed(() => {
  const titles = {
    'all': 'Nenhuma submissão encontrada',
    'complaint': 'Nenhuma reclamação encontrada',
    'grievance': 'Nenhuma queixa encontrada',
    'suggestion': 'Nenhuma sugestão encontrada'
  }
  return titles[activeTab.value] || 'Nenhuma submissão encontrada'
})

const getEmptyMessage = computed(() => {
  const messages = {
    'all': 'Você ainda não submeteu nenhuma reclamação, queixa ou sugestão.',
    'complaint': 'Você ainda não submeteu nenhuma reclamação.',
    'grievance': 'Você ainda não submeteu nenhuma queixa.',
    'suggestion': 'Você ainda não submeteu nenhuma sugestão.'
  }
  return messages[activeTab.value] || 'Você ainda não submeteu nada.'
})

const getEmptyActionLabel = computed(() => {
  const labels = {
    'all': 'Nova Submissão',
    'complaint': 'Nova Reclamação',
    'grievance': 'Nova Queixa',
    'suggestion': 'Nova Sugestão'
  }
  return labels[activeTab.value] || 'Nova Submissão'
})

const getTypeLabel = (type) => {
  const labels = {
    'all': 'submissões',
    'complaint': 'Reclamação',
    'grievance': 'Queixa',
    'suggestion': 'Sugestão'
  }
  return labels[type] || type
}

const getTypeBadgeClass = (type) => {
  const classes = {
    'complaint': 'bg-red-100 text-red-800',
    'grievance': 'bg-orange-100 text-orange-800',
    'suggestion': 'bg-blue-100 text-blue-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

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

const viewDetails = (grievance) => {
  selectedGrievance.value = grievance
  goToSubmissions(grievance.type)
}

const goToSubmissions = (type) => {
  setActivePanel('mdqr')
  
  const dropdowns = {
    'complaint': 'reclamacoes',
    'grievance': 'queixas',
    'suggestion': 'sugestoes',
    'all': 'reclamacoes'
  }
  
  setActiveDropdown(dropdowns[type] || 'reclamacoes')
}
</script>