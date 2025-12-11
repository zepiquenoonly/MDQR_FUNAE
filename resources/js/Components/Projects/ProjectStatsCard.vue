<template>
  <div
    class="bg-white dark:bg-dark-secondary rounded-lg sm:rounded-xl p-3 sm:p-6 shadow-sm hover:shadow-md transition-all duration-300"
  >
    <div class="flex items-center justify-between">
      <div class="min-w-0 flex-1">
        <p :class="['text-xs sm:text-sm font-bold truncate', titleColorClass]">
          {{ title }}
        </p>
        <p
          class="text-lg sm:text-2xl lg:text-4xl font-bold text-gray-900 dark:text-dark-text-primary mt-1 sm:mt-2"
        >
          {{ value }}
        </p>
        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 truncate">
          {{ description }}
        </p>
      </div>
      <div
        :class="[
          'w-6 h-6 sm:w-8 sm:h-8 lg:w-12 lg:h-12 rounded-full flex items-center justify-center flex-shrink-0 ml-2',
          iconBgClass,
        ]"
      >
        <component
          :is="icon"
          :class="['w-3 h-3 sm:w-4 sm:h-4 lg:w-6 lg:h-6', iconColorClass]"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from "vue";

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    required: true,
  },
  description: {
    type: String,
    required: true,
  },
  icon: {
    type: Object,
    required: true,
  },
  titleColorClass: {
    type: String,
    default: "text-blue-700 dark:text-blue-400",
  },
  iconBgClass: {
    type: String,
    default: "bg-blue-100 dark:bg-blue-900/20",
  },
  iconColorClass: {
    type: String,
    default: "text-blue-600 dark:text-blue-400",
  },
});
</script>
