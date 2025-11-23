<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    MagnifyingGlassIcon,
    InformationCircleIcon,
    DocumentTextIcon,
    MapPinIcon,
    UserIcon,
    CalendarIcon,
    ClockIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import StatusBadge from '@/Components/Grievance/StatusBadge.vue';
import UpdatesTimeline from '@/Components/Grievance/UpdatesTimeline.vue';
import AttachmentsGallery from '@/Components/Grievance/AttachmentsGallery.vue';
import Header from '@/Components/Guest/Header.vue';
import Footer from '@/Components/Guest/Footer.vue';

const referenceNumber = ref('');
const isLoading = ref(false);
const grievance = ref(null);
const error = ref('');
const showNotFoundModal = ref(false);

const searchGrievance = async () => {
    if (!referenceNumber.value.trim()) {
        error.value = 'Por favor, insira o código de rastreamento.';
        return;
    }

    error.value = '';
    isLoading.value = true;
    grievance.value = null;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        if (!csrfToken) {
            error.value = 'Erro de configuração. Por favor, recarregue a página.';
            console.error('CSRF token not found');
            return;
        }

        const response = await fetch(route('grievance.track.search'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                reference_number: referenceNumber.value.trim()
            })
        });

        if (!response.ok) {
            if (response.status === 419) {
                error.value = 'Sessão expirada. Por favor, recarregue a página.';
            } else {
                // Em vez de mostrar erro, mostra o modal
                showNotFoundModal.value = true;
            }
            console.error('HTTP Error:', response.status, response.statusText);
            return;
        }

        const data = await response.json();

        if (data.success) {
            grievance.value = data.grievance;
        } else {
            // Mostra modal em vez de mensagem de erro
            showNotFoundModal.value = true;
        }
    } catch (err) {
        // Mostra modal em caso de erro geral também
        showNotFoundModal.value = true;
        console.error('Fetch error:', err);
    } finally {
        isLoading.value = false;
    }
};

const handleKeyPress = (event) => {
    if (event.key === 'Enter') {
        searchGrievance();
    }
};

const resetSearch = () => {
    referenceNumber.value = '';
    grievance.value = null;
    error.value = '';
};

const closeNotFoundModal = () => {
    showNotFoundModal.value = false;
};

const categoryLabels = {
    'ambiental': 'Impacto Ambiental',
    'social': 'Impacto Social',
    'economico': 'Desenvolvimento dos Projectos'
};
</script>

<template>

    <Head title="Acompanhar Reclamação" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
        <Header hide-track-link />

        <!-- Hero Section -->
        <div class="bg-brand text-white mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="text-center">
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-4">
                        Acompanhar Reclamação
                    </h1>
                    <p class="text-base sm:text-xl text-white max-w-2xl mx-auto">
                        Consulte o estado da sua reclamação em tempo real
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12 pb-16">
            <!-- Search Card -->
            <div class="bg-white rounded shadow-xl p-8 mb-8 border border-gray-100">
                <div class="max-w-3xl mx-auto">
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 bg-brand/10 rounded-full flex items-center justify-center mx-auto mb-4">
                            <MagnifyingGlassIcon class="w-8 h-8 text-brand" />
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">
                            Localizar Reclamação
                        </h2>
                        <p class="text-gray-600">
                            Insira o código de rastreamento único da sua reclamação
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="reference-number" class="block text-sm font-semibold text-gray-700 mb-2">
                                Código de Rastreamento
                            </label>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <div class="flex-1 relative">
                                    <input id="reference-number" v-model="referenceNumber" type="text"
                                        placeholder="Ex: GRM-2025-XXXXXXXX" @keypress="handleKeyPress"
                                        class="w-full px-4 py-4 border border-gray-300 rounded focus:ring-3 focus:ring-brand/20 focus:border-brand transition-all duration-200 uppercase font-mono text-lg shadow-sm"
                                        :disabled="isLoading" />
                                    <div class="absolute inset-y-0 right-3 flex items-center">
                                        <DocumentTextIcon class="w-5 h-5 text-gray-400" />
                                    </div>
                                </div>

                                <button @click="searchGrievance" :disabled="isLoading || !referenceNumber.trim()"
                                    class="sm:px-8 px-4 py-4 bg-brand text-white font-semibold rounded hover:bg-brand-dark focus:outline-none focus:ring-3 focus:ring-brand/20 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center gap-3 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 sm:w-auto w-full">
                                    <MagnifyingGlassIcon class="w-5 h-5" />
                                    {{ isLoading ? 'Buscando...' : 'Buscar' }}
                                </button>
                            </div>
                        </div>

                        <!-- Error Message (apenas para erros de validação) -->
                        <div v-if="error"
                            class="p-4 bg-red-50 border border-red-200 rounded text-red-700 text-sm flex items-start gap-3 animate-pulse">
                            <InformationCircleIcon class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" />
                            <span>{{ error }}</span>
                        </div>

                        <!-- Info Box -->
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded flex items-start gap-3">
                            <InformationCircleIcon class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                            <div>
                                <p class="text-sm text-blue-700 font-medium mb-1">
                                    Onde encontrar o código de rastreamento?
                                </p>
                                <p class="text-sm text-blue-600">
                                    O código foi enviado para o seu email após a submissão da reclamação.
                                    Formato: <strong class="font-mono">GRM-AAAA-XXXXXXXX</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Section -->
            <div v-if="grievance" class="space-y-8 animate-fade-in">
                <!-- Grievance Overview Card -->
                <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 mb-6">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-12 h-12 bg-brand/10 rounded-xl flex items-center justify-center">
                                    <DocumentTextIcon class="w-6 h-6 text-brand" />
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-900 font-mono">
                                        {{ grievance.reference_number }}
                                    </h2>
                                    <p class="text-gray-600 flex items-center gap-2 mt-1">
                                        <CalendarIcon class="w-4 h-4" />
                                        Submetida em {{ new Date(grievance.submitted_at).toLocaleDateString('pt-PT', {
                                        day: '2-digit',
                                        month: 'long',
                                        year: 'numeric'
                                        }) }}
                                        <ClockIcon class="w-4 h-4 ml-2" />
                                        {{ new Date(grievance.submitted_at).toLocaleTimeString('pt-PT', {
                                        hour: '2-digit',
                                        minute: '2-digit'
                                        }) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <StatusBadge :status="grievance.status" :label="grievance.status_label" size="lg" />
                    </div>

                    <!-- Metadata Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                        <!-- Category -->
                        <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <DocumentTextIcon class="w-5 h-5 text-purple-600" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Categoria</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ categoryLabels[grievance.category] || grievance.category }}
                                </p>
                                <p v-if="grievance.subcategory" class="text-xs text-gray-600">
                                    {{ grievance.subcategory }}
                                </p>
                            </div>
                        </div>

                        <!-- Location -->
                        <div v-if="grievance.province" class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <MapPinIcon class="w-5 h-5 text-blue-600" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Localização</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ grievance.province }}
                                    <span v-if="grievance.district" class="text-gray-600"> - {{ grievance.district
                                        }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Assigned Technician -->
                        <div v-if="grievance.assigned_user" class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <UserIcon class="w-5 h-5 text-green-600" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Técnico Responsável</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ grievance.assigned_user.name }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <DocumentTextIcon class="w-5 h-5 text-gray-500" />
                            Descrição da Reclamação
                        </h3>
                        <div class="prose prose-gray max-w-none text-gray-700 bg-gray-50 rounded-xl p-6"
                            v-html="grievance.description"></div>
                    </div>

                    <!-- Resolution Notes -->
                    <div v-if="grievance.resolution_notes && grievance.status === 'resolved'"
                        class="border-t border-gray-200 pt-6 mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <InformationCircleIcon class="w-5 h-5 text-green-600" />
                            Notas de Resolução
                        </h3>
                        <div class="bg-green-50 border border-green-200 rounded-xl p-6">
                            <p class="text-gray-700 leading-relaxed">{{ grievance.resolution_notes }}</p>
                            <div v-if="grievance.resolved_by"
                                class="flex items-center gap-2 mt-4 pt-4 border-t border-green-200">
                                <UserIcon class="w-4 h-4 text-green-600" />
                                <span class="text-sm text-gray-600">
                                    Resolvida por: <strong>{{ grievance.resolved_by.name }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Two Column Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Updates Timeline -->
                    <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                        <UpdatesTimeline :updates="grievance.updates" />
                    </div>

                    <!-- Attachments -->
                    <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                        <AttachmentsGallery :attachments="grievance.attachments" />
                    </div>
                </div>

                <!-- Action Button -->
                <div class="flex justify-center pt-4">
                    <button @click="resetSearch"
                        class="px-8 py-4 bg-white text-gray-700 font-semibold rounded-xl hover:bg-gray-50 border border-gray-200 transition-all duration-200 flex items-center gap-3 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <MagnifyingGlassIcon class="w-5 h-5" />
                        Consultar Outra Reclamação
                    </button>
                </div>
            </div>
        </main>

        <Footer />

        <!-- Modal para código não encontrado -->
        <div v-if="showNotFoundModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 animate-fade-in">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-auto transform animate-scale-in">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                            <XMarkIcon class="w-6 h-6 text-red-600" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Código Não Encontrado
                        </h3>
                    </div>
                    <button @click="closeNotFoundModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <MagnifyingGlassIcon class="w-8 h-8 text-red-500" />
                        </div>
                        <h4 class="text-xl font-semibold text-gray-900 mb-2">
                            O código de rastreio não foi encontrado
                        </h4>
                        <p class="text-gray-600 mb-6">
                            Verifique se o código está correto e tente novamente. O código foi enviado para o seu email
                            após a submissão da reclamação.
                        </p>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <InformationCircleIcon class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                            <div>
                                <p class="text-sm font-medium text-blue-800 mb-1">Formato do código:</p>
                                <p class="text-sm text-blue-700 font-mono">GRM-AAAA-XXXXXXXX</p>
                                <p class="text-xs text-blue-600 mt-2">
                                    Onde AAAA é o ano e XXXXXXXX é um código único de 8 caracteres
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex gap-3 p-6 border-t border-gray-200">
                    <button @click="closeNotFoundModal"
                        class="flex-1 px-4 py-3 bg-brand text-white font-semibold rounded-lg hover:bg-brand-dark transition-colors duration-200 focus:outline-none focus:ring-3 focus:ring-brand/20">
                        Tentar Novamente
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.prose img {
    max-width: 100%;
    height: auto;
    border-radius: 0.75rem;
    margin: 1rem 0;
}

.prose p {
    margin-bottom: 1rem;
    line-height: 1.6;
}

.prose ul,
.prose ol {
    margin: 1rem 0;
    padding-left: 1.5rem;
}

.prose li {
    margin-bottom: 0.5rem;
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes scale-in {
    from {
        opacity: 0;
        transform: scale(0.9);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}

.animate-scale-in {
    animation: scale-in 0.2s ease-out;
}
</style>