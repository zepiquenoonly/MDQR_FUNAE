<template>
  <div
    class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4 sm:mb-6"
  >
    <h3
      class="text-base sm:text-lg font-semibold text-gray-800 dark:text-dark-text-primary"
    >
      Listagem de Projectos
    </h3>

    <ProjectSearchBar
      :placeholder="'Pesquisar projectos...'"
      :show-create-button="canEdit"
      @search="$emit('search', $event)"
      @create="$emit('create')"
    />
  </div>
</template>

<script setup>
import ProjectSearchBar from "./ProjectSearchBar.vue";

const emit = defineEmits(["search", "create"]);

const props = defineProps({
  canEdit: {
    type: Boolean,
    default: false,
  },
});
</script>
