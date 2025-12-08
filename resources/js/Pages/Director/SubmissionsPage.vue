<template>
  <AppLayout title="Submissões - Reclamações, Queixas e Sugestões">
    <div class="max-w-full mx-auto">
      <!-- Cabeçalho -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Submissões</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Gere reclamações, queixas e sugestões do seu departamento
        </p>
      </div>

      <!-- Filtros e Estatísticas -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="glass p-4 rounded-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ stats.total }}
              </p>
            </div>
            <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
              <DocumentTextIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
        </div>

        <div class="glass p-4 rounded-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Pendentes
              </p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ stats.pending }}
              </p>
            </div>
            <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
              <ClockIcon class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
            </div>
          </div>
        </div>

        <div class="glass p-4 rounded-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Resolvidos
              </p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ stats.resolved }}
              </p>
            </div>
            <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
              <CheckCircleIcon class="h-6 w-6 text-green-600 dark:text-green-400" />
            </div>
          </div>
        </div>

        <!-- NOVO: Estatística para Casos Escalados -->
        <div class="glass p-4 rounded-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Escalados
              </p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ escalatedCasesCount }}
              </p>
            </div>
            <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
              <PaperAirplaneIcon class="h-6 w-6 text-purple-600 dark:text-purple-400" />
            </div>
          </div>
        </div>
      </div>

      <!-- Filtros e Busca -->
      <div class="glass p-4 mb-6 rounded-xl">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="flex-1">
            <div class="relative">
              <MagnifyingGlassIcon
                class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400"
              />
              <input
                v-model="search"
                type="text"
                placeholder="Buscar submissões..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-secondary focus:ring-2 focus:ring-brand focus:border-transparent"
              />
            </div>
          </div>
          <div class="flex gap-2">
            <select
              v-model="filterType"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-secondary focus:ring-2 focus:ring-brand focus:border-transparent"
            >
              <option value="">Todos os Tipos</option>
              <option value="grievance">Queixa</option>
              <option value="complaint">Reclamação</option>
              <option value="suggestion">Sugestão</option>
            </select>
            <select
              v-model="filterStatus"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-secondary focus:ring-2 focus:ring-brand focus:border-transparent"
            >
              <option value="">Todos os Status</option>
              <option value="pending">Pendente</option>
              <option value="in_progress">Em Análise</option>
              <option value="resolved">Resolvido</option>
              <option value="closed">Fechado</option>
              <option value="escalated">Escalado</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Tabs para alternar entre todas as submissões e casos específicos -->
      <div class="mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
          <nav class="-mb-px flex space-x-8">
            <button
              @click="switchTab('all')"
              :class="[
                activeTab === 'all'
                  ? 'border-brand text-brand dark:text-brand'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
              ]"
            >
              Todas as Submissões
              <span
                v-if="activeTab === 'all'"
                class="ml-2 inline-block h-2 w-2 rounded-full bg-brand"
              ></span>
            </button>
            <button
              @click="switchTab('specific')"
              :class="[
                activeTab === 'specific'
                  ? 'border-purple-500 text-bold text-purple-600 dark:text-purple-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
              ]"
            >
              Casos Escalados para Director
              <span
                v-if="activeTab === 'specific'"
                class="ml-2 inline-block h-2 w-2 rounded-full bg-purple-500"
              ></span>
              <span
                v-if="specificCases.length > 0"
                class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300"
              >
                {{ specificCases.length }}
              </span>
            </button>
          </nav>
        </div>
      </div>

      <!-- Lista de Submissões COM ROLAGEM VERTICAL -->
      <div class="glass rounded-xl overflow-hidden">
        <!-- Container com altura fixa para rolagem vertical -->
        <div class="max-h-[500px] overflow-y-auto">
          <div class="min-w-full inline-block align-middle">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-dark-secondary sticky top-0 z-10">
                <tr>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    ID
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Tipo
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Assunto
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Prioridade
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Status
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Data
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Acção
                  </th>
                </tr>
              </thead>
              <tbody
                class="bg-white dark:bg-dark-primary divide-y divide-gray-200 dark:divide-gray-700"
              >
                <!-- Casos Específicos (Apenas Escalados para Director) -->
                <template v-if="activeTab === 'specific'">
                  <tr
                    v-for="submission in filteredSpecificCases"
                    :key="submission.reference_number"
                    class="hover:bg-gray-50 dark:hover:bg-dark-secondary transition-colors"
                  >
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white"
                    >
                      #{{ submission.reference_number }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="getTypeBadgeClass(submission.type)">
                        {{ getTypeLabel(submission.type) }}
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      <div class="space-y-2">
                        <!-- Assunto original -->
                        <div>
                          <div
                            class="text-sm text-gray-900 dark:text-white font-medium mb-1"
                          >
                            {{ submission.subject }}
                          </div>
                          <div
                            class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2"
                          >
                            {{ submission.description }}
                          </div>
                        </div>

                        <!-- Informações do escalamento -->
                        <div
                          v-if="submission.escalated || submission.escalation_reason"
                          class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700"
                        >
                          <!-- Badge indicador -->
                          <div class="flex items-center gap-1 mb-1">
                            <PaperAirplaneIcon class="h-3 w-3 text-purple-500" />
                            <span
                              class="text-xs font-medium text-purple-600 dark:text-purple-400"
                            >
                              Solicitado ao Director
                            </span>
                          </div>

                          <!-- Motivo do escalamento -->
                          <div
                            v-if="submission.escalation_reason"
                            class="flex items-start gap-1"
                          >
                            <ChatBubbleLeftIcon
                              class="h-3 w-3 text-gray-400 mt-0.5 flex-shrink-0"
                            />
                            <span
                              class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2"
                            >
                              <span class="font-medium">Motivo:</span>
                              {{ submission.escalation_reason }}
                            </span>
                          </div>

                          <!-- Informações adicionais -->
                          <div
                            v-if="submission.escalated_by || submission.escalated_at"
                            class="flex flex-wrap gap-x-4 gap-y-1 mt-1"
                          >
                            <!-- Quem escalou -->
                            <div
                              v-if="submission.escalated_by"
                              class="flex items-center gap-1"
                            >
                              <UserCircleIcon class="h-3 w-3 text-gray-400" />
                              <span class="text-xs text-gray-500 dark:text-gray-400">
                                Por: {{ submission.escalated_by }}
                              </span>
                            </div>

                            <!-- Data do escalamento -->
                            <div
                              v-if="submission.escalated_at"
                              class="flex items-center gap-1"
                            >
                              <CalendarIcon class="h-3 w-3 text-gray-400" />
                              <span class="text-xs text-gray-500 dark:text-gray-400">
                                Em: {{ formatShortDate(submission.escalated_at) }}
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="getPriorityBadgeClass(submission.priority)">
                        {{ getPriorityLabel(submission.priority) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="getStatusBadgeClass(submission.status)">
                        {{ getStatusLabel(submission.status) }}
                      </span>
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                    >
                      {{ formatDate(submission.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex items-center gap-2">
                        <Link
                          :href="`/director/complaints-overview/${submission.reference_number}`"
                          class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 inline-flex items-center gap-2"
                          title="Ver Detalhes"
                        >
                          <EyeIcon class="h-5 w-5" />
                          <span class="text-black dark:text-white">Detalhes</span>
                        </Link>
                        <button
                          v-if="submission.priority === 'critical'"
                          @click="openValidationModal(submission)"
                          class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-900 dark:hover:text-yellow-300"
                          title="Validar"
                        >
                          <CheckBadgeIcon class="h-5 w-5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </template>

                <!-- Todas as Submissões -->
                <template v-else>
                  <tr
                    v-for="submission in filteredSubmissions"
                    :key="submission.reference_number"
                    class="hover:bg-gray-50 dark:hover:bg-dark-secondary transition-colors"
                  >
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white"
                    >
                      #{{ submission.reference_number }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="getTypeBadgeClass(submission.type)">
                        {{ getTypeLabel(submission.type) }}
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm text-gray-900 dark:text-white font-medium">
                        {{ submission.subject }}
                      </div>
                      <div
                        class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs"
                      >
                        {{ submission.description }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="getPriorityBadgeClass(submission.priority)">
                        {{ getPriorityLabel(submission.priority) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="getStatusBadgeClass(submission.status)">
                        {{ getStatusLabel(submission.status) }}
                      </span>
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                    >
                      {{ formatDate(submission.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex items-center gap-2">
                        <Link
                          :href="`/director/complaints-overview/${submission.reference_number}`"
                          class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300"
                          title="Ver Detalhes"
                        >
                          <EyeIcon class="h-5 w-5" />
                        </Link>
                        <button
                          v-if="submission.priority === 'critical'"
                          @click="openValidationModal(submission)"
                          class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-900 dark:hover:text-yellow-300"
                          title="Validar"
                        >
                          <CheckBadgeIcon class="h-5 w-5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Estado vazio -->
        <div
          v-if="
            activeTab === 'specific'
              ? filteredSpecificCases.length === 0
              : filteredSubmissions.length === 0
          "
          class="text-center py-12 border-t border-gray-200 dark:border-gray-700"
        >
          <template v-if="activeTab === 'specific'">
            <PaperAirplaneIcon class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
              Nenhum caso escalado para Director
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Não há submissões escaladas para análise do Director no momento.
            </p>
          </template>
          <template v-else>
            <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
              Nenhuma submissão encontrada
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Não há reclamações, queixas ou sugestões para exibir.
            </p>
          </template>
        </div>

        <!-- Paginação -->
        <div
          v-if="
            (activeTab === 'specific'
              ? filteredSpecificCases.length
              : filteredSubmissions.length) > 0
          "
          class="px-6 py-4 border-t border-gray-200 dark:border-gray-700"
        >
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-300">
              Mostrando
              {{
                activeTab === "specific"
                  ? filteredSpecificCases.length
                  : filteredSubmissions.length
              }}
              de
              {{ activeTab === "specific" ? specificCases.length : submissions.length }}
              registos
            </div>
            <div class="flex gap-2">
              <button
                :disabled="currentPage === 1"
                @click="prevPage"
                class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-dark-tertiary"
              >
                Anterior
              </button>
              <button
                :disabled="
                  currentPage * itemsPerPage >=
                  (activeTab === 'specific' ? specificCases.length : submissions.length)
                "
                @click="nextPage"
                class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-dark-tertiary"
              >
                Próximo
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal de Validação -->
      <ValidationModal
        :is-open="showValidationModal"
        :submission="selectedSubmission"
        @close="showValidationModal = false"
        @validate="handleValidation"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/ManagerLayout.vue";
import ValidationModal from "@/Components/Director/ValidationModal.vue";
import {
  DocumentTextIcon,
  ClockIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  MagnifyingGlassIcon,
  EyeIcon,
  CheckBadgeIcon,
  PaperAirplaneIcon,
  UserCircleIcon,
  CalendarIcon,
  ChatBubbleLeftIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  submissions: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Object,
    default: () => ({}),
  },
});

const search = ref("");
const filterType = ref("");
const filterStatus = ref("");
const showValidationModal = ref(false);
const selectedSubmission = ref(null);
const currentPage = ref(1);
const itemsPerPage = 10;
const activeTab = ref("all"); // 'all' ou 'specific'

// Computed: Contagem de casos escalados
const escalatedCasesCount = computed(() => {
  return props.submissions.filter((submission) => isDirectorCase(submission)).length;
});

// Filtrar CASOS ESCALADOS PARA DIRECTOR APENAS
const specificCases = computed(() => {
  return props.submissions.filter((submission) => {
    // Apenas casos escalados para director
    return (
      submission.escalated === true ||
      submission.metadata?.is_escalated_to_director === true ||
      (submission.assigned_to &&
        (submission.assigned_to.name?.toLowerCase().includes("director") ||
          submission.assigned_to.email?.includes("director")))
    );
  });
});

// Método para verificar se é caso escalado para director
const isDirectorCase = (submission) => {
  return (
    submission.escalated === true ||
    submission.metadata?.is_escalated_to_director === true ||
    (submission.assigned_to &&
      (submission.assigned_to.name?.toLowerCase().includes("director") ||
        submission.assigned_to.email?.includes("director")))
  );
};

// Filtrar casos específicos com busca
const filteredSpecificCases = computed(() => {
  let filtered = [...specificCases.value];

  // Aplicar filtro de busca
  if (search.value) {
    const searchTerm = search.value.toLowerCase();
    filtered = filtered.filter(
      (submission) =>
        submission.subject.toLowerCase().includes(searchTerm) ||
        submission.description.toLowerCase().includes(searchTerm) ||
        submission.reference_number.toString().includes(searchTerm) ||
        submission.escalated_by?.toLowerCase().includes(searchTerm) ||
        submission.escalation_reason?.toLowerCase().includes(searchTerm)
    );
  }

  // Aplicar filtro de tipo (se relevante)
  if (filterType.value) {
    filtered = filtered.filter((submission) => submission.type === filterType.value);
  }

  // Paginação
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filtered.slice(start, end);
});

const filteredSubmissions = computed(() => {
  let filtered = [...props.submissions];

  // Aplicar filtro de busca
  if (search.value) {
    const searchTerm = search.value.toLowerCase();
    filtered = filtered.filter(
      (submission) =>
        submission.subject.toLowerCase().includes(searchTerm) ||
        submission.description.toLowerCase().includes(searchTerm) ||
        submission.reference_number.toString().includes(searchTerm)
    );
  }

  // Aplicar filtro de tipo
  if (filterType.value) {
    filtered = filtered.filter((submission) => submission.type === filterType.value);
  }

  // Aplicar filtro de status
  if (filterStatus.value) {
    filtered = filtered.filter((submission) => submission.status === filterStatus.value);
  }

  // Paginação
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filtered.slice(start, end);
});

const resetFilters = () => {
  search.value = "";
  filterType.value = "";
  filterStatus.value = "";
  currentPage.value = 1;
};

const nextPage = () => {
  const totalItems =
    activeTab.value === "specific"
      ? specificCases.value.length
      : props.submissions.length;
  if (currentPage.value * itemsPerPage < totalItems) {
    currentPage.value++;
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

const openValidationModal = (submission) => {
  selectedSubmission.value = submission;
  showValidationModal.value = true;
};

const handleValidation = (data) => {
  console.log("Validar:", data);
  // Implementar lógica de validação aqui
  showValidationModal.value = false;
};

const getTypeBadgeClass = (type) => {
  const classes = {
    grievance: "bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400",
    complaint: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
    suggestion: "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400",
  };
  return `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
    classes[type] || "bg-gray-100 text-gray-800"
  }`;
};

const getPriorityBadgeClass = (priority) => {
  const classes = {
    low: "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
    medium: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
    high: "bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400",
    critical: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
  };
  return `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
    classes[priority] || "bg-gray-100 text-gray-800"
  }`;
};

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
    in_progress: "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400",
    resolved: "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
    closed: "bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400",
    submitted: "bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400",
    under_review:
      "bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400",
    assigned: "bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-400",
    escalated: "bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400",
    rejected: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
  };
  return `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
    classes[status] || "bg-gray-100 text-gray-800"
  }`;
};

const getTypeLabel = (type) => {
  const labels = {
    grievance: "Queixa",
    complaint: "Reclamação",
    suggestion: "Sugestão",
  };
  return labels[type] || type;
};

const getPriorityLabel = (priority) => {
  const labels = {
    low: "Baixa",
    medium: "Média",
    high: "Alta",
    critical: "Crítica",
    urgent: "Urgente",
  };
  return labels[priority] || priority;
};

const getStatusLabel = (status) => {
  const labels = {
    pending: "Pendente",
    in_progress: "Em Análise",
    resolved: "Resolvido",
    closed: "Fechado",
    submitted: "Submetida",
    under_review: "Em Revisão",
    assigned: "Atribuída",
    escalated: "Escalada para Director",
    rejected: "Rejeitada",
  };
  return labels[status] || status;
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString("pt-PT", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch (error) {
    console.error("Erro ao formatar data:", error);
    return "Data inválida";
  }
};

const formatShortDate = (dateString) => {
  if (!dateString) return "N/A";
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString("pt-PT", {
      day: "2-digit",
      month: "2-digit",
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch (error) {
    console.error("Erro ao formatar data:", error);
    return "Data inválida";
  }
};

// Resetar página quando mudar de tab
const switchTab = (tab) => {
  activeTab.value = tab;
  currentPage.value = 1;
};
</script>

<style scoped>
/* Estilos para a barra de rolagem */
.max-h-\[500px\]::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.max-h-\[500px\]::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.max-h-\[500px\]::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.max-h-\[500px\]::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Estilos para modo escuro */
.dark .max-h-\[500px\]::-webkit-scrollbar-track {
  background: #374151;
}

.dark .max-h-\[500px\]::-webkit-scrollbar-thumb {
  background: #6b7280;
}

.dark .max-h-\[500px\]::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

/* Fix para cabeçalho sticky */
.sticky {
  position: sticky;
  top: 0;
  background-color: inherit;
}

/* Transição suave para tabs */
.tab-transition {
  transition: all 0.3s ease-in-out;
}
</style>
