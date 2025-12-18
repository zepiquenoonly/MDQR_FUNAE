<template>
  <UnifiedLayout :stats="stats" @change-view="handleViewChange">
    <div class="min-h-screen bg-gray-50 dark:bg-dark-primary p-4 sm:p-6">
      <!-- Breadcrumb -->
      <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm">
          <li>
            <router-link
              to="/manager/projects"
              class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
            >
              Projectos
            </router-link>
          </li>
          <li class="text-gray-400 dark:text-gray-500">/</li>
          <li class="text-gray-700 dark:text-gray-300 font-semibold">
            {{ project?.name || "Detalhes" }}
          </li>
        </ol>
      </nav>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div
          class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand mx-auto"
        ></div>
        <p class="text-gray-600 dark:text-gray-400 mt-4">
          A carregar detalhes do projecto...
        </p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="text-center py-12">
        <div
          class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/20 mb-4"
        >
          <ExclamationTriangleIcon class="w-8 h-8 text-red-600 dark:text-red-400" />
        </div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-2">
          Erro ao carregar projecto
        </h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6">{{ error }}</p>
        <router-link
          to="/manager/projects"
          class="inline-flex items-center px-4 py-2 bg-brand hover:bg-orange-600 text-white rounded-lg transition-colors"
        >
          <ArrowLeftIcon class="w-4 h-4 mr-2" />
          Voltar aos Projectos
        </router-link>
      </div>

      <!-- Project Details -->
      <div v-else-if="project" class="space-y-6">
        <!-- Header with Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h1
              class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-dark-text-primary"
            >
              {{ project.name }}
            </h1>
            <div class="flex items-center gap-3 mt-2">
              <span
                :class="[
                  'px-3 py-1 text-sm font-semibold rounded-full',
                  getStatusClass(project.category),
                ]"
              >
                {{ getStatusLabel(project.category) }}
              </span>
              <span class="text-gray-500 dark:text-gray-400 text-sm">
                Criado em {{ formatDate(project.data_criacao) }}
              </span>
            </div>
          </div>

          <div class="flex gap-2">
            <!-- Botão Editar - apenas para Admin e Super Admin -->
            <router-link
              v-if="canEdit"
              :to="`/manager/projects/${project.id}/edit`"
              class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors"
            >
              <PencilIcon class="w-4 h-4 mr-2" />
              Editar
            </router-link>

            <!-- Botão Eliminar - apenas para Admin e Super Admin -->
            <button
              v-if="canDelete"
              @click="deleteProject"
              class="inline-flex items-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors"
            >
              <TrashIcon class="w-4 h-4 mr-2" />
              Eliminar
            </button>

            <router-link
              to="/manager/projects"
              class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-300 rounded-lg transition-colors"
            >
              <ArrowLeftIcon class="w-4 h-4 mr-2" />
              Voltar
            </router-link>
          </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Left Column -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Project Image -->
            <div
              class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm overflow-hidden"
            >
              <img
                :src="project.image_url || '/images/default-project.png'"
                :alt="project.name"
                class="w-full h-64 sm:h-80 object-cover"
              />
            </div>

            <!-- Description -->
            <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
              <h2
                class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
              >
                Descrição
              </h2>
              <div class="prose prose-gray dark:prose-invert max-w-none">
                <p class="text-gray-600 dark:text-gray-400 whitespace-pre-line">
                  {{ project.description || "Nenhuma descrição fornecida." }}
                </p>
              </div>
            </div>

            <!-- Objectives -->
            <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
              <div class="flex items-center justify-between mb-6">
                <h2
                  class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary"
                >
                  Objectivos
                </h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                  {{ project.objectives?.length || 0 }} objectivo(s)
                </span>
              </div>

              <div
                v-if="project.objectives && project.objectives.length > 0"
                class="space-y-4"
              >
                <div
                  v-for="(objective, index) in project.objectives"
                  :key="objective.id"
                  class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                >
                  <div class="flex items-start gap-3">
                    <div
                      class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center"
                    >
                      <span
                        class="text-blue-600 dark:text-blue-400 font-semibold text-sm"
                      >
                        {{ index + 1 }}
                      </span>
                    </div>
                    <div class="flex-1">
                      <h3
                        class="font-semibold text-gray-800 dark:text-dark-text-primary mb-1"
                      >
                        {{ objective.title }}
                      </h3>
                      <p class="text-gray-600 dark:text-gray-400 text-sm">
                        {{ objective.description }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="text-center py-8">
                <div
                  class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 mb-3"
                >
                  <ClipboardDocumentListIcon
                    class="w-6 h-6 text-gray-400 dark:text-gray-500"
                  />
                </div>
                <p class="text-gray-500 dark:text-gray-400">Nenhum objectivo definido</p>
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div class="space-y-6">
            <!-- Location Card -->
            <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
              <h2
                class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
              >
                Localização
              </h2>
              <div class="space-y-3">
                <div class="flex items-center gap-2">
                  <MapPinIcon class="w-5 h-5 text-gray-400 dark:text-gray-500" />
                  <span class="text-gray-600 dark:text-gray-400">
                    {{ project.bairro || "N/A" }}
                  </span>
                </div>
                <div class="flex items-center gap-2">
                  <BuildingOfficeIcon class="w-5 h-5 text-gray-400 dark:text-gray-500" />
                  <span class="text-gray-600 dark:text-gray-400">
                    {{ project.distrito || "N/A" }}
                  </span>
                </div>
                <div class="flex items-center gap-2">
                  <GlobeAltIcon class="w-5 h-5 text-gray-400 dark:text-gray-500" />
                  <span class="text-gray-600 dark:text-gray-400">
                    {{ project.provincia || "N/A" }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Finance Card -->
            <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
              <h2
                class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
              >
                Financiamento
              </h2>
              <div class="space-y-4">
                <div v-if="project.finance">
                  <div class="space-y-3">
                    <div>
                      <label class="block text-sm text-gray-500 dark:text-gray-400 mb-1">
                        Financiador
                      </label>
                      <p class="text-gray-800 dark:text-dark-text-primary font-medium">
                        {{ project.finance.financiador || "N/A" }}
                      </p>
                    </div>
                    <div>
                      <label class="block text-sm text-gray-500 dark:text-gray-400 mb-1">
                        Beneficiário
                      </label>
                      <p class="text-gray-800 dark:text-dark-text-primary font-medium">
                        {{ project.finance.beneficiario || "N/A" }}
                      </p>
                    </div>
                    <div>
                      <label class="block text-sm text-gray-500 dark:text-gray-400 mb-1">
                        Valor Financiado
                      </label>
                      <p class="text-gray-800 dark:text-dark-text-primary font-medium">
                        {{ project.finance.valor_financiado || "N/A" }}
                      </p>
                    </div>
                    <div>
                      <label class="block text-sm text-gray-500 dark:text-gray-400 mb-1">
                        Responsável
                      </label>
                      <p class="text-gray-800 dark:text-dark-text-primary font-medium">
                        {{ project.finance.responsavel || "N/A" }}
                      </p>
                    </div>
                    <div>
                      <label class="block text-sm text-gray-500 dark:text-gray-400 mb-1">
                        Código
                      </label>
                      <p class="text-gray-800 dark:text-dark-text-primary font-medium">
                        {{ project.finance.codigo || "N/A" }}
                      </p>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-4">
                  <p class="text-gray-500 dark:text-gray-400">
                    Informação de financiamento não disponível
                  </p>
                </div>
              </div>
            </div>

            <!-- Deadlines Card -->
            <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
              <h2
                class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
              >
                Prazos
              </h2>
              <div class="space-y-4">
                <div v-if="project.deadline">
                  <div class="space-y-3">
                    <div v-for="dateField in dateFields" :key="dateField.key">
                      <label class="block text-sm text-gray-500 dark:text-gray-400 mb-1">
                        {{ dateField.label }}
                      </label>
                      <p class="text-gray-800 dark:text-dark-text-primary font-medium">
                        {{ formatDate(project.deadline[dateField.key]) }}
                      </p>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-4">
                  <p class="text-gray-500 dark:text-gray-400">
                    Informação de prazos não disponível
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </UnifiedLayout>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { router } from "@inertiajs/vue3";
import UnifiedLayout from "@/Layouts/UnifiedLayout.vue";
import { useAuth, usePermissions } from "@/Composables/useAuth";
import {
  PencilIcon,
  TrashIcon,
  ArrowLeftIcon,
  MapPinIcon,
  BuildingOfficeIcon,
  GlobeAltIcon,
  ClipboardDocumentListIcon,
  ExclamationTriangleIcon,
} from "@heroicons/vue/24/outline";

// Usar useAuth para permissões
const { role, user } = useAuth();
const { permissions } = usePermissions();

const props = defineProps({
  stats: Object,
  project: Object,
});

// Verificar permissões baseadas no role
const canEdit = computed(() => {
  return role.value === "admin" || role.value === "super_admin";
});

const canDelete = computed(() => {
  return role.value === "admin" || role.value === "super_admin";
});

// Estado local permanece igual
const loading = ref(false);
const error = ref(null);

// Campos de data
const dateFields = [
  { key: "data_aprovacao", label: "Data de Aprovação" },
  { key: "data_inicio", label: "Data de Início" },
  { key: "data_inspecao", label: "Data de Inspecção" },
  { key: "data_finalizacao", label: "Data de Finalização" },
  { key: "data_inauguracao", label: "Data de Inauguração" },
];

// Handlers
const handleViewChange = (view) => {
  console.log("Mudando para view:", view);
  emit("change-view", view);
};

// Eliminar projeto
const deleteProject = () => {
  if (!props.project?.id || !confirm("Tem certeza que deseja eliminar este projecto?")) {
    return;
  }

  router.delete(`/manager/projects/${props.project.id}`, {
    preserveState: false,
    preserveScroll: false,
    onSuccess: () => {
      router.visit("/manager/projects");
    },
    onError: (errors) => {
      console.error("Erro ao eliminar projecto:", errors);
      alert("Erro ao eliminar projecto. Tente novamente.");
    },
  });
};

// Função para formatar data
const formatDate = (dateString) => {
  if (!dateString) return "N/A";

  try {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;

    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();

    return `${day}/${month}/${year}`;
  } catch (error) {
    return dateString;
  }
};

// Funções auxiliares para status
const getStatusClass = (category) => {
  if (!category) return "bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300";

  const classes = {
    finalizados: "bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300",
    andamento: "bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300",
    parados: "bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300",
  };

  return (
    classes[category] || "bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300"
  );
};

const getStatusLabel = (category) => {
  if (!category) return "N/A";

  const labels = {
    finalizados: "Finalizado",
    andamento: "Em Andamento",
    parados: "Parado",
  };

  return labels[category] || category;
};
</script>

<style scoped>
/* Estilos específicos para a página */
.prose {
  color: inherit;
}

.prose p {
  margin-top: 0;
  margin-bottom: 1em;
}

.prose-invert {
  --tw-prose-body: theme("colors.gray.300");
}
</style>
