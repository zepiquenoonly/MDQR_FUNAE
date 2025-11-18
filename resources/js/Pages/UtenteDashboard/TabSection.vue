<template>
  <div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <!-- Tabs -->
    <div class="border-b border-gray-200">
      <div class="flex flex-col sm:flex-row">
        <button v-for="tab in tabs" :key="tab.id" :class="[
          'flex-1 px-6 py-4 text-sm font-semibold transition-colors',
          activeTab === tab.id
            ? getActiveTabClass(tab.id)
            : 'bg-white text-gray-600 hover:bg-gray-50'
        ]" @click="activeTab = tab.id">
          {{ tab.name }}
        </button>
      </div>
    </div>

    <!-- Table Content -->
    <div class="p-6">
      <SubmissionsTable :type="activeTab" />

      <!-- Botão "Todos projectos" -->
      <div class="mt-8 flex justify-center">
        <button @click="showAllProjects = !showAllProjects" :class="[
          'px-8 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2',
          showAllProjects
            ? 'bg-brand-blue text-white hover:bg-blue-600'
            : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
        ]">
          <span v-if="!showAllProjects">↓</span>
          <span v-else>↑</span>
          {{ showAllProjects ? 'Mostrar menos' : 'Todos projectos' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import SubmissionsTable from './ProjectCards.vue'

const activeTab = ref('andamento')
const showAllProjects = ref(false)

const tabs = [
  { id: 'andamento', name: 'PROJECTOS EM ANDAMENTO' },
  { id: 'parados', name: 'PROJECTOS PARADOS' },
  { id: 'finalizados', name: 'PROJECTOS FINALIZADOS' }
]

const getActiveTabClass = (tabId) => {
  switch (tabId) {
    case 'andamento':
      return 'bg-brand text-white'
    case 'parados':
      return 'bg-brand-red text-white'
    case 'finalizados':
      return 'bg-brand-green text-white'
    default:
      return 'bg-brand text-white'
  }
}

// Passe a prop showAllProjects para o componente de cards
defineExpose({
  showAllProjects
})
</script>