<template>
    <Layout role="admin" :user="user">
        <div class="space-y-6 sm:space-y-8 px-3 sm:px-0">
            <!-- Welcome Message - Glassmorphism Hero -->
            <div class="relative overflow-hidden rounded-3xl">
                <div class="absolute inset-0 bg-gradient-to-br from-primary-600 via-orange-600 to-amber-700"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent"></div>
                <!-- Floating Elements -->
                <div class="absolute top-6 left-10 w-20 h-20 bg-white/10 rounded-full blur-xl animate-pulse"></div>
                <div class="absolute bottom-6 right-10 w-32 h-32 bg-purple-300/20 rounded-full blur-2xl animate-pulse delay-1000"></div>
                <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-indigo-200/15 rounded-full blur-lg animate-pulse delay-500"></div>

                <div class="relative p-8 sm:p-10">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 bg-white/10 backdrop-blur-xl rounded-2xl flex items-center justify-center border border-white/20 shadow-2xl shadow-black/10">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-2 text-white drop-shadow-2xl">
                                Bem-vindo(a), <span class="bg-gradient-to-r from-purple-200 to-indigo-200 bg-clip-text text-transparent">{{ user.name }}</span>!
                            </h1>
                            <div class="w-24 h-1 bg-gradient-to-r from-purple-300 to-indigo-300 rounded-full mb-4"></div>
                        </div>
                    </div>
                    <p class="text-white/90 text-base sm:text-lg lg:text-xl leading-relaxed max-w-4xl drop-shadow-lg">
                        Painel Administrativo - Visão Geral do Sistema
                    </p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                <!-- Card 1: Usuários Totais -->
                <div class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-2xl border border-gray-100 dark:border-gray-700 p-6 transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <!-- Background decorativo -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-primary-500/10 to-transparent rounded-full blur-2xl transition-all duration-500 group-hover:scale-150"></div>

                    <div class="relative">
                        <!-- Ícone -->
                        <div class="inline-flex p-3 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 shadow-lg shadow-primary-500/30 mb-4 group-hover:scale-110 transition-transform duration-300">
                            <UsersIcon class="h-6 w-6 text-white" />
                        </div>

                        <!-- Conteúdo -->
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Usuários Totais</p>
                            <h3 class="text-4xl font-bold text-gray-900 dark:text-white mb-1 tracking-tight">{{ stats.totalUsers }}</h3>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="flex items-center gap-1 px-2 py-1 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 rounded-full text-xs font-medium">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Registados</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Departamentos -->
                <div class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-2xl border border-gray-100 dark:border-gray-700 p-6 transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <!-- Background decorativo -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-emerald-500/10 to-transparent rounded-full blur-2xl transition-all duration-500 group-hover:scale-150"></div>

                    <div class="relative">
                        <!-- Ícone -->
                        <div class="inline-flex p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg shadow-emerald-500/30 mb-4 group-hover:scale-110 transition-transform duration-300">
                            <BuildingOfficeIcon class="h-6 w-6 text-white" />
                        </div>

                        <!-- Conteúdo -->
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Departamentos</p>
                            <h3 class="text-4xl font-bold text-gray-900 dark:text-white mb-1 tracking-tight">{{ stats.totalDepartments }}</h3>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="flex items-center gap-1 px-2 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 rounded-full text-xs font-medium">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Activos</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Projectos -->
                <div class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-2xl border border-gray-100 dark:border-gray-700 p-6 transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <!-- Background decorativo -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-purple-500/10 to-transparent rounded-full blur-2xl transition-all duration-500 group-hover:scale-150"></div>

                    <div class="relative">
                        <!-- Ícone -->
                        <div class="inline-flex p-3 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 shadow-lg shadow-purple-500/30 mb-4 group-hover:scale-110 transition-transform duration-300">
                            <FolderIcon class="h-6 w-6 text-white" />
                        </div>

                        <!-- Conteúdo -->
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Projectos</p>
                            <h3 class="text-4xl font-bold text-gray-900 dark:text-white mb-1 tracking-tight">{{ stats.totalProjects }}</h3>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="flex items-center gap-1 px-2 py-1 bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400 rounded-full text-xs font-medium">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Em curso</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Usuários Activos -->
                <div class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-2xl border border-gray-100 dark:border-gray-700 p-6 transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <!-- Background decorativo -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-amber-500/10 to-transparent rounded-full blur-2xl transition-all duration-500 group-hover:scale-150"></div>

                    <div class="relative">
                        <!-- Ícone -->
                        <div class="inline-flex p-3 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 shadow-lg shadow-amber-500/30 mb-4 group-hover:scale-110 transition-transform duration-300">
                            <UserGroupIcon class="h-6 w-6 text-white" />
                        </div>

                        <!-- Conteúdo -->
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Usuários Activos</p>
                            <h3 class="text-4xl font-bold text-gray-900 dark:text-white mb-1 tracking-tight">{{ stats.activeUsers }}</h3>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="flex items-center gap-1 px-2 py-1 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 rounded-full text-xs font-medium">
                                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                    <span>Online</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">

                <!-- Quick Actions Section -->
                <div class="lg:col-span-2">
                    <div class="glass-card p-6 border border-white/40 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <span class="p-2 bg-primary-50 rounded-lg text-primary-600">
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
                                class="group flex items-center gap-4 p-5 rounded-xl bg-gradient-to-br from-primary-50 via-white to-primary-50/30 dark:from-gray-700 dark:via-gray-800 dark:to-gray-700 border border-primary-100 dark:border-gray-600 hover:border-primary-300 dark:hover:border-primary-500 hover:shadow-xl hover:shadow-primary-500/20 transition-all duration-300 relative overflow-hidden"
                            >
                                <div class="absolute inset-0 bg-gradient-to-r from-primary-500/10 via-transparent to-primary-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="h-14 w-14 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center shadow-lg shadow-primary-500/30 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 relative z-10">
                                    <UsersIcon class="h-7 w-7 text-white" />
                                </div>
                                <div class="flex-1 relative z-10">
                                    <h3 class="font-bold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors text-base">Gerir Usuários</h3>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">Adicionar ou editar contas de usuários</p>
                                </div>
                                <div class="relative z-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400 group-hover:text-primary-500 group-hover:translate-x-2 transition-all duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </Link>

                            <Link
                                v-if="hasPermission('manage-departments')"
                                href="/admin/departments"
                                class="group flex items-center gap-4 p-5 rounded-xl bg-gradient-to-br from-emerald-50 via-white to-emerald-50/30 dark:from-gray-700 dark:via-gray-800 dark:to-gray-700 border border-emerald-100 dark:border-gray-600 hover:border-emerald-300 dark:hover:border-emerald-500 hover:shadow-xl hover:shadow-emerald-500/20 transition-all duration-300 relative overflow-hidden"
                            >
                                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/10 via-transparent to-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="h-14 w-14 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/30 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 relative z-10">
                                    <BuildingOfficeIcon class="h-7 w-7 text-white" />
                                </div>
                                <div class="flex-1 relative z-10">
                                    <h3 class="font-bold text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors text-base">Departamentos</h3>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">Gerir estrutura organizacional</p>
                                </div>
                                <div class="relative z-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400 group-hover:text-emerald-500 group-hover:translate-x-2 transition-all duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </Link>

                            <Link
                                v-if="hasPermission('manage-projects')"
                                href="/admin/projects"
                                class="group flex items-center gap-4 p-5 rounded-xl bg-gradient-to-br from-purple-50 via-white to-purple-50/30 dark:from-gray-700 dark:via-gray-800 dark:to-gray-700 border border-purple-100 dark:border-gray-600 hover:border-purple-300 dark:hover:border-purple-500 hover:shadow-xl hover:shadow-purple-500/20 transition-all duration-300 relative overflow-hidden"
                            >
                                <div class="absolute inset-0 bg-gradient-to-r from-purple-500/10 via-transparent to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="h-14 w-14 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-lg shadow-purple-500/30 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 relative z-10">
                                    <FolderIcon class="h-7 w-7 text-white" />
                                </div>
                                <div class="flex-1 relative z-10">
                                    <h3 class="font-bold text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors text-base">Projectos</h3>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">Supervisão e acompanhamento</p>
                                </div>
                                <div class="relative z-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400 group-hover:text-purple-500 group-hover:translate-x-2 transition-all duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
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
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden p-6 transition-all duration-300">
                        <div class="relative">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="p-2.5 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl shadow-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">Distribuição de Usuários</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Total cadastrados no sistema</p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <!-- Utentes -->
                                <div class="group flex items-center justify-between p-3 rounded-xl bg-gradient-to-r from-blue-50 to-transparent dark:from-blue-900/10 hover:from-blue-100 dark:hover:from-blue-900/20 border border-blue-100 dark:border-blue-800/30 transition-all duration-200">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-md">
                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Utentes</span>
                                    </div>
                                    <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ usersByRole.utentes }}</span>
                                </div>

                                <!-- Técnicos -->
                                <div class="group flex items-center justify-between p-3 rounded-xl bg-gradient-to-r from-amber-50 to-transparent dark:from-amber-900/10 hover:from-amber-100 dark:hover:from-amber-900/20 border border-amber-100 dark:border-amber-800/30 transition-all duration-200">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center shadow-md">
                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Técnicos</span>
                                    </div>
                                    <span class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ usersByRole.tecnicos }}</span>
                                </div>

                                <!-- Gestores -->
                                <div class="group flex items-center justify-between p-3 rounded-xl bg-gradient-to-r from-emerald-50 to-transparent dark:from-emerald-900/10 hover:from-emerald-100 dark:hover:from-emerald-900/20 border border-emerald-100 dark:border-emerald-800/30 transition-all duration-200">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-md">
                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Gestores</span>
                                    </div>
                                    <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ usersByRole.gestores }}</span>
                                </div>

                                <!-- Directores -->
                                <div class="group flex items-center justify-between p-3 rounded-xl bg-gradient-to-r from-indigo-50 to-transparent dark:from-indigo-900/10 hover:from-indigo-100 dark:hover:from-indigo-900/20 border border-indigo-100 dark:border-indigo-800/30 transition-all duration-200">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center shadow-md">
                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Directores</span>
                                    </div>
                                    <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ usersByRole.directores }}</span>
                                </div>

                                <!-- PCA -->
                                <div class="group flex items-center justify-between p-3 rounded-xl bg-gradient-to-r from-purple-50 to-transparent dark:from-purple-900/10 hover:from-purple-100 dark:hover:from-purple-900/20 border border-purple-100 dark:border-purple-800/30 transition-all duration-200">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-md">
                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">PCA</span>
                                    </div>
                                    <span class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ usersByRole.pca }}</span>
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
    },
    usersByRole: {
        type: Object,
        default: () => ({
            utentes: 0,
            tecnicos: 0,
            gestores: 0,
            directores: 0,
            pca: 0,
        })
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
