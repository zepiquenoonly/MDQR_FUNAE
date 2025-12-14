<template>
  <div class="glass-card p-6 hover:shadow-2xl transition-all duration-300 border border-white/40">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-bold text-gray-900">{{ title }}</h3>
      <!-- <button class="px-4 py-2 text-xs font-semibold text-primary-600 hover:text-white hover:bg-gradient-to-r from-primary-500 to-orange-600 rounded-lg transition-all duration-300 border border-primary-200 hover:border-transparent hover:scale-105">
        Ver Todos
      </button> -->
    </div>

    <div class="overflow-x-auto">
      <div :class="[
        'overflow-y-auto',
        rows.length > 10 ? 'max-h-96' : ''
      ]">
        <table class="w-full">
        <thead>
          <tr class="border-b-2 border-gray-200">
            <th v-for="(header, index) in headers" :key="index"
                class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
              {{ header }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="(row, index) in rows" :key="index"
              class="hover:bg-primary-50/50 transition-colors duration-200 group">
            <td class="px-4 py-4">
              <div class="flex items-center gap-3">
                <div :class="['w-2 h-2 rounded-full', row.statusColor || 'bg-primary-500']"></div>
                <span class="text-sm font-medium text-gray-900 group-hover:text-primary-700 transition-colors">
                  {{ row.id }}
                </span>
              </div>
            </td>
            <td class="px-4 py-4 text-sm text-gray-700">{{ row.type }}</td>
            <td class="px-4 py-4">
              <span :class="[
                'px-3 py-1 text-xs font-semibold rounded-full',
                row.statusClass || 'bg-yellow-100 text-yellow-700'
              ]">
                {{ row.status }}
              </span>
            </td>
            <td class="px-4 py-4 text-sm text-gray-500">{{ row.date }}</td>
            <td class="px-4 py-4">
              <button @click="$emit('view-details', { ...row, id: row.grievance_id || row.id })"
                class="text-primary-600 hover:text-orange-600 transition-colors font-medium text-sm group-hover:scale-110 duration-200 cursor-pointer">
                Ver Detalhes →
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>

    <!-- Empty state -->
    <div v-if="rows.length === 0" class="py-12 text-center">
      <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
      </div>
      <p class="text-gray-500 text-sm">Nenhum registro encontrado</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: 'Submissões Recentes'
  },
  headers: {
    type: Array,
    default: () => ['ID', 'Tipo', 'Status', 'Data', 'Acções']
  },
  rows: {
    type: Array,
    default: () => [
      {
        id: '#REC-2024-001',
        type: 'Reclamação',
        status: 'Em Análise',
        statusClass: 'bg-yellow-100 text-yellow-700',
        statusColor: 'bg-yellow-500',
        date: '15/12/2024'
      },
      {
        id: '#QUE-2024-012',
        type: 'Queixa',
        status: 'Resolvido',
        statusClass: 'bg-green-100 text-green-700',
        statusColor: 'bg-green-500',
        date: '14/12/2024'
      },
      {
        id: '#SUG-2024-008',
        type: 'Sugestão',
        status: 'Pendente',
        statusClass: 'bg-gray-100 text-gray-700',
        statusColor: 'bg-gray-500',
        date: '13/12/2024'
      },
      {
        id: '#REC-2024-002',
        type: 'Reclamação',
        status: 'Rejeitado',
        statusClass: 'bg-red-100 text-red-700',
        statusColor: 'bg-red-500',
        date: '12/12/2024'
      }
    ]
  }
})
</script>
