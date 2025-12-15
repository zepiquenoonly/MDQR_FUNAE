<template>
  <nav class="py-4">
    <!-- Navigation Label -->
    <div class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide">
      Navegação
    </div>

    <!-- Dashboard -->
    <MenuItem
      :active="isDashboardActive"
      :icon="HomeIcon"
      :text="'Dashboard'"
      @click="() => navigateToDashboardHandler()"
    />

    <!-- Role-specific sections -->
    <!-- Utente (default) -->
    <template v-if="!role || role === 'utente'">
      <!-- Projectos -->
      <MenuItem
        :active="isProjectosActive"
        :icon="BriefcaseIcon"
        :text="'Projectos'"
        @click="() => navigateTo('utente.projects')"
      />

      <!-- MDQR Section -->
      <div
        class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-3"
      >
        MDQR
      </div>

      <!-- Submeter Reclamação/Queixa/Sugestão -->
      <MenuDropdown
        :icon="DocumentPlusIcon"
        :text="'Nova Submissão'"
        :items="[
          { id: 'reclamacoes', text: 'Reclamação', icon: ExclamationCircleIcon },
          { id: 'queixas', text: 'Queixa', icon: ExclamationTriangleIcon },
          { id: 'sugestoes', text: 'Sugestão', icon: LightBulbIcon },
        ]"
        @item-clicked="handleNewSubmission"
      />

      <!-- Acompanhamento -->
      <MenuItem
        :active="isTrackingActive"
        :icon="MagnifyingGlassIcon"
        :text="'Acompanhamento'"
        @click="() => navigateToTracking()"
      />
    </template>

    <!-- Técnico -->
    <template v-if="role === 'technician'">
      <!-- MDQR Section -->
      <div
        class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-3"
      >
        MDQR
      </div>

      <!-- Submeter Reclamação/Queixa/Sugestão -->
      <MenuDropdown
        :icon="DocumentPlusIcon"
        :text="'Nova Submissão'"
        :items="[
          { id: 'reclamacoes', text: 'Reclamação', icon: ExclamationCircleIcon },
          { id: 'queixas', text: 'Queixa', icon: ExclamationTriangleIcon },
          { id: 'sugestoes', text: 'Sugestão', icon: LightBulbIcon },
        ]"
        @item-clicked="handleNewSubmission"
      />

      <!-- Acompanhamento -->
      <MenuItem
        :active="isTrackingActive"
        :icon="MagnifyingGlassIcon"
        :text="'Acompanhamento'"
        @click="() => navigateToTracking()"
      />
    </template>

    <!-- Gestor -->
    <template v-if="role === 'manager'">
      <!-- Projectos -->
      <MenuItem
        :active="isManagerProjectsActive"
        :icon="BriefcaseIcon"
        :text="'Projectos'"
        @click="() => navigateTo('manager.projects')"
      />

      <!-- Técnicos -->
      <MenuItem
        :active="isManagerTechniciansActive"
        :icon="UserGroupIcon"
        :text="'Técnicos'"
        @click="() => navigateTo('manager.technicians')"
      />

      <!-- Estatísticas -->
      <div
        class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-3"
      >
        Relatórios
      </div>
      <MenuItem
        :active="isManagerEstatisticasActive"
        :icon="ChartBarIcon"
        :text="'Estatísticas'"
        @click="() => navigateTo('manager.estatisticas')"
      />
    </template>

    <!-- Director -->
    <template v-if="role === 'director'">
      <!-- Submissões -->
      <MenuItem
        :active="isDirectorComplaintsActive"
        :icon="ClipboardDocumentListIcon"
        :text="'Submissões'"
        @click="() => navigateTo('director.complaints-overview')"
      />

      <!-- Indicadores -->
      <MenuItem
        :active="isDirectorEstatisticasActive"
        :icon="ChartBarIcon"
        :text="'Indicadores'"
        @click="() => navigateTo('director.estatisticas')"
      />

      <div
        :class="[
          'px-5 py-4 text-xs text-black font-semibold uppercase tracking-wide transition-opacity duration-300 mt-4',
          isCollapsed ? 'opacity-0' : 'opacity-100',
        ]"
      >
        Gestão do Departamento
      </div>

      <!-- Funcionários -->
      <MenuItem
        :active="isDirectorTechniciansActive"
        :icon="UserGroupIcon"
        :text="'Funcionários'"
        @click="() => navigateTo('director.technicians')"
      />
    </template>

    <!-- PCA -->
    <template v-if="role === 'pca'">
      <!-- Projectos -->
      <MenuItem
        :active="isProjectosActive"
        :icon="BriefcaseIcon"
        :text="'Projectos'"
        @click="() => navigateTo('pca.projects')"
      />

      <!-- Estatísticas -->
      <div
        class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-3"
      >
        Relatórios
      </div>
      <MenuItem
        :active="isPcaEstatisticasActive"
        :icon="ChartBarIcon"
        :text="'Estatísticas'"
        @click="() => navigateTo('pca.estatisticas')"
      />

      <!-- Gestão de Usuários -->
      <MenuItem
        :active="isUsuariosActive"
        :icon="UsersIcon"
        :text="'Usuários'"
        @click="() => navigateTo('pca.usuarios')"
      />
    </template>

    <!-- Admin -->
    <template v-if="role === 'Admin'">
      <div class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-3">Administração</div>

      <MenuItem
        :active="activePanel === 'admin-users'"
        :icon="UsersIcon"
        :text="'Gestão de Usuários'"
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
    <div
      class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-4"
    >
      Conta
    </div>

    <!-- Meu Perfil -->
    <MenuItem
      :active="isProfileActive"
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
    default: "utente",
  },
});

const emit = defineEmits(["item-clicked"]);

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

// Computed properties para verificar URL ativa
const isDashboardActive = computed(() => {
  return (
    page.url === "/" ||
    page.url.startsWith("/dashboard") ||
    page.url.startsWith("/director/dashboard") ||
    activePanel.value === "dashboard"
  );
});

const isTrackingActive = computed(() => {
  return page.url.startsWith("/track") || activePanel.value === "tracking";
});

const isProfileActive = computed(() => {
  return page.url.startsWith("/profile") || activePanel.value === "profile";
});

const isProjectosActive = computed(() => {
  return page.url.startsWith("/projectos") || activePanel.value === "projectos";
});

const isUsuariosActive = computed(() => {
  return page.url.startsWith("/usuarios") || activePanel.value === "usuarios";
});

// URL checks for Manager
const isManagerProjectsActive = computed(() => {
  return page.url.startsWith("/manager/projects");
});

const isManagerTechniciansActive = computed(() => {
  return (
    page.url.startsWith("/gestor/technicians") ||
    page.url.startsWith("/manager/technicians")
  );
});

const isManagerEstatisticasActive = computed(() => {
  return (
    page.url.startsWith("/gestor/estatisticas") ||
    page.url.startsWith("/manager/estatisticas")
  );
});

// URL checks for Director
const isDirectorComplaintsActive = computed(() => {
  return page.url.startsWith("/director/complaints-overview");
});

const isDirectorEstatisticasActive = computed(() => {
  return (
    page.url.startsWith("/gestor/estatisticas") ||
    page.url.startsWith("/director/estatisticas")
  );
});

const isDirectorTechniciansActive = computed(() => {
  return (
    page.url.startsWith("/gestor/technicians") ||
    page.url.startsWith("/director/technicians")
  );
});

// URL checks for PCA
const isPcaEstatisticasActive = computed(() => {
  return (
    page.url.startsWith("/gestor/estatisticas") ||
    page.url.startsWith("/pca/estatisticas")
  );
});

// Handler para navegação do dashboard
const navigateToDashboardHandler = () => {
  navigateToDashboard();
  emit("item-clicked", "dashboard");
};

// Handler para novas submissões
const handleNewSubmission = (type) => {
  let routeName = "";
  switch (type) {
    case "reclamacoes":
      routeName = "complaints.create";
      break;
    case "queixas":
      routeName = "grievances.create";
      break;
    case "sugestoes":
      routeName = "suggestions.create";
      break;
  }

  if (routeName) {
    navigateTo(routeName);
    emit("item-clicked", type);
  }
};

const handleLogout = () => {
  logout();
};

onMounted(() => {
  console.log("MenuSection - Role recebido:", props.role);
  console.log("MenuSection - URL atual:", page.url);
});
</script>
