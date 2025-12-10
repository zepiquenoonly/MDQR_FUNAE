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

    <!-- Admin -->
    <template v-if="role === 'Admin'">
      <div class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-3">Administração</div>

      <MenuItem
        :active="activePanel === 'admin-users'"
        :icon="UsersIcon"
        :text="'Gestão de Utilizadores'"
        @click="() => navigateToAdminUsers()"
      />
      <MenuItem
        :active="activePanel === 'admin-projects'"
        :icon="BriefcaseIcon"
        :text="'Gestão de Projectos'"
        @click="() => navigateToAdminProjects()"
      />
      <MenuItem
        :active="activePanel === 'admin-departments'"
        :icon="BuildingOfficeIcon"
        :text="'Gestão de Departamentos'"
        @click="() => navigateToAdminDepartments()"
      />
      <MenuItem
        :active="activePanel === 'admin-roles'"
        :icon="KeyIcon"
        :text="'Gestão de Funções e Permissões'"
        @click="() => navigateToAdminRoles()"
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
  UsersIcon,
  BuildingOfficeIcon,
  KeyIcon
} from '@heroicons/vue/24/outline'
import { useDashboard } from '@/Composables/useDashboard'
import { useNavigation } from '@/Composables/useNavigation'
import MenuItem from './MenuItem.vue'
import MenuDropdown from './MenuDropdown.vue'

const props = defineProps({
  role: {
    type: String,
    default: 'utente'
  }
})

const emit = defineEmits(['item-clicked'])

// Usar composables centralizados
const { activePanel, setActivePanel } = useDashboard()
const { 
  navigateToProfile, 
  navigateToTracking, 
  logout,
  navigateToAdminUsers,
  navigateToAdminProjects,
  navigateToAdminDepartments,
  navigateToAdminRoles
} = useNavigation({ role: props.role })

const emitItem = (panel) => {
  console.log('MenuSection - emit item:', panel)
  setActivePanel(panel)
  emit('item-clicked', panel)
}

const handleLogout = () => {
  logout()
}
</script>