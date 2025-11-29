<template>
  <div class="min-h-screen bg-gray-50 dark:bg-dark-primary">
    <!-- Header da página -->
    <div
      class="bg-white dark:bg-dark-secondary border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Botão voltar e título -->
          <div class="flex items-center gap-4">
            <button
              @click="handleBack"
              class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200 rounded-lg hover:bg-gray-50 dark:hover:bg-dark-accent"
            >
              <ArrowLeftIcon class="w-5 h-5" />
            </button>
            <div>
              <h1 class="text-xl font-bold text-gray-900 dark:text-dark-text-primary">
                Detalhes da Reclamação
              </h1>
              <div class="flex items-center gap-2 mt-1">
                <div
                  class="flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400"
                >
                  <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                  <span>Ativo</span>
                </div>
                <span class="text-gray-300 dark:text-gray-600">•</span>
                <span class="text-sm text-gray-500 dark:text-gray-400"
                  >Última: {{ getLastUpdate() }}</span
                >
              </div>
            </div>
          </div>

          <!-- Ações do header -->
          <div class="flex items-center gap-2">
            <button
              @click="handlePrint"
              :disabled="loading.print"
              class="flex items-center gap-2 px-3 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-dark-text-primary transition-colors duration-200 rounded-lg border border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <PrinterIcon class="w-4 h-4" />
              <span class="hidden sm:inline">Imprimir</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Conteúdo principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div v-if="complaint" class="space-y-6">
        <!-- Complaint Header -->
        <div
          class="bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-900/10 dark:to-amber-900/10 rounded-xl p-6 border border-orange-100 dark:border-orange-800"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-3 mb-3">
                <h4
                  class="text-xl font-bold text-gray-900 dark:text-dark-text-primary truncate"
                >
                  {{ complaint.title }}
                </h4>
                <span
                  class="px-2 py-1 bg-white dark:bg-dark-secondary rounded text-sm font-medium border border-orange-200 dark:border-orange-700 text-orange-700 dark:text-orange-300 shadow-sm"
                >
                  {{ getTypeText(complaint.type) }}
                </span>
              </div>

              <p class="text-gray-700 dark:text-gray-300 text-lg leading-relaxed mb-4">
                {{ complaint.description }}
              </p>

              <div class="flex flex-wrap items-center gap-4 text-sm">
                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                  <div
                    class="w-8 h-8 bg-brand rounded flex items-center justify-center text-white text-xs font-bold"
                  >
                    {{ getUserInitials(complaint.user?.name || "Utente") }}
                  </div>
                  <span class="font-medium">{{ complaint.user?.name || "Utente" }}</span>
                </div>
                <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                  <HashtagIcon class="w-4 h-4" />
                  <span class="font-mono">#{{ complaint.id }}</span>
                </div>
                <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                  <CalendarIcon class="w-4 h-4" />
                  <span>{{ formatDate(complaint.created_at) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
          <div
            class="bg-white dark:bg-dark-secondary rounded-lg p-4 text-center border border-gray-200 dark:border-gray-700 shadow-sm"
          >
            <div
              class="text-2xl font-bold text-gray-900 dark:text-dark-text-primary mb-1"
            >
              {{ complaint.activities?.length || 0 }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">
              Atividades
            </div>
          </div>
          <div
            class="bg-white dark:bg-dark-secondary rounded-lg p-4 text-center border border-gray-200 dark:border-gray-700 shadow-sm"
          >
            <div
              class="text-2xl font-bold text-gray-900 dark:text-dark-text-primary mb-1"
            >
              {{ complaint.attachments?.length || 0 }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">Anexos</div>
          </div>
          <div
            class="bg-white dark:bg-dark-secondary rounded-lg p-4 text-center border border-gray-200 dark:border-gray-700 shadow-sm"
          >
            <div
              class="text-2xl font-bold text-gray-900 dark:text-dark-text-primary mb-1"
            >
              {{ getDaysOpen() }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">
              Dias em aberto
            </div>
          </div>
          <div
            class="bg-white dark:bg-dark-secondary rounded-lg p-4 text-center border border-gray-200 dark:border-gray-700 shadow-sm"
          >
            <div
              class="text-2xl font-bold text-gray-900 dark:text-dark-text-primary mb-1"
            >
              {{ complaint.priority || "N/A" }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">
              Prioridade
            </div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
          <!-- Left Column -->
          <div class="lg:col-span-2 space-y-8">
            <!-- Actions -->
            <div
              class="bg-white dark:bg-dark-secondary rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm"
            >
              <div class="flex items-center gap-3 mb-6">
                <div
                  class="w-2 h-6 bg-gradient-to-b from-orange-500 to-amber-500 rounded-full"
                ></div>
                <h4 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary">
                  Ações Rápidas
                </h4>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button
                  @click="openPriorityModal"
                  :disabled="loading.priority"
                  class="flex items-center justify-center gap-2 p-4 bg-brand text-white rounded-lg font-semibold transition-all duration-200 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <template v-if="loading.priority">
                    <div
                      class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"
                    ></div>
                    <span>Processando...</span>
                  </template>
                  <template v-else>
                    <FlagIcon class="w-5 h-5" />
                    <span>Definir Prioridade</span>
                  </template>
                </button>

                <button
                  @click="openReassignModal"
                  :disabled="loading.reassign"
                  class="flex items-center justify-center gap-2 p-4 bg-white dark:bg-dark-accent text-gray-700 dark:text-gray-300 rounded-lg font-semibold border-2 border-gray-200 dark:border-gray-600 transition-all duration-200 hover:border-brand hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <template v-if="loading.reassign">
                    <div
                      class="animate-spin rounded-full h-5 w-5 border-b-2 border-brand"
                    ></div>
                    <span>Processando...</span>
                  </template>
                  <template v-else>
                    <UserGroupIcon class="w-5 h-5" />
                    <span>Reatribuir Técnico</span>
                  </template>
                </button>

                <button
                  @click="sendToDirector"
                  :disabled="loading.sendToDirector"
                  class="flex items-center justify-center gap-2 p-4 bg-white dark:bg-dark-accent text-gray-700 dark:text-gray-300 rounded-lg font-semibold border-2 border-gray-200 dark:border-gray-600 transition-all duration-200 hover:border-brand hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <template v-if="loading.sendToDirector">
                    <div
                      class="animate-spin rounded-full h-5 w-5 border-b-2 border-brand"
                    ></div>
                    <span>Enviando...</span>
                  </template>
                  <template v-else>
                    <PaperAirplaneIcon class="w-5 h-5" />
                    <span>Enviar ao Director</span>
                  </template>
                </button>

                <button
                  @click="markComplete"
                  :disabled="loading.markComplete"
                  class="flex items-center justify-center gap-2 p-4 bg-green-500 text-white rounded-lg font-semibold transition-all duration-200 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <template v-if="loading.markComplete">
                    <div
                      class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"
                    ></div>
                    <span>Processando...</span>
                  </template>
                  <template v-else>
                    <CheckIcon class="w-5 h-5" />
                    <span>Marcar Concluído</span>
                  </template>
                </button>
              </div>
            </div>

            <!-- Activity Log -->
            <div
              class="bg-white dark:bg-dark-secondary rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm"
            >
              <div class="flex items-center gap-3 mb-6">
                <div
                  class="w-2 h-6 bg-gradient-to-b from-blue-500 to-cyan-500 rounded-full"
                ></div>
                <h4 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary">
                  Histórico de Atividades
                </h4>
              </div>
              <div class="space-y-4 max-h-80 overflow-y-auto">
                <div
                  v-for="(activity, index) in complaint.activities"
                  :key="activity.id"
                  class="flex gap-3 group hover:bg-gray-50 dark:hover:bg-dark-accent p-3 rounded-xl transition-colors duration-200"
                >
                  <div class="flex flex-col items-center">
                    <div class="w-3 h-3 bg-blue-500 rounded-full mt-2"></div>
                    <div
                      v-if="index !== complaint.activities.length - 1"
                      class="w-0.5 h-full bg-gray-200 dark:bg-gray-600 mt-1"
                    ></div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                      <span
                        class="font-semibold text-gray-900 dark:text-dark-text-primary text-sm"
                      >
                        {{ activity.description }}
                      </span>
                      <span
                        class="text-xs text-gray-400 dark:text-gray-500 flex-shrink-0"
                      >
                        {{ formatRelativeTime(activity.created_at) }}
                      </span>
                    </div>
                    <div
                      class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1"
                    >
                      <UserIcon class="w-3 h-3" />
                      <span>Por: {{ activity.user?.name || "Sistema" }}</span>
                    </div>
                  </div>
                </div>
                <div
                  v-if="!complaint.activities?.length"
                  class="text-center py-8 text-gray-400 dark:text-gray-500"
                >
                  <ClockIcon class="w-12 h-12 mx-auto mb-3 opacity-50" />
                  <p class="text-sm">Nenhuma atividade registada</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div class="space-y-8">
            <!-- Complaint Details -->
            <div
              class="bg-white dark:bg-dark-secondary rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm"
            >
              <div class="flex items-center gap-3 mb-6">
                <div
                  class="w-2 h-6 bg-gradient-to-b from-purple-500 to-pink-500 rounded-full"
                ></div>
                <h4 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary">
                  Informações
                </h4>
              </div>
              <div class="space-y-4">
                <div
                  class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-xl"
                >
                  <span class="text-sm font-medium text-gray-600 dark:text-gray-400"
                    >Status</span
                  >
                  <span
                    class="px-2 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300 rounded-full text-xs font-bold"
                  >
                    {{ getStatusText(complaint.status) }}
                  </span>
                </div>
                <div
                  class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-xl"
                >
                  <span class="text-sm font-medium text-gray-600 dark:text-gray-400"
                    >Categoria</span
                  >
                  <span
                    class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary"
                  >
                    {{ complaint.category }}
                  </span>
                </div>
                <div
                  class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-xl"
                >
                  <span class="text-sm font-medium text-gray-600 dark:text-gray-400"
                    >Tipo</span
                  >
                  <span
                    class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary"
                  >
                    {{ getTypeText(complaint.type) }}
                  </span>
                </div>
                <div
                  class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-xl"
                >
                  <span class="text-sm font-medium text-gray-600 dark:text-gray-400"
                    >Técnico</span
                  >
                  <span
                    class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary flex items-center gap-1"
                  >
                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div>
                    <span>{{ complaint.technician?.name || "Não atribuído" }}</span>
                  </span>
                </div>
                <div
                  class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-xl"
                >
                  <span class="text-sm font-medium text-gray-600 dark:text-gray-400"
                    >Prioridade</span
                  >
                  <span
                    :class="[
                      'px-2 py-1 rounded-full text-xs font-bold',
                      complaint.priority === 'high'
                        ? 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300'
                        : complaint.priority === 'medium'
                        ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300'
                        : 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300',
                    ]"
                  >
                    {{ complaint.priority || "Não definida" }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Attachments -->
            <div
              class="bg-white dark:bg-dark-secondary rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm"
            >
              <div class="flex items-center gap-3 mb-6">
                <div
                  class="w-2 h-6 bg-gradient-to-b from-green-500 to-emerald-500 rounded-full"
                ></div>
                <h4 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary">
                  Anexos
                </h4>
              </div>
              <div class="space-y-2">
                <div
                  v-for="attachment in complaint.attachments"
                  :key="attachment.id"
                  class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-dark-accent rounded-lg border border-gray-200 dark:border-gray-600 hover:border-orange-300 dark:hover:border-orange-500 transition-all duration-200 cursor-pointer group"
                >
                  <div
                    class="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-500 rounded-lg flex items-center justify-center text-white flex-shrink-0"
                  >
                    <DocumentIcon class="w-5 h-5" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <div
                      class="text-sm font-medium text-gray-900 dark:text-dark-text-primary truncate"
                    >
                      {{ attachment.name }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                      {{ attachment.size || "N/A" }}
                    </div>
                  </div>
                  <ArrowDownTrayIcon
                    class="w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-orange-500 dark:group-hover:text-orange-400 transition-colors flex-shrink-0"
                  />
                </div>
                <div
                  v-if="!complaint.attachments?.length"
                  class="text-center py-6 text-gray-400 dark:text-gray-500"
                >
                  <PaperClipIcon class="w-12 h-12 mx-auto mb-3 opacity-50" />
                  <p class="text-sm">Sem anexos</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-else-if="loading.global" class="text-center py-16">
        <div
          class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand mx-auto mb-4"
        ></div>
        <p class="text-gray-500 dark:text-gray-400">
          Carregando detalhes da reclamação...
        </p>
      </div>

      <!-- Error State -->
      <div v-else class="text-center py-16">
        <div
          class="w-24 h-24 bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900/10 dark:to-amber-900/10 rounded-full flex items-center justify-center mx-auto mb-6"
        >
          <ExclamationTriangleIcon
            class="w-12 h-12 text-orange-300 dark:text-orange-600"
          />
        </div>
        <h4 class="text-lg font-semibold text-gray-900 dark:text-dark-text-primary mb-2">
          Reclamação não encontrada
        </h4>
        <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-6">
          A reclamação que você está tentando acessar não foi encontrada ou não existe
          mais.
        </p>
        <button
          @click="handleBack"
          class="px-6 py-3 bg-brand text-white rounded-lg font-semibold hover:shadow-lg transition-all duration-200"
        >
          Voltar para a lista
        </button>
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
</template>

<script setup>
import { router } from "@inertiajs/vue3";
import { ref, reactive, computed } from "vue";
import {
  UserIcon,
  HashtagIcon,
  FlagIcon,
  UserGroupIcon,
  PaperAirplaneIcon,
  CheckIcon,
  PaperClipIcon,
  DocumentIcon,
  ClockIcon,
  InformationCircleIcon,
  CalendarIcon,
  PrinterIcon,
  ArrowDownTrayIcon,
  XMarkIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  ArrowLeftIcon,
} from "@heroicons/vue/24/outline";
import PriorityModal from "./PriorityModal.vue";
import ReassignModal from "./ReassignModal.vue";

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
  global: false,
  priority: false,
  reassign: false,
  sendToDirector: false,
  markComplete: false,
  print: false,
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

// Navegação
const handleBack = () => {
  // Voltar para o dashboard do gestor
  router.get(route("manager.dashboard"));
};

const handlePrint = () => {
  loading.print = true;
  setTimeout(() => {
    window.print();
    loading.print = false;
    showToast("Preparando para impressão...", "info");
  }, 1000);
};

// Funções auxiliares
const getTypeText = (type) => {
  const types = {
    complaint: "Reclamação",
    suggestion: "Sugestão",
  };
  return types[type] || type;
};

const getStatusText = (status) => {
  const statusTexts = {
    open: "Aberto",
    in_progress: "Em Progresso",
    pending_completion: "Solicitado Conclusão",
    closed: "Concluído",
  };
  return statusTexts[status] || status;
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString("pt-BR", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
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

const getLastUpdate = () => {
  if (!complaint.value?.activities?.length) return "Nunca";
  const lastActivity = complaint.value.activities[complaint.value.activities.length - 1];
  return formatRelativeTime(lastActivity.created_at);
};

const getDaysOpen = () => {
  if (!complaint.value?.created_at) return 0;
  const created = new Date(complaint.value.created_at);
  const now = new Date();
  return Math.floor((now - created) / (1000 * 60 * 60 * 24));
};

const getUserInitials = (name) => {
  return name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .toUpperCase()
    .substring(0, 2);
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
    router.patch(
      route("complaints.reassign", { grievance: complaint.value.id }),
      {
        technician_id: technicianId,
      },
      {
        preserveScroll: true,
        onSuccess: () => {
          const technician = technicians.value.find((t) => t.id === technicianId);
          complaint.value.technician = technician;
          showReassignModal.value = false;
          showToast("Técnico reatribuído com sucesso!", "success");
        },
        onError: () => {
          showToast("Erro ao reatribuir técnico", "error");
        },
      }
    );
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
</script>

<style scoped>
@media print {
  .no-print {
    display: none !important;
  }
}
</style>
