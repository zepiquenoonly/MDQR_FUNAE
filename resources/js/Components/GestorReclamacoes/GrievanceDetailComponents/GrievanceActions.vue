<template>
  <div class="w-full bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6">
    <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4">
      Acções
    </h2>

    <div v-if="!complaint" class="text-center py-4 text-gray-500">
      A carregar dados...
    </div>

    <div v-else class="space-y-2">
      <button
        @click="handleCommentClick"
        :disabled="isCommentButtonDisabled"
        :class="[
          'w-full px-4 py-3 rounded font-semibold transition-all shadow-sm text-sm flex items-center justify-center gap-2 relative mb-3',
          !isCommentButtonDisabled
            ? 'bg-blue-600 text-white hover:bg-blue-700'
            : 'bg-gray-300 text-gray-500 cursor-not-allowed dark:bg-gray-700 dark:text-gray-400',
        ]"
        :title="commentButtonTitle"
      >
        <template v-if="loading.comment">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
          <span>A processar...</span>
        </template>
        <template v-else>
          <ChatBubbleLeftIcon class="h-4 w-4" />
          <span>Comentários ({{ complaint.comments_count || 0 }})</span>
          <!-- Debug indicator -->
          <span
            v-if="isCommentButtonDisabled"
            class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full"
            title="Desativado: resolved ou rejected"
          ></span>
        </template>
      </button>

      <!-- Botões condicionais -->
      <template v-if="shouldShowActions">
        <!-- Definir Prioridade -->
        <button
          v-if="showPriorityButton"
          @click="handlePriorityClick"
          :disabled="isButtonDisabled('priority')"
          :class="[
            'w-full px-4 py-3 rounded font-semibold transition-all shadow-sm text-sm flex items-center justify-center gap-2',
            !isButtonDisabled('priority')
              ? 'bg-brand text-white hover:bg-orange-700'
              : 'bg-gray-300 text-gray-500 cursor-not-allowed dark:bg-gray-700 dark:text-gray-400',
          ]"
        >
          <template v-if="loading.priority">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
            <span>A processar...</span>
          </template>
          <template v-else>
            <FlagIcon class="h-4 w-4" />
            <span>Definir Prioridade</span>
          </template>
        </button>

        <!-- Reatribuir Técnico -->
        <button
          v-if="showReassignButton"
          @click="handleReassignClick"
          :disabled="isButtonDisabled('reassign')"
          :class="[
            'w-full px-4 py-3 rounded font-semibold transition-all shadow-sm text-sm flex items-center justify-center gap-2 border',
            !isButtonDisabled('reassign')
              ? 'bg-white dark:bg-dark-accent text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-brand'
              : 'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 border-gray-300 dark:border-gray-600 cursor-not-allowed',
          ]"
        >
          <template v-if="loading.reassign">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-brand"></div>
            <span>A processar...</span>
          </template>
          <template v-else>
            <UserGroupIcon class="h-4 w-4" />
            <span>Reatribuir Técnico</span>
          </template>
        </button>

        <!-- Enviar ao Director (apenas para Gestor quando não escalado) -->
        <button
          v-if="showSendToDirectorButton && !isEscalatedToDirector"
          @click="handleSendToDirectorClick"
          :disabled="isButtonDisabled('sendToDirector')"
          :class="[
            'w-full px-4 py-3 rounded font-semibold transition-all shadow-sm text-sm flex items-center justify-center gap-2 mb-3',
            !isButtonDisabled('sendToDirector')
              ? 'bg-green-600 text-white hover:bg-green-700'
              : 'bg-gray-300 text-gray-500 cursor-not-allowed dark:bg-gray-700 dark:text-gray-400',
          ]"
        >
          <template v-if="loading.sendToDirector">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
            <span>A processar...</span>
          </template>
          <template v-else>
            <PaperAirplaneIcon class="h-4 w-4" />
            <span>Enviar ao Director</span>
          </template>
        </button>

        <!-- Botão de Validação -->
        <button
          v-if="showValidationButton"
          @click="handleValidationClick"
          :disabled="isButtonDisabled('markComplete')"
          :class="[
            'w-full px-4 py-3 rounded font-semibold transition-all shadow-sm text-sm flex items-center justify-center gap-2 mb-3',
            !isButtonDisabled('markComplete')
              ? 'bg-green-600 text-white hover:bg-green-700'
              : 'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed',
          ]"
        >
          <template v-if="loading.markComplete || loading.submitValidation">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
            <span>A processar...</span>
          </template>
          <template v-else>
            <CheckCircleIcon class="h-4 w-4" />
            <span>{{ validationButtonText }}</span>
          </template>
        </button>

        <!-- Botão de Rejeição -->
        <button
          v-if="showRejectButton"
          @click="handleRejectClick"
          :disabled="isButtonDisabled('reject')"
          :class="[
            'w-full px-4 py-3 rounded font-semibold transition-all shadow-sm text-sm flex items-center justify-center gap-2',
            !isButtonDisabled('reject')
              ? 'bg-red-600 text-white hover:bg-red-700'
              : 'bg-gray-300 text-gray-500 cursor-not-allowed dark:bg-gray-700 dark:text-gray-400',
          ]"
        >
          <template v-if="loading.reject">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
            <span>A processar...</span>
          </template>
          <template v-else>
            <XCircleIcon class="h-4 w-4" />
            <span>Rejeitar Submissão</span>
          </template>
        </button>
      </template>

      <!-- Botão para Director responder à solicitação -->
      <button
        v-if="isDirector && isEscalatedToDirector && !hasDirectorValidation"
        @click="handleValidationClick"
        :class="[
          'w-full px-4 py-3 rounded font-semibold transition-all shadow-sm text-sm flex items-center justify-center gap-2 mb-3',
          'bg-purple-600 text-white hover:bg-purple-700',
        ]"
      >
        <PaperAirplaneIcon class="h-4 w-4" />
        <span>Responder à Solicitação do Gestor</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import {
  ChatBubbleLeftIcon,
  FlagIcon,
  UserGroupIcon,
  PaperAirplaneIcon,
  CheckCircleIcon,
  XCircleIcon,
  ClockIcon,
  UserIcon,
} from "@heroicons/vue/24/outline";
import { computed } from "vue";

const props = defineProps({
  complaint: {
    type: Object,
    required: true,
  },
  technicians: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Object,
    default: () => ({}),
  },
  user: {
    type: Object,
    default: () => ({}),
  },
  isPendingApproval: {
    type: Boolean,
    default: false,
  },
  isRejected: {
    type: Boolean,
    default: false,
  },
  isResolved: {
    type: Boolean,
    default: false,
  },
  isApproved: {
    type: Boolean,
    default: false,
  },
  isEscalatedToDirector: {
    type: Boolean,
    default: false,
  },
  hasDirectorValidation: {
    type: Boolean,
    default: false,
  },
  isDirector: {
    type: Boolean,
    default: false,
  },
  isManager: {
    type: Boolean,
    default: false,
  },
  hasDirectorAssumedCase: {
    type: Boolean,
    default: false,
  },
  hasDirectorCommentedAndReturned: {
    type: Boolean,
    default: false,
  },
  isWaitingDirectorIntervention: {
    type: Boolean,
    default: false,
  },
  isCaseAssumedByDirector: {
    type: Boolean,
    default: false,
  },
  isCaseReturnedToManager: {
    type: Boolean,
    default: false,
  },
  shouldShowActions: {
    type: Boolean,
    default: true,
  },
  canComment: {
    type: Boolean,
    require: true,
  },
});

const emit = defineEmits(["open-modal", "refresh"]);

// ========== COMPUTED PROPERTIES ==========

const validationButtonText = computed(() => {
  if (props.isDirector && props.isEscalatedToDirector && !props.hasDirectorValidation) {
    return "Validar Solicitação";
  } else if (props.isPendingApproval) {
    return "Validar Aprovação";
  } else {
    return "Validar";
  }
});

const showPriorityButton = computed(() => {
  return props.shouldShowActions;
});

const showReassignButton = computed(() => {
  return props.shouldShowActions;
});

const showRejectButton = computed(() => {
  return props.shouldShowActions && !props.isRejected;
});

const showValidationButton = computed(() => {
  if (props.isDirector) {
    return props.isCaseAssumedByDirector && props.isPendingApproval;
  }

  if (props.isManager) {
    return props.shouldShowActions && props.isPendingApproval;
  }

  return false;
});

const showSendToDirectorButton = computed(() => {
  // Apenas Gestor pode enviar ao Director
  if (!props.isManager) return false;

  // Não mostrar se já foi escalado
  if (props.isEscalatedToDirector) return false;

  // Não mostrar se caso está resolvido ou rejeitado
  if (props.isResolved || props.isRejected || props.isApproved) return false;

  return props.shouldShowActions;
});

// ========== FUNÇÕES DE CLIQUE ==========

const isCommentButtonDisabled = computed(() => {
  console.log("=== DEBUG isCommentButtonDisabled ===");
  console.log("loading.comment:", props.loading.comment);
  console.log("canComment (from props):", props.canComment);
  console.log("isResolved:", props.isResolved);
  console.log("isRejected:", props.isRejected);
  console.log("status:", props.complaint?.status);

  // Se está em loading, desabilita
  if (props.loading.comment) {
    console.log("Desabilitado porque está em loading");
    return true;
  }

  // Usa a prop canComment que vem do composable
  // (que deve retornar false apenas para resolved/rejected)
  if (!props.canComment) {
    console.log("Desabilitado porque canComment é false");
    return true;
  }

  console.log("Habilitado - pode comentar");
  return false;
});

const handleCommentClick = () => {
  if (isCommentButtonDisabled.value) {
    console.log("Botão de comentários desabilitado, ignorando clique");
    console.log(
      "Debug: canComment=",
      props.canComment,
      "loading.comment=",
      props.loading.comment
    );
    return;
  }

  console.log("Abrindo modal de comentários");
  emit("open-modal", "comment");
};

const handlePriorityClick = () => {
  if (!props.shouldShowActions) return;
  emit("open-modal", "priority");
};

const handleReassignClick = () => {
  if (!props.shouldShowActions) return;
  emit("open-modal", "reassign");
};

const handleSendToDirectorClick = () => {
  if (!props.shouldShowActions) return;
  emit("open-modal", "sendToDirector");
};

const handleValidationClick = () => {
  if (props.isDirector && props.isEscalatedToDirector && !props.hasDirectorValidation) {
    // Director respondendo à solicitação do gestor pela primeira vez
    emit("open-modal", "approvalDirector");
  } else if (props.isDirector && props.isCaseAssumedByDirector) {
    // Director com caso assumido validando aprovação
    emit("open-modal", "validateSubmission");
  } else if (props.isManager) {
    // Gestor validando aprovação de técnico
    emit("open-modal", "validateSubmission");
  }
};

const handleRejectClick = () => {
  if (!props.shouldShowActions) return;
  emit("open-modal", "reject");
};

// ========== FUNÇÃO PARA VERIFICAR SE BOTÃO ESTÁ DESABILITADO ==========

const isButtonDisabled = (buttonType) => {
  // NOTA: Botão de comentários NÃO usa esta função - tem sua própria lógica

  // Verificar se está em loading
  if (props.loading[buttonType]) return true;

  // Se não deve mostrar ações, desabilita outros botões
  if (!props.shouldShowActions) return true;

  // Verificar estado final para outros botões
  if (props.isResolved || props.isRejected || props.isApproved) return true;

  return false;
};
</script>
