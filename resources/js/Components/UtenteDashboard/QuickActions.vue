<template>
  <div class="grid grid-cols-1 gap-2 mb-6 sm:grid-cols-2 md:grid-cols-2 sm:gap-2">
    <!-- Nova Reclamação - Primary Action -->
    <button
      @click="createNewComplaint"
      class="relative flex items-center justify-between p-4 overflow-hidden text-white transition-all duration-300 transform border shadow-lg bg-gradient-to-r from-primary-500 to-orange-600 hover:from-primary-600 hover:to-orange-700 rounded-xl border-orange-400/30 hover:scale-105 hover:shadow-2xl group"
    >
      <!-- Shine effect -->
      <div class="absolute inset-0 transition-transform duration-700 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent group-hover:translate-x-full"></div>
      <div class="relative z-10 flex items-center flex-1 min-w-0 gap-3">
        <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 transition-transform duration-300 sm:w-12 sm:h-12 bg-white/20 backdrop-blur-sm rounded-xl group-hover:scale-110">
          <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
        </div>
        <div class="min-w-0 text-left">
          <div class="text-sm font-semibold truncate sm:text-base drop-shadow-sm">Nova Reclamação</div>
          <div class="text-xs truncate opacity-90">Submeter nova reclamação</div>
        </div>
      </div>
      <svg class="relative z-10 flex-shrink-0 w-5 h-5 ml-2 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
    </button>



    <!-- Acompanhar Status -->
    <button
      @click="trackComplaint"
      class="relative flex items-center justify-between p-4 overflow-hidden transition-all duration-300 border glass-card hover:scale-105 group sm:col-span-2 md:col-span-1 hover:shadow-xl border-white/40"
    >
      <!-- Gradient overlay on hover -->
      <div class="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-br from-blue-500/5 to-indigo-500/5 group-hover:opacity-100"></div>

      <div class="relative z-10 flex items-center flex-1 min-w-0 gap-3">
        <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 transition-all duration-300 border border-blue-200 shadow-sm sm:w-12 sm:h-12 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl group-hover:scale-110 group-hover:rotate-3">
          <svg class="w-5 h-5 text-blue-600 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        <div class="min-w-0 text-left">
          <div class="text-sm font-semibold text-gray-800 truncate transition-colors sm:text-base group-hover:text-blue-600">Acompanhar Status</div>
          <div class="text-xs text-gray-600 truncate">Verificar progresso</div>
        </div>
      </div>
      <svg class="relative z-10 flex-shrink-0 w-5 h-5 ml-2 text-gray-400 transition-all group-hover:text-blue-600 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
    </button>
  </div>
</template>

<script setup>
import { useDashboardState } from './Composables/useDashboardState.js'

const emit = defineEmits(['create-complaint'])
const { setActivePanel, setActiveDropdown } = useDashboardState()

const createNewComplaint = () => {
  emit('create-complaint')
}

const viewAllComplaints = () => {
  setActivePanel('mdqr')
  setActiveDropdown('reclamacoes')
}

const trackComplaint = () => {
  window.open('/track', '_blank')
}
</script>
