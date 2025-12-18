<template>
  <div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="(project, index) in displayedProjects" :key="project.id" :class="[
        'bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden min-h-[230px] flex flex-col',
        getBorderClass()
      ]">

        <!-- CABEÇALHO COM IMAGEM E NOME DO PROJECTO -->
        <div class="flex items-center p-4 flex-shrink-0">
          <!-- IMAGEM DO PROJECTO (menor, à esquerda) -->
          <div class="w-20 h-20 flex items-center justify-center">
            <img :src="project.image" alt="project image" class="w-20 h-20 object-contain" />
          </div>

          <!-- NOME DO PROJECTO (à direita da imagem) -->
          <div class="ml-4 flex-1">
            <h3 class="font-bold text-gray-800 text-lg leading-tight">
              {{ project.name }}
            </h3>
          </div>
        </div>

        <!-- CONTEÚDO PRINCIPAL DA CARD -->
        <div class="flex-1 flex flex-col">
          <!-- BOTÃO MOSTRAR DETALHES (versão recolhida) -->
          <div v-if="!expandedStates[project.id]" class="px-4 py-3 flex items-center justify-center flex-1">
            <button @click="toggleDetails(project.id)" :class="[
              'text-white w-full py-3 rounded-lg flex items-center justify-center gap-2 font-semibold transition-colors',
              getButtonClass()
            ]">
              <span class="text-lg">⌄</span> Mais detalhes
            </button>
          </div>

          <!-- DETALHES EXPANDIDOS -->
          <transition name="slide-fade">
            <div v-if="expandedStates[project.id]" class="px-4 py-4 space-y-3 flex-1">
              <div class="space-y-2">
                <p class="text-sm"><strong>Localização:</strong> {{ project.detailedLocation }}</p>
                <p class="text-sm"><strong>Responsável do Projeto:</strong> {{ project.responsible }}</p>
                <p class="text-sm"><strong>Data de Início:</strong> {{ project.detailedStartDate }}</p>
                <p class="text-sm"><strong>Data de Termino:</strong> {{ project.detailedEndDate }}</p>
                <p class="text-sm"><strong>Orçamento:</strong> {{ project.detailedBudget }}</p>
              </div>

              <div class="mt-auto space-y-2">
                <!-- BOTÃO "VER DETALHES" (AZUL) - ATUALIZADO -->
                <button @click="viewProjectDetails(project.id)" :class="[
                  'text-white w-full mt-4 mb-2 py-2 rounded-lg font-semibold transition-colors',
                  getDetailsButtonClass()
                ]">
                  Ver detalhes
                </button>

                <!-- BOTÃO OCULTAR -->
                <button @click="toggleDetails(project.id)"
                  class="bg-gray-500 hover:bg-gray-600 text-white w-full mt-4 mb-4 py-2 rounded-lg flex items-center justify-center gap-2 font-semibold transition-colors">
                  <span class="text-lg">⌃</span> Ocultar detalhes
                </button>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import { router } from '@inertiajs/vue3'

// Props para receber o tipo da tab
const props = defineProps({
  type: {
    type: String,
    default: '1'
  },
  showAllProjects: {
    type: Boolean,
    default: false
  }
})

// Estado reativo separado para controlar a expansão de cada card
const expandedStates = reactive({})

// Dados dos projetos como ref reativa
const projectData = ref([
  {
    id: 1,
    image: '/images/Emblem_of_Mozambique.svg-2.png',
    name: 'PARQUE EÓLICO DE PEMBA',
    detailedLocation: 'Bairro Zimpeto',
    responsible: 'FUNAE, FP',
    detailedStartDate: '25 de Julho de 2023',
    detailedEndDate: '23 de Janeiro de 2026',
    detailedBudget: 'USD 15,6 Milhões',
    category: 'andamento',
  },
  {
    id: 2,
    image: '/images/Emblem_of_Mozambique.svg-2.png',
    name: 'SISTEMA DE ÁGUA POTÁVEL',
    detailedLocation: 'Bairro Machava',
    responsible: 'AIAS, EP',
    detailedStartDate: '15 de Março de 2022',
    detailedEndDate: '30 de Agosto de 2023',
    detailedBudget: 'MT 18 Milhões',
    category: 'finalizados',
  },
  {
    id: 3,
    image: '/images/Emblem_of_Mozambique.svg-2.png',
    name: 'CENTRO DE SAÚDE COMUNITÁRIO',
    detailedLocation: 'Bairro Khongolote',
    responsible: 'MISAU',
    detailedStartDate: '1 de Junho de 2024',
    detailedEndDate: '15 de Dezembro de 2024',
    detailedBudget: 'MT 12 Milhões',
    category: 'andamento',
  },
  {
    id: 4,
    image: '/images/Emblem_of_Mozambique.svg-2.png',
    name: 'PARQUE EÓLICO DE INHAMBANE',
    detailedLocation: 'Bairro Central',
    responsible: 'FUNAE, FP',
    detailedStartDate: '10 de Agosto de 2023',
    detailedEndDate: '30 de Março de 2026',
    detailedBudget: 'USD 12,8 Milhões',
    category: 'andamento',
  },
  {
    id: 5,
    image: '/images/Emblem_of_Mozambique.svg-2.png',
    name: 'SISTEMA DE DRENAGEM',
    detailedLocation: 'Bairro Urbanização',
    responsible: 'CMCM',
    detailedStartDate: '20 de Abril de 2022',
    detailedEndDate: '15 de Novembro de 2023',
    detailedBudget: 'MT 25 Milhões',
    category: 'finalizados',
  },
  {
    id: 6,
    image: '/images/Emblem_of_Mozambique.svg-2.png',
    name: 'ESCOLA PRIMÁRIA',
    detailedLocation: 'Bairro Magoanine',
    responsible: 'MINEDH',
    detailedStartDate: '5 de Setembro de 2024',
    detailedEndDate: '20 de Junho de 2025',
    detailedBudget: 'MT 15 Milhões',
    category: 'parados',
  },
  {
    id: 7,
    image: '/images/Emblem_of_Mozambique.svg-2.png',
    name: 'USINA SOLAR DE MATOLA',
    detailedLocation: 'Bairro Industrial',
    responsible: 'EDM, EP',
    detailedStartDate: '15 de Janeiro de 2023',
    detailedEndDate: '30 de Dezembro de 2024',
    detailedBudget: 'USD 8,5 Milhões',
    category: 'andamento',
  },
  {
    id: 8,
    image: '/images/Emblem_of_Mozambique.svg-2.png',
    name: 'PROJECTO DE HABITAÇÃO',
    detailedLocation: 'Bairro Laulane',
    responsible: 'IFH',
    detailedStartDate: '1 de Março de 2022',
    detailedEndDate: '31 de Outubro de 2023',
    detailedBudget: 'MT 32 Milhões',
    category: 'finalizados',
  },
  {
    id: 9,
    image: '/images/Emblem_of_Mozambique.svg-2.png',
    name: 'PONTE SOBRE RIO INCOMATI',
    detailedLocation: 'Distrito de Manhiça',
    responsible: 'ANE',
    detailedStartDate: '10 de Julho de 2024',
    detailedEndDate: '15 de Agosto de 2025',
    detailedBudget: 'MT 45 Milhões',
    category: 'parados',
  }
])

// Computed property para filtrar os projetos baseado na tab selecionada
const displayedProjects = computed(() => {
  if (props.showAllProjects) {
    // Mostrar todos os projetos
    return projectData.value
  } else {
    // Mostrar apenas os primeiros 9 projetos da categoria selecionada
    return projectData.value
      .filter(project => project.category === props.type)
      .slice(0, 9)
  }
})

// Função para alternar detalhes usando o ID do projeto
const toggleDetails = (projectId) => {
  // Inicializa o estado se não existir
  if (expandedStates[projectId] === undefined) {
    expandedStates[projectId] = false
  }
  // Alterna o estado específico da card
  expandedStates[projectId] = !expandedStates[projectId]
}

const viewProjectDetails = (projectId) => {
  router.get(`/project/${projectId}`)
}

const getDetailsButtonClass = () => {
  return 'bg-brand-blue hover:bg-blue-600'
}

// Função para obter a classe da borda baseada no tipo
const getBorderClass = () => {
  switch (props.type) {
    case 'andamento':
      return 'border border-brand'
    case 'parados':
      return 'border border-brand-red'
    case 'finalizados':
      return 'border border-brand-green'
    default:
      return 'border border-brand'
  }
}

// Função para obter a classe do botão baseada no tipo
const getButtonClass = () => {
  switch (props.type) {
    case 'andamento':
      return 'bg-brand hover:bg-orange-600'
    case 'parados':
      return 'bg-brand-red hover:bg-red-700'
    case 'finalizados':
      return 'bg-brand-green hover:bg-green-700'
    default:
      return 'bg-brand hover:bg-orange-600'
  }
}
</script>

<style scoped>
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from {
  transform: translateY(-10px);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateY(10px);
  opacity: 0;
}
</style>