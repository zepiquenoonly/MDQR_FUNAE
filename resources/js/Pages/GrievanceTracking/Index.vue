<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { MagnifyingGlassIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';
import StatusBadge from '@/Components/Grievance/StatusBadge.vue';
import UpdatesTimeline from '@/Components/Grievance/UpdatesTimeline.vue';
import AttachmentsGallery from '@/Components/Grievance/AttachmentsGallery.vue';

const referenceNumber = ref('');
const isLoading = ref(false);
const grievance = ref(null);
const error = ref('');

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
                error.value = `Erro do servidor (${response.status}). Por favor, tente novamente.`;
            }
            console.error('HTTP Error:', response.status, response.statusText);
            return;
        }

        const data = await response.json();

        if (data.success) {
            grievance.value = data.grievance;
        } else {
            error.value = data.message || 'Reclamação não encontrada.';
        }
    } catch (err) {
        error.value = 'Erro ao buscar reclamação. Por favor, tente novamente.';
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

const categoryLabels = {
    'ambiental': 'Impacto Ambiental',
    'social': 'Impacto Social',
    'economico': 'Desenvolvimento dos Projectos'
};
</script>

<template>
    <Head title="Acompanhar Reclamação" />

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            Acompanhar Reclamação
                        </h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Consulte o estado da sua reclamação em tempo real
                        </p>
                    </div>
                    <a
                        href="/"
                        class="text-sm text-brand hover:text-brand-dark font-medium"
                    >
                        ← Voltar ao Início
                    </a>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Search Section -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <div class="max-w-2xl mx-auto">
                    <label for="reference-number" class="block text-sm font-medium text-gray-700 mb-2">
                        Código de Rastreamento
                    </label>

                    <div class="flex gap-3">
                        <div class="flex-1">
                            <input
                                id="reference-number"
                                v-model="referenceNumber"
                                type="text"
                                placeholder="Ex: GRM-2025-XXXXXXXX"
                                @keypress="handleKeyPress"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-transparent uppercase"
                                :disabled="isLoading"
                            />
                        </div>

                        <button
                            @click="searchGrievance"
                            :disabled="isLoading || !referenceNumber.trim()"
                            class="px-6 py-3 bg-brand text-white font-medium rounded-lg hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
                        >
                            <MagnifyingGlassIcon class="w-5 h-5" />
                            {{ isLoading ? 'Buscando...' : 'Buscar' }}
                        </button>
                    </div>

                    <div v-if="error" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                        {{ error }}
                    </div>

                    <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-start gap-3">
                        <InformationCircleIcon class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                        <p class="text-sm text-blue-700">
                            O código de rastreamento foi enviado para o seu email quando submeteu a reclamação.
                            Formato: <strong>GRM-AAAA-XXXXXXXX</strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Results Section -->
            <div v-if="grievance" class="space-y-6">
                <!-- Grievance Header -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">
                                {{ grievance.reference_number }}
                            </h2>
                            <p class="text-sm text-gray-600">
                                Submetida em {{ new Date(grievance.submitted_at).toLocaleDateString('pt-PT', {
                                    day: '2-digit',
                                    month: 'long',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }) }}
                            </p>
                        </div>
                        <StatusBadge :status="grievance.status" :label="grievance.status_label" size="lg" />
                    </div>

                    <div class="border-t border-gray-200 pt-4 space-y-3">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Categoria:</span>
                            <span class="ml-2 text-sm text-gray-900">
                                {{ categoryLabels[grievance.category] || grievance.category }}
                                <span v-if="grievance.subcategory" class="text-gray-600">
                                    - {{ grievance.subcategory }}
                                </span>
                            </span>
                        </div>

                        <div v-if="grievance.province">
                            <span class="text-sm font-medium text-gray-500">Localização:</span>
                            <span class="ml-2 text-sm text-gray-900">
                                {{ grievance.province }}
                                <span v-if="grievance.district"> - {{ grievance.district }}</span>
                            </span>
                        </div>

                        <div v-if="grievance.assigned_user">
                            <span class="text-sm font-medium text-gray-500">Técnico Responsável:</span>
                            <span class="ml-2 text-sm text-gray-900">
                                {{ grievance.assigned_user.name }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-gray-200 pt-4">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">Descrição da Reclamação</h3>
                        <div
                            class="prose prose-sm max-w-none text-gray-700"
                            v-html="grievance.description"
                        ></div>
                    </div>

                    <div v-if="grievance.resolution_notes && grievance.status === 'resolved'" class="mt-6 border-t border-gray-200 pt-4">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">Notas de Resolução</h3>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <p class="text-sm text-gray-700">{{ grievance.resolution_notes }}</p>
                            <p v-if="grievance.resolved_by" class="text-xs text-gray-600 mt-2">
                                Resolvida por: {{ grievance.resolved_by.name }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Updates Timeline -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <UpdatesTimeline :updates="grievance.updates" />
                </div>

                <!-- Attachments -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <AttachmentsGallery :attachments="grievance.attachments" />
                </div>

                <!-- Actions -->
                <div class="flex justify-center">
                    <button
                        @click="resetSearch"
                        class="px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors"
                    >
                        Consultar Outra Reclamação
                    </button>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.prose img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
}
</style>
