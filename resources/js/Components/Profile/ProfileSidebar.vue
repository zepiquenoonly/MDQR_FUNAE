<template>
  <aside
    class="bg-brand dark:bg-dark-secondary text-white h-full flex flex-col overflow-hidden relative"
  >
    <!-- Header com botão hamburger -->
    <div class="flex justify-start p-4 sm:hidden">
      <button
        @click="$emit('close-sidebar')"
        class="text-white hover:bg-white/10 rounded-md p-2 transition-colors"
      >
        <!-- Ícone Hamburger (três linhas) -->
      </button>
    </div>

    <!-- Avatar e Info -->
    <div
      class="text-center mt-2 sm:mt-6 lg:mt-8 mb-4 sm:mb-6 px-2"
      :class="{ 'px-0': isCollapsed }"
    >
      <div class="relative inline-block mb-3 sm:mb-4">
        <div
          class="bg-gradient-to-r from-orange-500 to-red-500 rounded-full flex items-center justify-center text-white font-bold mx-auto relative overflow-hidden"
          :class="[
            isCollapsed
              ? 'w-16 h-16 text-lg'
              : 'w-24 h-24 sm:w-20 sm:h-20 lg:w-24 lg:h-24 text-xl sm:text-xl lg:text-2xl',
          ]"
        >
          <!-- Avatar Image or Initials -->
          <img
            v-if="user.avatar_url"
            :src="user.avatar_url"
            :alt="user.name"
            class="absolute inset-0 w-full h-full object-cover"
          />
          <span v-else>{{ user.initials }}</span>
        </div>
        <div class="avatar-upload cursor-pointer" @click="showAvatarPopup = true">
          <div
            class="avatar-overlay absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 transition-opacity"
          >
            <CameraIcon
              class="text-white"
              :class="[isCollapsed ? 'w-4 h-4' : 'w-6 h-6 sm:w-5 sm:h-5 lg:w-6 lg:h-6']"
            />
          </div>
        </div>
      </div>

      <!-- Informações do usuário (ocultas quando colapsado) -->
      <div v-if="!isCollapsed">
        <h2
          class="text-lg sm:text-lg lg:text-xl font-semibold text-white-100 dark:text-dark-text-primary px-2"
        >
          {{ user.name }}
        </h2>
        <div class="flex justify-center space-x-2 mt-1 sm:mt-2 px-2">
          <span
            class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 text-xs px-2 py-1 rounded-full truncate"
            >{{ user.role }}</span
          >
          <span
            v-if="user.email_verified_at"
            class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-xs px-2 py-1 rounded-full truncate"
            >Verificado</span
          >
        </div>
      </div>
    </div>

    <!-- Stats - Apenas para Utentes (oculto quando colapsado) -->
    <div v-if="showStats && stats && !isCollapsed" class="mb-4 sm:mb-6">
      <div
        class="flex items-center justify-between p-3 sm:p-4 bg-brand dark:bg-dark-secondary"
      >
        <div class="flex items-center space-x-3 sm:space-x-4">
          <ExclamationTriangleIcon
            class="w-5 h-5 sm:w-6 sm:h-6 text-orange-300 flex-shrink-0"
          />
          <div>
            <p class="text-xs sm:text-sm text-white/80 dark:text-white/80">Queixas</p>
            <p
              class="font-semibold text-white dark:text-dark-text-primary text-sm sm:text-base"
            >
              {{ stats.complaints }}
            </p>
          </div>
        </div>
      </div>

      <div
        class="flex items-center justify-between p-3 sm:p-4 bg-brand dark:bg-dark-secondary"
      >
        <div class="flex items-center space-x-3 sm:space-x-4">
          <ChatBubbleLeftRightIcon
            class="w-5 h-5 sm:w-6 sm:h-6 text-blue-300 flex-shrink-0"
          />
          <div>
            <p class="text-xs sm:text-sm text-white/80 dark:text-white/80">Reclamações</p>
            <p
              class="font-semibold text-white dark:text-dark-text-primary text-sm sm:text-base"
            >
              {{ stats.complaints }}
            </p>
          </div>
        </div>
      </div>

      <div
        class="flex items-center justify-between p-3 sm:p-4 bg-brand dark:bg-dark-secondary"
      >
        <div class="flex items-center space-x-3 sm:space-x-4">
          <LightBulbIcon class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-300 flex-shrink-0" />
          <div>
            <p class="text-xs sm:text-sm text-white/80 dark:text-white/80">Sugestões</p>
            <p
              class="font-semibold text-white dark:text-dark-text-primary text-sm sm:text-base"
            >
              {{ stats.suggestions }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Menu de Navegação -->
    <nav class="space-y-1 sm:space-y-2 px-2 sm:px-0" :class="{ 'px-1': isCollapsed }">
      <div class="relative group">
        <Link
          href="/profile/info"
          :class="[
            'w-full text-left transition-colors flex items-center space-x-2 sm:space-x-3 text-sm sm:text-base',
            isCollapsed
              ? 'px-2 sm:px-2 py-2 sm:py-3 justify-center'
              : 'px-3 sm:px-4 py-2 sm:py-3',
            activeTab === 'info'
              ? 'bg-brand text-white dark:bg-dark-accent dark:text-white'
              : 'text-white hover:bg-brand hover:text-white dark:text-white dark:hover:bg-gray-600',
          ]"
        >
          <UserCircleIcon class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" />
          <span v-if="!isCollapsed" class="truncate">Informações Pessoais</span>
        </Link>

        <!-- Tooltip para modo colapsado -->
        <div
          v-if="isCollapsed"
          class="absolute left-full top-1/2 transform -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 dark:bg-gray-700 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50"
        >
          Informações Pessoais
          <div
            class="absolute right-full top-1/2 transform -translate-y-1/2 border-4 border-transparent border-r-gray-900 dark:border-r-gray-700"
          ></div>
        </div>
      </div>

      <div class="relative group">
        <Link
          href="/profile/security"
          :class="[
            'w-full text-left transition-colors flex items-center space-x-2 sm:space-x-3 text-sm sm:text-base',
            isCollapsed
              ? 'px-2 sm:px-2 py-2 sm:py-3 justify-center'
              : 'px-3 sm:px-4 py-2 sm:py-3',
            activeTab === 'security'
              ? 'bg-brand text-white dark:bg-dark-accent dark:text-white'
              : 'text-white hover:bg-brand hover:text-white dark:text-white dark:hover:bg-gray-600',
          ]"
        >
          <ShieldCheckIcon class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" />
          <span v-if="!isCollapsed" class="truncate">Segurança</span>
        </Link>

        <!-- Tooltip para modo colapsado -->
        <div
          v-if="isCollapsed"
          class="absolute left-full top-1/2 transform -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 dark:bg-gray-700 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50"
        >
          Segurança
          <div
            class="absolute right-full top-1/2 transform -translate-y-1/2 border-4 border-transparent border-r-gray-900 dark:border-r-gray-700"
          ></div>
        </div>
      </div>

      <div class="relative group">
        <Link
          href="/profile/notifications"
          :class="[
            'w-full text-left transition-colors flex items-center space-x-2 sm:space-x-3 text-sm sm:text-base',
            isCollapsed
              ? 'px-2 sm:px-2 py-2 sm:py-3 justify-center'
              : 'px-3 sm:px-4 py-2 sm:py-3',
            activeTab === 'notifications'
              ? 'bg-brand text-white dark:bg-dark-accent dark:text-white'
              : 'text-white hover:bg-brand hover:text-white dark:text-white dark:hover:bg-gray-600',
          ]"
        >
          <BellIcon class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" />
          <span v-if="!isCollapsed" class="truncate">Notificações</span>
        </Link>

        <!-- Tooltip para modo colapsado -->
        <div
          v-if="isCollapsed"
          class="absolute left-full top-1/2 transform -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 dark:bg-gray-700 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50"
        >
          Notificações
          <div
            class="absolute right-full top-1/2 transform -translate-y-1/2 border-4 border-transparent border-r-gray-900 dark:border-r-gray-700"
          ></div>
        </div>
      </div>

      <!--<div class="relative group">
        <Link
          href="/profile/preferences"
          :class="[
            'w-full text-left transition-colors flex items-center space-x-2 sm:space-x-3 text-sm sm:text-base',
            isCollapsed
              ? 'px-2 sm:px-2 py-2 sm:py-3 justify-center'
              : 'px-3 sm:px-4 py-2 sm:py-3',
            activeTab === 'preferences'
              ? 'bg-brand text-white dark:bg-dark-accent dark:text-white'
              : 'text-white hover:bg-brand hover:text-white dark:text-white dark:hover:bg-gray-600',
          ]"
        >
          <Cog6ToothIcon class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" />
          <span v-if="!isCollapsed" class="truncate">Preferências</span>
        </Link>

        Tooltip para modo colapsado 
      <div v-if="isCollapsed"
        class="absolute left-full top-1/2 transform -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 dark:bg-gray-700 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">
        Preferências
        <div
          class="absolute right-full top-1/2 transform -translate-y-1/2 border-4 border-transparent border-r-gray-900 dark:border-r-gray-700">
        </div>
      </div>
      </div>-->
    </nav>

    <!-- Avatar Upload Popup -->
    <AvatarUploadPopup
      v-if="showAvatarPopup"
      :show="showAvatarPopup"
      :user="user"
      :current-avatar="user.avatar_url"
      @close="showAvatarPopup = false"
      @avatar-updated="handleAvatarUpdated"
    />
  </aside>
</template>

<script setup>
import { ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import {
  CameraIcon,
  UserCircleIcon,
  ShieldCheckIcon,
  BellIcon,
  Cog6ToothIcon,
  ExclamationTriangleIcon,
  ChatBubbleLeftRightIcon,
  LightBulbIcon,
} from "@heroicons/vue/24/outline";
import AvatarUploadPopup from "@/Components/Profile/AvatarUploadPopup.vue";

const props = defineProps({
  user: Object,
  stats: Object,
  activeTab: String,
  showStats: {
    type: Boolean,
    default: false,
  },
  isCollapsed: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["close-sidebar", "toggle-collapse"]);

const showAvatarPopup = ref(false);

const handleAvatarUpload = () => {
  showAvatarPopup.value = true;
};

const handleAvatarUpdated = (avatarUrl) => {
  // Refresh the page to update the user data with new avatar
  router.reload();
};
</script>

<style scoped>
.avatar-upload:hover .avatar-overlay {
  opacity: 1;
}
</style>
