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
    XMarkIcon,
    ArrowPathIcon,
    ChevronRightIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    BuildingOfficeIcon,
    MapIcon,
    QrCodeIcon
} from '@heroicons/vue/24/outline';
import {
    FingerPrintIcon,
    ShieldCheckIcon
} from '@heroicons/vue/20/solid';
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
const showSearchSection = ref(true);

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

        const response = await fetch(window.location.origin + window.route('grievance.track.search'), {
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
            } else if (response.status === 404) {
                showNotFoundModal.value = true;
            } else {
                error.value = `Erro ao buscar reclamação (${response.status}). Tente novamente.`;
            }
            console.error('HTTP Error:', response.status, response.statusText);
            return;
        }

        const data = await response.json();

        if (data.success) {
            grievance.value = data.grievance;
            showSearchSection.value = false; // Ocultar seção de busca após encontrar reclamação
        } else {
            showNotFoundModal.value = true;
        }
    } catch (err) {
        error.value = 'Erro de conexão. Verifique sua internet e tente novamente.';
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
    showSearchSection.value = true; // Mostrar seção de busca novamente
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

    <div class="min-h-screen bg-gradient-to-br from-orange-50 via-primary-50 to-amber-50">
        <Header hide-track-link />

        <!-- Hero Section - Modern Glassmorphism -->
        <div class="relative mt-16 overflow-hidden">
            <!-- Animated Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-primary-600 via-orange-600 to-amber-700"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent"></div>

            <!-- Floating Elements -->
            <div class="absolute top-20 left-10 w-32 h-32 bg-white/10 rounded-full blur-xl animate-pulse"></div>
            <div class="absolute bottom-20 right-10 w-48 h-48 bg-orange-300/20 rounded-full blur-2xl animate-pulse delay-1000"></div>
            <div class="absolute top-1/2 left-1/3 w-24 h-24 bg-amber-300/15 rounded-full blur-lg animate-pulse delay-500"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
                <div class="text-center">
                    <!-- Glass Icon Container -->
                    <div class="inline-flex items-center justify-center p-6 bg-white/10 backdrop-blur-xl rounded-3xl mb-8 border border-white/20 shadow-2xl shadow-black/10 group hover:bg-white/15 transition-all duration-500">
                        <QrCodeIcon class="w-16 h-16 text-white drop-shadow-lg" />
                        <div class="absolute inset-0 bg-gradient-to-r from-white/5 to-transparent rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    </div>

                    <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold text-white mb-6 tracking-tight drop-shadow-2xl">
                        Acompanhe Sua <span class="bg-gradient-to-r from-orange-200 to-amber-200 bg-clip-text text-transparent">Reclamação</span>
                    </h1>
                    <p class="text-xl text-white/90 max-w-3xl mx-auto leading-relaxed drop-shadow-lg font-medium">
                        Transparência total no acompanhamento das suas preocupações com tecnologia moderna
                    </p>

                    <!-- Stats Preview -->
                    <div class="flex justify-center gap-8 mt-12">
                        <div class="bg-white/10 backdrop-blur-md rounded-2xl px-6 py-4 border border-white/20 shadow-lg">
                            <div class="text-2xl font-bold text-white">24/7</div>
                            <div class="text-sm text-white/80">Acompanhamento</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-2xl px-6 py-4 border border-white/20 shadow-lg">
                            <div class="text-2xl font-bold text-white">100%</div>
                            <div class="text-sm text-white/80">Transparente</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-2xl px-6 py-4 border border-white/20 shadow-lg">
                            <div class="text-2xl font-bold text-white">Seguro</div>
                            <div class="text-sm text-white/80">Privado</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12 pb-20 z-10">
            <!-- Search Card - Modern Glassmorphism -->
            <div v-if="showSearchSection" class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl p-8 mb-12 border border-white/20 relative overflow-hidden group hover:shadow-3xl transition-all duration-500 hover:-translate-y-1 hover:bg-white/90">
                <!-- Glass Effect Background -->
                <div class="absolute inset-0 bg-gradient-to-br from-white/50 via-blue-50/30 to-indigo-50/50"></div>
                <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-blue-500/5 via-transparent to-purple-500/5"></div>

                <!-- Floating Decorative Elements -->
                <div class="absolute top-4 right-4 w-16 h-16 bg-gradient-to-br from-blue-200/20 to-purple-200/20 rounded-full blur-sm"></div>
                <div class="absolute bottom-4 left-4 w-12 h-12 bg-gradient-to-br from-indigo-200/15 to-blue-200/15 rounded-full blur-sm"></div>

                <div class="relative max-w-3xl mx-auto">
                    <div class="text-center mb-10">
                        <!-- Glass Icon Container -->
                        <div class="w-24 h-24 bg-gradient-to-br from-primary-500/20 to-orange-600/20 backdrop-blur-md rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-primary-500/10 border border-white/30 group-hover:shadow-primary-500/20 transition-all duration-500">
                            <FingerPrintIcon class="w-12 h-12 text-primary-600 drop-shadow-sm" />
                            <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        </div>
                        <h2 class="text-3xl font-bold bg-gradient-to-r from-gray-900 via-primary-900 to-orange-900 bg-clip-text text-transparent mb-3">
                            Rastreamento Seguro
                        </h2>
                        <p class="text-gray-700 text-lg font-medium">
                            Insira o código único fornecido no seu email
                        </p>
                    </div>

                    <div class="space-y-6">
                        <div class="relative">
                            <label for="reference-number" class="block text-sm font-semibold text-gray-800 mb-3 ml-1">
                                Código de Rastreamento
                            </label>
                            <div class="flex flex-col sm:flex-row gap-4">
                                <div class="flex-1 relative">
                                    <input
                                        id="reference-number"
                                        v-model="referenceNumber"
                                        type="text"
                                        placeholder="Ex: GRM-2025-XXXXXXXX"
                                        @keypress="handleKeyPress"
                                        class="relative w-full px-6 py-5 bg-white border-2 border-gray-200 rounded-xl focus:ring-3 focus:ring-brand/30 focus:border-brand transition-all duration-300 uppercase font-mono text-lg shadow-lg placeholder:text-gray-500"
                                        :disabled="isLoading"
                                    />
                                    <div class="absolute right-5 top-1/2 transform -translate-y-1/2">
                                        <ShieldCheckIcon class="w-6 h-6 text-brand" />
                                    </div>
                                </div>

                                <button
                                    @click="searchGrievance"
                                    :disabled="isLoading || !referenceNumber.trim()"
                                    class="relative sm:px-10 px-6 py-5 bg-gradient-to-r from-primary-500 via-primary-600 to-orange-600 text-white font-semibold rounded-2xl hover:shadow-2xl hover:shadow-primary-500/40 focus:outline-none focus:ring-3 focus:ring-primary-500/30 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 flex items-center justify-center gap-3 shadow-xl group/btn overflow-hidden sm:w-auto w-full backdrop-blur-sm border border-white/20 hover:border-white/30"
                                >
                                    <!-- Glassmorphism overlay -->
                                    <div class="absolute inset-0 bg-white/10 backdrop-blur-sm rounded-2xl opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>

                                    <!-- Shimmer effect -->
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent translate-x-[-100%] group-hover/btn:translate-x-[100%] transition-transform duration-1000 rounded-2xl"></div>

                                    <!-- Inner glow -->
                                    <div class="absolute inset-0 bg-gradient-to-r from-primary-400/20 via-transparent to-orange-400/20 rounded-2xl opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>

                                    <MagnifyingGlassIcon class="w-6 h-6 relative z-10 drop-shadow-sm" />
                                    <span class="relative z-10 font-medium drop-shadow-sm">
                                        {{ isLoading ? 'Buscando...' : 'Rastrear Agora' }}
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- Error Message - Glassmorphism -->
                        <div v-if="error"
                            class="p-5 bg-red-50/80 backdrop-blur-md border border-red-200/50 rounded-2xl text-red-800 text-sm flex items-start gap-4 shadow-lg shadow-red-500/10">
                            <div class="w-10 h-10 bg-red-500/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-red-300/30 flex-shrink-0">
                                <ExclamationTriangleIcon class="w-5 h-5 text-red-600" />
                            </div>
                            <span class="font-medium">{{ error }}</span>
                        </div>

                        <!-- Info Card - Glassmorphism -->
                        <div class="bg-primary-50/80 backdrop-blur-md border border-primary-200/50 rounded-2xl p-5 flex items-start gap-4 shadow-lg shadow-primary-500/10">
                            <div class="w-10 h-10 bg-primary-500/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-primary-300/30 flex-shrink-0">
                                <InformationCircleIcon class="w-5 h-5 text-primary-600" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800 mb-1">
                                    📧 Onde encontrar o código?
                                </p>
                                <p class="text-sm text-gray-700">
                                    O código <strong class="font-mono bg-gradient-to-r from-primary-600 to-orange-600 bg-clip-text text-transparent font-bold">GRM-AAAA-XXXXXXXX</strong> foi enviado para o seu email após a submissão da reclamação.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Section -->
            <div v-if="grievance" class="space-y-8 animate-fade-in">
                <!-- Main Grievance Card - fundo BRANCO SÓLIDO -->
                <div class="bg-white rounded-2xl shadow-2xl p-8 border border-gray-100 relative overflow-hidden group">
                    <!-- Header -->
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 mb-8">
                        <div class="flex-1">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-14 h-14 bg-gradient-to-br from-brand to-brand-dark rounded-xl flex items-center justify-center shadow-lg shadow-brand/20">
                                    <DocumentTextIcon class="w-7 h-7 text-white" />
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold font-mono text-gray-900">
                                        {{ grievance.reference_number }}
                                    </h2>
                                    <div class="flex flex-wrap items-center gap-4 mt-2">
                                        <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-100 rounded-full text-sm text-gray-700">
                                            <CalendarIcon class="w-4 h-4 text-brand" />
                                            {{ new Date(grievance.submitted_at).toLocaleDateString('pt-PT', {
                                                day: '2-digit',
                                                month: 'short',
                                                year: 'numeric'
                                            }) }}
                                        </span>
                                        <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-100 rounded-full text-sm text-gray-700">
                                            <ClockIcon class="w-4 h-4 text-brand-dark" />
                                            {{ new Date(grievance.submitted_at).toLocaleTimeString('pt-PT', {
                                                hour: '2-digit',
                                                minute: '2-digit'
                                            }) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <StatusBadge :status="grievance.status" :label="grievance.status_label" size="lg" />
                    </div>

                    <!-- Stats Grid - Glassmorphism Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8 relative z-10">
                        <!-- Type Card -->
                        <div v-if="grievance.type" class="bg-white/70 backdrop-blur-md p-5 rounded-2xl border border-white/40 hover:border-purple-300/50 hover:shadow-xl hover:shadow-purple-500/10 transition-all duration-300 group/card hover:-translate-y-1 shadow-lg shadow-purple-500/5 hover:bg-white/80">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500/20 to-pink-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                                    <DocumentTextIcon class="w-6 h-6 text-purple-600" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-600 uppercase tracking-wider">Tipo</p>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ grievance.type_label || grievance.type }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Priority Card -->
                        <div v-if="grievance.priority" class="bg-white/70 backdrop-blur-md p-5 rounded-2xl border border-white/40 hover:border-red-300/50 hover:shadow-xl hover:shadow-red-500/10 transition-all duration-300 group/card hover:-translate-y-1 shadow-lg shadow-red-500/5 hover:bg-white/80">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-red-500/20 to-orange-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                                    <ExclamationTriangleIcon class="w-6 h-6 text-red-600" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-600 uppercase tracking-wider">Prioridade</p>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ grievance.priority_label || grievance.priority }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Category Card -->
                        <!-- <div class="bg-white/70 backdrop-blur-md p-5 rounded-2xl border border-white/40 hover:border-blue-300/50 hover:shadow-xl hover:shadow-blue-500/10 transition-all duration-300 group/card hover:-translate-y-1 shadow-lg shadow-blue-500/5 hover:bg-white/80">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500/20 to-purple-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                                    <DocumentTextIcon class="w-6 h-6 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-600 uppercase tracking-wider">Categoria</p>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ categoryLabels[grievance.category] || grievance.category }}
                                    </p>
                                </div>
                            </div>
                        </div> -->

                        <!-- Location Card -->
                        <div v-if="grievance.province" class="bg-white/70 backdrop-blur-md p-5 rounded-2xl border border-white/40 hover:border-cyan-300/50 hover:shadow-xl hover:shadow-cyan-500/10 transition-all duration-300 group/card hover:-translate-y-1 shadow-lg shadow-cyan-500/5 hover:bg-white/80">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-cyan-500/20 to-blue-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                                    <MapIcon class="w-6 h-6 text-cyan-600" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-600 uppercase tracking-wider">Localização</p>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ grievance.province }}
                                        <span v-if="grievance.district" class="text-gray-600"> • {{ grievance.district }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Technician Card -->
                        <div v-if="grievance.assigned_user" class="bg-white/70 backdrop-blur-md p-5 rounded-2xl border border-white/40 hover:border-emerald-300/50 hover:shadow-xl hover:shadow-emerald-500/10 transition-all duration-300 group/card hover:-translate-y-1 shadow-lg shadow-emerald-500/5 hover:bg-white/80">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-emerald-500/20 to-green-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                                    <UserIcon class="w-6 h-6 text-emerald-600" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-600 uppercase tracking-wider">Técnico Responsável</p>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ grievance.assigned_user.name }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Project Information Section (if exists) -->
                    <div v-if="grievance.project" class="mb-8">
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-200">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
                                    <BuildingOfficeIcon class="w-6 h-6 text-white" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Projeto Relacionado</h3>
                                    <p class="text-xl font-bold text-blue-900 mb-3">{{ grievance.project.name }}</p>
                                    <p v-if="grievance.project.description" class="text-gray-700 mb-4 leading-relaxed bg-white/60 rounded-lg p-4 border border-blue-100">
                                        {{ grievance.project.description }}
                                    </p>
                                    <div v-if="grievance.project.province || grievance.project.district" class="flex flex-wrap items-center gap-3">
                                        <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-white rounded-full text-sm text-gray-700 border border-blue-200">
                                            <MapPinIcon class="w-4 h-4 text-blue-600" />
                                            {{ grievance.project.province }}
                                            <span v-if="grievance.project.district" class="text-gray-500">• {{ grievance.project.district }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Sections -->
                    <div class="space-y-8">
                        <!-- Description -->
                        <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-brand/10 to-brand-dark/10 rounded-lg flex items-center justify-center">
                                    <DocumentTextIcon class="w-5 h-5 text-brand" />
                                </div>
                                Descrição da Reclamação
                            </h3>
                            <div class="prose prose-gray max-w-none text-gray-700 bg-white rounded-lg p-6 border border-gray-200"
                                v-html="grievance.description"></div>
                        </div>

                        <!-- Resolution Notes -->
                        <div v-if="grievance.resolution_notes && grievance.status === 'resolved'"
                            class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 border border-green-200">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center shadow-lg flex-shrink-0">
                                    <CheckCircleIcon class="w-6 h-6 text-white" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Notas de Resolução</h3>
                                    <p class="text-gray-700 leading-relaxed bg-white rounded-lg p-5 border border-green-200">
                                        {{ grievance.resolution_notes }}
                                    </p>
                                    <div v-if="grievance.resolved_by"
                                        class="flex items-center gap-3 mt-4 pt-4 border-t border-green-200">
                                        <UserIcon class="w-5 h-5 text-green-600" />
                                        <span class="text-sm text-gray-600">
                                            Resolvida por: <strong class="text-gray-900">{{ grievance.resolved_by.name }}</strong>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Two Column Layout - Glassmorphism Cards -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Timeline -->
                    <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/30 shadow-lg shadow-primary-500/5 hover:bg-white/80 transition-all duration-300">
                        <UpdatesTimeline :updates="grievance.updates" />
                    </div>

                    <!-- Attachments -->
                    <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/30 shadow-lg shadow-orange-500/5 hover:bg-white/80 transition-all duration-300">
                        <AttachmentsGallery :attachments="grievance.attachments" />
                    </div>
                </div>

                <!-- Action Button -->
                <div class="flex justify-center pt-8">
                    <button @click="resetSearch"
                        class="group relative px-8 py-4 bg-white text-gray-700 font-semibold rounded-xl hover:shadow-2xl border border-gray-200 transition-all duration-300 flex items-center gap-3 shadow-lg hover:shadow-gray-200/50 transform hover:-translate-y-1 overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-gray-100 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>
                        <ArrowPathIcon class="w-5 h-5 relative z-10" />
                        <span class="relative z-10">Consultar Outra Reclamação</span>
                        <ChevronRightIcon class="w-5 h-5 relative z-10 opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0 transition-all duration-300" />
                    </button>
                </div>
            </div>
        </main>

        <Footer class="relative z-10" />

        <!-- Modal para código não encontrado - Glassmorphism -->
        <div v-if="showNotFoundModal"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50 animate-fade-in">
            <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl max-w-md w-full mx-auto transform animate-scale-in border border-white/30 overflow-hidden shadow-blue-500/10">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center shadow-lg">
                            <XMarkIcon class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Código Não Encontrado
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">Verifique e tente novamente</p>
                        </div>
                    </div>
                    <button @click="closeNotFoundModal"
                        class="w-10 h-10 rounded-full bg-gray-100 border border-gray-200 text-gray-400 hover:text-gray-600 hover:bg-gray-200 transition-all duration-300 flex items-center justify-center">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <div class="text-center mb-6">
                        <div class="w-20 h-20 bg-red-50 rounded-lg flex items-center justify-center mx-auto mb-4 border border-red-200">
                            <MagnifyingGlassIcon class="w-10 h-10 text-red-500" />
                        </div>
                        <h4 class="text-xl font-semibold text-gray-900 mb-2">
                            Código de rastreio não encontrado
                        </h4>
                        <p class="text-gray-600">
                            O código inserido não corresponde a nenhuma reclamação registada no sistema.
                        </p>
                    </div>

                    <div class="bg-brand/5 rounded-lg p-4 border border-brand/20">
                        <div class="flex items-start gap-3">
                            <InformationCircleIcon class="w-5 h-5 text-brand flex-shrink-0 mt-0.5" />
                            <div>
                                <p class="text-sm font-medium text-brand-dark mb-2">📋 Formato correto do código:</p>
                                <div class="inline-block px-4 py-2 bg-gradient-to-r from-brand to-brand-dark rounded-lg">
                                    <p class="text-white font-mono text-sm">GRM-AAAA-XXXXXXXX</p>
                                </div>
                                <p class="text-xs text-gray-700 mt-3">
                                    • AAAA representa o ano (ex: 2024)<br>
                                    • XXXXXXXX são 8 caracteres alfanuméricos<br>
                                    • O código é enviado automaticamente por email
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex gap-3 p-6 border-t border-gray-200">
                    <button @click="closeNotFoundModal"
                        class="flex-1 px-4 py-3 bg-gradient-to-r from-brand to-brand-dark text-white font-semibold rounded-lg hover:shadow-lg hover:shadow-brand/30 transition-all duration-300 focus:outline-none focus:ring-3 focus:ring-brand/20">
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
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.prose p {
    margin-bottom: 1rem;
    line-height: 1.7;
    color: #374151;
}

.prose ul,
.prose ol {
    margin: 1.25rem 0;
    padding-left: 1.75rem;
}

.prose li {
    margin-bottom: 0.75rem;
    color: #4b5563;
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
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-fade-in {
    animation: fade-in 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.animate-scale-in {
    animation: scale-in 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
