<template>
  <transition name="modal-fade">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-end justify-end sm:items-center"
    >
      <!-- Overlay -->
      <div
        class="absolute inset-0 bg-black bg-opacity-40 backdrop-blur-sm"
        @click="handleClose"
      ></div>

      <!-- Modal Container -->
      <div
        class="relative w-full sm:w-[500px] h-full sm:h-[85vh] bg-white dark:bg-gray-900 rounded-t-2xl sm:rounded-l-2xl shadow-2xl flex flex-col overflow-hidden transform transition-all duration-300 ease-out modal-right"
        :class="{
          'translate-y-0 opacity-100': isOpen,
          'translate-y-full sm:translate-x-full opacity-0': !isOpen,
        }"
      >
        <!-- Header minimalista -->
        <div
          class="sticky top-0 z-10 flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
        >
          <div class="flex items-center gap-3">
            <div
              class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center"
            >
              <ChatBubbleLeftIcon class="h-5 w-5 text-orange-600 dark:text-orange-400" />
            </div>
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white text-lg">
                Comentários da Submissão
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ filteredComments.length }} comentários
              </p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <!-- Botão para recarregar comentários -->
            <button
              @click="refreshComments"
              class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-colors"
              :title="loadingRefresh ? 'Recarregando...' : 'Recarregar comentários'"
              :disabled="loadingRefresh"
            >
              <ArrowPathIcon
                class="h-5 w-5 text-gray-500 dark:text-gray-400"
                :class="{ 'animate-spin': loadingRefresh }"
              />
            </button>
            <button
              @click="handleClose"
              class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-colors"
              aria-label="Fechar"
            >
              <XMarkIcon class="h-5 w-5 text-gray-500 dark:text-gray-400" />
            </button>
          </div>
        </div>

        <!-- Área de mensagens -->
        <div
          v-if="filteredComments.length > 0"
          class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 bg-gray-50 dark:bg-gray-800/50"
          ref="messagesContainer"
        >
          <!-- Mensagens -->
          <div class="space-y-3">
            <div
              v-for="comment in sortedComments"
              :key="comment.id"
              class="flex items-start gap-3"
              :class="{
                'justify-end': isCurrentUserComment(comment),
              }"
            >
              <!-- Avatar do remetente -->
              <div
                v-if="!isCurrentUserComment(comment)"
                class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold flex-shrink-0"
                :class="getUserAvatarClass(comment)"
                :title="getUserDisplayName(comment)"
              >
                {{ getUserInitials(comment.user?.name) }}
              </div>

              <!-- Mensagem -->
              <div
                :class="[
                  'max-w-[70%]',
                  isCurrentUserComment(comment) ? 'order-first' : '',
                ]"
              >
                <!-- Cabeçalho da mensagem -->
                <div
                  v-if="!isCurrentUserComment(comment)"
                  class="flex items-center gap-2 mb-1"
                >
                  <span class="font-medium text-sm text-gray-700 dark:text-gray-300">
                    {{ getUserDisplayName(comment) }}
                  </span>
                  <span
                    v-if="comment.user?.role"
                    class="text-xs text-gray-500 dark:text-gray-400"
                  >
                    ({{ getRoleLabel(comment.user.role) }})
                  </span>
                </div>

                <!-- Conteúdo da mensagem -->
                <div
                  :class="[
                    'rounded-2xl px-4 py-3 shadow-sm',
                    isCurrentUserComment(comment)
                      ? 'bg-orange-500 text-white rounded-tr-sm'
                      : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-tl-sm',
                  ]"
                >
                  <!-- Conteúdo do comentário -->
                  <p class="text-sm whitespace-pre-line break-words">
                    {{ comment.content || comment.comment }}
                  </p>

                  <!-- Anexos (se houver) -->
                  <div
                    v-if="comment.attachments && comment.attachments.length > 0"
                    class="mt-2 pt-2 border-t border-opacity-20"
                    :class="
                      isCurrentUserComment(comment)
                        ? 'border-white'
                        : 'border-gray-300 dark:border-gray-600'
                    "
                  >
                    <p class="text-xs mb-1">Anexos:</p>
                    <div class="flex flex-wrap gap-2">
                      <div
                        v-for="attachment in comment.attachments"
                        :key="attachment.id"
                        class="flex items-center gap-1 text-xs p-1 bg-black bg-opacity-10 rounded"
                      >
                        <PaperClipIcon class="h-3 w-3" />
                        <a
                          :href="attachment.url"
                          target="_blank"
                          class="hover:underline truncate max-w-[100px]"
                          @click.prevent="downloadAttachment(attachment)"
                        >
                          {{ attachment.name || attachment.file_name }}
                        </a>
                        <span class="text-xs opacity-75">
                          ({{ formatFileSize(attachment.size) }})
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Data e hora -->
                <p
                  class="text-xs mt-1"
                  :class="
                    isCurrentUserComment(comment)
                      ? 'text-right text-gray-500 dark:text-gray-400'
                      : 'ml-1 text-gray-500 dark:text-gray-400'
                  "
                >
                  {{ formatCommentTime(comment.created_at) }}
                </p>
              </div>

              <!-- Avatar do usuário atual -->
              <div
                v-if="isCurrentUserComment(comment)"
                class="w-8 h-8 rounded-full bg-orange-500 flex items-center justify-center text-white text-xs font-semibold flex-shrink-0"
                :title="currentUserName"
              >
                {{ currentUserInitials }}
              </div>
            </div>
          </div>
        </div>

        <!-- Mensagem quando não há comentários -->
        <div
          v-else
          class="flex-1 flex flex-col items-center justify-center p-6 bg-gray-50 dark:bg-gray-800/50"
        >
          <ChatBubbleLeftIcon class="h-12 w-12 text-gray-400 dark:text-gray-500 mb-3" />
          <p class="text-gray-500 dark:text-gray-400 text-center">
            Ainda não há comentários neste caso.<br />
            Seja o primeiro a comentar!
          </p>
        </div>

        <!-- Campo de entrada de mensagem -->
        <div
          class="sticky bottom-0 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4"
        >
          <div class="flex items-end gap-3">
            <!-- Área de texto -->
            <div class="flex-1 relative">
              <textarea
                v-model="form.comment"
                ref="textareaRef"
                rows="1"
                @input="autoResize"
                @keydown.enter.exact.prevent="handleEnter"
                class="w-full px-4 py-3 pr-12 text-base border border-gray-300 dark:border-gray-600 rounded-2xl focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 resize-none max-h-32 overflow-y-auto"
                placeholder="Digite sua mensagem..."
                :class="{ 'border-red-500': errors.comment }"
              ></textarea>
            </div>

            <!-- Botão de envio -->
            <button
              @click="submit"
              :disabled="loading || !form.comment.trim()"
              class="p-3 bg-orange-500 text-white rounded-full hover:bg-orange-600 disabled:bg-gray-300 disabled:cursor-not-allowed dark:disabled:bg-gray-700 transition-colors flex-shrink-0"
              :class="{
                'animate-pulse': loading,
              }"
              aria-label="Enviar mensagem"
            >
              <template v-if="loading">
                <div
                  class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"
                ></div>
              </template>
              <template v-else>
                <PaperAirplaneIcon class="h-5 w-5" />
              </template>
            </button>
          </div>

          <!-- Erro de validação -->
          <p
            v-if="errors.comment"
            class="mt-2 text-sm text-red-600 dark:text-red-400 px-1"
          >
            {{ errors.comment }}
          </p>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, reactive, computed, nextTick, onMounted, watch } from "vue";
import {
  XMarkIcon,
  ChatBubbleLeftIcon,
  InformationCircleIcon,
  PaperAirplaneIcon,
  PaperClipIcon,
  ArrowPathIcon,
} from "@heroicons/vue/24/outline";
import { usePage } from "@inertiajs/vue3";
import { useAuth } from "@/Composables/useAuth";

const emit = defineEmits(["close", "submit", "mark-read", "comment-added"]);
const props = defineProps({
  complaint: Object,
  isOpen: {
    type: Boolean,
    default: false,
  },
  comments: {
    type: Array,
    default: () => [],
  },
});

const { checkRole, role, user } = useAuth();
const page = usePage();
const textareaRef = ref(null);
const messagesContainer = ref(null);
const fileInput = ref(null);

const loading = ref(false);
const loadingRefresh = ref(false);
const errors = reactive({});
const showAttachments = ref(false);
const selectedFiles = ref([]);

// Armazenar comentários localmente
const localComments = ref([]);

// Inicializar comentários locais
watch(
  () => props.comments,
  (newComments) => {
    console.log("Comentários recebidos no modal:", newComments?.length);
    localComments.value = [...(newComments || [])];
  },
  { immediate: true, deep: true }
);

// Computed properties para verificar roles
const isDirector = computed(() => checkRole("director"));
const isManager = computed(() => checkRole("manager"));
const isTechnician = computed(() => checkRole("technician"));

// Informações do usuário atual
const currentUser = computed(() => user.value || page.props.auth?.user || {});
const currentUserName = computed(() => currentUser.value?.name || "Utilizador");
const currentUserInitials = computed(() => {
  const name = currentUserName.value;
  return name
    .split(" ")
    .map((word) => word[0])
    .join("")
    .toUpperCase()
    .substring(0, 2);
});

const form = reactive({
  comment: "",
});

// FILTRAR COMENTÁRIOS: Apenas comentários relevantes
const filteredComments = computed(() => {
  if (!localComments.value || !props.complaint) return [];

  return localComments.value.filter((comment) => {
    // Ocultar validações e status
    if (
      comment.action_type?.includes("validation") ||
      comment.action_type?.includes("director_validation") ||
      comment.type === "director_validation" ||
      comment.action_type?.includes("status_") ||
      comment.action_type === "rejected" ||
      comment.action_type === "approved" ||
      comment.action_type === "escalated"
    ) {
      return false;
    }

    // Verificar usuário do comentário
    const commentUser = comment.user || {};
    const commentUserId = commentUser.id || comment.user_id;
    const assignedToId = props.complaint.assigned_to?.id || props.complaint.assigned_to;

    // Permitir comentários de Gestor, Director e Técnico atribuído
    const isManagerComment =
      commentUser.role === "manager" || commentUser.role === "Gestor";
    const isDirectorComment =
      commentUser.role === "director" || commentUser.role === "Director";
    const isAssignedTechnicianComment = commentUserId === assignedToId;

    return isManagerComment || isDirectorComment || isAssignedTechnicianComment;
  });
});

// Comentários ordenados por data (mais recente no final)
const sortedComments = computed(() => {
  return [...filteredComments.value].sort((a, b) => {
    return new Date(a.created_at) - new Date(b.created_at);
  });
});

// Verificar se o comentário é do usuário atual
const isCurrentUserComment = (comment) => {
  const commentUser = comment.user || {};
  const commentUserId = commentUser.id || comment.user_id;
  return commentUserId === currentUser.value?.id;
};

// Obter nome de exibição do usuário
const getUserDisplayName = (comment) => {
  const commentUser = comment.user || {};

  if (isCurrentUserComment(comment)) {
    return "Você";
  }

  if (commentUser.name) {
    return commentUser.name;
  }

  if (commentUser.username) {
    return commentUser.username;
  }

  return "Utilizador";
};

// Obter label do role
const getRoleLabel = (roleName) => {
  if (!roleName) return "";

  const labels = {
    manager: "Gestor",
    director: "Director",
    technician: "Técnico",
    Gestor: "Gestor",
    Director: "Director",
    Técnico: "Técnico",
  };

  return labels[roleName] || roleName;
};

// Obter iniciais do usuário
const getUserInitials = (name) => {
  if (!name) return "U";
  return name
    .split(" ")
    .map((word) => word[0])
    .join("")
    .toUpperCase()
    .substring(0, 2);
};

// Obter classe CSS para o avatar baseado no role
const getUserAvatarClass = (comment) => {
  const commentUser = comment.user || {};
  const userRole = commentUser.role?.toLowerCase();

  if (userRole === "director") {
    return "bg-purple-500";
  }

  if (userRole === "manager" || userRole === "gestor") {
    return "bg-blue-500";
  }

  if (userRole === "technician" || userRole === "técnico") {
    return "bg-green-500";
  }

  return "bg-gray-500";
};

// Formatador de data/hora para comentários
const formatCommentTime = (dateString) => {
  if (!dateString) return "";
  try {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffHours = diffMs / (1000 * 60 * 60);

    if (diffHours < 24) {
      return date.toLocaleTimeString("pt-PT", {
        hour: "2-digit",
        minute: "2-digit",
      });
    }

    if (diffHours < 168) {
      return date.toLocaleDateString("pt-PT", {
        weekday: "short",
        hour: "2-digit",
        minute: "2-digit",
      });
    }

    return date.toLocaleString("pt-PT", {
      day: "2-digit",
      month: "2-digit",
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch (error) {
    return "";
  }
};

// Formatador de tamanho de arquivo
const formatFileSize = (bytes) => {
  if (!bytes) return "0 Bytes";
  if (bytes === 0) return "0 Bytes";
  const k = 1024;
  const sizes = ["Bytes", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};

// Auto-resize do textarea
const autoResize = () => {
  nextTick(() => {
    if (textareaRef.value) {
      textareaRef.value.style.height = "auto";
      textareaRef.value.style.height =
        Math.min(textareaRef.value.scrollHeight, 128) + "px";
    }
  });
};

// Enviar com Enter (sem Shift)
const handleEnter = (e) => {
  if (!e.shiftKey && form.comment.trim()) {
    e.preventDefault();
    submit();
  }
};

// Alternar anexos
const toggleAttachment = () => {
  showAttachments.value = !showAttachments.value;
};

// Acionar input de arquivo
const triggerFileInput = () => {
  if (fileInput.value) {
    fileInput.value.click();
  }
};

// Manipular seleção de arquivos
const handleFileSelect = (event) => {
  const files = Array.from(event.target.files);

  // Validar número de arquivos
  if (selectedFiles.value.length + files.length > 5) {
    alert("Máximo de 5 arquivos permitidos");
    return;
  }

  // Validar tamanho dos arquivos
  for (const file of files) {
    if (file.size > 10 * 1024 * 1024) {
      // 10MB
      alert(`Arquivo ${file.name} excede o tamanho máximo de 10MB`);
      return;
    }
  }

  selectedFiles.value.push(...files);
  event.target.value = ""; // Limpar input
};

// Remover arquivo
const removeFile = (index) => {
  selectedFiles.value.splice(index, 1);
};

// Download de anexo
const downloadAttachment = (attachment) => {
  if (attachment.url) {
    window.open(attachment.url, "_blank");
  }
};

// Fechar modal
const handleClose = () => {
  // Limpar arquivos selecionados
  selectedFiles.value = [];
  showAttachments.value = false;

  // Emitir evento para marcar como lido
  emit("mark-read");
  emit("close");
};

// Recarregar comentários
const refreshComments = async () => {
  loadingRefresh.value = true;
  try {
    // Emitir evento para o parent recarregar
    emit("comment-refresh");
  } finally {
    loadingRefresh.value = false;
  }
};

// Atualizar comentários externamente
const refreshCommentsExternal = (newComments) => {
  localComments.value = [...(newComments || [])];
  scrollToBottom();
};

const validateForm = () => {
  errors.comment = "";

  if (!form.comment.trim()) {
    errors.comment = "Por favor, escreva um comentário";
    return false;
  }

  if (form.comment.length < 3) {
    errors.comment = "O comentário deve ter pelo menos 3 caracteres";
    return false;
  }

  if (form.comment.length > 2000) {
    errors.comment = "O comentário não pode exceder 2000 caracteres";
    return false;
  }

  return true;
};

const submit = async () => {
  if (!validateForm()) return;

  loading.value = true;

  const formData = {
    comment: form.comment.trim(),
    attachments: selectedFiles.value,
  };

  try {
    emit("submit", formData);

    // Limpar após envio
    form.comment = "";
    selectedFiles.value = [];
    showAttachments.value = false;

    if (textareaRef.value) {
      textareaRef.value.style.height = "auto";
    }
  } finally {
    loading.value = false;
  }
};

// Função para adicionar comentário localmente (exposta)
const addLocalComment = (newComment) => {
  console.log("Adicionando comentário local no modal:", newComment);

  const formattedComment = {
    id: newComment.id,
    content: newComment.content || newComment.comment,
    comment: newComment.comment || newComment.content,
    type: newComment.type || "internal",
    action_type: newComment.action_type || "comment",
    created_at: newComment.created_at || new Date().toISOString(),
    user: newComment.user || {
      id: currentUser.value?.id,
      name: currentUser.value?.name,
      role: role.value,
    },
    attachments: newComment.attachments || [],
    metadata: newComment.metadata || {},
  };

  localComments.value.push(formattedComment);

  // Emitir evento
  emit("comment-added", formattedComment);

  // Rolar para baixo
  scrollToBottom();

  return formattedComment;
};

// Rolar para o final
const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
};

// Watch para mudanças nos comentários
watch(
  () => filteredComments.value,
  () => {
    scrollToBottom();
  },
  { deep: true }
);

// Focar no textarea quando o modal abrir
watch(
  () => props.isOpen,
  (isOpen) => {
    if (isOpen) {
      nextTick(() => {
        if (textareaRef.value) {
          textareaRef.value.focus();
        }
        scrollToBottom();
      });
    }
  }
);

// Expor funções
defineExpose({
  addLocalComment,
  refreshComments: refreshCommentsExternal,
});
</script>

<style scoped>
/* Estilos mantidos (mesmos do código anterior) */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

/* Estilo de scroll personalizado */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.7);
}

.dark ::-webkit-scrollbar-thumb {
  background: rgba(75, 85, 99, 0.5);
}

.dark ::-webkit-scrollbar-thumb:hover {
  background: rgba(75, 85, 99, 0.7);
}

/* Animações para novos comentários */
@keyframes highlight {
  0% {
    background-color: rgba(59, 130, 246, 0.3);
  }
  100% {
    background-color: transparent;
  }
}

.new-comment {
  animation: highlight 2s ease-out;
}

/* Estilos para modal no lado direito */
@media (min-width: 640px) {
  .modal-right {
    animation: slideInRight 0.3s ease-out;
  }

  .modal-fade-leave-active .modal-right {
    animation: slideOutRight 0.3s ease-out;
  }
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
  }
  to {
    transform: translateX(0);
  }
}

@keyframes slideOutRight {
  from {
    transform: translateX(0);
  }
  to {
    transform: translateX(100%);
  }
}
</style>
