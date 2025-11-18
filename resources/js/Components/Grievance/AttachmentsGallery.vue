<script setup>
import { DocumentIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';

defineProps({
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
    if (mimeType.startsWith('image/')) return '🖼️';
    if (mimeType.startsWith('video/')) return '🎥';
    if (mimeType === 'application/pdf') return '📄';
    if (mimeType.includes('word')) return '📝';
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return '📊';
    return '📎';
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
            <a 
                v-for="attachment in attachments" 
                :key="attachment.id"
                :href="attachment.url"
                target="_blank"
                class="flex items-center gap-3 p-4 bg-white border border-gray-200 rounded-lg hover:border-brand hover:shadow-md transition-all group"
            >
                <div class="flex-shrink-0 text-3xl">
                    {{ getFileIcon(attachment.mime_type) }}
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
                </div>
                
                <ArrowDownTrayIcon class="w-5 h-5 text-gray-400 group-hover:text-brand flex-shrink-0" />
            </a>
        </div>
    </div>
</template>
