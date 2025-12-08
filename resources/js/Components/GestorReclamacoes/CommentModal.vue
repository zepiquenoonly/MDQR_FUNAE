<template>
  <div
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
  >
    <div
      class="bg-white dark:bg-dark-secondary rounded-xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden"
    >
      <!-- Header -->
      <div class="p-8 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-bold text-gray-900 dark:text-dark-text-primary">
            Comentar a Submissão
          </h3>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>
        <p class="text-base text-gray-600 dark:text-gray-400 mt-2">
          Adicione um comentário sobre esta submissão
        </p>
      </div>

      <!-- Form -->
      <div class="p-8 flex-1 overflow-y-auto">
        <div class="space-y-6">
          <!-- Comentário -->
          <div>
            <label
              class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-3"
            >
              Comentário
            </label>
            <textarea
              v-model="form.comment"
              rows="6"
              class="w-full px-4 py-3 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-0 focus:ring-brand focus:border-brand dark:focus:ring-orange-500 dark:focus:border-orange-500 bg-white dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary"
              placeholder="Escreva o seu comentário aqui..."
              :class="{ 'border-red-500': errors.comment }"
            ></textarea>
            <p v-if="errors.comment" class="mt-2 text-base text-red-600">
              {{ errors.comment }}
            </p>
          </div>

          <!-- Visibilidade -->
          <div>
            <label
              class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-3"
            >
              Visibilidade do Comentário
            </label>
            <div class="space-y-3">
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="form.is_public"
                  :value="true"
                  class="h-5 w-5 text-brand focus:ring-brand border-gray-300"
                />
                <span class="ml-3 text-base text-gray-700 dark:text-gray-300">
                  Público (visível para todos)
                </span>
              </label>
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="form.is_public"
                  :value="false"
                  class="h-5 w-5 text-brand focus:ring-brand border-gray-300"
                />
                <span class="ml-3 text-base text-gray-700 dark:text-gray-300">
                  Privado (apenas para quem submeteu)
                </span>
              </label>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-4 mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
          <button
            @click="$emit('close')"
            class="flex-1 px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-semibold text-base hover:bg-gray-50 dark:hover:bg-dark-accent transition-all"
          >
            Cancelar
          </button>
          <button
            @click="submit"
            :disabled="loading"
            class="flex-1 px-6 py-3 bg-brand text-white rounded-lg font-semibold text-base hover:bg-orange-700 transition-all flex items-center justify-center gap-2"
          >
            <template v-if="loading">
              <div
                class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"
              ></div>
              <span>Enviando...</span>
            </template>
            <template v-else>
              <ChatBubbleLeftIcon class="h-5 w-5" />
              <span>Enviar Comentário</span>
            </template>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from "vue";
import { XMarkIcon, ChatBubbleLeftIcon } from "@heroicons/vue/24/outline";

const emit = defineEmits(["close", "submit"]);
const props = defineProps({
  complaint: Object,
});

const loading = ref(false);
const errors = reactive({});

const form = reactive({
  comment: "",
  is_public: true,
});

const validateForm = () => {
  errors.comment = "";

  if (!form.comment.trim()) {
    errors.comment = "Por favor, escreva um comentário";
    return false;
  }

  if (form.comment.length < 10) {
    errors.comment = "O comentário deve ter pelo menos 10 caracteres";
    return false;
  }

  if (form.comment.length > 2000) {
    errors.comment = "O comentário não pode exceder 2000 caracteres";
    return false;
  }

  return true;
};

const submit = () => {
  if (!validateForm()) return;

  loading.value = true;

  // Simular um delay de rede
  setTimeout(() => {
    emit("submit", { ...form });
    loading.value = false;
  }, 500);
};
</script>
