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
                    Submeta queixas de forma segura — pode optar por permanecer anónimo. Use este canal para reportar
                    problemas locais (obras, ruídos, perigos) no seu bairro, município ou província. Vamos avaliar a
                    situação e agir para proteger os direitos e bem-estar da comunidade.
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
                    Partilhe ideias e propostas para melhorar serviços e projectos locais. As sugestões ajudam a
                    identificar oportunidades de melhoria e a orientar decisões — anónimas ou identificadas, todas
                    são bem-vindas e serão consideradas pela equipa responsável.
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
                    Registe reclamações formais quando um serviço ou projecto não cumpre o esperado. Forneça detalhes
                    e evidências (fotos ou documentos) para que a equipa possa investigar, acompanhar o caso e
                    fornecer uma resposta clara sobre as ações tomadas.
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
