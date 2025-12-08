<template>
  <div
    class="bg-white dark:bg-dark-secondary rounded-xl p-6 border border-gray-200 dark:border-gray-700"
  >
    <h2 class="text-lg font-semibold text-gray-900 dark:text-dark-text-primary mb-6">
      Performance dos Técnicos
    </h2>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead>
          <tr
            class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
          >
            <th class="px-4 py-3">Técnico</th>
            <th class="px-4 py-3 text-center">Casos Totais</th>
            <th class="px-4 py-3 text-center">Resolvidos</th>
            <th class="px-4 py-3 text-center">Taxa</th>
            <th class="px-4 py-3 text-center">Tempo Médio</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr
            v-for="tech in technicians"
            :key="tech.id"
            class="hover:bg-gray-50 dark:hover:bg-dark-accent transition-colors"
          >
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <div
                  class="w-8 h-8 bg-brand text-white rounded-full flex items-center justify-center font-medium text-sm"
                >
                  {{ getInitials(tech.name) }}
                </div>
                <span class="font-medium text-gray-900 dark:text-dark-text-primary">
                  {{ tech.name }}
                </span>
              </div>
            </td>
            <td class="px-4 py-3 text-center">
              <span class="font-medium text-gray-900 dark:text-dark-text-primary">
                {{ tech.total_cases || 0 }}
              </span>
            </td>
            <td class="px-4 py-3 text-center">
              <span class="font-medium text-green-600 dark:text-green-400">
                {{ tech.resolved_cases || 0 }}
              </span>
            </td>
            <td class="px-4 py-3 text-center">
              <div class="flex items-center justify-center gap-2">
                <span class="font-medium text-gray-900 dark:text-dark-text-primary">
                  {{
                    tech.resolution_rate !== null && tech.resolution_rate !== undefined
                      ? `${Number(tech.resolution_rate).toFixed(1)}%`
                      : "0.0%"
                  }}
                </span>
                <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                  <div
                    :class="getPerformanceColor(tech.resolution_rate || 0)"
                    class="h-2 rounded-full"
                    :style="{
                      width: `${Math.min(tech.resolution_rate || 0, 100)}%`,
                    }"
                  ></div>
                </div>
              </div>
            </td>
            <td class="px-4 py-3 text-center">
              <span class="font-medium text-gray-900 dark:text-dark-text-primary">
                {{ (tech.avg_resolution_time || 0).toFixed(1) }} dias
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import {
  getInitials,
  getPerformanceColor,
} from "@/Components/Indicators/Composables/indicatorUtils";

defineProps({
  technicians: {
    type: Array,
    required: true,
    default: () => [],
  },
});
</script>
