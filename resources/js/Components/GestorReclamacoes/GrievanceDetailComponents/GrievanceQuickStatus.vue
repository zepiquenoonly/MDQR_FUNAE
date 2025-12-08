<template>
  <div
    class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
  >
    <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4">
      Estado
    </h2>
    <div class="space-y-3">
      <div
        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg"
      >
        <span class="text-sm text-gray-600 dark:text-gray-400">Estado</span>
        <StatusBadge
          :status="complaint.status"
          :label="getStatusText(complaint.status)"
          size="sm"
        />
      </div>
      <div
        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg"
      >
        <span class="text-sm text-gray-600 dark:text-gray-400">Prioridade</span>
        <span
          :class="priorityBadgeClass(complaint.priority)"
          class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary"
        >
          {{ priorityLabel(complaint.priority) }}
        </span>
      </div>

      <div
        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg"
      >
        <span class="text-sm text-gray-600 dark:text-gray-400">Impacto</span>
        <span
          :class="priorityBadgeClass(complaint.category)"
          class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary"
        >
          {{ getImpactText(complaint.category) }}
        </span>
      </div>

      <div
        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg"
      >
        <span class="text-sm text-gray-600 dark:text-gray-400">Tipo de submissão</span>
        <span class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary">
          {{ getTypeText(complaint.type) }}
        </span>
      </div>
      <div
        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg"
      >
        <span class="text-sm text-gray-600 dark:text-gray-400">Técnico</span>
        <span
          class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary flex items-center gap-1"
        >
          <div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div>
          {{ complaint.technician?.name || "Não atribuído" }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import StatusBadge from "@/Components/Grievance/StatusBadge.vue";

const props = defineProps({
  complaint: {
    type: Object,
    required: true,
  },
});

// Funções auxiliares
const getTypeText = (type) => {
  const types = {
    grievance: "Queixa",
    complaint: "Reclamação",
    suggestion: "Sugestão",
  };
  return types[type] || type;
};

const priorityLabel = (priority) => {
  const map = {
    low: "Baixa",
    medium: "Média",
    high: "Alta",
  };
  return map[priority] ?? priority ?? "N/D";
};

const priorityBadgeClass = (priority) => {
  const map = {
    low: "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400",
    medium: "bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400",
    high: "bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400",
  };
  return map[priority] ?? "bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300";
};

const getStatusText = (status) => {
  const statusTexts = {
    submitted: "Submetida",
    under_review: "Em Análise",
    assigned: "Atribuída",
    in_progress: "Em Andamento",
    pending_approval: "Pendente de Aprovação",
    resolved: "Resolvida",
    rejected: "Rejeitada",
  };
  return statusTexts[status] || status;
};

const getImpactText = (category) => {
  if (!category) return "N/D";
  return category.charAt(0).toUpperCase() + category.slice(1).toLowerCase();
};
</script>
