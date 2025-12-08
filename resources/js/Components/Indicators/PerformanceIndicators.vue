<template>
  <div
    class="bg-white dark:bg-dark-secondary rounded-xl p-6 border border-gray-200 dark:border-gray-700"
  >
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-dark-text-primary">
        Indicadores de Performance
      </h2>
      <!--<button
        @click="$emit('export-indicators', 'excel')"
        class="text-brand hover:text-orange-600 text-sm font-medium flex items-center gap-1"
        :disabled="loading.exporting"
      >
        <ArrowDownTrayIcon class="w-4 h-4" />
        <span v-if="loading.exporting && exportFormat === 'excel'">Exportando...</span>
        <span v-else>Exportar</span>
      </button>-->
    </div>

    <div class="space-y-4">
      <IndicatorCard
        v-for="indicator in indicators"
        :key="indicator.id"
        :indicator="indicator"
        :get-performance-color="getPerformanceColor"
        :translate-category="translateCategory"
        :format-value="formatValue"
      />
    </div>
  </div>
</template>

<script setup>
import { ArrowDownTrayIcon } from "@heroicons/vue/24/outline";
import IndicatorCard from "./IndicatorCard.vue";

defineProps({
  indicators: {
    type: Array,
    required: true,
    default: () => [],
  },
  loading: {
    type: Object,
    default: () => ({}),
  },
  exportFormat: {
    type: String,
    default: null,
  },
  getPerformanceColor: {
    type: Function,
    required: true,
  },
  translateCategory: {
    type: Function,
    required: true,
  },
  formatValue: {
    type: Function,
    required: true,
  },
});

defineEmits(["export-indicators"]);
</script>
