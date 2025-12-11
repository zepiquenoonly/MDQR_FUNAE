<template>
  <div class="glass-card hover:scale-[1.02] cursor-pointer">
    <div class="flex items-center justify-between">
      <div>
        <h3 class="text-gray-600 text-sm font-semibold">{{ title }}</h3>
        <div class="text-3xl font-bold text-black mt-2">{{ value }}</div>
        <p class="text-gray-500 text-sm mt-1">{{ description }}</p>
      </div>

      <!-- Icon & Trend -->
      <div class="flex flex-col items-end">
        <div
          class="w-14 h-14 bg-gradient-to-br from-primary-50 to-orange-50 rounded-xl flex items-center justify-center shadow-glass border border-primary-200"
        >
          <component :is="dynamicIcon" class="w-7 h-7 text-primary-600" />
        </div>

        <!-- Trend Indicator -->
        <div
          v-if="trend"
          :class="[
            'flex items-center gap-1 mt-2 text-xs font-semibold px-2 py-1 rounded-full',
            trend === 'up'
              ? 'bg-green-100 text-green-700'
              : trend === 'down'
              ? 'bg-red-100 text-red-700'
              : 'bg-gray-100 text-gray-700',
          ]"
        >
          <ArrowTrendingUpIcon v-if="trend === 'up'" class="w-3 h-3" />
          <ArrowTrendingDownIcon v-else-if="trend === 'down'" class="w-3 h-3" />
          <MinusIcon v-else class="w-3 h-3" />
          <span>{{ trendText }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  ExclamationTriangleIcon,
  ClockIcon,
  ExclamationCircleIcon,
  CheckCircleIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  MinusIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  title: String,
  value: [String, Number],
  description: String,
  icon: String,
  trend: String, // 'up', 'down', 'stable'
});

// Mapeamento de ícones
const iconMap = {
  ExclamationTriangleIcon,
  ClockIcon,
  ExclamationCircleIcon,
  CheckCircleIcon,
};

const dynamicIcon = computed(() => {
  return iconMap[props.icon] || ExclamationTriangleIcon;
});

const trendText = computed(() => {
  const texts = {
    up: "+12%",
    down: "-5%",
    stable: "0%",
  };
  return texts[props.trend] || "";
});
</script>
