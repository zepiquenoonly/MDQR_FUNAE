<template>
    <header class="bg-white shadow-sm fixed w-full top-0 z-50 h-24">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-0 h-full">
            <div class="flex justify-between items-center h-full">
                <div class="flex-shrink-0t">
                    <img src="/images/Emblem_of_Mozambique.svg-2.png" alt="Ícone de autenticação"
                        class="md:h-16 md:w-16 object-contain" />
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-8">
                        <a href="#inicio"
                            class="text-black hover:text-brand px-3 py-2 text-sm font-medium transition-colors scroll-smooth"
                            :class="{ 'text-brand': activeSection === 'inicio' }" @click="scrollToSection('inicio')">
                            INÍCIO
                        </a>
                        <a href="#sobre"
                            class="text-brand hover:text-brand px-3 py-2 text-sm font-medium transition-colors scroll-smooth"
                            :class="{ 'text-brand': activeSection === 'sobre' }" @click="scrollToSection('sobre')">
                            SOBRE
                        </a>
                        <a href="#faq"
                            class="text-black hover:text-brand px-3 py-2 text-sm font-medium transition-colors scroll-smooth"
                            :class="{ 'text-brand': activeSection === 'faq' }" @click="scrollToSection('faq')">
                            PERGUNTAS FREQUENTES
                        </a>
                        <a href="#contactos"
                            class="text-black hover:text-brand px-3 py-2 text-sm font-medium transition-colors scroll-smooth"
                            :class="{ 'text-brand': activeSection === 'contactos' }"
                            @click="scrollToSection('contactos')">
                            CONTACTOS
                        </a>
                        <a href="/login"
                            class="bg-brand hover:bg-orange-700 text-white px-6 py-2 rounded-full text-sm font-semibold transition-all hover:shadow-lg hover:-translate-y-0.5">
                            ENTRAR
                        </a>
                        <a 
                            href="/track"
                            class="text-xs md:text-sm text-gray-700 hover:text-brand font-medium flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Acompanhar Reclamação
                        </a>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="text-gray-900 p-2">
                        <Bars3Icon class="h-6 w-6" />
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div v-show="isMobileMenuOpen" class="md:hidden">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-white border-t">
                    <a href="#inicio" class="text-gray-900 hover:text-teal-600 block px-3 py-2 text-base font-medium"
                        @click="scrollToSection('inicio'); isMobileMenuOpen = false;">
                        INÍCIO
                    </a>
                    <a href="#sobre" class="text-gray-900 hover:text-teal-600 block px-3 py-2 text-base font-medium"
                        @click="scrollToSection('sobre'); isMobileMenuOpen = false;">
                        SOBRE
                    </a>
                    <a href="#faq" class="text-gray-900 hover:text-teal-600 block px-3 py-2 text-base font-medium"
                        @click="scrollToSection('faq'); isMobileMenuOpen = false;">
                        PERGUNTAS FREQUENTES
                    </a>
                    <a href="#contactos" class="text-gray-900 hover:text-teal-600 block px-3 py-2 text-base font-medium"
                        @click="scrollToSection('contactos'); isMobileMenuOpen = false;">
                        CONTACTOS
                    </a>
                    <a href="/track"
                        class="text-gray-900 hover:text-teal-600 block px-3 py-2 text-base font-medium flex items-center gap-2"
                        @click="isMobileMenuOpen = false">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Acompanhar Reclamação
                    </a>
                    <a href="/login"
                        class="bg-teal-600 hover:bg-teal-700 text-white block px-3 py-2 rounded-full text-base font-semibold text-center mt-4"
                        @click="isMobileMenuOpen = false">
                        ENTRAR
                    </a>
                </div>
            </div>
        </nav>
    </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Bars3Icon } from '@heroicons/vue/24/outline'

const isMobileMenuOpen = ref(false)
const activeSection = ref('inicio')

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