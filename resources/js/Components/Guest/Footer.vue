<template>
    <footer id="contactos" class="bg-brand text-white py-12 md:py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-6 sm:gap-8 md:gap-12">
            <!-- Logo and Description - Ocupa 2 colunas em mobile -->
            <div class="sm:col-span-2 md:col-span-2">
                <div class="block items-start gap-4">
                    <div class="flex-shrink-0">
                        <img src="/images/Emblem_of_Mozambique.svg-2.png" alt="Brasão de Moçambique"
                            class="h-16 w-16 md:h-20 md:w-20 object-contain" />
                    </div>
                    <div>
                        <p class="text-white/90 leading-relaxed text-sm md:text-base mt-8">
                            Trabalhando juntos pelo progresso e desenvolvimento sustentável de Moçambique.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Serviços -->
            <div>
                <h3 class="text-lg font-semibold mb-4 md:mb-6 text-white">SERVIÇOS</h3>
                <ul class="space-y-2 md:space-y-3">
                    <li>
                        <a href="#"
                            class="text-white/80 hover:text-white transition-colors duration-200 text-sm md:text-base block py-1">
                            Sugestão
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="text-white/80 hover:text-white transition-colors duration-200 text-sm md:text-base block py-1">
                            Reclamação
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="text-white/80 hover:text-white transition-colors duration-200 text-sm md:text-base block py-1">
                            Queixa
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Useful Links -->
            <div>
                <h3 class="text-lg font-semibold mb-4 md:mb-6 text-white">LINKS ÚTEIS</h3>
                <ul class="space-y-2 md:space-y-3">
                    <li>
                        <a href="#"
                            class="text-white/80 hover:text-white transition-colors duration-200 text-sm md:text-base block py-1">
                            Conselho Municipal
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="text-white/80 hover:text-white transition-colors duration-200 text-sm md:text-base block py-1">
                            Assembleia da República
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="text-white/80 hover:text-white transition-colors duration-200 text-sm md:text-base block py-1">
                            Presidência
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Contacts - Ocupa 2 colunas em mobile -->
            <div class="sm:col-span-2 md:col-span-1">
                <h3 class="text-lg font-semibold mb-4 md:mb-6 text-white">CONTACTOS</h3>
                <div class="space-y-3 md:space-y-4">
                    <div class="flex items-center gap-3 text-white/80 hover:text-white transition-colors duration-200">
                        <HomeIcon class="h-4 w-4 md:h-5 md:w-5 flex-shrink-0" />
                        <span class="text-sm md:text-base">Maputo, MZ</span>
                    </div>
                    <div class="flex items-center gap-3 text-white/80 hover:text-white transition-colors duration-200">
                        <EnvelopeIcon class="h-4 w-4 md:h-5 md:w-5 flex-shrink-0" />
                        <span class="text-sm md:text-base">info@enabel.co.mz</span>
                    </div>
                    <div class="flex items-center gap-3 text-white/80 hover:text-white transition-colors duration-200">
                        <PhoneIcon class="h-4 w-4 md:h-5 md:w-5 flex-shrink-0" />
                        <span class="text-sm md:text-base">+258 21 123 456</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="max-w-7xl mx-auto mt-8 md:mt-12 pt-6 md:pt-8 border-t border-white/20">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-white/70 text-sm text-center md:text-left">
                    © 2024 Gestor de Reclamações. Todos os direitos reservados.
                </p>
                <div class="flex space-x-6">
                    <a href="#" class="text-white/70 hover:text-white transition-colors duration-200">
                        <span class="text-sm">Termos de Serviço</span>
                    </a>
                    <a href="#" class="text-white/70 hover:text-white transition-colors duration-200">
                        <span class="text-sm">Política de Privacidade</span>
                    </a>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { HomeIcon, EnvelopeIcon, PhoneIcon } from '@heroicons/vue/24/outline'
</script>
