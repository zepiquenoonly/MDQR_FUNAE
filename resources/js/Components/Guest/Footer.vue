<template>
    <footer id="contactos" class="bg-brand text-white py-12 md:py-16 px-4 sm:px-6 lg:px-8">

        <!-- GRID PRINCIPAL -->
        <div class="max-w-7xl mx-auto 
                   grid grid-cols-2 
                   gap-8 
                   sm:grid-cols-2 
                   md:grid-cols-4">

            <!-- 1ª LINHA - COLUNA 1 (Logo + Descrição) -->
            <div class="col-span-1 md:col-span-1">
                <div class="flex flex-col items-start gap-4">
                    <!-- Logo -->
                    <img src="/images/Logotipo-scaled.png" alt="Logo"
                        class="h-10 w-20 md:h-10 md:w-40 object-contain" />

                    <!-- Descrição - Em mobile fica lado a lado, em desktop abaixo -->
                    <p class="text-white/90 leading-relaxed 
                              text-sm md:text-base 
                              text-left
                              md:mt-2">
                        Trabalhando juntos pelo progresso e desenvolvimento sustentável de Moçambique.
                    </p>
                </div>
            </div>

            <!-- 1ª LINHA - COLUNA 2 (SERVIÇOS) -->
            <div class="col-span-1 md:col-span-1 text-left">
                <h3 class="text-lg font-semibold mb-4 text-left">SERVIÇOS</h3>
                <ul class="space-y-3">
                    <li><a class="block text-left text-white/80 hover:text-white">Sugestão</a></li>
                    <li><a class="block text-left text-white/80 hover:text-white">Reclamação</a></li>
                    <li><a class="block text-left text-white/80 hover:text-white">Queixa</a></li>
                </ul>
            </div>

            <!-- 2ª LINHA - COLUNA 1 (LINKS ÚTEIS) -->
            <div class="col-span-1 md:col-span-1 text-left">
                <h3 class="text-lg font-semibold mb-4 text-left">LINKS ÚTEIS</h3>
                <ul class="space-y-3">
                    <li><a class="block text-left text-white/80 hover:text-white">Conselho Municipal</a>
                    </li>
                    <li><a class="block text-left text-white/80 hover:text-white">Assembleia da
                            República</a></li>
                    <li><a class="block text-left text-white/80 hover:text-white">Presidência</a></li>
                </ul>
            </div>

            <!-- 2ª LINHA - COLUNA 2 (CONTACTOS) -->
            <div class="col-span-1 md:col-span-1 text-left">
                <h3 class="text-lg font-semibold mb-4 text-left">CONTACTOS</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-start gap-3 text-white/80 hover:text-white">
                        <HomeIcon class="h-5 w-5 flex-shrink-0" />
                        <span>Maputo, MZ</span>
                    </div>
                    <div class="flex items-center justify-start gap-3 text-white/80 hover:text-white">
                        <EnvelopeIcon class="h-5 w-5 flex-shrink-0" />
                        <span>info@enabel.co.mz</span>
                    </div>
                    <div class="flex items-center justify-start gap-3 text-white/80 hover:text-white">
                        <PhoneIcon class="h-5 w-5 flex-shrink-0" />
                        <span>+258 21 123 456</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- BARRA INFERIOR -->
        <div class="max-w-7xl mx-auto mt-10 pt-6 border-t border-white/20">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-white/70 text-sm text-center md:text-left">
                    © 2025 FUNAE. Todos os direitos reservados.
                </p>
                <div class="flex space-x-6">
                    <a class="text-white/70 hover:text-white text-sm">Termos de Serviço</a>
                    <a class="text-white/70 hover:text-white text-sm">Política de Privacidade</a>
                    <a href="/email-test" class="text-white/50 hover:text-white/70 text-xs opacity-50">Teste Email</a>
                </div>
            </div>
        </div>

    </footer>
</template>

<script setup>
import { HomeIcon, EnvelopeIcon, PhoneIcon } from '@heroicons/vue/24/outline'
</script>