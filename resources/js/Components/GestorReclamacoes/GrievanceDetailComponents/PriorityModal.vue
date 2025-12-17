<template>
  <div class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-6">
    <div
      class="bg-white dark:bg-dark-secondary rounded-2xl w-full max-w-xl max-h-[95vh] flex flex-col shadow-2xl"
    >
      <!-- Header -->
      <div class="p-8 flex-shrink-0 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-dark-text-primary">
              Definir Prioridade
            </h2>
            <p class="text-base text-gray-700 dark:text-gray-300 mt-2">
              Alterar prioridade para a reclamação
              <span class="font-bold">#{{ complaint.reference_number }}</span>
            </p>
          </div>
          <button
            @click="$emit('close')"
            class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full p-2 transition-all duration-200"
          >
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>
      </div>

      <!-- Form -->
      <div class="p-8 flex-1 overflow-y-auto">
        <div class="space-y-6">
          <!-- Opções de prioridade -->
          <div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4">
              Selecione o nível de prioridade
            </h3>

            <div class="space-y-4">
              <!-- Alta Prioridade -->
              <div
                class="flex items-start gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all duration-200"
                :class="
                  selectedPriority === 'high'
                    ? 'border-red-500 bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 shadow-lg'
                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md'
                "
                @click="selectedPriority = 'high'"
              >
                <input
                  type="radio"
                  id="high"
                  v-model="selectedPriority"
                  value="high"
                  class="h-5 w-5 text-red-600 mt-0.5 focus:ring-red-500"
                />
                <div class="flex-1">
                  <label for="high" class="block cursor-pointer">
                    <span class="font-bold text-gray-900 dark:text-white"
                      >Alta Prioridade</span
                    >
                    <span class="ml-2 text-sm text-red-600 dark:text-red-400 font-medium">
                      Requer atenção imediata
                    </span>
                  </label>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Casos críticos com impacto significativo e prazo urgente
                  </p>
                </div>
                <div class="flex-shrink-0">
                  <div
                    class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center"
                  >
                    <ExclamationTriangleIcon
                      class="h-5 w-5 text-red-600 dark:text-red-400"
                    />
                  </div>
                </div>
              </div>

              <!-- Média Prioridade -->
              <div
                class="flex items-start gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all duration-200"
                :class="
                  selectedPriority === 'medium'
                    ? 'border-yellow-500 bg-gradient-to-r from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 shadow-lg'
                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md'
                "
                @click="selectedPriority = 'medium'"
              >
                <input
                  type="radio"
                  id="medium"
                  v-model="selectedPriority"
                  value="medium"
                  class="h-5 w-5 text-yellow-600 mt-0.5 focus:ring-yellow-500"
                />
                <div class="flex-1">
                  <label for="medium" class="block cursor-pointer">
                    <span class="font-bold text-gray-900 dark:text-white"
                      >Média Prioridade</span
                    >
                    <span
                      class="ml-2 text-sm text-yellow-600 dark:text-yellow-400 font-medium"
                    >
                      Atenção necessária em breve
                    </span>
                  </label>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Casos importantes com prazos moderados e impacto considerável
                  </p>
                </div>
                <div class="flex-shrink-0">
                  <div
                    class="w-8 h-8 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center"
                  >
                    <ClockIcon class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                  </div>
                </div>
              </div>

              <!-- Baixa Prioridade -->
              <div
                class="flex items-start gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all duration-200"
                :class="
                  selectedPriority === 'low'
                    ? 'border-green-500 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 shadow-lg'
                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md'
                "
                @click="selectedPriority = 'low'"
              >
                <input
                  type=" radio"
                  id="low"
                  v-model="selectedPriority"
                  value="low"
                  class="h-5 w-5 text-green-600 mt-0.5 focus:ring-green-500"
                />
                <div class="flex-1">
                  <label for="low" class="block cursor-pointer">
                    <span class="font-bold text-gray-900 dark:text-white"
                      >Baixa Prioridade</span
                    >
                    <span
                      class="ml-2 text-sm text-green-600 dark:text-green-400 font-medium"
                    >
                      Pode ser tratado posteriormente
                    </span>
                  </label>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Casos rotineiros sem urgência imediata ou impacto significativo
                  </p>
                </div>
                <div class="flex-shrink-0">
                  <div
                    class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center"
                  >
                    <CheckCircleIcon class="h-5 w-5 text-green-600 dark:text-green-400" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Informações da decisão -->
          <div
            v-if="selectedPriority"
            class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700"
          >
            <h4 class="font-medium text-gray-900 dark:text-white mb-2">
              Impacto da sua decisão:
            </h4>
            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-2">
              <li v-if="selectedPriority === 'high'" class="flex items-start gap-2">
                <ExclamationTriangleIcon
                  class="h-4 w-4 text-red-500 mt-0.5 flex-shrink-0"
                />
                <div>
                  <span class="font-medium"
                    >Atribuída a equipas com disponibilidade imediata</span
                  >
                  <p class="text-xs text-gray-500 mt-0.5">
                    Técnicos serão notificados com urgência
                  </p>
                </div>
              </li>
              <li v-if="selectedPriority === 'medium'" class="flex items-start gap-2">
                <ClockIcon class="h-4 w-4 text-yellow-500 mt-0.5 flex-shrink-0" />
                <div>
                  <span class="font-medium"
                    >Processada dentro do prazo normal de operação</span
                  >
                  <p class="text-xs text-gray-500 mt-0.5">
                    Atribuída durante o próximo ciclo de trabalho
                  </p>
                </div>
              </li>
              <li v-if="selectedPriority === 'low'" class="flex items-start gap-2">
                <CheckCircleIcon class="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
                <div>
                  <span class="font-medium"
                    >Tratada quando recursos estiverem disponíveis</span
                  >
                  <p class="text-xs text-gray-500 mt-0.5">
                    Pode levar mais tempo para resolução
                  </p>
                </div>
              </li>
              <li class="flex items-start gap-2">
                <BellIcon class="h-4 w-4 text-orange-500 mt-0.5 flex-shrink-0" />
                <div>
                  <span class="font-medium">Todas as partes serão notificadas</span>
                  <p class="text-xs text-gray-500 mt-0.5">
                    A alteração será registrada no histórico
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
          <button
            @click="$emit('close')"
            :disabled="isLoading"
            class="flex-1 px-6 py-3 border border-gray-300 dark:border-gray-700 text-gray-800 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Cancelar
          </button>
          <button
            @click="confirmPriority"
            :disabled="isLoading || !selectedPriority"
            :class="[
              'flex-1 px-6 py-3 rounded-lg font-medium transition-colors flex items-center justify-center gap-2',
              isLoading || !selectedPriority
                ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                : selectedPriority === 'high'
                ? 'bg-red-600 text-white hover:bg-red-700'
                : selectedPriority === 'medium'
                ? 'bg-yellow-600 text-white hover:bg-yellow-700'
                : 'bg-green-600 text-white hover:bg-green-700',
            ]"
          >
            <template v-if="isLoading">
              <div
                class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"
              ></div>
              <span>Processando...</span>
            </template>
            <template v-else>
              <CheckIcon class="h-5 w-5" />
              <span>Confirmar Prioridade</span>
            </template>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import {
  XMarkIcon,
  CheckIcon,
  ExclamationTriangleIcon,
  ClockIcon,
  CheckCircleIcon,
  BellIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  complaint: Object,
});

const emit = defineEmits(["close", "update"]);

const selectedPriority = ref(props.complaint?.priority || "");
const isLoading = ref(false);

const confirmPriority = async () => {
  if (!selectedPriority.value || isLoading.value) return;

  isLoading.value = true;

  try {
    emit("update", selectedPriority.value);
  } catch (error) {
    console.error("Erro ao atualizar prioridade:", error);
    isLoading.value = false;
  }
};
</script>
