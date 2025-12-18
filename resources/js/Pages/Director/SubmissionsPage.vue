<!-- SubmissionsPage.vue - template atualizado -->
<template>
  <Layout :stats="safeStats">
    <div class="space-y-3 sm:space-y-6">
      <!-- Título da página -->
      <div class="mb-4 sm:mb-6">
        <h1
          class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-dark-text-primary"
        >
          <span v-if="isDirector">Gestão de Submissões - Visão do Director</span>
          <span v-else-if="isManager">Submissões do Departamento</span>
          <span v-else>Minhas Submissões</span>
        </h1>
        <p class="text-gray-600 dark:text-gray-400 text-sm sm:text-base mt-1 sm:mt-2">
          <span v-if="isDirector">
            Veja e gere todas as submissões, sejam sugestões, queixas e reclamações
          </span>
          <span v-else-if="isManager">
            Gere reclamações, queixas e sugestões do seu departamento
          </span>
          <span v-else> Visualize suas submissões </span>
        </p>
      </div>

      <!-- KPIs Grid -->
      <div
        class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4 lg:gap-6"
      >
        <KpiCard
          :title="isDirector ? 'Total de Casos' : 'Total de Submissões'"
          :value="safeStats.total || formattedAllComplaints.length"
          :description="
            isDirector
              ? 'Todos os casos do sistema'
              : 'Todas as reclamações, queixas e sugestões'
          "
          icon="DocumentTextIcon"
          trend="up"
        />

        <KpiCard
          title="Pendentes"
          :value="pendingCount"
          :description="isDirector ? 'Casos pendentes no sistema' : 'Aguardando ação'"
          icon="ClockIcon"
          trend="stable"
        />

        <KpiCard
          title="Resolvidas"
          :value="resolvedCount"
          :description="isDirector ? 'Casos finalizados' : 'Finalizadas com sucesso'"
          icon="CheckCircleIcon"
          trend="down"
        />

        <KpiCard
          :title="isDirector ? 'Solicitações do Gestor' : 'Casos Escalados'"
          :value="escalatedCasesCount"
          :description="
            isDirector ? 'Aguardando análise do director' : 'Solicitados pelo Director'
          "
          icon="PaperAirplaneIcon"
          trend="up"
        />
      </div>

      <!-- ComplaintsList -->
      <div class="grid grid-cols-1 gap-3 sm:gap-6">
        <div>
          <ComplaintsList
            :complaints="formattedAllComplaints"
            :filters="combinedFilters"
            :all-complaints="formattedAllComplaints"
            :role="authenticatedRole"
            :recent-submissions="recentSubmissions"
            :director-interventions="props.director_interventions || []"
            :manager-requests="props.manager_requests || []"
            :my-submissions-to-director="props.my_submissions_to_director || []"
            :counts="{
              suggestions: counts.suggestions,
              grievances: counts.grievances,
              complaints: counts.complaints,
              director_interventions: counts.director_interventions,
              manager_requests: counts.manager_requests,
              my_submissions_to_director: isManager
                ? counts.my_submissions_to_director
                : 0,
              total: formattedAllComplaints.length,
            }"
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
import { ref, computed, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import { useAuth } from "@/Composables/useAuth";
import { InformationCircleIcon } from "@heroicons/vue/24/outline";

// Importar componentes do Dashboard
import Layout from "@/Layouts/UnifiedLayout.vue";
import KpiCard from "@/Components/GestorReclamacoes/KpiCard.vue";
import ComplaintsList from "@/Components/GestorReclamacoes/ComplaintsList.vue";
import { usePageProps } from "@/Composables/usePageProps";

// Usar o composable de auth para obter o role corretamente
const { role: authenticatedRole, checkRole } = useAuth();

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
  counts: {
    type: [Object, null],
    default: () => ({
      suggestions: 0,
      grievances: 0,
      complaints: 0,
      director_interventions: 0,
      manager_requests: 0,
      total: 0,
    }),
    director_interventions: {
      type: Array,
      default: () => [],
    },
    my_submissions_to_director: {
      type: Array,
      default: () => [],
    },
  },
  director_interventions: {
    type: Array,
    default: () => [],
  },
  my_submissions_to_director: {
    type: Array,
    default: () => [],
  },
  manager_requests: {
    type: Array,
    default: () => [],
  },
});

// Referência ao componente ComplaintsList
const complaintsListRef = ref(null);

const directorInterventionsData = computed(() => {
  // Se temos dados específicos do controller, usamos esses
  if (
    isManager.value &&
    props.director_interventions &&
    props.director_interventions.length > 0
  ) {
    console.log(
      "📦 Manager: Usando director_interventions do controller:",
      props.director_interventions.length
    );
    return props.director_interventions;
  }

  if (
    isDirector.value &&
    props.director_interventions &&
    props.director_interventions.length > 0
  ) {
    console.log(
      "📦 Director: Usando director_interventions do controller:",
      props.director_interventions.length
    );
    return props.director_interventions;
  }

  // Fallback: filtrar localmente
  return formattedAllComplaints.value.filter(
    (item) =>
      item.has_director_intervention === true ||
      item.director_updates?.length > 0 ||
      item.director_validation ||
      item.director_comments_count > 0
  );
});

// **ADICIONAR PARA DIRECTOR:**
const managerRequestsData = computed(() => {
  if (isDirector.value && props.manager_requests && props.manager_requests.length > 0) {
    console.log(
      "📦 Director: Usando manager_requests do controller:",
      props.manager_requests.length
    );
    return props.manager_requests;
  }

  // Fallback: filtrar localmente
  return formattedAllComplaints.value.filter(
    (item) =>
      item.escalated === true ||
      item.is_escalated_to_director === true ||
      (item.metadata && item.metadata.is_escalated_to_director === true)
  );
});

// Usar composable para safe props
const { getSafeProp } = usePageProps(props);
const safeSubmissions = getSafeProp("submissions", []);
const safeAllComplaints = getSafeProp("allComplaints", []);
const safeStats = getSafeProp("stats", {});
const safeManagers = getSafeProp("managers", []);
const safeFilters = getSafeProp("filters", {});
const safeCounts = getSafeProp("counts", {});
const safeDirectorInterventions = getSafeProp("director_interventions", []);
const safeMySubmissionsToDirector = getSafeProp("my_submissions_to_director", []);

// Computed properties para roles
const isDirector = computed(() => checkRole("director"));
const isManager = computed(() => checkRole("manager"));

const mySubmissionsToDirectorData = computed(() => {
  if (isManager.value && safeMySubmissionsToDirector.value.length > 0) {
    console.log(
      "📦 Usando my_submissions_to_director do controller:",
      safeMySubmissionsToDirector.value.length
    );
    return safeMySubmissionsToDirector.value;
  }

  // Fallback: filtrar localmente
  return formattedAllComplaints.value.filter(
    (item) =>
      item.escalated === true ||
      item.is_escalated_to_director === true ||
      (item.metadata && item.metadata.is_escalated_to_director === true)
  );
});

// Estado local
const selectedComplaint = ref(null);
const showDirectorFilters = ref(false);
const directorFilters = ref({
  department: "",
  manager: "",
  specificity: "",
  period: "",
});

// **MODIFICAÇÃO: Formatar TODOS os casos para Director**
const formattedAllComplaints = computed(() => {
  // **IMPORTANTE: Para Director, usar TODOS os casos da base (allComplaints)**
  const sourceData =
    safeAllComplaints.value.length > 0 ? safeAllComplaints.value : safeSubmissions.value;

  console.log("Source data length:", sourceData.length);
  console.log("Is Director:", isDirector.value);

  return sourceData.map((item) => ({
    id: item.id || item.reference_number,
    reference_number: item.reference_number || item.id,
    title:
      item.subject ||
      item.title ||
      (item.description ? item.description.substring(0, 50) + "..." : "Sem título"),
    description: item.description || "",
    priority: item.priority,
    status: item.status,
    type: item.type,
    category: item.category || item.department,
    created_at: item.created_at || item.submitted_at,
    submitted_at: item.submitted_at || item.created_at,
    user: item.user || { name: item.user_name || "Utente" },
    technician: item.technician || item.assigned_to,
    assigned_to: item.assigned_to || item.technician,
    escalated: item.escalated,
    metadata: item.metadata,
    escalated_by: item.escalated_by,
    escalation_reason: item.escalation_reason,
    escalated_at: item.escalated_at,
    assigned_manager_id: item.assigned_manager_id,
    department: item.department,

    // Informações de intervenção do director
    has_director_intervention: item.has_director_intervention,
    director_updates: item.director_updates || [],
    director_comments_count: item.director_comments_count || 0,
    director_validation: item.director_validation,
    director_interventions: item.director_interventions || [],

    // Informações de solicitação do gestor
    is_escalated_to_director: item.is_escalated_to_director,
    manager_request: item.manager_request,

    // Updates gerais
    updates: item.updates || [],
    activities: item.activities || [],

    // Informações básicas para todos os casos
    contact_name: item.contact_name,
    contact_email: item.contact_email,
    contact_phone: item.contact_phone,
    location: item.location,
    province: item.province,
    district: item.district,
    project_id: item.project_id,
    project_name: item.project?.name,

    // Para tracking
    viewed_by_director: item.viewed_by_director,
    director_viewed_at: item.director_viewed_at,

    // Status e datas importantes
    resolved_at: item.resolved_at,
    closed_at: item.closed_at,
    assigned_at: item.assigned_at,
  }));
});

// **MODIFICAÇÃO: Submissões recentes para Director deve incluir TODOS os casos**
const recentSubmissions = computed(() => {
  const sourceData = formattedAllComplaints.value;

  if (sourceData.length === 0) return [];

  // Ordenar por data de criação (mais recente primeiro)
  const sortedData = [...sourceData].sort((a, b) => {
    const dateA = new Date(a.created_at || a.submitted_at || 0);
    const dateB = new Date(b.created_at || b.submitted_at || 0);
    return dateB - dateA;
  });

  // **MODIFICAÇÃO: Para Director, pegar as 4 submissões MAIS RECENTES de TODAS**
  if (isDirector.value) {
    return sortedData.slice(0, 4);
  }

  // Para Manager: apenas submissões atribuídas mais recentes
  return sortedData
    .filter((item) => item.assigned_to || item.technician || item.status !== "pending")
    .slice(0, 4);
});

// **MODIFICAÇÃO: Contadores atualizados para Director ver TODOS os casos**
const counts = computed(() => {
  const data = formattedAllComplaints.value;

  return {
    suggestions: data.filter(
      (item) =>
        item.type?.toLowerCase().includes("suggestion") ||
        item.type?.toLowerCase().includes("sugest")
    ).length,
    grievances: data.filter(
      (item) =>
        item.type?.toLowerCase().includes("grievance") ||
        item.type?.toLowerCase().includes("queixa")
    ).length,
    complaints: data.filter(
      (item) =>
        item.type?.toLowerCase().includes("complaint") ||
        item.type?.toLowerCase().includes("reclam")
    ).length,
    director_interventions: data.filter(
      (item) =>
        item.has_director_intervention === true ||
        item.director_updates?.length > 0 ||
        item.director_validation ||
        item.director_comments_count > 0
    ).length,
    manager_requests: data.filter(
      (item) =>
        item.escalated === true ||
        item.is_escalated_to_director === true ||
        item.manager_request ||
        (item.metadata && item.metadata.is_escalated_to_director === true)
    ).length,
    total: data.length,
  };
});

// Contar casos escalados
const escalatedCasesCount = computed(() => {
  return formattedAllComplaints.value.filter(
    (complaint) =>
      complaint.escalated === true ||
      complaint.is_escalated_to_director === true ||
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
    only: ["submissions", "stats", "filters", "allComplaints", "managers", "counts"],
  });
};

// Inicializar quando o componente for montado
onMounted(() => {
  console.log("=== SUBMISSIONS PAGE MOUNTED ===");
  console.log("Role autenticado:", authenticatedRole.value);
  console.log("É Director:", isDirector.value);
  console.log("É Manager:", isManager.value);
  console.log("Total de casos formatados:", formattedAllComplaints.value?.length);
  console.log("Submissões recentes (4):", recentSubmissions.value?.length);
  console.log("Contadores:", counts.value);
  console.log("Casos escalados:", escalatedCasesCount.value);
  console.log("Casos pendentes:", pendingCount.value);
  console.log("Casos resolvidos:", resolvedCount.value);

  // Log para verificar se Director está recebendo TODOS os casos
  if (isDirector.value) {
    console.log("=== DIRECTOR VIEW ===");
    console.log("Total cases for Director:", formattedAllComplaints.value.length);
    console.log("Sample cases:", formattedAllComplaints.value.slice(0, 3));
  }
});
</script>
