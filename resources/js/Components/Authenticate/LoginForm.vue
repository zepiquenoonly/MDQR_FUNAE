<template>
    <form @submit.prevent="handleSubmit"
        class="bg-white flex justify-center items-center flex-col text-center h-full w-full py-8 px-6 md:px-12">
        <div class="mb-6 mt-4 text-center">
            <img src="/images/Logotipo-scaled.png" alt="Ícone de autenticação"
                class="max-w-[180px] w-full h-auto md:max-w-none md:h-20 md:w-44 object-contain mx-auto" />
        </div>

        <h2 class="text-lg text-brand md:text-xl mb-2"><strong>Já tem conta?</strong></h2>
        <div class="w-full max-w-xs">
            <!-- Campo Username -->
            <div class="w-full">
                <input type="text" name="username" placeholder="Digite o nome de usuário" required
                    v-model="form.username" @blur="validateField('username')" @input="clearError('username')"
                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-lg my-1 outline-none focus:bg-white focus:border-brand focus:ring-0 focus:ring-brand transition-all duration-200 text-sm md:text-base shadow-sm"
                    :class="{
                        'border-red-500 focus:border-red-500': hasError('username') || hasAuthError,
                        'border-green-500 focus:border-green-500': fieldValid.username && !hasError('username') && !hasAuthError
                    }" :disabled="loading" />
                <p v-if="validationErrors.username" class="text-red-500 text-xs mt-1 w-full text-left min-h-[16px]">
                    {{ validationErrors.username }}
                </p>
            </div>


            <!-- Campo Password -->
            <div class="w-full mt-2">
                <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'" name="password" placeholder="Digite a senha"
                        required v-model="form.password" @blur="validateField('password')"
                        @input="clearError('password')"
                        class="w-full py-3 px-4 pr-10 bg-gray-50 border border-gray-200 rounded-lg my-1 outline-none focus:bg-white focus:border-brand focus:ring-0 focus:ring-brand transition-all duration-200 text-sm md:text-base shadow-sm"
                        :class="{
                            'border-red-500 focus:border-red-500': hasError('password') || hasAuthError,
                            'border-green-500 focus:border-green-500': fieldValid.password && !hasError('password') && !hasAuthError
                        }" :disabled="loading" />
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600 hover:text-brand transition-colors"
                        :disabled="loading">
                        <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                        <EyeSlashIcon v-else class="w-5 h-5" />
                    </button>
                </div>
                <p v-if="validationErrors.password" class="text-red-500 text-xs mt-1 w-full text-left min-h-[16px]">
                    {{ validationErrors.password }}
                </p>
            </div>

            <!--<div class="flex items-center justify-between w-full my-4">
                
                <a href="#" class="text-xs md:text-sm text-gray-600 hover:text-gray-800"
                    :class="{ 'pointer-events-none opacity-50': loading }">Esqueceu a senha?</a>
            </div>-->

            <button type="submit" :disabled="loading || !isFormValid"
                class="w-full border border-[#F15F22] bg-[#F15F22] text-white font-bold py-3 rounded uppercase tracking-wider transition-all duration-200 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed mt-4 hover:bg-[#e5561a] text-sm md:text-base flex items-center justify-center">
                <div v-if="loading" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                <span>{{ loading ? 'Entrando...' : 'Entrar' }}</span>
            </button>

            <!-- Link para cadastro -->
            <p class="text-xs md:text-sm text-gray-600 mt-6 text-center">
                Não tem uma conta?
                <button type="button" @click="switchToRegister"
                    class="text-[#F15F22] font-medium hover:text-[#e5561a] ml-1 focus:outline-none" :disabled="loading">
                    Cadastre-se agora
                </button>
            </p>

            <!-- Link para voltar à página principal -->
            <div class="mt-4 pt-4 border-t border-gray-200">
                <a href="/"
                    class="text-xs md:text-sm text-gray-700 hover:text-brand font-medium flex items-center justify-center gap-2"
                    :class="{ 'pointer-events-none opacity-50': loading }">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar à Página Principal
                </a>
            </div>
        </div>
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
    },
    success: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['submit', 'switch-to-register', 'clear-error', 'show-error', 'show-success'])

const showPassword = ref(false)

// Estado do formulário
const form = reactive({
    username: '',
    password: '',
    remember: false
})

// Erros de validação local
const validationErrors = reactive({
    username: '',
    password: ''
})

// Campos válidos (para feedback visual)
const fieldValid = reactive({
    username: false,
    password: false
})

// Verificar se há erro no campo
const hasError = (fieldName) => {
    return !!validationErrors[fieldName]
}

// Computed para verificar se há erro de autenticação
const hasAuthError = computed(() => {
    return !!props.errors.auth_error
})

// Regras de validação
const validationRules = {
    username: {
        required: true,
        minLength: 3,
        messages: {
            required: 'Nome de usuário é obrigatório',
            minLength: 'Mínimo 3 caracteres'
        }
    },
    password: {
        required: true,
        minLength: 1,
        messages: {
            required: 'Senha é obrigatória',
            minLength: 'Senha é obrigatória'
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

    fieldValid[fieldName] = true
    return true
}

// Validar todos os campos
const validateAllFields = () => {
    const usernameValid = validateField('username')
    const passwordValid = validateField('password')
    return usernameValid && passwordValid
}

// Limpar erro quando usuário começar a digitar
const clearError = (fieldName) => {
    if (validationErrors[fieldName]) {
        validationErrors[fieldName] = ''
    }
    if (hasAuthError.value) {
        emit('clear-error', 'auth_error')
    }
    emit('clear-error', fieldName)
}

// Computed para verificar se o formulário é válido
const isFormValid = computed(() => {
    return form.username.trim().length >= 3 &&
        form.password.trim().length >= 1 &&
        !validationErrors.username &&
        !validationErrors.password
})

// Função para alternar para registro
const switchToRegister = () => {
    if (!props.loading) {
        emit('switch-to-register')
    }
}

// Handle submit
const handleSubmit = (event) => {
    const isValid = validateAllFields()

    if (!isValid || props.loading) {
        event.preventDefault()
        return
    }

    emit('submit', event)
}

// Watch para detectar erros de autenticação
watch(() => props.errors, (newErrors) => {
    if (newErrors.auth_error) {
        emit('show-error', newErrors.auth_error)
    }
}, { deep: true })

// Watch para detectar sucesso
watch(() => props.success, (newSuccess) => {
    if (newSuccess) {
        emit('show-success')
    }
})
</script>