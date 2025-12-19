<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto">
    <div
      class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
    >
      <!-- Overlay de fundo -->
      <div class="fixed inset-0 transition-opacity" aria-hidden="true">
        <div
          class="absolute inset-0 bg-gray-500 opacity-75 dark:bg-gray-900 dark:opacity-90"
        ></div>
      </div>

      <!-- Modal -->
      <div
        class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
      >
        <!-- Cabeçalho -->
        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div
              class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 dark:bg-amber-900/30 sm:mx-0 sm:h-10 sm:w-10"
            >
              <ExclamationTriangleIcon
                class="h-6 w-6 text-amber-600 dark:text-amber-400"
              />
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3 class="text-lg leading-6 font-bold text-gray-900 dark:text-white">
                Reabrir Submissão
              </h3>
              <div class="mt-2">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                  Tem a certeza que deseja reabrir esta submissão? Ao reabrir:
                </p>
                <ul
                  class="mt-2 text-sm text-gray-600 dark:text-gray-300 list-disc list-inside space-y-1"
                >
                  <li>A submissão retornará ao estado "Pendente"</li>
                  <li>O motivo da rejeição será removido</li>
                  <li>As ações normais estarão disponíveis novamente</li>
                  <li>Será registada uma nova entrada na timeline</li>
                </ul>
                <div
                  class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg"
                >
                  <div class="flex items-center gap-2 text-amber-700 dark:text-amber-300">
                    <InformationCircleIcon class="h-5 w-5" />
                    <span class="font-medium">Atenção:</span>
                  </div>
                  <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                    Esta ação não pode ser desfeita automaticamente. A submissão será
                    tratada como nova.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Campos do formulário -->
        <div class="px-4 pb-3">
          <div class="space-y-4">
            <!-- Motivo da reabertura -->
            <div>
              <label
                for="reopen-reason"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
              >
                Motivo da Reabertura *
              </label>
              <input
                type="text"
                id="reopen-reason"
                v-model="formData.reason"
                placeholder="Ex: Nova informação disponível, erro na rejeição, etc."
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white"
                required
              />
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Explique brevemente o motivo da reabertura desta submissão.
              </p>
            </div>

            <!-- Comentário adicional -->
            <div>
              <label
                for="reopen-comment"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
              >
                Comentário Adicional (opcional)
              </label>
              <textarea
                id="reopen-comment"
                v-model="formData.comment"
                rows="3"
                placeholder="Adicione qualquer informação adicional relevante..."
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white"
              ></textarea>
            </div>

            <!-- Opções de notificação -->
            <div class="space-y-2">
              <div class="flex items-center">
                <input
                  id="notify-user"
                  v-model="formData.notify_user"
                  type="checkbox"
                  class="h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700"
                />
                <label
                  for="notify-user"
                  class="ml-2 block text-sm text-gray-700 dark:text-gray-300"
                >
                  Notificar o utilizador sobre a reabertura
                </label>
              </div>

              <div class="flex items-center">
                <input
                  id="notify-technician"
                  v-model="formData.notify_technician"
                  type="checkbox"
                  class="h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700"
                />
                <label
                  for="notify-technician"
                  class="ml-2 block text-sm text-gray-700 dark:text-gray-300"
                >
                  Notificar o técnico responsável
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Rodapé com botões -->
        <div
          class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse"
        >
          <button
            type="button"
            @click="submitReopen"
            :disabled="loading || !formData.reason"
            :class="[
              'w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white sm:ml-3 sm:w-auto sm:text-sm',
              !loading && formData.reason
                ? 'bg-amber-600 hover:bg-amber-700 focus:ring-2 focus:ring-offset-2 focus:ring-amber-500'
                : 'bg-amber-400 cursor-not-allowed',
            ]"
          >
            <template v-if="loading">
              <div
                class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"
              ></div>
              A processar...
            </template>
            <template v-else>
              <CheckIcon class="h-4 w-4 mr-2" />
              Confirmar Reabertura
            </template>
          </button>
          <button
            type="button"
            @click="closeModal"
            :disabled="loading"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-600 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
          >
            <XMarkIcon class="h-4 w-4 mr-2" />
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from "vue";
import {
  ExclamationTriangleIcon,
  InformationCircleIcon,
  CheckIcon,
  XMarkIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true,
    default: false,
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

// Dados do formulário
const formData = reactive({
  reason: "",
  comment: "",
  notify_user: true,
  notify_technician: true,
});

// Resetar formulário quando modal abre
watch(
  () => props.isOpen,
  (isOpen) => {
    if (isOpen) {
      resetForm();
    }
  }
);

const resetForm = () => {
  formData.reason = "";
  formData.comment = "";
  formData.notify_user = true;
  formData.notify_technician = true;
};

const closeModal = () => {
  if (!props.loading) {
    emit("close");
  }
};

const submitReopen = () => {
  if (!formData.reason.trim()) {
    return;
  }

  emit("submit", {
    ...formData,
    reason: formData.reason.trim(),
    comment: formData.comment.trim() || null,
  });
};
</script>
