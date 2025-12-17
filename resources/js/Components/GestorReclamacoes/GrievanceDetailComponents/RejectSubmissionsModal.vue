<template>
  <Modal :show="isOpen" @close="closeModal">
    <!-- Overlay escuro com opacidade -->
    <div
      class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
      @click="closeModal"
    ></div>

    <!-- Container para centralizar o modal -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
      <!-- Conteúdo do modal com largura responsiva -->
      <div
        class="w-full max-w-xl bg-white dark:bg-gray-800 rounded-xl shadow-2xl transform transition-all duration-300"
      >
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-xl font-bold text-gray-900 dark:text-dark-text-primary">
                Rejeitar Submissão
              </h2>
              <p class="text-base text-gray-700 dark:text-gray-300 mt-2">
                <span class="font-bold">#{{ complaint.reference_number }}</span>
              </p>
            </div>
            <!-- Botão de fechar (X) -->
            <button
              @click="closeModal"
              class="text-gray-400 hover:text-gray-500 dark:text-gray-300 dark:hover:text-gray-200 p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
              :disabled="loading"
            >
              <XMarkIcon class="h-6 w-6" />
            </button>
          </div>

          <div class="space-y-4">
            <!-- Motivos de rejeição -->
            <div>
              <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                Selecione o motivo da rejeição <span class="text-red-500">*</span>
              </h3>
              <div class="space-y-2">
                <div
                  v-for="reason in rejectionReasons"
                  :key="reason.value"
                  class="flex items-start"
                >
                  <input
                    :id="`reason-${reason.value}`"
                    v-model="selectedReason"
                    :value="reason.value"
                    type="radio"
                    class="h-4 w-4 mt-0.5 text-red-600 border-gray-300 focus:ring-red-500 dark:border-gray-600 dark:bg-gray-700"
                    :disabled="loading"
                  />
                  <label
                    :for="`reason-${reason.value}`"
                    class="ml-3 cursor-pointer text-sm text-gray-700 dark:text-gray-300"
                  >
                    <span class="font-medium">{{ reason.label }}</span>
                  </label>
                </div>
              </div>

              <!-- Mensagem de erro para motivo -->
              <p v-if="errors.reason" class="mt-1 text-xs text-red-600 dark:text-red-400">
                {{ errors.reason }}
              </p>
            </div>

            <!-- Comentário adicional -->
            <div>
              <label
                for="comment"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-8 mb-2"
              >
                Comentário adicional (obrigatório) <span class="text-red-500">*</span>
                <span class="text-xs text-gray-500 ml-1">
                  {{ comment.length }}/{{ maxCommentLength }} caracteres
                </span>
              </label>
              <textarea
                id="comment"
                v-model="comment"
                :disabled="loading"
                :maxlength="maxCommentLength"
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-0 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white transition-colors"
                :class="{
                  'border-red-300 dark:border-red-600': errors.comment,
                  'opacity-50 cursor-not-allowed': loading,
                }"
                placeholder="Descreva detalhadamente o motivo da rejeição."
              ></textarea>

              <!-- Mensagem de erro para comentário -->
              <p
                v-if="errors.comment"
                class="mt-1 text-xs text-red-600 dark:text-red-400"
              >
                {{ errors.comment }}
              </p>
            </div>

            <!-- Botões de ação -->
            <div
              class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700"
            >
              <button
                @click="closeModal"
                :disabled="loading"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Cancelar
              </button>
              <button
                @click="submitRejection"
                :disabled="loading"
                :class="[
                  'px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors flex items-center gap-2',
                  loading || !isFormValid
                    ? 'bg-red-400 dark:bg-red-800 cursor-not-allowed'
                    : 'bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800',
                ]"
              >
                <template v-if="loading">
                  <div
                    class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"
                  ></div>
                  <span>Processando...</span>
                </template>
                <template v-else>
                  <XCircleIcon class="h-4 w-4" />
                  <span>Confirmar Rejeição</span>
                </template>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import {
  XMarkIcon,
  ExclamationTriangleIcon,
  XCircleIcon,
} from "@heroicons/vue/24/outline";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true,
  },
  complaint: {
    type: Object,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["close", "submit"]);

// Estados do formulário
const selectedReason = ref("");
const comment = ref("");

// Erros de validação
const errors = ref({
  reason: "",
  comment: "",
});

// Constantes
const maxCommentLength = 1000;

// Motivos de rejeição pré-definidos
const rejectionReasons = [
  {
    value: "duplicate_submission",
    label: "Submissão Duplicada",
  },
  {
    value: "outside_scope",
    label: "Fora do Âmbito do Projecto",
  },
  {
    value: "insufficient_evidence",
    label: "Evidências Insuficientes",
  },
  {
    value: "unclear_description",
    label: "Descrição Pouco Clara",
  },
  {
    value: "already_resolved",
    label: "Já Resolvido",
  },
  {
    value: "inappropriate_content",
    label: "Conteúdo Inapropriado",
  },
];

// Computed properties
const isFormValid = computed(() => {
  return (
    selectedReason.value.trim() !== "" &&
    comment.value.trim() !== "" &&
    comment.value.trim().length >= 10
  );
});

// Métodos
const closeModal = () => {
  resetForm();
  emit("close");
};

const resetForm = () => {
  selectedReason.value = "";
  comment.value = "";
  errors.value = {
    reason: "",
    comment: "",
  };
};

const validateForm = () => {
  let isValid = true;
  errors.value = { reason: "", comment: "" };

  if (!selectedReason.value.trim()) {
    errors.value.reason = "Por favor, selecione um motivo de rejeição.";
    isValid = false;
  }

  if (!comment.value.trim()) {
    errors.value.comment = "Por favor, insira um comentário explicando a rejeição.";
    isValid = false;
  } else if (comment.value.trim().length < 10) {
    errors.value.comment = "O comentário deve ter pelo menos 10 caracteres.";
    isValid = false;
  } else if (comment.value.length > maxCommentLength) {
    errors.value.comment = `O comentário não pode exceder ${maxCommentLength} caracteres.`;
    isValid = false;
  }

  return isValid;
};

const submitRejection = () => {
  if (!validateForm()) {
    return;
  }

  // Encontrar o motivo selecionado
  const selectedReasonObj = rejectionReasons.find(
    (r) => r.value === selectedReason.value
  );

  const formData = {
    reason: selectedReason.value, // Para validação e lógica interna
    reason_label: selectedReasonObj?.label, // Para mostrar ao utente
    comment: comment.value,
    rejection_type: selectedReason.value, // Para compatibilidade com o backend
    internal_comment: comment.value, // Para compatibilidade
  };

  emit("submit", formData);
};

// Limpar erros quando o usuário começa a digitar/selecionar
watch(selectedReason, () => {
  if (errors.value.reason) {
    errors.value.reason = "";
  }
});

watch(comment, () => {
  if (errors.value.comment) {
    errors.value.comment = "";
  }
});

// Limpar formulário quando modal fecha
watch(
  () => props.isOpen,
  (isOpen) => {
    if (!isOpen) {
      resetForm();
    }
  }
);
</script>
