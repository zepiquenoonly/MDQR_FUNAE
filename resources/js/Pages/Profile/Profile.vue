<template>
    <Layout>
        <div class="container mx-auto px-6 py-8">
            <!-- Breadcrumb -->
            <nav class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400 mb-8">
                <Link href="/home" class="hover:text-orange-600 transition-colors">Painel</Link>
                <ChevronRightIcon class="w-3 h-3 text-gray-400" />
                <span class="text-orange-600 font-medium">Gestão de Perfil</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
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
    </Layout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import Layout from '@/Layouts/ProfileLayout.vue'
import ProfileSidebar from '@/Components/Profile/ProfileSidebar.vue'
import ProfileInfoTab from '@/Components/Profile/ProfileInfoTab.vue'
import ProfileSecurityTab from '@/Components/Profile/ProfileSecurityTab.vue'
import ProfileNotificationsTab from '@/Components/Profile/ProfileNotificationsTab.vue'
import ProfilePreferencesTab from '@/Components/Profile/ProfilePreferencesTab.vue'
import { ChevronRightIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    stats: Object,
    activeTab: String
})
</script>
