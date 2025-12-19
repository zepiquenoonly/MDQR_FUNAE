<template>
  <UnifiedLayout :user="user" :role="role">
    <div class="min-h-screen dark:bg-dark-primary p-4 sm:p-6">
      <!-- Breadcrumb -->
      <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm">
          <li>
            <Link
              href="/gestor/dashboard"
              class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
            >
              Dashboard
            </Link>
          </li>
          <li class="text-gray-400 dark:text-gray-500">/</li>
          <li class="text-gray-700 dark:text-gray-300 font-semibold">
            Estatísticas Gerais
          </li>
        </ol>
      </nav>

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
          <h1
            class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-dark-text-primary"
          >
            Indicadores do Departamento
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Análise completa de submissões, funcionários e desempenho
            <span
              class="text-xs bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300 px-2 py-1 rounded ml-2"
            >
              {{ formatDate(start_date) }} - {{ formatDate(end_date) }}
            </span>
          </p>
        </div>

        <div class="flex gap-2">
          <!-- Filtro de Período -->
          <select
            v-model="period"
            @change="changePeriod"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-secondary text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
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
            class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <ArrowDownTrayIcon class="w-5 h-5 mr-2" />
            {{ loading ? "Exportando..." : "Exportar" }}
          </button>
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

      <!-- Cards de Estatísticas Gerais -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total de Submissões -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900/20 rounded-lg p-3">
              <InboxIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Total de Submissões
              </p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ general_stats.total_submissions?.toLocaleString() || 0 }}
              </p>
              <div class="flex items-center mt-1">
                <span
                  :class="[
                    'text-xs font-medium',
                    general_stats.growth_rate >= 0
                      ? 'text-green-600 dark:text-green-400'
                      : 'text-red-600 dark:text-red-400',
                  ]"
                >
                  <ArrowTrendingUpIcon
                    v-if="general_stats.growth_rate >= 0"
                    class="w-4 h-4 inline mr-1"
                  />
                  <ArrowTrendingDownIcon v-else class="w-4 h-4 inline mr-1" />
                  {{ Math.abs(general_stats.growth_rate || 0) }}%
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400 ml-2"
                  >vs período anterior</span
                >
              </div>
            </div>
          </div>
        </div>

        <!-- Taxa de Resolução -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-100 dark:bg-green-900/20 rounded-lg p-3">
              <CheckCircleIcon class="h-6 w-6 text-green-600 dark:text-green-400" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Taxa de Resolução
              </p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ general_stats.resolution_rate || 0 }}%
              </p>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ general_stats.total_resolved?.toLocaleString() || 0 }} resolvidas
              </p>
            </div>
          </div>
        </div>

        <!-- Tempo Médio de Resolução -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-purple-100 dark:bg-purple-900/20 rounded-lg p-3">
              <ClockIcon class="h-6 w-6 text-purple-600 dark:text-purple-400" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Tempo Médio
              </p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ general_stats.avg_resolution_time || 0 }}h
              </p>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Até resolução</p>
            </div>
          </div>
        </div>

        <!-- Pendentes -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg p-3">
              <ExclamationTriangleIcon
                class="h-6 w-6 text-yellow-600 dark:text-yellow-400"
              />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Pendentes
              </p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ general_stats.pending_submissions?.toLocaleString() || 0 }}
              </p>
              <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                <span class="inline-block w-2 h-2 bg-yellow-500 rounded-full mr-1"></span>
                {{ general_stats.submissions_today || 0 }} hoje
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Grid de Gráficos -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Gráfico de Submissões por Dia -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
          <h2
            class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
          >
            Submissões por Dia
          </h2>
          <div class="h-64">
            <LineChart
              v-if="chart_data.daily_submissions_chart"
              :data="chart_data.daily_submissions_chart"
              :options="{
                plugins: {
                  title: {
                    display: false,
                  },
                },
                scales: {
                  y: {
                    title: {
                      display: true,
                      text: 'Número de Submissões',
                      color: isDarkMode ? '#9CA3AF' : '#6B7280',
                    },
                  },
                  x: {
                    title: {
                      display: true,
                      text: 'Data',
                      color: isDarkMode ? '#9CA3AF' : '#6B7280',
                    },
                  },
                },
              }"
            />
            <div v-else class="flex items-center justify-center h-full">
              <p class="text-gray-500 dark:text-gray-400">Sem dados disponíveis</p>
            </div>
          </div>
        </div>

        <!-- Distribuição por Status -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
          <h2
            class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
          >
            Distribuição por Status
          </h2>
          <div class="h-64">
            <PieChart
              v-if="chart_data.status_distribution_chart"
              :data="chart_data.status_distribution_chart"
              :options="{
                plugins: {
                  legend: {
                    position: 'right',
                  },
                },
              }"
            />
            <div v-else class="flex items-center justify-center h-full">
              <p class="text-gray-500 dark:text-gray-400">Sem dados disponíveis</p>
            </div>
          </div>
        </div>

        <!-- Status de Submissões -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6 mb-6">
          <h2
            class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
          >
            Status de Submissões
          </h2>
          <div class="h-64">
            <DoughnutChart
              v-if="chart_data.resolved_vs_pending_chart"
              :data="chart_data.resolved_vs_pending_chart"
              :options="{
                plugins: {
                  legend: {
                    position: 'bottom',
                  },
                },
              }"
            />
            <div v-else class="flex items-center justify-center h-full">
              <p class="text-gray-500 dark:text-gray-400">Sem dados disponíveis</p>
            </div>
          </div>
        </div>

        <!-- Distribuição por Tipo -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6 mb-6">
          <h2
            class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
          >
            Distribuição por Tipo
          </h2>
          <div class="h-64">
            <BarChart
              v-if="chart_data.type_distribution_chart"
              :data="chart_data.type_distribution_chart"
              :options="{
                indexAxis: 'y',
                plugins: {
                  legend: {
                    display: false,
                  },
                },
              }"
            />
            <div v-else class="flex items-center justify-center h-full">
              <p class="text-gray-500 dark:text-gray-400">Sem dados disponíveis</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Estatísticas de Funcionários - Versão Compacta -->
      <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6 mb-6">
        <!-- Cabeçalho com indicadores principais -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
          <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary">
              Gestor e Técnicos
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              {{ employee_stats.total_employees || 0 }} funcionários •
              {{ employee_stats.online_employees || 0 }} online
            </p>
          </div>

          <!-- Status rápido -->
          <div class="flex flex-wrap gap-3">
            <div
              class="flex items-center px-3 py-1.5 bg-green-50 dark:bg-green-900/20 rounded-lg"
            >
              <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
              <span class="text-sm font-medium text-green-700 dark:text-green-300">
                {{
                  (employee_stats.technicians?.active || 0) +
                  (employee_stats.managers?.active || 0)
                }}
                ativos
              </span>
            </div>
            <div
              class="flex items-center px-3 py-1.5 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
            >
              <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
              <span class="text-sm font-medium text-blue-700 dark:text-blue-300">
                +{{ employee_stats.new_employees || 0 }} novos (30d)
              </span>
            </div>
          </div>
        </div>

        <!-- Grid principal mais compacto -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
          <!-- Card: Distribuição por Função -->
          <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Distribuição por Função
              </h3>
              <span class="text-xs text-gray-500 dark:text-gray-400">
                {{ employee_stats.total_employees || 0 }} total
              </span>
            </div>

            <!-- Gráfico de rosca compacto -->
            <div class="relative h-32 mb-4">
              <!-- SVG para gráfico de rosca -->
              <svg class="w-full h-full" viewBox="0 0 100 100">
                <!-- Fundo -->
                <circle
                  cx="50"
                  cy="50"
                  r="40"
                  fill="none"
                  stroke="#e5e7eb"
                  :stroke-width="isDarkMode ? '10' : '8'"
                  stroke-dasharray="251.2"
                  stroke-dashoffset="0"
                />

                <!-- Gestores -->
                <circle
                  cx="50"
                  cy="50"
                  r="40"
                  fill="none"
                  stroke="#8b5cf6"
                  stroke-width="10"
                  stroke-dasharray="251.2"
                  :stroke-dashoffset="
                    251.2 -
                    (getRolePercentage('Gestor', employee_stats.by_role?.Gestor || 0) /
                      100) *
                      251.2
                  "
                  stroke-linecap="round"
                  transform="rotate(-90 50 50)"
                />

                <!-- Técnicos -->
                <circle
                  cx="50"
                  cy="50"
                  r="40"
                  fill="none"
                  stroke="#3b82f6"
                  stroke-width="10"
                  stroke-dasharray="251.2"
                  :stroke-dashoffset="
                    251.2 -
                    (getRolePercentage('Técnico', employee_stats.by_role?.Técnico || 0) /
                      100) *
                      251.2 -
                    (getRolePercentage('Gestor', employee_stats.by_role?.Gestor || 0) /
                      100) *
                      251.2
                  "
                  stroke-linecap="round"
                  transform="rotate(-90 50 50)"
                />
              </svg>

              <!-- Centro do gráfico -->
              <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center">
                  <div class="text-2xl font-bold text-gray-800 dark:text-white">
                    {{ employee_stats.total_employees || 0 }}
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Total</div>
                </div>
              </div>
            </div>

            <!-- Legenda -->
            <div class="space-y-2">
              <div
                v-for="(count, role) in employee_stats.by_role"
                :key="role"
                class="flex items-center justify-between text-sm"
              >
                <div class="flex items-center">
                  <div
                    :class="[
                      'w-3 h-3 rounded-full mr-2',
                      role === 'Gestor' ? 'bg-purple-500' : 'bg-blue-500',
                    ]"
                  ></div>
                  <span class="text-gray-600 dark:text-gray-300">{{ role }}</span>
                </div>
                <div class="flex items-center">
                  <span class="font-medium text-gray-800 dark:text-white mr-2">
                    {{ count }}
                  </span>
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    ({{ getRolePercentage(role, count) }}%)
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Card: Estatísticas de Disponibilidade -->
          <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
              Disponibilidade
            </h3>

            <!-- Gestores -->
            <div class="mb-4">
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center">
                  <div class="w-3 h-3 bg-purple-500 rounded-full mr-2"></div>
                  <span class="text-sm text-gray-600 dark:text-gray-300">Gestores</span>
                </div>
                <div class="flex items-center">
                  <span class="text-sm font-medium text-gray-800 dark:text-white mr-2">
                    {{ employee_stats.managers?.active || 0 }}/{{
                      employee_stats.managers?.total || 0
                    }}
                  </span>
                  <span class="text-xs font-medium text-purple-600 dark:text-purple-400">
                    {{ employee_stats.managers?.availability_rate || 0 }}%
                  </span>
                </div>
              </div>
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div
                  class="bg-purple-500 h-2 rounded-full transition-all duration-300"
                  :style="{
                    width: (employee_stats.managers?.availability_rate || 0) + '%',
                  }"
                ></div>
              </div>
              <div
                class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1"
              >
                <span>{{ employee_stats.managers?.active || 0 }} ativos</span>
                <span>{{ employee_stats.managers?.inactive || 0 }} inativos</span>
              </div>
            </div>

            <!-- Técnicos -->
            <div>
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center">
                  <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                  <span class="text-sm text-gray-600 dark:text-gray-300">Técnicos</span>
                </div>
                <div class="flex items-center">
                  <span class="text-sm font-medium text-gray-800 dark:text-white mr-2">
                    {{ employee_stats.technicians?.active || 0 }}/{{
                      employee_stats.technicians?.total || 0
                    }}
                  </span>
                  <span class="text-xs font-medium text-blue-600 dark:text-blue-400">
                    {{ employee_stats.technicians?.availability_rate || 0 }}%
                  </span>
                </div>
              </div>
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div
                  class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                  :style="{
                    width: (employee_stats.technicians?.availability_rate || 0) + '%',
                  }"
                ></div>
              </div>
              <div
                class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1"
              >
                <span>{{ employee_stats.technicians?.active || 0 }} ativos</span>
                <span>{{ employee_stats.technicians?.inactive || 0 }} inativos</span>
              </div>
            </div>

            <!-- Status geral -->
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
              <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500 dark:text-gray-400"
                  >Status geral:</span
                >
                <span
                  :class="[
                    'text-xs font-medium px-2 py-1 rounded',
                    getOverallStatus() === 'Excelente'
                      ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
                      : getOverallStatus() === 'Bom'
                      ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
                      : getOverallStatus() === 'Regular'
                      ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300'
                      : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                  ]"
                >
                  {{ getOverallStatus() }}
                </span>
              </div>
            </div>
          </div>

          <!-- Card: Produtividade e Crescimento -->
          <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Produtividade & Crescimento
              </h3>
              <div class="text-xs text-gray-500 dark:text-gray-400">
                Média: {{ employee_stats.avg_tasks_per_technician || 0 }} tarefas
              </div>
            </div>

            <!-- Média de tarefas -->
            <div class="mb-4">
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs text-gray-500 dark:text-gray-400"
                  >Média por técnico</span
                >
                <span class="text-sm font-medium text-gray-800 dark:text-white">
                  {{ employee_stats.avg_tasks_per_technician || 0 }}
                </span>
              </div>
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div
                  class="bg-green-500 h-2 rounded-full transition-all duration-300"
                  :style="{
                    width:
                      Math.min((employee_stats.avg_tasks_per_technician || 0) * 10, 100) +
                      '%',
                  }"
                ></div>
              </div>
              <div
                class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1"
              >
                <span>Baixa</span>
                <span>Alta</span>
              </div>
            </div>

            <!-- Crescimento recente -->
            <div class="mb-4">
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-500 dark:text-gray-400"
                  >Novos (último mês)</span
                >
                <div class="flex items-center">
                  <ArrowTrendingUpIcon class="w-3 h-3 text-green-500 mr-1" />
                  <span class="text-sm font-medium text-green-600 dark:text-green-400">
                    +{{ employee_stats.new_employees || 0 }}
                  </span>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-2">
                <div
                  class="bg-purple-50 dark:bg-purple-900/20 p-2 rounded-lg text-center"
                >
                  <div class="text-lg font-bold text-purple-600 dark:text-purple-400">
                    {{ employee_stats.new_managers || 0 }}
                  </div>
                  <div class="text-[10px] text-gray-500 dark:text-gray-400">Gestores</div>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 p-2 rounded-lg text-center">
                  <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                    {{
                      Math.max(
                        (employee_stats.new_employees || 0) -
                          (employee_stats.new_managers || 0),
                        0
                      )
                    }}
                  </div>
                  <div class="text-[10px] text-gray-500 dark:text-gray-400">Técnicos</div>
                </div>
              </div>
            </div>

            <!-- Tendência de crescimento -->
            <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs text-gray-500 dark:text-gray-400"
                  >Tendência (6 meses)</span
                >
                <span class="text-xs font-medium text-green-600 dark:text-green-400">
                  +{{ getTotalNewEmployees() }} novos
                </span>
              </div>
              <div class="flex items-end h-8 space-x-1">
                <div
                  v-for="(month, index) in employee_stats.employee_growth?.slice(-6) ||
                  []"
                  :key="index"
                  class="flex-1 flex flex-col items-center"
                >
                  <div
                    class="w-full bg-gradient-to-t from-blue-500 to-blue-400 dark:from-blue-600 dark:to-blue-500 rounded-t"
                    :style="{ height: getBarHeight(month.total_employees) * 0.8 + '%' }"
                  ></div>
                  <div class="text-[8px] text-gray-500 dark:text-gray-400 mt-1">
                    {{ month.month?.slice(0, 3) || "???" }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Seção de métricas rápidas -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">
              Disponibilidade Total
            </div>
            <div class="text-lg font-semibold text-gray-800 dark:text-white">
              {{ getOverallAvailability() }}%
            </div>
            <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">
              Gestores: {{ employee_stats.managers?.availability_rate || 0 }}% • Técnicos:
              {{ employee_stats.technicians?.availability_rate || 0 }}%
            </div>
          </div>

          <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Online agora</div>
            <div class="text-lg font-semibold text-green-600 dark:text-green-400">
              {{ employee_stats.online_employees || 0 }}
            </div>
            <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">
              {{ getOnlinePercentage() }}% da equipe
            </div>
          </div>

          <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">
              Novos últimos 30 dias
            </div>
            <div class="text-lg font-semibold text-blue-600 dark:text-blue-400">
              +{{ employee_stats.new_employees || 0 }}
            </div>
            <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">
              {{ getGrowthRate() }}% crescimento
            </div>
          </div>

          <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">
              Média de tarefas
            </div>
            <div class="text-lg font-semibold text-purple-600 dark:text-purple-400">
              {{ employee_stats.avg_tasks_per_technician || 0 }}
            </div>
            <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">
              {{ getProductivityStatus(employee_stats.avg_tasks_per_technician || 0) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Distribuição Geográfica -->
      <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4">
          Distribuição Geográfica
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Lista de Províncias -->
          <div class="space-y-3">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
              Top 10 Províncias
            </h3>
            <div class="space-y-2">
              <div
                v-for="(item, index) in geographic_distribution"
                :key="item.province"
                class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50"
              >
                <div class="flex items-center">
                  <span
                    class="text-xs font-medium text-gray-500 dark:text-gray-400 mr-3 w-6"
                    >{{ index + 1 }}</span
                  >
                  <MapPinIcon class="w-4 h-4 text-gray-400 dark:text-gray-500 mr-2" />
                  <span class="text-sm text-gray-700 dark:text-gray-300">{{
                    item.province
                  }}</span>
                </div>
                <div class="flex items-center">
                  <span class="text-sm font-medium text-gray-900 dark:text-white mr-2">{{
                    item.count
                  }}</span>
                  <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div
                      class="bg-blue-500 h-2 rounded-full"
                      :style="{ width: getProvincePercentage(item.count) + '%' }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Mapa (Placeholder) -->
          <div class="flex flex-col">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
              Mapa de Distribuição
            </h3>
            <div
              class="flex-1 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-lg flex items-center justify-center"
            >
              <div class="text-center p-4">
                <MapIcon
                  class="h-12 w-12 text-gray-400 dark:text-gray-600 mx-auto mb-2"
                />
                <p class="text-gray-500 dark:text-gray-400">Mapa interativo</p>
                <p class="text-sm text-gray-400 dark:text-gray-500">
                  Total: {{ getTotalGeographicCount() }} submissões
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Séries Temporais -->
      <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary">
            Tendências Temporais
          </h2>
          <span class="text-sm text-gray-500 dark:text-gray-400">
            {{ time_series_data.length }} semanas analisadas
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
              <tr
                class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                <th class="px-4 py-3">Semana</th>
                <th class="px-4 py-3">Submissões</th>
                <th class="px-4 py-3">Resolvidas</th>
                <th class="px-4 py-3">Taxa de Resolução</th>
                <th class="px-4 py-3">Tendência</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr
                v-for="week in time_series_data.slice(-8)"
                :key="week.week"
                class="text-sm hover:bg-gray-50 dark:hover:bg-gray-800/50"
              >
                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 font-medium">
                  {{ week.week }}
                </td>
                <td class="px-4 py-3">
                  <span class="font-medium text-gray-900 dark:text-white">{{
                    week.submissions
                  }}</span>
                </td>
                <td class="px-4 py-3">
                  <span class="font-medium text-green-600 dark:text-green-400">{{
                    week.resolved
                  }}</span>
                </td>
                <td class="px-4 py-3">
                  <span class="font-medium text-gray-900 dark:text-white">
                    {{
                      week.submissions > 0
                        ? Math.round((week.resolved / week.submissions) * 100)
                        : 0
                    }}%
                  </span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center">
                    <ArrowTrendingUpIcon
                      v-if="week.resolved >= week.submissions * 0.7"
                      class="w-4 h-4 text-green-500"
                    />
                    <ArrowTrendingDownIcon
                      v-else-if="week.resolved < week.submissions * 0.5"
                      class="w-4 h-4 text-red-500"
                    />
                    <MinusIcon v-else class="w-4 h-4 text-yellow-500" />
                    <span class="ml-1 text-xs text-gray-500 dark:text-gray-400">
                      {{ getTrendLabel(week.resolved / week.submissions) }}
                    </span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Desempenho por Técnico -->
      <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4">
          Desempenho por Técnico
        </h2>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
              <tr
                class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                <th class="px-4 py-3">Técnico</th>
                <th class="px-4 py-3">Total de Tarefas</th>
                <th class="px-4 py-3">Concluídas</th>
                <th class="px-4 py-3">Pendentes</th>
                <th class="px-4 py-3">Taxa de Conclusão</th>
                <th class="px-4 py-3">Tempo Médio</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr
                v-for="tech in performance_stats.technician_performance?.slice(0, 5) ||
                []"
                :key="tech.id"
                class="text-sm hover:bg-gray-50 dark:hover:bg-gray-800/50"
              >
                <td class="px-4 py-3">
                  <div class="flex items-center">
                    <div
                      class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center mr-2"
                    >
                      <UserIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">{{
                      tech.name
                    }}</span>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <span class="font-medium text-gray-900 dark:text-white">{{
                    tech.total_tasks
                  }}</span>
                </td>
                <td class="px-4 py-3">
                  <span class="font-medium text-green-600 dark:text-green-400">{{
                    tech.completed_tasks
                  }}</span>
                </td>
                <td class="px-4 py-3">
                  <span class="font-medium text-yellow-600 dark:text-yellow-400">{{
                    tech.pending_tasks
                  }}</span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center">
                    <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-2">
                      <div
                        class="bg-green-500 h-2 rounded-full"
                        :style="{ width: tech.completion_rate + '%' }"
                      ></div>
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white"
                      >{{ tech.completion_rate }}%</span
                    >
                  </div>
                </td>
                <td class="px-4 py-3">
                  <span class="font-medium text-gray-900 dark:text-white"
                    >{{ tech.avg_resolution_time }}h</span
                  >
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </UnifiedLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { Link, router } from "@inertiajs/vue3";
import UnifiedLayout from "@/Layouts/UnifiedLayout.vue";
import LineChart from "@/Components/Statistics/LineChart.vue";
import BarChart from "@/Components/Statistics/BarChart.vue";
import PieChart from "@/Components/Statistics/PieChart.vue";
import DoughnutChart from "@/Components/Statistics/DoughnutChart.vue";
import {
  ArrowDownTrayIcon,
  InboxIcon,
  CheckCircleIcon,
  ClockIcon,
  ExclamationTriangleIcon,
  MapPinIcon,
  MapIcon,
  UserIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  MinusIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  user: Object,
  role: String,
  period: String,
  start_date: String,
  end_date: String,
  general_stats: Object,
  submission_stats: Object,
  employee_stats: Object,
  performance_stats: Object,
  chart_data: Object,
  top_performers: Array,
  geographic_distribution: Array,
  time_series_data: Array,
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
const chart_data = computed(() => localData.value.chart_data || {});
const geographic_distribution = computed(
  () => localData.value.geographic_distribution || []
);
const time_series_data = computed(() => localData.value.time_series_data || []);
const performance_stats = computed(() => localData.value.performance_stats || {});
const top_performers = computed(() => localData.value.top_performers || []);
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

const getProvincePercentage = (count) => {
  const total =
    localData.value.geographic_distribution?.reduce((sum, item) => sum + item.count, 0) ||
    0;
  return total > 0 ? Math.round((count / total) * 100) : 0;
};

const getTotalGeographicCount = () => {
  return (
    localData.value.geographic_distribution?.reduce((sum, item) => sum + item.count, 0) ||
    0
  );
};

const getTrendLabel = (ratio) => {
  if (ratio >= 0.7) return "Excelente";
  if (ratio >= 0.5) return "Bom";
  if (ratio >= 0.3) return "Médio";
  return "Baixo";
};

const getRolePercentage = (role, count) => {
  const total = Object.values(employee_stats.value.by_role || {}).reduce(
    (a, b) => a + b,
    0
  );
  return total > 0 ? Math.round((count / total) * 100) : 0;
};

const getAvailabilityStatus = (rate) => {
  if (rate >= 80) return "Excelente";
  if (rate >= 60) return "Bom";
  if (rate >= 40) return "Regular";
  return "Crítico";
};

const getProductivityStatus = (avgTasks) => {
  if (avgTasks >= 10) return "Produtividade alta";
  if (avgTasks >= 5) return "Produtividade média";
  if (avgTasks >= 2) return "Produtividade baixa";
  return "Produtividade muito baixa";
};

const getTotalNewEmployees = () => {
  if (!employee_stats.value.employee_growth) return 0;
  return employee_stats.value.employee_growth.reduce(
    (total, month) => total + (month.new_employees || 0),
    0
  );
};

const getBarHeight = (totalEmployees) => {
  if (
    !employee_stats.value.employee_growth ||
    employee_stats.value.employee_growth.length === 0
  ) {
    return 0;
  }

  const maxEmployees = Math.max(
    ...employee_stats.value.employee_growth.map((m) => m.total_employees || 0)
  );

  return maxEmployees > 0 ? Math.min((totalEmployees / maxEmployees) * 100, 100) : 0;
};

const getNewEmployeeHeight = (month) => {
  if (!month.new_employees || month.total_employees === 0) return 0;
  return (month.new_employees / month.total_employees) * 100;
};

const getOverallStatus = () => {
  const managersRate = employee_stats.value.managers?.availability_rate || 0;
  const techniciansRate = employee_stats.value.technicians?.availability_rate || 0;
  const avgAvailability = (managersRate + techniciansRate) / 2;
  if (avgAvailability >= 85) return "Excelente";
  if (avgAvailability >= 70) return "Bom";
  if (avgAvailability >= 50) return "Regular";
  return "Crítico";
};

const getOverallAvailability = () => {
  const managers = employee_stats.value.managers || {};
  const technicians = employee_stats.value.technicians || {};
  const totalActive = (managers.active || 0) + (technicians.active || 0);
  const totalEmployees = (managers.total || 0) + (technicians.total || 0);
  return totalEmployees > 0 ? Math.round((totalActive / totalEmployees) * 100) : 0;
};

const getOnlinePercentage = () => {
  const managers = employee_stats.value.managers || {};
  const technicians = employee_stats.value.technicians || {};
  const totalEmployees = (managers.total || 0) + (technicians.total || 0);
  return totalEmployees > 0
    ? Math.round(((employee_stats.value.online_employees || 0) / totalEmployees) * 100)
    : 0;
};

const getGrowthRate = () => {
  if (
    !employee_stats.value.employee_growth ||
    employee_stats.value.employee_growth.length < 2
  )
    return 0;

  const lastMonth =
    employee_stats.value.employee_growth[employee_stats.value.employee_growth.length - 1];
  const prevMonth =
    employee_stats.value.employee_growth[employee_stats.value.employee_growth.length - 2];

  if (!prevMonth?.total_employees || prevMonth.total_employees === 0) return 0;

  const growth =
    ((lastMonth.total_employees - prevMonth.total_employees) /
      prevMonth.total_employees) *
    100;
  return Math.round(growth);
};

const getGrowthPercentage = (month) => {
  if (
    !employee_stats.value.employee_growth ||
    employee_stats.value.employee_growth.length === 0
  ) {
    return 0;
  }

  const maxValue = Math.max(
    ...employee_stats.value.employee_growth.map((m) => m.total_employees || 0)
  );
  return maxValue > 0 ? Math.round(((month.total_employees || 0) / maxValue) * 100) : 0;
};
</script>
