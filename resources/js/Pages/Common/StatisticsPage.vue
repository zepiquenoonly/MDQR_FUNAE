<template>
  <UnifiedLayout :user="user" :role="role">
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
              <span
                class="text-xs bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300 px-2 py-1 rounded"
              >
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
                  {{ loading ? "A Exportar..." : "Exportar" }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- KPIs Grid -->
      <div
        class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4 lg:gap-6"
      >
        <KpiCard
          title="Total de Submissões"
          :value="general_stats.total_submissions?.toLocaleString() || 0"
          description="Todas as reclamações, queixas e sugestões"
          icon="DocumentTextIcon"
          :trend="general_stats.growth_rate >= 0 ? 'up' : 'down'"
        />

        <KpiCard
          title="Taxa de Resolução"
          :value="(general_stats.resolution_rate || 0) + '%'"
          description="Submissões finalizadas com sucesso"
          icon="CheckCircleIcon"
          :trend="(general_stats.resolution_rate || 0) >= 80 ? 'up' : 'down'"
        />

        <KpiCard
          title="Tempo Médio"
          :value="(general_stats.avg_resolution_time || 0) + 'h'"
          description="Até resolução completa"
          icon="ClockIcon"
          :trend="(general_stats.avg_resolution_time || 0) <= 48 ? 'up' : 'down'"
        />

        <KpiCard
          title="Funcionários Ativos"
          :value="employee_stats.online_employees || 0"
          description="Gestores e técnicos disponíveis"
          icon="UsersIcon"
          :trend="
            (employee_stats.online_employees || 0) >=
            (employee_stats.total_employees || 0) * 0.8
              ? 'up'
              : 'down'
          "
        />
      </div>

      <!-- Resumo Executivo -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Principais Métricas -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
          <h3
            class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
          >
            Principais Métricas
          </h3>
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400"
                >Submissões Hoje</span
              >
              <span class="font-semibold text-gray-900 dark:text-white">{{
                general_stats.submissions_today || 0
              }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400"
                >Submissões Esta Semana</span
              >
              <span class="font-semibold text-blue-600 dark:text-blue-400">{{
                general_stats.submissions_this_week || 0
              }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400"
                >Submissões Este Mês</span
              >
              <span class="font-semibold text-blue-600 dark:text-blue-400">{{
                general_stats.submissions_this_month || 0
              }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400"
                >Taxa de Crescimento</span
              >
              <span
                class="font-semibold"
                :class="
                  general_stats.growth_rate >= 0
                    ? 'text-green-600 dark:text-green-400'
                    : 'text-red-600 dark:text-red-400'
                "
              >
                {{ general_stats.growth_rate >= 0 ? "+" : ""
                }}{{ general_stats.growth_rate || 0 }}%
              </span>
            </div>
          </div>
        </div>

        <!-- Distribuição por Status (Resumo) -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
          <h3
            class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
          >
            Distribuição por Status
          </h3>
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">Resolvidas</span>
              <div class="flex items-center">
                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                <span class="font-semibold text-gray-900 dark:text-white">{{
                  general_stats.total_resolved || 0
                }}</span>
              </div>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">Pendentes</span>
              <div class="flex items-center">
                <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                <span class="font-semibold text-gray-900 dark:text-white">{{
                  general_stats.pending_submissions || 0
                }}</span>
              </div>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400"
                >Taxa de Resolução</span
              >
              <div class="flex items-center">
                <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                <span class="font-semibold text-gray-900 dark:text-white"
                  >{{ general_stats.resolution_rate || 0 }}%</span
                >
              </div>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">Tempo Médio</span>
              <div class="flex items-center">
                <div class="w-3 h-3 bg-purple-500 rounded-full mr-2"></div>
                <span class="font-semibold text-gray-900 dark:text-white"
                  >{{ general_stats.avg_resolution_time || 0 }}h</span
                >
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Seção de Gráficos -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Distribuição por Tipo -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary">
              Distribuição por Tipo
            </h3>
            <div class="flex space-x-2">
              <button
                @click="activeTypeChart = 'doughnut'"
                :class="[
                  'px-3 py-1 text-sm rounded-lg transition-colors',
                  activeTypeChart === 'doughnut'
                    ? 'bg-blue-500 text-white'
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600',
                ]"
              >
                Donut
              </button>
              <button
                @click="activeTypeChart = 'pie'"
                :class="[
                  'px-3 py-1 text-sm rounded-lg transition-colors',
                  activeTypeChart === 'pie'
                    ? 'bg-blue-500 text-white'
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600',
                ]"
              >
                Pizza
              </button>
            </div>
          </div>
          <div class="h-64">
            <DistributionChart
              v-if="Object.keys(typeDistribution).length > 0"
              :chart-type="activeTypeChart"
              :chart-data="typeDistribution"
              title=""
              :height="256"
              legend-position="bottom"
            />
            <div v-else class="h-full flex items-center justify-center text-gray-500">
              Sem dados de tipo disponíveis
            </div>
          </div>
          <div class="mt-4 text-sm text-gray-600 dark:text-gray-400 text-center">
            <p>Total de submissões por tipo</p>
          </div>
        </div>

        <!-- Timeline de Resolução -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary">
              Evolução Semanal
            </h3>
            <div class="flex space-x-2">
              <button
                @click="activeTimelineChart = 'line'"
                :class="[
                  'px-3 py-1 text-sm rounded-lg transition-colors',
                  activeTimelineChart === 'line'
                    ? 'bg-blue-500 text-white'
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600',
                ]"
              >
                Linha
              </button>
              <button
                @click="activeTimelineChart = 'bar'"
                :class="[
                  'px-3 py-1 text-sm rounded-lg transition-colors',
                  activeTimelineChart === 'bar'
                    ? 'bg-blue-500 text-white'
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600',
                ]"
              >
                Barras
              </button>
            </div>
          </div>
          <div class="h-64">
            <DistributionChart
              v-if="timelineData.length > 0"
              :chart-type="activeTimelineChart"
              :chart-data="prepareTimelineData"
              title=""
              :height="256"
              legend-position="top"
            />
            <div v-else class="h-full flex items-center justify-center text-gray-500">
              Sem dados temporais disponíveis
            </div>
          </div>
          <div class="mt-4 text-sm text-gray-600 dark:text-gray-400 text-center">
            <p>Evolução semanal de submissões vs. resoluções</p>
          </div>
        </div>
      </div>

      <!-- Distribuição por Status e Províncias -->
      <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Gráfico de Distribuição por Status -->
          <div>
            <h3
              class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-6"
            >
              Distribuição por Status
            </h3>
            <div class="h-80">
              <DistributionChart
                v-if="Object.keys(statusDistribution).length > 0"
                chart-type="doughnut"
                :chart-data="statusDistribution"
                title=""
                :height="320"
                legend-position="right"
              />
              <div v-else class="h-full flex items-center justify-center text-gray-500">
                Sem dados de status disponíveis
              </div>
            </div>
          </div>

          <!-- Distribuição por Províncias -->
          <div>
            <h3
              class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-6"
            >
              Distribuição por Província
            </h3>
            <div
              v-if="geographicDistribution.length > 0"
              class="h-80 overflow-y-auto pr-4"
            >
              <div class="space-y-4">
                <div
                  v-for="(province, index) in geographicDistribution"
                  :key="province.province"
                  class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                  <div class="flex items-center">
                    <div class="w-8 h-8 flex items-center justify-center mr-3">
                      <span
                        class="text-gray-600 dark:text-gray-400 font-semibold text-sm"
                      >
                        {{ index + 1 }}
                      </span>
                    </div>
                    <div>
                      <div class="font-medium text-gray-900 dark:text-white">
                        {{ province.province || "Não especificada" }}
                      </div>
                      <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ province.count }} submissões
                      </div>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="font-bold text-gray-900 dark:text-white text-lg">
                      {{ province.count }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                      {{ getPercentage(province.count, totalGeographicSubmissions) }}%
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="h-full flex items-center justify-center text-gray-500">
              Sem dados geográficos disponíveis
            </div>
            <div
              v-if="geographicDistribution.length > 0"
              class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"
            >
              <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600 dark:text-gray-400">Total de submissões</span>
                <span class="font-semibold text-gray-900 dark:text-white">
                  {{ totalGeographicSubmissions }}
                </span>
              </div>
              <div class="flex justify-between items-center text-sm mt-2">
                <span class="text-gray-600 dark:text-gray-400"
                  >Províncias registadas</span
                >
                <span class="font-semibold text-gray-900 dark:text-white">
                  {{ geographicDistribution.length }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Distribuição por Funcionários -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Gráfico de Distribuição de Funcionários -->
        <div
          v-if="Object.keys(employeeDistribution).length > 0"
          class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6"
        >
          <h3
            class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-6"
          >
            Distribuição de Funcionários
          </h3>
          <div class="h-64">
            <DistributionChart
              chart-type="doughnut"
              :chart-data="employeeDistribution"
              title=""
              :height="256"
              legend-position="bottom"
            />
          </div>
          <div class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
            <p>Total de {{ employee_stats.total_employees || 0 }} funcionários</p>
          </div>
        </div>

        <!-- Top Províncias Resumo -->
        <div
          v-if="geographicDistribution.length > 0"
          class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6"
        >
          <h3
            class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-6"
          >
            Top Províncias
          </h3>
          <div class="space-y-4">
            <div
              v-for="(province, index) in topProvinces"
              :key="province.province"
              class="flex items-center"
            >
              <div class="w-8 text-center">
                <div
                  class="w-6 h-6 flex items-center justify-center rounded-full text-xs font-semibold"
                  :class="[
                    'text-white',
                    index === 0
                      ? 'bg-yellow-500'
                      : index === 1
                      ? 'bg-gray-400'
                      : index === 2
                      ? 'bg-amber-700'
                      : 'bg-gray-300 dark:bg-gray-600',
                  ]"
                >
                  {{ index + 1 }}
                </div>
              </div>
              <div class="flex-1 ml-4">
                <div class="flex justify-between mb-1">
                  <span class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ province.province || "Não especificada" }}
                  </span>
                  <span class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ province.count }}
                  </span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                  <div
                    class="bg-blue-500 h-2 rounded-full"
                    :style="{
                      width:
                        getPercentage(province.count, totalGeographicSubmissions) + '%',
                    }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
          <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
              <span>Média por província:</span>
              <span class="font-semibold text-gray-900 dark:text-white">
                {{ Math.round(averagePerProvince) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Performers -->
      <div
        v-if="topPerformers.length > 0"
        class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6"
      >
        <h3 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-6">
          Melhores Técnicos
        </h3>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700">
                <th
                  class="text-left py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400"
                >
                  Técnico
                </th>
                <th
                  class="text-left py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400"
                >
                  Tarefas Resolvidas
                </th>
                <th
                  class="text-left py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400"
                >
                  Taxa de Conclusão
                </th>
                <th
                  class="text-left py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400"
                >
                  Tempo Médio
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="performer in performanceStats.technician_performance"
                :key="performer.id"
                class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800"
              >
                <td class="py-3 px-4">
                  <div class="flex items-center">
                    <div
                      class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mr-3"
                    >
                      <span class="text-blue-600 dark:text-blue-300 font-medium text-sm">
                        {{ getInitials(performer.name) }}
                      </span>
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">{{
                      performer.name
                    }}</span>
                  </div>
                </td>
                <td class="py-3 px-4">
                  <div class="flex items-center">
                    <span class="font-semibold text-gray-900 dark:text-white">{{
                      performer.completed_tasks || 0
                    }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400 ml-1"
                      >/ {{ performer.total_tasks || 0 }}</span
                    >
                  </div>
                </td>
                <td class="py-3 px-4">
                  <div class="flex items-center">
                    <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-3">
                      <div
                        class="bg-green-500 h-2 rounded-full"
                        :style="{
                          width: `${Math.min(performer.completion_rate || 0, 100)}%`,
                        }"
                      ></div>
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white"
                      >{{ performer.completion_rate || 0 }}%</span
                    >
                  </div>
                </td>
                <td class="py-3 px-4">
                  <span class="font-medium text-gray-900 dark:text-white"
                    >{{ performer.avg_resolution_time || 0 }}h</span
                  >
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </UnifiedLayout>
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
        <!--<button
              @click="handleExport('xlsx')"
              :disabled="loading"
              class="w-full flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <div class="flex items-center">
                <div
                  class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-3"
                >
                  <span class="text-green-600 dark:text-green-300 font-bold">XLSX</span>
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
              :disabled="loading"
              class="w-full flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
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
            </button>-->

        <button
          @click="handleExport('pdf')"
          :disabled="loading"
          class="w-full flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
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
</template>

<script setup>
import axios from "axios";
import { ref, computed, onMounted, watch } from "vue";
import { Link, router } from "@inertiajs/vue3";
import UnifiedLayout from "@/Layouts/UnifiedLayout.vue";
import KpiCard from "@/Components/GestorReclamacoes/KpiCard.vue";
import DistributionChart from "@/Components/Indicators/Charts/DistributionChart.vue";
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
  chart_data: Object,
  top_performers: Array,
  geographic_distribution: Array,
  time_series_data: Array,
  performance_stats: Object,
});

// Estados reativos
const period = ref(props.period);
const loading = ref(false);
const localData = ref({ ...props });
const showExportModal = ref(false);
const activeTypeChart = ref("doughnut");
const activeTimelineChart = ref("line");
const exportToast = ref(null);

// Computed para verificar modo escuro
const isDarkMode = computed(() => {
  return document.documentElement.classList.contains("dark");
});

// Computed properties para acessar os dados
const general_stats = computed(() => localData.value.general_stats || {});
const employee_stats = computed(() => localData.value.employee_stats || {});
const chart_data = computed(() => localData.value.chart_data || {});
const topPerformers = computed(() => localData.value.top_performers || []);
const geographicDistribution = computed(
  () => localData.value.geographic_distribution || []
);
const timeSeriesData = computed(() => localData.value.time_series_data || []);
const performanceStats = computed(() => localData.value.performance_stats || {});
const start_date = computed(() => localData.value.start_date);
const end_date = computed(() => localData.value.end_date);

// Computed para dados geográficos
const totalGeographicSubmissions = computed(() => {
  return geographicDistribution.value.reduce(
    (total, province) => total + province.count,
    0
  );
});

const topProvinces = computed(() => {
  return [...geographicDistribution.value].sort((a, b) => b.count - a.count).slice(0, 5);
});

const averagePerProvince = computed(() => {
  if (geographicDistribution.value.length === 0) return 0;
  return totalGeographicSubmissions.value / geographicDistribution.value.length;
});

// Dados para os gráficos - usando dados do backend
const typeDistribution = computed(() => {
  // Usar type_distribution do chart_data
  const typeData = chart_data.value.type_distribution || {};

  // Traduzir labels para Português
  const translatedData = {};
  Object.entries(typeData).forEach(([key, value]) => {
    const translatedKey = getTranslatedType(key);
    translatedData[translatedKey] = value;
  });

  return translatedData;
});

const statusDistribution = computed(() => {
  // Usar status_distribution do chart_data
  const statusData = chart_data.value.status_distribution || {};

  // Traduzir labels para Português
  const translatedData = {};
  Object.entries(statusData).forEach(([key, value]) => {
    const translatedKey = getTranslatedStatus(key);
    translatedData[translatedKey] = value;
  });

  return translatedData;
});

const employeeDistribution = computed(() => {
  const stats = employee_stats.value;

  // Construir distribuição baseada nos dados reais
  const distribution = {};

  if (stats.managers?.total !== undefined) {
    distribution.Gestores = stats.managers.total;
  }

  if (stats.technicians?.total !== undefined) {
    distribution.Técnicos = stats.technicians.total;
  }

  if (stats.by_role) {
    Object.entries(stats.by_role).forEach(([role, count]) => {
      const translatedRole = getTranslatedRole(role);
      distribution[translatedRole] = count;
    });
  }

  return distribution;
});

const timelineData = computed(() => {
  return timeSeriesData.value;
});

const prepareTimelineData = computed(() => {
  // Formatar dados para o componente DistributionChart
  if (!timelineData.value.length) return [];

  return timelineData.value.map((item) => ({
    month: item.week,
    submitted: item.submissions || 0,
    resolved: item.resolved || 0,
  }));
});

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
    const formatMapping = {
      xlsx: "xlsx",
      csv: "csv",
      pdf: "pdf",
    };

    const backendFormat = formatMapping[format] || format;

    console.log(`Iniciando exportação para ${backendFormat}...`);

    const response = await axios.post("/director/export/async", {
      period: period.value,
      format: backendFormat,
    });

    console.log("Resposta do servidor:", response.data);

    if (response.data.status === "queued") {
      showToast("Exportação em processamento. Aguarde alguns instantes...", "info");

      // Iniciar verificação do status
      startExportPolling(backendFormat);
    } else {
      showToast("Resposta inesperada do servidor.", "error");
      loading.value = false;
    }
  } catch (error) {
    console.error("Erro:", error.response?.data || error.message);
    showToast(
      "Erro ao exportar: " + (error.response?.data?.message || error.message),
      "error"
    );
    loading.value = false;
  }
};

const startExportPolling = (format) => {
  let attempts = 0;
  const maxAttempts = 30;
  const checkInterval = 2000;

  const poll = async () => {
    attempts++;

    if (attempts > maxAttempts) {
      showToast("Tempo limite excedido. A exportação está demorando muito.", "warning");
      loading.value = false;
      return;
    }

    console.log(`Verificando exportação (tentativa ${attempts}/${maxAttempts})...`);

    try {
      const response = await axios.get("/director/export/status");
      const files = response.data;

      // Encontrar o arquivo mais recente do formato correto
      let targetFile = null;

      // Procurar por extensão
      const extension = format.toLowerCase();
      targetFile = files.find(
        (f) =>
          f.filename.toLowerCase().endsWith(`.${extension}`) ||
          (f.filename.toLowerCase().includes("estatisticas") &&
            f.filename.toLowerCase().includes(extension))
      );

      // Se não encontrar, usar o mais recente
      if (!targetFile && files.length > 0) {
        targetFile = files[0];
      }

      if (targetFile) {
        console.log("✅ Arquivo encontrado:", targetFile);
        loading.value = false;

        // Forçar download
        await forceDownload(targetFile);

        showToast(`Download iniciado: ${targetFile.filename}`, "success");
        return;
      } else {
        setTimeout(poll, checkInterval);
      }
    } catch (error) {
      console.error("Erro no polling:", error);
      setTimeout(poll, checkInterval);
    }
  };

  poll();
};

const forceDownload = async (file) => {
  try {
    // Usar download_url se disponível, senão usar url
    const downloadUrl = file.download_url || file.url;

    // Criar link invisível e clicar
    const link = document.createElement("a");
    link.href = downloadUrl;
    link.download = file.filename;
    link.target = "_blank";
    link.style.display = "none";

    document.body.appendChild(link);
    link.click();

    // Limpar após o clique
    setTimeout(() => {
      document.body.removeChild(link);
    }, 100);

    return true;
  } catch (error) {
    console.error("Erro ao forçar download:", error);
    // Fallback: abrir em nova aba
    window.open(file.url || file.download_url, "_blank");
    return false;
  }
};

// Sistema de notificações melhorado (sem alerts)
const showToast = (message, type = "info") => {
  // Remover toast anterior se existir
  if (exportToast.value) {
    exportToast.value.remove();
  }

  // Criar novo toast
  const toast = document.createElement("div");
  exportToast.value = toast;

  const colors = {
    success: "bg-green-500",
    error: "bg-red-500",
    warning: "bg-yellow-500",
    info: "bg-blue-500",
  };

  toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-x-0`;
  toast.innerHTML = `
    <div class="flex items-center">
      <span class="font-medium">${message}</span>
      <button class="ml-4 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">
        ×
      </button>
    </div>
  `;

  document.body.appendChild(toast);

  // Auto-remover após 5 segundos
  setTimeout(() => {
    if (toast.parentNode) {
      toast.classList.add("opacity-0", "translate-x-full");
      setTimeout(() => {
        if (toast.parentNode) {
          toast.remove();
        }
      }, 300);
    }
  }, 5000);
};

// Observar mudanças no modo escuro para atualizar gráficos
watch(isDarkMode, () => {
  loadStatistics();
});

// Inicializar quando o componente é montado
onMounted(() => {
  loadStatistics();
});

// Helper functions
const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  try {
    return new Date(dateString).toLocaleDateString("pt-PT");
  } catch (error) {
    return "Data inválida";
  }
};

const getTranslatedType = (type) => {
  const translations = {
    grievance: "Queixa",
    complaint: "Reclamação",
    suggestion: "Sugestão",
  };
  return translations[type] || type;
};

const getTranslatedStatus = (status) => {
  const translations = {
    submitted: "Submetida",
    under_review: "Em Revisão",
    assigned: "Atribuída",
    in_progress: "Em Progresso",
    pending_approval: "Aprovação Pendente",
    resolved: "Resolvida",
    rejected: "Rejeitada",
    cancelled: "Cancelada",
  };
  return translations[status] || status;
};

const getTranslatedRole = (role) => {
  const translations = {
    Gestor: "Gestor",
    Técnico: "Técnico",
    Director: "Diretor",
    PCA: "PCA",
  };
  return translations[role] || role;
};

const getInitials = (name) => {
  if (!name) return "??";
  return name
    .split(" ")
    .map((word) => word[0])
    .join("")
    .toUpperCase()
    .substring(0, 2);
};

const getPercentage = (value, total) => {
  if (total === 0) return 0;
  return Math.round((value / total) * 100);
};
</script>
