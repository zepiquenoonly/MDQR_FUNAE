<template>
  <div
    class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-brand dark:hover:border-orange-500 transition-colors"
  >
    <div class="flex items-center justify-between mb-2">
      <div>
        <h3 class="font-medium text-gray-900 dark:text-dark-text-primary">
          {{ indicator.name }}
        </h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
          {{ indicator.description }}
        </p>
      </div>
      <span
        :class="getIndicatorStatusClass(indicator.category)"
        class="px-2 py-1 rounded-full text-xs font-medium"
      >
        {{ translateCategory(indicator.category) }}
      </span>
    </div>

    <div class="flex items-end justify-between">
      <div>
        <p class="text-2xl font-bold text-gray-900 dark:text-dark-text-primary">
          {{ indicator.formatted_value }}
        </p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
          Meta:
          {{
            indicator.target_value
              ? formatValue(indicator.target_value, indicator.measurement_unit)
              : "Não definida"
          }}
        </p>
      </div>

      <div class="text-right">
        <div class="flex items-center gap-1 justify-end">
          <ArrowTrendingUpIcon
            v-if="indicator.trend > 0"
            class="w-4 h-4 text-green-500"
          />
          <ArrowTrendingDownIcon
            v-else-if="indicator.trend < 0"
            class="w-4 h-4 text-red-500"
          />
          <MinusIcon v-else class="w-4 h-4 text-gray-400" />

          <span
            :class="{
              'text-green-600': indicator.trend > 0,
              'text-red-600': indicator.trend < 0,
              'text-gray-600': indicator.trend === 0 || indicator.trend === null,
            }"
            class="text-sm font-medium"
          >
            {{
              indicator.trend !== null && indicator.trend !== undefined
                ? `${indicator.trend > 0 ? "+" : ""}${Number(indicator.trend).toFixed(
                    1
                  )}%`
                : "N/A"
            }}
          </span>
        </div>

        <div
          v-if="indicator.performance !== null && indicator.performance !== undefined"
          class="mt-1"
        >
          <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
            <div
              :class="getPerformanceColor(indicator.performance)"
              class="h-2 rounded-full"
              :style="{ width: `${Math.min(indicator.performance, 100)}%` }"
            ></div>
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            {{
              indicator.performance !== null && indicator.performance !== undefined
                ? `${Number(indicator.performance).toFixed(1)}% da meta`
                : "Meta não alcançável"
            }}
          </p>
        </div>
      </div>
    </div>

    <!-- Mini Chart -->
    <div v-if="indicator.records && indicator.records.length > 1" class="mt-4">
      <div class="h-20">
        <canvas :data-indicator-chart="indicator.id" class="w-full h-full"></canvas>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  MinusIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  indicator: {
    type: Object,
    required: true,
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

const getIndicatorStatusClass = (category) => {
  const categoryLower = category.toLowerCase();
  const classes = {
    performance: "bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300",
    satisfaction: "bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300",
    efficiency:
      "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300",
    quality: "bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-300",
  };

  return (
    classes[categoryLower] ||
    "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300"
  );
};
</script>
