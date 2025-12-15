<template>
  <div class="flex flex-col h-full overflow-hidden rounded-md bg-gray-50 dark:bg-dark-primary">
    <!-- Header fixo -->
    <div class="flex-shrink-0 px-6 py-5 bg-white border-b dark:bg-dark-secondary dark:border-gray-700">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-2xl font-bold text-gray-900 dark:text-dark-text-primary">
            Informações Pessoais
          </h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Gerencie suas informações de perfil e localização
          </p>
        </div>

        <!-- Botões de ação no header -->
        <div class="flex gap-3">
          <button
            v-if="isEditing"
            type="button"
            @click="cancelEdit"
            :disabled="form.processing"
            class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <XMarkIcon class="w-4 h-4 mr-2" />
            Cancelar
          </button>

          <button
            v-if="!isEditing"
            type="button"
            @click="enableEditing"
            class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-white transition-all duration-200 rounded-lg bg-brand hover:bg-orange-600 hover:shadow-lg"
          >
            <PencilIcon class="w-4 h-4 mr-2" />
            Editar Perfil
          </button>

          <button
            v-else
            type="submit"
            @click="submitForm"
            :disabled="form.processing || !hasChanges"
            :class="[
              'inline-flex items-center px-5 py-2.5 text-sm font-medium text-white transition-all duration-200 rounded-lg',
              form.processing || !hasChanges
                ? 'bg-gray-400 cursor-not-allowed'
                : 'bg-brand hover:bg-orange-600 hover:shadow-lg',
            ]"
          >
            <CheckIcon class="w-4 h-4 mr-2" />
            <span>{{ form.processing ? "A Guardar..." : "Guardar Alterações" }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Conteúdo com scroll -->
    <div class="flex-1 overflow-y-auto">
      <div class="px-6 py-6">
        <!-- Modal de Sucesso -->
        <SuccessModal
          :show="showSuccessModal"
          title="Sucesso!"
          :message="successMessage"
          @close="showSuccessModal = false"
        />

        <!-- Modal de Erro -->
        <ErrorModal
          :show="showErrorModal"
          title="Erro!"
          :message="errorMessage"
          @close="showErrorModal = false"
        />

        <!-- Alerta de sucesso -->
        <transition
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="translate-y-2 opacity-0"
          enter-to-class="translate-y-0 opacity-100"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="translate-y-0 opacity-100"
          leave-to-class="translate-y-2 opacity-0"
        >
          <div
            v-if="$page.props.flash.success && !showSuccessModal"
            class="p-4 mb-6 border border-green-300 rounded-xl bg-green-50 dark:bg-green-900/20 dark:border-green-800"
          >
            <div class="flex items-center space-x-3">
              <div class="flex-shrink-0">
                <CheckCircleIcon class="w-6 h-6 text-green-600 dark:text-green-400" />
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-green-800 dark:text-green-300">
                  {{ $page.props.flash.success }}
                </p>
              </div>
            </div>
          </div>
        </transition>

        <!-- Alerta de erros -->
        <transition
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="translate-y-2 opacity-0"
          enter-to-class="translate-y-0 opacity-100"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="translate-y-0 opacity-100"
          leave-to-class="translate-y-2 opacity-0"
        >
          <div
            v-if="hasErrors"
            class="p-4 mb-6 border border-red-300 rounded-xl bg-red-50 dark:bg-red-900/20 dark:border-red-800"
          >
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0">
                <ExclamationTriangleIcon class="w-6 h-6 text-red-600 dark:text-red-400" />
              </div>
              <div class="flex-1">
                <h4 class="text-sm font-semibold text-red-800 dark:text-red-300">
                  Por favor, corrija os seguintes erros:
                </h4>
                <ul class="mt-2 space-y-1 text-sm text-red-700 list-disc list-inside dark:text-red-300">
                  <li v-for="(error, field) in form.errors" :key="field">
                    <span class="font-medium">{{ getFieldLabel(field) }}:</span> {{ error }}
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </transition>

        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- Card: Informações Básicas -->
          <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-dark-secondary dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h4 class="text-lg font-semibold text-gray-900 dark:text-dark-text-primary">
                Informações Básicas
              </h4>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Dados principais do seu perfil
              </p>
            </div>

            <div class="p-6">
              <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <!-- Nome Completo -->
                <div class="lg:col-span-2">
                  <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Nome Completo *
                  </label>
                  <input
                    type="text"
                    v-model="form.name"
                    @input="checkForChanges"
                    :disabled="!isEditing"
                    :class="[
                      'w-full px-4 py-3 text-sm border rounded-lg transition-all duration-200 dark:bg-dark-accent dark:text-dark-text-primary',
                      form.errors.name
                        ? 'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-200'
                        : 'border-gray-300 dark:border-gray-600 focus:border-brand focus:ring-2 focus:ring-orange-200 dark:focus:ring-orange-900',
                      !isEditing ? 'bg-gray-50 dark:bg-gray-800 cursor-not-allowed opacity-60' : 'bg-white',
                    ]"
                    placeholder="Digite seu nome completo"
                  />
                  <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                    {{ form.errors.name }}
                  </p>
                </div>

                <!-- Nome de Utilizador -->
                <div>
                  <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Nome de Utilizador *
                  </label>
                  <input
                    type="text"
                    v-model="form.username"
                    @input="checkForChanges"
                    :disabled="!isEditing"
                    :class="[
                      'w-full px-4 py-3 text-sm border rounded-lg transition-all duration-200 dark:bg-dark-accent dark:text-dark-text-primary',
                      form.errors.username
                        ? 'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-200'
                        : 'border-gray-300 dark:border-gray-600 focus:border-brand focus:ring-2 focus:ring-orange-200 dark:focus:ring-orange-900',
                      !isEditing ? 'bg-gray-50 dark:bg-gray-800 cursor-not-allowed opacity-60' : 'bg-white',
                    ]"
                    placeholder="Digite seu nome de utilizador"
                  />
                  <p v-if="form.errors.username" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                    {{ form.errors.username }}
                  </p>
                </div>

                <!-- Email -->
                <div>
                  <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Email *
                  </label>
                  <div class="relative">
                    <input
                      type="email"
                      v-model="form.email"
                      disabled
                      class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg cursor-not-allowed bg-gray-50 opacity-60 dark:bg-gray-800 dark:text-dark-text-primary dark:border-gray-600"
                      placeholder="seu@email.com"
                    />
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                      <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                      </svg>
                    </div>
                  </div>
                  <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                    O email não pode ser alterado
                  </p>
                </div>

                <!-- Telefone -->
                <div class="lg:col-span-2">
                  <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Telefone (Opcional)
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                      <span class="text-sm font-medium text-gray-500 dark:text-gray-400">+258</span>
                    </div>
                    <input
                      type="text"
                      v-model="phoneDisplay"
                      @input="formatPhone"
                      @paste="handlePhonePaste"
                      @keydown="handlePhoneKeydown"
                      :disabled="!isEditing"
                      placeholder="84 123 4567"
                      maxlength="11"
                      :class="[
                        'w-full pl-16 pr-4 py-3 text-sm border rounded-lg transition-all duration-200 dark:bg-dark-accent dark:text-dark-text-primary',
                        form.errors.phone
                          ? 'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-200'
                          : 'border-gray-300 dark:border-gray-600 focus:border-brand focus:ring-2 focus:ring-orange-200 dark:focus:ring-orange-900',
                        !isEditing ? 'bg-gray-50 dark:bg-gray-800 cursor-not-allowed opacity-60' : 'bg-white',
                      ]"
                    />
                  </div>
                  <p v-if="form.errors.phone" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                    {{ form.errors.phone }}
                  </p>
                  <p v-else class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                    Formato: 84 123 4567
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Card: Informações de Localização -->
          <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-dark-secondary dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h4 class="text-lg font-semibold text-gray-900 dark:text-dark-text-primary">
                Informações de Localização
              </h4>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Onde você está localizado
              </p>
            </div>

            <div class="p-6">
              <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">
                <!-- Província -->
                <div>
                  <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Província *
                  </label>
                  <select
                    v-model="form.province"
                    @change="checkForChanges"
                    :disabled="!isEditing"
                    :class="[
                      'w-full px-4 py-3 text-sm border rounded-lg transition-all duration-200 dark:bg-dark-accent dark:text-dark-text-primary',
                      form.errors.province
                        ? 'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-200'
                        : 'border-gray-300 dark:border-gray-600 focus:border-brand focus:ring-2 focus:ring-orange-200 dark:focus:ring-orange-900',
                      !isEditing ? 'bg-gray-50 dark:bg-gray-800 cursor-not-allowed opacity-60' : 'bg-white',
                    ]"
                  >
                    <option value="">Selecionar Província</option>
                    <option value="Maputo">Maputo</option>
                    <option value="Gaza">Gaza</option>
                    <option value="Inhambane">Inhambane</option>
                    <option value="Sofala">Sofala</option>
                    <option value="Manica">Manica</option>
                    <option value="Zambézia">Zambézia</option>
                    <option value="Nampula">Nampula</option>
                    <option value="Cabo Delgado">Cabo Delgado</option>
                    <option value="Niassa">Niassa</option>
                    <option value="Tete">Tete</option>
                  </select>
                  <p v-if="form.errors.province" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                    {{ form.errors.province }}
                  </p>
                </div>

                <!-- Distrito -->
                <div>
                  <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Distrito *
                  </label>
                  <input
                    type="text"
                    v-model="form.district"
                    @input="checkForChanges"
                    :disabled="!isEditing"
                    placeholder="Digite o Distrito"
                    :class="[
                      'w-full px-4 py-3 text-sm border rounded-lg transition-all duration-200 dark:bg-dark-accent dark:text-dark-text-primary',
                      form.errors.district
                        ? 'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-200'
                        : 'border-gray-300 dark:border-gray-600 focus:border-brand focus:ring-2 focus:ring-orange-200 dark:focus:ring-orange-900',
                      !isEditing ? 'bg-gray-50 dark:bg-gray-800 cursor-not-allowed opacity-60' : 'bg-white',
                    ]"
                  />
                  <p v-if="form.errors.district" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                    {{ form.errors.district }}
                  </p>
                </div>

                <!-- Bairro -->
                <div>
                  <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Bairro *
                  </label>
                  <input
                    type="text"
                    v-model="form.neighborhood"
                    @input="checkForChanges"
                    :disabled="!isEditing"
                    placeholder="Digite o Bairro"
                    :class="[
                      'w-full px-4 py-3 text-sm border rounded-lg transition-all duration-200 dark:bg-dark-accent dark:text-dark-text-primary',
                      form.errors.neighborhood
                        ? 'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-200'
                        : 'border-gray-300 dark:border-gray-600 focus:border-brand focus:ring-2 focus:ring-orange-200 dark:focus:ring-orange-900',
                      !isEditing ? 'bg-gray-50 dark:bg-gray-800 cursor-not-allowed opacity-60' : 'bg-white',
                    ]"
                  />
                  <p v-if="form.errors.neighborhood" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                    {{ form.errors.neighborhood }}
                  </p>
                </div>

                <!-- Rua -->
                <div>
                  <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Rua <span class="text-gray-400">(Opcional)</span>
                  </label>
                  <input
                    type="text"
                    v-model="form.street"
                    @input="checkForChanges"
                    :disabled="!isEditing"
                    placeholder="Digite a Rua"
                    :class="[
                      'w-full px-4 py-3 text-sm border rounded-lg transition-all duration-200 dark:bg-dark-accent dark:text-dark-text-primary',
                      form.errors.street
                        ? 'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-200'
                        : 'border-gray-300 dark:border-gray-600 focus:border-brand focus:ring-2 focus:ring-orange-200 dark:focus:ring-orange-900',
                      !isEditing ? 'bg-gray-50 dark:bg-gray-800 cursor-not-allowed opacity-60' : 'bg-white',
                    ]"
                  />
                  <p v-if="form.errors.street" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                    {{ form.errors.street }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { watch, computed, ref, onMounted } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import {
  CheckIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  PencilIcon,
  XMarkIcon,
  XCircleIcon,
} from "@heroicons/vue/24/outline";

// Componente SuccessModal
const SuccessModal = {
  props: ["show", "title", "message"],
  emits: ["close"],
  template: `
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
      <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-xl dark:bg-dark-secondary">
        <div class="text-center">
          <div class="flex items-center justify-center w-12 h-12 mx-auto bg-green-100 rounded-full dark:bg-green-900/30">
            <CheckCircleIcon class="w-6 h-6 text-green-600 dark:text-green-400" />
          </div>
          <h3 class="mt-3 text-lg font-semibold text-gray-900 dark:text-dark-text-primary">{{ title }}</h3>
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ message }}</p>
          <div class="mt-4">
            <button
              @click="$emit('close')"
              class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white transition-colors bg-orange-500 rounded-md hover:bg-orange-600"
            >
              Fechar
            </button>
          </div>
        </div>
      </div>
    </div>
  `,
  components: { CheckCircleIcon },
};

// Componente ErrorModal
const ErrorModal = {
  props: ["show", "title", "message"],
  emits: ["close"],
  template: `
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
      <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-xl dark:bg-dark-secondary">
        <div class="text-center">
          <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full dark:bg-red-900/30">
            <XCircleIcon class="w-6 h-6 text-red-600 dark:text-red-400" />
          </div>
          <h3 class="mt-3 text-lg font-semibold text-gray-900 dark:text-dark-text-primary">{{ title }}</h3>
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ message }}</p>
          <div class="mt-4">
            <button
              @click="$emit('close')"
              class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white transition-colors bg-red-500 rounded-md hover:bg-red-600"
            >
              Fechar
            </button>
          </div>
        </div>
      </div>
    </div>
  `,
  components: { XCircleIcon },
};

const props = defineProps({
  user: Object,
});

const page = usePage();

// Estado para controlar o modo de edição
const isEditing = ref(false);
const hasChanges = ref(false);
const showSuccessModal = ref(false);
const showErrorModal = ref(false);
const successMessage = ref("");
const errorMessage = ref("");

// Variável reativa para a exibição formatada do telefone
const phoneDisplay = ref("");

// Guardar os valores originais para cancelar edição
const originalValues = ref({});

// Usar useForm do Inertia para gestão de estado e submissão
const form = useForm({
  name: props.user.name,
  username: props.user.username,
  email: props.user.email,
  phone: props.user.phone,
  province: props.user.province,
  district: props.user.district,
  neighborhood: props.user.neighborhood,
  street: props.user.street,
});

const hasErrors = computed(() => {
  return form.errors && Object.keys(form.errors).length > 0;
});

const getFieldLabel = (field) => {
  const labels = {
    name: "Nome Completo",
    username: "Nome de Utilizador",
    email: "Email",
    phone: "Telefone",
    province: "Província",
    district: "Distrito",
    neighborhood: "Bairro",
    street: "Rua",
  };
  return labels[field] || field;
};

// Função para extrair dígitos do telefone
const extractPhoneDigits = (phone) => {
  if (!phone) return "";

  // Remove tudo que não é dígito
  const digits = phone.replace(/\D/g, "");

  // Se começar com 258 (código do país), remove
  if (digits.startsWith("258")) {
    return digits.slice(3);
  }

  return digits;
};

// Função para formatar os dígitos no formato XX XXX XXXX
const formatDigits = (digits) => {
  if (!digits) return "";

  let formatted = digits;

  // Aplica a formatação: XX XXX XXXX
  if (digits.length > 2) {
    formatted = digits.slice(0, 2) + " " + digits.slice(2, 5);
  }
  if (digits.length > 5) {
    formatted += " " + digits.slice(5, 9);
  }

  return formatted;
};

// Verificar se houve mudanças nos dados
const checkForChanges = () => {
  if (!isEditing.value) return;

  const currentValues = {
    name: form.name,
    username: form.username,
    phone: form.phone,
    province: form.province,
    district: form.district,
    neighborhood: form.neighborhood,
    street: form.street,
  };

  hasChanges.value = Object.keys(currentValues).some(
    (key) => currentValues[key] !== originalValues.value[key]
  );
};

// Função para habilitar a edição
const enableEditing = () => {
  // Guardar os valores atuais para possível cancelamento
  originalValues.value = {
    name: form.name,
    username: form.username,
    phone: form.phone,
    province: form.province,
    district: form.district,
    neighborhood: form.neighborhood,
    street: form.street,
    phoneDisplay: phoneDisplay.value,
  };

  isEditing.value = true;
  hasChanges.value = false;

  // Limpar erros ao iniciar edição
  form.clearErrors();
};

// Função para cancelar a edição
const cancelEdit = () => {
  // Restaurar os valores originais
  form.name = originalValues.value.name;
  form.username = originalValues.value.username;
  form.phone = originalValues.value.phone;
  form.province = originalValues.value.province;
  form.district = originalValues.value.district;
  form.neighborhood = originalValues.value.neighborhood;
  form.street = originalValues.value.street;
  phoneDisplay.value = originalValues.value.phoneDisplay;

  // Limpar erros
  form.clearErrors();

  // Sair do modo edição
  isEditing.value = false;
  hasChanges.value = false;
};

// Função para lidar com colagem de texto
const handlePhonePaste = (event) => {
  if (!isEditing.value) return;

  event.preventDefault();
  const pastedData = event.clipboardData.getData("text");

  // Remove todos os caracteres não numéricos
  let digitsOnly = pastedData.replace(/[^\d]/g, "");

  // Valida o primeiro dígito
  if (digitsOnly.length > 0 && digitsOnly[0] !== "8") {
    digitsOnly = "";
  }

  // Valida o segundo dígito
  if (digitsOnly.length > 1 && !["2", "3", "4", "5", "6", "7"].includes(digitsOnly[1])) {
    digitsOnly = digitsOnly[0];
  }

  // Limita a 9 dígitos
  digitsOnly = digitsOnly.slice(0, 9);

  // Atualiza o campo
  phoneDisplay.value = formatDigits(digitsOnly);

  if (digitsOnly.length === 9) {
    form.phone = "+258 " + phoneDisplay.value;
  } else {
    form.phone = "";
  }

  checkForChanges();
};

// Função para lidar com teclas pressionadas
const handlePhoneKeydown = (event) => {
  if (!isEditing.value) return;

  // Permite apenas: números, backspace, delete, tab, setas
  const allowedKeys = [
    "Backspace",
    "Delete",
    "Tab",
    "ArrowLeft",
    "ArrowRight",
    "ArrowUp",
    "ArrowDown",
    "Home",
    "End",
  ];

  const isDigit = /^\d$/.test(event.key);
  const isAllowedKey = allowedKeys.includes(event.key);

  if (!isDigit && !isAllowedKey) {
    event.preventDefault();
  }

  // Validação em tempo real para primeiro dígito
  if (isDigit) {
    const currentDigits = phoneDisplay.value.replace(/\s/g, "");

    // Primeiro dígito deve ser 8
    if (currentDigits.length === 0 && event.key !== "8") {
      event.preventDefault();
      return;
    }

    // Segundo dígito deve ser entre 2-7
    if (
      currentDigits.length === 1 &&
      !["2", "3", "4", "5", "6", "7"].includes(event.key)
    ) {
      event.preventDefault();
      return;
    }
  }
};

// Função para formatar o telefone durante a digitação
const formatPhone = (event) => {
  if (!isEditing.value) return;

  let digits = event.target.value.replace(/\D/g, "");

  // Permite apenas números começando com 8
  if (digits.length > 0 && digits[0] !== "8") {
    digits = "";
  }

  // Segundo dígito deve estar entre 2 e 7
  if (digits.length > 1 && !["2", "3", "4", "5", "6", "7"].includes(digits[1])) {
    digits = digits[0];
  }

  // Limitar a 9 dígitos reais
  digits = digits.slice(0, 9);

  // Atualizar a versão formatada
  phoneDisplay.value = formatDigits(digits);

  // Atualizar o form apenas se estiver válido
  if (digits.length === 9) {
    form.phone = `+258 ${phoneDisplay.value}`;
  } else {
    form.phone = null;
  }

  checkForChanges();
};

// Watch para detectar mensagens de sucesso do Inertia
watch(
  () => page.props.flash.success,
  (newSuccess) => {
    if (newSuccess) {
      successMessage.value = newSuccess;
      showSuccessModal.value = true;

      // Auto-fechar após 3 segundos
      setTimeout(() => {
        showSuccessModal.value = false;
      }, 3000);
    }
  }
);

// Watch para detectar erros do Inertia
watch(
  () => page.props.flash.error,
  (newError) => {
    if (newError) {
      errorMessage.value = newError;
      showErrorModal.value = true;
    }
  }
);

// Inicializar o campo de telefone quando o componente é montado
onMounted(() => {
  if (props.user.phone) {
    const digits = extractPhoneDigits(props.user.phone);
    phoneDisplay.value = formatDigits(digits);

    if (digits.length === 9) {
      form.phone = "+258 " + phoneDisplay.value;
    }
  }

  // Verificar se há mensagem de sucesso ao carregar o componente
  if (page.props.flash.success) {
    successMessage.value = page.props.flash.success;
    showSuccessModal.value = true;

    setTimeout(() => {
      showSuccessModal.value = false;
    }, 3000);
  }
});

// Watch para atualizar os dados do utilizador
watch(
  () => props.user,
  (newUser) => {
    form.defaults({
      name: newUser.name,
      username: newUser.username,
      email: newUser.email,
      phone: newUser.phone,
      province: newUser.province,
      district: newUser.district,
      neighborhood: newUser.neighborhood,
      street: newUser.street,
    });

    form.reset();

    // Atualiza os dígitos do telefone
    const digits = extractPhoneDigits(newUser.phone);
    phoneDisplay.value = formatDigits(digits);

    if (digits.length === 9) {
      form.phone = "+258 " + phoneDisplay.value;
    }
  },
  { deep: true }
);

// Função para submeter o formulário
const submitForm = () => {
  const digits = phoneDisplay.value.replace(/\s/g, "");

  // Validação do telefone
  if (digits.length > 0 && digits.length !== 9) {
    form.errors.phone = "O número de telefone deve ter 9 dígitos no formato XX XXX XXXX.";
    return;
  }

  // Se o utilizador apagar o telefone → enviar null
  if (digits.length === 0) {
    form.phone = null;
  }

  // Usar o método patch do Inertia form
  form.post(route('profile.info'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      form.clearErrors();
      isEditing.value = false;
      hasChanges.value = false;

      // O modal será mostrado automaticamente pelo watch que monitora page.props.flash.success
    },
    onError: (errors) => {
      // Erros serão automaticamente atribuídos ao form.errors
      if (Object.keys(errors).length > 0) {
        const firstError = Object.values(errors)[0];
        errorMessage.value = firstError;
        showErrorModal.value = true;
      }
    },
    onFinish: () => {
      // Esta função é chamada sempre, independente de sucesso ou erro
    }
  });
};

// Watcher para limpar erros quando o utilizador começa a editar
watch(isEditing, (newVal) => {
  if (newVal) {
    // Limpar erros ao entrar no modo de edição
    form.clearErrors();
  }
});

// Watcher para limpar mensagens de erro quando os dados são corrigidos
watch([() => form.name, () => form.username, () => form.phone, () => form.province, () => form.district, () => form.neighborhood], () => {
  if (hasErrors.value && isEditing.value) {
    // Se houver erros e o utilizador estiver editando, limpar erros específicos quando os campos são modificados
    form.clearErrors();
  }
});
</script>
