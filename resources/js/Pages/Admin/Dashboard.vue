<template>
    <Layout role="admin" :user="user">
        <div class="p-6">
            <!-- Welcome Message -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Bem-vindo, {{ user.name }}!</h1>
                <p class="text-gray-600 mt-1">Painel de Administração</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <p class="text-sm font-medium text-gray-600 mb-1">Total de Usuários</p>
                    <p class="text-3xl font-bold text-blue-600">{{ stats.totalUsers }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <p class="text-sm font-medium text-gray-600 mb-1">Departamentos</p>
                    <p class="text-3xl font-bold text-green-600">{{ stats.totalDepartments }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <p class="text-sm font-medium text-gray-600 mb-1">Projectos</p>
                    <p class="text-3xl font-bold text-purple-600">{{ stats.totalProjects }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <p class="text-sm font-medium text-gray-600 mb-1">Usuários Ativos</p>
                    <p class="text-3xl font-bold text-orange-600">{{ stats.activeUsers }}</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Acções Rápidas</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Link
                        v-if="hasPermission('manage-users')"
                        href="/admin/users"
                        class="block bg-gradient-to-br from-white to-gray-50 rounded-lg p-6 border border-gray-200 hover:border-brand hover:shadow-lg transition-all"
                    >
                        <h3 class="font-semibold text-gray-900 mb-1">Gerir Usuários</h3>
                        <p class="text-sm text-gray-600">Criar e gerir contas de usuários</p>
                    </Link>
                    <Link
                        v-if="hasPermission('manage-departments')"
                        href="/admin/departments"
                        class="block bg-gradient-to-br from-white to-gray-50 rounded-lg p-6 border border-gray-200 hover:border-brand hover:shadow-lg transition-all"
                    >
                        <h3 class="font-semibold text-gray-900 mb-1">Departamentos</h3>
                        <p class="text-sm text-gray-600">Gerir departamentos</p>
                    </Link>
                    <Link
                        v-if="hasPermission('manage-projects')"
                        href="/admin/projects"
                        class="block bg-gradient-to-br from-white to-gray-50 rounded-lg p-6 border border-gray-200 hover:border-brand hover:shadow-lg transition-all"
                    >
                        <h3 class="font-semibold text-gray-900 mb-1">Projectos</h3>
                        <p class="text-sm text-gray-600">Gerir projectos</p>
                    </Link>
                    <Link
                        v-if="hasPermission('manage-settings')"
                        href="/profile"
                        class="block bg-gradient-to-br from-white to-gray-50 rounded-lg p-6 border border-gray-200 hover:border-brand hover:shadow-lg transition-all"
                    >
                        <h3 class="font-semibold text-gray-900 mb-1">Configurações</h3>
                        <p class="text-sm text-gray-600">Configurações do sistema</p>
                    </Link>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Atividade Recente</h2>
                <div class="text-gray-500 text-center py-8">
                    <p>Nenhuma atividade recente</p>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import Layout from '@/Layouts/Layout.vue'

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
