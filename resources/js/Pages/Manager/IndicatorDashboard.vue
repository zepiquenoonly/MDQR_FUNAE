<template>
  <Layout>
    <div class="space-y-6">
      <!-- Header -->
      <IndicatorsHeader
        :time-range="timeRange"
        :category-filter="categoryFilter"
        :loading="loading"
        @update-filters="updateFilters"
        @update:timeRange="(val) => (timeRange = val)"
        @update:categoryFilter="(val) => (categoryFilter = val)"
        @open-report-generator="openReportGenerator"
      />

      <!-- Toast Component -->
      <Toast ref="toast" />

      <!-- Summary Cards -->
      <SummaryCards :stats="summaryStats" />

      <!-- Main Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Indicators -->
        <div class="lg:col-span-2 space-y-6">
          <TechnicianPerformanceTable
            v-if="technicianPerformance.length > 0"
            :technicians="technicianPerformance"
          />

          <TechnicianPerformanceTable
            v-if="technicianPerformance.length > 0"
            :technicians="technicianPerformance"
          />
        </div>

        <!-- Right Column - Charts -->
        <div class="space-y-6">
          <CategoryDistributionChart
            v-if="translatedCategoryDistribution.length > 0"
            :data="translatedCategoryDistribution"
          />

          <ResolutionTimelineChart
            v-if="resolutionTimeline.length > 0"
            :data="resolutionTimeline"
          />

          <QuickStats :stats="quickStats" />
        </div>
      </div>
    </div>

    <!-- Report Generator Modal -->
    <ReportGeneratorModal
      v-if="showReportModal"
      :date-range="dateRange"
      :indicators="indicators"
      @close="closeReportModal"
      @generate="generateReport"
    />
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { router } from "@inertiajs/vue3";
import Layout from "@/Layouts/UnifiedLayout.vue";
import ReportGeneratorModal from "@/Components/GestorReclamacoes/ReportGeneratorModal.vue";
import Toast from "@/Components/Toast.vue";

// Import components
import IndicatorsHeader from "@/Components/Indicators/IndicatorsHeader.vue";
import SummaryCards from "@/Components/Indicators/SummaryCards.vue";
import PerformanceIndicators from "@/Components/Indicators/PerformanceIndicators.vue";
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
  showReportModal,
  loading,
  exportFormat,
  filteredIndicators,
  translatedCategoryDistribution,
  summaryStats,
  quickStats,

  // Methods
  updateFilters,
  openReportGenerator,
  closeReportModal,
  exportIndicators,
  generateReport,
  translateCategory,
  getPerformanceColor,
  formatValue,
  getInitials,
} = useIndicators(props);

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
