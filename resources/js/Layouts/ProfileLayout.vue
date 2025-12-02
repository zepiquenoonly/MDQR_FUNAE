<template>
  <div class="min-h-screen bg-gray-50 dark:bg-dark-primary flex transition-colors duration-200">
    <!-- Sidebar para desktop - sempre visível e fixa -->
    <div class="hidden sm:block transition-all duration-300 fixed left-0 top-0 h-full z-30"
      :class="sidebarCollapsed ? 'w-16 sm:w-20' : 'w-56 sm:w-64'">
      <ProfileSidebar :user="user" :stats="safeStats" :active-tab="activeTab" :is-collapsed="sidebarCollapsed"
        :show-stats="showStats" @close-sidebar="handleSidebarToggle(true)" @toggle-collapse="handleSidebarToggle" />
    </div>

    <!-- Sidebar para mobile - overlay absoluto que cobre TUDO -->
    <div v-if="!sidebarCollapsed && isMobile" class="sm:hidden fixed inset-0 z-50">
      <!-- Overlay escuro -->
      <div class="absolute inset-0 bg-black bg-opacity-50" @click="handleSidebarToggle(true)"></div>
      <!-- Sidebar que cobre toda a altura incluindo header -->
      <div class="absolute left-0 top-0 h-full w-64 bg-white dark:bg-dark-secondary shadow-xl z-50">
        <ProfileSidebar :user="user" :stats="stats" :active-tab="activeTab" :show-stats="showStats"
          :is-collapsed="false" @close-sidebar="handleSidebarToggle(true)" />
      </div>
    </div>

    <!-- Main Content Area - com margem para a sidebar fixa -->
    <div class="flex-1 flex flex-col min-w-0 w-full transition-all duration-300"
      :class="sidebarCollapsed && !isMobile ? 'sm:ml-16 lg:ml-20' : 'sm:ml-56 lg:ml-64'">
      <!-- Header Fixo no Topo -->
      <ProfileHeader :sidebar-collapsed="sidebarCollapsed" :user="user" @toggle-sidebar="handleSidebarToggle"
        class="flex-shrink-0 sticky top-0 z-40" />

      <!-- Loading Spinner Global -->
      <div v-if="loading"
        class="fixed inset-0 bg-white dark:bg-dark-primary bg-opacity-75 flex items-center justify-center z-50">
        <div class="text-center">
          <div
            class="animate-spin rounded-full h-8 w-8 sm:h-10 sm:w-10 lg:h-12 lg:w-12 border-b-2 border-orange-500 mx-auto">
          </div>
          <p class="text-gray-600 dark:text-gray-400 mt-2 text-xs sm:text-sm">
            A carregar...
          </p>
        </div>
      </div>

      <!-- Page Content Scrollável -->
      <main class="flex-1 overflow-auto">
        <div class="p-3 sm:p-4 lg:p-6">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import ProfileSidebar from "@/Components/Profile/ProfileSidebar.vue";
import ProfileHeader from "@/Components/GestorReclamacoes/Header.vue";

// Props
const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
  stats: {
    type: [Object, null],
    default: () => ({}),
  },
  activeTab: {
    type: String,
    default: "info",
  },
  showStats: {
    type: Boolean,
    default: false,
  },
});

const safeStats = computed(() => props.stats || {});

const sidebarCollapsed = ref(false);
const loading = ref(false);
const isMobile = ref(false);

const checkMobile = () => {
  isMobile.value = window.innerWidth < 640;
  if (isMobile.value) {
    sidebarCollapsed.value = true;
  }
};

function handleSidebarToggle(isCollapsed = null) {
  if (isCollapsed !== null) {
    sidebarCollapsed.value = isCollapsed;
  } else {
    sidebarCollapsed.value = !sidebarCollapsed.value;
  }
}

// Função para fechar o sidebar no mobile
const closeSidebar = () => {
  if (isMobile.value) {
    sidebarCollapsed.value = true;
  }
};

let loadingTimeout = null;

const startLoading = () => {
  loadingTimeout = setTimeout(() => {
    loading.value = true;
  }, 300);
};

const finishLoading = () => {
  if (loadingTimeout) {
    clearTimeout(loadingTimeout);
    loadingTimeout = null;
  }
  loading.value = false;
};

onMounted(() => {
  const removeStartListener = router.on("start", startLoading);
  const removeFinishListener = router.on("finish", finishLoading);

  checkMobile();
  window.addEventListener("resize", checkMobile);
});

// Cleanup
onUnmounted(() => {
  if (loadingTimeout) {
    clearTimeout(loadingTimeout);
  }
  window.removeEventListener("resize", checkMobile);
});
</script>