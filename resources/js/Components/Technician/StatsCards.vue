<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div
      class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700"
    >
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
            Total de Técnicos
          </p>
          <p class="text-2xl font-bold text-gray-900 dark:text-dark-text-primary">
            {{ stats.totalTecnicos || 0 }}
          </p>
        </div>
        <div class="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
          <UserGroupIcon class="w-6 h-6 text-blue-600 dark:text-blue-400" />
        </div>
      </div>
    </div>

    <div
      class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700"
    >
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
            Técnicos Ativos
          </p>
          <p class="text-2xl font-bold text-green-600 dark:text-green-400">
            {{ stats.tecnicosAtivos || 0 }}
          </p>
        </div>
        <div class="p-3 bg-green-100 dark:bg-green-900/20 rounded-lg">
          <CheckBadgeIcon class="w-6 h-6 text-green-600 dark:text-green-400" />
        </div>
      </div>
    </div>

    <div
      class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700"
    >
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
            Casos Atribuídos
          </p>
          <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">
            {{ stats.casosAtribuidos || 0 }}
          </p>
        </div>
        <div class="p-3 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
          <ClipboardDocumentListIcon
            class="w-6 h-6 text-orange-600 dark:text-orange-400"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  UserGroupIcon,
  CheckBadgeIcon,
  ClipboardDocumentListIcon,
} from "@heroicons/vue/24/outline";

defineProps({
  stats: {
    type: Object,
    default: () => ({}),
  },
});
</script>
