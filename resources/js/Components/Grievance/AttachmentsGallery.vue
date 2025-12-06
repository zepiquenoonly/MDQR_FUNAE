<script setup>
import { DocumentIcon, ArrowDownTrayIcon, PhotoIcon, FilmIcon, DocumentTextIcon, TableCellsIcon, PaperClipIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';

defineProps({
    attachments: {
        type: Array,
        default: () => []
    }
});

const showStorageModal = ref(false);
const selectedAttachment = ref(null);

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const getFileIcon = (mimeType) => {
    if (mimeType.startsWith('image/')) return PhotoIcon;
    if (mimeType.startsWith('video/')) return FilmIcon;
    if (mimeType === 'application/pdf') return DocumentIcon;
    if (mimeType.includes('word')) return DocumentTextIcon;
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return TableCellsIcon;
    return PaperClipIcon;
};

const showAttachmentInfo = (attachment) => {
    selectedAttachment.value = attachment;
    showStorageModal.value = true;
};

const closeStorageModal = () => {
    showStorageModal.value = false;
    selectedAttachment.value = null;
};
</script>

<template>
    <div class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-900">Anexos e Evidências</h3>

        <div v-if="attachments.length === 0" class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
            <DocumentIcon class="w-12 h-12 mx-auto mb-2 text-gray-400" />
            <p>Nenhum anexo disponível</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
                v-for="attachment in attachments"
                :key="attachment.id"
                class="flex items-center gap-3 p-4 bg-white border border-gray-200 rounded-lg hover:border-brand hover:shadow-md transition-all group cursor-pointer"
                @click="showAttachmentInfo(attachment)"
            >
                <div class="flex-shrink-0 text-3xl">
                    <component :is="getFileIcon(attachment.mime_type)" class="w-8 h-8 text-gray-400" />
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate group-hover:text-brand">
                        {{ attachment.original_filename }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ formatFileSize(attachment.size) }}
                    </p>
                    <p class="text-xs text-gray-400">
                        {{ new Date(attachment.uploaded_at).toLocaleDateString('pt-PT', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        }) }}
                    </p>
                    <p class="text-xs text-blue-600 mt-1">
                        📁 Armazenado em: storage/app/private/grievances/{{ attachment.id }}
                    </p>
                </div>

                <div class="flex-shrink-0 text-gray-400 group-hover:text-brand">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Modal de informações de armazenamento -->
        <div v-if="showStorageModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Localização do Arquivo</h3>
                    <button @click="closeStorageModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="selectedAttachment" class="space-y-4">
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <component :is="getFileIcon(selectedAttachment.mime_type)" class="w-8 h-8 text-gray-600" />
                        <div>
                            <p class="font-medium text-gray-900">{{ selectedAttachment.original_filename }}</p>
                            <p class="text-sm text-gray-600">{{ formatFileSize(selectedAttachment.size) }}</p>
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z" />
                            </svg>
                            <span class="font-medium text-blue-900">Diretório de Armazenamento</span>
                        </div>
                        <div class="bg-white rounded border p-3 font-mono text-sm text-gray-800 break-all">
                            storage/app/private/grievances/{{ selectedAttachment.id }}/
                        </div>
                        <p class="text-xs text-blue-700 mt-2">
                            Este arquivo está armazenado de forma segura no servidor em um diretório privado.
                        </p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button @click="closeStorageModal" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
