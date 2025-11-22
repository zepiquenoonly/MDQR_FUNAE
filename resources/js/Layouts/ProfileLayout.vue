<template>
    <div class="min-h-screen bg-gray-50 dark:bg-dark-primary">
        <!-- Header -->
        <header class="bg-brand shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo e Navegação -->
                    <div class="flex items-center">
                        <Link href="/dashboard" class="flex items-center space-x-3">
                        <div class="w-8 h-8 flex items-center justify-center">
                            <img src="/images/Logotipo-scaled.png" alt="Ícone de autenticação"
                                class="h-20 w-20 md:h-24 md:w-24 mx-auto object-contain" />
                        </div>
                        <span class="text-xl font-bold text-white">MDQR</span>
                        </Link>
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center space-x-4">
                        <div
                            class="hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition-all duration-200">
                            <UserDropdown :user="$page.props.auth.user" :hide-profile="true"
                                bg-color="hover:bg-orange-100 dark:hover:bg-orange-900/30" text-color="text-white" />
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main>
            <slot />
        </main>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import UserDropdown from '@/Components/Dashboard/UserDropdown.vue'
</script>