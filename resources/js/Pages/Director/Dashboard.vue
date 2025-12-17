<template>
  <AppLayout title="Dashboard">
    <!-- Estado de carregamento -->
    <div v-if="isLoading" class="flex items-center justify-center h-64">
      <div class="text-center">
        <div
          class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"
        ></div>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Carregando dados...</p>
      </div>
    </div>

    <!-- Erro -->
    <div
      v-else-if="error"
      class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6"
    >
      <p class="text-red-700 dark:text-red-400">{{ error }}</p>
    </div>

    <!-- Conteúdo principal -->
    <div v-else class="max-w-full mx-auto">
      <!-- Welcome Message - Glassmorphism Hero -->
      <div class="relative overflow-hidden rounded-3xl mb-8">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-600 via-orange-600 to-amber-700"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent"></div>
        <!-- Floating Elements -->
        <div class="absolute top-6 left-10 w-20 h-20 bg-white/10 rounded-full blur-xl animate-pulse"></div>
        <div class="absolute bottom-6 right-10 w-32 h-32 bg-purple-300/20 rounded-full blur-2xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-indigo-200/15 rounded-full blur-lg animate-pulse delay-500"></div>

        <div class="relative p-8 sm:p-10">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 bg-white/10 backdrop-blur-xl rounded-2xl flex items-center justify-center border border-white/20 shadow-2xl shadow-black/10">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-2 text-white drop-shadow-2xl">
                Bem-vindo(a), <span class="bg-gradient-to-r from-purple-200 to-indigo-200 bg-clip-text text-transparent">{{ $page.props.auth?.user?.name }}</span>!
              </h1>
              <div class="w-24 h-1 bg-gradient-to-r from-purple-300 to-indigo-300 rounded-full mb-4"></div>
            </div>
          </div>
          <p class="text-white/90 text-base sm:text-lg lg:text-xl leading-relaxed max-w-4xl drop-shadow-lg">
            Painel de Diretor - Visão geral das reclamações, queixas e sugestões do departamento
          </p>
        </div>
      </div>

      <!-- Métricas Principais -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Cartão de Métricas -->
        <div class="glass p-6 rounded-xl">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              Reclamações Pendentes
            </h3>
            <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
              <ClockIcon class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
            </div>
          </div>
          <p class="text-3xl font-bold text-gray-900 dark:text-white">
            {{ metrics.pending_complaints || 0 }}
          </p>
          <div class="mt-4">
            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
              <ArrowTrendingUpIcon
                v-if="metrics.pending_change >= 0"
                class="h-4 w-4 mr-1"
              />
              <ArrowTrendingDownIcon v-else class="h-4 w-4 mr-1" />
              <span
                :class="metrics.pending_change >= 0 ? 'text-green-600' : 'text-red-600'"
              >
                {{ metrics.pending_change >= 0 ? "+" : ""
                }}{{ metrics.pending_change || 0 }}% desde ontem
              </span>
            </div>
          </div>
        </div>

        <div class="glass p-6 rounded-xl">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              Reclamações Críticas
            </h3>
            <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
              <ExclamationTriangleIcon class="h-6 w-6 text-red-600 dark:text-red-400" />
            </div>
          </div>
          <p class="text-3xl font-bold text-gray-900 dark:text-white">
            {{ metrics.critical_complaints || 0 }}
          </p>
          <div class="mt-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">
              {{ metrics.critical_unvalidated || 0 }} precisam de validação
            </div>
          </div>
        </div>

        <div class="glass p-6 rounded-xl">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              Taxa de Resolução
            </h3>
            <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
              <CheckCircleIcon class="h-6 w-6 text-green-600 dark:text-green-400" />
            </div>
          </div>
          <p class="text-3xl font-bold text-gray-900 dark:text-white">
            {{ metrics.resolution_rate || 0 }}%
          </p>
          <div class="mt-4">
            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
              <span>Tempo médio: {{ metrics.average_resolution_time || 0 }} dias</span>
            </div>
          </div>
        </div>

        <div class="glass p-6 rounded-xl">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              Satisfação
            </h3>
            <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
              <FaceSmileIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
          <p class="text-3xl font-bold text-gray-900 dark:text-white">
            {{ metrics.satisfaction_rate || 0 }}%
          </p>
          <div class="mt-4">
            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
              <ArrowTrendingUpIcon
                v-if="metrics.satisfaction_change >= 0"
                class="h-4 w-4 mr-1"
              />
              <ArrowTrendingDownIcon v-else class="h-4 w-4 mr-1" />
              <span
                :class="
                  metrics.satisfaction_change >= 0 ? 'text-green-600' : 'text-red-600'
                "
              >
                {{ metrics.satisfaction_change >= 0 ? "+" : ""
                }}{{ metrics.satisfaction_change || 0 }} pontos
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Gráficos -->
        <div class="lg:col-span-2 glass p-6 rounded-xl">
          <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
              Distribuição por Status e Tipo
            </h3>
            <div class="flex space-x-2">
              <button
                @click="activeChart = 'status'"
                :class="[
                  'px-3 py-1 text-sm rounded-lg transition-colors',
                  activeChart === 'status'
                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
                    : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700',
                ]"
              >
                Por Status
              </button>
              <button
                @click="activeChart = 'type'"
                :class="[
                  'px-3 py-1 text-sm rounded-lg transition-colors',
                  activeChart === 'type'
                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
                    : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700',
                ]"
              >
                Por Tipo
              </button>
              <button
                @click="activeChart = 'priority'"
                :class="[
                  'px-3 py-1 text-sm rounded-lg transition-colors',
                  activeChart === 'priority'
                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
                    : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700',
                ]"
              >
                Por Prioridade
              </button>
              <button
                @click="activeChart = 'trends'"
                :class="[
                  'px-3 py-1 text-sm rounded-lg transition-colors',
                  activeChart === 'trends'
                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
                    : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700',
                ]"
              >
                Tendências
              </button>
            </div>
          </div>

          <div class="h-[400px] lg:h-[450px]">
            <!-- Gráfico de Status -->
            <DistributionChart
              v-if="
                activeChart === 'status' &&
                chartData.status_distribution &&
                Object.keys(chartData.status_distribution).length > 0
              "
              :chart-type="'doughnut'"
              :chart-data="chartData.status_distribution"
              :title="'Distribuição por Status'"
              :height="400"
              :legend-position="'right'"
            />

            <!-- Gráfico de Tipo -->
            <DistributionChart
              v-else-if="
                activeChart === 'type' &&
                chartData.type_distribution &&
                Object.keys(chartData.type_distribution).length > 0
              "
              :chart-type="'pie'"
              :chart-data="chartData.type_distribution"
              :title="'Distribuição por Tipo'"
              :height="400"
              :legend-position="'right'"
            />

            <!-- Gráfico de Prioridade -->
            <DistributionChart
              v-else-if="
                activeChart === 'priority' &&
                chartData.priority_distribution &&
                Object.keys(chartData.priority_distribution).length > 0
              "
              :chart-type="'doughnut'"
              :chart-data="chartData.priority_distribution"
              :title="'Distribuição por Prioridade'"
              :height="400"
              :legend-position="'right'"
            />

            <!-- Gráfico de Tendências -->
            <DistributionChart
              v-else-if="
                activeChart === 'trends' &&
                chartData.monthly_trends &&
                Array.isArray(chartData.monthly_trends) &&
                chartData.monthly_trends.length > 0
              "
              :chart-type="'line'"
              :chart-data="chartData.monthly_trends"
              :title="'Tendências Mensais (Últimos 6 Meses)'"
              :height="400"
            />

            <!-- Estado de carregamento -->
            <div
              v-else
              class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400"
            >
              <div class="text-center">
                <div
                  class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-2"
                ></div>
                <p>Carregando dados do gráfico...</p>
              </div>
            </div>
          </div>

          <!-- Estatísticas rápidas -->
          <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ chartData.total_complaints || 0 }}
              </p>
            </div>

            <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
              <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Ativas</p>
              <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                {{ activeComplaints || 0 }}
              </p>
            </div>

            <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
              <p class="text-sm font-medium text-green-600 dark:text-green-400">
                Resolvidas
              </p>
              <p class="text-2xl font-bold text-green-700 dark:text-green-300">
                {{ resolvedComplaints || 0 }}
              </p>
            </div>

            <div class="text-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
              <p class="text-sm font-medium text-red-600 dark:text-red-400">Pendentes</p>
              <p class="text-2xl font-bold text-red-700 dark:text-red-300">
                {{ pendingComplaints || 0 }}
              </p>
            </div>
          </div>
        </div>

        <!-- Últimas Ações -->
        <div class="glass p-6 rounded-xl">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              Últimas Ações
            </h3>
            <Link
              :href="complaintsOverviewUrl"
              class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
            >
              Ver todas
            </Link>
          </div>
          <div class="space-y-4">
            <div
              v-for="activity in recentActivities"
              :key="activity.id"
              class="flex items-start gap-3"
            >
              <div class="flex-shrink-0 mt-1">
                <div :class="getActivityIconClass(activity.type)" class="p-2 rounded-lg">
                  <component :is="getActivityIcon(activity.type)" class="h-4 w-4" />
                </div>
              </div>
              <div>
                <p class="text-sm text-gray-900 dark:text-white">
                  {{ activity.description }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ formatTimeAgo(activity.created_at) }}
                </p>
              </div>
            </div>

            <div v-if="recentActivities.length === 0" class="text-center py-4">
              <p class="text-gray-500 dark:text-gray-400 text-sm">
                Nenhuma atividade recente
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Indicadores de Desempenho -->
      <div class="glass p-6 rounded-xl mb-8">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Indicadores de Desempenho
          </h3>
          <select
            v-model="performancePeriod"
            @change="updatePeriod"
            class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-secondary focus:ring-2 focus:ring-brand focus:border-transparent"
          >
            <option value="week">Última Semana</option>
            <option value="month">Último Mês</option>
            <option value="quarter">Último Trimestre</option>
            <option value="year">Último Ano</option>
          </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="text-center">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
              Tempo Médio de Resposta
            </p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">
              {{ performance.average_response_time || 0 }}h
            </p>
            <div class="mt-2">
              <span
                :class="
                  performance.response_time_trend >= 0 ? 'text-red-600' : 'text-green-600'
                "
                class="text-xs"
              >
                {{ performance.response_time_trend >= 0 ? "+" : ""
                }}{{ performance.response_time_trend || 0 }}%
              </span>
            </div>
          </div>

          <div class="text-center">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
              Taxa de Reincidência
            </p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">
              {{ performance.recurrence_rate || 0 }}%
            </p>
            <div class="mt-2">
              <span
                :class="
                  performance.recurrence_trend >= 0 ? 'text-red-600' : 'text-green-600'
                "
                class="text-xs"
              >
                {{ performance.recurrence_trend >= 0 ? "+" : ""
                }}{{ performance.recurrence_trend || 0 }}%
              </span>
            </div>
          </div>

          <div class="text-center">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
              Casos por Funcionário
            </p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">
              {{ performance.cases_per_employee || 0 }}
            </p>
            <div class="mt-2">
              <span
                :class="performance.cases_trend >= 0 ? 'text-red-600' : 'text-green-600'"
                class="text-xs"
              >
                {{ performance.cases_trend >= 0 ? "+" : ""
                }}{{ performance.cases_trend || 0 }}%
              </span>
            </div>
          </div>

          <div class="text-center">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
              Conformidade
            </p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">
              {{ performance.compliance_rate || 0 }}%
            </p>
            <div class="mt-2">
              <span
                :class="
                  performance.compliance_trend >= 0 ? 'text-green-600' : 'text-red-600'
                "
                class="text-xs"
              >
                {{ performance.compliance_trend >= 0 ? "+" : ""
                }}{{ performance.compliance_trend || 0 }}%
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Relatórios e Ações Rápidas -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Relatórios Estatísticos -->
        <div class="lg:col-span-2 glass p-6 rounded-xl">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Relatórios Estatísticos
          </h3>
          <div class="space-y-4">
            <div
              v-for="report in reports"
              :key="report.id"
              class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg"
            >
              <div class="flex items-center gap-3">
                <DocumentChartBarIcon class="h-6 w-6 text-gray-400" />
                <div>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ report.name }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    Gerado em {{ formatDate(report.generated_at) }}
                  </p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <button
                  @click="downloadReport(report)"
                  class="px-3 py-1 text-sm text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg"
                  title="Baixar relatório"
                >
                  <ArrowDownTrayIcon class="h-4 w-4" />
                </button>
                <button
                  @click="viewReport(report)"
                  class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-900/20 rounded-lg"
                  title="Visualizar relatório"
                >
                  <EyeIcon class="h-4 w-4" />
                </button>
              </div>
            </div>

            <div
              v-if="reports.length === 0"
              class="text-center py-4 border border-gray-200 dark:border-gray-700 rounded-lg"
            >
              <DocumentChartBarIcon class="h-8 w-8 mx-auto mb-2 text-gray-400" />
              <p class="text-gray-500 dark:text-gray-400 text-sm">
                Nenhum relatório gerado recentemente
              </p>
            </div>
          </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="glass p-6 rounded-xl">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Ações Rápidas
          </h3>
          <div class="space-y-3">
            <Link
              :href="complaintsOverviewUrl"
              class="flex items-center gap-3 p-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900/20 rounded-lg transition-colors"
            >
              <DocumentTextIcon class="h-5 w-5" />
              <span>Ver Todas Submissões</span>
            </Link>

            <Link
              :href="`${complaintsOverviewUrl}?priority=high`"
              class="flex items-center gap-3 p-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900/20 rounded-lg transition-colors"
            >
              <ExclamationTriangleIcon class="h-5 w-5" />
              <span>Submissões Críticas</span>
            </Link>

            <Link
              :href="indicatorsUrl"
              class="w-full flex items-center gap-3 p-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900/20 rounded-lg transition-colors"
            >
              <DocumentArrowDownIcon class="h-5 w-5" />
              <span>Gerar Relatório Mensal</span>
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { usePage, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/UnifiedLayout.vue";
import DistributionChart from "@/Components/Indicators/Charts/DistributionChart.vue";
import {
  ClockIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  FaceSmileIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  DocumentChartBarIcon,
  ArrowDownTrayIcon,
  EyeIcon,
  DocumentTextIcon,
  DocumentArrowDownIcon,
  UsersIcon,
  CheckBadgeIcon,
  ChartPieIcon,
  ChatBubbleLeftRightIcon,
  UserPlusIcon,
  TagIcon,
  ExclamationCircleIcon,
  ChartBarIcon,
} from "@heroicons/vue/24/outline";

const page = usePage();

// URLs fixas - use as URLs diretas ou obtenha do page.props
const complaintsOverviewUrl = "/director/complaints-overview";
const indicatorsUrl = "/director/indicators";
const managersUrl = "/director/managers";

// Acessar dados do Inertia
const metrics = computed(() => page.props.metrics || {});
const performance = computed(() => page.props.performance || {});
const recentActivities = computed(() => page.props.recentActivities || []);
const reports = computed(() => page.props.reports || []);
const stats = computed(() => page.props.stats || {});
const period = computed(() => page.props.period || "month");

const chartData = computed(
  () =>
    page.props.chartData || {
      status_distribution: {},
      type_distribution: {},
      priority_distribution: {},
      monthly_trends: [],
      total_complaints: 0,
    }
);

// Estado local
const performancePeriod = ref(period.value);
const isLoading = ref(false);
const error = ref(null);

// Função para atualizar período
const updatePeriod = () => {
  isLoading.value = true;

  // Use a URL direta em vez de route()
  router.get(
    "/director/dashboard",
    {
      period: performancePeriod.value,
    },
    {
      preserveState: true,
      preserveScroll: true,
      onFinish: () => {
        isLoading.value = false;
      },
      onError: (errors) => {
        error.value = "Erro ao atualizar período";
        console.error(errors);
      },
    }
  );
};

// Verificar se o usuário pode validar
const canValidate = computed(() => {
  return (
    page.props.auth?.user?.can_validate ||
    page.props.auth?.user?.roles?.includes("Director") ||
    page.props.auth?.user?.hasRole?.("Director")
  );
});

// Helper functions
const getActivityIcon = (type) => {
  const icons = {
    created: DocumentTextIcon,
    comment: ChatBubbleLeftRightIcon,
    status_change: ArrowTrendingUpIcon,
    assignment: UserPlusIcon,
    priority_change: ExclamationCircleIcon,
    category_change: TagIcon,
    validation: CheckBadgeIcon,
  };
  return icons[type] || DocumentTextIcon;
};

const getActivityIconClass = (type) => {
  const classes = {
    created: "bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400",
    comment: "bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400",
    status_change:
      "bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400",
    assignment:
      "bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400",
    priority_change: "bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400",
    category_change:
      "bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400",
    validation:
      "bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400",
  };
  return (
    classes[type] || "bg-gray-100 text-gray-600 dark:bg-gray-900/30 dark:text-gray-400"
  );
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  try {
    return new Date(dateString).toLocaleDateString("pt-PT", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch {
    return dateString;
  }
};

const formatTimeAgo = (dateString) => {
  if (!dateString) return "";
  try {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 60) {
      return `há ${diffMins} min${diffMins !== 1 ? "s" : ""}`;
    } else if (diffHours < 24) {
      return `há ${diffHours} h${diffHours !== 1 ? "s" : ""}`;
    } else if (diffDays < 7) {
      return `há ${diffDays} dia${diffDays !== 1 ? "s" : ""}`;
    } else {
      return formatDate(dateString);
    }
  } catch {
    return dateString;
  }
};

const activeChart = ref("status");

const activeComplaints = computed(() => {
  if (!chartData.value.status_distribution) return 0;
  const activeStatuses = [
    "Submetidas",
    "Em Análise",
    "Atribuídas",
    "Em Andamento",
    "Pendentes Aprovação",
  ];
  return Object.entries(chartData.value.status_distribution)
    .filter(([status]) => activeStatuses.includes(status))
    .reduce((sum, [, count]) => sum + count, 0);
});

const resolvedComplaints = computed(() => {
  return chartData.value.status_distribution?.["Resolvidas"] || 0;
});

const pendingComplaints = computed(() => {
  return chartData.value.status_distribution?.["Pendentes Aprovação"] || 0;
});

const downloadReport = (report) => {
  if (report.download_url) {
    window.open(report.download_url, "_blank");
  }
};

const viewReport = (report) => {
  if (report.view_url) {
    window.open(report.view_url, "_blank");
  }
};
</script>

<style scoped>
.glass {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.dark .glass {
  background: rgba(17, 24, 39, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.1);
}
</style>
