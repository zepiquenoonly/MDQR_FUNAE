<!-- UnifiedMenuSection.vue -->
<template>
  <nav class="py-4">
    <!-- Navigation Label -->
    <div class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide">
      Navegação
    </div>

    <!-- Dashboards -->
    <MenuItem
      v-if="role === 'admin'"
      :active="$page.url.startsWith('/admin/dashboard')"
      :icon="HomeIcon"
      :text="'Dashboard'"
      href="/admin/dashboard"
    />
    <MenuItem
      v-if="role === 'director'"
      :active="$page.url.startsWith('/director/dashboard')"
      :icon="HomeIcon"
      :text="'Dashboard'"
      href="/director/dashboard"
    />
    <MenuItem
      v-if="role === 'manager'"
      :active="$page.url.startsWith('/gestor/dashboard')"
      :icon="HomeIcon"
      :text="'Dashboard'"
      href="/gestor/dashboard"
    />
    <MenuItem
      v-if="role === 'pca'"
      :active="$page.url.startsWith('/pca/dashboard')"
      :icon="HomeIcon"
      :text="'Dashboard'"
      href="/pca/dashboard"
    />
    <MenuItem
      v-if="role === 'technician'"
      :active="$page.url.startsWith('/tecnico/dashboard')"
      :icon="HomeIcon"
      :text="'Dashboard'"
      href="/tecnico/dashboard"
    />
    <MenuItem
      v-if="role === 'utente'"
      :active="$page.url.startsWith('/utente/dashboard')"
      :icon="HomeIcon"
      :text="'Dashboard'"
      href="/utente/dashboard"
    />

    <MenuItem
      v-if="role === 'utente'"
      :icon="MagnifyingGlassIcon"
      :text="'Acompanhar submissão'"
      href="/track"
    />

    <template v-if="role === 'manager'">
      <!-- Técnicos -->
      <MenuItem
        :active="$page.url.startsWith('/gestor/technicians')"
        :icon="UserGroupIcon"
        :text="'Técnicos'"
        href="/gestor/technicians"
      />

      <MenuItem
        v-if="permissions.canManageProjects"
        :active="$page.url.startsWith(projectsRoute)"
        :icon="BriefcaseIcon"
        :text="'Projectos'"
        :href="projectsRoute"
      />

      <!-- Estatísticas -->
      <MenuItem
        :active="$page.url.startsWith('/gestor/estatisticas')"
        :icon="ChartBarIcon"
        :text="'Estatísticas'"
        :href="'/gestor/indicators'"
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

      <!-- <MenuItem
        v-if="permissions.canManageProjects"
        :active="$page.url.startsWith(projectsRoute)"
        :icon="BriefcaseIcon"
        :text="'Projectos'"
        :href="projectsRoute"
      /> -->

      <!-- Estatísticas -->
      <MenuItem
        :active="$page.url.startsWith('/director/indicators')"
        :icon="ChartBarIcon"
        :text="'Indicadores'"
        :href="'/director/estatisticas'"
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
      <!-- <MenuItem
        :active="$page.url.startsWith('/gestor/estatisticas')"
        :icon="ChartBarIcon"
        :text="'Estatísticas'"
        href="/gestor/estatisticas"
      /> -->
    </template>

    <template v-if="role === 'admin'">
      <!-- Usuários -->
      <MenuItem
        :active="$page.url === '/admin/users'"
        :icon="UsersIcon"
        :text="'Usuários'"
        @click="() => navigateToAdminUsers()"
      />

      <!-- Departamentos -->
      <MenuItem
        :active="$page.url === '/admin/departments'"
        :icon="BuildingOfficeIcon"
        :text="'Departamentos'"
        @click="() => navigateToAdminDepartments()"
      />

      <!-- Projectos -->
      <MenuItem
        :active="$page.url === '/admin/projects'"
        :icon="BriefcaseIcon"
        :text="'Projectos'"
        @click="() => navigateToAdminProjects()"
      />

      <!-- Estatísticas -->
      <!-- <MenuItem
        :active="$page.url.startsWith('/gestor/estatisticas')"
        :icon="ChartBarIcon"
        :text="'Estatísticas'"
        href="/gestor/estatisticas"
      /> -->
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
  BuildingOfficeIcon,
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

const navigateToAdminUsers = () => {
  router.visit("/admin/users");
};

const navigateToAdminDepartments = () => {
  router.visit("/admin/departments");
};

const navigateToAdminProjects = () => {
  router.visit("/admin/projects");
};

const handleLogout = () => {
  router.post("/logout");
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
