<template>
    <Layout role="admin" :user="user">
        <div class="p-3 sm:p-4 md:p-6 space-y-4 sm:space-y-6">
            <!-- Welcome Message with Glass Effect -->
            <div class="relative overflow-hidden rounded-2xl p-6 sm:p-8 lg:p-10 shadow-2xl border border-orange-400/30 group transition-all duration-500 hover:shadow-3xl">
                <!-- Animated Gradient Background -->
                <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-indigo-600 to-indigo-800"></div>
                <!-- Glass Layer -->
                <div class="absolute inset-0 backdrop-blur-sm bg-white/10"></div>
                <!-- Content -->
                <div class="relative z-10">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl xl:text-4xl font-bold mb-2 text-white drop-shadow-lg">
                        Bem-vindo(a), {{ user.name }}!
                    </h1>
                    <p class="text-blue-50 text-sm sm:text-base lg:text-lg font-medium drop-shadow">
                        Painel Administrativo - Visão Geral do Sistema
                    </p>
                </div>
                <!-- Decorative Elements -->
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-700"></div>
                <div class="absolute -left-10 -top-10 w-32 h-32 bg-blue-300/10 rounded-full blur-2xl group-hover:scale-125 transition-transform duration-700"></div>
                <!-- Floating particles -->
                <div class="absolute top-1/4 right-1/4 w-2 h-2 bg-white/30 rounded-full animate-pulse"></div>
                <div class="absolute top-3/4 right-1/3 w-2 h-2 bg-white/20 rounded-full animate-pulse"></div>
                <div class="absolute top-1/2 right-1/2 w-1 h-1 bg-white/40 rounded-full animate-pulse"></div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                <StatCard
                    title="Usuários Totais"
                    :value="stats.totalUsers"
                    description="Registados no sistema"
                    :icon="UsersIcon"
                    color="blue"
                />
                <StatCard
                    title="Departamentos"
                    :value="stats.totalDepartments"
                    description="Departamentos activos"
                    :icon="BuildingOfficeIcon"
                    color="green"
                />
                <StatCard
                    title="Projectos"
                    :value="stats.totalProjects"
                    description="Projectos em curso"
                    :icon="FolderIcon"
                    color="purple"
                />
                <StatCard
                    title="Usuários Activos"
                    :value="stats.activeUsers"
                    description="Online ou activos recentemente"
                    :icon="UserGroupIcon"
                    color="orange"
                />
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                
                <!-- Quick Actions Section -->
                <div class="lg:col-span-2">
                    <div class="glass-card p-6 border border-white/40 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <span class="p-2 bg-blue-50 rounded-lg text-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </span>
                                Acções Rápidas
                            </h2>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <Link
                                v-if="hasPermission('manage-users')"
                                href="/admin/users"
                                class="group flex items-center gap-4 p-4 rounded-xl bg-gradient-to-br from-slate-50 to-slate-100/50 border border-slate-200 hover:border-blue-300 hover:shadow-md transition-all duration-300 relative overflow-hidden"
                            >
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-md group-hover:scale-110 transition-transform relative z-10">
                                    <UsersIcon class="h-6 w-6 text-white" />
                                </div>
                                <div class="flex-1 relative z-10">
                                    <h3 class="font-semibold text-slate-900 group-hover:text-blue-600 transition-colors">Gerir Usuários</h3>
                                    <p class="text-xs text-slate-500">Adicionar ou editar contas</p>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 group-hover:text-blue-500 group-hover:translate-x-1 transition-all relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>

                            <Link
                                v-if="hasPermission('manage-departments')"
                                href="/admin/departments"
                                class="group flex items-center gap-4 p-4 rounded-xl bg-gradient-to-br from-slate-50 to-slate-100/50 border border-slate-200 hover:border-emerald-300 hover:shadow-md transition-all duration-300 relative overflow-hidden"
                            >
                                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-md group-hover:scale-110 transition-transform relative z-10">
                                    <BuildingOfficeIcon class="h-6 w-6 text-white" />
                                </div>
                                <div class="flex-1 relative z-10">
                                    <h3 class="font-semibold text-slate-900 group-hover:text-emerald-600 transition-colors">Departamentos</h3>
                                    <p class="text-xs text-slate-500">Gerir estrutura orgânica</p>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 group-hover:text-emerald-500 group-hover:translate-x-1 transition-all relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>

                            <Link
                                v-if="hasPermission('manage-projects')"
                                href="/admin/projects"
                                class="group flex items-center gap-4 p-4 rounded-xl bg-gradient-to-br from-slate-50 to-slate-100/50 border border-slate-200 hover:border-purple-300 hover:shadow-md transition-all duration-300 relative overflow-hidden"
                            >
                                <div class="absolute inset-0 bg-gradient-to-r from-purple-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-md group-hover:scale-110 transition-transform relative z-10">
                                    <FolderIcon class="h-6 w-6 text-white" />
                                </div>
                                <div class="flex-1 relative z-10">
                                    <h3 class="font-semibold text-slate-900 group-hover:text-purple-600 transition-colors">Projectos</h3>
                                    <p class="text-xs text-slate-500">Supervisão de projectos</p>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 group-hover:text-purple-500 group-hover:translate-x-1 transition-all relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>

                            <Link
                                v-if="hasPermission('manage-settings')"
                                href="/profile"
                                class="group flex items-center gap-4 p-4 rounded-xl bg-gradient-to-br from-slate-50 to-slate-100/50 border border-slate-200 hover:border-slate-400 hover:shadow-md transition-all duration-300 relative overflow-hidden"
                            >
                                <div class="absolute inset-0 bg-gradient-to-r from-slate-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-slate-600 to-slate-700 flex items-center justify-center shadow-md group-hover:scale-110 transition-transform relative z-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1 relative z-10">
                                    <h3 class="font-semibold text-slate-900 group-hover:text-slate-700 transition-colors">Configurações</h3>
                                    <p class="text-xs text-slate-500">Definições do sistema</p>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 group-hover:text-slate-600 group-hover:translate-x-1 transition-all relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- System Status Sidebar -->
                <div class="space-y-6">
                    <!-- System Health -->
                    <div class="glass-card p-6 border border-white/40 rounded-2xl shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-900 flex items-center gap-2 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Estado do Sistema
                        </h2>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-600">Servidor</span>
                                <span class="inline-flex items-center gap-1.5 text-sm font-medium text-emerald-600">
                                    <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                                    Operacional
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-600">Base de Dados</span>
                                <span class="inline-flex items-center gap-1.5 text-sm font-medium text-emerald-600">
                                    <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                                    Conectada
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-600">Email (SMTP)</span>
                                <span class="inline-flex items-center gap-1.5 text-sm font-medium text-emerald-600">
                                    <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                                    Activo
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats Widget -->
                    <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl shadow-lg shadow-blue-500/25 overflow-hidden text-white p-6 relative">
                        <!-- Decorative bg -->
                        <div class="absolute top-0 right-0 -mr-4 -mt-4 w-24 h-24 rounded-full bg-white/10 blur-xl"></div>
                        <div class="absolute bottom-0 left-0 -ml-4 -mb-4 w-20 h-20 rounded-full bg-indigo-500/30 blur-xl"></div>

                        <div class="relative z-10">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="h-10 w-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold">Resumo Rápido</h3>
                                    <p class="text-sm text-blue-200">Última atualização: agora</p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between py-2 border-t border-white/20">
                                    <span class="text-blue-100">Técnicos</span>
                                    <span class="font-bold">17</span>
                                </div>
                                <div class="flex items-center justify-between py-2 border-t border-white/20">
                                    <span class="text-blue-100">Gestores</span>
                                    <span class="font-bold">9</span>
                                </div>
                                <div class="flex items-center justify-between py-2 border-t border-white/20">
                                    <span class="text-blue-100">Directores</span>
                                    <span class="font-bold">6</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import Layout from '@/Layouts/UnifiedLayout.vue'
import StatCard from '@/Components/UtenteDashboard/StatCard.vue'
import { UsersIcon, BuildingOfficeIcon, FolderIcon, UserGroupIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    permissions: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        required: true
    }
})

const hasPermission = (permission) => {
    return props.permissions.includes(permission)
}
</script>

<style scoped>
.glass-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
}
</style>