<template>
  <nav class="py-4">
    <!-- Navigation Label -->
    <div class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide">
      Navegação
    </div>

    <!-- Dashboards -->
    <MenuItem v-if="role === 'admin'"
      :active="$page.url.startsWith('/admin/dashboard')"
      :icon="HomeIcon"
      :text="'Dashboard'"
      href="/admin/dashboard"
    />
    <MenuItem v-if="role === 'director'"
      :active="$page.url.startsWith('/director/dashboard')"
      :icon="HomeIcon"
      :text="'Dashboard'"
      href="/director/dashboard"
    />
    <MenuItem v-if="role === 'manager'"
      :active="$page.url.startsWith('/gestor/dashboard')"
      :icon="HomeIcon"
      :text="'Dashboard'"
      href="/gestor/dashboard"
    />
    <MenuItem v-if="role === 'pca'"
      :active="$page.url.startsWith('/pca/dashboard')"
      :icon="HomeIcon"
      :text="'Dashboard'"
      href="/pca/dashboard"
    />
    <MenuItem v-if="role === 'technician'"
      :active="$page.url.startsWith('/tecnico/dashboard')"
      :icon="HomeIcon"
      :text="'Dashboard'"
      href="/tecnico/dashboard"
    />
    <MenuItem v-if="role === 'utente'"
      :active="$page.url.startsWith('/utente/dashboard')"
      :icon="HomeIcon"
      :text="'Dashboard'"
      href="/utente/dashboard"
    />

    <MenuItem v-if="role === 'utente'"
      :icon="HomeIcon"
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

      <!-- Estatísticas -->
      <MenuItem
        :active="$page.url.startsWith('/gestor/estatisticas')"
        :icon="ChartBarIcon"
        :text="'Estatísticas'"
        href="/gestor/estatisticas"
      />
    </template>

    <template v-if="role === 'director'">
      <!-- Submissões -->
      <MenuItem
        :active="$page.url.startsWith('/director/complaints-overview')"
        :icon="ClipboardDocumentListIcon"
        :text="'Submissões'"
        href="/director/complaints-overview"
      />

      <!-- Estatísticas -->
      <MenuItem
        :active="$page.url.startsWith('/gestor/estatisticas')"
        :icon="ChartBarIcon"
        :text="'Estatísticas'"
        href="/director/indicators"
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
  ClipboardDocumentListIcon,
  UsersIcon,
  BuildingOfficeIcon,
} from "@heroicons/vue/24/outline";
import { router } from "@inertiajs/vue3";
import MenuItem from "@/Components/UtenteDashboard/MenuItem.vue";
import MenuDropdown from "@/Components/UtenteDashboard/MenuDropdown.vue";

const props = defineProps({
  role: {
    type: String,
    default: "technician",
  },
});

const emit = defineEmits(["item-clicked"]);

const emitItem = (item) => {
  emit("item-clicked", item);
};

const navigateToProfile = () => {
  router.visit("/profile");
};

const navigateToTracking = () => {
  router.visit("/track");
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
</script>
