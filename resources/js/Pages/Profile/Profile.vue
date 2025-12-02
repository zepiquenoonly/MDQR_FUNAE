<template>
  <Layout
    :user="user"
    :stats="stats"
    :active-tab="activeTab"
    :show-stats="showStats"
    :hide-profile="true"
  >
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
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-4">
          <!-- Sidebar do Perfil -->
          <ProfileSidebar :user="user" :stats="stats" :active-tab="activeTab" />

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
