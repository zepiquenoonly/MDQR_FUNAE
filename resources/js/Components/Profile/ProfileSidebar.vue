<template>
  <aside
    class="relative flex flex-col h-full text-white transition-all duration-300 bg-gradient-to-b from-orange-600 via-orange-500 to-red-500 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900"
    :class="isCollapsed ? 'w-20' : 'w-64 xl:w-80'"
  >
    <!-- Header mobile com botão fechar -->
    <div class="flex items-center justify-between p-4 lg:hidden">
      <span class="text-lg font-semibold">Perfil</span>
      <button
        @click="$emit('close-sidebar')"
        class="p-2 transition-colors rounded-lg hover:bg-white/10"
      >
        <XMarkIcon class="w-6 h-6" />
      </button>
    </div>

    <!-- Avatar e Info -->
    <div
      class="flex-shrink-0 px-4 pt-6 pb-4 text-center lg:pt-8 lg:pb-6"
      :class="isCollapsed ? 'px-2' : ''"
    >
      <!-- Avatar com animação -->
      <div class="relative inline-block mb-3 group lg:mb-4">
        <div
          class="relative flex items-center justify-center mx-auto overflow-hidden font-bold text-white transition-all duration-300 rounded-full shadow-xl bg-gradient-to-br from-white/20 to-white/5 backdrop-blur-sm ring-4 ring-white/30"
          :class="isCollapsed ? 'w-12 h-12 text-sm lg:w-14 lg:h-14 lg:text-base' : 'w-20 h-20 text-xl lg:w-24 lg:h-24 xl:w-28 xl:h-28 lg:text-2xl xl:text-3xl'"
        >
          <img
            v-if="user.avatar_url"
            :src="user.avatar_url"
            :alt="user.name"
            class="absolute inset-0 object-cover w-full h-full"
          />
          <span v-else class="font-bold">{{ user.initials }}</span>
        </div>

        <!-- Overlay de upload com animação suave -->
        <div
          class="absolute inset-0 cursor-pointer avatar-upload"
          @click="showAvatarPopup = true"
        >
          <div
            class="absolute inset-0 flex items-center justify-center transition-all duration-300 bg-black rounded-full opacity-0 bg-opacity-60 group-hover:opacity-100 backdrop-blur-sm"
          >
            <div class="text-center">
              <CameraIcon
                :class="[
                  'mx-auto text-white transition-transform group-hover:scale-110',
                  isCollapsed ? 'w-4 h-4 lg:w-5 lg:h-5' : 'w-5 h-5 lg:w-6 lg:h-6 xl:w-7 xl:h-7',
                ]"
              />
              <span
                v-if="!isCollapsed"
                class="block mt-1 text-xs font-medium text-white"
              >
                Alterar
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Informações do usuário com transição suave -->
      <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="scale-95 opacity-0"
        enter-to-class="scale-100 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="scale-100 opacity-100"
        leave-to-class="scale-95 opacity-0"
      >
        <div v-if="!isCollapsed" class="space-y-2 lg:space-y-3">
          <h2
            class="text-base font-bold leading-tight text-white lg:text-lg xl:text-xl drop-shadow-lg"
          >
            {{ user.name }}
          </h2>

          <div class="flex flex-wrap justify-center gap-2">
            <span
              class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full shadow-sm lg:px-3 lg:py-1.5 dark:bg-green-900/40 dark:text-green-300"
            >
              <span class="w-2 h-2 mr-1.5 bg-green-500 rounded-full animate-pulse"></span>
              {{ user.role }}
            </span>
            <span
              v-if="user.email_verified_at"
              class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-blue-700 bg-blue-100 rounded-full shadow-sm lg:px-3 lg:py-1.5 dark:bg-blue-900/40 dark:text-blue-300"
            >
              <CheckBadgeIcon class="w-3 h-3 mr-1 lg:w-4 lg:h-4" />
              Verificado
            </span>
          </div>
        </div>
      </transition>
    </div>

    <!-- Stats Card - Apenas para Utentes -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="-translate-y-4 opacity-0"
      enter-to-class="translate-y-0 opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="translate-y-0 opacity-100"
      leave-to-class="-translate-y-4 opacity-0"
    >
      <div
        v-if="showStats && stats && !isCollapsed"
        class="flex-shrink-0 px-4 pb-4 lg:pb-6"
      >
        <div
          class="p-3 space-y-2 border shadow-xl lg:p-4 lg:space-y-3 rounded-2xl bg-white/10 backdrop-blur-md border-white/20"
        >
          <h3 class="mb-2 text-sm font-bold lg:mb-3 text-white/90">Estatísticas</h3>

          <!-- Queixas -->
          <div
            class="flex items-center justify-between p-2.5 transition-all duration-200 lg:p-3 rounded-xl bg-white/5 hover:bg-white/10 group"
          >
            <div class="flex items-center space-x-2 lg:space-x-3">
              <div
                class="flex items-center justify-center w-8 h-8 rounded-lg lg:w-10 lg:h-10 bg-orange-500/20"
              >
                <ExclamationTriangleIcon class="w-4 h-4 text-orange-300 lg:w-5 lg:h-5" />
              </div>
              <div>
                <p class="text-xs font-medium text-white/70">Queixas</p>
                <p class="text-base font-bold text-white lg:text-lg">
                  {{ stats.complaints }}
                </p>
              </div>
            </div>
          </div>

          <!-- Reclamações -->
          <div
            class="flex items-center justify-between p-2.5 transition-all duration-200 lg:p-3 rounded-xl bg-white/5 hover:bg-white/10 group"
          >
            <div class="flex items-center space-x-2 lg:space-x-3">
              <div
                class="flex items-center justify-center w-8 h-8 rounded-lg lg:w-10 lg:h-10 bg-blue-500/20"
              >
                <ChatBubbleLeftRightIcon class="w-4 h-4 text-blue-300 lg:w-5 lg:h-5" />
              </div>
              <div>
                <p class="text-xs font-medium text-white/70">Reclamações</p>
                <p class="text-base font-bold text-white lg:text-lg">
                  {{ stats.complaints }}
                </p>
              </div>
            </div>
          </div>

          <!-- Sugestões -->
          <div
            class="flex items-center justify-between p-2.5 transition-all duration-200 lg:p-3 rounded-xl bg-white/5 hover:bg-white/10 group"
          >
            <div class="flex items-center space-x-2 lg:space-x-3">
              <div
                class="flex items-center justify-center w-8 h-8 rounded-lg lg:w-10 lg:h-10 bg-yellow-500/20"
              >
                <LightBulbIcon class="w-4 h-4 text-yellow-300 lg:w-5 lg:h-5" />
              </div>
              <div>
                <p class="text-xs font-medium text-white/70">Sugestões</p>
                <p class="text-base font-bold text-white lg:text-lg">
                  {{ stats.suggestions }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Menu de Navegação -->
    <nav class="flex-1 px-4 pb-4 space-y-2 overflow-y-auto lg:pb-6">
      <div
        v-if="!isCollapsed"
        class="mb-3 text-xs font-semibold tracking-wider uppercase lg:mb-4 text-white/50"
      >
        Menu
      </div>

      <!-- Informações Pessoais -->
      <div class="relative group">
        <Link
          href="/profile/info"
          :class="[
            'flex items-center space-x-3 text-sm font-medium transition-all duration-200 rounded-xl',
            isCollapsed ? 'justify-center p-2.5 lg:p-3' : 'px-3 py-2.5 lg:px-4 lg:py-3',
            activeTab === 'info'
              ? 'bg-white text-orange-600 shadow-lg scale-105'
              : 'text-white/90 hover:bg-white/10 hover:text-white hover:scale-105',
          ]"
        >
          <UserCircleIcon
            :class="[
              'flex-shrink-0 transition-transform group-hover:scale-110',
              isCollapsed ? 'w-5 h-5 lg:w-6 lg:h-6' : 'w-5 h-5',
            ]"
          />
          <span v-if="!isCollapsed" class="truncate">Informações Pessoais</span>
        </Link>

        <!-- Tooltip colapsado -->
        <div
          v-if="isCollapsed"
          class="absolute z-50 invisible px-3 py-2 ml-3 text-xs text-white transition-all duration-200 -translate-y-1/2 bg-gray-900 rounded-lg shadow-xl opacity-0 left-full group-hover:opacity-100 group-hover:visible whitespace-nowrap top-1/2"
        >
          Informações Pessoais
          <div
            class="absolute -translate-y-1/2 border-4 border-transparent right-full top-1/2 border-r-gray-900"
          ></div>
        </div>
      </div>

      <!-- Segurança -->
      <div class="relative group">
        <Link
          href="/profile/security"
          :class="[
            'flex items-center space-x-3 text-sm font-medium transition-all duration-200 rounded-xl',
            isCollapsed ? 'justify-center p-2.5 lg:p-3' : 'px-3 py-2.5 lg:px-4 lg:py-3',
            activeTab === 'security'
              ? 'bg-white text-orange-600 shadow-lg scale-105'
              : 'text-white/90 hover:bg-white/10 hover:text-white hover:scale-105',
          ]"
        >
          <ShieldCheckIcon
            :class="[
              'flex-shrink-0 transition-transform group-hover:scale-110',
              isCollapsed ? 'w-5 h-5 lg:w-6 lg:h-6' : 'w-5 h-5',
            ]"
          />
          <span v-if="!isCollapsed" class="truncate">Segurança</span>
        </Link>

        <!-- Tooltip colapsado -->
        <div
          v-if="isCollapsed"
          class="absolute z-50 invisible px-3 py-2 ml-3 text-xs text-white transition-all duration-200 -translate-y-1/2 bg-gray-900 rounded-lg shadow-xl opacity-0 left-full group-hover:opacity-100 group-hover:visible whitespace-nowrap top-1/2"
        >
          Segurança
          <div
            class="absolute -translate-y-1/2 border-4 border-transparent right-full top-1/2 border-r-gray-900"
          ></div>
        </div>
      </div>

      <!-- Notificações -->
      <div class="relative group">
        <Link
          href="/profile/notifications"
          :class="[
            'flex items-center space-x-3 text-sm font-medium transition-all duration-200 rounded-xl',
            isCollapsed ? 'justify-center p-2.5 lg:p-3' : 'px-3 py-2.5 lg:px-4 lg:py-3',
            activeTab === 'notifications'
              ? 'bg-white text-orange-600 shadow-lg scale-105'
              : 'text-white/90 hover:bg-white/10 hover:text-white hover:scale-105',
          ]"
        >
          <BellIcon
            :class="[
              'flex-shrink-0 transition-transform group-hover:scale-110',
              isCollapsed ? 'w-5 h-5 lg:w-6 lg:h-6' : 'w-5 h-5',
            ]"
          />
          <span v-if="!isCollapsed" class="truncate">Notificações</span>
        </Link>

        <!-- Tooltip colapsado -->
        <div
          v-if="isCollapsed"
          class="absolute z-50 invisible px-3 py-2 ml-3 text-xs text-white transition-all duration-200 -translate-y-1/2 bg-gray-900 rounded-lg shadow-xl opacity-0 left-full group-hover:opacity-100 group-hover:visible whitespace-nowrap top-1/2"
        >
          Notificações
          <div
            class="absolute -translate-y-1/2 border-4 border-transparent right-full top-1/2 border-r-gray-900"
          ></div>
        </div>
      </div>
    </nav>

    <!-- Footer com versão (opcional) -->
    <div
      v-if="!isCollapsed"
      class="flex-shrink-0 px-4 py-3 text-xs text-center border-t lg:py-4 text-white/50 border-white/10"
    >
      <p>MDQR © 2025</p>
    </div>

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
  ExclamationTriangleIcon,
  ChatBubbleLeftRightIcon,
  LightBulbIcon,
  ChevronLeftIcon,
  XMarkIcon,
  CheckBadgeIcon,
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

const handleAvatarUpdated = (avatarUrl) => {
  router.reload();
};
</script>

<style scoped>
/* Scrollbar personalizado */
nav::-webkit-scrollbar {
  width: 6px;
}

nav::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
}

nav::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
}

nav::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}
</style>
