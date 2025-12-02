<template>
  <div class="bg-white dark:bg-dark-secondary rounded shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-2xl font-semibold text-gray-800 dark:text-dark-text-primary">
        Informações Pessoais
      </h3>
    </div>

    <!-- Modal de Sucesso -->
    <SuccessModal :show="showSuccessModal" title="Sucesso!" :message="successMessage"
      @close="showSuccessModal = false" />

    <!-- Modal de Erro -->
    <ErrorModal :show="showErrorModal" title="Erro!" :message="errorMessage" @close="showErrorModal = false" />

    <!-- Mensagem de sucesso do Inertia -->
    <div v-if="$page.props.flash.success && !showSuccessModal"
      class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
      <div class="flex items-center space-x-2 text-green-800 dark:text-green-300">
        <CheckCircleIcon class="w-5 h-5" />
        <span class="font-medium">{{ $page.props.flash.success }}</span>
      </div>
    </div>

    <!-- Mensagens de erro do formulário -->
    <div v-if="hasErrors"
      class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
      <div class="flex items-center space-x-2 text-red-800 dark:text-red-300 mb-2">
        <ExclamationTriangleIcon class="w-5 h-5" />
        <span class="font-medium">Por favor, corrija os seguintes erros:</span>
      </div>
      <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300 space-y-1">
        <li v-for="(error, field) in form.errors" :key="field">
          <span class="font-medium">{{ getFieldLabel(field) }}:</span> {{ error }}
        </li>
      </ul>
    </div>

    <form @submit.prevent="submitForm">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nome Completo *</label>
          <input type="text" v-model="form.name" @input="checkForChanges" :disabled="!isEditing" :class="[
              'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-0 transition-colors dark:bg-dark-accent dark:text-dark-text-primary',
              form.errors.name
                ? 'border-red-300 focus:ring-red-500 focus:border-red-300'
                : 'border-gray-300 dark:border-gray-600 focus:ring-orange-500 focus:border-brand',
              !isEditing ? 'bg-gray-100 dark:bg-gray-700 cursor-not-allowed' : '',
            ]" />
          <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.name }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nome de Utilizador *</label>
          <input type="text" v-model="form.username" @input="checkForChanges" :disabled="!isEditing" :class="[
              'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-0 transition-colors dark:bg-dark-accent dark:text-dark-text-primary',
              form.errors.username
                ? 'border-red-300 focus:ring-red-500 focus:border-red-300'
                : 'border-gray-300 dark:border-gray-600 focus:ring-orange-500 focus:border-brand',
              !isEditing ? 'bg-gray-100 dark:bg-gray-700 cursor-not-allowed' : '',
            ]" />
          <p v-if="form.errors.username" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.username }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Email *
          </label>

          <input type="email" v-model="form.email" disabled
            class="w-full px-4 py-3 border rounded-lg bg-gray-100 dark:bg-dark-accent cursor-not-allowed dark:text-dark-text-primary border-gray-300 dark:border-gray-600" />

          <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.email }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Telefone
          </label>

          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <span class="text-gray-500 dark:text-gray-400">+258</span>
            </div>

            <input type="text" v-model="phoneDisplay" @input="formatPhone" @paste="handlePhonePaste"
              @keydown="handlePhoneKeydown" :disabled="!isEditing" placeholder="84 123 4567" maxlength="11" :class="[
                'w-full pl-14 pr-4 py-3 border rounded-lg focus:outline-none focus:ring-0 transition-colors dark:bg-dark-accent dark:text-dark-text-primary',
                form.errors.phone
                  ? 'border-red-300 focus:ring-red-500 focus:border-red-300'
                  : 'border-gray-300 dark:border-gray-600 focus:ring-orange-500 focus:border-brand',
                !isEditing ? 'bg-gray-100 dark:bg-gray-700 cursor-not-allowed' : '',
              ]" />
          </div>

          <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.phone }}
          </p>
        </div>
      </div>

      <h4 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-4">
        Informações de Localização
      </h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Província *</label>
          <select v-model="form.province" @change="checkForChanges" :disabled="!isEditing" :class="[
              'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-0 transition-colors dark:bg-dark-accent dark:text-dark-text-primary',
              form.errors.province
                ? 'border-red-300 focus:ring-red-500 focus:border-red-300'
                : 'border-gray-300 dark:border-gray-600 focus:ring-orange-500 focus:border-brand',
              !isEditing ? 'bg-gray-100 dark:bg-gray-700 cursor-not-allowed' : '',
            ]">
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
          <p v-if="form.errors.province" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.province }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Distrito *</label>
          <input type="text" v-model="form.district" @input="checkForChanges" :disabled="!isEditing"
            :placeholder="'Digite o Distrito'" :class="[
              'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-0 transition-colors dark:bg-dark-accent dark:text-dark-text-primary',
              form.errors.district
                ? 'border-red-300 focus:ring-red-500 focus:border-red-300'
                : 'border-gray-300 dark:border-gray-600 focus:ring-orange-500 focus:border-brand',
              !isEditing ? 'bg-gray-100 dark:bg-gray-700 cursor-not-allowed' : '',
            ]" />

          <p v-if="form.errors.district" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.district }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bairro *</label>
          <input type="text" v-model="form.neighborhood" @input="checkForChanges" :disabled="!isEditing" :class="[
              'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-0 transition-colors dark:bg-dark-accent dark:text-dark-text-primary',
              form.errors.neighborhood
                ? 'border-red-300 focus:ring-red-500 focus:border-red-300'
                : 'border-gray-300 dark:border-gray-600 focus:ring-orange-500 focus:border-brand',
              !isEditing ? 'bg-gray-100 dark:bg-gray-700 cursor-not-allowed' : '',
            ]" />
          <p v-if="form.errors.neighborhood" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.neighborhood }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rua (Opcional)</label>
          <input type="text" v-model="form.street" @input="checkForChanges" :disabled="!isEditing" :class="[
              'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-0 transition-colors dark:bg-dark-accent dark:text-dark-text-primary',
              form.errors.street
                ? 'border-red-300 focus:ring-red-500 focus:border-red-300'
                : 'border-gray-300 dark:border-gray-600 focus:ring-orange-500 focus:border-brand',
              !isEditing ? 'bg-gray-100 dark:bg-gray-700 cursor-not-allowed' : '',
            ]" placeholder="Opcional" />
          <p v-if="form.errors.street" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.street }}
          </p>
        </div>
      </div>

      <div class="flex justify-end mt-8 space-x-3">
        <!-- Botão Cancelar - aparece apenas no modo edição -->
        <button v-if="isEditing" type="button" @click="cancelEdit" :disabled="form.processing"
          class="bg-gray-500 px-6 py-2 rounded font-medium transition-colors flex items-center space-x-2 border border-gray-300 dark:border-gray-600 text-white dark:text-gray-300 hover:bg-gray-600 dark:hover:bg-gray-700">
          <XMarkIcon class="w-4 h-4" />
          <span>Cancelar</span>
        </button>

        <!-- Botão principal - alterna entre Editar e Guardar -->
        <button type="button" v-if="!isEditing" @click="enableEditing"
          class="px-6 py-2 rounded font-medium transition-colors flex items-center space-x-2 bg-brand hover:bg-orange-600 text-white">
          <PencilIcon class="w-4 h-4" />
          <span>Editar</span>
        </button>

        <button type="submit" v-else :disabled="form.processing || !hasChanges" :class="[
            'px-6 py-2 rounded font-medium transition-colors flex items-center space-x-2 text-white',
            form.processing || !hasChanges
              ? 'bg-brand cursor-not-allowed'
              : 'bg-brand hover:bg-orange-600',
          ]">
          <CheckIcon class="w-4 h-4" />
          <span>{{ form.processing ? "A Guardar..." : "Guardar" }}</span>
        </button>
      </div>
    </form>
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
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-dark-secondary rounded-lg shadow-xl max-w-md w-full p-6">
        <div class="text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/30">
            <CheckCircleIcon class="h-6 w-6 text-green-600 dark:text-green-400" />
          </div>
          <h3 class="mt-3 text-lg font-semibold text-gray-900 dark:text-dark-text-primary">{{ title }}</h3>
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ message }}</p>
          <div class="mt-4">
            <button
              @click="$emit('close')"
              class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 rounded-md transition-colors"
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
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-dark-secondary rounded-lg shadow-xl max-w-md w-full p-6">
        <div class="text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30">
            <XCircleIcon class="h-6 w-6 text-red-600 dark:text-red-400" />
          </div>
          <h3 class="mt-3 text-lg font-semibold text-gray-900 dark:text-dark-text-primary">{{ title }}</h3>
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ message }}</p>
          <div class="mt-4">
            <button
              @click="$emit('close')"
              class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-500 hover:bg-red-600 rounded-md transition-colors"
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
  form.patch(route('profile.info'), {
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
