<template>
    <form @submit.prevent="$emit('submit', $event)"
        class="bg-white flex justify-center items-center flex-col text-center h-full py-0 px-4 md:px-12">

        <div class="text-center">
            <img src="/images/Emblem_of_Mozambique.svg-2.png" alt="Ícone de autenticação"
                class="h-20 w-20 md:h-24 md:w-24 mt-4 md:mt-0 mx-auto object-contain" />
            <span class="text-base md:text-lg font-semibold block mt-3 md:mt-4 tracking-wide">Mecanismo De Diálogo, Queixas E
                Reclamações</span>
        </div>

        <h2 class="text-lg md:text-xl"><strong>Registo de Conta</strong></h2>
        <p class="text-gray-600 text-xs md:text-sm mt-2 text-center">Crie sua conta para fazer parte da plataforma.</p>

        <input type="text" name="name" placeholder="Nome de usuário" required
            class="w-full py-3 px-4 bg-gray-100 border border-transparent my-1 outline-none 
                focus:border-[#F15F22] focus:ring-2 focus:ring-[#F15F22]/30 
                transition-all duration-200 text-sm md:text-base"
            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.name }" :disabled="loading" />
        <p v-if="errors.name" class="text-red-500 text-xs mt-1 w-full text-left">{{ errors.name }}</p>

        <input 
            type="email" 
            name="email" 
            placeholder="Email" 
            required
            class="w-full py-3 px-4 bg-gray-100 border border-transparent my-1 outline-none 
                focus:border-[#F15F22] focus:ring-2 focus:ring-[#F15F22]/30 
                transition-all duration-200 text-sm md:text-base"
            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.email }"
            :disabled="loading"
        />
        <p v-if="errors.email" class="text-red-500 text-xs mt-1 w-full text-left">{{ errors.email }}</p>

        <div class="w-full relative">
            <input :type="showPassword ? 'text' : 'password'" name="password" placeholder="Senha" required
                class="w-full py-3 px-4 bg-gray-100 border border-transparent my-1 outline-none 
                focus:border-[#F15F22] focus:ring-2 focus:ring-[#F15F22]/30 
                transition-all duration-200 text-sm md:text-base"
                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.password }" :disabled="loading" />
            <button type="button" @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400" :disabled="loading">
                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
            </button>
        </div>
        <p v-if="errors.password" class="text-red-500 text-xs mt-1 w-full text-left">{{ errors.password }}</p>

        <div class="w-full relative">
            <input :type="showConfirmPassword ? 'text' : 'password'" name="password_confirmation"
                placeholder="Confirmar Senha" required
                class="w-full py-3 px-4 bg-gray-100 border border-transparent my-1 outline-none 
                focus:border-[#F15F22] focus:ring-2 focus:ring-[#F15F22]/30 
                transition-all duration-200 text-sm md:text-base"
                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.password_confirmation || passwordMismatch }" :disabled="loading" />
            <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400" :disabled="loading">
                <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
            </button>
        </div>
        <p v-if="passwordMismatch" class="text-red-500 text-xs mt-1 w-full text-left">As senhas não coincidem</p>
        <p v-if="errors.password_confirmation" class="text-red-500 text-xs mt-1 w-full text-left">{{
            errors.password_confirmation }}</p>
        <p class="text-xs md:text-sm text-gray-600 mt-4 text-center">
            Ao se registrar, você concorda com os
            <a href="#" class="text-[#F15F22] font-medium ml-1 hover:text-[#e5561a]">
                Termos de uso
            </a>
        </p>

        <button type="submit" :disabled="loading || !isFormComplete"
            class="w-full border border-[#F15F22] bg-[#F15F22] text-white font-bold py-3 rounded uppercase tracking-wider transition-transform duration-200 active:scale-95 disabled:opacity-50 mt-4 hover:bg-[#e5561a] text-sm md:text-base">
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
import { ref, computed } from 'vue'

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

defineEmits(['submit', 'switch-to-login'])

const showPassword = ref(false)
const showConfirmPassword = ref(false)

const passwordMismatch = computed(() => {
    return false
})

const isFormComplete = computed(() => {
    return true
})
</script>