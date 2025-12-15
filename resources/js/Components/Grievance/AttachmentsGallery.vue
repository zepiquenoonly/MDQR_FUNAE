<script setup>
import { DocumentIcon, PhotoIcon, FilmIcon, DocumentTextIcon, TableCellsIcon, PaperClipIcon, MusicalNoteIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    attachments: {
        type: Array,
        default: () => []
    }
});

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
    if (mimeType.startsWith('audio/')) return MusicalNoteIcon;
    if (mimeType === 'application/pdf') return DocumentIcon;
    if (mimeType.includes('word')) return DocumentTextIcon;
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return TableCellsIcon;
    return PaperClipIcon;
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
                class="flex items-center gap-3 p-4 bg-white border border-gray-200 rounded-lg hover:border-blue-300 hover:shadow-md transition-all"
            >
                <div class="flex-shrink-0">
                    <component :is="getFileIcon(attachment.mime_type)" class="w-8 h-8 text-gray-400" />
                </div>

                <div class="flex-1 min-w-0">
                    <a
                        :href="attachment.url"
                        target="_blank"
                        class="text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline block truncate"
                        :title="attachment.original_filename"
                    >
                        {{ attachment.original_filename }}
                    </a>
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
                </div>

                <div class="flex-shrink-0">
                    <a
                        :href="attachment.url"
                        target="_blank"
                        class="text-blue-600 hover:text-blue-800 p-2 rounded hover:bg-blue-50 transition-colors"
                        title="Abrir arquivo"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
