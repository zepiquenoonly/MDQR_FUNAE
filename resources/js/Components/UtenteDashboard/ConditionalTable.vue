<template>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden mt-6">
        <!-- Cabeçalho da Tabela -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">{{ tableTitle }}</h2>
                <div class="flex items-center space-x-4">
                    <!-- Barra de Pesquisa -->
                    <div class="relative">
                        <input type="text" v-model="searchQuery" placeholder="Pesquisar..."
                            class="pl-10 pr-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-0 focus:ring-brand focus:border-brand">
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                            <MagnifyingGlassIcon class="w-4 h-4 text-gray-400" />
                        </div>
                    </div>

                    <!-- Filtro Dropdown -->
                    <select v-model="statusFilter"
                        class="px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-0 focus:ring-brand focus:border-brand w-52">
                        <option value="all">Todos os Estados</option>
                        <option v-for="status in availableStatuses" :key="status" :value="status">
                            {{ status }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Conteúdo da Tabela -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b-2 border-gray-200">
                        <th v-for="column in tableColumns" :key="column.key"
                            class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer"
                            @click="sortTable(column.key)">
                            <div class="flex items-center space-x-1">
                                <span>{{ column.label }}</span>
                                <ChevronUpDownIcon v-if="sortKey === column.key" class="w-4 h-4 text-gray-400" />
                            </div>
                        </th>
                        <th class="text-left py-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in filteredAndSortedData" :key="item.id"
                        class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <!-- Colunas Dinâmicas -->
                        <td v-for="column in tableColumns" :key="column.key" class="py-4 px-4 text-sm"
                            :class="getCellClass(column.key, item[column.key])">
                            <template
                                v-if="column.key === 'status' || column.key === 'priority' || column.key === 'impactType'">
                                <span :class="getBadgeClass(item[column.key])">
                                    {{ item[column.key] }}
                                </span>
                            </template>
                            <template v-else-if="column.key === 'date'">
                                {{ formatDate(item[column.key]) }}
                            </template>
                            <template v-else-if="column.key === 'description' && item[column.key].length > 50">
                                {{ item[column.key].substring(0, 50) }}...
                            </template>
                            <template v-else>
                                {{ item[column.key] }}
                            </template>
                        </td>

                        <!-- Coluna de Ações -->
                        <td class="py-4 px-4">
                            <div class="flex items-center space-x-2">
                                <button @click="viewDetails(item)"
                                    class="text-blue-600 hover:text-blue-800 transition-colors" title="Ver Detalhes">
                                    <EyeIcon class="w-4 h-4" />
                                </button>
                                <button @click="editItem(item)"
                                    class="text-green-600 hover:text-green-800 transition-colors" title="Editar">
                                    <PencilIcon class="w-4 h-4" />
                                </button>
                                <button @click="deleteItem(item)"
                                    class="text-red-600 hover:text-red-800 transition-colors" title="Eliminar">
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Estado Vazio -->
        <div v-if="filteredAndSortedData.length === 0" class="text-center py-12">
            <DocumentTextIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <p class="text-gray-500 text-lg">Nenhuma {{ currentType }} encontrada</p>
            <p class="text-gray-400 text-sm mt-2">
                {{
                    searchQuery || statusFilter !== 'all'
                        ? 'Tente ajustar a sua pesquisa ou filtro'
                        : `Ainda não criou nenhuma ${currentType}`
                }}
            </p>
        </div>

        <!-- Rodapé da Tabela -->
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    A mostrar {{ filteredAndSortedData.length }} de {{ tableData.length }} {{ currentType }}
                </div>
                <div class="flex items-center space-x-4">
                    <button @click="exportData"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition-colors flex items-center space-x-2">
                        <ArrowDownTrayIcon class="w-4 h-4" />
                        <span>Exportar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import {
    MagnifyingGlassIcon,
    ChevronUpDownIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
    DocumentTextIcon,
    ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    type: {
        type: String,
        required: true,
        validator: (value) => ['suggestions', 'claims', 'complaints'].includes(value)
    },
    data: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['view-details', 'edit-item', 'delete-item', 'export-data'])

// Dados reativos
const searchQuery = ref('')
const statusFilter = ref('all')
const sortKey = ref('date')
const sortOrder = ref('desc')

// Propriedades computadas
const currentType = computed(() => {
    const types = {
        suggestions: 'sugestão',
        claims: 'queixa',
        complaints: 'reclamação'
    }
    return types[props.type] || 'item'
})

const tableTitle = computed(() => {
    const titles = {
        suggestions: 'Minhas Sugestões',
        claims: 'Minhas Queixas',
        complaints: 'Minhas Reclamações'
    }
    return titles[props.type] || 'Tabela de Dados'
})

const tableColumns = computed(() => {
    const baseColumns = [
        { key: 'id', label: 'ID' },
        { key: 'title', label: 'Título' },
        { key: 'description', label: 'Descrição' },
        { key: 'project', label: 'Projecto' },
        { key: 'date', label: 'Data' }
    ]

    const typeSpecificColumns = {
        suggestions: [
            ...baseColumns,
            { key: 'status', label: 'Estado' }
        ],
        claims: [
            ...baseColumns,
            { key: 'priority', label: 'Prioridade' },
            { key: 'status', label: 'Estado' }
        ],
        complaints: [
            ...baseColumns,
            { key: 'impactType', label: 'Tipo de Impacto' },
            { key: 'status', label: 'Estado' }
        ]
    }

    return typeSpecificColumns[props.type] || baseColumns
})

const availableStatuses = computed(() => {
    const statuses = {
        suggestions: ['Pendente', 'Em Análise', 'Aprovada', 'Rejeitada'],
        claims: ['Pendente', 'Em Análise', 'Validada', 'Rejeitada'],
        complaints: ['Aberta', 'Em Progresso', 'Resolvida', 'Fechada']
    }
    return statuses[props.type] || []
})

const tableData = computed(() => {
    return props.data.length > 0 ? props.data : getSampleData()
})

const filteredAndSortedData = computed(() => {
    let filtered = tableData.value.filter(item => {
        const matchesSearch = searchQuery.value === '' ||
            Object.values(item).some(value =>
                String(value).toLowerCase().includes(searchQuery.value.toLowerCase())
            )

        const matchesStatus = statusFilter.value === 'all' ||
            item.status === statusFilter.value

        return matchesSearch && matchesStatus
    })

    // Ordenar dados
    return filtered.sort((a, b) => {
        let aValue = a[sortKey.value]
        let bValue = b[sortKey.value]

        if (sortKey.value === 'date') {
            aValue = new Date(aValue)
            bValue = new Date(bValue)
        }

        if (aValue < bValue) return sortOrder.value === 'asc' ? -1 : 1
        if (aValue > bValue) return sortOrder.value === 'asc' ? 1 : -1
        return 0
    })
})

// Métodos
const getSampleData = () => {
    const sampleData = {
        suggestions: [
            {
                id: 'SUG-001',
                title: 'Melhoria na Distribuição de Água',
                description: 'Sugestão para melhor sistema de distribuição de água em áreas urbanas',
                project: 'Sistema de Abastecimento de Água',
                date: '2024-01-15',
                status: 'Aprovada'
            },
            {
                id: 'SUG-002',
                title: 'Implementação de Energia Solar',
                description: 'Proposta para implementação de energia solar em edifícios públicos',
                project: 'Projecto de Energia Renovável',
                date: '2024-01-10',
                status: 'Em Análise'
            },
            {
                id: 'SUG-003',
                title: 'Renovação do Centro Comunitário',
                description: 'Proposta de renovação para as instalações do centro comunitário',
                project: 'Desenvolvimento Urbano',
                date: '2024-01-05',
                status: 'Pendente'
            }
        ],
        claims: [
            {
                id: 'QUE-001',
                title: 'Queixa de Danos à Propriedade',
                description: 'Danos à propriedade residencial devido a actividades de construção',
                project: 'Desenvolvimento de Infraestrutura',
                date: '2024-01-14',
                priority: 'Alta',
                status: 'Em Análise'
            },
            {
                id: 'QUE-002',
                title: 'Queixa de Compensação',
                description: 'Pedido de compensação devido a atrasos e perturbações no projecto',
                project: 'Obras Públicas',
                date: '2024-01-09',
                priority: 'Média',
                status: 'Pendente'
            },
            {
                id: 'QUE-003',
                title: 'Queixa de Impacto Ambiental',
                description: 'Queixa sobre danos ambientais de actividades industriais',
                project: 'Operação Mineira',
                date: '2024-01-04',
                priority: 'Urgente',
                status: 'Validada'
            }
        ],
        complaints: [
            {
                id: 'REC-001',
                title: 'Danos Ambientais',
                description: 'Reclamação sobre impacto ambiental de actividades de construção',
                project: 'Projecto de Energia Eólica',
                date: '2024-01-12',
                impactType: 'Ambiental',
                status: 'Em Progresso'
            },
            {
                id: 'REC-002',
                title: 'Poluição Sonora',
                description: 'Ruído excessivo durante a noite afectando residentes',
                project: 'Desenvolvimento Urbano',
                date: '2024-01-08',
                impactType: 'Social',
                status: 'Resolvida'
            },
            {
                id: 'REC-003',
                title: 'Bloqueio de Estrada',
                description: 'Construção a bloquear estrada principal de acesso por período prolongado',
                project: 'Projecto de Infraestrutura',
                date: '2024-01-03',
                impactType: 'Económico',
                status: 'Aberta'
            }
        ]
    }

    return sampleData[props.type] || []
}

const sortTable = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortKey.value = key
        sortOrder.value = 'asc'
    }
}

const getBadgeClass = (value) => {
    const badgeClasses = {
        // Badges de estado
        'Pendente': 'badge-pending',
        'Em Análise': 'badge-review',
        'Aprovada': 'badge-approved',
        'Rejeitada': 'badge-rejected',
        'Validada': 'badge-validated',
        'Aberta': 'badge-open',
        'Em Progresso': 'badge-progress',
        'Resolvida': 'badge-resolved',
        'Fechada': 'badge-closed',

        // Badges de prioridade
        'Alta': 'badge-high',
        'Média': 'badge-medium',
        'Urgente': 'badge-urgent',
        'Baixa': 'badge-low',

        // Badges de tipo de impacto
        'Ambiental': 'badge-environmental',
        'Social': 'badge-social',
        'Económico': 'badge-economic'
    }

    return `badge ${badgeClasses[value] || 'badge-default'}`
}

const getCellClass = (key, value) => {
    if (key === 'title') {
        return 'font-medium text-gray-900'
    }
    if (key === 'description') {
        return 'text-gray-600'
    }
    return 'text-gray-500'
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('pt-PT', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const viewDetails = (item) => {
    emit('view-details', item)
}

const editItem = (item) => {
    emit('edit-item', item)
}

const deleteItem = (item) => {
    if (confirm(`Tem a certeza que deseja eliminar "${item.title}"?`)) {
        emit('delete-item', item)
    }
}

const exportData = () => {
    emit('export-data', {
        type: props.type,
        data: filteredAndSortedData.value
    })
}

// Observar mudanças de tipo para redefinir filtros
watch(() => props.type, () => {
    searchQuery.value = ''
    statusFilter.value = 'all'
    sortKey.value = 'date'
    sortOrder.value = 'desc'
})
</script>

<style scoped>
.badge {
    @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
}

.badge-default {
    @apply bg-gray-100 text-gray-800;
}

/* Badges de estado */
.badge-pending {
    @apply bg-yellow-100 text-yellow-800;
}

.badge-review {
    @apply bg-blue-100 text-blue-800;
}

.badge-approved {
    @apply bg-green-100 text-green-800;
}

.badge-rejected {
    @apply bg-red-100 text-red-800;
}

.badge-validated {
    @apply bg-green-100 text-green-800;
}

.badge-open {
    @apply bg-orange-100 text-orange-800;
}

.badge-progress {
    @apply bg-blue-100 text-blue-800;
}

.badge-resolved {
    @apply bg-green-100 text-green-800;
}

.badge-closed {
    @apply bg-gray-100 text-gray-800;
}

/* Badges de prioridade */
.badge-high {
    @apply bg-red-100 text-red-800;
}

.badge-medium {
    @apply bg-yellow-100 text-yellow-800;
}

.badge-urgent {
    @apply bg-purple-100 text-purple-800;
}

.badge-low {
    @apply bg-green-100 text-green-800;
}

/* Badges de tipo de impacto */
.badge-environmental {
    @apply bg-green-100 text-green-800;
}

.badge-social {
    @apply bg-blue-100 text-blue-800;
}

.badge-economic {
    @apply bg-purple-100 text-purple-800;
}
</style>