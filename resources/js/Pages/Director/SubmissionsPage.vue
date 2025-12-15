<!-- ComplaintsOverview.vue (ou sua página de director) -->
<template>
  <Layout :stats="safeStats">
    <div class="space-y-3 sm:space-y-6">
      <!-- Título da página -->
      <div class="mb-4 sm:mb-6">
        <h1
          class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-dark-text-primary"
        >
          Submissões - Reclamações, Queixas e Sugestões
        </h1>
        <p class="text-gray-600 dark:text-gray-400 text-sm sm:text-base mt-1 sm:mt-2">
          Gere reclamações, queixas e sugestões do seu departamento
        </p>
      </div>

      <!-- KPIs Grid -->
      <div
        class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4 lg:gap-6"
      >
        <KpiCard
          title="Total de Submissões"
          :value="safeStats.total || safeAllComplaints.length"
          description="Todas as reclamações, queixas e sugestões"
          icon="DocumentTextIcon"
          trend="up"
        />

        <KpiCard
          title="Pendentes"
          :value="pendingCount"
          description="Aguardando ação"
          icon="ClockIcon"
          trend="stable"
        />

        <KpiCard
          title="Resolvidas"
          :value="resolvedCount"
          description="Finalizadas com sucesso"
          icon="CheckCircleIcon"
          trend="down"
        />

        <KpiCard
          title="Casos Escalados"
          :value="escalatedCasesCount"
          description="Solicitados pelo Director"
          icon="PaperAirplaneIcon"
          trend="up"
        />
      </div>

      <!-- ComplaintsList - SIMPLIFICADO -->
      <div class="grid grid-cols-1 gap-3 sm:gap-6">
        <div>
          <ComplaintsList
            :complaints="formattedAllComplaints"
            :filters="combinedFilters"
            :all-complaints="formattedAllComplaints"
            :role="authenticatedRole"
            @update:filters="updateFilters"
            @select-complaint="selectComplaint"
            ref="complaintsListRef"
          />
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";

// Importar componentes do Dashboard
import Layout from "@/Layouts/UnifiedLayout.vue";
import KpiCard from "@/Components/GestorReclamacoes/KpiCard.vue";
import ComplaintsList from "@/Components/GestorReclamacoes/ComplaintsList.vue";
import { usePageProps } from "@/Composables/usePageProps";

// Obter o usuário autenticado
const page = usePage();
const { user } = usePage().props;

const authenticatedRole = computed(() => {
  const authUser = page.props.auth?.user;

  if (authUser?.role) {
    return authUser.role;
  }

  if (props.user?.role) {
    return props.user.role;
  }

  // Fallback para props.role
  if (props.role) {
    return props.role;
  }

  return "utente";
});

// Props do backend
const props = defineProps({
  submissions: {
    type: [Array, null],
    default: () => [],
  },
  allComplaints: {
    type: [Array, null],
    default: () => [],
  },
  stats: {
    type: [Object, null],
    default: () => ({}),
  },
  managers: {
    type: [Array, null],
    default: () => [],
  },
  filters: {
    type: [Object, null],
    default: () => ({}),
  },
});

// Referência ao componente ComplaintsList
const complaintsListRef = ref(null);

// Usar composable para safe props
const { getSafeProp } = usePageProps(props);
const safeSubmissions = getSafeProp("submissions", []);
const safeAllComplaints = getSafeProp("allComplaints", []);
const safeStats = getSafeProp("stats", {});
const safeManagers = getSafeProp("managers", []);
const safeFilters = getSafeProp("filters", {});

// Estado local
const selectedComplaint = ref(null);
const showDirectorFilters = ref(false);
const directorFilters = ref({
  department: "",
  manager: "",
  specificity: "",
  period: "",
});

// Computed properties
const formattedAllComplaints = computed(() => {
  // Usar submissions se existir, caso contrário usar allComplaints
  const sourceData =
    safeSubmissions.value.length > 0 ? safeSubmissions.value : safeAllComplaints.value;

  return sourceData.map((item) => ({
    id: item.id || item.reference_number,
    reference_number: item.reference_number || item.id,
    title: item.subject || item.title || item.description?.substring(0, 50) + "...",
    description: item.description || "",
    priority: item.priority,
    status: item.status,
    type: item.type,
    category: item.category || item.department,
    created_at: item.created_at || item.submitted_at,
    user: item.user || { name: item.user_name || "Utente" },
    technician: item.technician || item.assigned_to,
    escalated: item.escalated,
    metadata: item.metadata,
    escalated_by: item.escalated_by,
    escalation_reason: item.escalation_reason,
    escalated_at: item.escalated_at,
    assigned_manager_id: item.assigned_manager_id,
    department: item.department,
  }));
});

// Contar casos escalados
const escalatedCasesCount = computed(() => {
  return formattedAllComplaints.value.filter(
    (complaint) =>
      complaint.escalated === true ||
      complaint.metadata?.is_escalated_to_director === true ||
      complaint.status === "escalated"
  ).length;
});

// Contar pendentes
const pendingCount = computed(() => {
  return formattedAllComplaints.value.filter(
    (complaint) =>
      complaint.status === "pending" ||
      complaint.status === "submitted" ||
      complaint.status === "under_review"
  ).length;
});

// Contar resolvidos
const resolvedCount = computed(() => {
  return formattedAllComplaints.value.filter(
    (complaint) => complaint.status === "resolved"
  ).length;
});

// Combinar filtros
const combinedFilters = computed(() => {
  return {
    ...safeFilters.value,
    ...directorFilters.value,
  };
});

// Funções auxiliares para labels
const getTypeLabel = (type) => {
  if (!type) return "Tipo não definido";

  const normalizedType = type.toLowerCase();
  const labels = {
    suggestion: "Sugestão",
    grievance: "Queixa",
    complaint: "Reclamação",
    sugestão: "Sugestão",
    sugestao: "Sugestão",
    queixa: "Queixa",
    reclamação: "Reclamação",
    reclamacao: "Reclamação",
  };

  return labels[normalizedType] || type.charAt(0).toUpperCase() + type.slice(1);
};

const getPriorityLabel = (priority) => {
  if (!priority) return "Prioridade não definida";

  const labels = {
    low: "Baixa",
    medium: "Média",
    high: "Alta",
    critical: "Crítica",
  };

  return labels[priority] || priority.charAt(0).toUpperCase() + priority.slice(1);
};

const getStatusLabel = (status) => {
  if (!status) return "Estado não definido";

  const labels = {
    pending: "Pendente",
    submitted: "Submetida",
    in_progress: "Em Progresso",
    resolved: "Resolvida",
    closed: "Fechada",
    escalated: "Escalada",
    under_review: "Em Análise",
    pending_approval: "Pendente Aprovação",
    assigned: "Atribuída",
    rejected: "Rejeitada",
  };

  return labels[status] || status.charAt(0).toUpperCase() + status.slice(1);
};

// Handlers
const selectComplaint = (complaint) => {
  selectedComplaint.value = complaint;
};

const updateFilters = (newFilters) => {
  // Actualizar apenas os filtros básicos
  const basicFilters = { ...newFilters };
  delete basicFilters.department;
  delete basicFilters.manager;
  delete basicFilters.specificity;
  delete basicFilters.period;

  // Recarregar com novos filtros
  router.reload({
    data: basicFilters,
    preserveState: true,
    replace: true,
    only: ["submissions", "stats", "filters", "allComplaints", "managers"],
  });
};

// Watch para atualizar filtros baseado nos props
watch(
  () => props.filters,
  (newFilters) => {
    console.log("Filtros recebidos:", newFilters);
    // Você pode adicionar lógica aqui se necessário
  },
  { immediate: true }
);

// Inicializar quando o componente for montado
onMounted(() => {
  console.log("SubmissionsPage montado para:", authenticatedRole.value);
  console.log("Total de submissões:", formattedAllComplaints.value?.length);
});
</script>
