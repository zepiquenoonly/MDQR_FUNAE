<template>
  <Layout>
    <Toast ref="toast" />
    <div class="space-y-4 sm:space-y-6">
      <!-- Banner Informativo -->
      <div
        class="bg-gradient-to-r from-primary-50 to-orange-50 dark:from-primary-900/20 dark:to-orange-900/20 border border-primary-200 dark:border-primary-800 rounded-xl p-4 sm:p-6 mb-6"
      >
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <div
              class="w-12 h-12 bg-primary-100 dark:bg-primary-900/40 rounded-lg flex items-center justify-center"
            >
              <svg
                class="w-6 h-6 text-primary-600 dark:text-primary-400"
                fill="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                />
              </svg>
            </div>
          </div>
          <div class="flex-1">
            <div class="flex items-center justify-between">
              <div>
                <h3
                  class="text-lg font-semibold text-primary-900 dark:text-primary-100 mb-2"
                >
                  Dashboard de Indicadores
                </h3>
                <p class="text-primary-700 dark:text-primary-300 text-sm leading-relaxed">
                  Monitore o desempenho da equipe, acompanhe métricas de resolução e
                  analise tendências para melhorar a eficiência operacional.
                </p>
              </div>
              <!-- Botão Exportar PDF alinhado à direita -->
              <div class="flex-shrink-0">
                <button
                  @click="exportPdf"
                  :disabled="isExporting"
                  class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium text-sm shadow-sm hover:shadow transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed"
                >
                  <svg
                    v-if="isExporting"
                    class="w-4 h-4 animate-spin"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle
                      class="opacity-25"
                      cx="12"
                      cy="12"
                      r="10"
                      stroke="currentColor"
                      stroke-width="4"
                    ></circle>
                    <path
                      class="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                  </svg>
                  <svg
                    v-else
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                  </svg>
                  <span>{{ isExporting ? "A exportar..." : "Exportar PDF" }}</span>
                </button>
              </div>
            </div>

            <div v-if="summaryStats.total > 0" class="mt-3">
              <span
                class="inline-flex items-center gap-2 px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-xs font-medium rounded-full"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path
                    fill-rule="evenodd"
                    d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"
                    clip-rule="evenodd"
                  />
                </svg>
                {{ summaryStats.total }} indicadores activos no sistema
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- KPIs Grid -->
      <div class="space-y-4">
        <div class="flex items-center gap-3">
          <div
            class="w-1 h-8 bg-gradient-to-b from-primary-500 to-orange-600 rounded-full"
          ></div>
          <h2 class="text-xl font-bold text-gray-900 dark:text-white">
            Indicadores de Performance
          </h2>
        </div>

        <div
          class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4 lg:gap-6"
        >
          <KpiCard
            v-for="stat in summaryStats"
            :key="stat.title"
            :title="stat.title"
            :value="stat.value"
            :description="stat.description"
            :icon="stat.icon"
            :trend="stat.trend"
          />
        </div>
      </div>

      <!-- Main Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Indicators -->
        <div class="lg:col-span-2 space-y-6">
          <TechnicianPerformanceTable
            v-if="technicianPerformance.length > 0"
            :technicians="technicianPerformance"
          />

          <ResolutionTimelineChart
            v-if="resolutionTimeline.length > 0"
            :data="resolutionTimeline"
          />
        </div>

        <!-- Right Column - Charts -->
        <div class="space-y-6">
          <CategoryDistributionChart
            v-if="translatedCategoryDistribution.length > 0"
            :data="translatedCategoryDistribution"
          />

          <QuickStats :stats="quickStats" />
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { router } from "@inertiajs/vue3";
import Layout from "@/Layouts/UnifiedLayout.vue";
import Toast from "@/Components/Toast.vue";

// Import components
import KpiCard from "@/Components/GestorReclamacoes/KpiCard.vue";
import TechnicianPerformanceTable from "@/Components/Indicators/TechnicianPerformanceTable.vue";
import CategoryDistributionChart from "@/Components/Indicators/Charts/CategoryDistributionChart.vue";
import ResolutionTimelineChart from "@/Components/Indicators/Charts/ResolutionTimelineChart.vue";
import QuickStats from "@/Components/Indicators/QuickStats.vue";
import { useIndicators } from "@/Components/Indicators/Composables/useIndicators";
import { useIndicatorCharts } from "@/Components/Indicators/Composables/useIndicatorCharts";

const props = defineProps({
  indicators: {
    type: Array,
    default: () => [],
  },
  grievanceStats: {
    type: Object,
    default: () => ({}),
  },
  technicianPerformance: {
    type: Array,
    default: () => [],
  },
  categoryDistribution: {
    type: Array,
    default: () => [],
  },
  resolutionTimeline: {
    type: Array,
    default: () => [],
  },
  timeRange: {
    type: String,
    default: "month",
  },
  categoryFilter: {
    type: String,
    default: "all",
  },
  dateRange: {
    type: Object,
    default: () => ({}),
  },
});

// Use composables
const {
  timeRange,
  categoryFilter,
  loading,
  exportFormat,
  filteredIndicators,
  translatedCategoryDistribution,
  summaryStats,
  quickStats,

  // Methods
  updateFilters,
  exportIndicators,
  translateCategory,
  getPerformanceColor,
  formatValue,
  getInitials,
} = useIndicators(props);

// Referência para o Toast
const toast = ref(null);
const isExporting = computed(() => loading.value.exporting);

const exportPdf = async () => {
  if (loading.value.exporting) return;
  loading.value.exporting = true;

  try {
    const response = await axios.get(route("manager.indicators.export.pdf"), {
      params: {
        time_range: timeRange.value,
        category: categoryFilter.value,
        date_range: props.dateRange,
      },
      responseType: "blob",
      headers: {
        Accept: "application/pdf",
        "X-Requested-With": "XMLHttpRequest",
      },
    });

    // Verificar se é um PDF
    const contentType = response.headers['content-type'];
    const contentDisposition = response.headers['content-disposition'];

    // Se não for PDF, pode ser JSON com erro
    if (!contentType.includes('application/pdf')) {
      // Tentar ler como texto para ver o erro
      const text = await response.data.text();

      // Verificar se é JSON
      if (text.trim().startsWith('{') || text.trim().startsWith('[')) {
        try {
          const errorData = JSON.parse(text);
          throw new Error(errorData.error || errorData.message || 'Erro desconhecido');
        } catch {
          throw new Error(`Erro do servidor: ${text.substring(0, 100)}...`);
        }
      }
      throw new Error('Resposta inesperada do servidor');
    }

    // Criar URL para download
    const blob = new Blob([response.data], { type: 'application/pdf' });
    const url = window.URL.createObjectURL(blob);

    // Criar link para download
    const link = document.createElement('a');
    link.href = url;

    // Extrair nome do arquivo do header ou usar padrão
    let fileName = `dashboard-indicadores-${new Date().toISOString().split('T')[0]}.pdf`;
    if (contentDisposition) {
      const match = contentDisposition.match(/filename="(.+)"/);
      if (match) {
        fileName = match[1];
      }
    }

    link.download = fileName;
    document.body.appendChild(link);
    link.click();

    // Limpar
    setTimeout(() => {
      link.remove();
      window.URL.revokeObjectURL(url);
    }, 100);

    toast.value?.showToast("PDF exportado com sucesso!", "success");
  } catch (error) {
    console.error("Erro detalhado:", error);
    toast.value?.showToast(error.message || "Erro ao exportar PDF", "error");
  } finally {
    loading.value.exporting = false;
  }
};

// Use chart composable
const { initCharts, initIndicatorCharts } = useIndicatorCharts(
  translatedCategoryDistribution,
  props.resolutionTimeline,
  props.indicators
);

// Lifecycle
onMounted(() => {
  initCharts();
  setTimeout(() => {
    initIndicatorCharts();
  }, 100);
});

// Watchers
watch(
  () => translatedCategoryDistribution.value,
  () => {
    initCharts();
  },
  { deep: true }
);

watch(
  () => props.resolutionTimeline,
  () => {
    initCharts();
  },
  { deep: true }
);
</script>
