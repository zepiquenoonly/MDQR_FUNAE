<template>
  <div
    class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6"
  >
    <!-- Header compacto -->
    <div
      class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4 sm:mb-6"
    >
      <h3 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary">
        {{ headerTitle }}
      </h3>
      <div class="flex gap-2 self-end sm:self-auto">
        <button
          @click="toggleAllComplaints"
          class="text-brand hover:text-orange-600 text-sm font-medium flex items-center gap-1.5 group relative"
          :disabled="loading"
        >
          <span>{{ showAllComplaints ? "Ver Resumo" : "Ver Todos" }}</span>

          <!-- Badge de contagem (só aparece quando houver dados não vistos) -->
          <span
            v-if="unseenCount > 0 && !showAllComplaints"
            class="inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-medium leading-none rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 transform group-hover:scale-110 transition-transform duration-200"
          >
            {{ unseenCount > 99 ? "99+" : unseenCount }}
          </span>

          <!-- Ícone dinâmico -->
          <svg
            :class="[
              'w-4 h-4 transition-transform duration-300',
              showAllComplaints ? 'rotate-180' : '',
            ]"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 9l-7 7-7-7"
            />
          </svg>

          <!-- Tooltip para novos dados -->
          <span
            v-if="hasNewData && !showAllComplaints"
            class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"
          >
            {{ unseenCount }} nova{{ unseenCount !== 1 ? "s" : "" }} submissão{{
              unseenCount !== 1 ? "es" : ""
            }}
          </span>
        </button>
      </div>
    </div>

    <div v-if="showAllComplaints" class="mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <!-- Filtro de Categoria -->
        <div>
          <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
            Categoria
          </label>
          <select
            v-model="localFilters.category"
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-brand focus:border-transparent dark:bg-dark-secondary dark:text-dark-text-primary"
          >
            <option value="">Todas as Categorias</option>
            <option value="Ambiental">Ambiental</option>
            <option value="Social">Social</option>
            <option value="Económico">Económico</option>
            <option value="Segurança">Segurança</option>
            <option value="Saúde">Saúde</option>
            <option value="Educação">Educação</option>
          </select>
        </div>

        <!-- Filtro de Tipo -->
        <div>
          <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
            Tipo
          </label>
          <select
            v-model="localFilters.type"
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-brand focus:border-transparent dark:bg-dark-secondary dark:text-dark-text-primary"
          >
            <option value="">Todos os Tipos</option>
            <option value="suggestion">Sugestão</option>
            <option value="complaint">Reclamação</option>
            <option value="grievance">Queixa</option>
          </select>
        </div>

        <!-- Filtro de Prioridade -->
        <div>
          <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
            Prioridade
          </label>
          <select
            v-model="localFilters.priority"
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-brand focus:border-transparent dark:bg-dark-secondary dark:text-dark-text-primary"
          >
            <option value="">Todas as Prioridades</option>
            <option value="low">Baixa</option>
            <option value="medium">Média</option>
            <option value="high">Alta</option>
          </select>
        </div>

        <!-- Filtro de Estado -->
        <div>
          <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
            Estado
          </label>
          <select
            v-model="localFilters.status"
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-brand focus:border-transparent dark:bg-dark-secondary dark:text-dark-text-primary"
          >
            <option value="">Todos os Estados</option>
            <option value="submitted">Submetida</option>
            <option value="in_progress">Em Análise</option>
            <option value="assigned">Atribuída</option>
            <option value="pending">Em Progresso</option>
            <option value="pending_approval">Pendente de Aprovação</option>
            <option value="resolved">Aprovada</option>
            <option value="rejected">Rejeitada</option>
          </select>
        </div>
      </div>

      <!-- Botões de ação dos filtros -->
      <div class="flex justify-between items-center">
        <div class="text-sm text-gray-500 dark:text-gray-400">
          <span v-if="activeFiltersCount > 0">
            {{ activeFiltersCount }} filtro{{
              activeFiltersCount !== 1 ? "s" : ""
            }}
            ativo{{ activeFiltersCount !== 1 ? "s" : "" }}
          </span>
        </div>
        <div class="flex gap-2">
          <button
            @click="resetFilters"
            class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-gray-700 dark:text-gray-300 text-xs font-medium hover:bg-gray-50 dark:hover:bg-dark-accent transition-all duration-200"
            :disabled="loading || !hasActiveFilters"
          >
            Limpar Filtros
          </button>
        </div>
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
        <div class="space-y-4">
          <div v-for="complaint in allComplaints.slice(0, 3)" :key="complaint.id">
            <ComplaintRow
              :complaint="complaint"
              :role="role"
              :selected="selectedComplaintId === complaint.id"
            />
          </div>
          <div v-if="allComplaints.length === 0" class="text-center py-8">
            <p class="text-gray-500 dark:text-gray-400">Nenhuma submissão encontrada</p>
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
            {{ fullViewTitle }}
            <span class="text-sm font-normal text-gray-500">
              (Total: {{ filteredComplaints.length }})
            </span>
          </h3>
        </div>

        <!-- Tabs DINÂMICAS baseadas no role -->
        <div class="border-b border-gray-200 dark:border-gray-700">
          <nav class="-mb-px flex space-x-4 overflow-x-auto">
            <!-- Tabs comuns para todos os roles -->
            <button
              @click="changeTab('suggestions')"
              :class="[
                activeTab === 'suggestions'
                  ? 'border-brand text-brand dark:text-orange-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
                loading ? 'opacity-50 cursor-not-allowed' : '',
              ]"
              :disabled="loading"
            >
              Sugestões
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ getTabCount("suggestions") }}
              </span>
            </button>

            <button
              @click="changeTab('grievances')"
              :class="[
                activeTab === 'grievances'
                  ? 'border-brand text-brand dark:text-orange-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
                loading ? 'opacity-50 cursor-not-allowed' : '',
              ]"
              :disabled="loading"
            >
              Queixas
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ getTabCount("grievances") }}
              </span>
            </button>

            <button
              @click="changeTab('complaints')"
              :class="[
                activeTab === 'complaints'
                  ? 'border-brand text-brand dark:text-orange-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
                loading ? 'opacity-50 cursor-not-allowed' : '',
              ]"
              :disabled="loading"
            >
              Reclamações
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ getTabCount("complaints") }}
              </span>
            </button>

            <!-- Tab específica para Director: Solicitações do Gestor -->
            <button
              v-if="isDirector"
              @click="changeTab('manager_requests')"
              :class="[
                activeTab === 'manager_requests'
                  ? 'border-purple-500 text-purple-600 dark:text-purple-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
                loading ? 'opacity-50 cursor-not-allowed' : '',
              ]"
              :disabled="loading"
            >
              Solicitações do Gestor
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ getTabCount("manager_requests") }}
              </span>
            </button>

            <!-- Tab específica para Manager: Intervenções do Director -->
            <button
              v-if="isManager"
              @click="changeTab('director_interventions')"
              :class="[
                activeTab === 'director_interventions'
                  ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
                loading ? 'opacity-50 cursor-not-allowed' : '',
              ]"
              :disabled="loading"
            >
              Intervenções do Director
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ getTabCount("director_interventions") }}
              </span>
            </button>

            <!-- Tab "Todos" -->
            <button
              @click="changeTab('all')"
              :class="[
                activeTab === 'all'
                  ? 'border-brand text-brand dark:text-orange-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
                loading ? 'opacity-50 cursor-not-allowed' : '',
              ]"
              :disabled="loading"
            >
              Todos
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ filteredComplaints.length }}
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
                      Categoria
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
                      Intervenção
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
                    :class="[
                      'hover:bg-gray-50 dark:hover:bg-dark-accent',
                      item.has_director_intervention ? 'has-director-response' : '',
                    ]"
                  >
                    <td
                      class="px-3 py-2 whitespace-nowrap font-medium text-gray-900 dark:text-dark-text-primary"
                    >
                      {{ item.reference_number }}
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
                      <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400"
                      >
                        {{ item.category || item.department || "N/A" }}
                      </span>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                      <span :class="getPriorityBadgeClass(item.priority)">
                        {{ getPriorityLabel(item.priority) }}
                      </span>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                      <div class="flex flex-col gap-1">
                        <span :class="getStatusBadgeClass(item.status)">
                          {{ getStatusLabel(item.status) }}
                        </span>
                      </div>
                    </td>

                    <td
                      class="px-3 py-2 whitespace-nowrap text-gray-500 dark:text-gray-400"
                    >
                      {{ formatDate(item.created_at) }}
                    </td>

                    <!-- Coluna de Intervenção -->
                    <td class="px-3 py-2 whitespace-nowrap">
                      <div
                        v-if="
                          item.has_director_intervention ||
                          item.director_interventions?.length > 0
                        "
                        class="flex flex-col gap-1"
                      >
                        <span
                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-300"
                        >
                          <svg
                            class="w-3 h-3 mr-1"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                          >
                            <path
                              fill-rule="evenodd"
                              d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                              clip-rule="evenodd"
                            />
                          </svg>
                          {{ getInterventionType(item) }}
                        </span>
                        <span
                          v-if="item.director_comments_count > 0"
                          class="text-xs text-gray-500"
                        >
                          {{ item.director_comments_count }} comentário(s)
                        </span>
                      </div>
                      <span v-else class="text-gray-400 text-xs">Sem intervenção</span>
                    </td>

                    <td class="px-3 py-2 whitespace-nowrap font-medium">
                      <button
                        @click="handleRowClick(item)"
                        class="text-brand hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300 text-xs px-3 py-1.5 bg-brand/10 hover:bg-brand/20 rounded transition-colors duration-200"
                        :disabled="loading"
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
                <p
                  v-if="activeTab === 'director_interventions'"
                  class="text-sm text-gray-400 mt-2"
                >
                  Nenhuma intervenção do director encontrada
                </p>
                <p
                  v-if="activeTab === 'director_interventions' && isManager"
                  class="text-sm text-gray-500 mt-2"
                >
                  As submissões reencaminhadas ao Director aparecerão aqui
                </p>
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
            {{ filteredComplaints.length }} resultados
          </p>
          <div class="flex gap-2 self-end">
            <button
              @click="handleExport"
              class="px-3 py-1.5 bg-brand text-white rounded text-xs font-medium hover:bg-orange-600 transition-all duration-200"
              :disabled="loading"
            >
              Exportar {{ getExportLabel() }}
            </button>
            <button
              @click="handleBulkAssign"
              class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-gray-700 dark:text-gray-300 text-xs font-medium hover:bg-gray-50 dark:hover:bg-dark-accent transition-all duration-200"
              v-if="isManager"
              :disabled="loading"
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
import { ref, computed, watch, onMounted } from "vue";
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
  role: {
    type: String,
    default: "manager",
  },
  counts: {
    type: Object,
    default: () => ({
      suggestions: 0,
      grievances: 0,
      complaints: 0,
      director_interventions: 0,
      manager_requests: 0,
      total: 0,
    }),
  },
  debug_info: {
    type: Object,
    default: () => ({}),
  },
});

// Debug mode
const debugMode = ref(true);

// Estado
const showAllComplaints = ref(false);
const activeTab = ref("director_interventions");
const selectedComplaintId = ref(null);
const loading = ref(false);
const unseenCount = ref(0);
const hasNewData = ref(false);

// Filtros locais
const localFilters = ref({
  category: "",
  type: "",
  priority: "",
  status: "",
});

// Computed properties
const isDirector = computed(() => props.role?.toLowerCase() === "director");
const isManager = computed(() => props.role?.toLowerCase() === "manager");

const headerTitle = computed(() => {
  if (isDirector.value) return "Submissões do Departamento";
  if (isManager.value) return "Submissões Atribuídas";
  return "Minhas Submissões";
});

const fullViewTitle = computed(() => {
  if (isDirector.value) return "Todas as Submissões";
  if (isManager.value) return "Submissões Atribuídas";
  return "Minhas Submissões";
});

// Filtro aplicado às reclamações
const filteredComplaints = computed(() => {
  if (!props.allComplaints || props.allComplaints.length === 0) return [];

  let filtered = [...props.allComplaints];

  // Aplicar filtros locais
  if (localFilters.value.category) {
    filtered = filtered.filter(
      (item) =>
        item.category === localFilters.value.category ||
        item.department === localFilters.value.category
    );
  }

  if (localFilters.value.type) {
    filtered = filtered.filter((item) => {
      const itemType = item.type?.toLowerCase() || "";
      const filterType = localFilters.value.type.toLowerCase();

      if (filterType === "suggestion") {
        return itemType.includes("suggestion") || itemType.includes("sugest");
      } else if (filterType === "complaint") {
        return itemType.includes("complaint") || itemType.includes("reclam");
      } else if (filterType === "grievance") {
        return itemType.includes("grievance") || itemType.includes("queixa");
      }
      return true;
    });
  }

  if (localFilters.value.priority) {
    filtered = filtered.filter((item) => item.priority === localFilters.value.priority);
  }

  if (localFilters.value.status) {
    filtered = filtered.filter((item) => item.status === localFilters.value.status);
  }

  return filtered;
});

// Contar filtros ativos
const activeFiltersCount = computed(() => {
  return Object.values(localFilters.value).filter((value) => value !== "").length;
});

const hasActiveFilters = computed(() => {
  return activeFiltersCount.value > 0;
});

// Método para aplicar filtros
const applyFilters = () => {
  // Aqui você pode adicionar lógica para recarregar dados com os filtros
  // Por enquanto, apenas atualiza a lista localmente
  console.log("Filtros aplicados:", localFilters.value);
};

// Método para resetar filtros
const resetFilters = () => {
  localFilters.value = {
    category: "",
    type: "",
    priority: "",
    status: "",
  };
  applyFilters();
};

// Contadores - usar dados filtrados
const getTabCount = (tab) => {
  const data = getTabData(tab);
  return data.length;
};

const getTabData = (tab) => {
  switch (tab) {
    case "suggestions":
      return filteredComplaints.value.filter(
        (item) =>
          item.type?.toLowerCase().includes("suggestion") ||
          item.type?.toLowerCase().includes("sugest")
      );
    case "grievances":
      return filteredComplaints.value.filter(
        (item) =>
          item.type?.toLowerCase().includes("grievance") ||
          item.type?.toLowerCase().includes("queixa")
      );
    case "complaints":
      return filteredComplaints.value.filter(
        (item) =>
          item.type?.toLowerCase().includes("complaint") ||
          item.type?.toLowerCase().includes("reclam")
      );
    case "manager_requests":
      return filteredComplaints.value.filter(
        (item) =>
          item.escalated ||
          item.metadata?.is_escalated_to_director ||
          item.status === "escalated"
      );
    case "director_interventions":
      return directorInterventionsData.value.filter((item) =>
        filteredComplaints.value.some((f) => f.id === item.id)
      );
    default:
      return filteredComplaints.value;
  }
};

// Dados para a aba de intervenções do director
const directorInterventionsData = computed(() => {
  if (!props.allComplaints || !Array.isArray(props.allComplaints)) return [];

  // Filtrar reclamações que têm intervenção do director
  return props.allComplaints.filter((item) => {
    // Verificar propriedades diretas
    if (item.has_director_intervention === true) {
      return true;
    }

    // Verificar director_updates
    if (item.director_updates && item.director_updates.length > 0) {
      return true;
    }

    // Verificar director_comments_count
    if (item.director_comments_count > 0) {
      return true;
    }

    // Verificar director_validation
    if (item.director_validation) {
      return true;
    }

    // Verificar metadata
    if (item.metadata && item.metadata.director_validation) {
      return true;
    }

    // Verificar se foi escalado
    if (item.escalated === true || item.is_escalated_to_director === true) {
      return true;
    }

    // Verificar updates no array (se existir)
    if (item.updates && Array.isArray(item.updates)) {
      const hasDirectorUpdate = item.updates.some((update) => {
        return (
          update.action_type?.includes("director") ||
          (update.user && update.user.role === "Director") ||
          (update.metadata &&
            (update.metadata.created_by_director === true || update.metadata.director_id))
        );
      });

      if (hasDirectorUpdate) {
        return true;
      }
    }

    return false;
  });
});

// Dados da tab atual (usando dados filtrados)
const currentTabData = computed(() => {
  return getTabData(activeTab.value);
});

// **MÉTODO PRINCIPAL: Mudar de tab com Inertia**
const changeTab = (tab) => {
  if (loading.value) return; // Não fazer nada se já estiver carregando

  // Se já está na mesma tab, não fazer nada
  if (activeTab.value === tab) return;

  activeTab.value = tab;

  // Apenas para Manager na aba de intervenções
  if (isManager.value && tab === "director_interventions") {
    loading.value = true;

    // Recarregar com filtro de intervenções do director
    router.get(
      route("manager.dashboard"),
      {
        ...props.filters,
        director_interventions: true,
      },
      {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
          loading.value = false;
        },
      }
    );
  }
  // Se mudar para outra tab e tinha filtro de intervenções, remover
  else if (isManager.value && props.filters.director_interventions) {
    loading.value = true;

    const newFilters = { ...props.filters };
    delete newFilters.director_interventions;

    router.get(route("manager.dashboard"), newFilters, {
      preserveState: true,
      preserveScroll: true,
      onFinish: () => {
        loading.value = false;
      },
    });
  }
  // Para outras situações (Director ou outras tabs), apenas atualizar localmente
  else {
    // Atualização local apenas - não precisa recarregar
  }
};

// Métodos da UI
const toggleAllComplaints = () => {
  showAllComplaints.value = !showAllComplaints.value;
};

// ComplaintsList.vue - método handleRowClick
const handleRowClick = (item) => {
  if (loading.value) return;

  console.log("=== DEBUG CLICK ===");
  console.log("props.role:", props.role);
  console.log("isDirector.value:", isDirector.value);
  console.log("isManager.value:", isManager.value);
  console.log("Item reference:", item.reference_number);

  selectedComplaintId.value = item.id;

  // URL direta baseada no role (igual ao detailUrl do ComplaintRow)
  let url;
  url = `/complaints/grievance/${item.reference_number || item.id}`;

  console.log(`Navegando para: ${url}`);
  router.get(url);
};

const handleExport = () => {
  if (loading.value) return;

  // Determinar qual tab está ativa para exportar
  let exportType = activeTab.value;
  let label = getExportLabel();

  // Filtrar dados para exportação
  const dataToExport = currentTabData.value;

  // Implementar lógica de exportação
  console.log(`Exportando ${dataToExport.length} registros de ${label}...`);
  alert(`Exportando ${dataToExport.length} registros de ${label}...`);

  // Para uma implementação real, você pode usar:
  // router.post(route('complaints.export'), {
  //   tab: exportType,
  //   filters: localFilters.value,
  //   data: dataToExport
  // });
};

const handleBulkAssign = () => {
  if (loading.value) return;

  // Implementar lógica de atribuição automática
  alert("Atribuição automática em desenvolvimento...");

  // Para uma implementação real, você pode usar:
  // router.post(route('complaints.bulk-assign'));
};

const getExportLabel = () => {
  const labels = {
    suggestions: "Sugestões",
    grievances: "Queixas",
    complaints: "Reclamações",
    manager_requests: "Solicitações do Gestor",
    director_interventions: "Intervenções do Director",
    all: "Todos os Dados",
  };
  return labels[activeTab.value] || "Dados";
};

// Helper para obter tipo de intervenção
const getInterventionType = (item) => {
  if (!item) return "Sem intervenção";

  // Verificar validação do director
  if (item.director_validation) {
    const status = item.director_validation.status;
    return status === "approved"
      ? "Aprovado"
      : status === "rejected"
      ? "Rejeitado"
      : status === "needs_revision"
      ? "Revisão Solicitada"
      : "Validado";
  }

  // Verificar se foi reencaminhado
  if (
    item.escalated ||
    item.metadata?.is_escalated_to_director ||
    item.is_escalated_to_director
  ) {
    return "Reencaminhado";
  }

  // Verificar se tem updates do director
  if (item.director_updates && item.director_updates.length > 0) {
    const lastUpdate = item.director_updates[0];
    if (lastUpdate.action_type === "director_comment") return "Comentário do Director";
    if (lastUpdate.action_type === "director_validation_approved")
      return "Aprovado pelo Director";
    if (lastUpdate.action_type === "director_validation_rejected")
      return "Rejeitado pelo Director";
    if (lastUpdate.action_type === "director_validation_needs_revision")
      return "Revisão Solicitada";
    return "Intervenção do Director";
  }

  // Verificar em updates gerais
  if (item.updates && item.updates.length > 0) {
    const directorUpdates = item.updates.filter(
      (u) => u.action_type?.includes("director") || (u.user && u.user.role === "Director")
    );

    if (directorUpdates.length > 0) {
      const lastUpdate = directorUpdates[0];
      if (lastUpdate.action_type === "director_comment") return "Comentário do Director";
      if (lastUpdate.action_type?.includes("validation")) return "Validação do Director";
      return "Intervenção do Director";
    }
  }

  // Verificar contagem de comentários
  if (item.director_comments_count > 0) {
    return `${item.director_comments_count} Comentário(s) do Director`;
  }

  return "Intervenção do Director";
};

// Métodos auxiliares de formatação
const getTypeBadgeClass = (type) => {
  const normalizedType = type?.toLowerCase() || "";
  const classes = {
    suggestion: "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400",
    grievance: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
    complaint: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
  };
  const defaultClass = "bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400";

  return `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
    classes[normalizedType] || defaultClass
  }`;
};

const getPriorityBadgeClass = (priority) => {
  const classes = {
    low: "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
    medium: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
    high: "bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400",
    critical: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
  };
  const defaultClass = "bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400";

  return `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
    classes[priority] || defaultClass
  }`;
};

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
    submitted: "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400",
    in_progress:
      "bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400",
    resolved: "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
    closed: "bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400",
    escalated: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
    under_review:
      "bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400",
    pending_approval:
      "bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400",
    assigned: "bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-400",
    rejected: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
  };
  const defaultClass = "bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400";

  return `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
    classes[status] || defaultClass
  }`;
};

const getTypeLabel = (type) => {
  if (!type) return "Tipo não definido";

  const normalizedType = type.toLowerCase();
  const labels = {
    suggestion: "Sugestão",
    grievance: "Queixa",
    complaint: "Reclamação",
    sugestão: "Sugestão",
    sugestao: "Sugestão",
    queixa: "Queixa",
    reclamação: "Reclamação",
    reclamacao: "Reclamação",
  };

  return labels[normalizedType] || type.charAt(0).toUpperCase() + type.slice(1);
};

const getPriorityLabel = (priority) => {
  if (!priority) return "Prioridade não definida";

  const labels = {
    low: "Baixa",
    medium: "Média",
    high: "Alta",
    critical: "Crítica",
  };

  return labels[priority] || priority.charAt(0).toUpperCase() + priority.slice(1);
};

const getStatusLabel = (status) => {
  if (!status) return "Estado não definido";

  const labels = {
    pending: "Pendente",
    submitted: "Submetida",
    in_progress: "Em Progresso",
    resolved: "Resolvida",
    closed: "Fechada",
    escalated: "Escalada",
    under_review: "Em Análise",
    pending_approval: "Pendente Aprovação",
    assigned: "Atribuída",
    rejected: "Rejeitada",
  };

  return labels[status] || status.charAt(0).toUpperCase() + status.slice(1);
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString("pt-PT", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  } catch (error) {
    console.error("Erro ao formatar data:", error);
    return "Data inválida";
  }
};

const formatDateTime = (dateString) => {
  if (!dateString) return "N/A";
  try {
    const date = new Date(dateString);
    return date.toLocaleString("pt-PT", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch (error) {
    console.error("Erro ao formatar data/hora:", error);
    return "Data/hora inválida";
  }
};

// Inicializar filtros com base nos props
watch(
  () => props.filters,
  (newFilters) => {
    if (newFilters) {
      // Atualizar filtros locais baseados nos filtros recebidos
      if (newFilters.category) localFilters.value.category = newFilters.category;
      if (newFilters.type) localFilters.value.type = newFilters.type;
      if (newFilters.priority) localFilters.value.priority = newFilters.priority;
      if (newFilters.status) localFilters.value.status = newFilters.status;
    }
  },
  { immediate: true }
);

onMounted(() => {
  console.log("=== COMPLAINTS LIST MOUNTED ===");
  console.log("Filtros locais:", localFilters.value);
  console.log("Filtros ativos:", activeFiltersCount.value);
});
</script>

<style scoped>
.table-scroll-container {
  max-height: 500px;
  overflow-y: auto;
}

/* Destaque para intervenções do director */
tr.has-director-response {
  background-color: rgba(147, 51, 234, 0.05) !important;
}

tr.has-director-response:hover {
  background-color: rgba(147, 51, 234, 0.1) !important;
}

/* Estilo para botões desabilitados */
button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Estilos para os badges de intervenção */
.intervention-badge {
  transition: all 0.2s ease;
}

.intervention-badge:hover {
  transform: scale(1.05);
}

/* Scrollbar personalizada */
.table-scroll-container::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.table-scroll-container::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.table-scroll-container::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.table-scroll-container::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Estilos para modo escuro */
@media (prefers-color-scheme: dark) {
  .table-scroll-container::-webkit-scrollbar-track {
    background: #2d3748;
  }

  .table-scroll-container::-webkit-scrollbar-thumb {
    background: #4a5568;
  }

  .table-scroll-container::-webkit-scrollbar-thumb:hover {
    background: #718096;
  }
}

/* Estilos para os selects */
select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
}
</style>
