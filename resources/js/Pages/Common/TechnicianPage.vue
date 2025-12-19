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

      <!-- KPIs Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <KpiCard
          title="Total de Técnicos"
          :value="stats.total_technicians || 0"
          description="Técnicos registados no sistema"
          icon="UserGroupIcon"
          trend="stable"
        />

        <KpiCard
          title="Técnicos Ativos"
          :value="stats.active_technicians || 0"
          description="Técnicos atualmente ativos"
          icon="CheckCircleIcon"
          trend="up"
        />

        <KpiCard
          title="Técnicos Inativos"
          :value="stats.inactive_technicians || 0"
          description="Técnicos fora de serviço"
          icon="ClockIcon"
          trend="down"
        />

        <KpiCard
          title="Média de Tarefas"
          :value="stats.average_tasks_per_technician || 0"
          description="Tarefas por técnico"
          icon="ChartBarIcon"
          trend="stable"
        />
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
import KpiCard from "@/Components/GestorReclamacoes/KpiCard.vue";
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
