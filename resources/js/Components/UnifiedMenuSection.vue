<!-- UnifiedMenuSection.vue -->
<template>
  <nav class="py-4">
    <!-- Navigation Label -->
    <div class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide">
      Navegação
    </div>

    <!-- Dashboard -->
    <MenuItem
      :active="$page.url === dashboardRoute"
      :icon="HomeIcon"
      :text="'Dashboard'"
      :href="dashboardRoute"
    />

    <!-- Projectos - Mostrar apenas se tiver permissão -->
    <MenuItem
      v-if="permissions.canManageProjects"
      :active="$page.url.startsWith(projectsRoute)"
      :icon="BriefcaseIcon"
      :text="'Projectos'"
      :href="projectsRoute"
    />

    <!-- Role-specific sections -->
    <template v-if="role === 'technician' || role === 'utente'">
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
          {
            id: 'complaints',
            text: 'Reclamação',
            icon: ExclamationCircleIcon,
            href: '/reclamacoes/nova?type=complaint',
          },
          {
            id: 'grievances',
            text: 'Queixa',
            icon: ExclamationTriangleIcon,
            href: '/reclamacoes/nova?type=grievance',
          },
          {
            id: 'suggestions',
            text: 'Sugestão',
            icon: LightBulbIcon,
            href: '/reclamacoes/nova?type=suggestion',
          },
        ]"
        @item-clicked="navigateToRoute"
      />

      <!-- Acompanhamento -->
      <MenuItem
        :active="$page.url.startsWith('/reclamacoes/acompanhar')"
        :icon="MagnifyingGlassIcon"
        :text="'Acompanhamento'"
        :href="'/reclamacoes/acompanhar'"
      />
    </template>

    <template v-if="role === 'manager'">
      <!-- Técnicos -->
      <MenuItem
        :active="$page.url.startsWith('/gestor/technicians')"
        :icon="UserGroupIcon"
        :text="'Técnicos'"
        :href="'/gestor/technicians'"
      />

      <!-- Estatísticas -->
      <div
        class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-3"
        v-if="permissions.canViewStatistics"
      >
        Relatórios
      </div>
      <MenuItem
        v-if="permissions.canViewStatistics"
        :active="$page.url.startsWith('/gestor/estatisticas')"
        :icon="ChartBarIcon"
        :text="'Estatísticas'"
        :href="'/gestor/estatisticas'"
      />
    </template>

    <template v-if="role === 'director'">
      <!-- Submissões -->
      <MenuItem
        :active="$page.url.startsWith('/director/complaints-overview')"
        :icon="ClipboardDocumentListIcon"
        :text="'Submissões'"
        :href="'/director/complaints-overview'"
      />

      <!-- Indicadores -->
      <MenuItem
        :active="$page.url.startsWith('/director/indicators')"
        :icon="ChartBarIcon"
        :text="'Indicadores'"
        :href="'/director/indicators'"
      />

      <div
        class="px-5 py-4 text-xs text-black font-semibold uppercase tracking-wide mt-4"
        v-if="permissions.canManageUsers"
      >
        Gestão do Departamento
      </div>

      <!-- Funcionários -->
      <MenuItem
        v-if="permissions.canManageUsers"
        :active="
          $page.url.startsWith('/director/team') ||
          $page.url.startsWith('/director/managers')
        "
        :icon="UserGroupIcon"
        :text="'Funcionários'"
        :href="'/director/managers'"
      />
    </template>

    <template v-if="role === 'pca'">
      <!-- Estatísticas -->
      <div
        class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-3"
        v-if="permissions.canViewStatistics"
      >
        Relatórios
      </div>
      <MenuItem
        v-if="permissions.canViewStatistics"
        :active="$page.url.startsWith('/pca/statistics')"
        :icon="ChartBarIcon"
        :text="'Estatísticas'"
        :href="'/pca/statistics'"
      />

      <!-- Gestão de Usuários -->
      <MenuItem
        v-if="permissions.canManageUsers"
        :active="$page.url.startsWith('/pca/users')"
        :icon="UsersIcon"
        :text="'Usuários'"
        :href="'/pca/users'"
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
      :active="$page.url.startsWith('/profile')"
      :icon="UserCircleIcon"
      :text="'Meu Perfil'"
      :href="'/profile'"
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
  ClipboardDocumentListIcon,
  UsersIcon,
} from "@heroicons/vue/24/outline";
import { router, usePage } from "@inertiajs/vue3";
import { useAuth } from "@/Composables/useAuth";
import MenuItem from "@/Components/UtenteDashboard/MenuItem.vue";
import MenuDropdown from "@/Components/UtenteDashboard/MenuDropdown.vue";
import { computed } from "vue";

const page = usePage();
const csrfToken = computed(() => {
  const metaTag = document.querySelector('meta[name="csrf-token"]');
  return metaTag ? metaTag.getAttribute("content") : null;
});

// Usar useAuth para obter informações do usuário
const { role, roleLabel, permissions, isAuthenticated, user } = useAuth();

// Computed properties para rotas
const dashboardRoute = computed(() => {
  switch (role.value) {
    case "director":
      return "/director/dashboard";
    case "manager":
      return "/gestor/dashboard";
    case "pca":
      return "/pca/dashboard";
    case "technician":
      return "/technician/dashboard";
    case "utente":
      return "/utente/dashboard";
    default:
      return "/dashboard";
  }
});

const projectsRoute = computed(() => {
  switch (role.value) {
    case "director":
      return "/director/projects";
    case "manager":
      return "/gestor/projects";
    case "pca":
      return "/pca/projects";
    default:
      return null;
  }
});

// Função para navegar a partir de itens do dropdown
const navigateToRoute = (item) => {
  if (item.href) {
    router.visit(item.href);
  }
};

// Logout
const handleLogout = () => {
  router.post(
    "/logout",
    {},
    {
      headers: {
        "X-CSRF-TOKEN": csrfToken.value,
      },
    }
  );
};

// Debug info
if (import.meta.env.DEV) {
  console.log("🔍 UnifiedMenuSection montado", {
    role: role.value,
    roleLabel: roleLabel.value,
    permissions: permissions.value,
    isAuthenticated: isAuthenticated.value,
    dashboardRoute: dashboardRoute.value,
    projectsRoute: projectsRoute.value,
  });
}
</script>
