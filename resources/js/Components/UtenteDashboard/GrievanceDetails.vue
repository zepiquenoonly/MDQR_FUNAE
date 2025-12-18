<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-[1200px] h-[90vh] flex flex-col border border-white/30 shadow-primary-500/10">
            <!-- Header -->
            <div class="relative overflow-hidden rounded-t-3xl">
                <div class="absolute inset-0 bg-gradient-to-br from-primary-600 via-orange-600 to-amber-700"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent"></div>

                <!-- Floating Elements -->
                <div class="absolute top-4 left-10 w-16 h-16 bg-white/10 rounded-full blur-xl animate-pulse"></div>
                <div class="absolute bottom-4 right-10 w-24 h-24 bg-orange-300/20 rounded-full blur-2xl animate-pulse delay-1000"></div>

                <div class="relative flex items-center justify-between p-6">
                    <div class="flex-1 text-center">
                        <div class="inline-flex items-center justify-center p-3 bg-white/10 backdrop-blur-xl rounded-2xl mb-3 border border-white/20 shadow-2xl shadow-black/10">
                            <DocumentTextIcon class="w-8 h-8 text-white drop-shadow-lg" />
                        </div>
                        <h2 class="text-2xl font-bold text-white drop-shadow-2xl">
                            Detalhes da <span class="bg-gradient-to-r from-orange-200 to-amber-200 bg-clip-text text-transparent">Submissão</span>
                        </h2>
                        <div class="inline-block px-4 py-2 bg-white/10 backdrop-blur-md rounded-xl border border-white/20 shadow-lg mt-2">
                            <p class="text-white font-mono text-sm font-semibold drop-shadow-lg">
                                {{ grievance.reference_number || grievance.id }}
                            </p>
                        </div>
                    </div>
                    <button @click="$emit('close')" class="text-white transition-colors hover:text-gray-200 hover:bg-white/10 rounded-full p-2 backdrop-blur-sm">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto">
                <div v-if="loading" class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 border-4 border-primary-500 rounded-full animate-spin border-t-transparent"></div>
                        <p class="text-gray-600">Carregando detalhes...</p>
                    </div>
                </div>

                <div v-else-if="error" class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <ExclamationCircleIcon class="w-16 h-16 mx-auto mb-4 text-red-500" />
                        <p class="mb-4 text-lg font-semibold text-gray-800">Erro ao carregar detalhes</p>
                        <p class="mb-4 text-gray-600">{{ error }}</p>
                        <button @click="loadDetails" class="px-4 py-2 text-white bg-primary-500 rounded-lg hover:bg-primary-600">
                            Tentar novamente
                        </button>
                    </div>
                </div>

                <div class="p-8 space-y-8 animate-fade-in">
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
                                            {{ details ? details.reference_number : '' }}
                                        </h2>
                                        <div class="flex flex-wrap items-center gap-4 mt-2">
                                            <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-100 rounded-full text-sm text-gray-700">
                                                <ClockIcon class="w-4 h-4 text-brand" />
                                                {{ details ? new Date(details.submitted_at).toLocaleDateString('pt-PT', {
                                                    day: '2-digit',
                                                    month: 'short',
                                                    year: 'numeric'
                                                }) : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="details" class="bg-white/70 backdrop-blur-md p-4 rounded-2xl border border-white/40 shadow-lg shadow-primary-500/5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-primary-500/20 to-orange-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                                        <CheckCircleIcon class="w-5 h-5 text-primary-600" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 uppercase tracking-wider">Status</p>
                                        <p class="text-sm font-semibold text-gray-900">{{ details.status_label }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Grid - Glassmorphism Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8 relative z-10">
                            <!-- Type Card -->
                            <div v-if="details && details.type" class="bg-white/70 backdrop-blur-md p-5 rounded-2xl border border-white/40 hover:border-purple-300/50 hover:shadow-xl hover:shadow-purple-500/10 transition-all duration-300 group/card hover:-translate-y-1 shadow-lg shadow-purple-500/5 hover:bg-white/80">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500/20 to-pink-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                                        <DocumentTextIcon class="w-6 h-6 text-purple-600" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 uppercase tracking-wider">Tipo</p>
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ details.type_label || details.category }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Priority Card -->
                            <div v-if="details && details.priority" class="bg-white/70 backdrop-blur-md p-5 rounded-2xl border border-white/40 hover:border-red-300/50 hover:shadow-xl hover:shadow-red-500/10 transition-all duration-300 group/card hover:-translate-y-1 shadow-lg shadow-red-500/5 hover:bg-white/80">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-red-500/20 to-orange-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                                        <ExclamationTriangleIcon class="w-6 h-6 text-red-600" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 uppercase tracking-wider">Prioridade</p>
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ details.priority_label }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Location Card -->
                            <div v-if="details && details.province" class="bg-white/70 backdrop-blur-md p-5 rounded-2xl border border-white/40 hover:border-cyan-300/50 hover:shadow-xl hover:shadow-cyan-500/10 transition-all duration-300 group/card hover:-translate-y-1 shadow-lg shadow-cyan-500/5 hover:bg-white/80">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-cyan-500/20 to-blue-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                                        <MapPinIcon class="w-6 h-6 text-cyan-600" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 uppercase tracking-wider">Localização</p>
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ details.province }}
                                            <span v-if="details.district" class="text-gray-600"> • {{ details.district }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Technician Card -->
                            <div v-if="details && details.assigned_user" class="bg-white/70 backdrop-blur-md p-5 rounded-2xl border border-white/40 hover:border-emerald-300/50 hover:shadow-xl hover:shadow-emerald-500/10 transition-all duration-300 group/card hover:-translate-y-1 shadow-lg shadow-emerald-500/5 hover:bg-white/80">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-500/20 to-green-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                                        <UserIcon class="w-6 h-6 text-emerald-600" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 uppercase tracking-wider">Técnico Responsável</p>
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ details.assigned_user.name }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description Section - Glassmorphism -->
                    <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/30 shadow-lg shadow-blue-500/5 hover:bg-white/80 transition-all duration-300">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500/20 to-indigo-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                                <DocumentTextIcon class="w-5 h-5 text-blue-600" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">Descrição</h3>
                        </div>
                        <div class="bg-white/50 backdrop-blur-sm rounded-2xl p-6 border border-white/40">
                            <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">{{ details ? details.description : '' }}</p>
                        </div>
                    </div>

                    <!-- Project Related Section - Glassmorphism -->
                    <div v-if="details && details.project" class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/30 shadow-lg shadow-indigo-500/5 hover:bg-white/80 transition-all duration-300">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500/20 to-purple-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                                <DocumentTextIcon class="w-5 h-5 text-indigo-600" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">Projeto Relacionado</h3>
                        </div>
                        <div class="bg-white/50 backdrop-blur-sm rounded-2xl p-6 border border-white/40">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 font-medium">Nome do Projeto:</span>
                                <span class="text-sm text-primary-600 font-semibold bg-primary-50 px-3 py-1 rounded-full">{{ details.project.name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Responsible Assignment Section - Glassmorphism -->
                    <div v-if="details && details.assigned_to" class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/30 shadow-lg shadow-teal-500/5 hover:bg-white/80 transition-all duration-300">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-teal-500/20 to-cyan-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                                <UserIcon class="w-5 h-5 text-teal-600" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">Responsável pela Atribuição</h3>
                        </div>
                        <div class="bg-white/50 backdrop-blur-sm rounded-2xl p-6 border border-white/40 space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 font-medium">Técnico Responsável:</span>
                                <span class="text-sm text-gray-800 font-semibold">{{ details.assigned_to.name }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 font-medium">Email:</span>
                                <span class="text-sm text-gray-800">{{ details.assigned_to.email }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Location Section - Glassmorphism -->
                    <div v-if="details && (details.province || details.district || details.locality)" class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/30 shadow-lg shadow-slate-500/5 hover:bg-white/80 transition-all duration-300">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-slate-500/20 to-gray-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                                <MapPinIcon class="w-5 h-5 text-slate-600" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">Localização</h3>
                        </div>
                        <div class="bg-white/50 backdrop-blur-sm rounded-2xl p-6 border border-white/40">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-if="details.province" class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 font-medium">Província:</span>
                                    <span class="text-sm text-gray-800 font-semibold">{{ details.province }}</span>
                                </div>
                                <div v-if="details.district" class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 font-medium">Distrito:</span>
                                    <span class="text-sm text-gray-800 font-semibold">{{ details.district }}</span>
                                </div>
                                <div v-if="details.municipal_district" class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 font-medium">Posto Administrativo:</span>
                                    <span class="text-sm text-gray-800 font-semibold">{{ details.municipal_district }}</span>
                                </div>
                                <div v-if="details.locality" class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 font-medium">Localidade:</span>
                                    <span class="text-sm text-gray-800 font-semibold">{{ details.locality }}</span>
                                </div>
                            </div>
                            <div v-if="details.location_details" class="mt-6 pt-4 border-t border-gray-200">
                                <span class="text-sm text-gray-600 font-medium">Detalhes da Localização:</span>
                                <p class="text-sm text-gray-800 mt-2 bg-gray-50 p-3 rounded-lg">{{ details.location_details }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Two Column Layout - Glassmorphism Cards -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Timeline -->
                        <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/30 shadow-lg shadow-primary-500/5 hover:bg-white/80 transition-all duration-300">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-primary-500/20 to-orange-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                                        <ArrowPathIcon class="w-5 h-5 text-primary-600" />
                                    </div>
                                    Histórico de Atualizações
                                </h3>
                                <button @click="refreshUpdates" class="p-2 text-primary-500 transition-colors rounded-lg hover:bg-primary-50">
                                    <ArrowPathIcon class="w-5 h-5" :class="{ 'animate-spin': refreshing }" />
                                </button>
                            </div>
                            <div v-if="details && details.updates && details.updates.length > 0" class="space-y-4">
                                <div v-for="(update, index) in details.updates" :key="update.id"
                                    class="relative pl-6 border-l-2 border-gray-200">
                                    <div :class="[
                                        'absolute left-0 top-0 -ml-2 w-4 h-4 rounded-full border-2 border-white',
                                        index === 0 ? 'bg-primary-500' : 'bg-gray-400'
                                    ]"></div>
                                    <div class="pb-4">
                                        <div class="flex items-center justify-between mb-1">
                                            <p class="text-sm font-semibold text-gray-800">{{ update.user?.name || 'Sistema' }}</p>
                                            <p class="text-xs text-gray-500">{{ formatDate(update.created_at) }}</p>
                                        </div>
                                        <p v-if="update.description" class="text-sm text-gray-700">{{ update.description }}</p>
                                        <p v-if="update.comment" class="mt-1 text-sm italic text-gray-600">"{{ update.comment }}"</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else-if="details" class="text-center py-8">
                                <ArrowPathIcon class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                                <p class="text-gray-500">Nenhuma atualização disponível</p>
                            </div>
                        </div>

                        <!-- Attachments -->
                        <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/30 shadow-lg shadow-orange-500/5 hover:bg-white/80 transition-all duration-300">
                            <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-orange-500/20 to-amber-600/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                                    <PaperClipIcon class="w-5 h-5 text-orange-600" />
                                </div>
                                Anexos
                                <span v-if="details && details.attachments" class="text-sm text-gray-500">({{ details.attachments.length }})</span>
                            </h3>
                            <div v-if="details && details.attachments && details.attachments.length > 0" class="grid grid-cols-1 gap-4">
                                <div v-for="attachment in details.attachments" :key="attachment.id" class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-start space-x-3">
                                        <!-- Preview para imagens -->
                                        <div v-if="isImage(attachment.mime_type)" class="flex-shrink-0">
                                            <img :src="attachment.url" :alt="attachment.original_filename"
                                                 class="w-16 h-16 object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-80 transition-opacity"
                                                 @click="openImageModal(attachment)" />
                                        </div>
                                        <!-- Ícone para outros tipos de arquivo -->
                                        <div v-else class="flex-shrink-0">
                                            <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                                <PaperClipIcon class="w-8 h-8 text-gray-500" />
                                            </div>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ attachment.original_filename }}</p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ formatFileSize(attachment.file_size) }} • {{ getFileTypeLabel(attachment.mime_type) }}
                                            </p>
                                            <p v-if="attachment.uploaded_at" class="text-xs text-gray-400 mt-1">
                                                Enviado em {{ formatDate(attachment.uploaded_at) }}
                                            </p>
                                            <div class="mt-3 flex space-x-2">
                                                <a :href="attachment.url" target="_blank"
                                                   class="inline-flex items-center px-3 py-1 text-xs font-medium text-primary-600 bg-primary-50 rounded-md hover:bg-primary-100 transition-colors">
                                                    <ArrowDownTrayIcon class="w-3 h-3 mr-1" />
                                                    Download
                                                </a>
                                                <button v-if="isImage(attachment.mime_type)" @click="openImageModal(attachment)"
                                                        class="inline-flex items-center px-3 py-1 text-xs font-medium text-green-600 bg-green-50 rounded-md hover:bg-green-100 transition-colors">
                                                    <EyeIcon class="w-3 h-3 mr-1" />
                                                    Visualizar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else-if="details" class="text-center py-8">
                                <PaperClipIcon class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                                <p class="text-gray-500">Nenhum anexo disponível</p>
                            </div>
                        </div>
                    </div>

                    <!-- Resolution Notes -->
                    <div v-if="details && details.resolution_notes && details.status === 'resolved'"
                        class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 border border-green-200">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center shadow-lg flex-shrink-0">
                                <CheckCircleIcon class="w-6 h-6 text-white" />
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Notas de Resolução</h3>
                                <p class="text-gray-700 leading-relaxed bg-white rounded-lg p-5 border border-green-200">
                                    {{ details.resolution_notes }}
                                </p>
                                <div v-if="details.resolved_by"
                                    class="flex items-center gap-3 mt-4 pt-4 border-t border-green-200">
                                    <UserIcon class="w-5 h-5 text-green-600" />
                                    <span class="text-sm text-gray-600">
                                        Resolvida por: <strong class="text-gray-900">{{ details.resolved_by.name }}</strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer - Glassmorphism -->
            <div class="relative overflow-hidden rounded-b-3xl">
                <div class="absolute inset-0 bg-gradient-to-r from-gray-50 via-white to-gray-50"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-white/20 via-transparent to-transparent"></div>
                <div class="relative flex justify-end p-6">
                    <button @click="$emit('close')"
                        class="group relative px-8 py-3 bg-white/80 backdrop-blur-md text-gray-700 font-semibold rounded-2xl hover:bg-white border border-white/40 shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-3 hover:-translate-y-0.5">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-gray-100/50 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000 rounded-2xl"></div>
                        <XMarkIcon class="w-5 h-5 relative z-10" />
                        <span class="relative z-10">Fechar</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal de Visualização de Imagem -->
        <div v-if="imageModal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-75" @click="closeImageModal">
            <div class="relative max-w-4xl max-h-full">
                <img :src="imageModal.attachment.url" :alt="imageModal.attachment.original_filename"
                     class="max-w-full max-h-full object-contain rounded-lg" />
                <button @click="closeImageModal" class="absolute top-4 right-4 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-75 transition-colors">
                    <XMarkIcon class="w-6 h-6" />
                </button>
                <div class="absolute bottom-4 left-4 right-4 bg-black bg-opacity-50 rounded-lg p-4 text-white">
                    <p class="font-medium">{{ imageModal.attachment.original_filename }}</p>
                    <p class="text-sm opacity-75">{{ formatFileSize(imageModal.attachment.file_size) }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import {
    XMarkIcon,
    ExclamationCircleIcon,
    MapPinIcon,
    PaperClipIcon,
    ArrowDownTrayIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    EyeIcon,
    ClockIcon,
    UserIcon,
    DocumentTextIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    grievance: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close'])

const loading = ref(true)
const error = ref(null)
const details = ref(null)
const refreshing = ref(false)
const imageModal = ref({
    open: false,
    attachment: null
})

const loadDetails = async () => {
    loading.value = true
    error.value = null

    try {
        const response = await fetch(`/utente/grievances/${props.grievance.id}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })

        if (!response.ok) {
            throw new Error('Erro ao carregar detalhes da reclamação')
        }

        const data = await response.json()
        details.value = data.grievance
    } catch (err) {
        error.value = err.message
        console.error('Erro ao carregar detalhes:', err)
    } finally {
        loading.value = false
    }
}

const refreshUpdates = async () => {
    if (refreshing.value) return

    refreshing.value = true

    try {
        const response = await fetch(`/utente/grievances/${props.grievance.id}/status-updates`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })

        if (!response.ok) {
            throw new Error('Erro ao atualizar status')
        }

        const data = await response.json()
        details.value.updates = data.updates
        details.value.status = data.current_status
        details.value.status_label = data.status_label
    } catch (err) {
        console.error('Erro ao atualizar:', err)
    } finally {
        refreshing.value = false
    }
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('pt-PT', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getGenderLabel = (gender) => {
    const labels = {
        'male': 'Masculino',
        'female': 'Feminino',
        'other': 'Outro'
    }
    return labels[gender] || gender
}

const isImage = (mimeType) => {
    return mimeType && mimeType.startsWith('image/')
}

const getFileTypeLabel = (mimeType) => {
    if (!mimeType) return 'Arquivo'
    if (mimeType.startsWith('image/')) return 'Imagem'
    if (mimeType.startsWith('audio/')) return 'Áudio'
    if (mimeType.startsWith('video/')) return 'Vídeo'
    if (mimeType.includes('pdf')) return 'PDF'
    if (mimeType.includes('document') || mimeType.includes('word')) return 'Documento'
    return 'Arquivo'
}

const openImageModal = (attachment) => {
    imageModal.value = {
        open: true,
        attachment: attachment
    }
}

const closeImageModal = () => {
    imageModal.value = {
        open: false,
        attachment: null
    }
}

const getStatusBadgeClass = (status) => {
    const classes = {
        'submitted': 'bg-primary-100 text-primary-800',
        'under_review': 'bg-yellow-100 text-yellow-800',
        'assigned': 'bg-purple-100 text-purple-800',
        'in_progress': 'bg-orange-100 text-orange-800',
        'pending_approval': 'bg-indigo-100 text-indigo-800',
        'resolved': 'bg-green-100 text-green-800',
        'closed': 'bg-gray-100 text-gray-800',
        'rejected': 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPriorityBadgeClass = (priority) => {
    const classes = {
        'low': 'bg-green-100 text-green-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'high': 'bg-orange-100 text-orange-800',
        'urgent': 'bg-red-100 text-red-800'
    }
    return classes[priority] || 'bg-gray-100 text-gray-800'
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
}

onMounted(() => {
    loadDetails()
})
</script>
