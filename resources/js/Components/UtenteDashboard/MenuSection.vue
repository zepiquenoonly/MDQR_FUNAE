<template>
  <nav class="py-4">
    <!-- Navigation Label -->
    <div class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide">
      Navegação
    </div>

    <!-- Dashboard -->
    <MenuItem
      :active="activePanel === 'dashboard'"
      :icon="HomeIcon"
      :text="'Dashboard'"
      @click="() => emitItem('dashboard')"
    />

    <!-- Role-specific sections -->
    <!-- Utente (default) -->
    <template v-if="!role || role === 'utente'">
      <!-- Projectos -->
      <MenuItem
        :active="activePanel === 'projectos'"
        :icon="BriefcaseIcon"
        :text="'Projectos'"
        @click="() => emitItem('projectos')"
      />

      <!-- MDQR Section -->
      <div class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-3">MDQR</div>

      <!-- Submeter Reclamação/Queixa/Sugestão -->
      <MenuDropdown
        :icon="DocumentPlusIcon"
        :text="'Nova Submissão'"
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
        @click="() => navigateToTracking()"
      />
    </template>

    <!-- Técnico -->
    <template v-if="role === 'technician'">
      <!-- MDQR Section -->
      <div class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-3">MDQR</div>

      <!-- Submeter Reclamação/Queixa/Sugestão -->
      <MenuDropdown
        :icon="DocumentPlusIcon"
        :text="'Nova Submissão'"
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
        @click="() => navigateToTracking()"
      />
    </template>

    <!-- Gestor -->
    <template v-if="role === 'manager'">
      <!-- Projectos -->
      <MenuItem
        :active="activePanel === 'projectos'"
        :icon="BriefcaseIcon"
        :text="'Projectos'"
        @click="() => emitItem('projectos')"
      />

      <!-- Técnicos -->
      <MenuItem
        :active="activePanel === 'tecnicos'"
        :icon="UserGroupIcon"
        :text="'Técnicos'"
        @click="() => emitItem('tecnicos')"
      />

      <!-- Estatísticas -->
      <div class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-3">Relatórios</div>
      <MenuItem
        :active="activePanel === 'estatisticas'"
        :icon="ChartBarIcon"
        :text="'Estatísticas'"
        @click="() => emitItem('estatisticas')"
      />
    </template>

    <!-- PCA -->
    <template v-if="role === 'pca'">
      <!-- Projectos -->
      <MenuItem
        :active="activePanel === 'projectos'"
        :icon="BriefcaseIcon"
        :text="'Projectos'"
        @click="() => emitItem('projectos')"
      />

      <!-- Estatísticas -->
      <div class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-3">Relatórios</div>
      <MenuItem
        :active="activePanel === 'estatisticas'"
        :icon="ChartBarIcon"
        :text="'Estatísticas'"
        @click="() => emitItem('estatisticas')"
      />

      <!-- Gestão de Usuários -->
      <MenuItem
        :active="activePanel === 'usuarios'"
        :icon="UsersIcon"
        :text="'Usuários'"
        @click="() => emitItem('usuarios')"
      />
    </template>

    <!-- Conta / Perfil -->
    <div class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-4">Conta</div>

    <!-- Meu Perfil -->
    <MenuItem
      :active="false"
      :icon="UserCircleIcon"
      :text="'Meu Perfil'"
      @click="() => navigateToProfile()"
    />

    <!-- Logout -->
    <MenuItem
      :active="false"
      :icon="ArrowRightOnRectangleIcon"
      :text="'Sair'"
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
  ArrowRightOnRectangleIcon,
  UserGroupIcon,
  ChartBarIcon,
  UsersIcon
} from '@heroicons/vue/24/outline'
import { router } from '@inertiajs/vue3'
import { useDashboardState } from '@/Components/UtenteDashboard/Composables/useDashboardState.js'
import MenuItem from './MenuItem.vue'
import MenuDropdown from './MenuDropdown.vue'

const props = defineProps({
  role: {
    type: String,
    default: 'utente'
  }
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