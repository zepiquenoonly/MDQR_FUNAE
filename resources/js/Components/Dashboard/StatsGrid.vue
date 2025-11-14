<template>
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
    <StatCard
      title="Sugestões"
      value="40"
      description="Sugestões recebidas"
    />
    <StatCard
      title="Queixas"
      value="30"
      description="Queixas recebidas"
    />
    <StatCard
      title="Reclamações"
      value="30"
      description="Reclamações recebidas"
    />
    <StatCard
      title="Total"
      value="100"
      description="50% recebidas"
      total-style="true"
    />
  </div>
</template>

<script setup>
import StatCard from './StatCard.vue'
</script>