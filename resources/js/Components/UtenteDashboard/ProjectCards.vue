<template>
  <div>
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-8">
      <p class="text-red-500">Erro ao carregar projetos: {{ error }}</p>
      <button @click="fetchProjects" class="mt-4 bg-brand text-white px-4 py-2 rounded-lg">
        Tentar Novamente
      </button>
    </div>

    <!-- Success State -->
    <div v-else>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="(project, index) in displayedProjects" :key="project.id" :class="[
          'bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden min-h-[230px] flex flex-col',
          getBorderClass()
        ]">
          <!-- CABEÇALHO COM IMAGEM E NOME DO PROJECTO -->
          <div class="flex items-center p-4 flex-shrink-0">
            <!-- IMAGEM DO PROJECTO -->
            <div class="w-20 h-20 flex items-center justify-center">
              <img :src="project.image_url || '/images/Emblem_of_Mozambique.svg-2.png'" 
                   :alt="project.name" 
                   class="w-20 h-20 object-contain" />
            </div>

            <!-- NOME DO PROJECTO -->
            <div class="ml-4 flex-1">
              <h3 class="font-bold text-gray-800 text-lg leading-tight">
                {{ project.name }}
              </h3>
            </div>
          </div>

          <!-- CONTEÚDO PRINCIPAL DA CARD -->
          <div class="flex-1 flex flex-col">
            <!-- BOTÃO MOSTRAR DETALHES -->
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
                  <p class="text-sm"><strong>Localização:</strong> {{ getLocation(project) }}</p>
                  <p class="text-sm"><strong>Responsável do Projeto:</strong> {{ project.finance?.responsavel || 'N/A' }}</p>
                  <p class="text-sm"><strong>Data de Início:</strong> {{ formatDate(project.deadline?.data_inicio) }}</p>
                  <p class="text-sm"><strong>Data de Termino:</strong> {{ formatDate(project.deadline?.data_finalizacao) }}</p>
                  <p class="text-sm"><strong>Orçamento:</strong> {{ project.finance?.valor_financiado || 'N/A' }}</p>
                </div>

                <div class="mt-auto space-y-2">
                  <!-- BOTÃO "VER DETALHES" -->
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

      <!-- Empty State -->
      <div v-if="displayedProjects.length === 0 && !loading" class="text-center py-8">
        <p class="text-gray-500">Nenhum projeto encontrado.</p>
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
  return 'bg-brand-blue hover:bg-blue-600'
}

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