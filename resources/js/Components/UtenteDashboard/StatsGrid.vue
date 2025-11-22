<template>
  <div class="text-2xl font-semibold text-gray-800 mb-2">
    <h1>Visão Geral</h1>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
    <StatCard title="Sugestões" value="40" description="Sugestões submetidas" />
    <StatCard title="Queixas" value="30" description="Queixas submetidas" />
    <StatCard title="Reclamações" value="30" description="Reclamações subetidas" />
    <StatCard title="Total" value="100" description="50% submetidas" total-style="true" />
  </div>
</template>

<script setup>
import StatCard from './StatCard.vue'
</script>