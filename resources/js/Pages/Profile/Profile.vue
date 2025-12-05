<template>
    <Layout>
        <div class="px-6 mx-auto mt-5 ">
            <!-- Breadcrumb -->
            <nav class="flex items-center mb-8 space-x-2 text-sm text-gray-600 dark:text-gray-400">
                <Link href="/home" class="transition-colors hover:text-orange-600">Painel</Link>
                <ChevronRightIcon class="w-3 h-3 text-gray-400" />
                <span class="font-medium text-orange-600">Gestão de Perfil</span>
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

            <!-- Global Toast Notification -->
            <div v-if="globalToast.show" class="fixed bottom-4 right-4 z-50">
              <div class="flex items-center p-4 mb-4 text-sm border rounded-lg"
                :class="[
                  globalToast.type === 'success'
                    ? 'text-green-800 border-green-300 bg-green-50 dark:text-green-300 dark:border-green-800 dark:bg-gray-800'
                    : 'text-red-800 border-red-300 bg-red-50 dark:text-red-300 dark:border-red-800 dark:bg-gray-800',
                ]"
              >
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
        </div>
  </Layout>
</template>

<script setup>
import { Link, router } from "@inertiajs/vue3";
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import Layout from '@/Layouts/UnifiedLayout.vue'
import ProfileInfoTab from "@/Components/Profile/ProfileInfoTab.vue";
import ProfileSidebar from "@/Components/Profile/ProfileSidebar.vue";
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
