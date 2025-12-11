<template>
  <ChartWrapper :type="'pie'" :data="data" :options="options" />
</template>

<script setup>
import ChartWrapper from "./ChartWrapper.vue";

defineProps({
  data: {
    type: Object,
    required: true,
  },
  options: {
    type: Object,
    default: () => ({}),
  },
});
</script>
