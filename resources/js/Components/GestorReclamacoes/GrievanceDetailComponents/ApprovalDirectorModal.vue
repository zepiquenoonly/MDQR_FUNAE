<template>
  <!-- Root com posição fixa -->
  <div class="fixed inset-0 z-[9999]">
    <!-- Overlay (independente do modal) -->
    <div class="fixed inset-0 bg-black/70 z-40" @click.self="$emit('close')"></div>

    <!-- Wrapper do modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-6 pointer-events-none">
      <!-- Modal -->
      <div
        class="pointer-events-auto bg-white dark:bg-dark-secondary rounded-2xl w-full max-w-2xl max-h-[95vh] flex flex-col shadow-2xl"
        @click.stop>
        <!-- Header -->
        <div class="p-8 flex-shrink-0 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-xl font-bold text-gray-900 dark:text-dark-text-primary">
                Intervenção do Director
              </h2>
              <p class="text-base text-gray-700 dark:text-gray-300 mt-2">
                Responda à solicitação do gestor sobre a submissão
                <span class="font-bold">#{{ submission.reference_number }}</span>
              </p>
            </div>
            <button @click="$emit('close')"
              class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full p-2 transition-all duration-200">
              <XMarkIcon class="h-6 w-6" />
            </button>
          </div>
        </div>

        <!-- Form -->
        <div class="p-8 flex-1 overflow-y-auto">
          <div class="space-y-6">
            <!-- Opções de resposta -->
            <div>
              <h3 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4">
                Como pretende intervir?
              </h3>

              <div class="space-y-4">
                <!-- Aprovar - Assumir o caso -->
                <div class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition-colors" :class="form.status === 'approved'
                    ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                  " @click="form.status = 'approved'">
                  <input type="radio" id="approve" v-model="form.status" value="approved"
                    class="h-5 w-5 text-green-600 mt-0.5 focus:ring-green-500" />
                  <div class="flex-1">
                    <label for="approve" class="block cursor-pointer">
                      <span class="font-bold text-gray-900 dark:text-white">Aprovar - Assumir o caso</span>
                      <span class="ml-2 text-sm text-green-600 dark:text-green-400 font-medium">
                        Tomar a dianteira e assumir responsabilidade
                      </span>
                    </label>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                      Você assumirá a responsabilidade total pelo caso. O gestor perderá
                      acesso às ações de gestão.
                    </p>
                  </div>
                  <CheckBadgeIcon class="h-6 w-6 text-green-500 flex-shrink-0" />
                </div>

                <!-- Comentar - Dar parecer -->
                <div class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition-colors" :class="form.status === 'commented'
                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                  " @click="form.status = 'commented'">
                  <input type="radio" id="comment" v-model="form.status" value="commented"
                    class="h-5 w-5 text-blue-600 mt-0.5 focus:ring-blue-500" />
                  <div class="flex-1">
                    <label for="comment" class="block cursor-pointer">
                      <span class="font-bold text-gray-900 dark:text-white">Comentar - Dar parecer</span>
                      <span class="ml-2 text-sm text-blue-600 dark:text-blue-400 font-medium">
                        Dar orientação e devolver ao Gestor
                      </span>
                    </label>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                      Fornecerá orientações ao gestor que continuará a gerir o caso. O
                      gestor recuperará acesso às ações.
                    </p>
                  </div>
                  <ChatBubbleLeftIcon class="h-6 w-6 text-blue-500 flex-shrink-0" />
                </div>
              </div>

              <p v-if="errors.status" class="mt-2 text-sm text-red-600">
                {{ errors.status }}
              </p>
            </div>

            <!-- Comentário (obrigatório) -->
            <div>
              <h3 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-3">
                Comentário {{ form.status === "approved" ? "de Aprovação" : "de Parecer" }}
                <span class="text-red-500">*</span>
              </h3>
              <textarea v-model="form.comment" rows="6"
                class="w-full px-4 py-3 text-sm border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-600 dark:focus:border-blue-600 bg-white dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary resize-y transition-colors"
                :placeholder="form.status === 'approved'
                    ? 'Descreva por que está assumindo este caso e quais ações pretende tomar...'
                    : 'Forneça orientações específicas para o gestor continuar a gestão do caso...'
                  "></textarea>
              <p v-if="errors.comment" class="mt-2 text-sm text-red-600">
                {{ errors.comment }}
              </p>
            </div>

            <!-- Informações da decisão -->
            <div v-if="form.status" class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
              <h4 class="font-medium text-gray-900 dark:text-white mb-2">
                Consequências da sua decisão:
              </h4>
              <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-2">
                <li v-if="form.status === 'approved'" class="flex items-start gap-2">
                  <CheckCircleIcon class="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
                  <div>
                    <span class="font-medium">Você assumirá a responsabilidade total pelo caso</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                      O caso será transferido para sua gestão
                    </p>
                  </div>
                </li>
                <li v-if="form.status === 'approved'" class="flex items-start gap-2">
                  <CheckCircleIcon class="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
                  <div>
                    <span class="font-medium">O gestor perderá acesso às ações de gestão</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                      Apenas você poderá tomar decisões sobre o caso
                    </p>
                  </div>
                </li>
                <li v-if="form.status === 'commented'" class="flex items-start gap-2">
                  <ArrowUturnLeftIcon class="h-4 w-4 text-blue-500 mt-0.5 flex-shrink-0" />
                  <div>
                    <span class="font-medium">O caso será devolvido ao gestor para continuar a gestão</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                      O gestor recuperará todas as ações
                    </p>
                  </div>
                </li>
                <li class="flex items-start gap-2">
                  <BellIcon class="h-4 w-4 text-orange-500 mt-0.5 flex-shrink-0" />
                  <div>
                    <span class="font-medium">O gestor será notificado da sua resposta</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                      Receberá um email com seu comentário
                    </p>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Footer/Ações -->
        <div class="p-8 border-t border-gray-200 dark:border-gray-700">
          <div class="flex gap-4">
            <button @click="$emit('close')" :disabled="loading"
              class="flex-1 px-6 py-3 border border-gray-300 dark:border-gray-700 text-gray-800 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
              Cancelar
            </button>
            <button @click="submitResponse" :disabled="loading || !isFormValid" :class="[
              'flex-1 px-6 py-3 rounded-lg font-medium transition-colors flex items-center justify-center gap-2',
              loading || !isFormValid
                ? 'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed'
                : form.status === 'approved'
                  ? 'bg-green-600 hover:bg-green-700 text-white'
                  : 'bg-blue-600 hover:bg-blue-700 text-white',
            ]">
              <template v-if="loading">
                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                <span>Processando...</span>
              </template>
              <template v-else>
                <CheckIcon class="h-5 w-5" />
                <span>{{ form.status === "approved" ? "Assumir Caso" : "Enviar Parecer" }}</span>
              </template>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from "vue";
import {
  XMarkIcon,
  CheckBadgeIcon,
  ChatBubbleLeftIcon,
  CheckCircleIcon,
  ArrowUturnLeftIcon,
  BellIcon,
} from "@heroicons/vue/24/outline";

const emit = defineEmits(["close", "submit"]);
const props = defineProps({
  submission: Object,
  loading: Boolean,
});

const form = reactive({
  status: "", // 'approved' ou 'commented'
  comment: "",
});

const errors = reactive({});

const isFormValid = computed(() => {
  return form.status && form.comment.trim().length >= 10;
});

const validateForm = () => {
  errors.status = "";
  errors.comment = "";

  if (!form.status) {
    errors.status = "Selecione como pretende intervir";
    return false;
  }

  if (!form.comment.trim()) {
    errors.comment = "O comentário é obrigatório";
    return false;
  }

  if (form.comment.length < 10) {
    errors.comment = "O comentário deve ter pelo menos 10 caracteres";
    return false;
  }

  return true;
};

const submitResponse = () => {
  if (!validateForm()) return;

  emit("submit", {
    status: form.status,
    comment: form.comment,
  });
};
</script>
