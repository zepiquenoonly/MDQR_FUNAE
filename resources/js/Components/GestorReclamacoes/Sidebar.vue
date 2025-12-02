<template>
    <aside class="glass rounded-2xl h-full flex flex-col overflow-hidden shadow-glass m-3">
        <!-- Brand/Logo Section -->
        <div class="p-4 sm:p-6 flex-shrink-0 overflow-hidden bg-gradient-to-br from-primary-50 to-orange-50 rounded-t-2xl">
            <div class="flex items-center space-x-2 sm:space-x-3 min-w-0">
                <!-- Botão de fechar para mobile (apenas no overlay) -->
                <button v-if="isMobile" @click="toggleSidebar"
                    class="p-2 flex items-center justify-center hover:glass transition-all flex-shrink-0 rounded-xl text-primary-600">
                    <XMarkIcon class="w-5 h-5 sm:w-6 sm:h-6" />
                </button>

                <!-- Logo quando em mobile overlay -->
                <div v-if="isMobile" class="flex items-center space-x-2 sm:space-x-3 flex-1 min-w-0">
                    <div
                        class="w-8 h-8 sm:w-12 sm:h-12 bg-gradient-to-br from-primary-500 to-orange-600 rounded-xl flex items-center justify-center font-bold text-sm sm:text-lg shadow-glass text-white flex-shrink-0">
                        {{ logoInitials }}
                    </div>
                    <div class="transition-all duration-300 overflow-hidden min-w-0 flex-1">
                        <h1 class="font-semibold text-base sm:text-lg whitespace-nowrap truncate text-gray-900">{{ dashboardTitle }}
                        </h1>
                        <p class="text-primary-600 text-xs sm:text-sm truncate">{{ dashboardSubtitle }}</p>
                    </div>
                </div>

                <!-- Logo normal para desktop -->
                <div v-else class="flex items-center space-x-2 sm:space-x-3 flex-1 min-w-0">
                    <div
                        class="w-8 h-8 sm:w-12 sm:h-12 bg-gradient-to-br from-primary-500 to-orange-600 rounded-xl flex items-center justify-center font-bold text-sm sm:text-lg shadow-glass text-white flex-shrink-0">
                        {{ logoInitials }}
                    </div>
                    <div v-if="!isCollapsed" class="transition-all duration-300 overflow-hidden min-w-0 flex-1">
                        <h1 class="font-semibold text-base sm:text-lg whitespace-nowrap truncate text-gray-900">{{ dashboardTitle }}
                        </h1>
                        <p class="text-primary-600 text-xs sm:text-sm truncate">{{ dashboardSubtitle }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Sections -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden scrollbar-thin">
            <!-- Gestão de Casos Section -->
            <MenuSection :is-collapsed="isCollapsed && !isMobile" :stats="stats" :user="user" @item-clicked="handleMenuItemClick" />
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

// Computed para determinar role do usuário
const userRole = computed(() => {
    return props.user?.roles?.[0]?.name || props.user?.role || 'Gestor'
})

// Computed para título do dashboard baseado no role
const dashboardTitle = computed(() => {
    const titles = {
        'PCA': 'Dashboard PCA',
        'Gestor': 'Gestor de Reclamações',
        'Técnico': 'Dashboard Técnico'
    }
    return titles[userRole.value] || 'Dashboard'
})

// Computed para subtítulo do dashboard
const dashboardSubtitle = computed(() => {
    const subtitles = {
        'PCA': 'Painel Executivo',
        'Gestor': 'Painel do Gestor',
        'Técnico': 'Painel do Técnico'
    }
    return subtitles[userRole.value] || 'Painel'
})

// Computed para iniciais do logo
const logoInitials = computed(() => {
    const initials = {
        'PCA': 'PCA',
        'Gestor': 'GR',
        'Técnico': 'TEC'
    }
    return initials[userRole.value] || 'GR'
})

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