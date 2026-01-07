<template>
  <div class="table-scroll-container">
    <!-- TABELA RESPONSIVA MELHORADA -->
    <div class="overflow-x-auto">
      <table class="w-full min-w-[600px] sm:min-w-[800px]">
        <thead>
          <tr class="border-b-2 border-gray-200 dark:border-gray-700 text-left">
            <th
              class="py-2 px-1 sm:px-2 md:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-8 sm:w-12"
            >
              #
            </th>
            <th
              class="py-2 px-1 sm:px-2 md:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[100px] sm:min-w-[120px]"
            >
              Responsável
            </th>
            <th
              class="py-2 px-1 sm:px-2 md:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[120px] sm:min-w-[150px]"
            >
              Nome do Projecto
            </th>
            <th
              class="py-2 px-1 sm:px-2 md:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[80px] sm:min-w-[100px]"
            >
              Bairro
            </th>
            <th
              class="py-2 px-1 sm:px-2 md:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16 sm:w-24"
            >
              Estado
            </th>
            <th
              class="py-2 px-1 sm:px-2 md:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12 sm:w-20 text-center"
            >
              Reclamações
            </th>
            <th
              class="py-2 px-1 sm:px-2 md:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12 sm:w-20 text-center"
            >
              Sugestões
            </th>
            <th
              class="py-2 px-1 sm:px-2 md:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12 sm:w-20 text-center"
            >
              Queixas
            </th>
            <th
              class="py-2 px-1 sm:px-2 md:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-20 sm:w-28"
            >
              Ações
            </th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="(project, index) in safeProjects"
            :key="getProjectKey(project, index)"
            class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-dark-accent transition-colors"
          >
            <td
              class="py-2 px-1 sm:px-2 md:px-4 text-xs sm:text-sm text-gray-800 dark:text-dark-text-primary font-medium"
            >
              {{ index + 1 }}
            </td>

            <td
              class="py-2 px-1 sm:px-2 md:px-4 text-xs sm:text-sm font-semibold text-gray-800 dark:text-dark-text-primary"
            >
              <div class="flex items-center gap-1 sm:gap-2 min-w-0">
                <img
                  :src="getProjectImage(project)"
                  class="w-6 h-6 sm:w-8 sm:h-8 md:w-10 md:h-10 rounded object-cover flex-shrink-0"
                  :alt="getProjectName(project)"
                />
                <span class="truncate text-xs sm:text-sm">{{
                  getProjectResponsible(project)
                }}</span>
              </div>
            </td>

            <td
              class="py-2 px-1 sm:px-2 md:px-4 text-xs sm:text-sm text-gray-800 dark:text-dark-text-primary"
            >
              <span class="truncate-2-lines block leading-tight text-xs sm:text-sm">{{
                getProjectName(project)
              }}</span>
            </td>

            <td
              class="py-2 px-1 sm:px-2 md:px-4 text-xs sm:text-sm text-gray-600 dark:text-gray-400"
            >
              <span class="truncate block text-xs sm:text-sm">{{
                getProjectBairro(project)
              }}</span>
            </td>

            <td class="py-2 px-1 sm:px-2 md:px-4">
              <span
                :class="[
                  'px-1.5 py-0.5 sm:px-2 sm:py-1 text-xs font-semibold rounded-full whitespace-nowrap',
                  getStatusClass(project?.category),
                ]"
              >
                {{ getStatusLabel(project?.category) }}
              </span>
            </td>

            <td
              class="py-2 px-1 sm:px-2 md:px-4 text-center text-xs sm:text-sm font-bold text-gray-700 dark:text-dark-text-primary"
            >
              {{ getProjectReclamacoes(project) }}
            </td>

            <td
              class="py-2 px-1 sm:px-2 md:px-4 text-center text-xs sm:text-sm font-bold text-gray-700 dark:text-dark-text-primary"
            >
              {{ getProjectSugestoes(project) }}
            </td>

            <td
              class="py-2 px-1 sm:px-2 md:px-4 text-center text-xs sm:text-sm font-bold text-gray-700 dark:text-dark-text-primary"
            >
              <div class="flex flex-col items-center">
                <!-- Total de Queixas -->
                <span class="dark:text-red-400 font-bold">
                  {{ getTotalQueixas(project) }}
                </span>
                <!-- Breakdown (opcional) -->
                <div
                  class="flex gap-1 text-xs text-gray-500 dark:text-gray-400"
                  v-if="showQueixasBreakdown"
                >
                  <span>{{ getQueixasAbertas(project) }} aberta(s)</span>
                </div>
              </div>
            </td>

            <td class="py-2 px-1 sm:px-2 md:px-4">
              <Link
                v-if="isValidProject(project)"
                :href="getProjectUrl(project)"
                class="inline-block bg-brand hover:bg-orange-600 text-white px-2 py-1 sm:px-3 sm:py-1.5 rounded text-xs font-semibold flex items-center gap-1 w-full justify-center whitespace-nowrap transition-colors"
              >
                <EyeIcon class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                <span class="hidden xs:inline">Detalhes</span>
                <span class="xs:hidden">Ver</span>
              </Link>
              <span
                v-else
                class="inline-block bg-gray-300 text-gray-600 px-2 py-1 sm:px-3 sm:py-1.5 rounded text-xs font-semibold flex items-center gap-1 w-full justify-center whitespace-nowrap"
              >
                <EyeSlashIcon class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                N/A
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-6 sm:py-8">
      <div
        class="animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-brand mx-auto"
      ></div>
      <p class="text-gray-600 dark:text-gray-400 mt-2 text-xs sm:text-sm">
        A carregar projectos...
      </p>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="safeProjects.length === 0 && !loading"
      class="text-center py-6 sm:py-8"
    >
      <FolderIcon
        class="w-8 h-8 sm:w-12 sm:h-12 text-gray-400 dark:text-gray-600 mx-auto mb-2 sm:mb-3"
      />
      <p class="text-gray-600 dark:text-gray-400 text-xs sm:text-sm">
        Nenhum projecto encontrado.
      </p>
    </div>

    <!-- No Results State -->
    <div
      v-else-if="safeProjects.length === 0 && search && !loading"
      class="text-center py-6 sm:py-8"
    >
      <MagnifyingGlassIcon
        class="w-8 h-8 sm:w-12 sm:h-12 text-gray-400 dark:text-gray-600 mx-auto mb-2 sm:mb-3"
      />
      <p class="text-gray-600 dark:text-gray-400 text-xs sm:text-sm">
        Nenhum projecto encontrado para "{{ search }}"
      </p>
    </div>
  </div>

  <div class="mt-8 flex justify-end">
    <button
      @click="exportToPDF"
      :disabled="loading || safeProjects.length === 0 || isExporting"
      class="flex items-center gap-2 bg-brand hover:bg-orange-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-3 sm:px-4 py-2 rounded text-xs sm:text-sm font-semibold transition-colors shadow-md hover:shadow-lg"
    >
      <!-- Spinner quando está exportando -->
      <svg
        v-if="isExporting"
        class="animate-spin h-4 w-4 sm:h-5 sm:w-5 text-white"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
      >
        <circle
          class="opacity-25"
          cx="12"
          cy="12"
          r="10"
          stroke="currentColor"
          stroke-width="4"
        ></circle>
        <path
          class="opacity-75"
          fill="currentColor"
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        ></path>
      </svg>

      <!-- Ícone normal quando não está exportando -->
      <DocumentArrowDownIcon v-else class="w-4 h-4 sm:w-5 sm:h-5" />

      <!-- Texto do botão com estados diferentes -->
      <span v-if="isExporting" class="hidden sm:inline">A exportar...</span>
      <span v-else class="hidden sm:inline">Exportar PDF</span>

      <span v-if="isExporting" class="sm:hidden">...</span>
      <span v-else class="sm:hidden">PDF</span>
    </button>
  </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import {
  EyeIcon,
  FolderIcon,
  MagnifyingGlassIcon,
  EyeSlashIcon,
  DocumentArrowDownIcon,
} from "@heroicons/vue/24/outline";

const emit = defineEmits(["view-details"]);

const props = defineProps({
  projects: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  search: {
    type: String,
    default: "",
  },
  // Nova prop para controlar se mostra breakdown das queixas
  showQueixasBreakdown: {
    type: Boolean,
    default: false,
  },
});

const getProjectUrl = (project) => {
  if (!project?.id) return "#";

  // Baseado na rota atual, determine o prefixo
  if (props.currentRoute?.includes("director")) {
    return `/director/projects/${project.id}`;
  } else if (props.currentRoute?.includes("gestor")) {
    return `/gestor/projects/${project.id}`;
  } else {
    // Fallback
    return `/gestor/projects/${project.id}`;
  }
};

// Funções helper para segurança de dados
const isValidProject = (project) => {
  return project && typeof project === "object" && project.id;
};

const getProjectKey = (project, index) => {
  return isValidProject(project) ? project.id : `project-${index}`;
};

const getProjectImage = (project) => {
  return isValidProject(project)
    ? project.image_url || "/images/default-project.png"
    : "/images/default-project.png";
};

const getProjectName = (project) => {
  return isValidProject(project)
    ? project.name || "Nome não disponível"
    : "Projecto inválido";
};

const getProjectResponsible = (project) => {
  return isValidProject(project) ? project.finance?.responsavel || "N/A" : "N/A";
};

const getProjectBairro = (project) => {
  return isValidProject(project) ? project.bairro || "N/A" : "N/A";
};

// Funções para obter dados de reclamações/sugestões
const getProjectReclamacoes = (project) => {
  if (!isValidProject(project)) return "0";

  // Dados podem vir de diferentes lugares:
  // 1. Diretamente do projeto
  // 2. De uma propriedade específica (ex: project.metrics)
  // 3. De uma relação (ex: project.reclamacoes_count)

  // Verifique qual estrutura o backend está enviando
  console.log("Dados do projeto para reclamações:", project);

  // Exemplo de possíveis estruturas:
  // 1. project.reclamacoes_count
  // 2. project.metrics?.reclamacoes
  // 3. project.reclamacoes?.total

  return (
    project.reclamacoes_count ||
    project.metrics?.reclamacoes ||
    project.reclamacoes?.total ||
    "0"
  );
};

const getProjectSugestoes = (project) => {
  if (!isValidProject(project)) return "0";

  return (
    project.sugestoes_count ||
    project.metrics?.sugestoes ||
    project.sugestoes?.total ||
    "0"
  );
};

// Funções para Queixas (que podem incluir reclamações e/ou outras categorias)
const getTotalQueixas = (project) => {
  if (!isValidProject(project)) return "0";

  // Se o backend já envia total_queixas, use isso
  if (project.total_queixas !== undefined) {
    return project.total_queixas;
  }

  // Caso contrário, some reclamações e sugestões
  const reclamacoes = parseInt(getProjectReclamacoes(project)) || 0;
  const sugestoes = parseInt(getProjectSugestoes(project)) || 0;

  return reclamacoes + sugestoes;
};

const getQueixasAbertas = (project) => {
  if (!isValidProject(project)) return "0";

  return project.queixas_abertas || project.metrics?.queixas_abertas || "0";
};

// Garantir que sempre temos um array válido
const safeProjects = computed(() => {
  console.log("Projects recebidos:", props.projects);
  console.log("Tipo:", typeof props.projects);

  if (!props.projects || !Array.isArray(props.projects)) {
    console.warn("Projects não é um array válido:", props.projects);
    return [];
  }

  const filtered = props.projects.filter((project) => {
    return project && typeof project === "object";
  });

  console.log("Projetos filtrados:", filtered.length);
  return filtered;
});

// Badge de estado - agora mais seguro
const getStatusClass = (category) => {
  if (!category) return "bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300";

  switch (category) {
    case "finalizados":
      return "bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300";
    case "andamento":
      return "bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300";
    case "parados":
      return "bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300";
    default:
      return "bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300";
  }
};

const getStatusLabel = (category) => {
  if (!category) return "N/A";

  const labels = {
    finalizados: "Finalizado",
    andamento: "Em Andamento",
    parados: "Parado",
  };
  return labels[category] || category;
};

const isExporting = ref(false);

const exportToPDF = async () => {
  if (isExporting.value) return;

  try {
    // Ativar estado de loading
    isExporting.value = true;

    // Determinar a rota base
    let baseRoute = "/gestor";
    const currentPath = window.location.pathname;

    if (currentPath.includes("/director")) {
      baseRoute = "/director";
    } else if (currentPath.includes("/admin")) {
      baseRoute = "/admin";
    }

    // Preparar parâmetros
    const params = new URLSearchParams();

    if (props.search) {
      params.append("search", props.search);
    }

    if (props.filters && Object.keys(props.filters).length > 0) {
      Object.entries(props.filters).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== "") {
          params.append(`filters[${key}]`, value);
        }
      });
    }

    // Adicionar timestamp para evitar cache
    params.append("_t", Date.now());

    // Construir URL
    const queryString = params.toString();
    const url = `${baseRoute}/projects/export/pdf${queryString ? "?" + queryString : ""}`;

    // Criar link temporário para download
    const link = document.createElement("a");
    link.href = url;
    link.target = "_blank";
    link.download = `projectos_${new Date().toISOString().slice(0, 10)}.pdf`;

    // Simular clique para iniciar download
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Simular tempo de processamento (pode remover em produção)
    await new Promise((resolve) => setTimeout(resolve, 1500));
  } catch (error) {
    console.error("Erro ao exportar PDF:", error);

    // Em produção, você pode usar um sistema de notificação
    alert("Ocorreu um erro ao exportar o PDF. Por favor, tente novamente.");
  } finally {
    // Sempre desativar o loading
    isExporting.value = false;
  }
};
</script>

<style scoped>
/* Container de rolagem para a tabela */
.table-scroll-container {
  max-height: calc(100vh - 280px);
  overflow-y: auto;
  overflow-x: auto;
}

/* Scrollbar personalizada */
.table-scroll-container::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.table-scroll-container::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.table-scroll-container::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.table-scroll-container::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Dark mode para scrollbar */
.dark .table-scroll-container::-webkit-scrollbar-track {
  background: #374151;
}

.dark .table-scroll-container::-webkit-scrollbar-thumb {
  background: #6b7280;
}

.dark .table-scroll-container::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

/* Truncate para 2 linhas */
.truncate-2-lines {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.2;
}

/* Ajustes para diferentes tamanhos de tela */
@media (max-width: 640px) {
  .table-scroll-container {
    max-height: calc(100vh - 220px);
  }

  /*.min-w-[600px] {
    min-width: 700px; 
  }*/
}

@media (min-width: 641px) and (max-width: 1024px) {
  .table-scroll-container {
    max-height: calc(100vh - 260px);
  }
}

@media (min-width: 1025px) {
  .table-scroll-container {
    max-height: 600px;
  }
}
</style>
