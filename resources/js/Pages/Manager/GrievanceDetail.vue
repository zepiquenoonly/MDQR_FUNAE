<template>
  <Layout>
    <div class="space-y-4 sm:space-y-6">
      <!-- Breadcrumb & Header -->
      <div class="flex flex-col gap-3 sm:gap-4">
        <Link
          href="/gestor/dashboard"
          class="text-sm text-brand hover:text-orange-700 font-medium flex items-center gap-1"
        >
          ← Voltar ao Painel
        </Link>
        <div
          class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
        >
          <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3"
          >
            <div class="flex-1">
              <h1
                class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-dark-text-primary"
              >
                {{ complaint.reference_number }}
              </h1>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ complaint.title }}
              </p>
            </div>
            <StatusBadge
              :status="complaint.status"
              :label="getStatusText(complaint.status)"
              size="lg"
            />
          </div>
          <div class="flex flex-wrap gap-2">
            <span
              :class="priorityBadgeClass(complaint.priority)"
              class="rounded-full px-3 py-1 text-sm font-semibold"
            >
              {{ priorityLabel(complaint.priority) }}
            </span>
            <span
              class="rounded-full bg-blue-100 dark:bg-blue-900/20 px-3 py-1 text-sm text-blue-700 dark:text-blue-300 font-medium"
            >
              {{ complaint.category }}
            </span>
            <span
              class="rounded-full bg-purple-100 dark:bg-purple-900/20 px-3 py-1 text-sm text-purple-700 dark:text-purple-300 font-medium"
            >
              {{ getTypeText(complaint.type) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Main Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Left Column - 2/3 -->
        <div class="lg:col-span-2 space-y-4 sm:space-y-6">
          <!-- Description Card -->
          <div
            class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
          >
            <h2
              class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2"
            >
              <span
                class="flex items-center justify-center h-5 w-5 rounded-full bg-purple-100 dark:bg-purple-900/20 text-xs"
              >
                <DocumentTextIcon class="h-4 w-4 text-purple-600" />
              </span>
              Descrição da Reclamação
            </h2>
            <div
              class="prose prose-sm dark:prose-invert max-w-none bg-gray-50 dark:bg-dark-accent rounded-lg p-4"
            >
              {{ complaint.description }}
            </div>
            <div
              class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"
            >
              <div class="text-sm">
                <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">
                  Submetida
                </p>
                <p class="text-gray-900 dark:text-dark-text-primary font-semibold">
                  {{ formatDate(complaint.created_at) }}
                </p>
              </div>
              <div class="text-sm">
                <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">
                  Atualizada
                </p>
                <p class="text-gray-900 dark:text-dark-text-primary font-semibold">
                  {{ getLastUpdate() }}
                </p>
              </div>
              <div class="text-sm">
                <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">
                  Dias Aberto
                </p>
                <p class="text-gray-900 dark:text-dark-text-primary font-semibold">
                  {{ getDaysOpen() }}
                </p>
              </div>
            </div>
          </div>

          <!-- Timeline de Status -->
          <div
            class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
          >
            <h2
              class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2"
            >
              <span
                class="flex items-center justify-center h-5 w-5 rounded-full bg-blue-100 dark:bg-blue-900/20 text-xs flex-shrink-0"
              >
                <ClockIcon class="h-4 w-4 text-blue-600" />
              </span>
              Estado da Submissão
            </h2>

            <div
              v-if="complaint.activities?.length === 0"
              class="text-center py-8 text-gray-500 dark:text-gray-400"
            >
              <p class="text-sm">Nenhum estado registado ainda</p>
            </div>

            <div v-else class="space-y-4 max-h-96 overflow-y-auto pr-2">
              <!-- Timeline com estados organizados -->
              <div class="relative">
                <!-- Linha vertical da timeline -->
                <div
                  class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"
                ></div>

                <!-- Itens da timeline -->
                <div
                  v-for="(state, index) in getStatusTimeline()"
                  :key="state.status"
                  class="relative flex gap-4 pb-6 last:pb-0"
                >
                  <!-- Ponto da timeline -->
                  <div
                    :class="[
                      'flex-shrink-0 w-8 h-8 rounded-full border-2 flex items-center justify-center z-10',
                      state.isActive
                        ? getTimelineDotClass(state.status)
                        : 'bg-gray-300 border-gray-300 dark:bg-gray-600 dark:border-gray-600',
                    ]"
                  >
                    <CheckIcon
                      v-if="
                        state.isActive &&
                        (state.status === 'closed' || state.status === 'resolved')
                      "
                      class="h-3 w-3 text-white"
                    />
                    <ClockIcon
                      v-else-if="
                        state.isActive &&
                        (state.status === 'in_progress' ||
                          state.status === 'under_review')
                      "
                      class="h-3 w-3 text-white"
                    />
                    <ExclamationTriangleIcon
                      v-else-if="state.isActive && state.status === 'rejected'"
                      class="h-3 w-3 text-white"
                    />
                    <DocumentTextIcon
                      v-else-if="state.isActive"
                      class="h-3 w-3 text-white"
                    />
                    <div v-else class="w-2 h-2 bg-white rounded-full"></div>
                  </div>

                  <!-- Conteúdo do item -->
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                      <p
                        :class="[
                          'font-semibold text-sm',
                          state.isActive
                            ? 'text-gray-900 dark:text-dark-text-primary'
                            : 'text-gray-400 dark:text-gray-500',
                        ]"
                      >
                        {{ state.label }}
                      </p>
                      <span
                        v-if="state.isCurrent"
                        class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300 rounded-full"
                      >
                        Estado Actual
                      </span>
                      <span
                        v-else-if="state.isActive"
                        class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300 rounded-full"
                      >
                        Concluído
                      </span>
                    </div>

                    <p
                      v-if="state.date"
                      class="text-xs text-gray-600 dark:text-gray-400 mb-1"
                    >
                      {{ state.date }}
                    </p>

                    <p
                      v-if="state.description"
                      class="text-sm text-gray-700 dark:text-gray-300 mt-1 bg-gray-50 dark:bg-dark-accent rounded-lg p-2"
                    >
                      {{ state.description }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Anexos -->
          <div
            class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
          >
            <h2
              class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2"
            >
              <span
                class="flex items-center justify-center h-5 w-5 rounded-full bg-green-100 dark:bg-green-900/20 text-xs flex-shrink-0"
              >
                <PaperClipIcon class="h-4 w-4 text-green-600" />
              </span>
              Anexos ({{ complaint.attachments?.length || 0 }})
            </h2>
            <div
              v-if="complaint.attachments?.length === 0"
              class="text-center py-8 text-gray-500 dark:text-gray-400"
            >
              <p class="text-sm">Sem anexos no momento</p>
            </div>
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <a
                v-for="attach in complaint.attachments"
                :key="attach.id"
                :href="attach.url"
                target="_blank"
                class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-dark-accent border border-gray-200 dark:border-gray-600 hover:border-brand dark:hover:border-orange-500 transition-all group"
              >
                <DocumentTextIcon class="h-8 w-8 text-gray-400" />
                <div class="flex-1 min-w-0">
                  <p
                    class="text-sm font-medium text-gray-900 dark:text-dark-text-primary truncate group-hover:text-brand"
                  >
                    {{ attach.name }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ attach.size || "N/A" }}
                  </p>
                </div>
                <ArrowDownTrayIcon class="h-5 w-5 group-hover:text-brand" />
              </a>
            </div>
          </div>
        </div>

        <!-- Right Column - 1/3 -->
        <div class="space-y-4 sm:space-y-6">
          <!-- Quick Status -->
          <div
            class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
          >
            <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4">
              Estado
            </h2>
            <div class="space-y-3">
              <div
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg"
              >
                <span class="text-sm text-gray-600 dark:text-gray-400">Estado</span>
                <StatusBadge
                  :status="complaint.status"
                  :label="getStatusText(complaint.status)"
                  size="sm"
                />
              </div>
              <div
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg"
              >
                <span class="text-sm text-gray-600 dark:text-gray-400">Prioridade</span>
                <span
                  :class="priorityBadgeClass(complaint.priority)"
                  class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary"
                >
                  {{ priorityLabel(complaint.priority) }}
                </span>
              </div>

              <div
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg"
              >
                <span class="text-sm text-gray-600 dark:text-gray-400">Impacto</span>
                <span
                  :class="priorityBadgeClass(complaint.category)"
                  class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary"
                >
                  {{ getImpactText(complaint.category) }}
                </span>
              </div>

              <div
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg"
              >
                <span class="text-sm text-gray-600 dark:text-gray-400"
                  >Tipo de submissão</span
                >
                <span
                  class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary"
                >
                  {{ getTypeText(complaint.type) }}
                </span>
              </div>
              <div
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg"
              >
                <span class="text-sm text-gray-600 dark:text-gray-400">Técnico</span>
                <span
                  class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary flex items-center gap-1"
                >
                  <div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div>
                  {{ complaint.technician?.name || "Não atribuído" }}
                </span>
              </div>
            </div>
          </div>

          <!-- Utente Info -->
          <div
            class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
          >
            <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4">
              Utente
            </h2>
            <div class="space-y-3">
              <div>
                <p class="text-xs text-gray-600 dark:text-gray-400 uppercase font-medium">
                  Nome
                </p>
                <p
                  class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary"
                >
                  {{ complaint.user?.name || "Anónimo" }}
                </p>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div
            class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
          >
            <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4">
              Ações
            </h2>
            <div class="space-y-2">
              <button
                @click="openPriorityModal"
                :disabled="loading.priority"
                class="w-full bg-brand text-white px-4 py-3 rounded-lg font-semibold hover:bg-orange-700 transition-all shadow-sm text-sm flex items-center justify-center gap-2"
              >
                <template v-if="loading.priority">
                  <div
                    class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"
                  ></div>
                  <span>Processando...</span>
                </template>

                <template v-else>
                  <FlagIcon class="h-4 w-4" />
                  <span>Definir Prioridade</span>
                </template>
              </button>

              <button
                @click="openReassignModal"
                :disabled="loading.reassign"
                class="w-full bg-white dark:bg-dark-accent text-gray-700 dark:text-gray-300 px-4 py-3 rounded-lg font-semibold border border-gray-300 dark:border-gray-600 hover:border-brand transition-all shadow-sm text-sm flex items-center justify-center gap-2"
              >
                <template v-if="loading.reassign">
                  <div
                    class="animate-spin rounded-full h-4 w-4 border-b-2 border-brand"
                  ></div>
                  <span>Processando...</span>
                </template>
                <template v-else>
                  <UserGroupIcon class="h-4 w-4" />
                  <span>Reatribuir Técnico</span>
                </template>
              </button>

              <button
                @click="sendToDirector"
                :disabled="loading.sendToDirector"
                class="w-full bg-blue-600 text-white px-4 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-all shadow-sm text-sm flex items-center justify-center gap-2"
              >
                <template v-if="loading.sendToDirector">
                  <div
                    class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"
                  ></div>
                  <span>Enviando...</span>
                </template>
                <template v-else>
                  <PaperAirplaneIcon class="h-4 w-4" />
                  <span>Enviar ao Director</span>
                </template>
              </button>

              <button
                @click="markComplete"
                :disabled="loading.markComplete"
                class="w-full bg-green-600 text-white px-4 py-3 rounded-lg font-semibold hover:bg-green-700 transition-all shadow-sm text-sm flex items-center justify-center gap-2"
              >
                <template v-if="loading.markComplete">
                  <div
                    class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"
                  ></div>
                  <span>Processando...</span>
                </template>
                <template v-else>
                  <CheckIcon class="h-4 w-4" />
                  <span>Marcar Concluído</span>
                </template>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <div
      v-if="toast.show"
      :class="[
        'fixed left-4 right-4 sm:left-auto sm:right-4 top-4 z-50 p-4 rounded-lg shadow-lg border transform transition-all duration-300 max-w-sm',
        toast.type === 'success'
          ? 'bg-green-50 border-green-200 text-green-800 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300'
          : toast.type === 'error'
          ? 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300'
          : 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-300',
      ]"
    >
      <div class="flex items-center gap-3">
        <CheckCircleIcon
          v-if="toast.type === 'success'"
          class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0"
        />
        <ExclamationTriangleIcon
          v-else-if="toast.type === 'error'"
          class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0"
        />
        <InformationCircleIcon
          v-else
          class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0"
        />
        <span class="font-medium flex-1">{{ toast.message }}</span>
        <button
          @click="toast.show = false"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex-shrink-0"
        >
          <XMarkIcon class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Modals -->
    <PriorityModal
      v-if="showPriorityModal"
      :complaint="complaint"
      @close="showPriorityModal = false"
      @update="updatePriority"
    />
    <ReassignModal
      v-if="showReassignModal"
      :complaint="complaint"
      :technicians="technicians"
      @close="showReassignModal = false"
      @update="reassignTechnician"
    />
  </Layout>
</template>

<script setup>
import { router } from "@inertiajs/vue3";
import { ref, reactive } from "vue";
import { Link } from "@inertiajs/vue3";
import Layout from "@/Layouts/ManagerLayout.vue";
import StatusBadge from "@/Components/Grievance/StatusBadge.vue";
import PriorityModal from "@/Components/GestorReclamacoes/PriorityModal.vue";
import ReassignModal from "@/Components/GestorReclamacoes/ReassignModal.vue";
import {
  DocumentTextIcon,
  ClockIcon,
  PaperClipIcon,
  ArrowDownTrayIcon,
  CheckIcon,
  FlagIcon,
  UserGroupIcon,
  PaperAirplaneIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  XMarkIcon,
  InformationCircleIcon,
} from "@heroicons/vue/24/outline";

// Props do Inertia
const props = defineProps({
  complaint: Object,
  technicians: {
    type: Array,
    default: () => [],
  },
});

// Estados
const complaint = ref(props.complaint);
const technicians = ref(props.technicians);
const showPriorityModal = ref(false);
const showReassignModal = ref(false);

// Estados de loading
const loading = reactive({
  priority: false,
  reassign: false,
  sendToDirector: false,
  markComplete: false,
});

// Toast notification
const toast = reactive({
  show: false,
  message: "",
  type: "success",
});

// Função para mostrar toast
const showToast = (message, type = "success") => {
  toast.message = message;
  toast.type = type;
  toast.show = true;
  setTimeout(() => {
    toast.show = false;
  }, 5000);
};

const getTypeText = (type) => {
  const types = {
    grievance: "Queixa",
    complaint: "Reclamação",
    suggestion: "Sugestão",
  };
  return types[type] || type;
};

const getImpactText = (category) => {
  if (!category) return "N/D";
  return category.charAt(0).toUpperCase() + category.slice(1).toLowerCase();
};

const priorityLabel = (priority) => {
  const map = {
    low: "Baixa",
    medium: "Média",
    high: "Alta",
  };
  return map[priority] ?? priority ?? "N/D";
};

const priorityBadgeClass = (priority) => {
  const map = {
    low: "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400",
    medium: "bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400",
    high: "bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400",
  };
  return map[priority] ?? "bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300";
};

const formatDate = (dateString) => {
  if (!dateString) return "N/D";
  return new Date(dateString).toLocaleDateString("pt-PT", {
    day: "2-digit",
    month: "long",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const getLastUpdate = () => {
  if (!complaint.value?.activities?.length) return "Nunca";
  const lastActivity = complaint.value.activities[complaint.value.activities.length - 1];
  return formatRelativeTime(lastActivity.created_at);
};

const formatRelativeTime = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const diffInHours = Math.floor((now - date) / (1000 * 60 * 60));

  if (diffInHours < 1) return "Agora mesmo";
  if (diffInHours < 24) return `Há ${diffInHours}h`;
  if (diffInHours < 168) return `Há ${Math.floor(diffInHours / 24)}d`;
  return `Há ${Math.floor(diffInHours / 168)}sem`;
};

const getDaysOpen = () => {
  if (!complaint.value?.created_at) return 0;
  const created = new Date(complaint.value.created_at);
  const now = new Date();
  return Math.floor((now - created) / (1000 * 60 * 60 * 24));
};

// Ações
const openPriorityModal = () => {
  if (!complaint.value) return;
  showPriorityModal.value = true;
};

const openReassignModal = () => {
  if (!complaint.value) return;
  showReassignModal.value = true;
};

const updatePriority = async (priority) => {
  loading.priority = true;
  try {
    // Usar Inertia para fazer a requisição
    router.patch(
      route("complaints.update-priority", { grievance: complaint.value.id }),
      {
        priority: priority,
      },
      {
        preserveScroll: true,
        onSuccess: () => {
          complaint.value.priority = priority;
          showPriorityModal.value = false;
          showToast("Prioridade atualizada com sucesso!", "success");
        },
        onError: () => {
          showToast("Erro ao atualizar prioridade", "error");
        },
      }
    );
  } finally {
    loading.priority = false;
  }
};

const reassignTechnician = async (technicianId) => {
  loading.reassign = true;
  try {
    const response = await router.patch(
      route("complaints.reassign", { grievance: complaint.value.id }),
      {
        technician_id: technicianId,
      },
      {
        preserveScroll: true,
        onSuccess: (response) => {
          // Atualizar o técnico com os dados retornados do backend
          if (response.props.technician) {
            complaint.value.technician = response.props.technician;
          } else {
            // Fallback: encontrar o técnico na lista local
            const technician = technicians.value.find((t) => t.id === technicianId);
            if (technician) {
              complaint.value.technician = technician;
            }
          }
          showReassignModal.value = false;
          showToast("Técnico Alocado", "success");
        },
        onError: (errors) => {
          console.error("Erro ao reatribuir técnico:", errors);
          showToast("Erro ao alocar técnico", "error");
        },
      }
    );
  } catch (error) {
    console.error("Erro na requisição:", error);
    showToast("Erro ao alocar técnico", "error");
  } finally {
    loading.reassign = false;
  }
};

const sendToDirector = async () => {
  if (!complaint.value) return;
  loading.sendToDirector = true;
  try {
    router.post(
      route("complaints.escalate", { grievance: complaint.value.id }),
      {},
      {
        preserveScroll: true,
        onSuccess: () => {
          showToast("Reclamação enviada ao director com sucesso!", "success");
        },
        onError: () => {
          showToast("Erro ao enviar reclamação ao director", "error");
        },
      }
    );
  } finally {
    loading.sendToDirector = false;
  }
};

const markComplete = async () => {
  if (!complaint.value) return;
  loading.markComplete = true;
  try {
    router.patch(
      route("complaints.complete", { grievance: complaint.value.id }),
      {},
      {
        preserveScroll: true,
        onSuccess: () => {
          complaint.value.status = "closed";
          showToast("Reclamação marcada como concluída!", "success");
        },
        onError: () => {
          showToast("Erro ao marcar reclamação como concluída", "error");
        },
      }
    );
  } finally {
    loading.markComplete = false;
  }
};

const getStatusText = (status) => {
  const statusTexts = {
    submitted: "Submetida",
    under_review: "Em Análise",
    assigned: "Atribuída",
    in_progress: "Em Andamento",
    pending_approval: "Pendente de Aprovação",
    resolved: "Resolvida",
    rejected: "Rejeitada",
    open: "Aberto",
    pending_completion: "Solicitado Conclusão",
    closed: "Concluído",

    status_changed: "Estado Alterado",
    priority_changed: "Prioridade Alterada",
    technician_assigned: "Técnico Atribuído",
    created: "Reclamação Criada",
  };

  return statusTexts[status] || status;
};

const getStatusTimeline = () => {
  const statusFlow = [
    { status: "submitted", label: "Submetida" },
    { status: "under_review", label: "Em Análise" },
    { status: "assigned", label: "Atribuída" },
    { status: "in_progress", label: "Em Andamento" },
    { status: "pending_approval", label: "Pendente de Aprovação" },
    { status: "resolved", label: "Resolvida" },
    { status: "closed", label: "Concluída" },
    { status: "rejected", label: "Rejeitada" },
  ];

  const currentStatus = complaint.value.status;
  const currentIndex = statusFlow.findIndex((state) => state.status === currentStatus);

  // Mapear atividades por status
  const activitiesByStatus = {};
  complaint.value.activities?.forEach((activity) => {
    if (activity.status) {
      activitiesByStatus[activity.status] = activity;
    } else if (activity.type === "status_changed" && activity.metadata?.status) {
      activitiesByStatus[activity.metadata.status] = activity;
    }
  });

  return statusFlow.map((state, index) => {
    const activity = activitiesByStatus[state.status];
    const isActive = index <= currentIndex;
    const isCurrent = index === currentIndex;

    return {
      ...state,
      isActive,
      isCurrent,
      date: activity
        ? formatDate(activity.created_at)
        : isActive
        ? formatDate(complaint.value.created_at)
        : null,
      description:
        activity?.description ||
        activity?.metadata?.reason ||
        (isCurrent ? "Estado atual da reclamação" : null),
    };
  });
};

// Adicionar a função que estava faltando
const getTimelineDotClass = (status) => {
  const statusMap = {
    submitted: "bg-blue-500 border-blue-500",
    under_review: "bg-yellow-500 border-yellow-500",
    assigned: "bg-purple-500 border-purple-500",
    in_progress: "bg-orange-500 border-orange-500",
    pending_approval: "bg-indigo-500 border-indigo-500",
    resolved: "bg-green-500 border-green-500",
    rejected: "bg-red-500 border-red-500",
    closed: "bg-green-600 border-green-600",
  };

  return statusMap[status] || "bg-gray-500 border-gray-500";
};

// Função de debug para verificar os dados
const debugTimeline = () => {
  console.log("Current complaint:", complaint.value);
  console.log("Current status:", complaint.value.status);
  console.log("Activities:", complaint.value.activities);
  console.log("Timeline result:", getStatusTimeline());
};

const getStatusTimelineAlternative = () => {
  const statusFlow = [
    { status: "submitted", label: "Submetida" },
    { status: "under_review", label: "Em Análise" },
    { status: "assigned", label: "Atribuída" },
    { status: "in_progress", label: "Em Andamento" },
    { status: "pending_approval", label: "Pendente de Aprovação" },
    { status: "resolved", label: "Resolvida" },
    { status: "closed", label: "Concluída" },
    { status: "rejected", label: "Rejeitada" },
  ];

  const currentStatus = complaint.value.status;
  const currentIndex = statusFlow.findIndex((state) => state.status === currentStatus);

  // Mapear atividades por status
  const activitiesByStatus = {};
  complaint.value.activities?.forEach((activity) => {
    if (activity.status) {
      activitiesByStatus[activity.status] = activity;
    } else if (activity.type === "status_changed" && activity.metadata?.status) {
      activitiesByStatus[activity.metadata.status] = activity;
    }
  });

  return statusFlow.map((state, index) => {
    const activity = activitiesByStatus[state.status];
    const isActive = index <= currentIndex;
    const isCurrent = index === currentIndex;

    return {
      ...state,
      isActive,
      isCurrent,
      date: activity
        ? formatDate(activity.created_at)
        : isActive
        ? formatDate(complaint.value.created_at)
        : null,
      description:
        activity?.description ||
        activity?.metadata?.reason ||
        (isCurrent ? "Estado atual da reclamação" : null),
    };
  });
};
</script>
