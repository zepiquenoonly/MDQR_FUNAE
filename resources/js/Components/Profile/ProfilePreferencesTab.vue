<template>
  <div class="w-screen p-4 rounded shadow-sm sm:p-6">
    <h3
      class="mb-4 text-xl font-semibold text-gray-800 sm:text-2xl dark:text-dark-text-primary sm:mb-6"
    >
      Preferências do Sistema
    </h3>

    <div class="space-y-4 sm:space-y-6">
      <!-- Idioma e Região -->
      <div class="p-4 border border-gray-200 rounded-lg dark:border-gray-700 sm:p-6">
        <h4
          class="mb-3 text-base font-semibold text-gray-800 sm:text-lg dark:text-dark-text-primary sm:mb-4"
        >
          Idioma e Região
        </h4>
        <div class="grid grid-cols-1 gap-3 sm:gap-4">
          <div>
            <label
              class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300 sm:mb-2"
              >Idioma</label
            >
            <select
              v-model="preferences.language"
              class="w-full px-3 py-2 text-sm transition-colors border border-gray-300 rounded-lg sm:px-4 sm:py-3 sm:text-base dark:border-gray-600 focus:outline-none focus:ring-0 focus:ring-orange-500 focus:border-brand dark:bg-dark-accent dark:text-dark-text-primary"
            >
              <option value="pt">Português (PT)</option>
              <option value="en">English (EN)</option>
            </select>
          </div>

          <div>
            <label
              class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300 sm:mb-2"
              >Fuso Horário</label
            >
            <select
              v-model="preferences.timezone"
              class="w-full px-3 py-2 text-sm transition-colors border border-gray-300 rounded-lg sm:px-4 sm:py-3 sm:text-base dark:border-gray-600 focus:outline-none focus:ring-0 focus:ring-orange-500 focus:border-brand dark:bg-dark-accent dark:text-dark-text-primary"
            >
              <option value="Africa/Maputo">Africa/Maputo (UTC+2)</option>
              <option value="UTC">UTC</option>
              <option value="Europe/Lisbon">Europe/Lisbon (UTC+1)</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Aparência -->
      <div class="p-4 border border-gray-200 rounded-lg dark:border-gray-700 sm:p-6">
        <h4
          class="mb-3 text-base font-semibold text-gray-800 sm:text-lg dark:text-dark-text-primary sm:mb-4"
        >
          Aparência
        </h4>
        <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-3">
          <button
            @click="preferences.theme = 'light'"
            :class="[
              'flex items-center justify-center sm:justify-start space-x-2 px-3 sm:px-4 py-2 border-2 rounded-lg font-medium transition-colors text-sm sm:text-base',
              preferences.theme === 'light'
                ? 'border-orange-500 text-orange-500 bg-orange-50 dark:bg-orange-900/20'
                : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-400 dark:hover:border-gray-500',
            ]"
          >
            <SunIcon class="flex-shrink-0 w-4 h-4" />
            <span>Claro</span>
          </button>

          <button
            @click="preferences.theme = 'dark'"
            :class="[
              'flex items-center justify-center sm:justify-start space-x-2 px-3 sm:px-4 py-2 border-2 rounded-lg font-medium transition-colors text-sm sm:text-base',
              preferences.theme === 'dark'
                ? 'border-orange-500 text-orange-500 bg-orange-50 dark:bg-orange-900/20'
                : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-400 dark:hover:border-gray-500',
            ]"
          >
            <MoonIcon class="flex-shrink-0 w-4 h-4" />
            <span>Escuro</span>
          </button>

          <button
            @click="preferences.theme = 'auto'"
            :class="[
              'flex items-center justify-center sm:justify-start space-x-2 px-3 sm:px-4 py-2 border-2 rounded-lg font-medium transition-colors text-sm sm:text-base',
              preferences.theme === 'auto'
                ? 'border-orange-500 text-orange-500 bg-orange-50 dark:bg-orange-900/20'
                : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-400 dark:hover:border-gray-500',
            ]"
          >
            <ComputerDesktopIcon class="flex-shrink-0 w-4 h-4" />
            <span>Automático</span>
          </button>
        </div>
      </div>

      <!-- Zona Perigosa -->
      <div
        class="p-4 border border-red-200 rounded-lg dark:border-red-800 sm:p-6 bg-red-50 dark:bg-red-900/20"
      >
        <h4
          class="mb-3 text-base font-semibold text-red-800 sm:text-lg dark:text-red-300 sm:mb-4"
        >
          Zona Perigosa
        </h4>
        <div class="space-y-3 sm:space-y-4">
          <p class="text-sm text-red-700 dark:text-red-300 sm:text-base">
            Uma vez que apague a sua conta, não há retorno. Por favor, tenha certeza.
          </p>
          <button
            @click="confirmAccountDeletion"
            class="w-full px-4 py-2 text-sm font-medium text-white transition-colors bg-red-600 rounded-lg sm:w-auto hover:bg-red-700 sm:px-6 sm:py-3 sm:text-base"
          >
            Apagar Conta
          </button>
        </div>
      </div>

      <!-- Botão de Salvar -->
      <div class="flex justify-end">
        <button
          @click="savePreferences"
          class="w-full px-4 py-2 text-sm font-medium text-white transition-colors bg-orange-500 rounded-lg sm:w-auto hover:bg-orange-600 sm:px-6 sm:py-3 sm:text-base"
        >
          Guardar Preferências
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { SunIcon, MoonIcon, ComputerDesktopIcon } from "@heroicons/vue/24/outline";

// Dados de exemplo para preferências
const preferences = ref({
  language: "pt",
  timezone: "Africa/Maputo",
  theme: "light",
});

const savePreferences = () => {
  // Aqui você pode implementar a lógica para salvar as preferências
  console.log("Preferências guardadas!");
  // Em produção, você faria uma chamada API para salvar estas configurações
};

const confirmAccountDeletion = () => {
  console.log("Confirmando exclusão da conta");
  router.delete($route("profile.destroy"));
};
</script>
