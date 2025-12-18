<!-- UnifiedLayout.vue - Apenas a parte relevante -->
<template>
  <div
    class="relative flex min-h-screen overflow-hidden"
    style="background: url('/background.min.svg') center/cover fixed no-repeat; zoom: 90%"
  >
    <!-- Sidebar Desktop -->
    <div class="fixed top-0 left-0 z-30 hidden w-64 h-full sm:block">
      <Sidebar @change-view="$emit('change-view', $event)" :loading="loading" />
    </div>

    <!-- Sidebar Mobile -->
    <div v-if="sidebarOpen && isMobile" class="fixed inset-0 z-50 sm:hidden">
      <!-- Overlay escuro -->
      <div
        class="absolute inset-0 bg-black/50 backdrop-blur-sm"
        @click="closeSidebar"
      ></div>
      <!-- Sidebar -->
      <div class="absolute top-0 left-0 h-full shadow-2xl w-72 animate-slide-in-left">
        <Sidebar
          :is-mobile="true"
          @toggle-sidebar="closeSidebar"
          @change-view="handleMobileMenuClick"
          :loading="loading"
        />
      </div>
    </div>

    <!-- Main Content -->
    <div class="flex flex-col flex-1 w-full min-w-0 sm:ml-64">
      <!-- Header -->
      <Header :user="safeUser" @toggle-sidebar="openSidebar" class="flex-shrink-0" />

      <!-- Loading Spinner Global -->
      <div
        v-if="loading"
        class="fixed inset-0 z-50 flex items-center justify-center bg-white/75 backdrop-blur-sm"
      >
        <div class="text-center">
          <div
            class="w-12 h-12 mx-auto border-b-2 rounded-full animate-spin border-primary-500"
          ></div>
          <p class="mt-4 text-sm font-medium text-gray-600">A carregar...</p>
        </div>
      </div>

      <!-- Page Content -->
      <main class="flex-1 p-6 overflow-auto">
        <slot />
      </main>
    </div>

    <!-- Notification Container -->
    <NotificationContainer />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import Sidebar from "@/Components/UnifiedSidebar.vue";
import Header from "@/Components/UnifiedHeader.vue";
import NotificationContainer from "@/Components/NotificationContainer.vue";
import { useAuth } from "@/Composables/useAuth";
import { useDashboard } from "@/Composables/useDashboard";

// Remover props user e role
const props = defineProps({
  stats: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(["change-view"]);

// Usar composable de autenticação
const { user: safeUser, role: detectedRole, roleLabel, permissions } = useAuth({
  // Opcional: ativar debug durante desenvolvimento
  debug: import.meta.env.DEV,
});

// Para debug
if (import.meta.env.DEV) {
  onMounted(() => {
    console.log("🔄 UnifiedLayout montado");
    console.log("👤 Usuário:", safeUser.value);
    console.log("🎭 Role:", detectedRole.value);
    console.log("🏷️ Role Label:", roleLabel.value);
    console.log("🔑 Permissões:", permissions.value);
  });
}

const { syncWithUrl } = useDashboard();

const sidebarOpen = ref(false);
const loading = ref(false);
const isMobile = ref(false);

const openSidebar = () => {
  sidebarOpen.value = true;
};

const closeSidebar = () => {
  sidebarOpen.value = false;
};

const handleMobileMenuClick = (view) => {
  emit("change-view", view);
  closeSidebar();
};

// Listener para loading state do Inertia
router.on("start", () => {
  loading.value = true;
});

router.on("finish", () => {
  loading.value = false;
});

const checkMobile = () => {
  isMobile.value = window.innerWidth < 640;
  if (!isMobile.value) {
    sidebarOpen.value = false;
  }
};

onMounted(() => {
  checkMobile();
  window.addEventListener("resize", checkMobile);

  setTimeout(() => {
    syncWithUrl();
  }, 100);
});

onUnmounted(() => {
  window.removeEventListener("resize", checkMobile);
});
</script>
