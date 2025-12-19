<template>
  <UnifiedLayout :user="user" :role="role">
    <div class="space-y-3 sm:space-y-6">
      <!-- Banner Informativo -->
      <div class="bg-gradient-to-r from-primary-50 to-orange-50 dark:from-primary-900/20 dark:to-orange-900/20 border border-primary-200 dark:border-primary-800 rounded-xl p-4 sm:p-6 mb-6">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/40 rounded-lg flex items-center justify-center">
              <ChartBarIcon class="w-6 h-6 text-primary-600 dark:text-primary-400" />
            </div>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-primary-900 dark:text-primary-100 mb-2">
              Indicadores do Departamento
            </h3>
            <p class="text-primary-700 dark:text-primary-300 text-sm leading-relaxed">
              Análise completa de submissões, funcionários e desempenho organizacional.
              Monitore métricas chave e tome decisões baseadas em dados.
            </p>
            <div class="mt-3 flex items-center gap-4">
              <span class="text-xs bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300 px-2 py-1 rounded">
                {{ formatDate(start_date) }} - {{ formatDate(end_date) }}
              </span>
              <div class="flex gap-2">
                <!-- Filtro de Período -->
                <select
                  v-model="period"
                  @change="changePeriod"
                  class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-secondary text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
                >
                  <option value="today">Hoje</option>
                  <option value="week">Esta Semana</option>
                  <option value="month">Este Mês</option>
                  <option value="3months">Últimos 3 Meses</option>
                  <option value="6months">Últimos 6 Meses</option>
                  <option value="year">Este Ano</option>
                  <option value="12months" selected>Últimos 12 Meses</option>
                </select>

                <!-- Botão Exportar -->
                <button
                  v-if="canExport"
                  @click="showExportModal = true"
                  :disabled="loading"
                  class="inline-flex items-center px-3 py-1 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors font-medium text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <ArrowDownTrayIcon class="w-4 h-4 mr-1" />
                  {{ loading ? "Exportando..." : "Exportar" }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal de Exportação -->
      <div
        v-if="showExportModal"
        class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
      >
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              Exportar Estatísticas
            </h3>
            <button
              @click="showExportModal = false"
              class="text-gray-400 hover:text-gray-500"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>

          <p class="text-gray-600 dark:text-gray-300 mb-6">
            Selecione o formato para exportação:
          </p>

          <div class="space-y-3">
            <button
              @click="handleExport('excel')"
              class="w-full flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              <div class="flex items-center">
                <div
                  class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-3"
                >
                  <span class="text-green-600 dark:text-green-300 font-bold">XLS</span>
                </div>
                <div class="text-left">
                  <p class="font-medium text-gray-900 dark:text-white">Excel (.xlsx)</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    Ideal para análise e edição
                  </p>
                </div>
              </div>
              <ArrowDownTrayIcon class="w-5 h-5 text-gray-400" />
            </button>

            <button
              @click="handleExport('csv')"
              class="w-full flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              <div class="flex items-center">
                <div
                  class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-3"
                >
                  <span class="text-blue-600 dark:text-blue-300 font-bold">CSV</span>
                </div>
                <div class="text-left">
                  <p class="font-medium text-gray-900 dark:text-white">CSV (.csv)</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    Formato simples e compatível
                  </p>
                </div>
              </div>
              <ArrowDownTrayIcon class="w-5 h-5 text-gray-400" />
            </button>

            <button
              @click="handleExport('pdf')"
              class="w-full flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              <div class="flex items-center">
                <div
                  class="w-10 h-10 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center mr-3"
                >
                  <span class="text-red-600 dark:text-red-300 font-bold">PDF</span>
                </div>
                <div class="text-left">
                  <p class="font-medium text-gray-900 dark:text-white">PDF (.pdf)</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    Para impressão e compartilhamento
                  </p>
                </div>
              </div>
              <ArrowDownTrayIcon class="w-5 h-5 text-gray-400" />
            </button>
          </div>

          <div class="mt-6 text-sm text-gray-500 dark:text-gray-400">
            <p>
              Período selecionado:
              <span class="font-medium"
                >{{ formatDate(start_date) }} - {{ formatDate(end_date) }}</span
              >
            </p>
          </div>
        </div>
      </div>

      <!-- KPIs Grid -->
      <div class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4 lg:gap-6">
        <KpiCard
          title="Total de Submissões"
          :value="general_stats.total_submissions?.toLocaleString() || 0"
          description="Todas as reclamações, queixas e sugestões"
          icon="DocumentTextIcon"
          :trend="general_stats.growth_rate >= 0 ? 'up' : 'down'"
        />

        <KpiCard
          title="Taxa de Resolução"
          :value="general_stats.resolution_rate || 0 + '%'"
          description="Submissões finalizadas com sucesso"
          icon="CheckCircleIcon"
          trend="up"
        />

        <KpiCard
          title="Tempo Médio"
          :value="general_stats.avg_resolution_time || 0 + 'h'"
          description="Até resolução completa"
          icon="ClockIcon"
          trend="stable"
        />

        <KpiCard
          title="Funcionários Ativos"
          :value="employee_stats.online_employees || 0"
          description="Gestores e técnicos disponíveis"
          icon="UsersIcon"
          trend="up"
        />
      </div>

      <!-- Resumo Executivo -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Principais Métricas -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-4">
            Principais Métricas
          </h3>
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">Submissões Hoje</span>
              <span class="font-semibold text-gray-900 dark:text-white">{{ general_stats.submissions_today || 0 }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">Pendentes Críticas</span>
              <span class="font-semibold text-red-600 dark:text-red-400">{{ general_stats.critical_pending || 0 }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">Satisfação Média</span>
              <span class="font-semibold text-green-600 dark:text-green-400">{{ general_stats.avg_satisfaction || 0 }}%</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">Disponibilidade Equipe</span>
              <span class="font-semibold text-blue-600 dark:text-blue-400">{{ getOverallAvailability() }}%</span>
            </div>
          </div>
        </div>

        <!-- Distribuição por Status -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-4">
            Distribuição por Status
          </h3>
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">Resolvidas</span>
              <div class="flex items-center">
                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                <span class="font-semibold text-gray-900 dark:text-white">{{ general_stats.total_resolved || 0 }}</span>
              </div>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">Em Andamento</span>
              <div class="flex items-center">
                <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                <span class="font-semibold text-gray-900 dark:text-white">{{ general_stats.in_progress || 0 }}</span>
              </div>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">Pendentes</span>
              <div class="flex items-center">
                <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                <span class="font-semibold text-gray-900 dark:text-white">{{ general_stats.pending_submissions || 0 }}</span>
              </div>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">Escaladas</span>
              <div class="flex items-center">
                <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                <span class="font-semibold text-gray-900 dark:text-white">{{ general_stats.escalated || 0 }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>




    </div>
  </UnifiedLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { Link, router } from "@inertiajs/vue3";
import UnifiedLayout from "@/Layouts/UnifiedLayout.vue";
import KpiCard from "@/Components/GestorReclamacoes/KpiCard.vue";
import {
  ArrowDownTrayIcon,
  ChartBarIcon,
  DocumentTextIcon,
  CheckCircleIcon,
  ClockIcon,
  UsersIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  user: Object,
  role: String,
  period: String,
  start_date: String,
  end_date: String,
  general_stats: Object,
  employee_stats: Object,
  canExport: Boolean,
});

const period = ref(props.period);
const loading = ref(false);
const localData = ref({ ...props });
const showExportModal = ref(false);

// Computed para verificar modo escuro
const isDarkMode = computed(() => {
  return document.documentElement.classList.contains("dark");
});

// Computed properties para acessar os dados
const general_stats = computed(() => localData.value.general_stats || {});
const employee_stats = computed(
  () =>
    localData.value.employee_stats || {
      by_role: { Gestor: 0, Técnico: 0 },
      total_employees: 0,
      managers: { total: 0, active: 0, inactive: 0, availability_rate: 0 },
      technicians: { total: 0, active: 0, inactive: 0, availability_rate: 0 },
      avg_tasks_per_technician: 0,
      new_employees: 0,
      new_managers: 0,
      online_employees: 0,
      employee_growth: [],
    }
);
const start_date = computed(() => localData.value.start_date);
const end_date = computed(() => localData.value.end_date);

// Função para carregar estatísticas via AJAX
const loadStatistics = async () => {
  loading.value = true;

  try {
    router.get(
      "/director/estatisticas",
      { period: period.value },
      {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
          localData.value = {
            ...localData.value,
            ...page.props,
          };
          loading.value = false;
        },
        onError: (errors) => {
          console.error("Erro ao carregar estatísticas:", errors);
          loading.value = false;
        },
      }
    );
  } catch (error) {
    console.error("Erro:", error);
    loading.value = false;
  }
};

// Função para alterar período
const changePeriod = () => {
  loadStatistics();
};

const handleExport = async (format) => {
  showExportModal.value = false;
  loading.value = true;

  try {
    // Construir URL para download
    const params = new URLSearchParams({
      period: period.value,
      format: format,
    });

    const url = `/director/estatisticas/export?${params.toString()}`;

    // Método 1: Download direto (recomendado para arquivos)
    window.location.href = url;

    // Mensagem de sucesso após um delay
    setTimeout(() => {
      showSuccessToast(`Estatísticas exportadas em formato ${format.toUpperCase()}!`);
      loading.value = false;
    }, 1000);
  } catch (error) {
    console.error("Erro na exportação:", error);
    loading.value = false;
    showErrorToast("Erro ao exportar estatísticas: " + error.message);
  }
};

// Funções de toast (simplificadas)
const showSuccessToast = (message) => {
  // Você pode integrar com uma biblioteca de notificações
  console.log("✅", message);
  // Exemplo com alerta simples
  alert(message);
};

const showErrorToast = (message) => {
  console.error("❌", message);
  alert("Erro: " + message);
};

// Observar mudanças no modo escuro para atualizar gráficos
watch(isDarkMode, () => {
  loadStatistics();
});

// Inicializar quando o componente é montado
onMounted(() => {
  if (!props.chart_data || Object.keys(props.chart_data).length === 0) {
    loadStatistics();
  }
});

// Helper functions
const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  return new Date(dateString).toLocaleDateString("pt-PT");
};

const getOverallAvailability = () => {
  const managers = employee_stats.value.managers || {};
  const technicians = employee_stats.value.technicians || {};
  const totalActive = (managers.active || 0) + (technicians.active || 0);
  const totalEmployees = (managers.total || 0) + (technicians.total || 0);
  return totalEmployees > 0 ? Math.round((totalActive / totalEmployees) * 100) : 0;
};
</script>
