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
          <div class="flex justify-between items-center mb-4">
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
          </div>
          <ProjectRegister
            @project-created="handleProjectCreated"
            @cancel="closeRegisterForm"
          />
        </div>
      </div>
    </div>

    <!-- CARDS SUPERIORES -->
    <div
      class="grid grid-cols-2 xs:grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4 lg:gap-6 mb-4 sm:mb-8"
    >
      <ProjectStatsCard
        title="Finalizados"
        :value="stats.finished"
        description="Projectos Finalizados"
        :icon="CheckCircleIcon"
        title-color-class="text-green-700 dark:text-green-400"
        icon-bg-class="bg-green-100 dark:bg-green-900/20"
        icon-color-class="text-green-600 dark:text-green-400"
      />

      <ProjectStatsCard
        title="Em Andamento"
        :value="stats.progress"
        description="Projectos em Andamento"
        :icon="ClockIcon"
        title-color-class="text-yellow-700 dark:text-yellow-400"
        icon-bg-class="bg-yellow-100 dark:bg-yellow-900/20"
        icon-color-class="text-yellow-600 dark:text-yellow-400"
      />

      <ProjectStatsCard
        title="Parados"
        :value="stats.suspended"
        description="Projectos Parados"
        :icon="PauseCircleIcon"
        title-color-class="text-red-700 dark:text-red-400"
        icon-bg-class="bg-red-100 dark:bg-red-900/20"
        icon-color-class="text-red-600 dark:text-red-400"
      />

      <ProjectStatsCard
        title="Total"
        :value="stats.total"
        description="Todos Projectos"
        :icon="FolderIcon"
        title-color-class="text-blue-700 dark:text-blue-400"
        icon-bg-class="bg-blue-100 dark:bg-blue-900/20"
        icon-color-class="text-blue-600 dark:text-blue-400"
      />
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

      <ProjectsTable :projects="filteredProjects" :loading="loading" :search="search" />

      <!-- Informação de resultados -->
      <div
        v-if="filteredProjects.length > 0"
        class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1 sm:gap-2 mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-200 dark:border-gray-700"
      >
        <p class="text-xs text-gray-600 dark:text-gray-400">
          Mostrando <span class="font-semibold">{{ filteredProjects.length }}</span> de
          <span class="font-semibold">{{ projects.length }}</span> projectos
          <span v-if="search"> para "{{ search }}"</span>
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
import { ref, computed } from "vue";
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
  initialProjects: {
    type: Array,
    default: () => [],
  },
  initialStats: {
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
const search = ref("");

// Usar projetos iniciais passados via Inertia
const projects = ref(props.initialProjects);

// Usar estatísticas iniciais ou calcular a partir dos projetos
const stats = computed(() => {
  // Se temos projetos, calcular estatísticas em tempo real
  if (projects.value.length > 0) {
    const finished = projects.value.filter((p) => p.category === "finalizados").length;
    const progress = projects.value.filter((p) => p.category === "andamento").length;
    const suspended = projects.value.filter((p) => p.category === "parados").length;
    const total = projects.value.length;

    return {
      finished,
      progress,
      suspended,
      total,
    };
  }

  // Caso contrário, usar estatísticas iniciais
  return props.initialStats;
});

// Filtro da pesquisa
const filteredProjects = computed(() => {
  if (!search.value) return projects.value;

  return projects.value.filter(
    (project) =>
      project.name.toLowerCase().includes(search.value.toLowerCase()) ||
      (project.finance?.responsavel?.toLowerCase() || "").includes(
        search.value.toLowerCase()
      ) ||
      project.bairro.toLowerCase().includes(search.value.toLowerCase())
  );
});

const handleProjectUpdated = (updatedProject) => {
  console.log("Projecto actualizado recebido:", updatedProject);

  if (!updatedProject || !updatedProject.id) {
    console.error("Projecto actualizado inválido:", updatedProject);
    // Recarregar página para obter dados atualizados
    window.location.reload();
    return;
  }

  const index = projects.value.findIndex((p) => p.id === updatedProject.id);
  if (index !== -1) {
    projects.value[index] = { ...projects.value[index], ...updatedProject };
  } else {
    projects.value.unshift(updatedProject);
  }
};

const handleProjectDeleted = (projectId) => {
  console.log("Eliminando projecto:", projectId);
  projects.value = projects.value.filter((p) => p.id !== projectId);
};

const handleSearch = (searchTerm) => {
  search.value = searchTerm;
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
  if (projectData && projectData.id) {
    projects.value.unshift(projectData);
  }
  closeRegisterForm();
  // Recarregar a página para obter dados atualizados
  window.location.reload();
};
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
