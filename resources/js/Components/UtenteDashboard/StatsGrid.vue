<template>
  <div>
    <div class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">
      <h1>Visão Geral das Minhas Submissões</h1>
    </div>

    <!-- Estatísticas por Tipo -->
    <div class="text-lg font-semibold text-gray-800 dark:text-white mb-3">
      <h2 class="flex items-center gap-2">
        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center shadow-lg">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
          </svg>
        </div>
        Estatísticas por Tipo
      </h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <StatCard
        title="Reclamações"
        :value="statsByType.complaints.toString()"
        description="Total de reclamações submetidas"
        :icon="ClipboardDocumentIcon"
        color="red"
      />
      <StatCard
        title="Queixas"
        :value="statsByType.grievances.toString()"
        description="Total de queixas submetidas"
        :icon="ExclamationTriangleIcon"
        color="orange"
      />
      <StatCard
        title="Sugestões"
        :value="statsByType.suggestions.toString()"
        description="Total de sugestões submetidas"
        :icon="LightBulbIcon"
        color="blue"
      />
    </div>

    <!-- Estatísticas por Status -->
    <div class="text-lg font-semibold text-gray-800 dark:text-white mb-3 mt-8">
      <h2 class="flex items-center gap-2">
        <div class="w-8 h-8 bg-gradient-to-br from-cyan-500 to-teal-600 rounded-lg flex items-center justify-center shadow-lg">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
          </svg>
        </div>
        Status das Submissões
      </h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
      <StatCard
        title="Total de Submissões"
        :value="stats.total.toString()"
        description="Todas as submissões"
        :icon="ChartBarIcon"
        color="purple"
      />
      <StatCard
        title="Em Progresso"
        :value="stats.in_progress.toString()"
        description="Sendo processadas"
        :icon="ArrowPathIcon"
        color="orange"
      />
      <StatCard
        title="Resolvidas"
        :value="stats.resolved.toString()"
        description="Concluídas com sucesso"
        :icon="CheckCircleIcon"
        color="green"
      />
      <StatCard
        title="Pendentes"
        :value="stats.submitted.toString()"
        description="Aguardando análise"
        :icon="ClockIcon"
        color="yellow"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import StatCard from './StatCard.vue'
import { ClipboardDocumentIcon, ExclamationTriangleIcon, LightBulbIcon, ChartBarIcon, ArrowPathIcon, CheckCircleIcon, ClockIcon } from '@heroicons/vue/24/outline'

const page = usePage()

const stats = computed(() => {
  return page.props.stats || {
    total: 0,
    submitted: 0,
    in_progress: 0,
    resolved: 0,
    closed: 0,
    rejected: 0
  }
})

const statsByType = computed(() => {
  return page.props.statsByType || {
    complaints: 0,
    grievances: 0,
    suggestions: 0
  }
})
</script>
