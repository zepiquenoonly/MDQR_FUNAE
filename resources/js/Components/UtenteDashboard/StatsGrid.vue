<template>
  <div class="text-2xl font-semibold text-gray-800 mb-2">
    <h1>Visão Geral das Minhas Submissões</h1>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
    <StatCard 
      title="Total de Submissões" 
      :value="stats.total.toString()" 
      description="Todas as reclamações" 
      icon="📊"
      color="blue"
    />
    <StatCard 
      title="Em Progresso" 
      :value="stats.in_progress.toString()" 
      description="Sendo processadas" 
      icon="🔄"
      color="orange"
    />
    <StatCard 
      title="Resolvidas" 
      :value="stats.resolved.toString()" 
      description="Concluídas com sucesso" 
      icon="✅"
      color="green"
    />
    <StatCard 
      title="Pendentes" 
      :value="stats.submitted.toString()" 
      description="Aguardando análise" 
      icon="⏳"
      color="yellow"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import StatCard from './StatCard.vue'

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
</script>