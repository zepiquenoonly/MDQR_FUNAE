<template>
  <div>
    <h3 class="text-lg font-semibold text-gray-800 mb-6">
      Listagem das últimas {{ tableTitle }}
    </h3>

    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b-2 border-gray-200">
            <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
              Nome do munícipe
            </th>
            <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
              Bairro
            </th>
            <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
              Tipo de impacto
            </th>
            <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
              Tipo de submissão
            </th>
            <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
              Estado
            </th>
            <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
              Ações
            </th>
          </tr>
        </thead>
        <tbody>
          <tr 
            v-for="(row, index) in tableData" 
            :key="index"
            class="border-b border-gray-100 hover:bg-gray-50 transition-colors"
          >
            <td class="py-4 px-4 text-sm text-gray-800">{{ row.name }}</td>
            <td class="py-4 px-4 text-sm text-gray-600">{{ row.neighborhood }}</td>
            <td class="py-4 px-4">
              <span :class="['badge', row.impactType.class]">
                {{ row.impactType.text }}
              </span>
            </td>
            <td class="py-4 px-4">
              <span :class="['badge', row.submissionType.class]">
                {{ row.submissionType.text }}
              </span>
            </td>
            <td class="py-4 px-4">
              <span :class="['badge', row.status.class]">
                {{ row.status.text }}
              </span>
            </td>
            <td class="py-4 px-4">
              <button 
                @click="viewDetails(row)"
                class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
              >
                Ver mais
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: String
})

const tableTitle = computed(() => {
  const titles = {
    sugestoes: 'sugestões',
    queixas: 'queixas',
    reclamacoes: 'reclamações'
  }
  return titles[props.type] || 'submissões'
})

// Sample data - in real app, this would come from API
const tableData = computed(() => {
  const data = {
    sugestoes: [
      {
        name: 'João Silva',
        neighborhood: 'Machava',
        impactType: { class: 'badge-ambiental', text: 'Ambiental' },
        submissionType: { class: 'badge-sugestao', text: 'Sugestão' },
        status: { class: 'badge-pendente', text: 'Pendente' }
      }
      // ... more data
    ],
    queixas: [
      // ... queixas data
    ],
    reclamacoes: [
      // ... reclamações data
    ]
  }
  return data[props.type] || []
})

const viewDetails = (row) => {
  alert(`Visualizando detalhes de: ${row.name}\nTipo: ${row.submissionType.text}`)
}
</script>

<style scoped>
.badge {
  @apply inline-block px-3 py-1 text-xs font-semibold rounded-full;
}

.badge-ambiental {
  @apply bg-blue-100 text-blue-800;
}

.badge-social {
  @apply bg-indigo-100 text-indigo-800;
}

.badge-sugestao {
  @apply bg-green-100 text-green-800;
}

.badge-queixa {
  @apply bg-orange-100 text-orange-800;
}

.badge-reclamacao {
  @apply bg-red-100 text-red-800;
}

.badge-pendente {
  @apply bg-orange-100 text-orange-800;
}

.badge-considerada {
  @apply bg-green-100 text-green-800;
}

.badge-em-analise {
  @apply bg-blue-100 text-blue-800;
}

.badge-nao-considerada {
  @apply bg-red-100 text-red-800;
}

.badge-resolvida {
  @apply bg-green-100 text-green-800;
}

.badge-em-processo {
  @apply bg-orange-100 text-orange-800;
}
</style>