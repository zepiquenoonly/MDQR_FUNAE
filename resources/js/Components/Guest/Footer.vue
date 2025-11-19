<template>
    <footer id="contactos" class="bg-brand text-white py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-5 gap-12">
            <!-- Logo and Description -->
            <div class="md:col-span-2">
                <div class="flex-shrink-0t">
                    <img src="/images/Emblem_of_Mozambique.svg-2.png" alt="Ícone de autenticação"
                        class="md:h-20 md:w-20 object-contain" />
                </div>
                <p class="text-white leading-relaxed max-w-md mt-4">
                    Trabalhando juntos pelo progresso e desenvolvimento sustentável de Moçambique.
                </p>
            </div>

            <!-- Serviços -->
            <div>
                <h3 class="text-lg font-semibold mb-6">SERVIÇOS</h3>
                <ul class="space-y-3">
                    <li><a href="#" class="text-white hover:text-white transition-colors">Sugestão</a></li>
                    <li><a href="#" class="text-white hover:text-white transition-colors">Reclamação</a></li>
                    <li><a href="#" class="text-white hover:text-white transition-colors">Denúncia</a></li>
                </ul>
            </div>

            <!-- Useful Links -->
            <div>
                <h3 class="text-lg font-semibold mb-6">LINKS ÚTEIS</h3>
                <ul class="space-y-3">
                    <li><a href="#" class="text-white hover:text-white transition-colors">Conselho Municipal</a>
                    </li>
                    <li><a href="#" class="text-white hover:text-white transition-colors">Assembleia da
                            República</a></li>
                    <li><a href="#" class="text-white hover:text-white transition-colors">Presidência</a></li>
                </ul>
            </div>

            <!-- Contacts -->
            <div>
                <h3 class="text-lg font-semibold mb-6">CONTACTOS</h3>
                <div class="space-y-4">
                    <div class="flex items-center gap-3 text-white">
                        <HomeIcon class="h-5 w-5" />
                        <span>Maputo, MZ</span>
                    </div>
                    <div class="flex items-center gap-3 text-white">
                        <EnvelopeIcon class="h-5 w-5" />
                        <span>info@enabel.co.mz</span>
                    </div>
                    <div class="flex items-center gap-3 text-white">
                        <PhoneIcon class="h-5 w-5" />
                        <span>+258 21 123 456</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { HomeIcon, EnvelopeIcon, PhoneIcon } from '@heroicons/vue/24/outline'
</script>