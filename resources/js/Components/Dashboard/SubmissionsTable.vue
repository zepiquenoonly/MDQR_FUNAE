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
                <component
                  :is="row.impactType.icon"
                  class="w-3 h-3 mr-1"
                />
                {{ row.impactType.text }}
              </span>
            </td>
            <td class="py-4 px-4">
              <span :class="['badge', row.submissionType.class]">
                <component
                  :is="row.submissionType.icon"
                  class="w-3 h-3 mr-1"
                />
                {{ row.submissionType.text }}
              </span>
            </td>
            <td class="py-4 px-4">
              <span :class="['badge', row.status.class]">
                <component
                  :is="row.status.icon"
                  class="w-3 h-3 mr-1"
                />
                {{ row.status.text }}
              </span>
            </td>
            <td class="py-4 px-4">
              <button
                @click="viewDetails(row)"
                class="bg-brand hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2"
              >
                <EyeIcon class="w-4 h-4" />
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
import {
  EyeIcon,
  LightBulbIcon,
  ExclamationTriangleIcon,
  DocumentTextIcon,
  ClockIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  XCircleIcon,
  CogIcon
} from '@heroicons/vue/24/outline'

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

// Sample data with icons
const tableData = computed(() => {
  const data = {
    sugestoes: [
      {
        name: 'João Silva',
        neighborhood: 'Machava',
        impactType: {
          class: 'badge-ambiental',
          text: 'Ambiental',
          icon: LightBulbIcon
        },
        submissionType: {
          class: 'badge-sugestao',
          text: 'Sugestão',
          icon: LightBulbIcon
        },
        status: {
          class: 'badge-pendente',
          text: 'Pendente',
          icon: ClockIcon
        }
      },
      {
        name: 'Maria Santos',
        neighborhood: 'Khongolote',
        impactType: { class: 'badge-social', text: 'Social', icon: ExclamationTriangleIcon },
        submissionType: { class: 'badge-sugestao', text: 'Sugestão', icon: LightBulbIcon },
        status: { class: 'badge-em-analise', text: 'Em análise', icon: CogIcon }
      },
      {
        name: 'Carlos Fernandes',
        neighborhood: 'Matola',
        impactType: { class: 'badge-ambiental', text: 'Ambiental', icon: LightBulbIcon },
        submissionType: { class: 'badge-sugestao', text: 'Sugestão', icon: LightBulbIcon },
        status: { class: 'badge-considerada', text: 'Considerada', icon: CheckCircleIcon }
      },
      {
        name: 'Ana Oliveira',
        neighborhood: 'Zimpeto',
        impactType: { class: 'badge-social', text: 'Social', icon: ExclamationTriangleIcon },
        submissionType: { class: 'badge-sugestao', text: 'Sugestão', icon: LightBulbIcon },
        status: { class: 'badge-em-processo', text: 'Em processo', icon: ClockIcon }
      },
      {
        name: 'Pedro Gomes',
        neighborhood: 'Chamanculo',
        impactType: { class: 'badge-ambiental', text: 'Ambiental', icon: LightBulbIcon },
        submissionType: { class: 'badge-sugestao', text: 'Sugestão', icon: LightBulbIcon },
        status: { class: 'badge-resolvida', text: 'Resolvida', icon: CheckCircleIcon }
      },
      {
        name: 'Sofia Ribeiro',
        neighborhood: 'Matola',
        impactType: { class: 'badge-social', text: 'Social', icon: ExclamationCircleIcon },
        submissionType: { class: 'badge-sugestao', text: 'Sugestão', icon: LightBulbIcon },
        status: { class: 'badge-nao-considerada', text: 'Não considerada', icon: XCircleIcon }
      },
      {
        name: 'Miguel Costa',
        neighborhood: 'Polana',
        impactType: { class: 'badge-ambiental', text: 'Ambiental', icon: LightBulbIcon },
        submissionType: { class: 'badge-sugestao', text: 'Sugestão', icon: LightBulbIcon },
        status: { class: 'badge-pendente', text: 'Pendente', icon: ClockIcon }
      }
    ],
    queixas: [
      // dados de exemplo para queixas
    ],
    reclamacoes: [
      // dados de exemplo para reclamações
    ]
  }
  return data[props.type] || []
})


const viewDetails = (row) => {
  console.log(`Visualizando detalhes de: ${row.name}, Tipo: ${row.submissionType.text}`)
}
</script>

<style scoped>
.badge {
  @apply inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full;
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
