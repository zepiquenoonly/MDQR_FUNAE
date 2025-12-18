[file name]: ValidationModal.vue - COM SUPORTE A EDIÇÃO [file content begin]
<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="closeModal" class="relative z-50">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black bg-opacity-25" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-full p-4 text-center">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel
              class="w-full max-w-2xl p-6 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl dark:bg-dark-primary"
            >
              <DialogTitle
                as="h3"
                class="text-lg font-bold text-gray-900 dark:text-white"
              >
                {{ isEditMode ? "Editar Resposta do Director" : "Validar Submissão" }}
              </DialogTitle>

              <div class="mt-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  {{
                    isEditMode
                      ? "Edite sua resposta ao gestor para esta submissão"
                      : "Forneça uma resposta ao gestor para esta submissão"
                  }}
                </p>
              </div>

              <!-- Mensagens de erro do Inertia -->
              <div
                v-if="form.hasErrors"
<<<<<<< HEAD
                class="mt-4 mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg"
=======
                class="p-3 mb-4 border border-red-200 rounded-lg bg-red-50 dark:bg-red-900/20 dark:border-red-700"
>>>>>>> kev-dev
              >
                <div class="flex items-center gap-2 text-red-700 dark:text-red-300">
                  <ExclamationCircleIcon class="w-5 h-5" />
                  <span class="font-medium">Corrija os seguintes erros:</span>
                </div>
                <ul class="mt-2 space-y-1 text-sm text-red-600 dark:text-red-400">
                  <li v-for="(error, field) in form.errors" :key="field">
                    • {{ error }}
                  </li>
                </ul>
              </div>

              <div class="mt-6">
                <div class="space-y-6">
                  <!-- Informação da submissão -->
                  <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                      <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                          Submissão #{{ submission.reference_number }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                          Recebida do gestor:
                          {{
                            formatDate(submission.escalated_at || submission.created_at)
                          }}
                        </p>
                      </div>
                      <span
                        v-if="submission.escalation_reason"
                        class="px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 rounded-full"
                      >
                        {{ submission.escalation_reason }}
                      </span>
                    </div>
                  </div>

                  <!-- Status da validação -->
                  <div>
                    <label
                      class="block mb-3 text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                      Decisão do Director <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                      <label
                        class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                        :class="
                          form.status === 'approved'
                            ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                            : 'border-gray-300 dark:border-gray-600'
                        "
                      >
                        <input
                          v-model="form.status"
                          type="radio"
                          value="approved"
                          class="w-5 h-5 mr-3 text-green-600 focus:ring-green-500"
                        />
                        <div>
                          <div class="font-medium text-gray-900 dark:text-white">
                            Aprovado
                          </div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">
                            Aceitar e manter atribuição
                          </div>
                        </div>
                      </label>

                      <label
                        class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                        :class="
                          form.status === 'rejected'
                            ? 'border-red-500 bg-red-50 dark:bg-red-900/20'
                            : 'border-gray-300 dark:border-gray-600'
                        "
                      >
                        <input
                          v-model="form.status"
                          type="radio"
                          value="rejected"
                          class="w-5 h-5 mr-3 text-red-600 focus:ring-red-500"
                        />
                        <div>
                          <div class="font-medium text-gray-900 dark:text-white">
                            Rejeitado
                          </div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">
                            Devolver ao Gestor
                          </div>
                        </div>
                      </label>

                      <label
                        class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                        :class="
                          form.status === 'needs_revision'
                            ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20'
                            : 'border-gray-300 dark:border-gray-600'
                        "
                      >
                        <input
                          v-model="form.status"
                          type="radio"
                          value="needs_revision"
                          class="w-5 h-5 mr-3 text-yellow-600 focus:ring-yellow-500"
                        />
                        <div>
                          <div class="font-medium text-gray-900 dark:text-white">
                            Revisão
                          </div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">
                            Solicitar ajustes
                          </div>
                        </div>
                      </label>
                    </div>
                    <p
                      v-if="form.errors.status"
                      class="mt-1 text-sm text-red-600 dark:text-red-400"
                    >
                      {{ form.errors.status }}
                    </p>
                  </div>

                  <!-- Comentário obrigatório -->
                  <div>
                    <label
                      class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                      Comentário <span class="text-red-500">*</span>
                      <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{
                          isEditMode
                            ? "(Edite o comentário existente)"
                            : "(Obrigatório para justificar a decisão)"
                        }}
                      </span>
                    </label>
                    <textarea
                      v-model="form.comment"
                      rows="4"
<<<<<<< HEAD
                      :placeholder="
                        isEditMode
                          ? 'Edite seu comentário para o gestor...'
                          : 'Descreva detalhadamente o motivo da sua decisão...'
                      "
                      class="w-full px-4 py-3 border rounded-lg bg-white dark:bg-dark-secondary focus:ring-2 focus:ring-brand focus:border-transparent"
=======
                      placeholder="Descreva detalhadamente o motivo da sua decisão..."
                      class="w-full px-4 py-3 bg-white border rounded-lg dark:bg-dark-secondary focus:ring-2 focus:ring-brand focus:border-transparent"
>>>>>>> kev-dev
                      :class="
                        form.errors.comment
                          ? 'border-red-300 dark:border-red-600'
                          : 'border-gray-300 dark:border-gray-600'
                      "
                      required
                    ></textarea>
                    <div class="flex justify-between mt-1">
                      <p
                        v-if="form.errors.comment"
                        class="text-sm text-red-600 dark:text-red-400"
                      >
                        {{ form.errors.comment }}
                      </p>
                      <div class="ml-auto text-sm text-gray-500 dark:text-gray-400">
                        {{ form.comment.length }}/2000 caracteres
                      </div>
                    </div>
                  </div>

<<<<<<< HEAD
                  <!-- Notificações (não em modo de edição) -->
                  <div
                    v-if="!isEditMode"
                    class="border-t border-gray-200 dark:border-gray-700 pt-4"
                  >
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
=======
                  <!-- Notificações -->
                  <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
>>>>>>> kev-dev
                      Notificar
                    </h4>
                    <div class="space-y-3">
                      <label class="flex items-center justify-between">
                        <div class="flex items-center">
                          <UserCircleIcon class="w-5 h-5 mr-2 text-gray-400" />
                          <span class="text-sm text-gray-700 dark:text-gray-300">
                            Gestor de Reclamações
                          </span>
                        </div>
                        <input
                          v-model="form.notify_manager"
                          type="checkbox"
                          class="text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                      </label>

                      <label class="flex items-center justify-between">
                        <div class="flex items-center">
                          <WrenchIcon class="w-5 h-5 mr-2 text-gray-400" />
                          <span class="text-sm text-gray-700 dark:text-gray-300">
                            Técnico Atribuído
                            <span
                              v-if="submission.assigned_to"
                              class="text-xs text-gray-500"
                            >
                              ({{ submission.assigned_to?.name || "N/A" }})
                            </span>
                          </span>
                        </div>
                        <input
                          v-model="form.notify_technician"
                          type="checkbox"
                          class="text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                      </label>

                      <label
                        v-if="form.status === 'approved'"
                        class="flex items-center justify-between"
                      >
                        <div class="flex items-center">
                          <UserIcon class="w-5 h-5 mr-2 text-gray-400" />
                          <span class="text-sm text-gray-700 dark:text-gray-300">
                            Utente (Submetedor)
                          </span>
                        </div>
                        <input
                          v-model="form.notify_user"
                          type="checkbox"
                          class="text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                      </label>
                    </div>
                  </div>

                  <!-- Mensagem para modo de edição -->
                  <div
                    v-if="isEditMode"
                    class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-700"
                  >
                    <div class="flex items-start gap-3">
                      <InformationCircleIcon
                        class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5"
                      />
                      <div>
                        <p class="text-sm font-medium text-blue-800 dark:text-blue-300">
                          Modo de Edição
                        </p>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                          Você está editando sua resposta existente. A nova versão
                          substituirá a anterior.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex justify-end gap-3 mt-8">
                <button
                  type="button"
                  @click="closeModal"
                  :disabled="isSubmitting"
                  class="px-4 py-2 text-sm font-medium text-gray-700 transition-colors bg-gray-100 rounded-lg dark:text-gray-300 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Cancelar
                </button>
                <button
                  type="button"
                  @click="submitValidation"
                  :disabled="!form.comment.trim() || !form.status || isSubmitting"
                  :class="[
                    'px-6 py-2 text-sm font-medium text-white rounded-lg transition-colors flex items-center gap-2',
                    !form.comment.trim() || !form.status || isSubmitting
                      ? 'bg-gray-400 dark:bg-gray-600 cursor-not-allowed'
                      : getStatusButtonClass(form.status),
                  ]"
                >
                  <template v-if="isSubmitting">
                    <svg
                      class="w-4 h-4 text-white animate-spin"
                      fill="none"
                      viewBox="0 0 24 24"
                    >
                      <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                      ></circle>
                      <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                      ></path>
                    </svg>
                    {{ isEditMode ? "Atualizando..." : "Enviando..." }}
                  </template>
                  <template v-else>
<<<<<<< HEAD
                    <CheckBadgeIcon class="h-5 w-5" />
                    {{
                      isEditMode ? "Atualizar Resposta" : getStatusButtonText(form.status)
                    }}
=======
                    <CheckBadgeIcon class="w-5 h-5" />
                    {{ getStatusButtonText(form.status) }}
>>>>>>> kev-dev
                  </template>
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
<<<<<<< HEAD
import { ref, watch, computed, onMounted } from "vue";
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import {
  CheckBadgeIcon,
  UserCircleIcon,
  WrenchIcon,
  UserIcon,
  ExclamationCircleIcon,
  InformationCircleIcon,
} from "@heroicons/vue/24/outline";
=======
import { ref, watch } from "vue";


// Importações comentadas pois não são usadas diretamente no código
// import {
//   Dialog,
//   DialogPanel,
//   DialogTitle,
//   TransitionChild,
//   TransitionRoot,
// } from "@headlessui/vue";


// Ícones importados do Heroicons Vu
// import {
//   CheckBadgeIcon,
//   UserCircleIcon,
//   WrenchIcon,
//   UserIcon,
//   ExclamationCircleIcon,
// } from "@heroicons/vue/24/outline";

>>>>>>> kev-dev
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
  isOpen: Boolean,
  submission: Object,
  editData: {
    // NOVO: Dados para edição
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["close", "validate", "update"]); // ATUALIZADO: Adicionado "update"

// Use useForm para gerenciar o formulário
const form = useForm({
  status: "",
  comment: "",
  notify_manager: true,
  notify_technician: true,
  notify_user: false,
  is_edit: false, // NOVO: Flag para modo de edição
  validation_id: null, // NOVO: ID da validação a ser editada
});

const isSubmitting = ref(false);
const isEditMode = ref(false); // NOVO: Estado para modo de edição

// Computed property para verificar se há dados de edição
const hasEditData = computed(() => {
  return props.editData && props.editData.id && props.submission?.director_validation;
});

// Inicializar com dados de edição, se houver
onMounted(() => {
  if (hasEditData.value) {
    initializeEditMode();
  }
});

// Observar mudanças nos dados de edição
watch(
  () => props.editData,
  (newData) => {
    if (newData && newData.id) {
      initializeEditMode();
    } else {
      resetToCreateMode();
    }
  },
  { immediate: true }
);

// Observar abertura/fechamento do modal
watch(
  () => props.isOpen,
  (isOpen) => {
    if (isOpen) {
      form.clearErrors();
      if (hasEditData.value) {
        initializeEditMode();
      }
    } else {
      // Não resetamos completamente para manter dados durante edição
      isSubmitting.value = false;
    }
  }
);

// Função para inicializar modo de edição
const initializeEditMode = () => {
  if (!props.submission?.director_validation) return;

  const validation = props.submission.director_validation;
  isEditMode.value = true;

  // Preencher formulário com dados existentes
  form.status = validation.status;
  form.comment = validation.comment || validation.comments || "";
  form.notify_manager = true; // Sempre notificar ao editar
  form.notify_technician = true;
  form.notify_user = validation.status === "approved" ? true : false;
  form.is_edit = true;
  form.validation_id = validation.id;

  // Em modo de edição, desabilitar notificações específicas
  // Ou podemos manter as configurações originais
};

// Função para resetar para modo de criação
const resetToCreateMode = () => {
  isEditMode.value = false;
  form.reset();
  form.is_edit = false;
  form.validation_id = null;
  form.notify_manager = true;
  form.notify_technician = true;
  form.notify_user = false;
  form.clearErrors();
};

const closeModal = () => {
  if (!isSubmitting.value) {
    emit("close");
    // Aguardar um pouco antes de resetar para permitir transição
    setTimeout(() => {
      resetToCreateMode();
    }, 300);
  }
};

const submitValidation = async () => {
  if (!form.comment.trim() || !form.status) {
    return;
  }

  isSubmitting.value = true;

  try {
    if (isEditMode.value && form.validation_id) {
      // Modo de edição: PUT para atualizar
      await form.put(
        `/director/complaints/${props.submission.id}/validation/${form.validation_id}/update`,
        {
          preserveScroll: true,
          preserveState: true,
          onSuccess: () => {
            handleSuccess("Resposta atualizada com sucesso!", "update");
          },
          onError: (errors) => {
            console.error("Erros ao atualizar validação:", errors);
          },
          onFinish: () => {
            isSubmitting.value = false;
          },
        }
      );
    } else {
      // Modo de criação: POST para nova validação
      await form.post(`/director/complaints/${props.submission.id}/validate`, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          handleSuccess("Validação enviada com sucesso!", "validate");
        },
        onError: (errors) => {
          console.error("Erros de validação:", errors);
        },
        onFinish: () => {
          isSubmitting.value = false;
        },
      });
    }
  } catch (error) {
    console.error("Erro ao enviar validação:", error);
    isSubmitting.value = false;
  }
};

const handleSuccess = (message, eventType) => {
  // Resetar formulário
  form.reset();
  form.clearErrors();

  // Fechar modal
  emit("close");

  // Emitir evento apropriado
  if (eventType === "update") {
    emit("update", { ...form.data(), message });
  } else {
    emit("validate", { ...form.data(), message });
  }

  // Resetar modo
  setTimeout(() => {
    resetToCreateMode();
  }, 300);
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString("pt-PT", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch (error) {
    return "Data inválida";
  }
};

const getStatusButtonClass = (status) => {
  const classes = {
    approved: "bg-green-600 hover:bg-green-700",
    rejected: "bg-red-600 hover:bg-red-700",
    needs_revision: "bg-yellow-600 hover:bg-yellow-700",
  };
  return classes[status] || "bg-blue-600 hover:bg-blue-700";
};

const getStatusButtonText = (status) => {
  const texts = {
    approved: "Aprovar",
    rejected: "Rejeitar",
    needs_revision: "Solicitar Revisão",
  };
  return texts[status] || "Validar";
};
</script>
