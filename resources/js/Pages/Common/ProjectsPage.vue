<template>
  <Layout :stats="stats">
    <div class="space-y-4 sm:space-y-8">
      <!-- Banner Informativo -->
      <div
        class="bg-gradient-to-r from-primary-50 to-orange-50 dark:from-primary-900/20 dark:to-orange-900/20 border border-primary-200 dark:border-primary-800 rounded-xl p-4 sm:p-6 mb-6"
      >
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <div
              class="w-12 h-12 bg-primary-100 dark:bg-primary-900/40 rounded-lg flex items-center justify-center"
            >
              <FolderIcon class="w-6 h-6 text-primary-600 dark:text-primary-400" />
            </div>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-primary-900 dark:text-primary-100 mb-2">
              Visão Geral de Projectos
            </h3>
            <p class="text-primary-700 dark:text-primary-300 text-sm leading-relaxed">
              Veja todos os projectos existentes e confira os seus detalhes.
            </p>
            <div v-if="stats.total > 0" class="mt-3">
              <span
                class="inline-flex items-center gap-2 px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-xs font-medium rounded-full"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path
                    fill-rule="evenodd"
                    d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"
                    clip-rule="evenodd"
                  />
                </svg>
                {{ stats.total }} projectos registados no sistema
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- KPIs Grid -->
      <div class="space-y-4">
        <div class="flex items-center gap-3">
          <div
            class="w-1 h-8 bg-gradient-to-b from-primary-500 to-primary-600 rounded-full"
          ></div>
          <h2 class="text-xl font-bold text-gray-900 dark:text-white">
            Métricas de Performance
          </h2>
        </div>

        <div class="grid grid-cols-1 xs:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
          <KpiCard
            title="Finalizados"
            :value="stats.finished"
            description="Projectos Finalizados"
            icon="CheckCircleIcon"
            trend="up"
          />

          <KpiCard
            title="Em Andamento"
            :value="stats.progress"
            description="Projectos em Andamento"
            icon="ClockIcon"
            trend="stable"
          />

          <KpiCard
            title="Parados"
            :value="stats.suspended"
            description="Projectos Parados"
            icon="PauseCircleIcon"
            trend="down"
          />

          <KpiCard
            title="Total"
            :value="stats.total"
            description="Todos Projectos"
            icon="FolderIcon"
            trend="up"
          />
        </div>
      </div>

      <!-- Projects Manager -->
      <div class="space-y-4">
        <div class="flex items-center gap-3">
          <div
            class="w-1 h-8 bg-gradient-to-b from-primary-500 to-primary-600 rounded-full"
          ></div>
          <h2 class="text-xl font-bold text-gray-900 dark:text-white">
            Lista de Projectos
          </h2>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:gap-6">
          <div
            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden"
          >
            <ProjectsManager :projects="projects" :stats="stats" />
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { computed } from "vue";
import { FolderIcon } from "@heroicons/vue/24/outline";
import Layout from "@/Layouts/UnifiedLayout.vue";
import KpiCard from "@/Components/GestorReclamacoes/KpiCard.vue";
import ProjectsManager from "@/Components/Projects/ProjectManager.vue";
import { useAuth } from "@/Composables/useAuth";

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
  canEdit: {
    type: Boolean,
    default: false,
  },
});

// Determinar permissões baseadas no role
const canEdit = computed(() => {
  return props.canEdit || role.value === "admin" || role.value === "super_admin";
});
</script>
