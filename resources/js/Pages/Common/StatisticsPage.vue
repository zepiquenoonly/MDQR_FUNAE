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
            Estatísticas Gerais
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
            @click="exportStatistics"
            class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors font-medium"
          >
            <ArrowDownTrayIcon class="w-5 h-5 mr-2" />
            Exportar
          </button>
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
                {{ general_stats.total_submissions.toLocaleString() }}
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
                  {{ Math.abs(general_stats.growth_rate) }}%
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
                {{ general_stats.resolution_rate }}%
              </p>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ general_stats.total_resolved.toLocaleString() }} resolvidas
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
                {{ general_stats.avg_resolution_time }}h
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
                {{ general_stats.pending_submissions.toLocaleString() }}
              </p>
              <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                <span class="inline-block w-2 h-2 bg-yellow-500 rounded-full mr-1"></span>
                {{ general_stats.submissions_today }} hoje
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Grid de Gráficos e Estatísticas -->
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

      <!-- Estatísticas de Funcionários -->
      <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary">
            Estatísticas de Funcionários
          </h2>
          <span class="text-sm text-gray-500 dark:text-gray-400">
            {{ employee_stats.total_employees }} funcionários no total
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Total por Role -->
          <div class="space-y-3">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
              Distribuição por Função
            </h3>
            <div
              v-for="(count, role) in employee_stats.by_role"
              :key="role"
              class="flex items-center justify-between text-sm"
            >
              <span class="text-gray-600 dark:text-gray-400">{{ role }}</span>
              <span class="font-medium text-gray-900 dark:text-white">{{ count }}</span>
            </div>
          </div>

          <!-- Técnicos -->
          <div class="space-y-3">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Técnicos</h3>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Total</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                  employee_stats.technicians.total
                }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Ativos</span>
                <span class="text-sm font-medium text-green-600 dark:text-green-400">{{
                  employee_stats.technicians.active
                }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Inativos</span>
                <span class="text-sm font-medium text-red-600 dark:text-red-400">{{
                  employee_stats.technicians.inactive
                }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400"
                  >Taxa de Disponibilidade</span
                >
                <span class="text-sm font-medium text-gray-900 dark:text-white"
                  >{{ employee_stats.technicians.availability_rate }}%</span
                >
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400"
                  >Média de Tarefas</span
                >
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                  employee_stats.avg_tasks_per_technician
                }}</span>
              </div>
            </div>
          </div>

          <!-- Crescimento -->
          <div class="space-y-3">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
              Crescimento
            </h3>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400"
                  >Novos (30 dias)</span
                >
                <span class="text-sm font-medium text-green-600 dark:text-green-400">{{
                  employee_stats.new_employees
                }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Online agora</span>
                <span class="text-sm font-medium text-blue-600 dark:text-blue-400">{{
                  employee_stats.online_employees
                }}</span>
              </div>
            </div>

            <div class="pt-2">
              <h4 class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">
                Crescimento (últimos 6 meses)
              </h4>
              <div class="space-y-1">
                <div
                  v-for="month in employee_stats.employee_growth.slice(-6)"
                  :key="month.month"
                  class="flex items-center justify-between text-xs"
                >
                  <span class="text-gray-500 dark:text-gray-400">{{ month.month }}</span>
                  <div class="flex items-center gap-2">
                    <span class="text-green-600 dark:text-green-400 font-medium"
                      >+{{ month.new_employees }}</span
                    >
                    <span class="text-gray-900 dark:text-gray-300">{{
                      month.total_employees
                    }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Top Performers -->
          <div class="space-y-3">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
              Top Performers
            </h3>
            <div class="space-y-2">
              <div
                v-for="(performer, index) in top_performers.slice(0, 5)"
                :key="performer.id"
                class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50"
              >
                <div class="flex items-center">
                  <div
                    class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center mr-2"
                  >
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                      {{ index + 1 }}
                    </span>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ performer.name }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      {{ performer.resolved_count }} resolvidas
                    </p>
                  </div>
                </div>
                <div class="w-2 h-2 rounded-full bg-green-500"></div>
              </div>
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
                v-for="tech in performance_stats.technician_performance.slice(0, 5)"
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
import { Link, router } from "@inertiajs/vue3"; // Importe router do Inertia
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
  ChartBarIcon,
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

// Computed para verificar modo escuro
const isDarkMode = computed(() => {
  return document.documentElement.classList.contains("dark");
});

// Função para carregar estatísticas via AJAX
const loadStatistics = async () => {
  loading.value = true;

  try {
    // Use o router do Inertia para fazer uma requisição GET com os parâmetros
    router.get(
      "/statistics",
      { period: period.value },
      {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
          // Atualize os dados locais com os novos dados
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

// Alternativa: Use fetch API diretamente
const loadStatisticsAlternative = async () => {
  loading.value = true;

  try {
    const response = await fetch(`/api/stats?period=${period.value}`, {
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    });

    if (response.ok) {
      const data = await response.json();
      // Atualize os dados locais
      localData.value = {
        ...localData.value,
        ...data,
      };
    } else {
      console.error("Erro na resposta:", response.status);
    }
  } catch (error) {
    console.error("Erro na requisição:", error);
  } finally {
    loading.value = false;
  }
};

// Função para exportar
const exportStatistics = async () => {
  try {
    const response = await fetch("/statistics/export", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
      },
      body: JSON.stringify({ period: period.value }),
    });

    if (response.ok) {
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = `estatisticas-${period.value}-${
        new Date().toISOString().split("T")[0]
      }.pdf`;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
    } else {
      alert("Erro ao exportar estatísticas");
    }
  } catch (error) {
    console.error("Erro:", error);
    alert("Erro ao exportar estatísticas");
  }
};

// Observar mudanças no modo escuro para atualizar gráficos
watch(isDarkMode, () => {
  // Recarregar dados para atualizar cores dos gráficos
  loadStatistics();
});

// Inicializar quando o componente é montado
onMounted(() => {
  // Verificar se temos dados iniciais
  if (!props.chart_data || Object.keys(props.chart_data).length === 0) {
    loadStatistics();
  }
});

// Helper functions (mantenha as existentes)
const formatDate = (dateString) => {
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

// Computed properties para acessar os dados
const general_stats = computed(() => localData.value.general_stats || {});
const chart_data = computed(() => localData.value.chart_data || {});
const geographic_distribution = computed(
  () => localData.value.geographic_distribution || []
);
const time_series_data = computed(() => localData.value.time_series_data || []);
const performance_stats = computed(() => localData.value.performance_stats || {});
const top_performers = computed(() => localData.value.top_performers || []);
const employee_stats = computed(() => localData.value.employee_stats || {});
const start_date = computed(() => localData.value.start_date);
const end_date = computed(() => localData.value.end_date);
</script>
