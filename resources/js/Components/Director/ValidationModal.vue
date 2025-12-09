<!-- ValidationModal.vue - CORRIGIDO -->
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
        <div class="flex min-h-full items-center justify-center p-4 text-center">
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
              class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white dark:bg-dark-primary p-6 text-left align-middle shadow-xl transition-all"
            >
              <DialogTitle
                as="h3"
                class="text-lg font-bold text-gray-900 dark:text-white"
              >
                Validar Submissão
              </DialogTitle>

              <!-- Mensagens de erro do Inertia -->
              <div
                v-if="form.hasErrors"
                class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg"
              >
                <div class="flex items-center gap-2 text-red-700 dark:text-red-300">
                  <ExclamationCircleIcon class="h-5 w-5" />
                  <span class="font-medium">Corrija os seguintes erros:</span>
                </div>
                <ul class="mt-2 text-sm text-red-600 dark:text-red-400 space-y-1">
                  <li v-for="(error, field) in form.errors" :key="field">
                    • {{ error }}
                  </li>
                </ul>
              </div>

              <div class="mt-4">
                <div class="space-y-6">
                  <!-- Status da validação -->
                  <div>
                    <label
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3"
                    >
                      Decisão do Director <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
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
                          class="mr-3 h-5 w-5 text-green-600 focus:ring-green-500"
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
                          class="mr-3 h-5 w-5 text-red-600 focus:ring-red-500"
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
                          class="mr-3 h-5 w-5 text-yellow-600 focus:ring-yellow-500"
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
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                    >
                      Comentário <span class="text-red-500">*</span>
                      <span class="text-xs text-gray-500 dark:text-gray-400"
                        >(Obrigatório para justificar a decisão)</span
                      >
                    </label>
                    <textarea
                      v-model="form.comment"
                      rows="4"
                      placeholder="Descreva detalhadamente o motivo da sua decisão..."
                      class="w-full px-4 py-3 border rounded-lg bg-white dark:bg-dark-secondary focus:ring-2 focus:ring-brand focus:border-transparent"
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
                      <div class="text-sm text-gray-500 dark:text-gray-400 ml-auto">
                        {{ form.comment.length }}/2000 caracteres
                      </div>
                    </div>
                  </div>

                  <!-- Notificações -->
                  <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                      Notificar
                    </h4>
                    <div class="space-y-3">
                      <label class="flex items-center justify-between">
                        <div class="flex items-center">
                          <UserCircleIcon class="h-5 w-5 text-gray-400 mr-2" />
                          <span class="text-sm text-gray-700 dark:text-gray-300">
                            Gestor de Reclamações
                          </span>
                        </div>
                        <input
                          v-model="form.notify_manager"
                          type="checkbox"
                          class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                      </label>

                      <label class="flex items-center justify-between">
                        <div class="flex items-center">
                          <WrenchIcon class="h-5 w-5 text-gray-400 mr-2" />
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
                          class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                      </label>

                      <label
                        v-if="form.status === 'approved'"
                        class="flex items-center justify-between"
                      >
                        <div class="flex items-center">
                          <UserIcon class="h-5 w-5 text-gray-400 mr-2" />
                          <span class="text-sm text-gray-700 dark:text-gray-300">
                            Utente (Submetedor)
                          </span>
                        </div>
                        <input
                          v-model="form.notify_user"
                          type="checkbox"
                          class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-8 flex justify-end gap-3">
                <button
                  type="button"
                  @click="closeModal"
                  :disabled="isSubmitting"
                  class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
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
                      class="animate-spin h-4 w-4 text-white"
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
                    Enviando...
                  </template>
                  <template v-else>
                    <CheckBadgeIcon class="h-5 w-5" />
                    {{ getStatusButtonText(form.status) }}
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
import { ref, watch } from "vue";
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
} from "@heroicons/vue/24/outline";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
  isOpen: Boolean,
  submission: Object,
});

const emit = defineEmits(["close", "validate"]);

// Use useForm para gerenciar o formulário
const form = useForm({
  status: "",
  comment: "",
  notify_manager: true,
  notify_technician: true,
  notify_user: false,
});

const isSubmitting = ref(false);

const closeModal = () => {
  if (!isSubmitting.value) {
    emit("close");
    resetForm();
  }
};

const submitValidation = async () => {
  if (!form.comment.trim() || !form.status) {
    return;
  }

  isSubmitting.value = true;

  try {
    await form.post(`/director/complaints/${props.submission.id}/validate`, {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        // Resetar formulário
        form.reset();

        // Limpar erros
        form.clearErrors();

        // Fechar modal
        emit("close");

        // Emitir evento de sucesso
        emit("validate", form.data());
      },
      onError: (errors) => {
        console.error("Erros de validação:", errors);
        // Os erros já são mostrados automaticamente pelo Inertia
      },
      onFinish: () => {
        isSubmitting.value = false;
      },
    });
  } catch (error) {
    console.error("Erro ao enviar validação:", error);
    isSubmitting.value = false;
  }
};

const resetForm = () => {
  form.reset();
  form.clearErrors();
  isSubmitting.value = false;
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

// Verificar se o usuário pode validar
const userCanValidate = () => {
  return (
    props.submission?.escalated === true ||
    props.submission?.metadata?.is_escalated_to_director === true
  );
};

// Limpar erros quando o modal for aberto/fechado
watch(
  () => props.isOpen,
  (isOpen) => {
    if (isOpen) {
      form.clearErrors();
    } else {
      resetForm();
    }
  }
);
</script>
