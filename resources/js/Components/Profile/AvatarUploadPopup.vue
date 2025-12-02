<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
  >
    <div
      class="bg-white dark:bg-dark-secondary rounded-lg shadow-xl w-full max-w-sm mx-4"
    >
      <!-- Header -->
      <div
        class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-600"
      >
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          Foto de Perfil
        </h3>
        <button
          @click="closePopup"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
        >
          <XMarkIcon class="w-6 h-6" />
        </button>
      </div>

      <!-- Current Avatar Preview -->
      <div class="p-4 flex flex-col items-center">
        <div class="relative mb-4">
          <div
            class="bg-gradient-to-r from-orange-500 to-red-500 rounded-full flex items-center justify-center text-white font-bold mx-auto w-24 h-24 text-2xl"
          >
            {{ userInitials }}
          </div>
          <img
            v-if="currentAvatar"
            :src="currentAvatar"
            :alt="userName"
            class="absolute inset-0 w-24 h-24 rounded-full object-cover"
          />
        </div>

        <!-- Options -->
        <div class="w-full space-y-2">
          <!-- View Photo Option -->
          <button
            v-if="currentAvatar"
            @click="viewPhoto"
            class="w-full flex items-center justify-center space-x-2 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          >
            <EyeIcon class="w-5 h-5" />
            <span>Visualizar Foto</span>
          </button>

          <!-- Change Photo Options -->
          <div class="border-t border-gray-200 dark:border-gray-600 pt-2">
            <button
              @click="openFileInput"
              class="w-full flex items-center justify-center space-x-2 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
            >
              <PhotoIcon class="w-5 h-5" />
              <span>Carregar Foto</span>
            </button>

            <!-- Take Photo Option - Mobile Only -->
            <button
              v-if="isMobile"
              @click="takePhoto"
              class="w-full flex items-center justify-center space-x-2 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
            >
              <CameraIcon class="w-5 h-5" />
              <span>Tirar Foto</span>
            </button>
          </div>

          <!-- Remove Photo Option -->
          <button
            v-if="currentAvatar"
            @click="showDeleteConfirmation = true"
            class="w-full flex items-center justify-center space-x-2 px-4 py-3 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
          >
            <TrashIcon class="w-5 h-5" />
            <span>Remover Foto</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Hidden File Input -->
  <input
    ref="fileInput"
    type="file"
    accept="image/*"
    class="hidden"
    @change="handleFileSelect"
  />

  <!-- Photo Viewer Modal -->
  <div
    v-if="showPhotoViewer"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90"
  >
    <div class="relative max-w-4xl max-h-full">
      <button
        @click="showPhotoViewer = false"
        class="absolute -top-12 right-0 text-white hover:text-gray-300 transition-colors"
      >
        <XMarkIcon class="w-8 h-8" />
      </button>
      <img
        v-if="currentAvatar"
        :src="currentAvatar"
        :alt="userName"
        class="max-w-full max-h-screen object-contain"
      />
    </div>
  </div>

  <!-- Camera Modal -->
  <div
    v-if="showCamera"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90"
  >
    <div class="relative w-full max-w-2xl">
      <!-- Camera Header -->
      <div
        class="absolute top-4 left-0 right-0 flex justify-between items-center px-4 z-10"
      >
        <button
          @click="closeCamera"
          class="text-white hover:text-gray-300 transition-colors"
        >
          <XMarkIcon class="w-6 h-6" />
        </button>
        <h3 class="text-white text-lg font-semibold">Tirar Foto</h3>
        <div class="w-6"></div>
        <!-- Spacer for balance -->
      </div>

      <!-- Camera Feed -->
      <div class="relative">
        <video
          ref="videoElement"
          autoplay
          playsinline
          class="w-full h-auto rounded-lg"
        ></video>
        <canvas ref="canvasElement" class="hidden"></canvas>
      </div>

      <!-- Camera Controls -->
      <div class="absolute bottom-4 left-0 right-0 flex justify-center">
        <button
          @click="capturePhoto"
          class="bg-white rounded-full p-4 hover:bg-gray-100 transition-colors"
        >
          <CameraIcon class="w-8 h-8 text-gray-800" />
        </button>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div
    v-if="showDeleteConfirmation"
    class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50"
  >
    <div
      class="bg-white dark:bg-dark-secondary rounded-lg shadow-xl w-full max-w-sm mx-4"
    >
      <div class="p-6">
        <div
          class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 dark:bg-red-900/30 rounded-full"
        >
          <ExclamationTriangleIcon class="w-6 h-6 text-red-600 dark:text-red-400" />
        </div>
        <h3 class="text-lg font-semibold text-center text-gray-900 dark:text-white mb-2">
          Remover Foto de Perfil
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-6">
          Tem certeza que deseja remover a sua foto de perfil? Esta ação não pode ser
          desfeita.
        </p>
        <div class="flex space-x-3">
          <button
            @click="showDeleteConfirmation = false"
            class="flex-1 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors"
          >
            Cancelar
          </button>
          <button
            @click="removePhoto"
            class="flex-1 px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors flex items-center justify-center space-x-2"
          >
            <TrashIcon class="w-4 h-4" />
            <span>Remover</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast Container -->
  <div class="fixed top-4 right-4 z-[70] space-y-2">
    <div
      v-if="toast.show"
      :class="[
        'flex items-center p-4 rounded-lg shadow-lg transform transition-all duration-300 animate-in slide-in-from-right',
        toast.type === 'success'
          ? 'bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800'
          : 'bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800',
      ]"
      style="animation-duration: 300ms"
    >
      <div
        :class="[
          'flex-shrink-0',
          toast.type === 'success'
            ? 'text-green-600 dark:text-green-400'
            : 'text-red-600 dark:text-red-400',
        ]"
      >
        <CheckCircleIcon v-if="toast.type === 'success'" class="w-5 h-5" />
        <XCircleIcon v-else class="w-5 h-5" />
      </div>
      <div class="ml-3">
        <p
          :class="[
            'text-sm font-medium',
            toast.type === 'success'
              ? 'text-green-800 dark:text-green-300'
              : 'text-red-800 dark:text-red-300',
          ]"
        >
          {{ toast.message }}
        </p>
      </div>
      <button
        @click="hideToast"
        class="ml-auto -mx-1.5 -my-1.5 rounded-lg p-1.5 inline-flex items-center justify-center h-8 w-8"
        :class="[
          toast.type === 'success'
            ? 'text-green-500 hover:bg-green-100 dark:hover:bg-green-900/50'
            : 'text-red-500 hover:bg-red-100 dark:hover:bg-red-900/50',
        ]"
      >
        <XMarkIcon class="w-4 h-4" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import {
  XMarkIcon,
  EyeIcon,
  PhotoIcon,
  CameraIcon,
  TrashIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  XCircleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  user: {
    type: Object,
    required: true,
  },
  currentAvatar: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(["close", "avatar-updated"]);

// Refs
const fileInput = ref(null);
const videoElement = ref(null);
const canvasElement = ref(null);
const showPhotoViewer = ref(false);
const showCamera = ref(false);
const showDeleteConfirmation = ref(false);
const isMobile = ref(false);

// Toast
const toast = ref({
  show: false,
  message: "",
  type: "success", // 'success' or 'error'
});
let toastTimeout = null;

// Inertia Form
const avatarForm = useForm({
  avatar: null,
});

// Computed
const userInitials = computed(() => props.user.initials || "");
const userName = computed(() => props.user.name || "");

// Methods
const closePopup = () => {
  emit("close");
};

const showToast = (message, type = "success") => {
  // Clear existing timeout
  if (toastTimeout) {
    clearTimeout(toastTimeout);
    toastTimeout = null;
  }

  // Hide current toast first
  toast.value.show = false;

  // Show new toast after a brief delay
  setTimeout(() => {
    toast.value = {
      show: true,
      message,
      type,
    };

    // Auto hide after 5 seconds
    toastTimeout = setTimeout(() => {
      hideToast();
    }, 5000);
  }, 100);
};

const hideToast = () => {
  toast.value.show = false;
  if (toastTimeout) {
    clearTimeout(toastTimeout);
    toastTimeout = null;
  }
};

const openFileInput = () => {
  fileInput.value.click();
};

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  console.log("File selected:", file);

  if (file) {
    if (file.type.startsWith("image/")) {
      console.log("Valid image file, starting upload...");
      uploadAvatar(file);
    } else {
      console.log("Invalid file type:", file.type);
      showToast(
        "Por favor, selecione um arquivo de imagem válido (JPEG, PNG, GIF, WebP).",
        "error"
      );
    }
  }
  // Reset input
  event.target.value = "";
};

const viewPhoto = () => {
  showPhotoViewer.value = true;
};

const takePhoto = () => {
  showCamera.value = true;
  initializeCamera();
};

const closeCamera = () => {
  showCamera.value = false;
  stopCamera();
};

const removePhoto = async () => {
  showDeleteConfirmation.value = false;
  console.log("Removing avatar...");

  router.delete("/profile/avatar", {
    preserveScroll: true,
    onSuccess: (page) => {
      console.log("Avatar removed successfully");

      // Verificar se há mensagem de sucesso na resposta
      const successMessage =
        page.props.flash?.success || "Foto de perfil removida com sucesso!";

      showToast(successMessage);
      emit("avatar-updated", null);
      closePopup();
    },
    onError: (errors) => {
      console.error("Error removing avatar:", errors);

      // Tentar obter mensagem de erro da resposta
      let errorMessage = "Erro desconhecido";

      if (errors.message) {
        errorMessage = errors.message;
      } else if (typeof errors === "string") {
        errorMessage = errors;
      }

      showToast("Erro ao remover foto: " + errorMessage, "error");
    },
  });
};

const uploadAvatar = (file) => {
  console.log("Starting avatar upload:", file.name, file.size, file.type);

  avatarForm.avatar = file;

  avatarForm.post("/profile/avatar", {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: (page) => {
      console.log("Upload successful:", page.props.flash);

      // Verificar se há mensagem de sucesso na resposta
      const successMessage = page.props.flash?.success || "Foto carregada com sucesso!";
      const newAvatarUrl = page.props.flash?.avatar_url || page.props.user?.avatar_url;

      showToast(successMessage);
      emit("avatar-updated", newAvatarUrl);
      closePopup();

      // Limpar o formulário
      avatarForm.avatar = null;
    },
    onError: (errors) => {
      console.error("Upload error:", errors);

      // Tentar obter mensagem de erro da resposta
      let errorMessage = "Erro desconhecido ao fazer upload";

      if (errors.avatar && errors.avatar[0]) {
        errorMessage = errors.avatar[0];
      } else if (errors.message) {
        errorMessage = errors.message;
      } else if (typeof errors === "string") {
        errorMessage = errors;
      }

      showToast("Erro ao fazer upload da foto: " + errorMessage, "error");
      avatarForm.avatar = null;
    },
    onFinish: () => {
      console.log("Upload finished");
    },
  });
};

// Camera functions
const initializeCamera = async () => {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({
      video: {
        facingMode: "user",
        width: { ideal: 1280 },
        height: { ideal: 720 },
      },
    });
    if (videoElement.value) {
      videoElement.value.srcObject = stream;
    }
  } catch (error) {
    console.error("Erro ao acessar a câmera:", error);
    showToast("Não foi possível acessar a câmera. Verifique as permissões.", "error");
    closeCamera();
  }
};

const stopCamera = () => {
  if (videoElement.value && videoElement.value.srcObject) {
    const tracks = videoElement.value.srcObject.getTracks();
    tracks.forEach((track) => track.stop());
    videoElement.value.srcObject = null;
  }
};

const capturePhoto = () => {
  if (videoElement.value && canvasElement.value) {
    const context = canvasElement.value.getContext("2d");
    canvasElement.value.width = videoElement.value.videoWidth;
    canvasElement.value.height = videoElement.value.videoHeight;
    context.drawImage(videoElement.value, 0, 0);

    canvasElement.value.toBlob(
      (blob) => {
        const file = new File([blob], "profile-photo.jpg", { type: "image/jpeg" });
        uploadAvatar(file);
        closeCamera();
      },
      "image/jpeg",
      0.8
    );
  }
};

// Check if mobile
const checkMobile = () => {
  isMobile.value = window.innerWidth < 640;
};

// Lifecycle
onMounted(() => {
  checkMobile();
  window.addEventListener("resize", checkMobile);
});

onUnmounted(() => {
  stopCamera();
  if (toastTimeout) {
    clearTimeout(toastTimeout);
  }
  window.removeEventListener("resize", checkMobile);
});
</script>

<style scoped>
@keyframes slideInFromRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.animate-in {
  animation: slideInFromRight 0.3s ease-out;
}
</style>
