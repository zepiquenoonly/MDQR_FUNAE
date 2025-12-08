<template>
  <div
    :class="[
      'fixed left-4 right-4 sm:left-auto sm:right-4 top-4 z-50 p-4 rounded shadow-lg border transform transition-all duration-300 max-w-sm',
      toast.type === 'success'
        ? 'bg-green-50 border-green-200 text-green-800 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300'
        : toast.type === 'error'
        ? 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300'
        : toast.type === 'warning'
        ? 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-900/20 dark:border-amber-800 dark:text-amber-300'
        : 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-300',
    ]"
  >
    <div class="flex items-center gap-3">
      <CheckCircleIcon
        v-if="toast.type === 'success'"
        class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0"
      />
      <ExclamationTriangleIcon
        v-else-if="toast.type === 'error' || toast.type === 'warning'"
        class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0"
      />
      <InformationCircleIcon
        v-else
        class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0"
      />
      <span class="font-medium flex-1">{{ toast.message }}</span>
      <button
        @click="$emit('close')"
        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex-shrink-0"
      >
        <XMarkIcon class="w-4 h-4" />
      </button>
    </div>
  </div>
</template>

<script setup>
import {
  CheckCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  XMarkIcon,
} from "@heroicons/vue/24/outline";

defineProps({
  toast: {
    type: Object,
    required: true,
    default: () => ({
      show: false,
      message: "",
      type: "success",
    }),
  },
});

defineEmits(["close"]);
</script>
