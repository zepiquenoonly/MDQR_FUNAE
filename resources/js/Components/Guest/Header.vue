<template>
    <header class="bg-white shadow-sm fixed w-full top-0 z-50 h-16 md:h-24">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-0 h-full">
            <div class="flex justify-between items-center h-full">
                <!-- Logo - Tamanho menor em mobile -->
                <div class="flex-shrink-0">
                    <a href="/">
                        <img src="/images/Logotipo-scaled.png" alt="Ícone de autenticação"
                            class="h-12 w-24 md:h-24 md:w-40 object-contain cursor-pointer" />
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-8">
                        <!-- Links que redirecionam para a página principal com âncoras -->
                        <a href="/#inicio"
                            class="text-black hover:text-brand px-3 py-2 text-sm font-medium transition-colors duration-200"
                            :class="{ 'text-brand': activeSection === 'inicio' }">
                            INÍCIO
                        </a>
                        <a href="/#sobre"
                            class="text-black hover:text-brand px-3 py-2 text-sm font-medium transition-colors duration-200"
                            :class="{ 'text-brand': activeSection === 'sobre' }">
                            SOBRE
                        </a>
                        <a href="/#faq"
                            class="text-black hover:text-brand px-3 py-2 text-sm font-medium transition-colors duration-200"
                            :class="{ 'text-brand': activeSection === 'faq' }">
                            PERGUNTAS FREQUENTES
                        </a>
                        <a href="/#contactos"
                            class="text-black hover:text-brand px-3 py-2 text-sm font-medium transition-colors duration-200"
                            :class="{ 'text-brand': activeSection === 'contactos' }">
                            CONTACTOS
                        </a>
                        <button @click="navigateToDashboard" :disabled="isLoading"
                            class="bg-brand hover:bg-brand-dark text-white px-6 py-2 rounded-full text-sm font-semibold transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg v-if="isLoading" class="animate-spin h-4 w-4 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <svg v-if="!isLoading && isAuthenticated" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            {{ isLoading ? 'A CARREGAR...' : getDashboardLabel() }}
                        </button>
                        <a href="/track"
                            class="text-xs md:text-sm text-gray-700 hover:text-brand font-medium flex items-center justify-center gap-2 transition-colors duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Acompanhar Reclamação
                        </a>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center gap-4">
                    <!-- Botão Acompanhar Reclamação em mobile -->
                    <a v-if="!hideTrackLink" @click="navigateToTrack" :disabled="isLoadingTrack"
                        class="text-gray-700 hover:text-brand transition-colors duration-200 p-2 cursor-pointer disabled:opacity-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </a>

                    <!-- Estado de Autenticação Mobile -->
                    <template v-if="$page.props.auth.user">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-8 h-8 bg-brand rounded-full flex items-center justify-center text-white text-xs font-bold">
                                {{ getUserInitials($page.props.auth.user.name) }}
                            </div>
                            <button @click="navigateToDashboard" :disabled="isLoadingDashboard"
                                class="text-xs bg-brand text-white px-3 py-1 rounded flex items-center gap-1 disabled:opacity-50">
                                <div v-if="isLoadingDashboard"
                                    class="animate-spin rounded-full h-3 w-3 border-b-2 border-white">
                                </div>
                                {{ isLoadingDashboard ? '' : 'PAINEL' }}
                            </button>
                        </div>
                    </template>
                    <template v-else>
                        <button @click="navigateToLogin" :disabled="isLoadingLogin"
                            class="text-xs bg-brand text-white px-3 py-1 rounded flex items-center gap-1 disabled:opacity-50">
                            <div v-if="isLoadingLogin"
                                class="animate-spin rounded-full h-3 w-3 border-b-2 border-white">
                            </div>
                            {{ isLoadingLogin ? '' : 'ENTRAR' }}
                        </button>
                    </template>

                    <button @click="isMobileMenuOpen = !isMobileMenuOpen"
                        class="text-gray-900 p-2 hover:text-brand transition-colors duration-200">
                        <Bars3Icon class="h-6 w-6" />
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div v-show="isMobileMenuOpen"
                class="md:hidden absolute top-16 left-0 right-0 bg-white shadow-lg border-t border-gray-200">
                <div class="px-4 pt-2 pb-4 space-y-3">
                    <!-- Links mobile que redirecionam para a página principal -->
                    <a href="/#inicio"
                        class="text-gray-900 hover:text-brand hover:bg-gray-50 block px-4 py-3 rounded-lg text-base font-medium transition-all duration-200"
                        :class="{ 'text-brand bg-gray-50': activeSection === 'inicio' }"
                        @click="isMobileMenuOpen = false">
                        INÍCIO
                    </a>
                    <a href="/#sobre"
                        class="text-gray-900 hover:text-brand hover:bg-gray-50 block px-4 py-3 rounded-lg text-base font-medium transition-all duration-200"
                        :class="{ 'text-brand bg-gray-50': activeSection === 'sobre' }"
                        @click="isMobileMenuOpen = false">
                        SOBRE
                    </a>
                    <a href="/#faq"
                        class="text-gray-900 hover:text-brand hover:bg-gray-50 block px-4 py-3 rounded-lg text-base font-medium transition-all duration-200"
                        :class="{ 'text-brand bg-gray-50': activeSection === 'faq' }" @click="isMobileMenuOpen = false">
                        PERGUNTAS FREQUENTES
                    </a>
                    <a href="/#contactos"
                        class="text-gray-900 hover:text-brand hover:bg-gray-50 block px-4 py-3 rounded-lg text-base font-medium transition-all duration-200"
                        :class="{ 'text-brand bg-gray-50': activeSection === 'contactos' }"
                        @click="isMobileMenuOpen = false">
                        CONTACTOS
                    </a>

                    <div class="pt-2 border-t border-gray-200">
                        <a v-if="!hideTrackLink" @click="handleMobileTrackClick" :disabled="isLoadingTrack"
                            class="text-gray-900 hover:text-brand hover:bg-gray-50 px-4 py-3 rounded-lg text-base font-medium transition-all duration-200 flex items-center gap-3 cursor-pointer disabled:opacity-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Acompanhar Reclamação
                        </a>
                    </div>

                    <div class="pt-2">
                        <button @click="navigateToDashboard" :disabled="isLoading"
                            class="w-full bg-brand hover:bg-brand-dark text-white px-4 py-3 rounded-lg text-base font-semibold text-center transition-all duration-200 hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <svg v-if="isLoading" class="animate-spin h-4 w-4 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <svg v-if="!isLoading && isAuthenticated" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            {{ isLoading ? 'A CARREGAR...' : getDashboardLabel() }}
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Loading Overlay -->
        <div v-if="isLoadingLogin || isLoadingDashboard || isLoadingTrack"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full mx-4">
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand"></div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">A Redirecionar</h3>
                    <p class="text-gray-600">Aguarde um momento...</p>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Bars3Icon } from '@heroicons/vue/24/outline'
import { router } from '@inertiajs/vue3'
import { useAuth } from '@/composables/useAuth'
import { useNavigation } from '@/composables/useNavigation'
import { getRoleLabel } from '@/utils/roles'

const props = defineProps({
    hideTrackLink: {
        type: Boolean,
        default: false
    }
})

const isMobileMenuOpen = ref(false)
const activeSection = ref('inicio')
const isLoading = ref(false)
const isLoadingDashboard = ref(false)
const isLoadingTrack = ref(false)
const isLoadingLogin = ref(false)

// Usar composables para auth e navegação
const { user, isAuthenticated, role } = useAuth()
const navigation = useNavigation({ user: user.value })
const { navigateToDashboard: navToDashboard, navigateToTracking, navigateToLogin: navToLogin } = navigation

const getUserInitials = (name) => {
    if (!name) return '?'
    const names = name.trim().split(' ')
    if (names.length === 1) return names[0].charAt(0).toUpperCase()
    return (names[0].charAt(0) + names[names.length - 1].charAt(0)).toUpperCase()
}

const getDashboardLabel = () => {
    if (!isAuthenticated.value) return 'ENTRAR'
    const roleLabel = getRoleLabel(role.value)
    return roleLabel === 'Utente' ? 'MEU DASHBOARD' : `DASHBOARD ${roleLabel.toUpperCase()}`
}

const navigateToDashboard = () => {
    isLoading.value = true
    isLoadingDashboard.value = true

    setTimeout(() => {
        navToDashboard({
            onFinish: () => {
                isLoading.value = false
                isLoadingDashboard.value = false
            },
            onError: () => {
                isLoading.value = false
                isLoadingDashboard.value = false
            }
        })
    }, 500)
}

const navigateToLogin = () => {
    isLoadingLogin.value = true

    setTimeout(() => {
        navToLogin({
            onFinish: () => {
                isLoadingLogin.value = false
            },
            onError: () => {
                isLoadingLogin.value = false
            }
        })
    }, 500)
}

const navigateToTrack = () => {
    isLoadingTrack.value = true
    setTimeout(() => {
        navigateToTracking({
            onFinish: () => {
                isLoadingTrack.value = false
            },
            onError: () => {
                isLoadingTrack.value = false
            }
        })
    }, 500)
}

const scrollToSection = (sectionId) => {
    const element = document.getElementById(sectionId)
    if (element) {
        const offsetTop = element.offsetTop - 100
        window.scrollTo({
            top: offsetTop,
            behavior: 'smooth'
        })
    }
}

const handleScroll = () => {
    const sections = ['inicio', 'sobre', 'faq', 'contactos']
    const scrollPosition = window.scrollY + 150

    sections.forEach(section => {
        const element = document.getElementById(section)
        if (element) {
            const offsetTop = element.offsetTop
            const offsetBottom = offsetTop + element.offsetHeight

            if (scrollPosition >= offsetTop && scrollPosition < offsetBottom) {
                activeSection.value = section
            }
        }
    })
}

// Funções para links mobile que fecham o menu
const handleMobileTrackClick = () => {
    navigateToTrack()
    isMobileMenuOpen.value = false
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
})
</script>
