<!-- GrievanceActions.vue -->
<template>
  <div class="w-full bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6">
    <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4">
      Acções
    </h2>

    <!-- Verificar se complaint existe -->
    <div v-if="!complaint" class="text-center py-4 text-gray-500">
      Carregando dados...
    </div>

    <div v-else class="space-y-2">
      <!-- Comentar a Submissão (sem restrição) -->
      <button
        @click="$emit('open-modal', 'comment')"
        :disabled="loading.comment"
        class="w-full bg-blue-600 text-white px-4 py-3 rounded font-semibold hover:bg-blue-700 transition-all shadow-sm text-sm flex items-center justify-center gap-2"
      >
        <template v-if="loading.comment">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
          <span>Processando...</span>
        </template>
        <template v-else>
          <ChatBubbleLeftIcon class="h-4 w-4" />
          <span>Comentar a Submissão</span>
        </template>
      </button>

      <!-- Definir Prioridade (apenas Submetida ou Em Análise) -->
      <button
        @click="$emit('open-modal', 'priority')"
        :disabled="loading.priority || !canUpdatePriority"
        :class="[
          'w-full px-4 py-3 rounded font-semibold transition-all shadow-sm text-sm flex items-center justify-center gap-2',
          canUpdatePriority
            ? 'bg-brand text-white hover:bg-orange-700'
            : 'bg-gray-300 text-gray-500 cursor-not-allowed dark:bg-gray-700 dark:text-gray-400',
        ]"
      >
        <template v-if="loading.priority">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
          <span>Processando...</span>
        </template>
        <template v-else>
          <FlagIcon class="h-4 w-4" />
          <span>Definir Prioridade</span>
        </template>
      </button>

      <!-- Reatribuir Técnico (apenas Submetida, Em Análise, Atribuída) -->
      <button
        @click="$emit('open-modal', 'reassign')"
        :disabled="loading.reassign || !canReassignTechnician"
        :class="[
          'w-full px-4 py-3 rounded font-semibold transition-all shadow-sm text-sm flex items-center justify-center gap-2 border',
          canReassignTechnician
            ? 'bg-white dark:bg-dark-accent text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-brand'
            : 'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 border-gray-300 dark:border-gray-600 cursor-not-allowed',
        ]"
      >
        <template v-if="loading.reassign">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-brand"></div>
          <span>Processando...</span>
        </template>
        <template v-else>
          <UserGroupIcon class="h-4 w-4" />
          <span>Reatribuir Técnico</span>
        </template>
      </button>

      <!-- Enviar ao Director (apenas Submetida, Em Análise, Atribuída) - Ocultar se role for director -->
      <button
        v-if="showSendToDirectorButton"
        @click="handleEscalationClick"
        :disabled="loading.escalation || !canEscalate"
        :class="[
          'w-full px-4 py-3 rounded font-semibold transition-all shadow-sm text-sm flex items-center justify-center gap-2',
          isEscalated
            ? 'bg-red-600 text-white hover:bg-red-700'
            : 'bg-green-600 text-white hover:bg-green-700',
        ]"
      >
        <template v-if="loading.escalation">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
          <span>Processando...</span>
        </template>
        <template v-else>
          <PaperAirplaneIcon v-if="!isEscalated" class="h-4 w-4" />
          <XCircleIcon v-else class="h-4 w-4" />
          <span>{{
            isEscalated ? "Revogar o encaminhamento" : "Enviar ao Director"
          }}</span>
        </template>
      </button>

      <!-- Aprovar Conclusão (sempre visível, habilitado apenas quando status for pending_approval) -->
      <button
        @click="$emit('mark-complete')"
        :disabled="loading.markComplete || complaint.status !== 'pending_approval'"
        :class="[
          'w-full px-4 py-3 rounded font-semibold transition-all shadow-sm text-sm flex items-center justify-center gap-2',
          complaint.status === 'pending_approval' && !loading.markComplete
            ? 'bg-green-600 text-white hover:bg-green-700'
            : 'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed',
        ]"
      >
        <template v-if="loading.markComplete">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
          <span>A Processar...</span>
        </template>
        <template v-else>
          <CheckCircleIcon class="h-4 w-4" />
          <span>
            {{
              complaint.status === "pending_approval"
                ? "Marcar Concluído"
                : "A aguardar Aprovação"
            }}
          </span>
        </template>
      </button>

      <!-- Rejeitar Submissão (para outros status) -->
      <button
        v-if="complaint.status !== 'pending_approval' && complaint.status !== 'resolved'"
        @click="$emit('reject-submission')"
        :disabled="loading.rejectSubmission"
        class="w-full bg-red-600 text-white px-4 py-3 rounded font-semibold hover:bg-red-700 transition-all shadow-sm text-sm flex items-center justify-center gap-2"
      >
        <template v-if="loading.rejectSubmission">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
          <span>Processando...</span>
        </template>
        <template v-else>
          <XCircleIcon class="h-4 w-4" />
          <span>Rejeitar Submissão</span>
        </template>
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
} from "@heroicons/vue/24/outline";
import { computed } from "vue";
import { useAuth } from "@/Composables/useAuth";

const props = defineProps({
  complaint: {
    type: Object,
    required: true,
    default: () => ({}),
  },
  technicians: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Object,
    default: () => ({}),
  },
});

// Use o composable de auth
const { role, checkRole } = useAuth();

// Computed properties para validação de estado
const canReassignTechnician = computed(() => {
  if (!props.complaint || !props.complaint.status) return false;
  const allowedStatuses = ["submitted", "under_review", "assigned"];
  return allowedStatuses.includes(props.complaint.status);
});

const canUpdatePriority = computed(() => {
  if (!props.complaint || !props.complaint.status) return false;
  const allowedStatuses = ["submitted", "under_review"];
  return allowedStatuses.includes(props.complaint.status);
});

const canSendToDirector = computed(() => {
  if (!props.complaint || !props.complaint.status) return false;
  const allowedStatuses = ["submitted", "under_review", "assigned"];
  return allowedStatuses.includes(props.complaint.status);
});

// Mostrar botão "Enviar ao Director" apenas se o usuário NÃO for director
const showSendToDirectorButton = computed(() => {
  // Use checkRole para verificar se é director
  return !checkRole("director");
});

const handleEscalationClick = () => {
  if (isEscalated.value) {
    // Se já está escalado, emitir evento para revogar
    emit("revoke-escalation");
  } else {
    // Se não está escalado, emitir evento para abrir modal
    emit("open-modal", "sendToDirector");
  }
};

const isEscalated = computed(() => {
  if (!props.complaint) return false;
  return (
    props.complaint.escalated === true ||
    props.complaint.status === "escalated" ||
    (props.complaint.updates &&
      props.complaint.updates.some((u) => u.action_type === "escalated_to_director"))
  );
});

const canEscalate = computed(() => {
  if (!props.complaint || !props.complaint.status) return false;

  if (isEscalated.value) {
    // Pode revogar se for escalado
    return true;
  }

  // Pode enviar se não for escalado e estiver nos status permitidos
  const allowedStatuses = ["submitted", "under_review", "assigned"];
  return allowedStatuses.includes(props.complaint.status);
});

const emit = defineEmits([
  "open-modal",
  "mark-complete",
  "revoke-escalation",
  "reject-completion",
  "reject-submission",
  "refresh",
]);
</script>
