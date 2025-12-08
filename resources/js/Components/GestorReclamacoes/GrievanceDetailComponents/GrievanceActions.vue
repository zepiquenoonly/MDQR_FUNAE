<template>
  <div class="w-full bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6">
    <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4">
      Acções
    </h2>
    <div class="space-y-2">
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

      <!-- Enviar ao Director (apenas Submetida, Em Análise, Atribuída) -->
      <button
        @click="$emit('open-modal', 'sendToDirector')"
        :disabled="loading.sendToDirector || !canSendToDirector"
        :class="[
          'w-full px-4 py-3 rounded font-semibold transition-all shadow-sm text-sm flex items-center justify-center gap-2',
          canSendToDirector
            ? 'bg-green-600 text-white hover:bg-green-700'
            : 'bg-gray-300 text-gray-500 cursor-not-allowed dark:bg-gray-700 dark:text-gray-400',
        ]"
      >
        <template v-if="loading.sendToDirector">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
          <span>Processando...</span>
        </template>
        <template v-else>
          <PaperAirplaneIcon class="h-4 w-4" />
          <span>Enviar ao Director</span>
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
  canReassignTechnician: {
    type: Boolean,
    default: false,
  },
  canUpdatePriority: {
    type: Boolean,
    default: false,
  },
  canSendToDirector: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits([
  "open-modal",
  "mark-complete",
  "reject-completion",
  "reject-submission",
  "refresh",
]);
</script>
