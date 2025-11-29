<template>
  <div class="relative">
    <div
      class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-dark-accent transition-colors"
      @click="toggleDropdown"
    >
      <div
        class="w-9 h-9 bg-gray-400 dark:bg-gray-600 rounded-full flex items-center justify-center text-white"
      >
        <UserIcon class="w-5 h-5" />
      </div>
      <span class="text-sm font-medium text-gray-700 dark:text-gray-300 hidden sm:block">
        {{ user?.name || "Usuário" }}
      </span>
      <ChevronDownIcon class="text-gray-500 dark:text-gray-400 w-4 h-4" />
    </div>

    <!-- Dropdown Menu -->
    <div
      v-if="isOpen"
      class="absolute top-full right-0 mt-2 w-48 bg-white dark:bg-dark-secondary rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50"
    >
      <!-- Perfil - Ocultar quando hideProfile for true -->
      <Link
        v-if="!hideProfile"
        href="/profile/info"
        class="flex items-center gap-3 px-4 py-2 text-sm transition-colors cursor-pointer text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-accent"
        @click="isOpen = false"
      >
        <UserIcon class="w-4 h-4" />
        <span>Perfil</span>
      </Link>

      <!-- Sair - URL direta -->
      <a
        class="flex items-center gap-3 px-4 py-2 text-sm transition-colors cursor-pointer text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
        @click="handleLogout"
      >
        <ArrowRightOnRectangleIcon class="w-4 h-4" />
        <span>Sair</span>
      </a>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { router, Link } from "@inertiajs/vue3";
import {
  UserIcon,
  ChevronDownIcon,
  LockClosedIcon,
  ArrowRightOnRectangleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  user: {
    type: Object,
    default: () => ({
      name: "Usuário",
      email: "",
    }),
  },
  hideProfile: {
    type: Boolean,
    default: false,
  },
  // Props para customizar cores
  bgColor: {
    type: String,
    default: "hover:bg-gray-50",
  },
  textColor: {
    type: String,
    default: "text-gray-700",
  },
});

const isOpen = ref(false);

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

const handleLogout = () => {
  router.post("/logout");
  isOpen.value = false;
};

const handleItemClick = (item) => {
  console.log("Action:", item.text);
  isOpen.value = false;
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  const dropdown = event.target.closest(".relative");
  if (!dropdown) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
});
</script>
