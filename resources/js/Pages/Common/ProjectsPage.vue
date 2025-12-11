<template>
  <UnifiedLayout :user="user" :role="role" :stats="stats" @change-view="handleViewChange">
    <ProjectsManager
      :can-edit="canEdit"
      :initial-projects="projects"
      :initial-stats="stats"
    />
  </UnifiedLayout>
</template>

<script setup>
import { computed } from "vue";
import { router } from "@inertiajs/vue3";
import UnifiedLayout from "@/Layouts/UnifiedLayout.vue";
import ProjectsManager from "@/Components/Projects/ProjectManager.vue";
import { usePageProps } from "@/Composables/usePageProps";
// Props recebidos do backend via Inertia
const props = defineProps({
  user: {
    type: Object,
    default: null,
  },
  role: {
    type: String,
    default: null,
  },
  stats: {
    type: Object,
    default: () => ({
      total: 0,
      finished: 0,
      progress: 0,
      suspended: 0,
    }),
  },
  projects: {
    type: Array,
    default: () => [],
  },
  canEdit: {
    type: Boolean,
    default: false,
  },
});

// Handlers
const handleViewChange = (view) => {
  console.log("Mudando para view:", view);
  // Você pode usar Inertia para navegar se necessário
  // Exemplo: Inertia.visit(`/${view}`)
};
</script>
