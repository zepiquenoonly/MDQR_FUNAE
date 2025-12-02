<template>
  <header
    class="bg-white dark:bg-dark-secondary border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-3 sm:py-4 sticky top-0 z-40"
  >
    <div class="flex items-center justify-between">
      <!-- Left Section -->
      <div class="flex items-center gap-2 sm:gap-4">
        <!-- Botão hambúrguer - SEMPRE VISÍVEL EM MOBILE -->
        <button
          @click="$emit('toggle-sidebar')"
          class="hover:text-orange-600 dark:text-white dark:hover:text-orange-400 transition-colors p-1 sm:p-2"
        >
          <Bars3Icon class="w-5 h-5 sm:w-6 sm:h-6" />
        </button>

        <div class="flex items-center">
          <img
            src="/images/Logotipo-scaled.png"
            alt="Logo"
            class="h-6 sm:h-12 md:h-8 w-auto object-contain"
          />
        </div>
      </div>

      <!-- Right Section -->
      <div class="flex items-center gap-2 sm:gap-4">
        <!-- Toggle Theme Button -->
        <button
          @click="toggleTheme"
          class="text-gray-600 dark:text-gray-300 hover:text-brand dark:hover:text-orange-400 transition-colors p-1 sm:p-2"
        >
          <component :is="themeIcon" class="w-4 h-4 sm:w-5 sm:h-5" />
        </button>

        <!-- Notifications Dropdown -->
        <div class="relative">
          <button
            @click="toggleNotifications"
            class="text-gray-600 dark:text-gray-300 hover:text-brand dark:hover:text-orange-400 transition-colors p-1 sm:p-2 relative"
          >
            <BellIcon class="w-4 h-4 sm:w-5 sm:h-5" />
            <span
              v-if="unreadCount > 0"
              class="absolute -top-1 -right-1 bg-brand text-white rounded-full w-3 h-3 sm:w-4 sm:h-4 text-[10px] sm:text-xs flex items-center justify-center font-bold"
            >
              {{ unreadCount > 9 ? "9+" : unreadCount }}
            </span>
          </button>

          <!-- Notifications Dropdown Menu -->
          <div
            v-if="showNotifications"
            class="absolute right-0 mt-2 w-72 sm:w-80 bg-white dark:bg-dark-secondary rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50"
          >
            <!-- ... resto do código do dropdown ... -->
          </div>
        </div>

        <!-- User Profile -->
        <UserDropdown :user="user" />
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import {
  Bars3Icon,
  MagnifyingGlassIcon,
  MoonIcon,
  SunIcon,
  BellIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  InformationCircleIcon,
  ClockIcon,
  UserGroupIcon,
} from "@heroicons/vue/24/outline";
import UserDropdown from "./UserDropdown.vue";
import { useTheme } from "@/Components/GestorReclamacoes/Composables/useTheme";

defineProps({
  sidebarCollapsed: Boolean,
  user: {
    type: Object,
    required: true,
  },
});

defineEmits(["toggle-sidebar"]);

// Sistema de tema
const { isDark, toggleTheme } = useTheme();

// Ícone do tema baseado no estado atual
const themeIcon = computed(() => (isDark.value ? SunIcon : MoonIcon));

// Estados
const showNotifications = ref(false);
const loading = ref(false);
const notifications = ref([]);

// Dados de exemplo para notificações
const sampleNotifications = [
  {
    id: 1,
    title: "Nova Reclamação",
    message: "Uma nova reclamação foi submetida por João Silva",
    type: "complaint",
    read: false,
    created_at: new Date(Date.now() - 5 * 60 * 1000).toISOString(),
    action_url: "/complaints/1",
  },
  {
    id: 2,
    title: "Reclamação Atribuída",
    message: "A reclamação #123 foi atribuída a si",
    type: "assignment",
    read: false,
    created_at: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString(),
    action_url: "/complaints/123",
  },
];

// Computed
const unreadCount = computed(() => {
  return notifications.value.filter((n) => !n.read).length;
});

// Métodos
const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value;
  if (showNotifications.value && notifications.value.length === 0) {
    loadNotifications();
  }
};

const loadNotifications = async () => {
  loading.value = true;
  try {
    await new Promise((resolve) => setTimeout(resolve, 1000));
    notifications.value = [...sampleNotifications];
  } catch (error) {
    console.error("Erro ao carregar notificações:", error);
  } finally {
    loading.value = false;
  }
};

const getNotificationIcon = (type) => {
  const icons = {
    complaint: { icon: ExclamationTriangleIcon, bg: "bg-red-500" },
    assignment: { icon: UserGroupIcon, bg: "bg-blue-500" },
    status: { icon: CheckCircleIcon, bg: "bg-green-500" },
    reminder: { icon: ClockIcon, bg: "bg-yellow-500" },
    default: { icon: InformationCircleIcon, bg: "bg-gray-500" },
  };
  return icons[type] || icons.default;
};

const formatTime = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const diffInMinutes = Math.floor((now - date) / (1000 * 60));
  const diffInHours = Math.floor(diffInMinutes / 60);
  const diffInDays = Math.floor(diffInHours / 24);

  if (diffInMinutes < 1) return "Agora";
  if (diffInMinutes < 60) return `Há ${diffInMinutes} min`;
  if (diffInHours < 24) return `Há ${diffInHours} h`;
  if (diffInDays < 7) return `Há ${diffInDays} dia${diffInDays > 1 ? "s" : ""}`;
  return date.toLocaleDateString("pt-BR");
};

const viewNotification = (notification) => {
  if (!notification.read) {
    markAsRead(notification.id);
  }
  if (notification.action_url) {
    window.location.href = notification.action_url;
  }
  showNotifications.value = false;
};

const markAsRead = async (notificationId) => {
  const notification = notifications.value.find((n) => n.id === notificationId);
  if (notification) {
    notification.read = true;
  }
};

const markAllAsRead = async () => {
  notifications.value.forEach((notification) => {
    notification.read = true;
  });
};

const deleteNotification = async (notificationId) => {
  notifications.value = notifications.value.filter((n) => n.id !== notificationId);
};

const clearAll = async () => {
  notifications.value = [];
};

const viewAllNotifications = () => {
  window.location.href = "/notifications";
  showNotifications.value = false;
};

// Fechar dropdown ao clicar fora
const handleClickOutside = (event) => {
  const dropdown = event.target.closest(".relative");
  if (!dropdown) {
    showNotifications.value = false;
  }
};

// Lifecycle
onMounted(() => {
  document.addEventListener("click", handleClickOutside);
  loadNotifications();
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
});
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
