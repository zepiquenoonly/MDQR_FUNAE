<template>
  <div>
    <!-- Loading State -->
    <div v-if="loading" class="glass-card p-12 text-center hover:shadow-2xl transition-all duration-300 border border-white/40">
      <div class="w-16 h-16 mx-auto mb-6 border-4 border-primary-500 rounded-full animate-spin border-t-transparent"></div>
      <p class="text-lg font-semibold text-gray-800">Carregando projetos...</p>
      <p class="text-sm text-gray-600 mt-2">Aguarde um momento</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="glass-card p-8 text-center hover:shadow-2xl transition-all duration-300 border border-red-200 relative overflow-hidden group">
      <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 to-orange-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div class="relative z-10">
        <div class="p-4 bg-red-50 rounded-2xl inline-block mb-4">
          <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="mb-2 text-xl font-bold text-gray-900">Erro ao carregar projetos</p>
        <p class="mb-6 text-gray-600">{{ error }}</p>
        <button @click="fetchProjects" class="px-6 py-3 font-semibold text-white bg-gradient-to-r from-primary-500 to-orange-600 hover:from-primary-600 hover:to-orange-700 rounded-xl transition-all duration-300 hover:scale-105 shadow-lg hover:shadow-2xl border border-orange-400/30">
          Tentar Novamente
        </button>
      </div>
    </div>

    <!-- Success State -->
    <div v-else>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <div v-for="(project, index) in displayedProjects" :key="project.id" :class="[
          'bg-white rounded-xl overflow-hidden min-h-[250px] flex flex-col hover:shadow-lg transition-all duration-200 border group',
          getBorderClass()
        ]">
          <!-- CABEÇALHO COM IMAGEM E NOME DO PROJECTO -->
          <div class="flex items-center p-5 flex-shrink-0 border-b border-gray-100">
            <!-- IMAGEM DO PROJECTO -->
            <div class="w-16 h-16 flex items-center justify-center flex-shrink-0 bg-gray-50 rounded-lg border border-gray-200">
              <img :src="project.image_url || '/images/Emblem_of_Mozambique.svg-2.png'"
                   :alt="project.name"
                   class="w-12 h-12 object-contain" />
            </div>

            <!-- NOME DO PROJECTO -->
            <div class="ml-4 flex-1">
              <h3 class="font-semibold text-gray-900 text-base leading-tight mb-2">
                {{ project.name }}
              </h3>
              <span :class="['inline-block px-2.5 py-1 text-xs font-medium rounded-md', getStatusBadgeClass()]">
                {{ getStatusText() }}
              </span>
            </div>
          </div>

          <!-- CONTEÚDO PRINCIPAL DA CARD -->
          <div class="flex-1 flex flex-col">
            <!-- BOTÃO MOSTRAR DETALHES -->
            <div v-if="!expandedStates[project.id]" class="px-5 py-4 flex items-center justify-center flex-1 bg-gray-50">
              <button @click="toggleDetails(project.id)" class="w-full py-2.5 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 hover:border-gray-400 transition-all duration-200 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
                <span>Mais detalhes</span>
              </button>
            </div>

            <!-- DETALHES EXPANDIDOS -->
            <transition name="slide-fade">
              <div v-if="expandedStates[project.id]" class="px-5 py-4 space-y-3 flex-1 bg-gray-50 border-t border-gray-100">
                <div class="space-y-2.5">
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-600 font-medium">Localização:</span>
                    <span class="text-gray-900">{{ getLocation(project) }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-600 font-medium">Responsável:</span>
                    <span class="text-gray-900">{{ project.finance?.responsavel || 'N/A' }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-600 font-medium">Data de Início:</span>
                    <span class="text-gray-900">{{ formatDate(project.deadline?.data_inicio) }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-600 font-medium">Data de Término:</span>
                    <span class="text-gray-900">{{ formatDate(project.deadline?.data_finalizacao) }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-600 font-medium">Orçamento:</span>
                    <span class="text-gray-900 font-semibold">{{ project.finance?.valor_financiado || 'N/A' }}</span>
                  </div>
                </div>

                <div class="mt-4 space-y-2 pt-3 border-t border-gray-200">
                  <!-- BOTÃO "VER DETALHES" -->
                  <button @click="viewProjectDetails(project.id)" :class="[
                    'text-white w-full py-2.5 rounded-lg font-medium transition-all duration-200 text-sm',
                    getDetailsButtonClass()
                  ]">
                    Ver detalhes completos
                  </button>

                  <!-- BOTÃO OCULTAR -->
                  <button @click="toggleDetails(project.id)"
                    class="bg-white hover:bg-gray-50 text-gray-700 w-full py-2.5 rounded-lg flex items-center justify-center gap-2 font-medium transition-all duration-200 border border-gray-300 hover:border-gray-400 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                    </svg>
                    <span>Ocultar detalhes</span>
                  </button>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="displayedProjects.length === 0 && !loading" class="glass-card p-12 text-center hover:shadow-2xl transition-all duration-300 border border-white/40">
        <div class="p-6 bg-gray-50 rounded-2xl inline-block mb-4">
          <svg class="w-16 h-16 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
          </svg>
        </div>
        <p class="text-xl font-bold text-gray-900 mb-2">Nenhum projeto encontrado</p>
        <p class="text-gray-600">Não há projetos disponíveis no momento.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'andamento'
  },
  showAllProjects: {
    type: Boolean,
    default: false
  }
})

// Estados reativos
const projectData = ref([])
const loading = ref(false)
const error = ref(null)
const expandedStates = reactive({})

const emit = defineEmits(['view-project-details'])

// Buscar projetos da API
const fetchProjects = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await fetch('/api/projects', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (!response.ok) {
      throw new Error(`Erro ${response.status}: ${response.statusText}`)
    }
    
    const data = await response.json()
    projectData.value = data
    console.log('Projetos carregados:', data)
  } catch (err) {
    console.error('Erro ao buscar projetos:', err)
    error.value = err.message
  } finally {
    loading.value = false
  }
}

// Computed property para filtrar projetos
const displayedProjects = computed(() => {
  if (props.showAllProjects) {
    return projectData.value
  } else {
    return projectData.value
      .filter(project => project.category === props.type)
      .slice(0, 9)
  }
})

// Funções auxiliares
const getLocation = (project) => {
  if (project.provincia && project.distrito && project.bairro) {
    return `${project.bairro}, ${project.distrito}, ${project.provincia}`
  }
  return project.detailedLocation || 'Localização não disponível'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('pt-MZ', {
      day: '2-digit',
      month: 'long',
      year: 'numeric'
    })
  } catch {
    return dateString
  }
}

// Resto das funções permanecem iguais
const toggleDetails = (projectId) => {
  if (expandedStates[projectId] === undefined) {
    expandedStates[projectId] = false
  }
  expandedStates[projectId] = !expandedStates[projectId]
}

const viewProjectDetails = (projectId) => {
  emit('view-project-details', projectId)
}

const getDetailsButtonClass = () => {
  return 'bg-blue-600 hover:bg-blue-700'
}

const getBorderClass = () => {
  switch (props.type) {
    case 'andamento':
      return 'border-gray-200 hover:border-primary-300'
    case 'parados':
      return 'border-gray-200 hover:border-red-300'
    case 'finalizados':
      return 'border-gray-200 hover:border-green-300'
    default:
      return 'border-gray-200 hover:border-primary-300'
  }
}

const getButtonClass = () => {
  switch (props.type) {
    case 'andamento':
      return 'bg-primary-600 hover:bg-primary-700'
    case 'parados':
      return 'bg-red-600 hover:bg-red-700'
    case 'finalizados':
      return 'bg-green-600 hover:bg-green-700'
    default:
      return 'bg-primary-600 hover:bg-primary-700'
  }
}

const getStatusBadgeClass = () => {
  switch (props.type) {
    case 'andamento':
      return 'bg-primary-50 text-primary-700 border border-primary-200'
    case 'parados':
      return 'bg-red-50 text-red-700 border border-red-200'
    case 'finalizados':
      return 'bg-green-50 text-green-700 border border-green-200'
    default:
      return 'bg-primary-50 text-primary-700 border border-primary-200'
  }
}

const getStatusText = () => {
  switch (props.type) {
    case 'andamento':
      return 'Em andamento'
    case 'parados':
      return 'Parado'
    case 'finalizados':
      return 'Finalizado'
    default:
      return 'Projeto'
  }
}

// Buscar projetos quando o componente for montado
onMounted(() => {
  fetchProjects()
})
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