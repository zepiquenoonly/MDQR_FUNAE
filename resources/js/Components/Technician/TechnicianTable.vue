<template>
  <div
    class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6"
  >
    <!-- Header com título e estatísticas -->
    <div
      class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4 sm:mb-6"
    >
      <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary">
          {{ headerTitle }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
          Total: {{ totalTechnicians }}
          {{ currentUserRole === "director" ? "membros" : "técnicos" }}
          <span v-if="hasActiveFilters" class="text-brand font-medium">
            ({{ currentData.length }} filtrados)
          </span>
        </p>
      </div>

      <!-- Botão para limpar filtros -->
      <button
        v-if="hasActiveFilters"
        @click="clearAllFilters"
        class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-gray-700 dark:text-gray-300 text-xs font-medium hover:bg-gray-50 dark:hover:bg-dark-accent transition-all duration-200 flex items-center gap-1"
      >
        <XMarkIcon class="w-3 h-3" />
        Limpar Filtros
      </button>

      <button
        @click="exportToPdf"
        :disabled="currentData.length === 0 || isLoading"
        class="px-3 py-1.5 bg-orange-500 border dark:border-gray-600 rounded text-white dark:text-gray-300 text-xs font-medium hover:bg-orange-600 dark:hover:bg-dark-accent transition-all duration-200 flex items-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <svg
          v-if="isLoading"
          class="w-3 h-3 animate-spin"
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
        <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
          />
        </svg>
        {{ isLoading ? "A exportar..." : "Exportar PDF" }}
      </button>
    </div>

    <!-- Indicador de filtros ativos -->
    <div v-if="hasActiveFilters" class="mb-4">
      <div class="flex flex-wrap gap-2">
        <span class="text-xs text-gray-600 dark:text-gray-400">Filtros ativos:</span>

        <span
          v-if="searchQuery"
          class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-xs rounded-full"
        >
          <span class="font-medium">Pesquisa:</span> {{ searchQuery }}
          <button
            @click="
              searchQuery = '';
              applyFilters();
            "
            class="ml-1 hover:text-blue-600"
          >
            <XMarkIcon class="w-3 h-3" />
          </button>
        </span>

        <span
          v-if="statusFilter"
          class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 text-xs rounded-full"
        >
          <span class="font-medium">Status:</span>
          {{ statusFilter === "active" ? "Activo" : "Inactivo" }}
          <button
            @click="
              statusFilter = '';
              applyFilters();
            "
            class="ml-1 hover:text-green-600"
          >
            <XMarkIcon class="w-3 h-3" />
          </button>
        </span>

        <span
          v-if="roleFilter && currentUserRole === 'director'"
          class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 dark:purple:bg-purple-900/30 text-purple-800 dark:text-purple-300 text-xs rounded-full"
        >
          <span class="font-medium">Tipo:</span>
          {{ roleFilter === "technician" ? "Técnico" : "Gestor" }}
          <button
            @click="
              roleFilter = '';
              applyFilters();
            "
            class="ml-1 hover:text-purple-600"
          >
            <XMarkIcon class="w-3 h-3" />
          </button>
        </span>
      </div>
    </div>

    <!-- Barra de pesquisa e filtros -->
    <div class="flex flex-col sm:flex-row gap-3 mb-4 sm:mb-6">
      <!-- Campo de pesquisa (ajustado para ser menos largo) -->
      <div class="sm:w-64">
        <!-- Alterado de flex-1 para sm:w-64 -->
        <div class="relative">
          <MagnifyingGlassIcon
            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"
          />
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Pesquisar por nome, email ou username..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary text-sm focus:ring-2 focus:ring-brand focus:border-transparent transition-all"
            @input="handleSearch"
          />
        </div>
      </div>

      <!-- Filtro por Status -->
      <div class="w-full sm:w-48">
        <select
          v-model="statusFilter"
          @change="applyFilters"
          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary text-sm focus:ring-2 focus:ring-brand focus:border-transparent transition-all"
        >
          <option value="">Todos os status</option>
          <option value="active">Activo</option>
          <option value="inactive">Inactivo</option>
        </select>
      </div>

      <!-- Filtro por Role (apenas para director) -->
      <div class="w-full sm:w-48" v-if="currentUserRole === 'director'">
        <select
          v-model="roleFilter"
          @change="applyFilters"
          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary text-sm focus:ring-2 focus:ring-brand focus:border-transparent transition-all"
        >
          <option value="">Todos os cargos</option>
          <option value="technician">Técnicos</option>
          <option value="manager">Gestores</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div
        class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand mx-auto"
      ></div>
      <p class="text-gray-500 dark:text-gray-400 mt-2">
        A carregar {{ currentUserRole === "director" ? "membros" : "técnicos" }}...
      </p>
    </div>

    <!-- Conteúdo principal -->
    <div v-else>
      <!-- Container com rolagem interna para tabela -->
      <div class="table-scroll-container">
        <div class="overflow-x-auto -mx-4 sm:mx-0">
          <div class="min-w-full inline-block align-middle">
            <table
              class="min-w-full divide-y divide-gray-300 dark:divide-gray-600 text-xs sm:text-sm"
            >
              <thead class="table-header-sticky bg-gray-50 dark:bg-dark-accent">
                <tr>
                  <th
                    scope="col"
                    class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    #
                  </th>
                  <th
                    scope="col"
                    class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    {{ currentUserRole === "director" ? "Membro" : "Técnico" }}
                  </th>
                  <th
                    scope="col"
                    class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Contacto
                  </th>
                  <th
                    scope="col"
                    class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Cargo
                  </th>
                  <th
                    scope="col"
                    class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Status
                  </th>
                  <th
                    scope="col"
                    class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Estatísticas
                  </th>
                  <th
                    scope="col"
                    class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Performance
                  </th>
                  <th
                    scope="col"
                    class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Acções
                  </th>
                </tr>
              </thead>
              <tbody
                class="bg-white dark:bg-dark-secondary divide-y divide-gray-200 dark:divide-gray-700"
              >
                <tr
                  v-for="(technician, index) in currentData"
                  :key="technician.id"
                  class="hover:bg-gray-50 dark:hover:bg-dark-accent transition-colors"
                >
                  <td
                    class="px-3 py-2 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400"
                  >
                    {{ getItemNumber(index) }}
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap">
                    <div class="flex items-center">
                      <div
                        class="flex-shrink-0 h-10 w-10 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center"
                      >
                        <span class="text-gray-600 dark:text-gray-300 font-medium">
                          {{ getInitials(technician.name) }}
                        </span>
                      </div>
                      <div class="ml-3">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                          {{ technician.name }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                          @{{ technician.username }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                      {{ technician.email }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      {{ technician.phone || "N/A" }}
                    </div>
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap">
                    <span
                      :class="roleBadgeClass(technician.role || technician.role_label)"
                    >
                      {{ getRoleLabel(technician.role || technician.role_label) }}
                    </span>
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap">
                    <span :class="statusClass(technician.is_available)">
                      {{ technician.is_available ? "Activo" : "Inactivo" }}
                    </span>
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap">
                    <div v-if="technician.is_technician" class="grid grid-cols-2 gap-2">
                      <div class="text-center">
                        <div
                          class="text-sm font-semibold text-blue-600 dark:text-blue-400"
                        >
                          {{ technician.tasks_assigned || 0 }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                          Atribuídas
                        </div>
                      </div>
                      <div class="text-center">
                        <div
                          class="text-sm font-semibold text-green-600 dark:text-green-400"
                        >
                          {{ technician.tasks_completed || 0 }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                          Concluídas
                        </div>
                      </div>
                    </div>
                    <div v-else class="text-center">
                      <span class="text-xs text-gray-500 dark:text-gray-400 italic">
                        Não aplicável
                      </span>
                    </div>
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap">
                    <div v-if="technician.is_technician" class="flex items-center">
                      <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div
                          :class="performanceClass(technician.performance_rate)"
                          :style="{
                            width: `${Math.min(technician.performance_rate || 0, 100)}%`,
                          }"
                          class="h-2 rounded-full transition-all duration-300"
                        ></div>
                      </div>
                      <span
                        class="ml-2 text-xs font-medium text-gray-700 dark:text-gray-300"
                      >
                        {{ technician.performance_rate || 0 }}%
                      </span>
                    </div>
                    <div v-else class="text-center">
                      <span class="text-xs text-gray-500 dark:text-gray-400 italic">
                        -
                      </span>
                    </div>
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap text-sm font-medium">
                    <Link
                      :href="getDetailsLink(technician.id)"
                      class="text-brand hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300 text-xs px-3 py-1.5 bg-brand/10 hover:bg-brand/20 rounded transition-colors duration-200 inline-flex items-center gap-1"
                    >
                      <EyeIcon class="w-3 h-3" />
                      Detalhes
                    </Link>
                  </td>
                </tr>

                <!-- Linhas vazias para completar 10 por página -->
                <template v-if="currentData.length < 10 && pagination">
                  <tr v-for="n in 10 - currentData.length" :key="`empty-${n}`">
                    <td
                      colspan="8"
                      class="px-3 py-4 text-center text-gray-500 dark:text-gray-400 italic"
                    >
                      &nbsp;
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>

            <!-- Empty State para tabela -->
            <div v-if="currentData.length === 0" class="text-center py-8">
              <UserGroupIcon
                class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4"
              />
              <p class="text-gray-500 dark:text-gray-400">
                Nenhum
                {{ currentUserRole === "director" ? "membro" : "técnico" }} encontrado
              </p>
              <p v-if="hasActiveFilters" class="text-sm text-gray-400 mt-2">
                Tente alterar os filtros ou a pesquisa
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Paginação -->
      <div
        v-if="pagination"
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-4 border-t border-gray-200 dark:border-gray-700"
      >
        <p class="text-xs text-gray-700 dark:text-gray-300">
          Mostrando {{ technicians.from || 1 }} a
          {{ technicians.to || currentData.length }} de
          {{ technicians.total || totalTechnicians }}
          {{ currentUserRole === "director" ? "membros" : "técnicos" }}
        </p>
        <div class="flex gap-2">
          <Link
            v-if="technicians.prev_page_url"
            :href="technicians.prev_page_url"
            class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-gray-700 dark:text-gray-300 text-xs font-medium hover:bg-gray-50 dark:hover:bg-dark-accent transition-all duration-200"
            preserve-state
          >
            Anterior
          </Link>
          <Link
            v-if="technicians.next_page_url"
            :href="technicians.next_page_url"
            class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-gray-700 dark:text-gray-300 text-xs font-medium hover:bg-gray-50 dark:hover:bg-dark-accent transition-all duration-200"
            preserve-state
          >
            Próxima
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from "@inertiajs/vue3";
import {
  EyeIcon,
  UserGroupIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
} from "@heroicons/vue/24/outline";
import { ref, computed, watch, onMounted } from "vue";
import { debounce } from "lodash";

const props = defineProps({
  technicians: {
    type: [Object, Array],
    default: () => ({}),
  },
  counts: {
    type: Object,
    default: () => ({
      all: 0,
      technicians: 0,
      managers: 0,
      active: 0,
      inactive: 0,
    }),
  },
  loading: {
    type: Boolean,
    default: false,
  },
  canEdit: {
    type: Boolean,
    default: false,
  },
  currentUserRole: {
    type: String,
    default: "manager",
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  perPage: {
    type: Number,
    default: 10, // Alterado de 15 para 10
  },
});

// Estado reativo
const searchQuery = ref("");
const statusFilter = ref("");
const roleFilter = ref("");
const loading = ref(false);
const isLoading = ref(false);

// Inicializar filtros dos props
onMounted(() => {
  console.log("Filtros recebidos no componente:", props.filters);

  // Inicializar filtros da URL
  if (props.filters) {
    searchQuery.value = props.filters.search || "";
    statusFilter.value = props.filters.status || "";
    roleFilter.value = props.filters.role || "";
  }

  console.log("Filtros inicializados:", {
    search: searchQuery.value,
    status: statusFilter.value,
    role: roleFilter.value,
  });
});

// Computed properties
const headerTitle = computed(() => {
  if (props.currentUserRole === "director") return "Ver Funcionários";
  return "Ver Técnicos";
});

// Dados atuais
const currentData = computed(() => {
  // Se technicians for um objeto paginado, pega os dados
  if (props.technicians && props.technicians.data) {
    return props.technicians.data;
  }
  // Se for um array simples
  if (Array.isArray(props.technicians)) {
    return props.technicians;
  }
  // Caso contrário, retorna array vazio
  return [];
});

const totalTechnicians = computed(() => {
  if (props.technicians && props.technicians.total !== undefined) {
    return props.technicians.total;
  }
  if (Array.isArray(props.technicians)) {
    return props.technicians.length;
  }
  return 0;
});

const pagination = computed(() => {
  return (
    props.technicians &&
    typeof props.technicians === "object" &&
    (props.technicians.current_page !== undefined ||
      props.technicians.total !== undefined)
  );
});

// Contar filtros ativos
const hasActiveFilters = computed(() => {
  return (
    searchQuery.value.trim() !== "" ||
    statusFilter.value !== "" ||
    roleFilter.value !== ""
  );
});

// Número do item considerando paginação
const getItemNumber = (index) => {
  if (!pagination.value) return index + 1;

  const currentPage = props.technicians.current_page || 1;
  const perPage = props.perPage || 10;
  return (currentPage - 1) * perPage + index + 1;
};

// Debounced search function
const handleSearch = debounce(() => {
  applyFilters();
}, 500);

const applyFilters = () => {
  console.log("Estado atual dos filtros:", {
    search: searchQuery.value,
    status: statusFilter.value,
    role: roleFilter.value,
  });

  // Construir URL com filtros combinados
  const filters = {};

  // Adicionar apenas filtros que não estão vazios
  if (searchQuery.value.trim() !== "") {
    filters.search = searchQuery.value.trim();
  }

  if (statusFilter.value !== "") {
    filters.status = statusFilter.value;
  }

  if (roleFilter.value !== "") {
    filters.role = roleFilter.value;
  }

  // Adicionar parâmetro per_page para garantir 10 itens por página
  filters.per_page = props.perPage || 10;

  console.log("Filtros a serem enviados:", filters);
  console.log("URL atual:", window.location.href);

  // Navegar para a URL com filtros
  const currentPath = window.location.pathname;

  // Usar router do Inertia para navegação suave
  router.get(currentPath, filters, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    onSuccess: (page) => {
      console.log("Requisição bem-sucedida:", page);
    },
    onError: (errors) => {
      console.error("Erro na requisição:", errors);
    },
  });
};

const clearAllFilters = () => {
  console.log("Limpando filtros");

  searchQuery.value = "";
  statusFilter.value = "";
  roleFilter.value = "";

  // Navegar para URL sem filtros
  const currentPath = window.location.pathname;
  router.get(
    currentPath,
    { per_page: props.perPage || 10 }, // Manter 10 itens por página
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  );
};

// Helper methods
const getInitials = (name) => {
  if (!name) return "NA";
  return name
    .split(" ")
    .map((word) => word[0])
    .join("")
    .toUpperCase()
    .slice(0, 2);
};

const statusClass = (isAvailable) => {
  return isAvailable
    ? "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300"
    : "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300";
};

const performanceClass = (rate) => {
  const performance = rate || 0;
  if (performance >= 80) return "bg-green-500";
  if (performance >= 60) return "bg-yellow-500";
  return "bg-red-500";
};

const roleBadgeClass = (role) => {
  const baseClass =
    "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium";

  if (role === "technician" || role === "Técnico") {
    return `${baseClass} bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300`;
  } else if (role === "manager" || role === "Gestor") {
    return `${baseClass} bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-300`;
  } else if (role === "director" || role === "Director") {
    return `${baseClass} bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-300`;
  }

  return `${baseClass} bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-300`;
};

const getRoleLabel = (role) => {
  const labels = {
    technician: "Técnico",
    manager: "Gestor",
    director: "Director",
    admin: "Administrador",
    pca: "PCA",
    utente: "Utente",
    técnico: "Técnico",
    gestor: "Gestor",
  };

  return labels[role] || role;
};

const getDetailsLink = (userId) => {
  if (props.currentUserRole === "director") {
    return `/director/employees/${userId}`;
  } else if (props.currentUserRole === "manager") {
    return `/gestor/technicians/${userId}`;
  }
  return `#`;
};

// Watch para atualizar quando os props mudarem
watch(
  () => props.filters,
  (newFilters) => {
    console.log("Filtros atualizados via props:", newFilters);

    if (newFilters) {
      searchQuery.value = newFilters.search || "";
      statusFilter.value = newFilters.status || "";
      roleFilter.value = newFilters.role || "";
    }

    console.log("Estado atualizado:", {
      search: searchQuery.value,
      status: statusFilter.value,
      role: roleFilter.value,
    });
  },
  { immediate: true, deep: true }
);

const exportToPdf = async () => {
  // CORREÇÃO: Usar currentData.value em vez de props.currentData
  if (currentData.value.length === 0 || isLoading.value) return;

  isLoading.value = true;

  try {
    // Construir URL usando os valores reativos locais, não props
    const params = new URLSearchParams();

    // Usar searchQuery.value em vez de props.searchQuery
    if (searchQuery.value.trim() !== "") {
      params.append("search", searchQuery.value.trim());
    }

    // Usar statusFilter.value em vez de props.statusFilter
    if (statusFilter.value !== "") {
      params.append("status", statusFilter.value);
    }

    // Usar roleFilter.value em vez de props.roleFilter
    if (roleFilter.value !== "") {
      params.append("role", roleFilter.value);
    }

    // Usar props.filters?.province
    if (props.filters?.province) {
      params.append("province", props.filters.province);
    }

    let baseUrl;
    if (props.currentUserRole === "director") {
      baseUrl = "/director/employees/export/pdf";
    } else if (props.currentUserRole === "manager") {
      baseUrl = "/gestor/technicians/export/pdf";
    } else {
      return;
    }

    const queryString = params.toString();
    const url = queryString ? `${baseUrl}?${queryString}` : baseUrl;

    // Fazer requisição fetch
    const response = await fetch(url);

    if (!response.ok) {
      throw new Error("Erro ao exportar PDF");
    }

    // Obter blob do PDF
    const blob = await response.blob();

    // Criar URL temporária para download
    const downloadUrl = window.URL.createObjectURL(blob);

    // Extrair nome do arquivo do header ou criar um
    const contentDisposition = response.headers.get("content-disposition");
    let filename = "relatorio-funcionarios.pdf";

    if (contentDisposition && contentDisposition.indexOf("filename=") !== -1) {
      const matches = contentDisposition.match(/filename="(.+)"/);
      if (matches && matches[1]) {
        filename = matches[1];
      }
    }

    // Criar link temporário para download
    const link = document.createElement("a");
    link.href = downloadUrl;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Liberar URL
    window.URL.revokeObjectURL(downloadUrl);
  } catch (error) {
    console.error("Erro ao exportar PDF:", error);
    // Você pode adicionar uma notificação aqui
    alert("Erro ao exportar PDF. Tente novamente.");
  } finally {
    isLoading.value = false;
  }
};

// Watch para detectar mudanças nos filtros
watch([searchQuery, statusFilter, roleFilter], () => {
  // Se qualquer filtro mudar, mostrar no console para debug
  console.log("Filtros alterados no estado local:", {
    search: searchQuery.value,
    status: statusFilter.value,
    role: roleFilter.value,
    hasActiveFilters: hasActiveFilters.value,
  });
});
</script>

<style scoped>
.table-scroll-container {
  max-height: 500px;
  overflow-y: auto;
}

.table-header-sticky {
  position: sticky;
  top: 0;
  z-index: 10;
}

@media (max-width: 640px) {
  .table-scroll-container {
    max-height: 400px;
  }

  /* Em telas pequenas, a pesquisa ocupa toda a largura */
  .sm\:w-64 {
    width: 100%;
  }
}
</style>
