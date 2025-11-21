<template>
    <aside :class="[
        'bg-brand text-white shadow-xl transition-all duration-300 flex flex-col h-screen sticky top-0 z-40 overflow-x-hidden',
        isCollapsed ? 'w-20' : 'w-64'
    ]">
        <!-- Brand/Logo Section -->
        <div class="p-6 border-b border-orange-400 flex-shrink-0 overflow-hidden">
            <div class="flex items-center space-x-3 min-w-0">
                <!-- Botão hambúrguer quando sidebar está recolhida -->
                <button v-if="isCollapsed" @click="toggleSidebar"
                    class="p-3flex items-center justify-center hover:bg-white/30 transition-colors flex-shrink-0">
                    <Bars3Icon class="w-6 h-6 text-white" />
                </button>

                <!-- Logo quando sidebar está expandida -->
                <div v-else class="flex items-center space-x-3 flex-1 min-w-0">
                    <div
                        class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center font-bold text-lg shadow-lg flex-shrink-0">
                        GR
                    </div>
                    <div class="transition-all duration-300 overflow-hidden min-w-0 flex-1">
                        <h1 class="font-semibold text-lg whitespace-nowrap truncate">Gestor de Reclamações</h1>
                        <p class="text-orange-100 text-sm truncate">Painel do Gestor</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Sections -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden">
            <!-- Gestão de Casos Section -->
            <MenuSection :is-collapsed="isCollapsed" :stats="stats" @item-clicked="handleMenuItemClick" />
        </div>
    </aside>
</template>

<script setup>
import { inject, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import {
    UserGroupIcon,
    FolderIcon,
    Cog6ToothIcon,
    Bars3Icon
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
    },
    isCollapsed: {
        type: Boolean,
        default: false
    }
})

// Emits
const emit = defineEmits(['toggle-sidebar'])

// Estado do dashboard e sidebar injetados
const dashboardState = inject('dashboardState')
const sidebarState = inject('sidebarState')

// Computed para painel ativo
const activePanel = computed(() => {
    return dashboardState?.activePanel?.value || 'dashboard'
})

// Função para alternar sidebar
const toggleSidebar = () => {
    console.log('Toggle sidebar clicked in Sidebar component')
    emit('toggle-sidebar')
}

// Handlers para cliques nos itens do menu
const handleMenuItemClick = (item) => {
    console.log('Menu item clicked:', item)

    // Navegar para a rota correspondente usando Inertia (que ativará o loading)
    if (item.href) {
        router.visit(item.href)
    } else if (item.id) {
        // Fallback para IDs de rota
        router.visit(route(`dashboard.${item.id}`))
    }
}

const handleItemClick = (panelId) => {
    // Usar Inertia para navegação com o parâmetro panel
    if (panelId === 'tecnicos') {
        router.visit('/gestor/dashboard?panel=tecnicos')
    } else if (panelId === 'projetos') {
        router.visit('/gestor/dashboard?panel=projectos')
    } else {
        router.visit(route(`dashboard.${panelId}`))
    }

    // Atualizar estado local se necessário
    if (dashboardState?.setActivePanel) {
        dashboardState.setActivePanel(panelId)
    }
}
</script>