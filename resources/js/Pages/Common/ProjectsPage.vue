<template>
  <UnifiedLayout :stats="stats" @change-view="handleViewChange">
    <ProjectsManager :can-edit="canEdit" :projects="projects" :stats="stats" />
  </UnifiedLayout>
</template>

<script setup>
import { computed } from "vue";
import UnifiedLayout from "@/Layouts/UnifiedLayout.vue";
import ProjectsManager from "@/Components/Projects/ProjectManager.vue";
import { useAuth, usePermissions } from "@/Composables/useAuth";

// Usar useAuth para permissões
const { role, user } = useAuth();

const props = defineProps({
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
});

// Determinar permissões baseadas no role
const canEdit = computed(() => {
  return role.value === "admin" || role.value === "super_admin";
});

const handleViewChange = (view) => {
  console.log("Mudando para view:", view);
};
</script>
