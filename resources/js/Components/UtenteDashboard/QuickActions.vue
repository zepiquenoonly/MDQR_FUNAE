<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <button
      @click="createNewComplaint"
      class="flex items-center justify-between p-4 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1"
    >
      <div class="flex items-center">
        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
        </div>
        <div class="text-left">
          <div class="font-semibold">Nova Reclamação</div>
          <div class="text-xs opacity-90">Submeter nova reclamação</div>
        </div>
      </div>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
    </button>

    <button
      @click="viewAllComplaints"
      class="flex items-center justify-between p-4 bg-white border-2 border-gray-200 text-gray-800 rounded-xl shadow-sm hover:shadow-md hover:border-orange-500 transition-all duration-300"
    >
      <div class="flex items-center">
        <div class="w-12 h-12 bg-orange-50 rounded-lg flex items-center justify-center mr-4">
          <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <div class="text-left">
          <div class="font-semibold">Minhas Reclamações</div>
          <div class="text-xs text-gray-600">Ver todas as submissões</div>
        </div>
      </div>
      <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
    </button>

    <button
      @click="trackComplaint"
      class="flex items-center justify-between p-4 bg-white border-2 border-gray-200 text-gray-800 rounded-xl shadow-sm hover:shadow-md hover:border-blue-500 transition-all duration-300"
    >
      <div class="flex items-center">
        <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center mr-4">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        <div class="text-left">
          <div class="font-semibold">Acompanhar Status</div>
          <div class="text-xs text-gray-600">Verificar progresso</div>
        </div>
      </div>
      <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
    </button>
  </div>
</template>

<script setup>
import { useDashboardState } from './Composables/useDashboardState.js'

const { setActivePanel, setActiveDropdown } = useDashboardState()

const createNewComplaint = () => {
  setActivePanel('mdqr')
  setActiveDropdown('reclamacoes')
  // O componente Complaints já tem o botão de nova reclamação
}

const viewAllComplaints = () => {
  setActivePanel('mdqr')
  setActiveDropdown('reclamacoes')
}

const trackComplaint = () => {
  window.open('/track', '_blank')
}
</script>
