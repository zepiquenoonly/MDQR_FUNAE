<!-- TechnicianPage.vue -->
<template>
  <UnifiedLayout :stats="stats">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1
            class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-dark-text-primary"
          >
            Gestão de Técnicos
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Total de técnicos: {{ totalTechnicians }}
          </p>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Técnicos -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900/20 rounded-lg p-3">
              <UserGroupIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ stats.total_technicians || 0 }}
              </p>
            </div>
          </div>
        </div>

        <!-- Card 2: Ativos -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-100 dark:bg-green-900/20 rounded-lg p-3">
              <CheckCircleIcon class="h-6 w-6 text-green-600 dark:text-green-400" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ativos</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ stats.active_technicians || 0 }}
              </p>
            </div>
          </div>
        </div>

        <!-- Card 3: Inativos -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg p-3">
              <ClockIcon class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Inativos</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ stats.inactive_technicians || 0 }}
              </p>
            </div>
          </div>
        </div>

        <!-- Card 4: Média Tarefas -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-purple-100 dark:bg-purple-900/20 rounded-lg p-3">
              <ChartBarIcon class="h-6 w-6 text-purple-600 dark:text-purple-400" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Média Tarefas
              </p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ stats.average_tasks_per_technician || 0 }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Técnicos Table -->
      <TechnicianTable
        :technicians="technicians"
        :loading="loading"
        :can-edit="canEdit"
      />
    </div>
  </UnifiedLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import UnifiedLayout from "@/Layouts/UnifiedLayout.vue";
import TechnicianTable from "@/Components/Technician/TechnicianTable.vue";
import { useAuth } from "@/Composables/useAuth";
import {
  UserGroupIcon,
  CheckCircleIcon,
  ClockIcon,
  ChartBarIcon,
} from "@heroicons/vue/24/outline";

// Usar useAuth para verificar permissões
const { role, permissions, checkRole } = useAuth();

// Props apenas com dados necessários
const props = defineProps({
  technicians: {
    type: [Object, Array],
    default: () => ({}),
  },
  stats: Object,
  filters: Object,
  top_technicians: Array,
  provinces: Array,
});

// Computed properties
const totalTechnicians = computed(() => {
  return props.stats?.total_technicians || 0;
});

// Verificar permissões de edição baseado no role
const canEdit = computed(() => {
  return checkRole("manager") || checkRole("admin") || checkRole("director");
});

const loading = ref(false);
</script>
