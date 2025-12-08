<template>
  <div
    class="bg-white dark:bg-dark-secondary rounded-xl p-6 border border-gray-200 dark:border-gray-700"
  >
    <h2 class="text-lg font-semibold text-gray-900 dark:text-dark-text-primary mb-4">
      Estatísticas Rápidas
    </h2>
    <div class="space-y-3">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg"
      >
        <span class="text-sm text-gray-600 dark:text-gray-400">{{ stat.label }}</span>
        <span class="font-medium text-gray-900 dark:text-dark-text-primary">{{
          stat.value
        }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  stats: {
    type: Array,
    required: true,
    default: () => [],
  },
});
</script>
