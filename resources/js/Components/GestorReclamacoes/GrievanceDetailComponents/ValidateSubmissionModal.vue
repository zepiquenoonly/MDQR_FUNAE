<template>
  <div class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-6">
    <div
      class="w-[90vw] md:w-[50vw] lg:w-[25vw] xl:w-[40vw] max-w-full bg-white dark:bg-gray-800 rounded-xl shadow-2xl transform transition-all duration-300"
    >
      <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-bold text-gray-900 dark:text-dark-text-primary">
            Validar Submissão
          </h2>
          <button
            @click="$emit('close')"
            class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
          >
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
          Submissão #{{ submission.reference_number }}
        </p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitForm" class="p-6 space-y-6">
        <!-- Status da validação -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Decisão da Validação *
          </label>
          <div class="space-y-3">
            <label
              class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
            >
              <input
                type="radio"
                v-model="form.status"
                value="approved"
                class="h-4 w-4 text-green-600 focus:ring-green-500"
                required
              />
              <div class="ml-3 flex-1">
                <span class="font-medium text-green-700 dark:text-green-400"
                  >Aprovar Submissão</span
                >
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  A submissão será marcada como aprovada e será enviada para conclusão
                </p>
              </div>
            </label>

            <label
              class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
            >
              <input
                type="radio"
                v-model="form.status"
                value="rejected"
                class="h-4 w-4 text-red-600 focus:ring-red-500"
                required
              />
              <div class="ml-3 flex-1">
                <span class="font-medium text-red-700 dark:text-red-400"
                  >Rejeitar Submissão</span
                >
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  A submissão será devolvida para o técnico em estado "Em Andamento"
                </p>
              </div>
            </label>
          </div>
        </div>

        <!-- Comentário -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Comentário (opcional)
          </label>
          <textarea
            v-model="form.comment"
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
            placeholder="Adicione um comentário sobre sua decisão..."
          ></textarea>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Este comentário será visível para o técnico responsável
          </p>
        </div>

        <!-- Botões -->
        <div
          class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700"
        >
          <button
            type="button"
            @click="$emit('close')"
            :disabled="loading"
            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors disabled:opacity-50"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="loading || !form.status"
            :class="[
              'px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors flex items-center gap-2',
              loading || !form.status
                ? 'bg-gray-400 cursor-not-allowed'
                : form.status === 'approved'
                ? 'bg-green-600 hover:bg-green-700'
                : 'bg-red-600 hover:bg-red-700',
            ]"
          >
            <template v-if="loading">
              <div
                class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"
              ></div>
              A processar...
            </template>
            <template v-else>
              {{ form.status === "approved" ? "Aprovar" : "Rejeitar" }} Submissão
            </template>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from "vue";
import { XMarkIcon } from "@heroicons/vue/24/outline";

const emit = defineEmits(["close", "submit"]);
const props = defineProps({
  submission: Object,
  loading: Boolean,
});

const form = reactive({
  status: "",
  comment: "",
});

const submitForm = () => {
  emit("submit", { ...form });
};
</script>
