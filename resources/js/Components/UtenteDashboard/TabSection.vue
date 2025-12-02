<template>
  <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-200">
    <!-- Header limpo e profissional -->
    <div class="bg-white border-b border-gray-200 px-6 py-5">
      <h2 class="text-2xl font-semibold text-gray-900">Gestão de Projectos</h2>
      <p class="text-sm text-gray-600 mt-1">Acompanhe o estado dos seus projectos</p>
    </div>

    <!-- Tabs profissionais -->
    <div class="bg-gray-50 border-b border-gray-200">
      <div class="flex">
        <button v-for="tab in tabs" :key="tab.id" :class="[
          'flex-1 px-6 py-4 text-sm font-medium transition-all duration-200 border-b-2 flex items-center justify-center gap-2',
          activeTab === tab.id
            ? getActiveTabClass(tab.id)
            : 'text-gray-600 border-transparent hover:text-gray-900 hover:border-gray-300'
        ]" @click="activeTab = tab.id">
          <span v-html="getTabIcon(tab.id)" class="w-4 h-4"></span>
          <span>{{ tab.name }}</span>
        </button>
      </div>
    </div>

    <!-- Project Cards Content -->
    <div class="p-6 bg-gray-50">
      <ProjectCards :type="activeTab" :show-all-projects="showAllProjects"
        @view-project-details="$emit('view-project-details', $event)" />

      <!-- Botão "Todos projectos" profissional -->
      <div class="mt-8 flex justify-center">
        <button @click="showAllProjects = !showAllProjects" :class="[
          'px-8 py-3 rounded-lg font-medium transition-all duration-200 flex items-center gap-2 border',
          showAllProjects
            ? 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
            : 'bg-primary-600 text-white border-primary-600 hover:bg-primary-700'
        ]">
          <svg v-if="!showAllProjects" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
          </svg>
          <span>{{ showAllProjects ? 'Mostrar menos' : 'Ver todos os projectos' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import ProjectCards from './ProjectCards.vue'

defineEmits(['view-project-details'])

const activeTab = ref('andamento')
const showAllProjects = ref(false)

const tabs = [
  { id: 'andamento', name: 'EM ANDAMENTO' },
  { id: 'parados', name: 'PARADOS' },
  { id: 'finalizados', name: 'FINALIZADOS' }
]

const getActiveTabClass = (tabId) => {
  switch (tabId) {
    case 'andamento':
      return 'text-primary-600 border-primary-600 bg-white'
    case 'parados':
      return 'text-red-600 border-red-600 bg-white'
    case 'finalizados':
      return 'text-green-600 border-green-600 bg-white'
    default:
      return 'text-primary-600 border-primary-600 bg-white'
  }
}

const getTabIcon = (tabId) => {
  switch (tabId) {
    case 'andamento':
      return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'
    case 'parados':
      return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
    case 'finalizados':
      return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
    default:
      return ''
  }
}
</script>