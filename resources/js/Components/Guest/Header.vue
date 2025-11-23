<template>
    <header class="bg-white shadow-sm fixed w-full top-0 z-50 h-16 md:h-24">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-0 h-full">
            <div class="flex justify-between items-center h-full">
                <!-- Logo - Tamanho menor em mobile -->
                <div class="flex-shrink-0">
                    <img src="/images/Logotipo-scaled.png" alt="Ícone de autenticação"
                        class="h-12 w-24 md:h-24 md:w-40 object-contain" />
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-8">
                        <a href="#inicio"
                            class="text-black hover:text-brand px-3 py-2 text-sm font-medium transition-colors duration-200"
                            :class="{ 'text-brand': activeSection === 'inicio' }" @click="scrollToSection('inicio')">
                            INÍCIO
                        </a>
                        <a href="#sobre"
                            class="text-black hover:text-brand px-3 py-2 text-sm font-medium transition-colors duration-200"
                            :class="{ 'text-brand': activeSection === 'sobre' }" @click="scrollToSection('sobre')">
                            SOBRE
                        </a>
                        <a href="#faq"
                            class="text-black hover:text-brand px-3 py-2 text-sm font-medium transition-colors duration-200"
                            :class="{ 'text-brand': activeSection === 'faq' }" @click="scrollToSection('faq')">
                            PERGUNTAS FREQUENTES
                        </a>
                        <a href="#contactos"
                            class="text-black hover:text-brand px-3 py-2 text-sm font-medium transition-colors duration-200"
                            :class="{ 'text-brand': activeSection === 'contactos' }"
                            @click="scrollToSection('contactos')">
                            CONTACTOS
                        </a>

                        <!-- Estado de Autenticação -->
                        <template v-if="$page.props.auth.user">
                            <!-- Usuário autenticado -->
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2 text-sm text-gray-700">
                                    <div
                                        class="w-8 h-8 bg-brand rounded-full flex items-center justify-center text-white text-xs font-bold">
                                        {{ getUserInitials($page.props.auth.user.name) }}
                                    </div>
                                    <span>{{ $page.props.auth.user.name }}</span>
                                </div>
                                <button @click="navigateToDashboard" :disabled="isLoading"
                                    class="bg-brand hover:bg-brand-dark text-white px-6 py-2 rounded-full text-sm font-semibold transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                    <div v-if="isLoading"
                                        class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>
                                    {{ isLoading ? 'A ENTRAR...' : 'IR AO PAINEL' }}
                                </button>
                            </div>
                        </template>
                        <template v-else>
                            <!-- Usuário não autenticado -->
                            <button @click="navigateToLogin" :disabled="isLoading"
                                class="bg-brand hover:bg-brand-dark text-white px-6 py-2 rounded-full text-sm font-semibold transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                <div v-if="isLoading"
                                    class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>
                                {{ isLoading ? 'A ENTRAR...' : 'ENTRAR' }}
                            </button>
                        </template>

                        <a href="/track"
                            class="text-xs md:text-sm text-gray-700 hover:text-brand font-medium flex items-center justify-center gap-2 transition-colors duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Acompanhar Reclamação
                        </a>
                        <a href="/email-test"
                            class="text-xs md:text-sm text-gray-700 hover:text-brand font-medium flex items-center justify-center gap-2 transition-colors duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Teste Emails
                        </a>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center gap-4">
                    <!-- Botão Acompanhar Reclamação em mobile -->
                    <a href="/track" class="text-gray-700 hover:text-brand transition-colors duration-200 p-2">
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
                            <button @click="navigateToDashboard" :disabled="isLoading"
                                class="text-xs bg-brand text-white px-3 py-1 rounded flex items-center gap-1">
                                <div v-if="isLoading" class="animate-spin rounded-full h-3 w-3 border-b-2 border-white">
                                </div>
                                {{ isLoading ? '' : 'PAINEL' }}
                            </button>
                        </div>
                    </template>
                    <template v-else>
                        <button @click="navigateToLogin" :disabled="isLoading"
                            class="text-xs bg-brand text-white px-3 py-1 rounded flex items-center gap-1">
                            <div v-if="isLoading" class="animate-spin rounded-full h-3 w-3 border-b-2 border-white">
                            </div>
                            {{ isLoading ? '' : 'ENTRAR' }}
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
                    <a href="#inicio"
                        class="text-gray-900 hover:text-brand hover:bg-gray-50 block px-4 py-3 rounded-lg text-base font-medium transition-all duration-200"
                        :class="{ 'text-brand bg-gray-50': activeSection === 'inicio' }"
                        @click="scrollToSection('inicio'); isMobileMenuOpen = false;">
                        INÍCIO
                    </a>
                    <a href="#sobre"
                        class="text-gray-900 hover:text-brand hover:bg-gray-50 block px-4 py-3 rounded-lg text-base font-medium transition-all duration-200"
                        :class="{ 'text-brand bg-gray-50': activeSection === 'sobre' }"
                        @click="scrollToSection('sobre'); isMobileMenuOpen = false;">
                        SOBRE
                    </a>
                    <a href="#faq"
                        class="text-gray-900 hover:text-brand hover:bg-gray-50 block px-4 py-3 rounded-lg text-base font-medium transition-all duration-200"
                        :class="{ 'text-brand bg-gray-50': activeSection === 'faq' }"
                        @click="scrollToSection('faq'); isMobileMenuOpen = false;">
                        PERGUNTAS FREQUENTES
                    </a>
                    <a href="#contactos"
                        class="text-gray-900 hover:text-brand hover:bg-gray-50 block px-4 py-3 rounded-lg text-base font-medium transition-all duration-200"
                        :class="{ 'text-brand bg-gray-50': activeSection === 'contactos' }"
                        @click="scrollToSection('contactos'); isMobileMenuOpen = false;">
                        CONTACTOS
                    </a>

                    <div class="pt-2 border-t border-gray-200">
                        <a href="/track"
                            class="text-gray-900 hover:text-brand hover:bg-gray-50 block px-4 py-3 rounded-lg text-base font-medium transition-all duration-200 flex items-center gap-3"
                            @click="isMobileMenuOpen = false">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Acompanhar Reclamação
                        </a>
                        <a href="/email-test"
                            class="text-gray-900 hover:text-brand hover:bg-gray-50 block px-4 py-3 rounded-lg text-base font-medium transition-all duration-200 flex items-center gap-3"
                            @click="isMobileMenuOpen = false">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Teste Emails
                        </a>
                    </div>

                    <div class="pt-2">
                        <template v-if="$page.props.auth.user">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 bg-brand rounded-full flex items-center justify-center text-white text-sm font-bold">
                                        {{ getUserInitials($page.props.auth.user.name) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $page.props.auth.user.name }}
                                        </p>
                                        <p class="text-xs text-gray-600">{{ $page.props.auth.user.email }}</p>
                                    </div>
                                </div>
                                <button @click="navigateToDashboard" :disabled="isLoading"
                                    class="bg-brand hover:bg-brand-dark text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                    <div v-if="isLoading"
                                        class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                                    {{ isLoading ? '' : 'PAINEL' }}
                                </button>
                            </div>
                        </template>
                        <template v-else>
                            <button @click="navigateToLogin" :disabled="isLoading"
                                class="w-full bg-brand hover:bg-brand-dark text-white px-4 py-3 rounded-lg text-base font-semibold text-center transition-all duration-200 hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                <div v-if="isLoading" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white">
                                </div>
                                {{ isLoading ? 'A ENTRAR...' : 'ENTRAR' }}
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Loading Overlay -->
        <div v-if="isLoading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
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
import { ref, onMounted, onUnmounted } from 'vue'
import { Bars3Icon } from '@heroicons/vue/24/outline'
import { router } from '@inertiajs/vue3'

const isMobileMenuOpen = ref(false)
const activeSection = ref('inicio')
const isLoading = ref(false)

// Método para obter iniciais do usuário
const getUserInitials = (name) => {
    if (!name) return 'U'
    const names = name.split(' ')
    if (names.length >= 2) {
        return (names[0][0] + names[names.length - 1][0]).toUpperCase()
    }
    return name.substring(0, 2).toUpperCase()
}

const navigateToLogin = () => {
    isLoading.value = true
    setTimeout(() => {
        router.visit('/login', {
            onFinish: () => {
                isLoading.value = false
            },
            onError: () => {
                isLoading.value = false
            }
        })
    }, 500)
}

const navigateToDashboard = () => {
    isLoading.value = true
    setTimeout(() => {
        router.visit('/home', {
            onFinish: () => {
                isLoading.value = false
            },
            onError: () => {
                isLoading.value = false
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

onMounted(() => {
    window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
})
</script>