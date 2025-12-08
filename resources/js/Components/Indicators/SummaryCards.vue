<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <div
      v-for="stat in stats"
      :key="stat.title"
      class="bg-white dark:bg-dark-secondary rounded-xl p-6 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow"
    >
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
            {{ stat.title }}
          </p>
          <p class="text-2xl font-bold text-gray-900 dark:text-dark-text-primary mt-1">
            {{ stat.value }}
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            {{ stat.description }}
          </p>
        </div>
        <div :class="stat.iconBg" class="p-3 rounded-lg">
          <component :is="stat.icon" :class="stat.iconColor" class="w-6 h-6" />
        </div>
      </div>

      <div
        v-if="stat.trend !== null && stat.trend !== undefined"
        class="mt-3 flex items-center gap-1"
      >
        <ArrowTrendingUpIcon v-if="stat.trend > 0" class="w-4 h-4 text-green-500" />
        <ArrowTrendingDownIcon v-else-if="stat.trend < 0" class="w-4 h-4 text-red-500" />
        <MinusIcon v-else class="w-4 h-4 text-gray-400" />
        <span
          :class="
            stat.trend > 0
              ? 'text-green-600'
              : stat.trend < 0
              ? 'text-red-600'
              : 'text-gray-600'
          "
          class="text-sm font-medium"
        >
          {{ stat.trend > 0 ? "+" : ""
          }}{{
            stat.trend !== null && stat.trend !== undefined
              ? stat.trend.toFixed(1)
              : "0.0"
          }}%
        </span>
        <span class="text-xs text-gray-500 dark:text-gray-400 ml-2"
          >vs período anterior</span
        >
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

defineProps({
  stats: {
    type: Array,
    required: true,
    default: () => [],
  },
});
</script>
