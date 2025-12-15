<script setup>
import { DocumentIcon, PhotoIcon, FilmIcon, DocumentTextIcon, TableCellsIcon, PaperClipIcon, MusicalNoteIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    attachments: {
        type: Array,
        default: () => []
    },
    title: {
        type: String,
        default: 'Anexos e Evidências'
    },
    emptyMessage: {
        type: String,
        default: 'Nenhum anexo disponível'
    }
});

const formatFileSize = (bytes) => {
    if (!bytes || bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const getFileIcon = (mimeType) => {
    if (!mimeType) return PaperClipIcon;
    if (mimeType.startsWith('image/')) return PhotoIcon;
    if (mimeType.startsWith('video/')) return FilmIcon;
    if (mimeType.startsWith('audio/')) return MusicalNoteIcon;
    if (mimeType === 'application/pdf') return DocumentIcon;
    if (mimeType.includes('word') || mimeType.includes('document')) return DocumentTextIcon;
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet') || mimeType.includes('csv')) return TableCellsIcon;
    return PaperClipIcon;
};
</script>

<template>
    <div class="space-y-4">
        <h3 v-if="title" class="text-lg font-semibold text-gray-900 dark:text-dark-text-primary">{{ title }}</h3>

        <div v-if="attachments.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-dark-accent rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
            <DocumentIcon class="w-12 h-12 mx-auto mb-2 text-gray-400 dark:text-gray-500" />
            <p>{{ emptyMessage }}</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
                v-for="attachment in attachments"
                :key="attachment.id"
                class="flex items-center gap-3 p-4 bg-white dark:bg-dark-secondary border border-gray-200 dark:border-gray-700 rounded-lg hover:border-brand dark:hover:border-orange-500 hover:shadow-md transition-all group"
            >
                <div class="flex-shrink-0">
                    <component :is="getFileIcon(attachment.mime_type)" class="w-8 h-8 text-gray-400 dark:text-gray-500 group-hover:text-brand dark:group-hover:text-orange-500 transition-colors" />
                </div>

                <div class="flex-1 min-w-0">
                    <a
                        :href="attachment.url"
                        target="_blank"
                        class="text-sm font-medium text-gray-900 dark:text-dark-text-primary hover:text-brand dark:hover:text-orange-500 block truncate transition-colors"
                        :title="attachment.original_filename || attachment.name"
                    >
                        {{ attachment.original_filename || attachment.name }}
                    </a>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ formatFileSize(attachment.size) }}
                        <span v-if="attachment.uploaded_at"> • {{ new Date(attachment.uploaded_at).toLocaleDateString('pt-PT', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        }) }}</span>
                    </p>
                </div>

                <div class="flex-shrink-0">
                    <a
                        :href="attachment.url"
                        target="_blank"
                        class="text-gray-400 hover:text-brand dark:text-gray-500 dark:hover:text-orange-500 p-2 rounded hover:bg-gray-50 dark:hover:bg-dark-accent transition-colors"
                        title="Baixar arquivo"
                    >
                        <ArrowDownTrayIcon class="w-5 h-5" />
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
