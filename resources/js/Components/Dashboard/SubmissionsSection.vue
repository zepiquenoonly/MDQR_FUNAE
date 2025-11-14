<template>
  <div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <!-- Section Header -->
    <div class="px-6 py-4 border-b border-gray-200">
      <h2 class="text-xl font-semibold text-gray-800">Últimas submissões</h2>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
      <div class="flex flex-col sm:flex-row">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          :class="[
            'flex-1 px-6 py-4 text-sm font-semibold transition-colors',
            activeTab === tab.id
              ? 'bg-teal-600 text-white'
              : 'bg-white text-gray-600 hover:bg-gray-50'
          ]"
          @click="activeTab = tab.id"
        >
          {{ tab.name }}
        </button>
      </div>
    </div>

    <!-- Table Content -->
    <div class="p-6">
      <SubmissionsTable :type="activeTab" />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import SubmissionsTable from './SubmissionsTable.vue'

const activeTab = ref('sugestoes')

const tabs = [
  { id: 'sugestoes', name: 'Sugestões' },
  { id: 'queixas', name: 'Queixas' },
  { id: 'reclamacoes', name: 'Reclamações' }
]
</script>