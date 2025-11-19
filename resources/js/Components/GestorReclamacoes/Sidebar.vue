<template>
    <aside :class="[
        'bg-gradient-to-b from-orange-500 to-orange-600 text-white shadow-xl transition-all duration-300 flex flex-col',
        isCollapsed ? 'w-20' : 'w-64'
    ]">
        <!-- Brand/Logo Section -->
        <div class="p-6 border-b border-orange-400">
            <div class="flex items-center space-x-3">
                <div
                    class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center font-bold text-lg shadow-lg flex-shrink-0">
                    GR
                </div>
                <div :class="[
                    'transition-all duration-300 overflow-hidden',
                    isCollapsed ? 'w-0 opacity-0' : 'w-auto opacity-100'
                ]">
                    <h1 class="font-semibold text-lg whitespace-nowrap">Gestor de Reclamações</h1>
                    <p class="text-orange-100 text-sm">Painel do Gestor</p>
                </div>
            </div>
        </div>

        <!-- Menu Sections -->
        <div class="flex-1 overflow-y-auto">
            <!-- Gestão de Casos Section -->
            <MenuSection :is-collapsed="isCollapsed" :stats="stats" @item-clicked="handleMenuItemClick" />

            <!-- Administrative Section -->
            <nav class="py-4 border-t border-orange-400">
                <div :class="[
                    'px-5 py-4 text-xs text-white font-semibold uppercase tracking-wide transition-opacity duration-300',
                    isCollapsed ? 'opacity-0' : 'opacity-100'
                ]">
                    Administrativo
                </div>

                <MenuItem :active="activePanel === 'tecnicos'" :icon="UserGroupIcon" :text="'Técnicos'"
                    :is-collapsed="isCollapsed" @click="handleItemClick('tecnicos')" />

                <MenuItem :active="activePanel === 'projetos'" :icon="FolderIcon" :text="'Projetos'"
                    :is-collapsed="isCollapsed" @click="handleItemClick('projetos')" />

                <MenuItem :active="activePanel === 'configuracoes'" :icon="Cog6ToothIcon" :text="'Configurações'"
                    :is-collapsed="isCollapsed" @click="handleItemClick('configuracoes')" />
            </nav>
        </div>

        <!-- User Info & Collapse Button -->
        <div class="p-4 border-t border-orange-400">
            <!-- User Info -->
            <div :class="[
                'flex items-center space-x-3 mb-4 transition-all duration-300',
                isCollapsed ? 'justify-center' : ''
            ]">
                <div
                    class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center text-white font-semibold flex-shrink-0">
                    <UserIcon class="w-5 h-5" />
                </div>
                <div :class="[
                    'transition-all duration-300 overflow-hidden',
                    isCollapsed ? 'w-0 opacity-0' : 'w-auto opacity-100'
                ]">
                    <div class="font-semibold text-sm">{{ user.name }}</div>
                    <div class="text-orange-100 text-xs">{{ user.role || 'Gestor de Reclamações' }}</div>
                </div>
            </div>

            <!-- Collapse Button -->
            <button @click="toggleSidebar" :class="[
                'w-full flex items-center justify-center space-x-2 p-3 rounded-lg text-orange-100 hover:bg-white/10 hover:text-white transition-all duration-200',
                isCollapsed ? 'justify-center' : ''
            ]">
                <ChevronDoubleLeftIcon :class="[
                    'w-5 h-5 transition-transform duration-300',
                    isCollapsed ? 'rotate-180' : ''
                ]" />
                <span :class="[
                    'text-sm font-medium transition-opacity duration-300',
                    isCollapsed ? 'opacity-0 w-0' : 'opacity-100'
                ]">
                    Recolher Menu
                </span>
            </button>
        </div>
    </aside>
</template>

<script setup>
import { ref, inject, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import {
    UserGroupIcon,
    FolderIcon,
    Cog6ToothIcon,
    UserIcon,
    ChevronDoubleLeftIcon
} from '@heroicons/vue/24/outline'
import MenuSection from './MenuSection.vue'
import MenuItem from './MenuItem.vue'

// Props
const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    stats: {
        type: Object,
        default: () => ({})
    }
})

// Emits
const emit = defineEmits(['toggle-sidebar'])

// Estado do dashboard
const dashboardState = inject('dashboardState')

// Estado local da sidebar
const isCollapsed = ref(false)

// Computed para painel ativo
const activePanel = computed(() => {
    return dashboardState?.activePanel?.value || 'dashboard'
})

// Função para alternar sidebar
const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value
    emit('toggle-sidebar', isCollapsed.value)
}

// Handlers para cliques nos itens do menu
const handleMenuItemClick = (item) => {
    console.log('Menu item clicked:', item)

    // Navegar para a rota correspondente
    if (item.id) {
        router.visit(route(`dashboard.${item.id}`))
    }
}

const handleItemClick = (panelId) => {
    if (dashboardState?.setActivePanel) {
        dashboardState.setActivePanel(panelId)
    }

    // Navegar para a rota
    router.visit(route(`dashboard.${panelId}`))
}
</script>