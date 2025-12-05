<template>
  <nav class="py-4 overflow-hidden">
    <!-- Menu PCA -->
    <template v-if="userRole === 'PCA'">
      <div
        :class="[
          'px-5 py-4 text-xs text-white font-semibold uppercase tracking-wide transition-opacity duration-300',
          isCollapsed ? 'opacity-0' : 'opacity-100',
        ]"
      >
        Visão Executiva
      </div>

      <MenuItem
        :active="$page.url === '/pca/dashboard'"
        :icon="ChartBarIcon"
        :text="'Dashboard'"
        :is-collapsed="isCollapsed"
        :is-mobile="isMobile"
        href="/pca/dashboard"
      />

      <MenuItem
        :active="$page.url === '/track'"
        :icon="MagnifyingGlassIcon"
        :text="'Acompanhamento'"
        :is-collapsed="isCollapsed"
        :is-mobile="isMobile"
        href="/track"
      />
    </template>

    <!-- Menu Gestor -->
    <template v-else-if="userRole === 'Gestor'">
      <div
        :class="[
          'px-5 py-4 text-xs text-white font-semibold uppercase tracking-wide transition-opacity duration-300',
          isCollapsed ? 'opacity-0' : 'opacity-100',
        ]"
      >
        Gestão de Casos
      </div>

      <MenuItem
        :active="$page.url === '/gestor/dashboard' && !$page.url.includes('panel=')"
        :icon="HomeIcon"
        :text="'Home'"
        :is-collapsed="isCollapsed"
        :is-mobile="isMobile"
        href="/gestor/dashboard"
      />

      <MenuItem
        :active="$page.url.includes('panel=projectos')"
        :icon="FolderIcon"
        :text="'Ver Projectos'"
        :is-collapsed="isCollapsed"
        :is-mobile="isMobile"
        href="/gestor/dashboard?panel=projectos"
      />

      <MenuItem
        :active="$page.url.includes('panel=tecnicos')"
        :icon="UserGroupIcon"
        :text="'Ver Técnicos'"
        :is-collapsed="isCollapsed"
        :is-mobile="isMobile"
        href="/gestor/dashboard?panel=tecnicos"
      />
    </template>

    <!-- Menu Técnico -->
    <template v-else-if="userRole === 'Técnico'">
      <div
        :class="[
          'px-5 py-4 text-xs text-white font-semibold uppercase tracking-wide transition-opacity duration-300',
          isCollapsed ? 'opacity-0' : 'opacity-100',
        ]"
      >
        Minhas Tarefas
      </div>

      <MenuItem
        :active="$page.url === '/tecnico/dashboard'"
        :icon="HomeIcon"
        :text="'Dashboard'"
        :is-collapsed="isCollapsed"
        :is-mobile="isMobile"
        href="/tecnico/dashboard"
      />

      <MenuItem
        :active="$page.url === '/track'"
        :icon="MagnifyingGlassIcon"
        :text="'Acompanhamento'"
        :is-collapsed="isCollapsed"
        :is-mobile="isMobile"
        href="/track"
      />
    </template>

    <!-- Conta Section (comum para todos) -->
    <div
      :class="[
        'px-5 py-4 text-xs text-white font-semibold uppercase tracking-wide transition-opacity duration-300 mt-4',
        isCollapsed ? 'opacity-0' : 'opacity-100',
      ]"
    >
      Conta
    </div>

    <!-- Meu Perfil -->
    <MenuItem
      :active="$page.url.startsWith('/profile')"
      :icon="UserCircleIcon"
      :text="'Meu Perfil'"
      :is-collapsed="isCollapsed"
      :is-mobile="isMobile"
      href="/profile"
    />

    <!-- Sair -->
    <MenuItem
      :active="false"
      :icon="ArrowRightOnRectangleIcon"
      :text="'Sair'"
      :is-collapsed="isCollapsed"
      :is-mobile="isMobile"
      @click="handleLogout"
    />
  </nav>
</template>

<script setup>
import { computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import {
  HomeIcon,
  ChartBarIcon,
  FolderIcon,
  UserCircleIcon,
  UserGroupIcon,
  MagnifyingGlassIcon,
  ArrowRightOnRectangleIcon,
} from "@heroicons/vue/24/outline";
import { useDropdownManager } from "./Composables/useDropdownManager.js";
import MenuItem from "./MenuItem.vue";

const props = defineProps({
  isCollapsed: Boolean,
  isMobile: {
    type: Boolean,
    default: false,
  },
  user: {
    type: Object,
    required: true,
  },
  stats: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(["item-clicked"]);

// Inicializar o gerenciador de dropdowns
const dropdownManager = useDropdownManager();
const page = usePage();

// Determinar role do usuário
const userRole = computed(() => {
  return props.user?.roles?.[0]?.name || props.user?.role || "Gestor";
});

const handleLogout = () => {
  router.post("/logout");
};
</script>
