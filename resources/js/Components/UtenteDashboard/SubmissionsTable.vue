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
              Código da Submissão
            </th>
            <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
              Bairro
            </th>
            <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
              Tipo de impacto
            </th>
            <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
              Data
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
          <tr v-for="(row, index) in tableData" :key="index"
            class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
            <td class="py-4 px-4 text-sm text-gray-800">{{ row.code }}</td>
            <td class="py-4 px-4 text-sm text-gray-600">{{ row.neighborhood }}</td>
            <td class="py-4 px-4">
              <span :class="['badge', row.impactType.class]">
                <component :is="row.impactType.icon" class="w-3 h-3 mr-1" />
                {{ row.impactType.text }}
              </span>
            </td>
            <td class="py-4 px-4">
              <span class="text-sm text-gray-500">
                {{ formatDate(row.date) }}
              </span>
            </td>
            <td class="py-4 px-4">
              <span :class="['badge', row.status.class]">
                <component :is="row.status.icon" class="w-3 h-3 mr-1" />
                {{ row.status.text }}
              </span>
            </td>
            <td class="py-4 px-4">
              <button @click="viewDetails(row)"
                class="bg-brand hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
                <EyeIcon class="w-4 h-4" />
                Ver mais
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Estado vazio -->
    <div v-if="tableData.length === 0" class="text-center py-12">
      <DocumentTextIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
      <p class="text-gray-500 text-lg">Nenhuma {{ tableTitle }} encontrada</p>
      <p class="text-gray-400 text-sm mt-2">
        Não existem {{ tableTitle }} registadas no momento
      </p>
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
  CogIcon,
  DocumentTextIcon as DocumentIcon
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

// Dados completos para todas as categorias
const tableData = computed(() => {
  const data = {
    sugestoes: [
      {
        code: 'SUG-001',
        neighborhood: 'Machava',
        impactType: {
          class: 'badge-ambiental',
          text: 'Impacto Ambiental',
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
        },
        date: '2024-01-10'
      },
      {
        code: 'SUG-002',
        neighborhood: 'Khongolote',
        impactType: {
          class: 'badge-social',
          text: 'Impacto Social',
          icon: ExclamationTriangleIcon
        },
        submissionType: {
          class: 'badge-sugestao',
          text: 'Sugestão',
          icon: LightBulbIcon
        },
        status: {
          class: 'badge-em-analise',
          text: 'Em análise',
          icon: CogIcon
        },
        date: '2024-01-11'
      },
      {
        code: 'SUG-003',
        neighborhood: 'Matola',
        impactType: {
          class: 'badge-projeto',
          text: 'Impacto do Projecto',
          icon: DocumentTextIcon
        },
        submissionType: {
          class: 'badge-sugestao',
          text: 'Sugestão',
          icon: LightBulbIcon
        },
        status: {
          class: 'badge-considerada',
          text: 'Considerada',
          icon: CheckCircleIcon
        },
        date: '2024-01-12'
      }
    ],
    queixas: [
      {
        code: 'QUE-001',
        neighborhood: 'Zimpeto',
        impactType: {
          class: 'badge-ambiental',
          text: 'Impacto Ambiental',
          icon: ExclamationTriangleIcon
        },
        submissionType: {
          class: 'badge-queixa',
          text: 'Queixa',
          icon: ExclamationTriangleIcon
        },
        status: {
          class: 'badge-em-processo',
          text: 'Em processo',
          icon: CogIcon
        },
        date: '2024-01-08'
      },
      {
        code: 'QUE-002',
        neighborhood: 'Magoanine',
        impactType: {
          class: 'badge-social',
          text: 'Impacto Social',
          icon: ExclamationTriangleIcon
        },
        submissionType: {
          class: 'badge-queixa',
          text: 'Queixa',
          icon: ExclamationTriangleIcon
        },
        status: {
          class: 'badge-resolvida',
          text: 'Resolvida',
          icon: CheckCircleIcon
        },
        date: '2024-01-05'
      },
      {
        code: 'QUE-003',
        neighborhood: 'Laulane',
        impactType: {
          class: 'badge-projeto',
          text: 'Impacto do Projecto',
          icon: ExclamationTriangleIcon
        },
        submissionType: {
          class: 'badge-queixa',
          text: 'Queixa',
          icon: ExclamationTriangleIcon
        },
        status: {
          class: 'badge-pendente',
          text: 'Pendente',
          icon: ClockIcon
        },
        date: '2024-01-03'
      }
    ],
    reclamacoes: [
      {
        code: 'REC-001',
        neighborhood: 'Urbanização',
        impactType: {
          class: 'badge-ambiental',
          text: 'Impacto Ambiental',
          icon: DocumentIcon
        },
        submissionType: {
          class: 'badge-reclamacao',
          text: 'Reclamação',
          icon: DocumentIcon
        },
        status: {
          class: 'badge-em-analise',
          text: 'Em análise',
          icon: CogIcon
        },
        date: '2024-01-15'
      },
      {
        code: 'REC-002',
        neighborhood: 'Machava Sede',
        impactType: {
          class: 'badge-social',
          text: 'Impacto Social',
          icon: DocumentIcon
        },
        submissionType: {
          class: 'badge-reclamacao',
          text: 'Reclamação',
          icon: DocumentIcon
        },
        status: {
          class: 'badge-resolvida',
          text: 'Resolvida',
          icon: CheckCircleIcon
        },
        date: '2024-01-12'
      },
      {
        code: 'REC-003',
        neighborhood: 'Khongolote',
        impactType: {
          class: 'badge-projeto',
          text: 'Impacto do Projecto',
          icon: DocumentIcon
        },
        submissionType: {
          class: 'badge-reclamacao',
          text: 'Reclamação',
          icon: DocumentIcon
        },
        status: {
          class: 'badge-nao-considerada',
          text: 'Não considerada',
          icon: XCircleIcon
        },
        date: '2024-01-09'
      }
    ]
  }

  return data[props.type] || []
})

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('pt-PT', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const viewDetails = (row) => {
  console.log(`Visualizando detalhes de: ${row.code}, Tipo: ${row.submissionType.text}, Bairro: ${row.neighborhood}`)
}
</script>

<style scoped>
.badge-projeto {
  @apply bg-purple-100 text-purple-800;
}

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
