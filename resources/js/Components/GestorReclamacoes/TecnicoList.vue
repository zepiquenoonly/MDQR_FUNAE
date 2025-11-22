<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Técnicos</h1>
            </div>
            <div class="flex gap-3">
                <button @click="refreshTecnicos"
                    class="px-4 py-2 bg-brand text-white rounded-lg hover:bg-gray-700 transition-colors flex items-center gap-2">
                    <ArrowPathIcon class="w-4 h-4" />
                    Atualizar
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total de Técnicos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.totalTecnicos || 0 }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <UserGroupIcon class="w-6 h-6 text-blue-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Técnicos Ativos</p>
                        <p class="text-2xl font-bold text-green-600">{{ stats.tecnicosAtivos || 0 }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-lg">
                        <CheckBadgeIcon class="w-6 h-6 text-green-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Casos Atribuídos</p>
                        <p class="text-2xl font-bold text-orange-600">{{ stats.casosAtribuidos || 0 }}</p>
                    </div>
                    <div class="p-3 bg-orange-100 rounded-lg">
                        <ClipboardDocumentListIcon class="w-6 h-6 text-orange-600" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Técnicos Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Lista de Técnicos</h2>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="p-8 text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand mx-auto"></div>
                <p class="text-gray-600 mt-2">A carregar técnicos...</p>
            </div>

            <!-- Técnicos List -->
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Técnico
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Contacto
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Localização
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Casos Ativos
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="tecnico in tecnicos" :key="tecnico.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 bg-brand text-white rounded-full flex items-center justify-center font-semibold">
                                        {{ getInitials(tecnico.name) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ tecnico.name }}</div>
                                        <div class="text-sm text-gray-500">{{ tecnico.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ tecnico.phone || 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ tecnico.username }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ tecnico.province || 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ tecnico.district || '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ tecnico.active_cases_count || 0 }} casos
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="[
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    tecnico.is_active
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-red-100 text-red-800'
                                ]">
                                    {{ tecnico.is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <!--<button @click="viewTecnicoDesempenho(tecnico)"
                                    class="text-brand hover:text-brand-dark mr-3">
                                    Ver Desempenho
                                </button>-->
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty State -->
                <div v-if="tecnicos.length === 0 && !loading" class="text-center py-12">
                    <UserGroupIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum técnico encontrado</h3>
                    <p class="mt-1 text-sm text-gray-500">Não existem técnicos registados no sistema.</p>
                </div>
            </div>
        </div>

        <!-- Technician Performance Modal -->
        <TecnicoDesempenho v-if="showDesempenhoModal" :tecnico="selectedTecnico" @close="closeDesempenhoModal" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import {
    UserGroupIcon,
    CheckBadgeIcon,
    ClipboardDocumentListIcon,
    ArrowPathIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'
import TecnicoDesempenho from './TecnicoDesempenho.vue'

// Estado
const tecnicos = ref([])
const stats = ref({})
const loading = ref(false)
const selectedTecnico = ref(null)
const showDesempenhoModal = ref(false)

// Função para carregar técnicos
const loadTecnicos = async () => {
    loading.value = true
    try {
        console.log('🔄 A carregar técnicos...')
        const response = await fetch('/api/tecnicos')
        console.log('📊 Resposta da API:', response)

        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`)
        }

        const data = await response.json()
        console.log('📋 Dados recebidos:', data)

        tecnicos.value = data.tecnicos || []
        stats.value = data.stats || {}
    } catch (error) {
        console.error('❌ Erro ao carregar técnicos:', error)
        tecnicos.value = []
    } finally {
        loading.value = false
    }
}

// Função para obter iniciais do nome
const getInitials = (name) => {
    if (!name) return '?'
    return name.split(' ')
        .map(part => part.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2)
}

// Função para atualizar lista
const refreshTecnicos = () => {
    loadTecnicos()
}

// Função para ver desempenho do técnico
const viewTecnicoDesempenho = (tecnico) => {
    selectedTecnico.value = tecnico
    showDesempenhoModal.value = true
}

// Função para fechar modal de desempenho
const closeDesempenhoModal = () => {
    showDesempenhoModal.value = false
    selectedTecnico.value = null
}

// Carregar técnicos quando o componente é montado
onMounted(() => {
    console.log('🚀 TecnicoList montado')
    loadTecnicos()
})
</script>