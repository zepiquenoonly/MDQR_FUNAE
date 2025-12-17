<script setup>
import { ref, computed } from "vue";
import {
  DocumentIcon,
  PhotoIcon,
  FilmIcon,
  DocumentTextIcon,
  TableCellsIcon,
  PaperClipIcon,
  MusicalNoteIcon,
  XMarkIcon,
  ArrowDownTrayIcon,
  EyeIcon,
  ExclamationTriangleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  attachments: {
    type: Array,
    default: () => [],
  },
  baseUrl: {
    type: String,
    default: window.location.origin, // URL base do ambiente local
  },
});

const showPreview = ref(false);
const currentAttachment = ref(null);
const errorMessage = ref("");

const formatFileSize = (bytes) => {
  if (!bytes || bytes === 0) return "0 Bytes";
  const k = 1024;
  const sizes = ["Bytes", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + " " + sizes[i];
};

const getFileIcon = (mimeType) => {
  if (!mimeType) return PaperClipIcon;
  if (mimeType.startsWith("image/")) return PhotoIcon;
  if (mimeType.startsWith("video/")) return FilmIcon;
  if (mimeType.startsWith("audio/")) return MusicalNoteIcon;
  if (mimeType === "application/pdf") return DocumentIcon;
  if (mimeType.includes("word")) return DocumentTextIcon;
  if (mimeType.includes("excel") || mimeType.includes("spreadsheet"))
    return TableCellsIcon;
  return PaperClipIcon;
};

const getFileExtension = (filename) => {
  if (!filename) return "";
  const parts = filename.split(".");
  return parts.length > 1 ? parts.pop().toLowerCase() : "";
};

const isImage = (mimeType) => {
  return mimeType && mimeType.startsWith("image/");
};

const isVideo = (mimeType) => {
  return mimeType && mimeType.startsWith("video/");
};

const isAudio = (mimeType) => {
  return mimeType && mimeType.startsWith("audio/");
};

const isPDF = (mimeType) => {
  return mimeType === "application/pdf";
};

const canPreview = (mimeType) => {
  return isImage(mimeType) || isVideo(mimeType) || isAudio(mimeType) || isPDF(mimeType);
};

const getFullUrl = (path) => {
  if (!path) return "";

  // Se o path já é uma URL completa, retorna como está
  if (
    path.startsWith("http://") ||
    path.startsWith("https://") ||
    path.startsWith("//")
  ) {
    return path;
  }

  // Se o path começa com /, remove para evitar duplicação
  let cleanPath = path;
  if (path.startsWith("/")) {
    cleanPath = path.substring(1);
  }

  // Constrói a URL completa
  return `${props.baseUrl}/${cleanPath}`;
};

const openPreview = async (attachment) => {
  if (!canPreview(attachment.mime_type)) {
    downloadFile(attachment);
    return;
  }

  const fullUrl = getFullUrl(attachment.path);

  // Verifica se a URL é válida
  if (!fullUrl) {
    errorMessage.value = "URL do arquivo não encontrada";
    setTimeout(() => (errorMessage.value = ""), 3000);
    return;
  }

  currentAttachment.value = {
    ...attachment,
    fullUrl: fullUrl,
  };

  // Testa se o arquivo está acessível
  try {
    const response = await fetch(fullUrl, { method: "HEAD" });
    if (!response.ok) {
      errorMessage.value = "Arquivo não encontrado no servidor";
      setTimeout(() => (errorMessage.value = ""), 3000);
      return;
    }
  } catch (error) {
    errorMessage.value = "Erro ao acessar o arquivo";
    setTimeout(() => (errorMessage.value = ""), 3000);
    return;
  }

  showPreview.value = true;
};

const closePreview = () => {
  showPreview.value = false;
  currentAttachment.value = null;
  errorMessage.value = "";
};

const downloadFile = async (attachment) => {
  const fullUrl = getFullUrl(attachment.path);

  if (!fullUrl) {
    errorMessage.value = "URL do arquivo inválida";
    setTimeout(() => (errorMessage.value = ""), 3000);
    return;
  }

  try {
    // Primeiro verifica se o arquivo existe
    const response = await fetch(fullUrl);

    if (!response.ok) {
      errorMessage.value = "Arquivo não encontrado ou sem permissão de acesso";
      setTimeout(() => (errorMessage.value = ""), 3000);
      return;
    }

    // Obtém o blob do arquivo
    const blob = await response.blob();

    // Cria uma URL temporária para o blob
    const blobUrl = window.URL.createObjectURL(blob);

    // Cria link de download
    const link = document.createElement("a");
    link.href = blobUrl;
    link.download = attachment.original_filename || `download-${Date.now()}`;
    link.style.display = "none";

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Libera a URL do blob
    window.URL.revokeObjectURL(blobUrl);
  } catch (error) {
    console.error("Erro no download:", error);
    errorMessage.value = "Erro ao baixar o arquivo";
    setTimeout(() => (errorMessage.value = ""), 3000);

    // Fallback: tenta abrir em nova aba
    window.open(fullUrl, "_blank");
  }
};

const openInNewTab = (attachment) => {
  const fullUrl = getFullUrl(attachment.path);
  if (fullUrl) {
    window.open(fullUrl, "_blank");
  } else {
    errorMessage.value = "URL do arquivo inválida";
    setTimeout(() => (errorMessage.value = ""), 3000);
  }
};

// Formata a data de forma segura
const formatDate = (dateString) => {
  if (!dateString) return "Data desconhecida";

  try {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return "Data inválida";

    return date.toLocaleDateString("pt-PT", {
      day: "2-digit",
      month: "short",
      year: "numeric",
    });
  } catch (error) {
    return "Data inválida";
  }
};

const formatDateTime = (dateString) => {
  if (!dateString) return "Data desconhecida";

  try {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return "Data inválida";

    return date.toLocaleDateString("pt-PT", {
      day: "2-digit",
      month: "long",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch (error) {
    return "Data inválida";
  }
};

// Calcula o tamanho total dos anexos
const totalSize = computed(() => {
  return props.attachments.reduce((total, attachment) => {
    return total + (attachment.size || 0);
  }, 0);
});

const totalFiles = computed(() => {
  return props.attachments.length;
});
</script>

<template>
  <div class="space-y-4">
    <div class="flex justify-between items-center">
      <h3 class="text-lg font-semibold text-gray-900">Anexos e Evidências</h3>
      <div v-if="attachments.length > 0" class="text-sm text-gray-500">
        <span>{{ totalFiles }} arquivo{{ totalFiles !== 1 ? "s" : "" }}</span>
        <span class="mx-2">•</span>
        <span>{{ formatFileSize(totalSize) }}</span>
      </div>
    </div>

    <!-- Mensagem de erro -->
    <div
      v-if="errorMessage"
      class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2"
    >
      <ExclamationTriangleIcon class="w-5 h-5" />
      <span>{{ errorMessage }}</span>
    </div>

    <div
      v-if="attachments.length === 0"
      class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300"
    >
      <DocumentIcon class="w-12 h-12 mx-auto mb-2 text-gray-400" />
      <p>Nenhum anexo disponível</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div
        v-for="attachment in attachments"
        :key="attachment.id"
        class="flex items-center gap-3 p-4 bg-white border border-gray-200 rounded-lg hover:border-blue-300 hover:shadow-md transition-all group"
      >
        <div class="flex-shrink-0">
          <component
            :is="getFileIcon(attachment.mime_type)"
            class="w-8 h-8 text-gray-400"
          />
        </div>

        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-1">
            <button
              @click="openPreview(attachment)"
              :class="[
                'text-sm font-medium truncate text-left',
                canPreview(attachment.mime_type)
                  ? 'text-blue-600 hover:text-blue-800 cursor-pointer hover:underline'
                  : 'text-gray-700 cursor-default',
              ]"
              :title="attachment.original_filename || 'Arquivo sem nome'"
            >
              {{ attachment.original_filename || `arquivo-${attachment.id}` }}
            </button>
            <span
              v-if="getFileExtension(attachment.original_filename)"
              class="text-xs text-gray-400 px-2 py-1 bg-gray-100 rounded"
            >
              .{{ getFileExtension(attachment.original_filename) }}
            </span>
          </div>
          <p class="text-xs text-gray-500">
            {{ formatFileSize(attachment.size) }}
            <span class="mx-2">•</span>
            {{ attachment.mime_type || "Tipo desconhecido" }}
          </p>
          <p v-if="attachment.uploaded_at" class="text-xs text-gray-400 mt-1">
            {{ formatDate(attachment.uploaded_at) }}
          </p>
        </div>

        <div
          class="flex-shrink-0 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
        >
          <button
            v-if="canPreview(attachment.mime_type)"
            @click="openPreview(attachment)"
            class="text-blue-600 hover:text-blue-800 p-2 rounded hover:bg-blue-50 transition-colors"
            title="Visualizar"
          >
            <EyeIcon class="w-5 h-5" />
          </button>

          <button
            @click="downloadFile(attachment)"
            class="text-green-600 hover:text-green-800 p-2 rounded hover:bg-green-50 transition-colors"
            title="Download"
          >
            <ArrowDownTrayIcon class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Preview -->
    <div
      v-if="showPreview && currentAttachment"
      class="fixed inset-0 z-50 overflow-y-auto"
    >
      <div
        class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
        @click="closePreview"
      ></div>

      <div class="flex min-h-full items-center justify-center p-4">
        <div
          class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden"
        >
          <!-- Cabeçalho do Modal -->
          <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <div class="flex items-center gap-3">
              <component
                :is="getFileIcon(currentAttachment.mime_type)"
                class="w-6 h-6 text-gray-400"
              />
              <div>
                <h3 class="text-lg font-medium text-gray-900 truncate max-w-md">
                  {{
                    currentAttachment.original_filename ||
                    `arquivo-${currentAttachment.id}`
                  }}
                </h3>
                <p class="text-sm text-gray-500">
                  {{ formatFileSize(currentAttachment.size) }}
                  <span class="mx-2">•</span>
                  {{ currentAttachment.mime_type }}
                </p>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <button
                @click="downloadFile(currentAttachment)"
                class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                title="Download"
              >
                <ArrowDownTrayIcon class="w-5 h-5" />
              </button>
              <button
                @click="closePreview"
                class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
              >
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>
          </div>

          <!-- Conteúdo do Preview -->
          <div class="p-4 overflow-auto max-h-[70vh]">
            <div
              v-if="currentAttachment.fullUrl"
              class="flex items-center justify-center"
            >
              <!-- Preview de Imagem -->
              <div v-if="isImage(currentAttachment.mime_type)" class="max-w-full">
                <img
                  :src="currentAttachment.fullUrl"
                  :alt="currentAttachment.original_filename"
                  class="max-w-full h-auto max-h-[60vh] rounded-lg shadow-sm object-contain"
                  @error="errorMessage = 'Erro ao carregar imagem'"
                />
              </div>

              <!-- Preview de PDF -->
              <div v-else-if="isPDF(currentAttachment.mime_type)" class="w-full">
                <object
                  :data="currentAttachment.fullUrl + '#view=FitH'"
                  type="application/pdf"
                  class="w-full h-[60vh] border rounded-lg"
                  title="PDF Preview"
                >
                  <div class="text-center py-12">
                    <DocumentIcon class="w-12 h-12 mx-auto mb-4 text-gray-400" />
                    <p class="text-gray-600">Não foi possível carregar o PDF.</p>
                    <button
                      @click="downloadFile(currentAttachment)"
                      class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                      Baixar PDF
                    </button>
                  </div>
                </object>
              </div>

              <!-- Preview de Vídeo -->
              <div v-else-if="isVideo(currentAttachment.mime_type)" class="w-full">
                <video
                  :src="currentAttachment.fullUrl"
                  controls
                  class="w-full max-h-[60vh] rounded-lg"
                  @error="errorMessage = 'Erro ao carregar vídeo'"
                >
                  Seu navegador não suporta a tag de vídeo.
                  <a :href="currentAttachment.fullUrl" class="text-blue-600 underline">
                    Clique aqui para baixar o vídeo
                  </a>
                </video>
              </div>

              <!-- Preview de Áudio -->
              <div v-else-if="isAudio(currentAttachment.mime_type)" class="w-full">
                <div class="bg-gray-50 p-8 rounded-lg">
                  <div class="flex items-center gap-4 mb-4">
                    <MusicalNoteIcon class="w-12 h-12 text-gray-400" />
                    <div>
                      <h4 class="font-medium text-gray-900">
                        {{ currentAttachment.original_filename }}
                      </h4>
                      <p class="text-sm text-gray-500">Arquivo de áudio</p>
                    </div>
                  </div>
                  <audio
                    :src="currentAttachment.fullUrl"
                    controls
                    class="w-full"
                    @error="errorMessage = 'Erro ao carregar áudio'"
                  >
                    Seu navegador não suporta o elemento de áudio.
                  </audio>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-12">
              <ExclamationTriangleIcon class="w-12 h-12 mx-auto mb-4 text-red-400" />
              <p class="text-gray-600">URL do arquivo não disponível para preview.</p>
            </div>
          </div>

          <!-- Rodapé do Modal -->
          <div class="p-4 border-t border-gray-200 bg-gray-50">
            <div class="flex justify-between items-center">
              <div class="text-sm text-gray-500">
                <p v-if="currentAttachment.uploaded_at">
                  Enviado em: {{ formatDateTime(currentAttachment.uploaded_at) }}
                </p>
              </div>
              <div class="flex gap-2">
                <button
                  @click="downloadFile(currentAttachment)"
                  class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                >
                  <ArrowDownTrayIcon class="w-4 h-4" />
                  Download
                </button>
                <button
                  @click="closePreview"
                  class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                >
                  Fechar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Melhorias visuais */
img {
  max-height: 60vh;
}

object {
  min-height: 400px;
}

/* Scrollbar personalizada */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
