<template>
  <div class="flex justify-between items-center">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-dark-text-primary">
        Técnicos
      </h1>
    </div>
    <div class="flex gap-3">
      <button
        @click="$emit('refresh')"
        class="px-4 py-2 bg-brand text-white rounded-lg hover:bg-orange-600 transition-colors flex items-center gap-2"
      >
        <ArrowPathIcon class="w-4 h-4" />
        Actualizar
      </button>
    </div>
  </div>
</template>

<script setup>
import { ArrowPathIcon } from "@heroicons/vue/24/outline";

defineEmits(["refresh"]);
</script>
