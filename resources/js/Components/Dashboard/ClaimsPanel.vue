<template>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Header Section -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Reclamações - Comitê do bairro Machava</h2>
        </div>

        <!-- Stats Cards -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <StatCard title="Pendentes" value="20" description="Sugestões Pendentes" :total-style="false"
                    class="border border-gray-200" />
                <StatCard title="Em análise" value="30" description="Reclamações em análise" :total-style="false"
                    class="border border-gray-200" />
                <StatCard title="Considerada" value="10" description="Sugestões Consideradas" :total-style="false"
                    class="border border-gray-200" />
                <StatCard title="Não considerada" value="10" description="Sugestões não consideradas"
                    :total-style="false" class="border border-gray-200" />
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Listagem das reclamações</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b-2 border-gray-200">
                                <th
                                    class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Nome do munícipe
                                </th>
                                <th
                                    class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Bairro
                                </th>
                                <th
                                    class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Tipo de impacto
                                </th>
                                <th
                                    class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Tipo de submissão
                                </th>
                                <th
                                    class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th
                                    class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, index) in tableData" :key="index"
                                class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-4 text-sm text-gray-800">{{ row.name }}</td>
                                <td class="py-4 px-4 text-sm text-gray-600">{{ row.neighborhood }}</td>
                                <td class="py-4 px-4">
                                    <span :class="['badge', row.impactType.class]">
                                        <component :is="row.impactType.icon" class="w-3 h-3 mr-1" />
                                        {{ row.impactType.text }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span :class="['badge', row.submissionType.class]">
                                        <component :is="row.submissionType.icon" class="w-3 h-3 mr-1" />
                                        {{ row.submissionType.text }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span :class="['badge', row.status.class]">
                                        <component :is="row.status.icon" class="w-3 h-3 mr-1" />
                                        {{ row.status.text }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <button @click="viewDetails(row)"
                                        class="bg-brand hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
                                        <EyeIcon class="w-4 h-4" />
                                        Ver mais
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Registrar Button -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <button @click="registerNew"
                        class="bg-brand hover:bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
                        <PlusIcon class="w-5 h-5" />
                        Registrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import {
    EyeIcon,
    PlusIcon,
    LightBulbIcon,
    ExclamationTriangleIcon,
    DocumentTextIcon,
    ClockIcon,
    CheckCircleIcon,
    CogIcon,
    XCircleIcon,
    UserIcon
} from '@heroicons/vue/24/outline'
import StatCard from './StatCard.vue'

// Sample data based on the image
const tableData = ref([
    {
        name: 'João Silva',
        neighborhood: 'Machava',
        impactType: {
            class: 'badge-ambiental',
            text: 'Ambiental',
            icon: LightBulbIcon
        },
        submissionType: {
            class: 'badge-reclamacao',
            text: 'Sugestão',
            icon: DocumentTextIcon
        },
        status: {
            class: 'badge-presidente',
            text: 'Presidente',
            icon: UserIcon
        }
    },
    {
        name: 'Maria Santos',
        neighborhood: 'Matola',
        impactType: {
            class: 'badge-social',
            text: 'Social',
            icon: ExclamationTriangleIcon
        },
        submissionType: {
            class: 'badge-reclamacao',
            text: 'Sugestão',
            icon: DocumentTextIcon
        },
        status: {
            class: 'badge-considerada',
            text: 'Considerado',
            icon: CheckCircleIcon
        }
    },
    {
        name: 'Carlos Mendes',
        neighborhood: 'Boane',
        impactType: {
            class: 'badge-ambiental',
            text: 'Ambiental',
            icon: LightBulbIcon
        },
        submissionType: {
            class: 'badge-reclamacao',
            text: 'Sugestão',
            icon: DocumentTextIcon
        },
        status: {
            class: 'badge-em-analise',
            text: 'Em análise',
            icon: CogIcon
        }
    },
    {
        name: 'Carlos Cossa',
        neighborhood: 'Boane',
        impactType: {
            class: 'badge-ambiental',
            text: 'Ambiental',
            icon: LightBulbIcon
        },
        submissionType: {
            class: 'badge-reclamacao',
            text: 'Sugestão',
            icon: DocumentTextIcon
        },
        status: {
            class: 'badge-nao-considerada',
            text: 'Não considerado',
            icon: XCircleIcon
        }
    }
])

const viewDetails = (row) => {
    alert(`Visualizando detalhes da reclamação: ${row.name}\nBairro: ${row.neighborhood}\nEstado: ${row.status.text}`)
}

const registerNew = () => {
    alert('Abrir formulário para registrar nova reclamação')
}
</script>

<style scoped>
.badge {
    @apply inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full;
}

.badge-ambiental {
    @apply bg-blue-100 text-blue-800;
}

.badge-social {
    @apply bg-indigo-100 text-indigo-800;
}

.badge-reclamacao {
    @apply bg-red-100 text-red-800;
}

.badge-presidente {
    @apply bg-purple-100 text-purple-800;
}

.badge-considerada {
    @apply bg-green-100 text-green-800;
}

.badge-em-analise {
    @apply bg-blue-100 text-blue-800;
}

.badge-nao-considerada {
    @apply bg-red-100 text-red-800;
}
</style>