<template>
  <div class="bg-white dark:bg-dark-secondary rounded shadow-sm p-4 sm:p-6">
    <h3
      class="text-xl sm:text-2xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4 sm:mb-6"
    >
      Preferências de Notificação
    </h3>

    <div class="space-y-4 sm:space-y-6">
      <!-- Notificações por Email -->
      <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 sm:p-6">
        <h4
          class="text-base sm:text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-3 sm:mb-4"
        >
          Notificações por Email
        </h4>
        <div class="space-y-3 sm:space-y-4">
          <div
            class="flex flex-col sm:flex-row sm:items-center justify-between space-y-2 sm:space-y-0"
          >
            <div class="flex-1">
              <p
                class="font-medium text-gray-800 dark:text-dark-text-primary text-sm sm:text-base"
              >
                Novas submissões
              </p>
              <p class="text-gray-600 dark:text-gray-400 text-xs sm:text-sm">
                Receber notificações quando novas submissões forem criadas
              </p>
            </div>
            <label
              class="relative inline-flex items-center cursor-pointer self-start sm:self-auto"
            >
              <input
                type="checkbox"
                v-model="emailNotifications.newSubmissions"
                class="sr-only peer"
              />
              <div
                class="w-10 h-5 sm:w-11 sm:h-6 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] sm:after:top-[2px] after:left-[1px] sm:after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 sm:after:h-5 sm:after:w-5 after:transition-all peer-checked:bg-brand"
              ></div>
            </label>
          </div>

          <div
            class="flex flex-col sm:flex-row sm:items-center justify-between space-y-2 sm:space-y-0"
          >
            <div class="flex-1">
              <p
                class="font-medium text-gray-800 dark:text-dark-text-primary text-sm sm:text-base"
              >
                Atualizações de status
              </p>
              <p class="text-gray-600 dark:text-gray-400 text-xs sm:text-sm">
                Receber notificações quando o status das submissões mudar
              </p>
            </div>
            <label
              class="relative inline-flex items-center cursor-pointer self-start sm:self-auto"
            >
              <input
                type="checkbox"
                v-model="emailNotifications.statusUpdates"
                class="sr-only peer"
              />
              <div
                class="w-10 h-5 sm:w-11 sm:h-6 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] sm:after:top-[2px] after:left-[1px] sm:after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 sm:after:h-5 sm:after:w-5 after:transition-all peer-checked:bg-brand"
              ></div>
            </label>
          </div>

          <!-- <div
            class="flex flex-col sm:flex-row sm:items-center justify-between space-y-2 sm:space-y-0"
          >
            <div class="flex-1">
              <p
                class="font-medium text-gray-800 dark:text-dark-text-primary text-sm sm:text-base"
              >
                Relatórios semanais
              </p>
              <p class="text-gray-600 dark:text-gray-400 text-xs sm:text-sm">
                Receber relatórios semanais de atividade
              </p>
            </div>
            
          <label class="relative inline-flex items-center cursor-pointer self-start sm:self-auto">
            <input type="checkbox" v-model="emailNotifications.weeklyReports" class="sr-only peer" />
            <div
              class="w-10 h-5 sm:w-11 sm:h-6 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] sm:after:top-[2px] after:left-[1px] sm:after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 sm:after:h-5 sm:after:w-5 after:transition-all peer-checked:bg-brand">
            </div>
          </label>
        </div>-->
        </div>
      </div>

      <!-- Notificações no Sistema -->
      <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 sm:p-6">
        <h4
          class="text-base sm:text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-3 sm:mb-4"
        >
          Notificações no Sistema
        </h4>
        <div class="space-y-3 sm:space-y-4">
          <div
            class="flex flex-col sm:flex-row sm:items-center justify-between space-y-2 sm:space-y-0"
          >
            <div class="flex-1">
              <p
                class="font-medium text-gray-800 dark:text-dark-text-primary text-sm sm:text-base"
              >
                Notificações push
              </p>
              <p class="text-gray-600 dark:text-gray-400 text-xs sm:text-sm">
                Receber notificações push no navegador
              </p>
            </div>
            <label
              class="relative inline-flex items-center cursor-pointer self-start sm:self-auto"
            >
              <input
                type="checkbox"
                v-model="systemNotifications.push"
                class="sr-only peer"
              />
              <div
                class="w-10 h-5 sm:w-11 sm:h-6 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] sm:after:top-[2px] after:left-[1px] sm:after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 sm:after:h-5 sm:after:w-5 after:transition-all peer-checked:bg-brand"
              ></div>
            </label>
          </div>

          <div
            class="flex flex-col sm:flex-row sm:items-center justify-between space-y-2 sm:space-y-0"
          >
            <div class="flex-1">
              <p
                class="font-medium text-gray-800 dark:text-dark-text-primary text-sm sm:text-base"
              >
                Sons de notificação
              </p>
              <p class="text-gray-600 dark:text-gray-400 text-xs sm:text-sm">
                Reproduzir sons para novas notificações
              </p>
            </div>
            <label
              class="relative inline-flex items-center cursor-pointer self-start sm:self-auto"
            >
              <input
                type="checkbox"
                v-model="systemNotifications.sounds"
                class="sr-only peer"
              />
              <div
                class="w-10 h-5 sm:w-11 sm:h-6 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] sm:after:top-[2px] after:left-[1px] sm:after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 sm:after:h-5 sm:after:w-5 after:transition-all peer-checked:bg-brand"
              ></div>
            </label>
          </div>
        </div>
      </div>

      <!-- Botão de Salvar -->
      <div class="flex justify-end">
        <button
          @click="saveNotifications"
          class="w-full sm:w-auto bg-brand hover:bg-orange-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded font-medium transition-colors text-sm sm:text-base"
        >
          Guardar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";

// Dados de exemplo para notificações
const emailNotifications = ref({
  newSubmissions: true,
  statusUpdates: true,
  weeklyReports: false,
});

const systemNotifications = ref({
  push: true,
  sounds: false,
});

const saveNotifications = () => {
  // Aqui você pode implementar a lógica para salvar as preferências
  console.log("Preferências de notificação guardadas!");
  // Em produção, você faria uma chamada API para salvar estas configurações
};
</script>
