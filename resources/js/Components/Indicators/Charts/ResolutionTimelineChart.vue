<template>
  <div
    class="bg-white dark:bg-dark-secondary rounded-xl p-6 border border-gray-200 dark:border-gray-700"
  >
    <h2 class="text-lg font-semibold text-gray-900 dark:text-dark-text-primary mb-4">
      Timeline de Resolução
    </h2>
    <div class="h-64">
      <canvas id="timeline-chart-canvas" class="w-full h-full"></canvas>
    </div>
  </div>
</template>

<script setup>
defineProps({
  data: {
    type: Array,
    required: true,
    default: () => [],
  },
});
</script>
