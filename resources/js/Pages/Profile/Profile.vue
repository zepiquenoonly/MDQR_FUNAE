<template>
  <Layout :user="user" :stats="stats" :active-tab="activeTab" :show-stats="showStats">
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
      <!-- Breadcrumb com botão Voltar -->
      <div class="flex items-center justify-between -mt-4 mb-4 sm:mb-6 lg:mb-8">
        <nav
          class="flex items-center space-x-1 sm:space-x-2 text-xs sm:text-sm text-gray-600 dark:text-gray-400"
        >
          <Link href="/home" class="hover:text-orange-600 transition-colors truncate"
            >Painel</Link
          >
          <ChevronRightIcon class="w-3 h-3 text-gray-400 flex-shrink-0" />
          <span class="text-orange-600 font-medium truncate">Gestão de Perfil</span>
        </nav>

        <!-- Botão Voltar -->
        <button
          @click="goBack"
          class="flex items-center bg-gray-500 space-x-2 px-3 sm:px-4 py-2 text-xs sm:text-sm text-white dark:text-white hover:bg-gray-600 transition-colors border border-gray-300 dark:border-gray-600 rounded"
        >
          <ArrowLeftIcon class="w-3 h-3 sm:w-4 sm:h-4" />
          <span>Voltar</span>
        </button>
      </div>

      <div class="space-y-4 sm:space-y-6 lg:space-y-8">
        <!-- Conteúdo Principal -->
        <div class="lg:col-span-3">
          <!-- Informações Pessoais -->
          <ProfileInfoTab v-if="activeTab === 'info'" :user="user" />

          <!-- Segurança -->
          <ProfileSecurityTab v-else-if="activeTab === 'security'" />

          <!-- Notificações -->
          <ProfileNotificationsTab v-else-if="activeTab === 'notifications'" />

          <!-- Preferências -->
          <ProfilePreferencesTab v-else-if="activeTab === 'preferences'" />
        </div>
      </div>
    </div>

    <div class="fixed top-4 right-4 z-[70] space-y-2">
      <div
        v-if="globalToast.show"
        :class="[
          'flex items-center p-4 rounded-lg shadow-lg transform transition-all duration-300 animate-in slide-in-from-right',
          globalToast.type === 'success'
            ? 'bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800'
            : 'bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800',
        ]"
        style="animation-duration: 300ms"
      >
        <div
          :class="[
            'flex-shrink-0',
            globalToast.type === 'success'
              ? 'text-green-600 dark:text-green-400'
              : 'text-red-600 dark:text-red-400',
          ]"
        >
          <CheckCircleIcon v-if="globalToast.type === 'success'" class="w-5 h-5" />
          <XCircleIcon v-else class="w-5 h-5" />
        </div>
        <div class="ml-3">
          <p
            :class="[
              'text-sm font-medium',
              globalToast.type === 'success'
                ? 'text-green-800 dark:text-green-300'
                : 'text-red-800 dark:text-red-300',
            ]"
          >
            {{ globalToast.message }}
          </p>
        </div>
        <button
          @click="hideGlobalToast"
          class="ml-auto -mx-1.5 -my-1.5 rounded-lg p-1.5 inline-flex items-center justify-center h-8 w-8"
          :class="[
            globalToast.type === 'success'
              ? 'text-green-500 hover:bg-green-100 dark:hover:bg-green-900/50'
              : 'text-red-500 hover:bg-red-100 dark:hover:bg-red-900/50',
          ]"
        >
          <XMarkIcon class="w-4 h-4" />
        </button>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { Link, router } from "@inertiajs/vue3";
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import Layout from "@/Layouts/ProfileLayout.vue";
import ProfileInfoTab from "@/Components/Profile/ProfileInfoTab.vue";
import ProfileSecurityTab from "@/Components/Profile/ProfileSecurityTab.vue";
import ProfileNotificationsTab from "@/Components/Profile/ProfileNotificationsTab.vue";
import ProfilePreferencesTab from "@/Components/Profile/ProfilePreferencesTab.vue";
import {
  ChevronRightIcon,
  ArrowLeftIcon,
  CheckCircleIcon,
  XCircleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  user: Object,
  stats: Object,
  activeTab: String,
  showStats: {
    type: Boolean,
    default: false,
  },
});

const globalToast = ref({
  show: false,
  message: "",
  type: "success",
});

router.on("success", (event) => {
  const props = event.detail.page.props;

  if (props.flash?.success) {
    showGlobalToast(props.flash.success, "success");
  } else if (props.flash?.error) {
    showGlobalToast(props.flash.error, "error");
  }
});

const showGlobalToast = (message, type = "success") => {
  globalToast.value = {
    show: true,
    message,
    type,
  };

  setTimeout(() => {
    globalToast.value.show = false;
  }, 5000);
};

const goBack = () => {
  window.history.length > 1 ? router.visit("/home") : router.visit("/home");
};
</script>
