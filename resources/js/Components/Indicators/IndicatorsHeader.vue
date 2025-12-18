<template>
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1
        class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-dark-text-primary"
      >
        Estatísticas Gerais
      </h1>
      <p class="text-gray-600 dark:text-gray-400 mt-1">
        Monitoramento de performance e métricas dos Técnicos e Submissões
      </p>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-3">
      <select
        :value="timeRange"
        @change="handleTimeRangeChange"
        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand dark:bg-dark-accent dark:text-dark-text-primary text-sm"
      >
        <option value="week">Última Semana</option>
        <option value="month">Último Mês</option>
        <option value="quarter">Último Trimestre</option>
        <option value="year">Último Ano</option>
      </select>

      <select
        :value="categoryFilter"
        @change="handleCategoryFilterChange"
        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand dark:bg-dark-accent dark:text-dark-text-primary text-sm"
      >
        <option value="all">Todas Categorias</option>
        <option value="performance">Performance</option>
        <option value="satisfaction">Satisfação</option>
        <option value="efficiency">Eficiência</option>
        <option value="quality">Qualidade</option>
      </select>

      <!--<button
        @click="$emit('open-report-generator')"
        class="px-4 py-2 bg-brand text-white rounded-lg hover:bg-orange-600 transition-colors flex items-center gap-2 text-sm font-medium"
        :disabled="loading.generatingReport"
      >
        <DocumentArrowDownIcon class="w-4 h-4" />
        <span v-if="loading.generatingReport">Gerando...</span>
        <span v-else>Gerar Relatório</span>
      </button>-->
    </div>
  </div>
</template>

<script setup>
import { DocumentArrowDownIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
  timeRange: {
    type: String,
    default: "month",
  },
  categoryFilter: {
    type: String,
    default: "all",
  },
  loading: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits([
  "update-filters",
  "open-report-generator",
  "update:timeRange",
  "update:categoryFilter",
]);

const handleTimeRangeChange = (event) => {
  const newValue = event.target.value;
  emit("update:timeRange", newValue);
  emit("update-filters");
};

const handleCategoryFilterChange = (event) => {
  const newValue = event.target.value;
  emit("update:categoryFilter", newValue);
  emit("update-filters");
};
</script>
