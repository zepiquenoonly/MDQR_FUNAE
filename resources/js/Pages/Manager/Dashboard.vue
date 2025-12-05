<template>
  <Layout :stats="safeStats" :role="'manager'">
    <!-- Renderizar ProjectsManager quando o panel for 'projectos' -->
    <ProjectsManager v-if="activePanel === 'projectos'" :can-edit="canEdit" />

    <ProjectDetail
      v-if="showProjectDetails && project"
      :project="project"
      :can-edit="canEdit"
    />

    <!-- Renderizar TecnicoList quando o panel for 'tecnicos' -->
    <TecnicoList v-else-if="activePanel === 'tecnicos'" />

    <!-- Conteúdo normal do dashboard para outros casos -->
    <div v-else class="space-y-3 sm:space-y-6">
      <!-- KPIs Grid - Melhorado para mobile -->
      <div
        class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4 lg:gap-6"
      >
        <KpiCard
          title="Total em Aberto"
          :value="safeStats.pending_complaints || 0"
          description="Reclamações não resolvidas"
          icon="ExclamationTriangleIcon"
          trend="up"
          color="orange"
        />

        <KpiCard
          title="Em Progresso"
          :value="safeStats.in_progress || 0"
          description="Com técnicos atribuídos"
          icon="ClockIcon"
          trend="stable"
          color="blue"
        />

        <KpiCard
          title="Urgentes (Alta)"
          :value="safeStats.high_priority || 0"
          description="Encaminhar se crítico"
          icon="ExclamationCircleIcon"
          trend="up"
          color="red"
        />

        <KpiCard
          title="Solicitações"
          :value="safeStats.pending_completion_requests || 0"
          description="Aguardando aprovação"
          icon="CheckCircleIcon"
          trend="down"
          color="green"
        />
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 gap-3 sm:gap-6">
        <!-- Recent Complaints - CORRIGIDO: Agora passa todos os tipos de grievances -->
        <div>
          <ComplaintsList
            :complaints="safeAllComplaints"
            :filters="safeFilters"
            :all-complaints="safeAllComplaints"
            @update:filters="updateFilters"
            @select-complaint="selectComplaint"
            @show-details="showDetailsModal"
          />
        </div>
      </div>

      <!-- Complaint Details Modal - Melhorado para mobile -->
      <div
        v-if="showModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-1 sm:p-4 z-50"
      >
        <div
          class="bg-white dark:bg-dark-secondary rounded-lg sm:rounded-xl shadow-lg w-full h-full sm:h-auto sm:max-w-4xl sm:max-h-[90vh] overflow-y-auto"
        >
          <div
            class="sticky top-0 bg-white dark:bg-dark-secondary border-b border-gray-200 dark:border-gray-700 px-3 sm:px-6 py-2 sm:py-4 flex justify-between items-center"
          >
            <h3
              class="text-sm sm:text-lg font-semibold text-gray-800 dark:text-dark-text-primary"
            >
              Detalhes da Reclamação
            </h3>
            <button
              @click="closeModal"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-1"
            >
              <XMarkIcon class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
          </div>
          <div class="p-3 sm:p-6">
            <GrievanceDetails
              :complaint="selectedComplaint"
              :technicians="safeTechnicians"
              :hide-buttons="true"
              @close="showModal = false"
              @update-priority="updatePriority"
              @reassign-technician="reassignTechnician"
              @send-to-director="sendToDirector"
              @mark-complete="markComplete"
            />
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { XMarkIcon } from "@heroicons/vue/24/outline";
import Layout from "@/Layouts/UnifiedLayout.vue";
import KpiCard from "@/Components/GestorReclamacoes/KpiCard.vue";
import ComplaintsList from "@/Components/GestorReclamacoes/ComplaintsList.vue";
import GrievanceDetails from "./GrievanceDetail.vue";
import ProjectsManager from "@/Components/Dashboard/ProjectsManager.vue";
import TecnicoList from "@/Components/GestorReclamacoes/TecnicoList.vue";
import ProjectDetail from "@/Pages/Common/ProjectDetail.vue";

// Usar usePage() para acessar as props de forma reativa
const page = usePage();

// Props do backend com valores padrão seguros - agora reativos via usePage()
const props = defineProps({
  showProjectDetails: Boolean,
  project: {
    type: Object,
    default: null,
  },
  complaints: {
    type: [Object, null],
    default: () => ({ data: [] }),
  },
  allComplaints: {
    type: [Array, null],
    default: () => [],
  },
  stats: {
    type: [Object, null],
    default: () => ({}),
  },
  technicians: {
    type: [Array, null],
    default: () => [],
  },
  filters: {
    type: [Object, null],
    default: () => ({}),
  },
  canEdit: {
    type: Boolean,
    default: false,
  },
});

// Computed properties seguras para evitar null errors - agora reativas
const safeComplaints = computed(
  () => page.props.complaints || props.complaints || { data: [] }
);
const safeAllComplaints = computed(
  () => page.props.allComplaints || props.allComplaints || []
);
const safeStats = computed(() => page.props.stats || props.stats || {});
const safeTechnicians = computed(() => page.props.technicians || props.technicians || []);
const safeFilters = computed(() => page.props.filters || props.filters || {});

const debugDataTypes = () => {
  const typeCount = safeAllComplaints.value.reduce((acc, item) => {
    acc[item.type] = (acc[item.type] || 0) + 1;
    return acc;
  }, {});
};

// Estado local
const selectedComplaint = ref(null);
const localFilters = ref({ ...safeFilters.value });
const showModal = ref(false);
const dataLoaded = ref(false);

// Determinar o panel ativo baseado na URL
const activePanel = computed(() => {
  const urlParams = new URLSearchParams(window.location.search);
  const panelFromUrl = urlParams.get("panel");

  if (panelFromUrl === "projectos") return "projectos";
  if (panelFromUrl === "tecnicos") return "tecnicos";
  return "dashboard";
});

// Inicializar selectedComplaint quando os dados estiverem disponíveis
const initializeSelectedComplaint = () => {
  if (safeAllComplaints.value?.length && !selectedComplaint.value) {
    selectedComplaint.value = safeAllComplaints.value[0];
    dataLoaded.value = true;
  }
};

// Watcher para detectar mudanças no panel
watch(activePanel, (newPanel, oldPanel) => {
  if (newPanel === "dashboard" && oldPanel !== "dashboard") {
    reloadDashboardData();
  }
});

// Watcher para allComplaints data - mais robusto
watch(
  () => safeAllComplaints.value,
  (newData) => {
    if (newData?.length) {
      debugDataTypes();
    }

    if (newData?.length && !selectedComplaint.value) {
      selectedComplaint.value = newData[0];
      dataLoaded.value = true;
    }
  },
  { immediate: true, deep: true }
);

// Watcher para detectar quando as props são atualizadas via Inertia
watch(
  () => page.props.allComplaints,
  (newAllComplaints) => {
    if (newAllComplaints?.length && !selectedComplaint.value) {
      selectedComplaint.value = newAllComplaints[0];
      dataLoaded.value = true;

      // DEBUG
      debugDataTypes();
    }
  },
  { deep: true }
);

// Watcher para filtros
watch(
  localFilters,
  (newFilters) => {
    if (activePanel.value === "dashboard") {
      router.reload({
        data: newFilters,
        preserveState: true,
        replace: true,
        only: ["complaints", "stats", "filters", "allComplaints", "technicians"],
      });
    }
  },
  { deep: true }
);

// Função para recarregar dados do dashboard
const reloadDashboardData = () => {
  dataLoaded.value = false;
  selectedComplaint.value = null;

  router.reload({
    preserveState: true,
    preserveScroll: true,
    only: ["complaints", "stats", "allComplaints", "technicians", "filters"],
    onSuccess: () => {
      nextTick(() => {
        initializeSelectedComplaint();
        dataLoaded.value = true;

        // DEBUG após recarregar
        debugDataTypes();
      });
    },
    onError: (errors) => {
      dataLoaded.value = true;
    },
  });
};

// Handlers
const selectComplaint = (complaint) => {
  selectedComplaint.value = complaint;
};

const showDetailsModal = (complaint) => {
  selectedComplaint.value = complaint;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const updateFilters = (newFilters) => {
  localFilters.value = { ...localFilters.value, ...newFilters };
};

const updatePriority = async ({ complaintId, priority }) => {
  try {
    await router.patch(
      route("complaints.update-priority", complaintId),
      {
        priority,
      },
      {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          reloadDashboardData();
        },
      }
    );
  } catch (error) {}
};

const reassignTechnician = async ({ complaintId, technicianId }) => {
  try {
    await router.patch(
      route("complaints.reassign", complaintId),
      {
        technician_id: technicianId,
      },
      {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          reloadDashboardData();
        },
      }
    );
  } catch (error) {}
};

const sendToDirector = async (complaintId) => {
  try {
    await router.post(
      route("complaints.escalate", complaintId),
      {},
      {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          reloadDashboardData();
        },
      }
    );
  } catch (error) {}
};

const markComplete = async (complaintId) => {
  try {
    await router.patch(
      route("complaints.complete", complaintId),
      {},
      {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          reloadDashboardData();
        },
      }
    );
  } catch (error) {}
};

// Recarregar dados quando o componente for montado
onMounted(() => {
  initializeSelectedComplaint();

  // DEBUG inicial
  debugDataTypes();

  // Se estamos no dashboard, garantir que os dados estão carregados
  if (activePanel.value === "dashboard") {
    if (!safeAllComplaints.value?.length) {
      reloadDashboardData();
    }
  }
});

// Adicionar listener para popstate (navegação pelo browser)
onMounted(() => {
  window.addEventListener("popstate", handlePopState);
});

onUnmounted(() => {
  window.removeEventListener("popstate", handlePopState);
});

const handlePopState = () => {
  // Pequeno delay para garantir que a URL já foi atualizada
  setTimeout(() => {
    if (activePanel.value === "dashboard") {
      reloadDashboardData();
    }
  }, 100);
};
</script>
