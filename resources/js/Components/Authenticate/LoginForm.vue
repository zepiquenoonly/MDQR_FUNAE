<template>
    <form @submit.prevent="handleSubmit"
        class="glass flex justify-center items-center flex-col text-center h-full w-full py-8 px-6 md:px-12 backdrop-blur-2xl border border-white/40 shadow-2xl relative overflow-hidden">
        <!-- Decorative gradient overlays -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-primary-500/10 to-orange-500/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-gradient-to-tr from-orange-500/10 to-primary-500/5 rounded-full blur-3xl"></div>

        <!-- Animated background SVG -->
        <div class="absolute top-8 left-8 opacity-5 pointer-events-none animate-pulse">
            <img src='/background.min.svg' class='w-32 h-32'/>
        </div>

        <div class="mb-8 text-center relative z-10 animate-slide-up">
            <h1 class="mb-2 text-2xl md:text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">Bem-vindo de Volta</h1>
            <p class="text-gray-600 text-sm md:text-base">Entre na sua conta</p>
        </div>
        <div class="w-full max-w-md relative z-10 animate-slide-up space-y-6">
            <!-- Campo Username -->
            <div class="relative group">
                <label for="username" class="block mb-2 text-sm font-medium text-gray-700 text-left">Nome de Usuário</label>
                <div class="relative">
                    <UserIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 transition-colors duration-200 group-focus-within:text-primary-600" />
                    <input
                        id="username"
                        type="text"
                        name="username"
                        placeholder="Digite seu nome de usuário"
                        required
                        v-model="form.username"
                        @blur="validateField('username')"
                        @input="clearError('username')"
                        class="w-full py-3 pl-10 pr-4 bg-white/80 backdrop-blur-sm border border-gray-300 rounded-md outline-none focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200 text-sm md:text-base shadow-sm hover:shadow-md"
                        :class="{
                            'border-red-500 focus:border-red-500 focus:ring-red-500/20 animate-shake': hasError('username') || hasAuthError,
                            'border-green-500 focus:border-green-500 focus:ring-green-500/20': fieldValid.username && !hasError('username') && !hasAuthError
                        }"
                        :disabled="loading"
                    />
                </div>
                <p v-if="validationErrors.username" class="text-red-600 text-xs mt-2 text-left animate-shake">
                    {{ validationErrors.username }}
                </p>
            </div>

            <!-- Campo Password -->
            <div class="relative group">
                <label for="password" class="block mb-2 text-sm font-medium text-gray-700 text-left">Senha</label>
                <div class="relative">
                    <LockClosedIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 transition-colors duration-200 group-focus-within:text-primary-600" />
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        name="password"
                        placeholder="Digite sua senha"
                        required
                        v-model="form.password"
                        @blur="validateField('password')"
                        @input="clearError('password')"
                        class="w-full py-3 pl-10 pr-10 bg-white/80 backdrop-blur-sm border border-gray-300 rounded-md outline-none focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200 text-sm md:text-base shadow-sm hover:shadow-md"
                        :class="{
                            'border-red-500 focus:border-red-500 focus:ring-red-500/20 animate-shake': hasError('password') || hasAuthError,
                            'border-green-500 focus:border-green-500 focus:ring-green-500/20': fieldValid.password && !hasError('password') && !hasAuthError
                        }"
                        :disabled="loading"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-primary-600 transition-colors p-1"
                        :disabled="loading"
                    >
                        <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                        <EyeSlashIcon v-else class="w-5 h-5" />
                    </button>
                </div>
                <p v-if="validationErrors.password" class="text-red-600 text-xs mt-2 text-left animate-shake">
                    {{ validationErrors.password }}
                </p>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="loading || !isFormValid"
                class="flex items-center justify-center w-full px-10 py-3 transition-all duration-200 ease-in-out transform bg-primary-600 text-white font-semibold rounded-md hover:bg-orange-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none shadow-md hover:shadow-lg relative overflow-hidden group"
            >
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                <span class="mr-2 relative z-10">{{ loading ? 'Entrando...' : 'Entrar' }}</span>
                <div v-if="loading" class="loader-circle relative z-10"></div>
                <ArrowRightIcon v-else class="w-5 h-5 relative z-10" />
            </button>

            <!-- Link para cadastro -->
            <p class="text-xs md:text-sm text-gray-700 mt-6 text-center">
                Não tem uma conta?
                <button type="button" @click="switchToRegister"
                    class="text-primary-600 font-semibold hover:text-orange-600 ml-1 focus:outline-none transition-colors duration-200 underline decoration-primary-500/30 hover:decoration-orange-600" :disabled="loading">
                    Cadastre-se agora
                </button>
            </p>

            <!-- Link para voltar à página principal -->
            <div class="mt-4 pt-4 border-t border-gray-200/50">
                <a href="/"
                    class="text-xs md:text-sm text-gray-700 hover:text-primary-600 font-medium flex items-center justify-center gap-2 transition-all duration-200 group"
                    :class="{ 'pointer-events-none opacity-50': loading }">
                    <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
    EyeSlashIcon,
    UserIcon,
    LockClosedIcon,
    ArrowRightIcon
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

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}

.animate-slide-up {
    animation: slideUp 0.6s ease-out;
}

.animate-shake {
    animation: shake 0.5s ease-in-out;
}

.loader-circle {
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>