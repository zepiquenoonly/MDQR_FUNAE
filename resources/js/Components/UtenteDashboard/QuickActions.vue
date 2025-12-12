<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4 mb-6">
    <!-- Nova Reclamação - Primary Action -->
    <button
      @click="createNewComplaint"
      class="flex items-center justify-between p-4 bg-gradient-to-r from-primary-500 to-orange-600 hover:from-primary-600 hover:to-orange-700 text-white rounded-xl shadow-lg border border-orange-400/30 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl relative overflow-hidden group"
    >
      <!-- Shine effect -->
      <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
      <div class="flex items-center gap-3 min-w-0 flex-1 relative z-10">
        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
          <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
        </div>
        <div class="text-left min-w-0">
          <div class="font-semibold text-sm sm:text-base truncate drop-shadow-sm">Nova Reclamação</div>
          <div class="text-xs opacity-90 truncate">Submeter nova reclamação</div>
        </div>
      </div>
      <svg class="w-5 h-5 flex-shrink-0 ml-2 group-hover:translate-x-1 transition-transform duration-300 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
    </button>



    <!-- Acompanhar Status -->
    <button
      @click="trackComplaint"
      class="flex items-center justify-between p-4 glass-card hover:scale-105 group transition-all duration-300 sm:col-span-2 md:col-span-1 hover:shadow-xl border border-white/40 relative overflow-hidden"
    >
      <!-- Gradient overlay on hover -->
      <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

      <div class="flex items-center gap-3 min-w-0 flex-1 relative z-10">
        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0 border border-blue-200 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 shadow-sm">
          <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        <div class="text-left min-w-0">
          <div class="font-semibold text-sm sm:text-base text-gray-800 group-hover:text-blue-600 transition-colors truncate">Acompanhar Status</div>
          <div class="text-xs text-gray-600 truncate">Verificar progresso</div>
        </div>
      </div>
      <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition-all flex-shrink-0 ml-2 group-hover:translate-x-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
