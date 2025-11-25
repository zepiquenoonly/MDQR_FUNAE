<template>
    <div class="lg:col-span-1">
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6 sticky top-8">
            <!-- Avatar e Info -->
            <div class="text-center mb-6">
                <div class="relative inline-block mb-4">
                    <div
                        class="w-24 h-24 bg-gradient-to-r from-orange-500 to-red-500 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto">
                        {{ user.initials }}
                    </div>
                    <div class="avatar-upload cursor-pointer" @click="handleAvatarUpload">
                        <div
                            class="avatar-overlay absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 transition-opacity">
                            <CameraIcon class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary">{{ user.name }}</h2>
                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ user.role }}</p>
                <div class="flex justify-center space-x-2 mt-2">
                    <span
                        class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 text-xs px-2 py-1 rounded-full">Verificado
                        Verificado</span>
                </div>
            </div>

            <!-- Stats -->
            <div class="space-y-4 mb-6">
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <LightBulbIcon class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Sugestões</p>
                            <p class="font-semibold text-gray-800 dark:text-dark-text-primary">{{ stats.suggestions }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                            <ExclamationTriangleIcon class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Queixas</p>
                            <p class="font-semibold text-gray-800 dark:text-dark-text-primary">{{ stats.complaints }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

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
