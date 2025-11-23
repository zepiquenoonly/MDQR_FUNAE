<template>
  <nav class="py-4 overflow-hidden">
      <!-- Navigation Label -->
      <div :class="[
        'px-5 py-3 text-xs text-white font-semibold uppercase tracking-wide transition-opacity duration-300',
        isCollapsed ? 'opacity-0' : 'opacity-100'
      ]">
        Navegação
      </div>

      <!-- Dashboard -->
      <MenuItem
        :active="activePanel === 'dashboard'"
        :icon="HomeIcon"
        :text="'Dashboard'"
        :is-collapsed="isCollapsed"
        @click="() => emitItem('dashboard')"
      />

      <!-- Acompanhar Reclamação -->
      <div :class="[
        'px-5 py-3 text-xs text-white font-semibold uppercase tracking-wide transition-opacity duration-300 mt-3',
        isCollapsed ? 'opacity-0' : 'opacity-100'
      ]">Reclamações</div>

      <MenuItem
        :active="activePanel === 'reclamacoes'"
        :icon="DocumentTextIcon"
        :text="'Acompanhar Reclamação'"
        :is-collapsed="isCollapsed"
        @click="() => emitItem('reclamacoes')"
      />

      <!-- Conta / Perfil -->
      <div :class="[
        'px-5 py-3 text-xs text-white font-semibold uppercase tracking-wide transition-opacity duration-300 mt-4',
        isCollapsed ? 'opacity-0' : 'opacity-100'
      ]">Conta</div>

      <MenuItem
        :active="activePanel === 'perfil'"
        :icon="UserIcon"
        :text="'Gestão de Perfil'"
        :is-collapsed="isCollapsed"
        @click="() => emitItem('perfil')"
      />
  </nav>
</template>

<script setup>
import {
  HomeIcon,
  DocumentTextIcon,
  UserIcon
} from '@heroicons/vue/24/outline'
import { useDashboardState } from '@/Components/UtenteDashboard/Composables/useDashboardState.js'
import MenuItem from './MenuItem.vue'
import { defineEmits, defineProps } from 'vue'

defineProps({
  isCollapsed: Boolean
})

const emit = defineEmits(['item-clicked'])

const { activePanel, setActivePanel } = useDashboardState()

const emitItem = (panel) => {
  console.log('MenuSection - emit item:', panel)
  setActivePanel(panel)
  emit('item-clicked', panel)
}
</script>