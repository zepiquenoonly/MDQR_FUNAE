<template>
    <div class="">
        <div class="sticky p-6 bg-white shadow-sm dark:bg-dark-secondary rounded-xl top-8">
            <!-- Avatar e Info -->
            <div class="mb-6 text-center">
                <div class="relative inline-block mb-4">
                    <div
                        class="flex items-center justify-center w-24 h-24 mx-auto text-2xl font-bold text-white rounded-full bg-gradient-to-r from-orange-500 to-red-500">
                        {{ user.initials }}
                    </div>
                    <div class="" >
                        <div
                            class="absolute inset-0 flex items-center justify-center transition-opacity bg-black bg-opacity-50 rounded-full opacity-0 avatar-overlay">
                            <CameraIcon class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary">{{ user.name }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ user.role }}</p>
                <div class="flex justify-center mt-2 space-x-2">
                    <span
                        class="px-2 py-1 text-xs text-green-800 bg-green-100 rounded-full dark:bg-green-900/30 dark:text-green-300">Verificado</span>
                </div>
            </div>

            <!-- Stats -->
            <!-- <div class="mb-6 space-y-4">
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-dark-accent">
                    <div class="flex items-center space-x-3">
                        <div
                            class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg dark:bg-blue-900/30">
                            <LightBulbIcon class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Sugestões</p>
                            <p class="font-semibold text-gray-800 dark:text-dark-text-primary">{{ stats.suggestions }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-dark-accent">
                    <div class="flex items-center space-x-3">
                        <div
                            class="flex items-center justify-center w-10 h-10 bg-orange-100 rounded-lg dark:bg-orange-900/30">
                            <ExclamationTriangleIcon class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Queixas</p>
                            <p class="font-semibold text-gray-800 dark:text-dark-text-primary">{{ stats.complaints }}
                            </p>
                        </div>
                    </div>
                </div>
            </div> -->

            <!-- Menu de Navegação -->
            <nav class="space-y-2">
                <Link href="/profile/info" :class="[
                    'w-full text-left px-4 py-3 rounded-lg transition-colors flex items-center space-x-3',
                    activeTab === 'info'
                        ? 'bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400'
                        : 'text-gray-700 dark:text-gray-300 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:text-orange-600 dark:hover:text-orange-400'
                ]">
                <UserCircleIcon class="w-5 h-5" />
                <span>Informações Pessoais</span>
                </Link>

                <Link href="/profile/security" :class="[
                    'w-full text-left px-4 py-3 rounded-lg transition-colors flex items-center space-x-3',
                    activeTab === 'security'
                        ? 'bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400'
                        : 'text-gray-700 dark:text-gray-300 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:text-orange-600 dark:hover:text-orange-400'
                ]">
                <ShieldCheckIcon class="w-5 h-5" />
                <span>Segurança</span>
                </Link>

                <Link href="/profile/notifications" :class="[
                    'w-full text-left px-4 py-3 rounded-lg transition-colors flex items-center space-x-3',
                    activeTab === 'notifications'
                        ? 'bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400'
                        : 'text-gray-700 dark:text-gray-300 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:text-orange-600 dark:hover:text-orange-400'
                ]">
                <BellIcon class="w-5 h-5" />
                <span>Notificações</span>
                </Link>

                <Link href="/profile/preferences" :class="[
                    'w-full text-left px-4 py-3 rounded-lg transition-colors flex items-center space-x-3',
                    activeTab === 'preferences'
                        ? 'bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400'
                        : 'text-gray-700 dark:text-gray-300 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:text-orange-600 dark:hover:text-orange-400'
                ]">
                <Cog6ToothIcon class="w-5 h-5" />
                <span>Preferências</span>
                </Link>
            </nav>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import {
    CameraIcon,
    UserCircleIcon,
    ShieldCheckIcon,
    BellIcon,
    Cog6ToothIcon,
    LightBulbIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

defineProps({
    user: Object,
    stats: Object,
    activeTab: String
})

const handleAvatarUpload = () => {
    // TODO: Implementar upload de avatar
    console.log('Upload de avatar será implementado em breve')
}
</script>

<style scoped>
.avatar-upload:hover .avatar-overlay {
    opacity: 1;
}
</style>
