<!-- @/Components/Director/CommentModal.vue -->
<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto">
    <div
      class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
    >
      <!-- Overlay -->
      <div
        class="fixed inset-0 transition-opacity"
        aria-hidden="true"
        @click="handleClose"
      >
        <div class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"></div>
      </div>

      <!-- Modal Container -->
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true"
        >&#8203;</span
      >

      <!-- Modal Content -->
      <div
        class="max-h-[98vh] inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
      >
        <!-- Header -->
        <div
          class="bg-gray-50 dark:bg-gray-900 px-6 py-4 border-b border-gray-200 dark:border-gray-700"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                <ChatBubbleLeftIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  Adicionar Comentário
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  #{{ submission.reference_number }} -
                  {{ submission.user?.name || "Submissão" }}
                </p>
              </div>
            </div>
            <button
              @click="handleClose"
              class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            >
              <XMarkIcon class="h-5 w-5" />
            </button>
          </div>
        </div>

        <!-- Body -->
        <div class="overflow-y-auto max-h-[calc(97vh-200px)]">
          <div class="px-6 py-5">
            <!-- Tipo de comentário -->
            <div class="mb-6">
              <label
                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
              >
                Tipo de Comentário
              </label>
              <div class="flex gap-4">
                <label class="inline-flex items-center">
                  <input
                    type="radio"
                    v-model="commentType"
                    value="internal"
                    class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                  />
                  <span class="ml-2 text-sm text-gray-700 dark:text-gray-300"
                    >Interno (gestores e técnicos)</span
                  >
                </label>
                <label class="inline-flex items-center">
                  <input
                    type="radio"
                    v-model="commentType"
                    value="public"
                    class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                  />
                  <span class="ml-2 text-sm text-gray-700 dark:text-gray-300"
                    >Público (visível para o utente)</span
                  >
                </label>
              </div>
            </div>

            <!-- Editor de Texto Rico -->
            <div class="mb-6">
              <label
                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
              >
                Comentário <span class="text-red-500">*</span>
              </label>
              <RichTextEditor
                v-model="commentContent"
                :max-length="5000"
                :placeholder="'Digite seu comentário detalhado aqui...'"
                class="mb-3"
              />

              <div
                class="flex justify-between items-center mt-4 text-sm text-gray-500 dark:text-gray-400"
              >
                <div class="flex items-center gap-4">
                  <div class="flex items-center gap-1">
                    <InformationCircleIcon class="h-4 w-4" />
                    <span>Dica: Use formatação para destacar pontos importantes</span>
                  </div>
                </div>
                <div>
                  <span :class="commentContent.length > 4900 ? 'text-yellow-500' : ''">
                    {{ 5000 - commentContent.length }} caracteres restantes
                  </span>
                </div>
              </div>
            </div>

            <!-- Anexar arquivos -->
            <div class="mb-6">
              <label
                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
              >
                Anexar Arquivos (opcional)
              </label>
              <div
                class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center"
              >
                <div v-if="!files.length" class="space-y-2">
                  <DocumentArrowUpIcon class="h-12 w-12 mx-auto text-gray-400" />
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    Arraste e solte arquivos ou
                  </p>
                  <input
                    type="file"
                    ref="fileInput"
                    @change="handleFileSelect"
                    multiple
                    class="hidden"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt"
                  />
                  <button
                    @click="$refs.fileInput.click()"
                    type="button"
                    class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors"
                  >
                    Selecione arquivos
                  </button>
                  <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                    PDF, Word, Excel, Imagens, TXT (máx. 10MB cada)
                  </p>
                </div>

                <div v-else class="space-y-3">
                  <div
                    v-for="(file, index) in files"
                    :key="index"
                    class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 rounded-lg p-3"
                  >
                    <div class="flex items-center gap-3">
                      <DocumentIcon class="h-5 w-5 text-gray-400" />
                      <div>
                        <p
                          class="text-sm font-medium text-gray-900 dark:text-gray-200 truncate max-w-xs"
                        >
                          {{ file.name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                          {{ formatFileSize(file.size) }}
                        </p>
                      </div>
                    </div>
                    <button
                      @click="removeFile(index)"
                      type="button"
                      class="p-1 text-gray-400 hover:text-red-500 dark:hover:text-red-400 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20"
                    >
                      <XMarkIcon class="h-4 w-4" />
                    </button>
                  </div>

                  <button
                    @click="$refs.fileInput.click()"
                    type="button"
                    class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300"
                  >
                    + Adicionar mais arquivos
                  </button>
                </div>
              </div>
            </div>

            <!-- Notificar utilizador -->
            <div
              v-if="commentType === 'public'"
              class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
            >
              <div class="flex items-start gap-3">
                <BellAlertIcon class="h-5 w-5 text-blue-500 dark:text-blue-400 mt-0.5" />
                <div class="flex-1">
                  <label class="inline-flex items-center">
                    <input
                      type="checkbox"
                      v-model="notifyUser"
                      class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    />
                    <span
                      class="ml-2 text-sm font-medium text-blue-700 dark:text-blue-300"
                    >
                      Notificar o utilizador por email sobre este comentário
                    </span>
                  </label>
                  <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                    O utilizador receberá um email com o conteúdo do comentário (exceto
                    partes internas)
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div
            class="bg-gray-50 dark:bg-gray-900 px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3"
          >
            <button
              type="button"
              @click="handleClose"
              class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors"
            >
              Cancelar
            </button>
            <button
              type="button"
              @click="handleSubmit"
              :disabled="!commentContent.trim() || isSubmitting"
              :class="[
                'px-6 py-2 text-sm font-medium text-white rounded-lg transition-colors flex items-center gap-2',
                !commentContent.trim() || isSubmitting
                  ? 'bg-blue-400 dark:bg-blue-600 cursor-not-allowed'
                  : 'bg-blue-500 dark:bg-blue-600 hover:bg-blue-600 dark:hover:bg-blue-700',
              ]"
            >
              <template v-if="isSubmitting">
                <svg
                  class="animate-spin h-4 w-4 text-white"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </svg>
                Enviando...
              </template>
              <template v-else>
                <PaperAirplaneIcon class="h-4 w-4" />
                Enviar Comentário
              </template>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import {
  XMarkIcon,
  ChatBubbleLeftIcon,
  PaperAirplaneIcon,
  DocumentIcon,
  DocumentArrowUpIcon,
  BellAlertIcon,
  InformationCircleIcon,
  ExclamationCircleIcon,
} from "@heroicons/vue/24/outline";
import RichTextEditor from "@/Components/Director/RichTextEditor.vue";

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true,
  },
  submission: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["close", "submit"]);

// Use useForm para gerenciar o formulário
const form = useForm({
  content: "",
  comment_type: "internal",
  notify_manager: true,
  notify_technician: true,
  notify_user: false,
  attachments: [],
});

const isSubmitting = ref(false);
const fileInput = ref(null);
const files = ref([]);
const commentContent = ref("");
const commentType = ref("internal");
const notifyUser = ref(false);

const handleClose = () => {
  if (!isSubmitting.value) {
    emit("close");
    resetForm();
  }
};

const handleSubmit = async () => {
  if (!commentContent.value.trim() || isSubmitting.value) return;

  isSubmitting.value = true;
  
  // Sync refs to form
  form.content = commentContent.value;
  form.comment_type = commentType.value;
  form.notify_user = notifyUser.value;
  form.attachments = files.value;

  try {
    await form.post(`/director/complaints/${props.submission.id}/comment`, {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        // Resetar formulário
        form.reset();
        form.clearErrors();
        resetForm();

        // Fechar modal
        emit("close");

        // Emitir evento de sucesso
        emit("submit", form.data());
      },
      onError: (errors) => {
        console.error("Erros de validação:", errors);
      },
      onFinish: () => {
        isSubmitting.value = false;
      },
    });
  } catch (error) {
    console.error("Erro ao enviar comentário:", error);
    isSubmitting.value = false;
  }
};

const handleFileSelect = (event) => {
  const selectedFiles = Array.from(event.target.files);

  // Validar tamanho dos arquivos (10MB max)
  const validFiles = selectedFiles.filter((file) => {
    if (file.size > 10 * 1024 * 1024) {
      alert(`O arquivo "${file.name}" excede o limite de 10MB`);
      return false;
    }
    return true;
  });

  files.value = [...files.value, ...validFiles];

  // Limpar input para permitir selecionar os mesmos arquivos novamente
  if (fileInput.value) {
    fileInput.value.value = "";
  }
};

const removeFile = (index) => {
  files.value.splice(index, 1);
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return "0 Bytes";
  const k = 1024;
  const sizes = ["Bytes", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};

const resetForm = () => {
  commentContent.value = "";
  commentType.value = "internal";
  notifyUser.value = false;
  files.value = [];
  isSubmitting.value = false;

  if (fileInput.value) {
    fileInput.value.value = "";
  }
};

// Watch para resetar quando modal fechar
watch(
  () => props.isOpen,
  (isOpen) => {
    if (isOpen) {
      form.clearErrors();
    } else {
      resetForm();
    }
  }
);
</script>

<style scoped>
/* Animações para o modal */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: translateY(-20px) scale(0.95);
}

.modal-enter-to,
.modal-leave-from {
  opacity: 1;
  transform: translateY(0) scale(1);
}
</style>
