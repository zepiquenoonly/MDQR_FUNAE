<template>
    <aside class="bg-brand dark:bg-dark-secondary text-white h-full flex flex-col overflow-hidden">
        <!-- Brand/Logo Section -->
        <div class="p-4 sm:p-6 flex-shrink-0 overflow-hidden">
            <div class="flex items-center space-x-2 sm:space-x-3 min-w-0">
                <!-- Botão de fechar para mobile (apenas no overlay) -->
                <button v-if="isMobile" @click="toggleSidebar"
                    class="p-2 flex items-center justify-center hover:bg-white/30 transition-colors flex-shrink-0 rounded">
                    <XMarkIcon class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                </button>

                <!-- Logo quando em mobile overlay -->
                <div v-if="isMobile" class="flex items-center space-x-2 sm:space-x-3 flex-1 min-w-0">
                    <div
                        class="w-8 h-8 sm:w-12 sm:h-12 bg-white/20 rounded-lg flex items-center justify-center font-bold text-sm sm:text-lg shadow-lg flex-shrink-0">
                        GR
                    </div>
                    <div class="transition-all duration-300 overflow-hidden min-w-0 flex-1">
                        <h1 class="font-semibold text-base sm:text-lg whitespace-nowrap truncate">Gestor de Reclamações
                        </h1>
                        <p class="text-orange-100 dark:text-orange-200 text-xs sm:text-sm truncate">Painel do Gestor</p>
                    </div>
                </div>

                <!-- Logo normal para desktop -->
                <div v-else class="flex items-center space-x-2 sm:space-x-3 flex-1 min-w-0">
                    <div
                        class="w-8 h-8 sm:w-12 sm:h-12 bg-white/20 rounded-lg flex items-center justify-center font-bold text-sm sm:text-lg shadow-lg flex-shrink-0">
                        GR
                    </div>
                    <div v-if="!isCollapsed" class="transition-all duration-300 overflow-hidden min-w-0 flex-1">
                        <h1 class="font-semibold text-base sm:text-lg whitespace-nowrap truncate">Gestor de Reclamações
                        </h1>
                        <p class="text-orange-100 dark:text-orange-200 text-xs sm:text-sm truncate">Painel do Gestor</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Sections -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden">
            <!-- Gestão de Casos Section -->
            <MenuSection :is-collapsed="isCollapsed && !isMobile" :stats="stats" @item-clicked="handleMenuItemClick" />
        </div>
    </aside>
</template>

<script setup>
import { inject, computed, ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import {
    Bars3Icon,
    XMarkIcon
} from '@heroicons/vue/24/outline'
import MenuSection from './MenuSection.vue'

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
const isMobile = ref(false)

// Computed para painel ativo
const activePanel = computed(() => {
    return dashboardState?.activePanel?.value || 'dashboard'
})

// Detectar mobile
const checkMobile = () => {
    isMobile.value = window.innerWidth < 640
}

// Função para alternar sidebar
const toggleSidebar = () => {
    console.log('Toggle sidebar clicked in Sidebar component')
    emit('toggle-sidebar')
}

// Handlers para cliques nos itens do menu
const handleMenuItemClick = (item) => {
    console.log('Menu item clicked:', item)

    // Em mobile, fechar a sidebar após clicar em um item
    if (isMobile.value) {
        emit('toggle-sidebar', true)
    }

    // Navegar para a rota correspondente usando Inertia
    if (item.href) {
        router.visit(item.href)
    } else if (item.id) {
        router.visit(route(`dashboard.${item.id}`))
    }
}

// Lifecycle
onMounted(() => {
    checkMobile()
    window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile)
})
</script>