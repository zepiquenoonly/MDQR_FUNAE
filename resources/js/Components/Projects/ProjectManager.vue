<template>
  <div class="min-h-screen dark:bg-dark-primary p-2 sm:p-4 -mt-4 sm:-mt-6 relative">
    <!-- Overlay do Formulário -->
    <div
      v-if="showRegisterForm && canEdit"
      class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-2 sm:p-4"
    >
      <div
        class="bg-white dark:bg-dark-secondary rounded-lg sm:rounded-xl shadow-xl w-full h-full sm:h-auto sm:max-w-6xl sm:max-h-[95vh] overflow-y-auto"
      >
        <div class="p-4 sm:p-6">
          <!--<div class="flex justify-between items-center mb-4">
            <h2
              class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-dark-text-primary"
            >
              Registar Novo Projecto
            </h2>
            <button
              @click="closeRegisterForm"
              class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 p-1"
            >
              <XMarkIcon class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
          </div>-->
          <ProjectRegister
            @project-created="handleProjectCreated"
            @cancel="closeRegisterForm"
          />
        </div>
      </div>
    </div>

    <!-- LISTAGEM DOS PROJECTOS -->
    <div
      class="bg-white dark:bg-dark-secondary rounded-lg sm:rounded-xl shadow-sm p-3 sm:p-6 -mx-2 sm:mx-0"
    >
      <ProjectsHeader
        :can-edit="canEdit"
        @search="handleSearch"
        @create="openRegisterForm"
      />

      <!-- Passar os projetos FILTRADOS para a tabela -->
      <ProjectsTable
        :projects="filteredProjects"
        :loading="loading"
        :search="searchQuery"
      />

      <!-- Informação de resultados -->
      <div
        v-if="filteredProjects.length > 0"
        class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1 sm:gap-2 mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-200 dark:border-gray-700"
      >
        <p class="text-xs text-gray-600 dark:text-gray-400">
          Mostrando <span class="font-semibold">{{ filteredProjects.length }}</span> de
          <span class="font-semibold">{{ projectList.length }}</span> projectos
          <span v-if="searchQuery"> para "{{ searchQuery }}"</span>
        </p>

        <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
          <span class="hidden sm:inline">Dica:</span>
          <span class="text-xs">Deslize para ver mais dados</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useForm, usePage } from "@inertiajs/vue3"; // Importe do Inertia
import {
  XMarkIcon,
  CheckCircleIcon,
  ClockIcon,
  PauseCircleIcon,
  FolderIcon,
} from "@heroicons/vue/24/outline";
import ProjectRegister from "./ProjectRegister.vue";
import ProjectStatsCard from "./ProjectStatsCard.vue";
import ProjectsHeader from "./ProjectsHeader.vue";
import ProjectsTable from "./ProjectsTable.vue";

const props = defineProps({
  canEdit: {
    type: Boolean,
    default: false,
  },
  projects: {
    type: [Object, Array],
    default: () => [],
  },
  stats: {
    type: Object,
    default: () => ({
      total: 0,
      finished: 0,
      progress: 0,
      suspended: 0,
    }),
  },
});

const page = usePage();
const showRegisterForm = ref(false);
const loading = ref(false);
const searchQuery = ref(""); // Renomeado para evitar conflito

// Usar stats diretamente das props
const stats = computed(() => props.stats);

// Extrair dados de projects (tratando tanto Paginator quanto Array)
const projectList = computed(() => {
  if (!props.projects) return [];

  // Se for um objeto paginator, extrair a propriedade data
  if (props.projects.data && Array.isArray(props.projects.data)) {
    return props.projects.data;
  }

  // Se já for um array, usar diretamente
  if (Array.isArray(props.projects)) {
    return props.projects;
  }

  return [];
});

// Filtro da pesquisa - CORRIGIDO
const filteredProjects = computed(() => {
  if (!searchQuery.value.trim()) return projectList.value;

  const query = searchQuery.value.toLowerCase().trim();

  return projectList.value.filter((project) => {
    if (!project || typeof project !== "object") return false;

    // Verificar nome do projeto
    const projectName = project.name || "";
    const projectBairro = project.bairro || "";
    const responsavel = project.finance?.responsavel || "";

    return (
      projectName.toLowerCase().includes(query) ||
      projectBairro.toLowerCase().includes(query) ||
      responsavel.toLowerCase().includes(query)
    );
  });
});

const handleProjectUpdated = (updatedProject) => {
  console.log("Projecto actualizado recebido:", updatedProject);

  if (!updatedProject || !updatedProject.id) {
    console.error("Projecto actualizado inválido:", updatedProject);
    // Recarregar página para obter dados atualizados
    window.location.reload();
    return;
  }

  const index = projectList.value.findIndex((p) => p.id === updatedProject.id);
  if (index !== -1) {
    // Atualizar no array local (se necessário)
    console.log("Projecto actualizado localmente");
  }
};

const handleProjectDeleted = (projectId) => {
  console.log("Eliminando projecto:", projectId);
  // A atualização será feita pelo reload da página
};

// Função de pesquisa CORRIGIDA
const handleSearch = (searchTerm) => {
  console.log("Pesquisando por:", searchTerm);
  searchQuery.value = searchTerm;
};

// Funções para controlar o formulário
const openRegisterForm = () => {
  showRegisterForm.value = true;
};

const closeRegisterForm = () => {
  showRegisterForm.value = false;
};

const handleProjectCreated = (projectData) => {
  console.log("Novo projecto criado:", projectData);
  closeRegisterForm();
  // Recarregar a página para obter dados atualizados
  window.location.reload();
};

// Log para debug
watch(filteredProjects, (newValue) => {
  console.log("Projetos filtrados:", newValue.length);
});

watch(searchQuery, (newValue) => {
  console.log("Query de pesquisa alterada:", newValue);
});
</script>

<style scoped>
/* Breakpoint personalizado para telas muito pequenas */
@media (max-width: 475px) {
  .xs\:flex-row {
    flex-direction: row;
  }

  .xs\:w-48 {
    width: 12rem;
  }

  .xs\:flex-none {
    flex: none;
  }

  .xs\:inline {
    display: inline;
  }

  .xs\:hidden {
    display: none;
  }
}

/* Remover margens negativas em mobile */
@media (max-width: 640px) {
  .-mx-2 {
    margin-left: -0.5rem;
    margin-right: -0.5rem;
  }

  .-mt-4 {
    margin-top: -1rem;
  }
}

/* Melhorias específicas para telas muito pequenas (<= 360px) */
@media (max-width: 360px) {
  .grid-cols-2 {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }
}
</style>
