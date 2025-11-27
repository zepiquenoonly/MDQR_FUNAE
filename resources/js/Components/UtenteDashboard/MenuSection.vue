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

      <!-- Projectos -->
      <MenuItem
        :active="activePanel === 'projectos'"
        :icon="BriefcaseIcon"
        :text="'Projectos'"
        :is-collapsed="isCollapsed"
        @click="() => emitItem('projectos')"
      />

      <!-- MDQR Section -->
      <div :class="[
        'px-5 py-3 text-xs text-white font-semibold uppercase tracking-wide transition-opacity duration-300 mt-3',
        isCollapsed ? 'opacity-0' : 'opacity-100'
      ]">MDQR</div>

      <!-- Submeter Reclamação/Queixa/Sugestão -->
      <MenuDropdown
        :icon="DocumentPlusIcon"
        :text="'Nova Submissão'"
        :is-collapsed="isCollapsed"
        :items="[
          { id: 'reclamacoes', text: 'Reclamação', icon: ExclamationCircleIcon },
          { id: 'queixas', text: 'Queixa', icon: ExclamationTriangleIcon },
          { id: 'sugestoes', text: 'Sugestão', icon: LightBulbIcon }
        ]"
        @item-clicked="emitItem"
      />

      <!-- Acompanhamento -->
      <MenuItem
        :active="false"
        :icon="MagnifyingGlassIcon"
        :text="'Acompanhamento'"
        :is-collapsed="isCollapsed"
        @click="() => navigateToTracking()"
      />

      <!-- Conta / Perfil -->
      <div :class="[
        'px-5 py-3 text-xs text-white font-semibold uppercase tracking-wide transition-opacity duration-300 mt-4',
        isCollapsed ? 'opacity-0' : 'opacity-100'
      ]">Conta</div>

      <!-- Meu Perfil -->
      <MenuItem
        :active="false"
        :icon="UserCircleIcon"
        :text="'Meu Perfil'"
        :is-collapsed="isCollapsed"
        @click="() => navigateToProfile()"
      />

      <!-- Logout -->
      <MenuItem
        :active="false"
        :icon="ArrowRightOnRectangleIcon"
        :text="'Sair'"
        :is-collapsed="isCollapsed"
        @click="() => handleLogout()"
      />
  </nav>
</template>

<script setup>
import {
  HomeIcon,
  BriefcaseIcon,
  DocumentPlusIcon,
  ExclamationCircleIcon,
  ExclamationTriangleIcon,
  LightBulbIcon,
  MagnifyingGlassIcon,
  UserCircleIcon,
  ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline'
import { router } from '@inertiajs/vue3'
import { useDashboardState } from '@/Components/UtenteDashboard/Composables/useDashboardState.js'
import MenuItem from './MenuItem.vue'
import MenuDropdown from './MenuDropdown.vue'
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

const navigateToProfile = () => {
  router.visit('/profile')
}

const navigateToTracking = () => {
  router.visit('/track')
}

const handleLogout = () => {
  router.post('/logout')
}
</script>