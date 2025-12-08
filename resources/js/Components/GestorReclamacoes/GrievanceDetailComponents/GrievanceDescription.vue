<template>
  <div
    class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
  >
    <h2
      class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2"
    >
      <span
        class="flex items-center justify-center h-5 w-5 rounded-full bg-purple-100 dark:bg-purple-900/20 text-xs"
      >
        <DocumentTextIcon class="h-4 w-4 text-purple-600" />
      </span>
      Descrição da Submissão
    </h2>
    <div
      class="prose prose-sm dark:prose-invert max-w-none bg-gray-50 dark:bg-dark-accent rounded-lg p-4"
    >
      {{ complaint.description }}
    </div>
    <div
      class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"
    >
      <div class="text-sm">
        <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">
          Submetida
        </p>
        <p class="text-gray-900 dark:text-dark-text-primary font-semibold">
          {{ formatDate(complaint.created_at) }}
        </p>
      </div>
      <div class="text-sm">
        <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">
          Atualizada
        </p>
        <p class="text-gray-900 dark:text-dark-text-primary font-semibold">
          {{ getLastUpdate() }}
        </p>
      </div>
      <div class="text-sm">
        <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">
          Dias Aberto
        </p>
        <p class="text-gray-900 dark:text-dark-text-primary font-semibold">
          {{ getDaysOpen() }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { DocumentTextIcon } from "@heroicons/vue/24/outline";
import { useGrievanceUtils } from "@/Components/GestorReclamacoes/Composables/useGrievanceUtils";

const props = defineProps({
  complaint: {
    type: Object,
    required: true,
  },
});

const { formatDate, getDaysOpen, getLastUpdate } = useGrievanceUtils(props.complaint);
</script>
