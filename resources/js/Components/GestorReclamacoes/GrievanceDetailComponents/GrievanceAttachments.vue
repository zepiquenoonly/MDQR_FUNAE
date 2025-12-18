<template>
  <div
    class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
  >
    <AttachmentsGallery
      :attachments="complaint.attachments"
      :title="`Anexos (${complaint.attachments?.length || 0})`"
      empty-message="Sem anexos no momento"
    />
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
import AttachmentsGallery from '@/Components/Grievance/AttachmentsGallery.vue';

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
