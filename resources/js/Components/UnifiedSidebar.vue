<!-- UnifiedSidebar.vue -->
<template>
  <aside
    class="glass rounded-2xl h-full flex flex-col overflow-hidden shadow-glass m-3 backdrop-blur-xl border border-white/40 transition-all duration-300 hover:shadow-2xl"
  >
    <!-- Brand/Logo Section -->
    <div
      class="p-4 sm:p-6 flex-shrink-0 overflow-hidden bg-gradient-to-br from-primary-500/20 via-orange-500/15 to-primary-500/10 rounded-t-2xl relative border-b border-white/20"
    >
      <!-- Decorative elements -->
      <div
        class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-orange-500/10 to-transparent rounded-full blur-2xl"
      ></div>
      <div
        class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-primary-500/10 to-transparent rounded-full blur-2xl"
      ></div>

      <div class="flex items-center space-x-3 min-w-0 relative z-10">
        <!-- Botão de fechar para mobile -->
        <button
          v-if="isMobile"
          @click="$emit('toggle-sidebar')"
          class="p-2 flex items-center justify-center hover:bg-white/30 transition-all flex-shrink-0 rounded-xl text-primary-700 hover:rotate-90 duration-300 backdrop-blur-sm"
        >
          <XMarkIcon class="w-6 h-6" />
        </button>

        <!-- Logo -->
        <div class="flex justify-center items-center w-full">
          <div class="w-28 h-28 flex items-center justify-center p-1">
            <img
              src="/images/Logotipo-scaled.png"
              alt="FUNAE"
              class="w-full h-full object-contain filter drop-shadow-lg"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Title Section -->
    <!-- <div class="p-4 flex-shrink-0 bg-gradient-to-br from-primary-50/50 to-orange-50/30 border-b border-white/20">
      <div class="text-center">
        <h1 class="font-bold text-lg text-gray-900 drop-shadow-sm">Dashboard</h1>
        <p class="text-primary-700 text-sm font-medium">{{ getRoleTitle() }}</p>
      </div>
    </div> -->

    <!-- Menu Items -->
    <div
      class="flex-1 overflow-y-auto overflow-x-hidden scrollbar-thin scrollbar-thumb-primary-300 scrollbar-track-transparent hover:scrollbar-thumb-primary-400 transition-colors"
    >
      <UnifiedMenuSection @item-clicked="handleMenuItemClick" />
    </div>
  </aside>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { XMarkIcon } from "@heroicons/vue/24/outline";
import UnifiedMenuSection from "./UnifiedMenuSection.vue";
import { useAuth } from "@/Composables/useAuth";

const props = defineProps({
  isMobile: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["toggle-sidebar", "change-view"]);

// Usar useAuth para obter informações do usuário
const { user, role, roleLabel, permissions, isAuthenticated } = useAuth();

// Detectar se é mobile
const isMobile = ref(props.isMobile);

const checkMobile = () => {
  isMobile.value = window.innerWidth < 640;
};

const getRoleTitle = () => {
  const titles = {
    technician: "Painel do Técnico",
    manager: "Painel do Gestor",
    pca: "Painel do PCA",
    director: "Painel do Director",
    admin: "Painel do Administrador",
    utente: "Painel do Utente",
  };
  return titles[props.role] || "Painel";
};

const handleMenuItemClick = (item) => {
  emit("change-view", item);
};

onMounted(() => {
  checkMobile();
  window.addEventListener("resize", checkMobile);
});

onUnmounted(() => {
  window.removeEventListener("resize", checkMobile);
});
</script>

<style scoped>
.scrollbar-thin::-webkit-scrollbar {
  width: 4px;
}

.scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
  background-color: rgba(249, 115, 22, 0.3);
  border-radius: 20px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
  background-color: rgba(249, 115, 22, 0.5);
}
</style>
