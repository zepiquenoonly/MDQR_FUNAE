<template>
  <div class="flex items-center gap-2 mb-8 text-sm text-gray-600">
    <span>Página Inicial</span>
    <span class="text-gray-400">→</span>
    <span class="bg-brand text-white px-3 py-1 rounded-lg text-xs font-medium">
      {{ currentPage }}
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useDashboard } from '@/composables/useDashboard'

const { activePanel, activeDropdown } = useDashboard()

const currentPage = computed(() => {
  const pages = {
    'dashboard': 'Dashboard',
    'projectos': 'Projectos',
    'mdqr': 'MDQR',
    'sugestoes': 'Minhas Sugestões',
    'queixas': 'Minhas Queixas',
    'reclamacoes': 'Minhas Reclamações'
  }

  // Se houver um dropdown ativo dentro do MDQR, mostrar ele
  if (activePanel.value === 'mdqr' && activeDropdown.value) {
    return pages[activeDropdown.value] || 'MDQR'
  }

  return pages[activePanel.value] || 'Dashboard'
})
</script>