<template>
  <div class="bg-white dark:bg-dark-secondary rounded shadow-sm p-4 sm:p-6">
    <h3
      class="text-xl sm:text-2xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4 sm:mb-6"
    >
      Segurança da Conta
    </h3>

    <!-- Mensagens de Status -->
    <div
      v-if="$page.props.flash.success"
      class="mb-4 sm:mb-6 p-3 sm:p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg"
    >
      <div class="flex items-center space-x-2 text-green-800 dark:text-green-300">
        <CheckCircleIcon class="w-4 h-4 sm:w-5 sm:h-5" />
        <span class="font-medium text-sm sm:text-base">{{
          $page.props.flash.success
        }}</span>
      </div>
    </div>

    <div
      v-if="passwordForm.errors && Object.keys(passwordForm.errors).length > 0"
      class="mb-4 sm:mb-6 p-3 sm:p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"
    >
      <div class="flex items-center space-x-2 text-red-800 dark:text-red-300 mb-2">
        <ExclamationTriangleIcon class="w-4 h-4 sm:w-5 sm:h-5" />
        <span class="font-medium text-sm sm:text-base"
          >Por favor, corrija os seguintes erros:</span
        >
      </div>
      <ul
        class="list-disc list-inside text-xs sm:text-sm text-red-700 dark:text-red-300 space-y-1"
      >
        <li v-for="error in passwordForm.errors" :key="error">{{ error }}</li>
      </ul>
    </div>

    <!-- Alteração de Password -->
    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 sm:p-6 mb-6">
      <h4 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-4">
        Alterar Palavra-passe
      </h4>

      <form @submit.prevent="updatePassword">
        <div class="space-y-4">
          <!-- Password Atual -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">
              Palavra-passe Actual *
            </label>
            <div class="relative">
              <input
                :type="showCurrentPassword ? 'text' : 'password'"
                v-model="passwordForm.current_password"
                :class="[
                  'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-0 transition-colors dark:bg-dark-accent dark:text-white pr-10',
                  passwordForm.errors.current_password
                    ? 'border-red-300 focus:ring-red-500 focus:border-red-300'
                    : 'border-gray-300 dark:border-gray-600 focus:ring-orange-500 focus:border-brand',
                ]"
                placeholder="Introduza a sua palavra-passe actual"
              />
              <button
                type="button"
                @click="showCurrentPassword = !showCurrentPassword"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white"
              >
                <component
                  :is="showCurrentPassword ? EyeSlashIcon : EyeIcon"
                  class="w-5 h-5"
                />
              </button>
            </div>
            <p
              v-if="passwordForm.errors.current_password"
              class="mt-1 text-sm text-red-600 dark:text-red-400"
            >
              {{ passwordForm.errors.current_password }}
            </p>
          </div>

          <!-- Nova Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">
              Nova Palavra-passe *
            </label>
            <div class="relative">
              <input
                :type="showNewPassword ? 'text' : 'password'"
                v-model="passwordForm.password"
                @input="checkPasswordStrength"
                :class="[
                  'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-0 transition-colors dark:bg-dark-accent dark:text-white pr-10',
                  passwordForm.errors.password
                    ? 'border-red-300 focus:ring-red-500 focus:border-red-300'
                    : 'border-gray-300 dark:border-gray-600 focus:ring-orange-500 focus:border-brand',
                ]"
                placeholder="Introduza a nova palavra-passe"
              />
              <button
                type="button"
                @click="showNewPassword = !showNewPassword"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white"
              >
                <component
                  :is="showNewPassword ? EyeSlashIcon : EyeIcon"
                  class="w-5 h-5"
                />
              </button>
            </div>

            <!-- Indicador de Força da Password -->
            <div class="mt-2">
              <div class="flex justify-between items-center mb-1">
                <span class="text-xs text-gray-600 dark:text-white"
                  >Força da palavra-passe:</span
                >
                <span class="text-xs font-medium" :class="passwordStrengthClass">
                  {{ passwordStrength.text }}
                </span>
              </div>
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div
                  class="h-2 rounded-full transition-all duration-300"
                  :class="passwordStrength.class"
                  :style="{ width: passwordStrength.width }"
                ></div>
              </div>
            </div>

            <p
              v-if="passwordForm.errors.password"
              class="mt-1 text-sm text-red-600 dark:text-red-400"
            >
              {{ passwordForm.errors.password }}
            </p>
          </div>

          <!-- Confirmar Nova Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">
              Confirmar Nova Palavra-passe *
            </label>
            <div class="relative">
              <input
                :type="showConfirmPassword ? 'text' : 'password'"
                v-model="passwordForm.password_confirmation"
                :class="[
                  'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-0 transition-colors dark:bg-dark-accent dark:text-white pr-10',
                  passwordForm.errors.password_confirmation
                    ? 'border-red-300 focus:ring-red-500 focus:border-red-300'
                    : 'border-gray-300 dark:border-gray-600 focus:ring-orange-500 focus:border-brand',
                ]"
                placeholder="Confirme a nova password"
              />
              <button
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white"
              >
                <component
                  :is="showConfirmPassword ? EyeSlashIcon : EyeIcon"
                  class="w-5 h-5"
                />
              </button>
            </div>
            <p
              v-if="passwordForm.errors.password_confirmation"
              class="mt-1 text-sm text-red-600 dark:text-red-400"
            >
              {{ passwordForm.errors.password_confirmation }}
            </p>
          </div>

          <!-- Botão de Submissão -->
          <div class="flex justify-end pt-2">
            <button
              type="submit"
              :disabled="passwordForm.processing"
              :class="[
                'px-6 py-3 rounded font-medium transition-colors flex items-center space-x-2 text-white',
                passwordForm.processing
                  ? 'bg-orange-400 cursor-not-allowed'
                  : 'bg-brand hover:bg-orange-600',
              ]"
            >
              <CheckIcon class="w-4 h-4" />
              <span>{{
                passwordForm.processing ? "A Actualizar..." : "Actualizar"
              }}</span>
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Autenticação de Dois Fatores
    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 sm:p-6">
      <h4 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-4">
        Autenticação de Dois Fatores
      </h4>

      <div class="flex items-center justify-between">
        <div class="flex-1">
          <p class="font-medium text-gray-800 dark:text-dark-text-primary">
            Verificação em Duas Etapas
          </p>
          <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">
            Adicione uma camada extra de segurança à sua conta
          </p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
          <input
            type="checkbox"
            v-model="twoFactorEnabled"
            @change="toggleTwoFactor"
            class="sr-only peer"
          />
          <div
            class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"
          ></div>
        </label>
      </div>

      <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
        * Funcionalidade em desenvolvimento
      </p>
    </div> -->
  </div>
</template>

<script setup>
import { ref, reactive, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import {
  CheckIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  EyeIcon,
  EyeSlashIcon,
} from "@heroicons/vue/24/outline";

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
const twoFactorEnabled = ref(false);

const passwordForm = useForm({
  current_password: "",
  password: "",
  password_confirmation: "",
});

const passwordStrength = reactive({
  width: "0%",
  class: "text-white",
  text: "A palavra-passe deve ter pelo menos 8 caracteres",
});

const passwordStrengthClass = computed(() => {
  if (passwordStrength.width === "0%") return "text-gray-600 dark:text-gray-400";
  if (passwordStrength.class.includes("red")) return "text-red-600 dark:text-red-400";
  if (passwordStrength.class.includes("orange"))
    return "text-orange-600 dark:text-orange-400";
  if (passwordStrength.class.includes("yellow"))
    return "text-yellow-600 dark:text-yellow-400";
  if (passwordStrength.class.includes("green"))
    return "text-green-600 dark:text-green-400";
  return "text-gray-600 dark:text-gray-400";
});

const checkPasswordStrength = () => {
  const password = passwordForm.password;
  let strength = 0;
  let text = "";
  let color = "";

  if (password.length === 0) {
    strength = 0;
    text = "A password deve ter pelo menos 8 caracteres";
    color = "bg-gray-200";
  } else if (password.length < 4) {
    strength = 25;
    text = "Muito fraca";
    color = "bg-red-500";
  } else if (password.length < 8) {
    strength = 50;
    text = "Fraca";
    color = "bg-orange-500";
  } else if (password.length < 12) {
    strength = 75;
    text = "Boa";
    color = "bg-yellow-500";
  } else {
    strength = 100;
    text = "Forte";
    color = "bg-green-500";
  }

  // Adicionar pontos por complexidade
  if (/(?=.*[a-z])/.test(password)) strength += 5;
  if (/(?=.*[A-Z])/.test(password)) strength += 10;
  if (/(?=.*\d)/.test(password)) strength += 10;
  if (/(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?])/.test(password)) strength += 15;

  // Limitar a 100%
  strength = Math.min(strength, 100);

  passwordStrength.width = `${strength}%`;
  passwordStrength.class = color;
  passwordStrength.text = text;
};

const updatePassword = () => {
  passwordForm.patch("/profile/password", {
    preserveScroll: true,
    onSuccess: () => {
      passwordForm.reset();
      showCurrentPassword.value = false;
      showNewPassword.value = false;
      showConfirmPassword.value = false;
      passwordStrength.width = "0%";
      passwordStrength.class = "bg-gray-200";
      passwordStrength.text = "A palavra-passe deve ter pelo menos 8 caracteres";
    },
  });
};
</script>
