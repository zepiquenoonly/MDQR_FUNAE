<!-- TechnicianPage.vue -->
<template>
  <UnifiedLayout :stats="stats">
    <div class="space-y-3 sm:space-y-6">
      <!-- Banner Informativo -->
      <div
        class="bg-gradient-to-r from-primary-50 to-orange-50 dark:from-primary-900/20 dark:to-orange-900/20 border border-primary-200 dark:border-primary-800 rounded-xl p-4 sm:p-6 mb-6"
      >
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <div
              class="w-12 h-12 bg-primary-100 dark:bg-primary-900/40 rounded-lg flex items-center justify-center"
            >
              <UserGroupIcon class="w-6 h-6 text-primary-600 dark:text-primary-400" />
            </div>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-primary-900 dark:text-primary-100 mb-2">
              {{
                currentUserRole === "director" ? "Gestão de Equipa" : "Gestão de Técnicos"
              }}
            </h3>
            <p class="text-primary-700 dark:text-primary-300 text-sm leading-relaxed">
              {{
                currentUserRole === "director"
                  ? "Monitore a sua equipa completa (técnicos e gestores), acompanhe métricas de performance e otimize a distribuição de tarefas."
                  : "Monitore a equipa de técnicos, acompanhe métricas de performance e otimize a distribuição de tarefas para garantir eficiência operacional."
              }}
            </p>
            <div v-if="totalTechnicians > 0" class="mt-3">
              <span
                class="inline-flex items-center gap-2 px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-xs font-medium rounded-full"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path
                    fill-rule="evenodd"
                    d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                    clip-rule="evenodd"
                  />
                </svg>
                {{ totalTechnicians }}
                {{
                  currentUserRole === "director"
                    ? "membros na equipa"
                    : "técnicos registados no sistema"
                }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- KPIs Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <KpiCard
          :title="
            currentUserRole === 'director' ? 'Total da Equipa' : 'Total de Técnicos'
          "
          :value="stats.total_technicians || 0"
          :description="
            currentUserRole === 'director'
              ? 'Técnicos e gestores no sistema'
              : 'Técnicos registados no sistema'
          "
          icon="UserGroupIcon"
          trend="stable"
        />

        <KpiCard
          :title="currentUserRole === 'director' ? 'Membros Activos' : 'Técnicos Activos'"
          :value="stats.active_technicians || 0"
          :description="
            currentUserRole === 'director'
              ? 'Membros actualmente activos'
              : 'Técnicos actualmente activos'
          "
          icon="CheckCircleIcon"
          trend="up"
        />

        <KpiCard
          :title="
            currentUserRole === 'director' ? 'Membros Inactivos' : 'Técnicos Inactivos'
          "
          :value="stats.inactive_technicians || 0"
          :description="
            currentUserRole === 'director'
              ? 'Membros fora de serviço'
              : 'Técnicos fora de serviço'
          "
          icon="ClockIcon"
          trend="down"
        />

        <KpiCard
          :title="
            currentUserRole === 'director' ? 'Média de Tarefas' : 'Média de Tarefas'
          "
          :value="stats.average_tasks_per_technician || 0"
          :description="
            currentUserRole === 'director'
              ? 'Tarefas por membro da equipa'
              : 'Tarefas por técnico'
          "
          icon="ChartBarIcon"
          trend="stable"
        />
      </div>

      <!-- Técnicos Table -->
      <TechnicianTable
        :technicians="technicians"
        :counts="counts"
        :loading="loading"
        :current-user-role="current_user_role"
      />
    </div>
  </UnifiedLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
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
const { role } = useAuth();

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
  current_user_role: String, // Adicionar role do usuário atual
});

// Computed properties
const totalTechnicians = computed(() => {
  return props.stats?.total_technicians || 0;
});

const currentUserRole = computed(() => {
  return props.current_user_role || role.value || "manager";
});

// Verificar permissões de edição baseado no role
const canEdit = computed(() => {
  const userRole = currentUserRole.value;
  return userRole === "manager" || userRole === "admin" || userRole === "director";
});

const loading = ref(false);

// Função para aplicar filtro de role
const applyRoleFilter = (roleType) => {
  router.get(
    route("manager.technicians.index"),
    {
      ...props.filters,
      role: roleType,
    },
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
};
</script>
