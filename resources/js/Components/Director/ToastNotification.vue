<template>
  <Transition
    enter-active-class="transform ease-out duration-300 transition"
    enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
    leave-active-class="transition ease-in duration-100"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div v-if="toast.show" class="fixed top-4 right-4 z-50 max-w-sm w-full">
      <div class="rounded-lg shadow-lg p-4" :class="toastClasses">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <component :is="toastIcon" class="h-5 w-5" />
          </div>
          <div class="ml-3 w-0 flex-1 pt-0.5">
            <p class="text-sm font-medium">
              {{ toast.title }}
            </p>
            <p class="mt-1 text-sm opacity-90">
              {{ toast.message }}
            </p>
          </div>
          <div class="ml-4 flex-shrink-0 flex">
            <button
              @click="$emit('close')"
              class="rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <span class="sr-only">Fechar</span>
              <XMarkIcon class="h-5 w-5" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from "vue";
import {
  CheckCircleIcon,
  XCircleIcon,
  InformationCircleIcon,
  XMarkIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  toast: {
    type: Object,
    required: true,
    default: () => ({
      show: false,
      type: "success",
      title: "",
      message: "",
    }),
  },
});

const emit = defineEmits(["close"]);

const toastClasses = computed(() => {
  const classes = {
    success: "bg-green-50 border border-green-200 text-green-800",
    error: "bg-red-50 border border-red-200 text-red-800",
    warning: "bg-yellow-50 border border-yellow-200 text-yellow-800",
    info: "bg-blue-50 border border-blue-200 text-blue-800",
  };
  return classes[props.toast.type] || classes.info;
});

const toastIcon = computed(() => {
  const icons = {
    success: CheckCircleIcon,
    error: XCircleIcon,
    warning: InformationCircleIcon,
    info: InformationCircleIcon,
  };
  return icons[props.toast.type] || InformationCircleIcon;
});
</script>
