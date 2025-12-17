<template>
  <div
    class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
  >
    <h2
      class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2"
    >
      <span
        class="flex items-center justify-center h-5 w-5 rounded-full bg-green-100 dark:bg-green-900/20 text-xs flex-shrink-0"
      >
        <PaperClipIcon class="h-4 w-4 text-green-600" />
      </span>
      Anexos ({{ complaint.attachments?.length || 0 }})
    </h2>

    <div
      v-if="!complaint.attachments || complaint.attachments.length === 0"
      class="text-center py-8 text-gray-500 dark:text-gray-400"
    >
      <p class="text-sm">Sem anexos no momento</p>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="attach in complaint.attachments"
        :key="attach.id"
        class="attachment-item"
      >
        <!-- Cabeçalho do anexo -->
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center gap-2">
            <component :is="getFileIcon(attach.name)" class="h-5 w-5 text-gray-400" />
            <span class="text-sm font-medium truncate max-w-[200px]">{{
              attach.name
            }}</span>
          </div>

          <div class="flex items-center gap-2">
            <span
              class="text-xs text-gray-500 px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded"
            >
              {{ formatFileSize(attach.size) }}
            </span>

            <!-- Botão para baixar -->
            <button
              @click.prevent="
                downloadFile(attach.download_url || attach.url, attach.name)
              "
              class="p-1.5 text-gray-500 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors"
              title="Baixar arquivo"
            >
              <ArrowDownTrayIcon class="h-4 w-4" />
            </button>

            <!-- Botão para visualizar (sempre visível, abre em nova aba) -->
            <button
              @click.prevent="viewFile(attach.url, attach.name)"
              class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
              title="Visualizar arquivo em nova aba"
            >
              <EyeIcon class="h-4 w-4" />
            </button>
          </div>
        </div>

        <!-- Preview: image -->
        <div v-if="isImageFile(attach.name)" class="image-viewer">
          <div class="relative">
            <img
              :src="attach.url"
              :alt="attach.name"
              class="w-full max-h-64 object-contain rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer"
              @click="openImageExpand(attach.url, attach.name)"
              loading="lazy"
              :key="attach.url"
            />
            <!-- Botão "Visualizar" na imagem -->
            <button
              @click="viewFile(attach.url, attach.name)"
              class="absolute bottom-2 right-2 bg-black/70 hover:bg-black/90 text-white text-xs px-3 py-1.5 rounded flex items-center gap-1 transition-colors"
              title="Visualizar imagem em nova aba"
            >
              <EyeIcon class="h-3 w-3" />
              <span>Visualizar</span>
            </button>
          </div>
        </div>

        <!-- Preview: video -->
        <div v-else-if="isVideoFile(attach.name)" class="video-viewer">
          <div class="relative">
            <video
              v-if="attach.url"
              :key="attach.url"
              ref="setVideoRef"
              :data-id="attach.id"
              :src="attach.url"
              controls
              preload="metadata"
              class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-black"
              @loadeddata="onVideoLoaded(attach.id)"
              @error="onVideoError(attach.id, $event)"
            >
              <p>Seu navegador não suporta vídeos HTML5.</p>
            </video>

            <!-- loading / error overlays -->
            <div
              v-if="videoLoading[attach.id]"
              class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-800 rounded-lg"
            >
              <div class="text-center">
                <div
                  class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto mb-2"
                ></div>
                <p class="text-xs text-gray-600 dark:text-gray-400">
                  Carregando vídeo...
                </p>
              </div>
            </div>

            <div
              v-if="videoError[attach.id]"
              class="absolute inset-0 flex items-center justify-center bg-red-50 rounded-lg"
            >
              <p class="text-sm text-red-600">Erro ao carregar o vídeo.</p>
            </div>
          </div>
        </div>

        <!-- Preview: audio -->
        <div v-else-if="isAudioFile(attach.name)" class="audio-viewer">
          <div class="flex flex-col space-y-3">
            <!-- Container principal do player de áudio -->
            <div
              class="flex items-center gap-3 p-3 bg-white border border-gray-200 dark:border-gray-700 rounded-lg max-w-md"
            >
              <!-- Botão de play/pause -->
              <button
                @click="toggleAudioPlayback(attach.id)"
                class="flex-shrink-0 w-8 h-8 rounded-full bg-green-600 hover:bg-green-700 text-white flex items-center justify-center transition-colors"
                :disabled="audioLoading[attach.id] || audioError[attach.id]"
                :title="audioLoading[attach.id] ? 'Carregando...' : 'Tocar / Pausar'"
              >
                <template v-if="audioLoading[attach.id]">
                  <div
                    class="animate-spin rounded-full h-3 w-3 border-b-2 border-white"
                  ></div>
                </template>
                <template v-else>
                  <PlayIcon v-if="!playingAudio[attach.id]" class="h-4 w-4 ml-0.5" />
                  <PauseIcon v-else class="h-4 w-4" />
                </template>
              </button>

              <!-- Controles de áudio e barra de progresso -->
              <div class="max-w-[260px]">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-xs text-gray-600 dark:text-gray-400">
                    {{ formatAudioTime(audioCurrentTime[attach.id] || 0) }}
                  </span>
                  <span class="text-xs text-gray-600 dark:text-gray-400">
                    {{ formatAudioTime(audioDuration[attach.id] || 0) }}
                  </span>
                </div>

                <!-- Barra de progresso -->
                <div
                  class="relative h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden"
                >
                  <div
                    class="absolute left-0 top-0 h-full bg-green-600 rounded-full transition-all duration-100"
                    :style="{ width: `${audioProgress[attach.id] || 0}%` }"
                  ></div>
                </div>
              </div>

              <!-- Elemento de áudio oculto -->
              <audio
                v-if="attach.url"
                :key="attach.url"
                ref="setAudioRef"
                :data-id="attach.id"
                :src="attach.url"
                @timeupdate="updateAudioProgress(attach.id)"
                @ended="handleAudioEnd(attach.id)"
                @loadedmetadata="onAudioLoaded(attach.id, $event)"
                @error="onAudioError(attach.id, $event)"
                class="hidden"
              ></audio>
            </div>

            <!-- Mensagem de erro (se houver) -->
            <div v-if="audioError[attach.id]" class="text-xs text-red-600 px-2">
              Erro ao carregar áudio.
            </div>
          </div>
        </div>

        <!-- Outros tipos de arquivo (PDF, DOC, etc.) -->
        <div v-else class="other-file">
          <a
            :href="attach.url"
            target="_blank"
            rel="noopener noreferrer"
            class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-dark-accent border border-gray-200 dark:border-gray-600 hover:border-green-500 transition-all"
            @click.prevent="viewFile(attach.url, attach.name)"
          >
            <DocumentTextIcon class="h-8 w-8 text-gray-400" />
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900 dark:text-dark-text-primary">
                {{ attach.name }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                {{ getFileTypeDescription(attach.name) }}
              </p>
            </div>
            <ArrowTopRightOnSquareIcon class="h-5 w-5 text-gray-400" />
          </a>
        </div>
      </div>
    </div>

    <!-- Modal expandido para imagens -->
    <div
      v-if="showImageExpanded"
      class="fixed inset-0 z-[100] bg-black/95 flex items-center justify-center p-4"
      @click.self="closeImageExpand"
    >
      <div class="relative w-full max-w-6xl max-h-[90vh]">
        <!-- Botão fechar (X) -->
        <button
          @click="closeImageExpand"
          class="absolute -top-10 right-0 text-white hover:text-gray-300 bg-black/50 hover:bg-black/70 rounded-full p-2 transition-colors z-10"
          title="Fechar (Esc)"
        >
          <XMarkIcon class="h-6 w-6" />
        </button>

        <!-- Container da imagem com controles -->
        <div class="relative w-full h-full">
          <img
            :src="expandedImageUrl"
            :alt="expandedImageName"
            class="w-full h-auto max-h-[85vh] object-contain rounded-lg"
            @click.stop
          />

          <!-- Nome da imagem e botões na parte inferior -->
          <div
            class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4 rounded-b-lg"
          >
            <div class="flex items-center justify-between">
              <div class="text-white text-sm font-medium truncate max-w-[70%]">
                {{ expandedImageName }}
              </div>
              <div class="flex items-center gap-2">
                <!-- Botão para baixar imagem expandida -->
                <button
                  @click="downloadFile(expandedImageUrl, expandedImageName)"
                  class="flex items-center gap-2 px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white text-xs rounded transition-colors"
                  title="Baixar imagem"
                >
                  <ArrowDownTrayIcon class="h-3 w-3" />
                  <span>Baixar</span>
                </button>

                <!-- Botão para visualizar em nova aba -->
                <button
                  @click="viewFile(expandedImageUrl, expandedImageName)"
                  class="flex items-center gap-2 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded transition-colors"
                  title="Visualizar em nova aba"
                >
                  <EyeIcon class="h-3 w-3" />
                  <span>Abrir em nova aba</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Instrução de tecla ESC -->
        <div
          class="absolute top-4 left-1/2 transform -translate-x-1/2 text-white/70 text-xs"
        >
          Pressione <kbd class="px-2 py-1 bg-white/20 rounded mx-1">ESC</kbd> para fechar
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onUnmounted, onMounted, onBeforeUnmount } from "vue";
import {
  PaperClipIcon,
  DocumentTextIcon,
  ArrowDownTrayIcon,
  PlayIcon,
  PauseIcon,
  XMarkIcon,
  ArrowTopRightOnSquareIcon,
  PhotoIcon,
  VideoCameraIcon,
  MusicalNoteIcon,
  EyeIcon,
} from "@heroicons/vue/24/outline";

/* Props */
const props = defineProps({
  complaint: { type: Object, required: true },
});

/* Refs e estados */
const videoRefs = ref({}); // armazenar elementos <video>
const audioRefs = ref({}); // armazenar elementos <audio>

const playingAudio = reactive({});
const audioCurrentTime = reactive({});
const audioDuration = reactive({});
const audioProgress = reactive({});
const audioLoading = reactive({});
const audioError = reactive({});

const videoLoading = reactive({});
const videoError = reactive({});

/* Estados para imagem expandida */
const showImageExpanded = ref(false);
const expandedImageUrl = ref("");
const expandedImageName = ref("");

/* Helpers de tipo */
const isImageFile = (fileName) =>
  /\.(jpg|jpeg|png|gif|bmp|webp|svg|tiff)$/i.test(fileName || "");
const isVideoFile = (fileName) =>
  /\.(mp4|webm|ogg|mov|avi|wmv|flv|mkv|m4v)$/i.test(fileName || "");
const isAudioFile = (fileName) =>
  /\.(mp3|wav|ogg|flac|m4a|aac|wma)$/i.test(fileName || "");
const isPdfFile = (fileName) => /\.(pdf)$/i.test(fileName || "");
const isDocumentFile = (fileName) =>
  /\.(doc|docx|xls|xlsx|ppt|pptx|txt|rtf|csv)$/i.test(fileName || "");

const getFileIcon = (fileName) => {
  if (isImageFile(fileName)) return PhotoIcon;
  if (isVideoFile(fileName)) return VideoCameraIcon;
  if (isAudioFile(fileName)) return MusicalNoteIcon;
  if (isPdfFile(fileName)) return DocumentTextIcon;
  return DocumentTextIcon;
};

const getFileTypeDescription = (fileName) => {
  if (isPdfFile(fileName)) return "Documento PDF - Clique para visualizar ou baixar";
  if (isDocumentFile(fileName)) return "Documento - Clique para visualizar ou baixar";
  return "Clique para visualizar ou baixar";
};

/* Formatters */
const formatFileSize = (bytes) => {
  if (!bytes && bytes !== 0) return "N/A";
  let size = Number(bytes);
  if (isNaN(size)) return "N/A";
  const units = ["B", "KB", "MB", "GB"];
  let idx = 0;
  while (size >= 1024 && idx < units.length - 1) {
    size /= 1024;
    idx++;
  }
  return `${size.toFixed(1)} ${units[idx]}`;
};

const formatAudioTime = (seconds) => {
  if (!seconds && seconds !== 0) return "0:00";
  const mins = Math.floor(seconds / 60);
  const secs = Math.floor(seconds % 60);
  return `${mins}:${secs.toString().padStart(2, "0")}`;
};

/* Função para lidar com tecla ESC */
const handleKeydown = (event) => {
  if (event.key === "Escape" && showImageExpanded.value) {
    closeImageExpand();
  }
};

/* Inicializar e limpar listener do teclado */
onMounted(() => {
  window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
  window.removeEventListener("keydown", handleKeydown);
});

/* Funções para download e visualização */
const downloadFile = async (url, filename) => {
  console.log("Tentando baixar arquivo:", { url, filename });

  if (!url) {
    console.error("URL inválida para download");
    alert("URL do arquivo não disponível.");
    return;
  }

  try {
    // Adicionar token CSRF se necessário
    const csrfToken = document
      .querySelector('meta[name="csrf-token"]')
      ?.getAttribute("content");
    const headers = {
      Accept: "application/octet-stream",
    };

    if (csrfToken) {
      headers["X-CSRF-TOKEN"] = csrfToken;
    }

    const response = await fetch(url, {
      method: "GET",
      credentials: "include", // Incluir cookies de sessão
      headers: headers,
    });

    if (!response.ok) {
      throw new Error(`Erro ${response.status}: ${response.statusText}`);
    }

    const blob = await response.blob();
    const blobUrl = window.URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = blobUrl;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(blobUrl);
  } catch (error) {
    console.error("Erro ao baixar arquivo:", error);

    // Fallback: abrir em nova aba com parâmetro de download
    let downloadUrl = url;
    if (!downloadUrl.includes("?")) {
      downloadUrl += "?download=1";
    } else if (!downloadUrl.includes("download=")) {
      downloadUrl += "&download=1";
    }

    window.open(downloadUrl, "_blank");
  }
};

/* Função para visualizar arquivo em nova aba */
const viewFile = (url, filename) => {
  console.log("Abrindo arquivo em nova aba:", { url, filename });

  if (!url) {
    console.error("URL inválida para visualização");
    alert("URL do arquivo não disponível.");
    return;
  }

  // Para todos os tipos de arquivo, abrir em nova aba
  window.open(url, "_blank", "noopener,noreferrer");
};

/* Funções para imagem expandida */
const openImageExpand = (url, name) => {
  expandedImageUrl.value = url;
  expandedImageName.value = name;
  showImageExpanded.value = true;
  document.body.style.overflow = "hidden";
};

const closeImageExpand = () => {
  showImageExpanded.value = false;
  expandedImageUrl.value = "";
  expandedImageName.value = "";
  document.body.style.overflow = "";
};

/* Funções para vídeo */
const setVideoRef = (el) => {
  if (!el) return;
  const id = el.dataset?.id;
  if (!id) return;
  videoRefs.value[id] = el;
  videoLoading[id] = true;
  videoError[id] = false;
};

const onVideoLoaded = (id) => {
  videoLoading[id] = false;
  videoError[id] = false;
};

const onVideoError = (id, evt) => {
  videoLoading[id] = false;
  videoError[id] = true;
  console.error("Erro ao carregar vídeo id:", id, evt);
};

/* Funções para áudio */
const setAudioRef = (el) => {
  if (!el) return;
  const id = el.dataset?.id;
  if (!id) return;
  audioRefs.value[id] = el;
  audioLoading[id] = true;
  audioError[id] = false;
};

const onAudioLoaded = (id, evt) => {
  const audio = audioRefs.value[id];
  if (!audio) return;
  audioLoading[id] = false;
  audioDuration[id] = audio.duration || 0;
  audioError[id] = false;
};

const onAudioError = (id, evt) => {
  audioLoading[id] = false;
  audioError[id] = true;
  console.error("Erro ao carregar áudio id:", id, evt);
};

const toggleAudioPlayback = (audioId) => {
  const audio = audioRefs.value[audioId];
  if (!audio) return;

  if (playingAudio[audioId]) {
    audio.pause();
    playingAudio[audioId] = false;
  } else {
    // Pausar outros áudios
    Object.keys(audioRefs.value).forEach((id) => {
      const a = audioRefs.value[id];
      if (a && id !== String(audioId)) {
        a.pause();
        playingAudio[id] = false;
      }
    });

    audio
      .play()
      .then(() => {
        playingAudio[audioId] = true;
      })
      .catch((err) => {
        console.error("Erro ao tentar reproduzir áudio:", err);
      });
  }
};

const updateAudioProgress = (audioId) => {
  const audio = audioRefs.value[audioId];
  if (!audio) return;
  audioCurrentTime[audioId] = audio.currentTime;
  if (audio.duration > 0) {
    audioProgress[audioId] = (audio.currentTime / audio.duration) * 100;
    audioDuration[audioId] = audio.duration;
  }
};

const handleAudioEnd = (audioId) => {
  playingAudio[audioId] = false;
  audioCurrentTime[audioId] = 0;
  audioProgress[audioId] = 0;
};

onUnmounted(() => {
  Object.values(audioRefs.value).forEach((a) => a && a.pause());
  Object.values(videoRefs.value).forEach((v) => v && v.pause());
  window.removeEventListener("keydown", handleKeydown);
});
</script>

<style scoped>
.attachment-item {
  @apply p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30;
}
.image-viewer img:hover {
  @apply opacity-90;
}
.video-viewer video {
  @apply bg-black;
}
.audio-viewer .progress-bar {
  transition: width 0.1s linear;
}

/* Animações para modal de imagem expandida */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.image-expand-enter-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.image-expand-enter-from {
  transform: scale(0.9);
  opacity: 0;
}

.image-expand-leave-active {
  transition: opacity 0.2s ease;
}

.image-expand-leave-to {
  opacity: 0;
}
</style>
