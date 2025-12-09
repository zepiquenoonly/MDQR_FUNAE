<!-- GrievanceDirectorComments.vue -->
<template>
  <div
    class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
  >
    <h2
      class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2"
    >
      <span
        class="flex items-center justify-center h-5 w-5 rounded-full bg-purple-100 dark:bg-purple-900/20 text-xs flex-shrink-0"
      >
        <ChatBubbleLeftRightIcon class="h-4 w-4 text-purple-600" />
      </span>
      Comentários do Director
    </h2>

    <div v-if="directorComments.length === 0" class="text-center py-6">
      <ChatBubbleLeftRightIcon class="w-12 h-12 text-gray-400 mx-auto mb-2" />
      <p class="text-gray-500 dark:text-gray-400">Sem comentários do director</p>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="comment in directorComments"
        :key="comment.id"
        :class="[
          'p-4 rounded-lg border',
          comment.type.includes('validation')
            ? 'bg-purple-50 dark:bg-purple-900/10 border-purple-200 dark:border-purple-800'
            : 'bg-blue-50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800',
        ]"
      >
        <!-- Cabeçalho do comentário -->
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center gap-2">
            <div
              :class="[
                'w-8 h-8 rounded-full flex items-center justify-center text-white font-medium text-xs',
                comment.type.includes('validation') ? 'bg-purple-600' : 'bg-blue-600',
              ]"
            >
              {{ getUserInitials(comment.user?.name || "Director") }}
            </div>
            <div>
              <p class="font-medium text-gray-900 dark:text-dark-text-primary">
                {{ comment.user?.name || "Director" }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ comment.user?.role || "Director" }} •
                {{ formatDate(comment.created_at) }}
              </p>
            </div>
          </div>

          <span
            :class="[
              'px-2 py-1 text-xs font-medium rounded-full',
              comment.type.includes('validation')
                ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-300'
                : 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300',
            ]"
          >
            {{ getCommentTypeLabel(comment.type) }}
          </span>
        </div>

        <!-- Conteúdo do comentário -->
        <div class="mt-2">
          <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
            {{ comment.content }}
          </p>
        </div>

        <!-- Metadados adicionais -->
        <div
          v-if="comment.metadata"
          class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700"
        >
          <div
            v-if="comment.metadata.notifications"
            class="text-xs text-gray-500 dark:text-gray-400"
          >
            <span class="font-medium">Notificações enviadas:</span>
            <span v-if="comment.metadata.notifications.manager"> Gestor</span>
            <span v-if="comment.metadata.notifications.technician"> Técnico</span>
            <span v-if="comment.metadata.notifications.user"> Utente</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ChatBubbleLeftRightIcon } from "@heroicons/vue/24/outline";
import { ref, reactive, computed, onMounted } from "vue";
const props = defineProps({
  complaint: {
    type: Object,
    required: true,
  },
});

const directorComments = computed(() => {
  return props.complaint.director_comments || [];
});

// Funções auxiliares
const getUserInitials = (name) => {
  if (!name) return "D";
  return name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .toUpperCase()
    .substring(0, 2);
};

const formatDate = (dateString) => {
  if (!dateString) return "";
  return new Date(dateString).toLocaleDateString("pt-BR", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const getCommentTypeLabel = (type) => {
  const labels = {
    director_comment: "Comentário",
    director_validation_approved: "Validação Aprovada",
    director_validation_rejected: "Validação Rejeitada",
    director_validation_needs_revision: "Precisa de Revisão",
  };
  return labels[type] || type;
};
</script>
