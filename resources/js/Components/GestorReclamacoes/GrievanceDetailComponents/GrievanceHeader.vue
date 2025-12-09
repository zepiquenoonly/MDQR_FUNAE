<template>
  <div
    class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
  >
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
      <div class="flex-1">
        <h1
          class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-dark-text-primary"
        >
          {{ complaint.reference_number }}
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
          {{ complaint.title }}
        </p>
      </div>
      <StatusBadge
        :status="complaint.status"
        :label="getStatusText(complaint.status)"
        size="lg"
      />
    </div>
    <div class="flex flex-wrap gap-2">
      <span
        :class="priorityBadgeClass(complaint.priority)"
        class="rounded-full px-3 py-1 text-sm font-semibold"
      >
        {{ priorityLabel(complaint.priority) }}
      </span>
      <span
        class="rounded-full bg-blue-100 dark:bg-blue-900/20 px-3 py-1 text-sm text-blue-700 dark:text-blue-300 font-medium"
      >
        {{ complaint.category }}
      </span>
      <span
        class="rounded-full bg-purple-100 dark:bg-purple-900/20 px-3 py-1 text-sm text-purple-700 dark:text-purple-300 font-medium"
      >
        {{ getTypeText(complaint.type) }}
      </span>
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

// Funções auxiliares (estas podem ser movidas para um arquivo de utilitários)
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
</script>
