<template>
  <div
    class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6"
  >
    <!-- Header compacto -->
    <div
      class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4 sm:mb-6"
    >
      <h3 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary">
        Submissões Recentes
      </h3>
      <div class="flex gap-2 self-end sm:self-auto">
        <button
          @click="toggleAllComplaints"
          class="text-brand hover:text-orange-600 text-sm font-medium"
        >
          {{ showAllComplaints ? "Ver Resumo" : "Ver Todos" }}
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div
        class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand mx-auto"
      ></div>
      <p class="text-gray-500 dark:text-gray-400 mt-2">Carregando dados...</p>
    </div>

    <!-- Conteúdo principal -->
    <div v-else>
      <!-- Visualização Resumida -->
      <div v-if="!showAllComplaints">
        <!-- Filtros -->
        <div class="space-y-3 mb-4 sm:mb-6">
          <h3 class="font-semibold text-gray-800 dark:text-dark-text-primary text-sm">
            Filtrar Por:
          </h3>
          <!-- Layout responsivo: grid 2x2 em mobile, linha única em desktop -->
          <div class="grid grid-cols-2 gap-3 md:flex md:flex-wrap md:gap-4">
            <!-- Prioridade -->
            <div class="flex flex-col min-w-0 flex-1 md:flex-none md:w-auto">
              <label
                for="priority"
                class="mb-1 text-xs font-medium text-gray-700 dark:text-gray-300"
              >
                Prioridade
              </label>
              <select
                id="priority"
                v-model="localFilters.priority"
                class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-xs focus:border-orange-500 focus:ring-1 focus:ring-orange-200 transition-all duration-200 appearance-none bg-white dark:bg-dark-accent cursor-pointer dark:text-dark-text-primary"
              >
                <option value="">Todos</option>
                <option value="high">Alta</option>
                <option value="medium">Média</option>
                <option value="low">Baixa</option>
              </select>
            </div>

            <!-- Estado -->
            <div class="flex flex-col min-w-0 flex-1 md:flex-none md:w-auto">
              <label
                for="status"
                class="mb-1 text-xs font-medium text-gray-700 dark:text-gray-300"
              >
                Estado
              </label>
              <select
                id="status"
                v-model="localFilters.status"
                class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-xs focus:border-orange-500 focus:ring-1 focus:ring-orange-200 transition-all duration-200 dark:bg-dark-accent dark:text-dark-text-primary"
              >
                <option value="">Todos</option>
                <option value="submitted">Submetida</option>
                <option value="under_review">Em Análise</option>
                <option value="assigned">Atribuída</option>
                <option value="in_progress">Em Andamento</option>
                <option value="pending_approval">Pendente Aprovação</option>
                <option value="resolved">Resolvida</option>
                <option value="rejected">Rejeitada</option>
              </select>
            </div>

            <!-- Tipo -->
            <div class="flex flex-col min-w-0 flex-1 md:flex-none md:w-auto">
              <label
                for="type"
                class="mb-1 text-xs font-medium text-gray-700 dark:text-gray-300"
              >
                Tipo
              </label>
              <select
                id="type"
                v-model="localFilters.type"
                class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-xs focus:border-orange-500 focus:ring-1 focus:ring-orange-200 transition-all duration-200 dark:bg-dark-accent dark:text-dark-text-primary"
              >
                <option value="">Todos</option>
                <option value="suggestion">Sugestão</option>
                <option value="grievance">Queixa</option>
                <option value="complaint">Reclamação</option>
              </select>
            </div>

            <!-- Categoria -->
            <div class="flex flex-col min-w-0 flex-1 md:flex-none md:w-auto">
              <label
                for="category"
                class="mb-1 text-xs font-medium text-gray-700 dark:text-gray-300"
              >
                Categoria
              </label>
              <select
                id="category"
                v-model="localFilters.category"
                class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-xs focus:border-orange-500 focus:ring-1 focus:ring-orange-200 transition-all duration-200 dark:bg-dark-accent dark:text-dark-text-primary"
              >
                <option value="">Todas</option>
                <option value="Serviços Públicos">Serviços Públicos</option>
                <option value="Infraestrutura">Infraestrutura</option>
                <option value="Ambiental">Ambiental</option>
                <option value="Social">Social</option>
                <option value="Administração">Administração</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Container com rolagem interna para lista de reclamações -->
        <div class="complaints-scroll-container">
          <!-- Complaints List -->
          <div class="space-y-2">
            <ComplaintRow
              v-for="complaint in recentComplaints"
              :key="complaint.id"
              :complaint="complaint"
              :selected="selectedComplaint?.id === complaint.id"
              @select="handleSelectComplaint"
              @show-details="handleRowClick"
            />
          </div>

          <!-- Empty State -->
          <div v-if="recentComplaints.length === 0" class="text-center py-8">
            <DocumentMagnifyingGlassIcon
              class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4"
            />
            <p class="text-gray-500 dark:text-gray-400">Nenhuma reclamação encontrada</p>
          </div>
        </div>

        <!-- Footer compacto -->
        <div
          class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mt-4"
        >
          <span class="text-gray-500 dark:text-gray-400 text-xs sm:text-sm">
            Mostrando {{ recentComplaints.length }} de
            {{ props.allComplaints.length }} reclamações
          </span>
          <div class="flex gap-2 self-end">
            <button
              @click="handleBulkAssign"
              class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-gray-700 dark:text-gray-300 text-xs font-medium hover:bg-gray-50 dark:hover:bg-dark-accent transition-all duration-200"
            >
              Atribuição Auto.
            </button>
            <button
              @click="handleExport"
              class="px-3 py-1.5 bg-brand text-white rounded text-xs font-medium hover:bg-orange-600 transition-all duration-200 shadow-md"
            >
              Exportar
            </button>
          </div>
        </div>
      </div>

      <!-- Visualização Completa -->
      <div v-else class="space-y-4">
        <!-- Header da visualização completa -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
          <h3
            class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-dark-text-primary"
          >
            Todas as Reclamações e Sugestões
            <span class="text-sm font-normal text-gray-500"
              >(Total: {{ props.allComplaints.length }})</span
            >
          </h3>
          <button
            @click="toggleAllComplaints"
            class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-gray-700 dark:text-gray-300 text-xs sm:text-sm font-medium hover:bg-gray-50 dark:hover:bg-dark-accent transition-all duration-200 self-start"
          >
            Voltar
          </button>
        </div>

        <!-- Tabs CORRIGIDAS: Sugestões, Queixas e Reclamações -->
        <div class="border-b border-gray-200 dark:border-gray-700">
          <nav class="-mb-px flex space-x-4 overflow-x-auto">
            <button
              @click="activeTab = 'suggestions'"
              :class="[
                activeTab === 'suggestions'
                  ? 'border-brand text-brand dark:text-orange-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
              ]"
            >
              Sugestões
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ suggestionsCount }}
              </span>
            </button>
            <button
              @click="activeTab = 'grievances'"
              :class="[
                activeTab === 'grievances'
                  ? 'border-brand text-brand dark:text-orange-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
              ]"
            >
              Queixas
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ grievancesCount }}
              </span>
            </button>
            <button
              @click="activeTab = 'complaints'"
              :class="[
                activeTab === 'complaints'
                  ? 'border-brand text-brand dark:text-orange-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
              ]"
            >
              Reclamações
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ complaintsCount }}
              </span>
            </button>
            <button
              @click="activeTab = 'all'"
              :class="[
                activeTab === 'all'
                  ? 'border-brand text-brand dark:text-orange-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
              ]"
            >
              Todos
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ props.allComplaints.length }}
              </span>
            </button>
          </nav>
        </div>

        <!-- Container com rolagem interna para tabela -->
        <div class="table-scroll-container">
          <div class="overflow-x-auto -mx-4 sm:mx-0">
            <div class="min-w-full inline-block align-middle">
              <table
                class="min-w-full divide-y divide-gray-300 dark:divide-gray-600 text-xs sm:text-sm"
              >
                <thead class="bg-gray-50 dark:bg-dark-accent">
                  <tr>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      ID
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-32"
                    >
                      Título
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Tipo
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Prioridade
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Estado
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Data
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Ações
                    </th>
                  </tr>
                </thead>
                <tbody
                  class="bg-white dark:bg-dark-secondary divide-y divide-gray-200 dark:divide-gray-700"
                >
                  <tr
                    v-for="item in currentTabData"
                    :key="item.id"
                    class="hover:bg-gray-50 dark:hover:bg-dark-accent"
                  >
                    <td
                      class="px-3 py-2 whitespace-nowrap font-medium text-gray-900 dark:text-dark-text-primary"
                    >
                      #{{ item.id }}
                    </td>
                    <td
                      class="px-3 py-2 text-gray-900 dark:text-dark-text-primary max-w-32 truncate"
                    >
                      {{ item.title || item.description }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                      <span :class="getTypeBadgeClass(item.type)">
                        {{ getTypeLabel(item.type) }}
                      </span>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                      <span :class="getPriorityBadgeClass(item.priority)">
                        {{ getPriorityLabel(item.priority) }}
                      </span>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                      <span :class="getStatusBadgeClass(item.status)">
                        {{ getStatusLabel(item.status) }}
                      </span>
                    </td>
                    <td
                      class="px-3 py-2 whitespace-nowrap text-gray-500 dark:text-gray-400"
                    >
                      {{ formatDate(item.created_at) }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap font-medium">
                      <button
                        @click="handleRowClick(item)"
                        class="text-brand hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300 text-xs"
                      >
                        Detalhes
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Empty State para tabela -->
              <div v-if="currentTabData.length === 0" class="text-center py-8">
                <DocumentMagnifyingGlassIcon
                  class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4"
                />
                <p class="text-gray-500 dark:text-gray-400">Nenhum dado encontrado</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Paginação -->
        <div
          class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-4 border-t border-gray-200 dark:border-gray-700"
        >
          <p class="text-xs text-gray-700 dark:text-gray-300">
            Mostrando <span class="font-medium">{{ currentTabData.length }}</span> de
            {{ props.allComplaints.length }} resultados
          </p>
          <div class="flex gap-2 self-end">
            <button
              @click="handleExport"
              class="px-3 py-1.5 bg-brand text-white rounded text-xs font-medium hover:bg-orange-600 transition-all duration-200"
            >
              Exportar {{ getExportLabel() }}
            </button>
            <button
              @click="handleBulkAssign"
              class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-gray-700 dark:text-gray-300 text-xs font-medium hover:bg-gray-50 dark:hover:bg-dark-accent transition-all duration-200"
            >
              Atribuição Auto.
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { DocumentMagnifyingGlassIcon } from "@heroicons/vue/24/outline";
import ComplaintRow from "./ComplaintRow.vue";

const props = defineProps({
  complaints: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  allComplaints: {
    type: Array,
    default: () => [],
  },
});

// CORREÇÃO: Removido o emit "show-details" para evitar abrir popup
const emit = defineEmits(["update:filters", "select-complaint"]);

const localFilters = ref({
  ...props.filters,
  type: props.filters.type || "", // Adicionar filtro de tipo
});
const selectedComplaint = ref(null);
const showAllComplaints = ref(false);
const activeTab = ref("suggestions");
const loading = ref(false);

// CORREÇÃO: Computed property para reclamações recentes - ordenar por data mais recente
const recentComplaints = computed(() => {
  // Usar allComplaints que já vem ordenado do backend por submitted_at desc
  let complaints = [...props.allComplaints];

  // Ordenar por data de criação (mais recente primeiro) como fallback
  complaints.sort((a, b) => {
    const dateA = new Date(a.created_at || a.submitted_at);
    const dateB = new Date(b.created_at || b.submitted_at);
    return dateB - dateA;
  });

  // Aplicar filtros locais
  if (localFilters.value.priority) {
    complaints = complaints.filter(
      (item) => item.priority === localFilters.value.priority
    );
  }

  if (localFilters.value.status) {
    complaints = complaints.filter((item) => item.status === localFilters.value.status);
  }

  if (localFilters.value.type) {
    complaints = complaints.filter(
      (item) => normalizeType(item.type) === localFilters.value.type
    );
  }

  if (localFilters.value.category) {
    complaints = complaints.filter(
      (item) => item.category === localFilters.value.category
    );
  }

  return complaints;
});

// CORREÇÃO: Função simplificada para normalizar tipos - usar diretamente os valores do enum
const normalizeType = (type) => {
  if (!type) return "unknown";

  // Converter para minúsculas para comparação
  const normalized = type.toString().toLowerCase().trim();

  // Mapeamento direto baseado no enum do banco de dados
  const typeMap = {
    suggestion: "suggestion",
    grievance: "grievance",
    complaint: "complaint",
    sugestão: "suggestion",
    sugestao: "suggestion",
    queixa: "grievance",
    reclamação: "complaint",
    reclamacao: "complaint",
  };

  return typeMap[normalized] || "unknown";
};

// Computed properties CORRIGIDAS para cada tipo
const suggestionsData = computed(() => {
  if (!props.allComplaints || !Array.isArray(props.allComplaints)) return [];
  const suggestions = props.allComplaints.filter(
    (item) => normalizeType(item.type) === "suggestion"
  );
  // Ordenar por data mais recente
  return suggestions.sort(
    (a, b) =>
      new Date(b.created_at || b.submitted_at) - new Date(a.created_at || a.submitted_at)
  );
});

const grievancesData = computed(() => {
  if (!props.allComplaints || !Array.isArray(props.allComplaints)) return [];
  const grievances = props.allComplaints.filter(
    (item) => normalizeType(item.type) === "grievance"
  );
  // Ordenar por data mais recente
  return grievances.sort(
    (a, b) =>
      new Date(b.created_at || b.submitted_at) - new Date(a.created_at || a.submitted_at)
  );
});

const complaintsData = computed(() => {
  if (!props.allComplaints || !Array.isArray(props.allComplaints)) return [];
  const complaints = props.allComplaints.filter(
    (item) => normalizeType(item.type) === "complaint"
  );
  // Ordenar por data mais recente
  return complaints.sort(
    (a, b) =>
      new Date(b.created_at || b.submitted_at) - new Date(a.created_at || a.submitted_at)
  );
});

const currentTabData = computed(() => {
  if (!props.allComplaints || !Array.isArray(props.allComplaints)) return [];

  let data = [];
  switch (activeTab.value) {
    case "suggestions":
      data = suggestionsData.value;
      break;
    case "grievances":
      data = grievancesData.value;
      break;
    case "complaints":
      data = complaintsData.value;
      break;
    case "all":
    default:
      data = [...props.allComplaints].sort(
        (a, b) =>
          new Date(b.created_at || b.submitted_at) -
          new Date(a.created_at || a.submitted_at)
      );
      break;
  }

  return data;
});

// Contadores CORRIGIDOS
const suggestionsCount = computed(() => suggestionsData.value.length);
const grievancesCount = computed(() => grievancesData.value.length);
const complaintsCount = computed(() => complaintsData.value.length);

// Watchers
watch(
  localFilters,
  (newFilters) => {
    emit("update:filters", newFilters);
  },
  { deep: true }
);

watch(showAllComplaints, (newValue) => {
  if (newValue && (!props.allComplaints || props.allComplaints.length === 0)) {
    reloadData();
  }
});

// Métodos
const toggleAllComplaints = () => {
  showAllComplaints.value = !showAllComplaints.value;
};

const reloadData = () => {
  loading.value = true;
  router.reload({
    preserveState: true,
    onFinish: () => {
      loading.value = false;
    },
  });
};

// CORREÇÃO: Função handleRowClick simplificada - apenas navega para a rota
const handleRowClick = (complaint) => {
  if (!complaint || !complaint.id) {
    return;
  }

  // Apenas navega para a rota sem emitir evento para popup
  router.get(route("complaints.grievance.show", { grievance: complaint.id }));
};

// Métodos auxiliares CORRIGIDOS
const getPriorityLabel = (priority) => {
  const labels = {
    high: "Alta",
    medium: "Média",
    low: "Baixa",
    urgent: "Urgente",
  };
  return labels[priority] || priority;
};

const getStatusLabel = (status) => {
  const labels = {
    submitted: "Submetida",
    under_review: "Em Análise",
    assigned: "Atribuída",
    in_progress: "Em Andamento",
    pending_approval: "Pendente Aprovação",
    resolved: "Resolvida",
    rejected: "Rejeitada",
  };
  return labels[status] || status;
};

const getTypeLabel = (type) => {
  const labels = {
    suggestion: "Sugestão",
    grievance: "Queixa",
    complaint: "Reclamação",
  };

  const normalized = normalizeType(type);
  return labels[normalized] || type || "Não definido";
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  return new Date(dateString).toLocaleDateString("pt-BR");
};

const getTypeBadgeClass = (type) => {
  const normalized = normalizeType(type);

  const badgeClasses = {
    suggestion: "bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300",
    grievance: "bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-300",
    complaint: "bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300",
    unknown: "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300",
  };

  return [
    "inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium",
    badgeClasses[normalized] || badgeClasses.unknown,
  ];
};

const getPriorityBadgeClass = (priority) => {
  return [
    "inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium",
    priority === "high" || priority === "urgent"
      ? "bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300"
      : priority === "medium"
      ? "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300"
      : "bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300",
  ];
};

const getStatusBadgeClass = (status) => {
  return [
    "inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium",
    status === "submitted" || status === "under_review"
      ? "bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300"
      : status === "assigned" || status === "in_progress"
      ? "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300"
      : status === "resolved"
      ? "bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300"
      : status === "rejected"
      ? "bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300"
      : "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300",
  ];
};

const getExportLabel = () => {
  switch (activeTab.value) {
    case "suggestions":
      return "Sugestões";
    case "grievances":
      return "Queixas";
    case "complaints":
      return "Reclamações";
    default:
      return "Todos os Dados";
  }
};

const handleSelectComplaint = (complaint) => {
  selectedComplaint.value = complaint;
  emit("select-complaint", complaint);
};

const handleBulkAssign = async () => {
  try {
    await router.post(
      route("complaints.bulk-assign"),
      {},
      {
        preserveScroll: true,
        preserveState: true,
      }
    );
  } catch (error) {}
};

const handleExport = () => {
  const queryParams = new URLSearchParams({
    ...localFilters.value,
    tab: activeTab.value,
  }).toString();
  window.open(route("complaints.export") + "?" + queryParams, "_blank");
};
</script>

<style scoped>
/* Container de rolagem para lista de reclamações */
.complaints-scroll-container {
  max-height: 60vh;
  overflow-y: auto;
  overflow-x: hidden;
}

/* Container de rolagem para tabela */
.table-scroll-container {
  max-height: 50vh;
  overflow-y: auto;
  overflow-x: auto;
}

/* Scrollbar personalizada para ambos os containers */
.complaints-scroll-container::-webkit-scrollbar,
.table-scroll-container::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.complaints-scroll-container::-webkit-scrollbar-track,
.table-scroll-container::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.complaints-scroll-container::-webkit-scrollbar-thumb,
.table-scroll-container::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.complaints-scroll-container::-webkit-scrollbar-thumb:hover,
.table-scroll-container::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Dark mode para scrollbar */
.dark .complaints-scroll-container::-webkit-scrollbar-track,
.dark .table-scroll-container::-webkit-scrollbar-track {
  background: #374151;
}

.dark .complaints-scroll-container::-webkit-scrollbar-thumb,
.dark .table-scroll-container::-webkit-scrollbar-thumb {
  background: #6b7280;
}

.dark .complaints-scroll-container::-webkit-scrollbar-thumb:hover,
.dark .table-scroll-container::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

/* Responsividade para diferentes tamanhos de tela */
@media (min-width: 640px) {
  .complaints-scroll-container {
    max-height: 65vh;
  }

  .table-scroll-container {
    max-height: 55vh;
  }
}

@media (min-width: 768px) {
  .complaints-scroll-container {
    max-height: 70vh;
  }

  .table-scroll-container {
    max-height: 60vh;
  }
}

@media (min-width: 1024px) {
  .complaints-scroll-container {
    max-height: 500px;
  }

  .table-scroll-container {
    max-height: 500px;
  }
}

@media (min-width: 1280px) {
  .complaints-scroll-container {
    max-height: 600px;
  }

  .table-scroll-container {
    max-height: 550px;
  }
}
</style>
