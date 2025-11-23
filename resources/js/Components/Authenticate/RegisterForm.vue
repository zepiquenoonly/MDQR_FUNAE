<template>
    <form @submit.prevent="handleSubmit"
        class="bg-white flex justify-center items-center flex-col text-center h-full py-0 px-4 md:px-12">

        <div class="text-center">
            <img src="/images/Logotipo-scaled.png" alt="Ícone de autenticação"
                class="md:h-20 md:w-44 object-contain mx-auto" />
        </div>

        <h2 class="text-lg md:text-xl mt-6 mb-4"><strong>Ainda não tem Conta?</strong></h2>

        <!-- Campo Email -->
        <div class="w-full">
            <input type="email" name="email" placeholder="Email" required v-model="form.email"
                @blur="validateField('email')" @input="clearError('email')"
                class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-lg my-1 outline-none focus:bg-white focus:border-brand focus:ring-0 focus:ring-brand transition-all duration-200 text-sm md:text-base shadow-sm"
                :class="{
                    'border-red-500 focus:border-red-500': validationErrors.email || errors.email,
                    'border-green-500 focus:border-green-500': fieldValid.email && !errors.email
                }" :disabled="loading" />
            <p v-if="validationErrors.email || errors.email" class="text-red-500 text-xs mt-1 text-left min-h-[16px]">
                {{ validationErrors.email || errors.email }}
            </p>
        </div>

        <!-- Campo Username -->
        <div class="w-full">
            <input type="text" name="username" placeholder="Nome de usuário" required v-model="form.username"
                @blur="validateField('username')" @input="clearError('username')"
                class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-lg my-1 outline-none focus:bg-white focus:border-brand focus:ring-0 focus:ring-brand transition-all duration-200 text-sm md:text-base shadow-sm"
                :class="{
                    'border-red-500 focus:border-red-500': validationErrors.username || errors.username,
                    'border-green-500 focus:border-green-500': fieldValid.username && !errors.username
                }" :disabled="loading" />
            <p v-if="validationErrors.username || errors.username"
                class="text-red-500 text-xs mt-1 text-left min-h-[16px]">
                {{ validationErrors.username || errors.username }}
            </p>
        </div>

        <!-- Campo Password -->
        <div class="w-full">
            <div class="relative">
                <input :type="showPassword ? 'text' : 'password'" name="password" placeholder="Senha" required
                    v-model="form.password" @blur="validateField('password')"
                    @input="clearError('password'); validatePasswordMatch()"
                    class="w-full py-3 px-4 pr-10 bg-gray-50 border border-gray-200 rounded-lg my-1 outline-none focus:bg-white focus:border-brand focus:ring-0 focus:ring-brand transition-all duration-200 text-sm md:text-base shadow-sm"
                    :class="{
                        'border-red-500 focus:border-red-500': validationErrors.password || errors.password,
                        'border-green-500 focus:border-green-500': fieldValid.password && !errors.password
                    }" :disabled="loading" />
                <button type="button" @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-brand transition-colors"
                    :disabled="loading">
                    <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                    <EyeSlashIcon v-else class="w-5 h-5" />
                </button>
            </div>

            <!-- Indicador de comprimento da senha -->
            <div v-if="form.password" class="mt-2 mb-1">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-500 text-left">
                        {{ form.password.length }}/8 caracteres
                    </p>
                    <p class="text-xs" :class="form.password.length >= 8 ? 'text-green-500' : 'text-red-500'">
                        {{ form.password.length >= 8 ? '✓ Mínimo atendido' : 'Mínimo 8 caracteres' }}
                    </p>
                </div>
            </div>

            <p v-if="validationErrors.password || errors.password"
                class="text-red-500 text-xs mt-1 text-left min-h-[16px]">
                {{ validationErrors.password || errors.password }}
            </p>
        </div>

        <!-- Campo Confirmar Senha -->
        <div class="w-full">
            <div class="relative">
                <input :type="showConfirmPassword ? 'text' : 'password'" name="password_confirmation"
                    placeholder="Confirmar Senha" required v-model="form.password_confirmation"
                    @blur="validatePasswordMatch()"
                    @input="clearError('password_confirmation'); validatePasswordMatch()"
                    class="w-full py-3 px-4 pr-10 bg-gray-50 border border-gray-200 rounded-lg my-1 outline-none focus:bg-white focus:border-brand focus:ring-0 focus:ring-brand transition-all duration-200 text-sm md:text-base shadow-sm"
                    :class="{
                        'border-red-500 focus:border-red-500': validationErrors.password_confirmation || errors.password_confirmation || passwordMismatch,
                        'border-green-500 focus:border-green-500': fieldValid.password_confirmation && !errors.password_confirmation && !passwordMismatch
                    }" :disabled="loading" />
                <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-brand transition-colors"
                    :disabled="loading">
                    <EyeIcon v-if="!showConfirmPassword" class="w-5 h-5" />
                    <EyeSlashIcon v-else class="w-5 h-5" />
                </button>
            </div>

            <p v-if="passwordMismatch || validationErrors.password_confirmation || errors.password_confirmation"
                class="text-red-500 text-xs mt-1 text-left min-h-[16px]">
                {{ passwordMismatch ? 'As senhas não coincidem' : (validationErrors.password_confirmation ||
                    errors.password_confirmation) }}
            </p>
        </div>

        <!--<p class="text-xs md:text-sm text-gray-600 mt-4 text-center">
            Ao se registrar, você concorda com os
            <a href="#" class="text-[#F15F22] font-medium ml-1 hover:text-[#e5561a]">
                Termos de uso
            </a>
        </p>-->

        <button type="submit" :disabled="loading || !isFormValid"
            class="w-full border border-[#F15F22] bg-[#F15F22] text-white font-bold py-3 rounded uppercase tracking-wider transition-transform duration-200 active:scale-95 disabled:opacity-50 mt-4 hover:bg-[#e5561a] text-sm md:text-base flex items-center justify-center">
            <div v-if="loading" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
            <span v-if="loading">Cadastrando...</span>
            <span v-else>Cadastrar</span>
        </button>

        <p class="text-xs md:text-sm text-gray-600 mt-4 mb-4 md:mb-0 text-center">
            Já tem uma conta?
            <button type="button" @click="$emit('switch-to-login')"
                class="text-[#F15F22] font-medium hover:text-[#e5561a] ml-1 focus:outline-none">
                Login
            </button>
        </p>
    </form>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import {
    EyeIcon,
    EyeSlashIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    loading: {
        type: Boolean,
        default: false
    },
    errors: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(['submit', 'switch-to-login'])

const showPassword = ref(false)
const showConfirmPassword = ref(false)

// Estado do formulário
const form = reactive({
    email: '',
    username: '',
    password: '',
    password_confirmation: ''
})

// Erros de validação local
const validationErrors = reactive({
    email: '',
    username: '',
    password: '',
    password_confirmation: ''
})

// Campos válidos (para feedback visual)
const fieldValid = reactive({
    email: false,
    username: false,
    password: false,
    password_confirmation: false
})

// Regras de validação simplificadas
const validationRules = {
    email: {
        required: true,
        pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        messages: {
            required: 'Email é obrigatório',
            pattern: 'Email inválido'
        }
    },
    username: {
        required: true,
        minLength: 3,
        maxLength: 20,
        pattern: /^[a-zA-Z0-9_]+$/,
        messages: {
            required: 'Usuário é obrigatório',
            minLength: 'Mínimo 3 caracteres',
            maxLength: 'Máximo 20 caracteres',
            pattern: 'Use apenas letras, números e _'
        }
    },
    password: {
        required: true,
        minLength: 8,
        messages: {
            required: 'Senha é obrigatória',
            minLength: 'Mínimo 8 caracteres'
        }
    },
    password_confirmation: {
        required: true,
        messages: {
            required: 'Confirme a senha'
        }
    }
}

// Validar campo individual
const validateField = (fieldName) => {
    const rules = validationRules[fieldName]
    const value = form[fieldName].trim()

    validationErrors[fieldName] = ''
    fieldValid[fieldName] = false

    if (rules.required && !value) {
        validationErrors[fieldName] = rules.messages.required
        return false
    }

    if (rules.minLength && value.length < rules.minLength) {
        validationErrors[fieldName] = rules.messages.minLength
        return false
    }

    if (rules.maxLength && value.length > rules.maxLength) {
        validationErrors[fieldName] = rules.messages.maxLength
        return false
    }

    if (rules.pattern && !rules.pattern.test(value)) {
        validationErrors[fieldName] = rules.messages.pattern
        return false
    }

    fieldValid[fieldName] = true
    return true
}

// Validar confirmação de senha
const validatePasswordMatch = () => {
    validationErrors.password_confirmation = ''
    fieldValid.password_confirmation = false

    if (!form.password_confirmation) {
        return false
    }

    if (form.password !== form.password_confirmation) {
        return false
    }

    fieldValid.password_confirmation = true
    return true
}

// Computed para verificar se as senhas coincidem
const passwordMismatch = computed(() => {
    return form.password_confirmation && form.password !== form.password_confirmation
})

// Limpar erro quando usuário começar a digitar
const clearError = (fieldName) => {
    if (validationErrors[fieldName]) {
        validationErrors[fieldName] = ''
    }
}

// Computed para verificar se o formulário é válido
const isFormValid = computed(() => {
    const basicValidation = form.email &&
        form.username.length >= 3 &&
        form.password.length >= 8 &&
        form.password === form.password_confirmation

    const noValidationErrors = !validationErrors.email &&
        !validationErrors.username &&
        !validationErrors.password

    return basicValidation && noValidationErrors
})

// Handle submit
const handleSubmit = (event) => {
    if (!isFormValid.value) {
        // Forçar validação de todos os campos antes do submit
        validateField('email')
        validateField('username')
        validateField('password')
        validatePasswordMatch()
        event.preventDefault()
        return
    }

    emit('submit', event)
}

// Watch para validação automática
watch(() => form.password_confirmation, () => {
    if (form.password_confirmation) {
        validatePasswordMatch()
    }
})

watch(() => form.password, () => {
    if (form.password) {
        validateField('password')
    }
})

const switchToLogin = () => {
    if (!props.loading) {
        emit('switch-to-login')
    }
}
</script>