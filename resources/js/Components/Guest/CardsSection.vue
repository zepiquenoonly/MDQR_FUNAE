<template>
    <section class="bg-gray-50 py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-10">
            <!-- Queixa Card -->
            <div
                class="bg-white p-10 rounded-2xl text-center shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                <div class="w-24 h-24 bg-brand rounded-full flex items-center justify-center mx-auto mb-8">
                    <ExclamationTriangleIcon class="h-12 w-12 text-white" />
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-6">QUEIXAS</h3>
                <p class="text-gray-600 leading-relaxed text-justify">
                    Através deste sistema, os munícipes podem fazer queixas de forma segura ou até anónima sobre
                    obras, projectos no seu bairro, município ou província. A sua voz será ouvida e as suas preocupações
                    serão tratadas com a devida atenção, contribuindo para a melhoria contínua da nossa comunidade.
                </p>
            </div>

            <!-- Sugestão Card -->
            <div
                class="bg-white p-10 rounded-2xl text-center shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                <div class="w-24 h-24 bg-brand rounded-full flex items-center justify-center mx-auto mb-8">
                    <ChatBubbleLeftRightIcon class="h-12 w-12 text-white" />
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-6">SUGESTÕES</h3>
                <p class="text-gray-600 leading-relaxed text-justify">
                    Através deste sistema, os munícipes podem fazer sugestões de forma segura ou até anónima sobre
                    obras, projectos no seu bairro, município ou província. A sua voz será ouvida e as suas preocupações
                    serão tratadas com a devida atenção, contribuindo para a melhoria contínua da nossa comunidade.
                </p>
            </div>

            <!-- Reclamação Card -->
            <div
                class="bg-white p-10 rounded-2xl text-center shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                <div class="w-24 h-24 bg-brand rounded-full flex items-center justify-center mx-auto mb-8">
                    <DocumentTextIcon class="h-12 w-12 text-white" />
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-6">RECLAMAÇÕES</h3>
                <p class="text-gray-600 leading-relaxed text-justify">
                    Através deste sistema, os munícipes podem fazer reclamações de forma segura ou até anónima sobre
                    obras, projectos no seu bairro, município ou província. A sua voz será ouvida e as suas preocupações
                    serão tratadas com a devida atenção, contribuindo para a melhoria contínua da nossa comunidade.
                </p>
            </div>
        </div>
    </section>
</template>

<script setup>
import {
    ExclamationTriangleIcon,
    ChatBubbleLeftRightIcon,
    DocumentTextIcon
} from '@heroicons/vue/24/outline'
</script>
