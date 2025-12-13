<template>
  <nav class="py-4">
    <!-- Navigation Label -->
    <div class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide">
      Navegação
    </div>

    <!-- Dashboard -->
    <MenuItem
      :active="false"
      :icon="HomeIcon"
      :text="'Dashboard'"
      @click="() => emitItem('dashboard')"
    />

    <template v-if="role === 'manager'">
      <!-- Gestão de Projectos -->
      <div
        class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-3"
      >
        Gestão de Projectos
      </div>

      <!-- Projectos -->
      <MenuItem
        :active="false"
        :icon="BriefcaseIcon"
        :text="'Projectos'"
        @click="() => emitItem('projectos')"
      />

      <!-- Técnicos -->
      <MenuItem
        :active="false"
        :icon="UserGroupIcon"
        :text="'Técnicos'"
        @click="() => emitItem('tecnicos')"
      />

      <!-- Funcionários -->
      <MenuItem
        :active="$page.url.startsWith('/gestor/technicians')"
        :icon="UserGroupIcon"
        :text="'Funcionários'"
        href="/gestor/technicians"
      />

      <!-- Relatórios Section -->
      <div
        class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-3"
      >
        Relatórios
      </div>

      <!-- Estatísticas -->
      <MenuItem
        :active="false"
        :icon="ChartBarIcon"
        :text="'Estatísticas'"
        @click="() => emitItem('estatisticas')"
      />

      <!-- Indicadores -->
      <MenuItem
        :active="$page.url.startsWith('/gestor/estatisticas')"
        :icon="ChartBarIcon"
        :text="'Indicadores'"
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

      <!-- Indicadores -->
      <MenuItem
        :active="$page.url.startsWith('/gestor/estatisticas')"
        :icon="ChartBarIcon"
        :text="'Indicadores'"
        href="/gestor/estatisticas"
      />

      <!-- Funcionários -->
      <MenuItem
        :active="$page.url.startsWith('/gestor/technicians')"
        :icon="UserGroupIcon"
        :text="'Funcionários'"
        href="/gestor/technicians"
      />
    </template>

    <template v-if="role === 'pca'">
      <!-- Projectos -->
      <MenuItem
        :active="false"
        :icon="BriefcaseIcon"
        :text="'Projectos'"
        @click="() => emitItem('projectos')"
      />

      <!-- Relatórios Section -->
      <div
        class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-3"
      >
        Relatórios
      </div>

      <!-- Estatísticas -->
      <MenuItem
        :active="false"
        :icon="ChartBarIcon"
        :text="'Estatísticas'"
        @click="() => emitItem('estatisticas')"
      />

      <!-- Gestão de Usuários -->
      <MenuItem
        :active="false"
        :icon="UsersIcon"
        :text="'Usuários'"
        @click="() => emitItem('usuarios')"
      />
    </template>

    <template v-if="role === 'admin'">
      <!-- Gestão do Sistema -->
      <div
        class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-3"
      >
        Gestão do Sistema
      </div>

      <!-- Utilizadores -->
      <MenuItem
        :active="false"
        :icon="UsersIcon"
        :text="'Utilizadores'"
        @click="() => navigateToAdminUsers()"
      />

      <!-- Departamentos -->
      <MenuItem
        :active="false"
        :icon="BuildingOfficeIcon"
        :text="'Departamentos'"
        @click="() => navigateToAdminDepartments()"
      />

      <!-- Projectos -->
      <MenuItem
        :active="false"
        :icon="BriefcaseIcon"
        :text="'Projectos'"
        @click="() => navigateToAdminProjects()"
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
