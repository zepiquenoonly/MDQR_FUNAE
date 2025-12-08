<template>
  <AppLayout :title="`Submissão #${submission.reference_number}`">
    <div class="max-w-full mx-auto">
      <!-- Cabeçalho -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <Link
              href="/director/complaints-overview"
              class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
            >
              <ArrowLeftIcon class="h-5 w-5" />
            </Link>
            <div>
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Submissão #{{ submission.reference_number }}
              </h1>
              <div class="flex items-center gap-2 mt-2">
                <span :class="getTypeBadgeClass(submission.type)">
                  {{ getTypeLabel(submission.type) }}
                </span>
                <span :class="getPriorityBadgeClass(submission.priority)">
                  {{ getPriorityLabel(submission.priority) }}
                </span>
                <span :class="getStatusBadgeClass(submission.status)">
                  {{ getStatusLabel(submission.status) }}
                </span>
                <!-- Badge para casos escalados -->
                <span
                  v-if="isEscalatedToDirector"
                  class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400"
                >
                  <PaperAirplaneIcon class="h-4 w-4 mr-1" />
                  Solicitado ao Director
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Conteúdo principal -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Detalhes -->
          <div class="glass p-6 rounded-xl">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
              Descrição
            </h2>
            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
              {{ submission.description }}
            </p>

            <div
              class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 grid grid-cols-2 gap-4"
            >
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Submetido por</p>
                <p class="font-medium">
                  {{ submission.user?.name || submission.contact_name || "Anónimo" }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                <p class="font-medium">
                  {{ submission.user?.email || submission.contact_email || "N/A" }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Data</p>
                <p class="font-medium">{{ formatDate(submission.created_at) }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Categoria</p>
                <p class="font-medium">{{ submission.category || "N/A" }}</p>
              </div>
            </div>
          </div>

          <!-- SECÇÃO DE INFORMAÇÕES DO ESCALAMENTO -->
          <div
            v-if="isEscalatedToDirector && escalationDetails"
            class="mt-6 p-4 border border-purple-200 dark:border-purple-700 bg-purple-50 dark:bg-purple-900/20 rounded-lg"
          >
            <div class="flex items-center gap-2 mb-4">
              <PaperAirplaneIcon class="h-6 w-6 text-purple-600 dark:text-purple-400" />
              <h3 class="text-lg font-bold text-purple-800 dark:text-purple-300">
                Solicitação do Gestor
              </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Quem escalou -->
              <div class="space-y-1">
                <p class="text-sm text-purple-700 dark:text-purple-400 font-medium">
                  Solicitado por:
                </p>
                <div
                  class="flex items-center gap-2 p-3 bg-white dark:bg-dark-secondary rounded-lg border"
                >
                  <UserCircleIcon class="h-5 w-5 text-gray-500" />
                  <div>
                    <span class="font-medium text-gray-800 dark:text-gray-200">
                      {{ escalationDetails.escalated_by?.name || "Gestor" }}
                    </span>
                    <p
                      v-if="escalationDetails.escalated_by?.email"
                      class="text-xs text-gray-500"
                    >
                      {{ escalationDetails.escalated_by.email }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Data do escalamento -->
              <div class="space-y-1">
                <p class="text-sm text-purple-700 dark:text-purple-400 font-medium">
                  Data da Solicitação:
                </p>
                <div
                  class="flex items-center gap-2 p-3 bg-white dark:bg-dark-secondary rounded-lg border"
                >
                  <CalendarIcon class="h-5 w-5 text-gray-500" />
                  <span class="font-medium text-gray-800 dark:text-gray-200">
                    {{ formatDateTime(escalationDetails.escalated_at) }}
                  </span>
                </div>
              </div>

              <!-- Motivo do escalamento -->
              <div class="space-y-1 md:col-span-2">
                <p class="text-sm text-purple-700 dark:text-purple-400 font-medium">
                  Motivo da Solicitação:
                </p>
                <div
                  class="p-4 bg-white dark:bg-dark-secondary rounded-lg border border-gray-200 dark:border-gray-700"
                >
                  <div class="flex items-start gap-2">
                    <ChatBubbleLeftIcon class="h-5 w-5 text-gray-500 mt-0.5" />
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                      {{
                        escalationDetails.escalation_reason ||
                        submission.escalation_reason ||
                        "Motivo não especificado"
                      }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Comentário do escalamento -->
              <div
                v-if="escalationDetails.escalation_comment || escalationDetails.comment"
                class="space-y-1 md:col-span-2"
              >
                <p class="text-sm text-purple-700 dark:text-purple-400 font-medium">
                  Comentário do Gestor:
                </p>
                <div
                  class="p-4 bg-white dark:bg-dark-secondary rounded-lg border border-gray-200 dark:border-gray-700"
                >
                  <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                    {{
                      escalationDetails.escalation_comment || escalationDetails.comment
                    }}
                  </p>
                </div>
              </div>
              <div class="md:col-span-2 flex justify-between items-center mt-4">
                <!-- Botão no canto direito -->
                <button
                  v-if="submission.priority === 'critical' || isEscalatedToDirector"
                  @click="openValidationModal"
                  class="flex items-center gap-2 px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors"
                >
                  <CheckBadgeIcon class="h-5 w-5" />
                  Validar
                </button>
              </div>
            </div>
          </div>

          <!-- Anexos -->
          <div class="glass p-6 rounded-xl">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Anexos</h2>
            <GrievanceAttachments :complaint="submission" />
          </div>

          <!-- Comentários -->
          <div v-if="comments?.length" class="glass p-6 rounded-xl">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
              Comentários e Validações
            </h2>
            <div class="space-y-4">
              <div
                v-for="comment in comments"
                :key="comment.id"
                class="border rounded-lg p-4"
                :class="{
                  'border-blue-200 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/20':
                    comment.type === 'director_only',
                  'border-green-200 dark:border-green-700 bg-green-50 dark:bg-green-900/20':
                    comment.type === 'public',
                  'border-gray-200 dark:border-gray-700': comment.type === 'internal',
                }"
              >
                <div class="flex justify-between items-start mb-2">
                  <div>
                    <div class="flex items-center gap-2">
                      <p class="font-medium">{{ comment.user?.name || "Sistema" }}</p>
                      <span
                        class="text-xs px-2 py-0.5 rounded-full"
                        :class="{
                          'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300':
                            comment.type === 'director_only',
                          'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300':
                            comment.type === 'public',
                          'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300':
                            comment.type === 'internal',
                        }"
                      >
                        {{ getCommentTypeLabel(comment.type) }}
                      </span>
                      <span v-if="comment.user?.role" class="text-xs text-gray-500">
                        ({{ comment.user.role }})
                      </span>
                    </div>
                    <p class="text-xs text-gray-500">{{ comment.user?.email || "" }}</p>
                  </div>
                  <p class="text-sm text-gray-500">
                    {{ formatDateTime(comment.created_at) }}
                  </p>
                </div>

                <!-- Conteúdo do comentário -->
                <div class="mt-3">
                  <!-- Se for uma validação -->
                  <div
                    v-if="comment.action_type?.includes('validation')"
                    class="mb-3 p-3 rounded bg-white dark:bg-gray-800 border"
                  >
                    <div class="flex items-center gap-2 mb-2">
                      <CheckBadgeIcon
                        class="h-5 w-5"
                        :class="{
                          'text-green-600 dark:text-green-400':
                            comment.metadata?.validation_status === 'approved',
                          'text-red-600 dark:text-red-400':
                            comment.metadata?.validation_status === 'rejected',
                          'text-yellow-600 dark:text-yellow-400':
                            comment.metadata?.validation_status === 'needs_revision',
                        }"
                      />
                      <span
                        class="font-medium"
                        :class="{
                          'text-green-700 dark:text-green-300':
                            comment.metadata?.validation_status === 'approved',
                          'text-red-700 dark:text-red-300':
                            comment.metadata?.validation_status === 'rejected',
                          'text-yellow-700 dark:text-yellow-300':
                            comment.metadata?.validation_status === 'needs_revision',
                        }"
                      >
                        {{ getValidationLabel(comment.metadata?.validation_status) }}
                      </span>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                      {{ comment.content }}
                    </p>
                  </div>

                  <!-- Comentário normal -->
                  <p v-else class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                    {{ comment.content }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Informações -->
          <div class="glass p-6 rounded-xl">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
              Informações da Submissão
            </h3>
            <div class="space-y-3">
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Projecto</p>
                <p class="font-medium">{{ submission.project?.name || "N/A" }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Atribuído a</p>
                <p class="font-medium">
                  {{ submission.assigned_to?.name || "Não atribuído" }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Localização</p>
                <p class="font-medium">
                  {{ submission.province }}, {{ submission.district }}
                </p>
                <p v-if="submission.location_details" class="text-xs text-gray-500 mt-1">
                  {{ submission.location_details }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Telefone</p>
                <p class="font-medium">{{ submission.contact_phone || "N/A" }}</p>
              </div>

              <!-- Informações do escalamento -->
              <div
                v-if="isEscalatedToDirector"
                class="pt-4 border-t border-gray-200 dark:border-gray-700"
              >
                <h4 class="text-sm font-medium text-purple-600 dark:text-purple-400 mb-2">
                  Informações da Solicitação
                </h4>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Status:</span>
                    <span class="font-medium text-purple-600 dark:text-purple-400">
                      Solicitação Recebida
                    </span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Por:</span>
                    <span class="font-medium">{{
                      escalationDetails.escalated_by?.name || "Gestor"
                    }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Data:</span>
                    <span class="font-medium">{{
                      formatShortDate(escalationDetails.escalated_at)
                    }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Atualizações -->
          <div class="glass p-6 rounded-xl">
            <GrievanceTimeline :complaint="submission" :timelineData="timelineData" />
          </div>

          <!-- Ações -->
          <div class="glass p-6 rounded-xl">
            <GrievanceActions
              :complaint="complaint"
              :technicians="technicians"
              @open-modal="handleOpenModal"
              @refresh="refreshComplaintData"
            />
          </div>
        </div>
      </div>

      <ToastNotification v-if="toast.show" :toast="toast" @close="toast.show = false" />

      <CommentModal
        :is-open="showCommentModal"
        :submission="submission"
        @close="showCommentModal = false"
        @submit="handleCommentSubmit"
      />

      <ValidationModal
        :is-open="showValidationModal"
        :submission="submission"
        @close="showValidationModal = false"
        @validate="handleValidationSubmit"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/ManagerLayout.vue";
import ValidationModal from "@/Components/Director/ValidationModal.vue";
import CommentModal from "@/Components/Director/CommentModal.vue";
import GrievanceTimeline from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceTimeline.vue";
import GrievanceActions from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceActions.vue";
import GrievanceAttachments from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceAttachments.vue";
import ToastNotification from "@/Components/Director/ToastNotification.vue";
import { useGrievanceDetail } from "@/Components/GestorReclamacoes/Composables/useGrievanceDetail";
import {
  ArrowLeftIcon,
  CheckBadgeIcon,
  PaperAirplaneIcon,
  UserCircleIcon,
  CalendarIcon,
  ChatBubbleLeftIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  complaint: Object,
  submission: {
    type: Object,
    required: true,
  },
  comments: {
    type: Array,
    default: () => [],
  },
  technicians: {
    type: Array,
    default: () => [],
  },
  managers: {
    type: Array,
    default: () => [],
  },
  projects: {
    type: Array,
    default: () => [],
  },
  timeline_data: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();
const showValidationModal = ref(false);
const showCommentModal = ref(false);

const toast = ref({
  show: false,
  type: "success",
  title: "",
  message: "",
});

const openCommentModal = () => {
  showCommentModal.value = true;
};

const closeCommentModal = () => {
  showCommentModal.value = false;
};

// Verificar se é um caso escalado para director
const isEscalatedToDirector = computed(() => {
  return (
    props.submission.escalated === true ||
    props.submission.escalation_details?.escalated === true ||
    props.submission.metadata?.is_escalated_to_director === true ||
    (props.submission.updates &&
      props.submission.updates.some(
        (update) => update.action_type === "escalated_to_director"
      ))
  );
});

const timelineData = computed(() => {
  // Se timeline_data foi passado diretamente, usá-lo
  if (props.timeline_data?.length > 0) {
    return props.timeline_data;
  }

  // Se updates existem na submission
  if (props.submission?.updates?.length > 0) {
    return props.submission.updates;
  }

  // Se activities existem na submission
  if (props.submission?.activities?.length > 0) {
    return props.submission.activities;
  }

  return [];
});

// Extrair detalhes do escalamento
const escalationDetails = computed(() => {
  if (!isEscalatedToDirector.value) {
    return {};
  }

  // Usar dados do escalation_details se disponíveis
  if (props.submission.escalation_details) {
    return props.submission.escalation_details;
  }

  // Fallback para dados antigos
  return {
    escalated_by: props.submission.escalated_by,
    escalated_at: props.submission.escalated_at,
    escalation_reason: props.submission.escalation_reason,
    // Tentar extrair dos updates
    escalation_comment: props.submission.updates?.find(
      (u) => u.action_type === "escalated_to_director"
    )?.comment,
    escalation_metadata: props.submission.updates?.find(
      (u) => u.action_type === "escalated_to_director"
    )?.metadata,
  };
});

// Helper para formatar chaves do metadata
const formatMetadataKey = (key) => {
  const keyMap = {
    escalated_by_name: "Solicitado por",
    escalated_at: "Data da solicitação",
    director_name: "Director atribuído",
    previous_technician_id: "Técnico anterior",
    priority_upgraded_to: "Prioridade alterada para",
  };
  return keyMap[key] || key.replace(/_/g, " ");
};

// Helper para formatar valores do metadata
const formatMetadataValue = (value) => {
  if (typeof value === "string") {
    if (value.includes("T") && value.includes(":")) {
      // É uma data ISO
      return formatShortDate(value);
    }
  }
  return value;
};

const getUpdateDescription = (update) => {
  if (update.description) return update.description;

  const actionMap = {
    escalated_to_director: "Submissão escalada para Director",
    status_changed: `Estado alterado: ${update.old_value || ""} → ${
      update.new_value || ""
    }`,
    priority_changed: `Prioridade alterada: ${update.old_value || ""} → ${
      update.new_value || ""
    }`,
    comment_added: "Comentário adicionado",
    assigned: "Caso atribuído",
    reassigned: "Caso reatribuído",
    created: "Submissão criada",
    resolved: "Caso resolvido",
  };

  return actionMap[update.action_type] || update.action_type.replace(/_/g, " ");
};

const getTypeBadgeClass = (type) => {
  const classes = {
    submission:
      "bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400",
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

const capitalizePortuguese = (text) => {
  if (!text) return "";

  // Converter para minúsculas primeiro
  let result = text.toLowerCase();

  // Capitalizar primeira letra da string
  result = result.charAt(0).toUpperCase() + result.slice(1);

  // Capitalizar primeira letra após ponto final
  result = result.replace(/\.\s*([a-zà-ú])/g, (match) => {
    return match.toUpperCase();
  });

  // Capitalizar pronomes pessoais e outros termos importantes
  const termsToCapitalize = [
    " eu ",
    " tu ",
    " ele ",
    " ela ",
    " nós ",
    " vós ",
    " eles ",
    " elas ",
    " sr. ",
    " sra. ",
    " dr. ",
    " dra. ",
    " eng. ",
    " prof. ",
    " profa. ",
    " min. ",
    " exmo. ",
    " exma. ",
    " v. exa. ",
  ];

  termsToCapitalize.forEach((term) => {
    const capitalizedTerm = term.toUpperCase();
    result = result.replace(new RegExp(term, "gi"), capitalizedTerm);
  });

  return result;
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
    escalated: "bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400",
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
    return date.toLocaleDateString("pt-PT");
  } catch (error) {
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
    return "Data/hora inválida";
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
    return "Data inválida";
  }
};

const {
  complaint,
  technicians,
  showPriorityModal,
  showReassignModal,
  loading,
  canMarkComplete,
  shouldShowResolution,

  // Métodos
  openPriorityModal,
  openReassignModal,
  closeModal,
  updatePriority,
  reassignTechnician,
  submitComment,
  markComplete,
  refreshComplaintData,
  handleOpenModal,
} = useGrievanceDetail(props);

const showToast = (message, type = "success", title = "") => {
  toast.value = {
    show: true,
    type,
    title: title || (type === "success" ? "Sucesso!" : "Erro!"),
    message,
  };
  setTimeout(() => {
    toast.value.show = false;
  }, 5000);
};

onMounted(() => {
  if (page.props.success) {
    showToast(page.props.success, "success");
    if (page.props.validation) {
      submission.value.director_validation = page.props.validation;
    }
    if (page.props.new_comment) {
      nextTick(() => {
        comments.value.unshift(page.props.new_comment);
      });
    }
  }
  if (page.props.errors) {
    Object.values(page.props.errors).forEach((error) => {
      showToast(error, "error");
    });
  }
});

const openValidationModal = () => {
  showValidationModal.value = true;
};

const handleValidationSubmit = async (formData) => {
  showValidationModal.value = false;
};

const handleCommentSubmit = async (formData) => {
  showCommentModal.value = false;
};

const getCommentTypeLabel = (type) => {
  const labels = {
    public: "Público",
    internal: "Interno",
    director_only: "Apenas Director",
  };
  return labels[type] || type;
};

const getValidationLabel = (status) => {
  const labels = {
    approved: "Aprovado pelo Director",
    rejected: "Rejeitado pelo Director",
    needs_revision: "Revisão Solicitada pelo Director",
  };
  return labels[status] || "Validação";
};

const assignToTechnician = () => {
  alert("Funcionalidade de atribuição será implementada");
};

const markAsResolved = () => {
  alert("Funcionalidade de marcar como resolvido será implementada");
};

const handleEscalatedCase = () => {
  alert(
    "Processar caso escalado para director - funcionalidade específica será implementada"
  );
};
</script>
