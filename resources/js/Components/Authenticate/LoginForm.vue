<template>
    <form @submit.prevent="$emit('submit', $event)"
        class="bg-white flex justify-center items-center flex-col text-center h-full w-full py-8 px-6 md:px-12">
        <div class="mb-6 text-center">
            <img src="/images/Emblem_of_Mozambique.svg-2.png" alt="Ícone de autenticação"
                class="h-20 w-20 md:h-24 md:w-24 mx-auto object-contain" />
            <span class="text-base md:text-lg font-semibold block mt-4 tracking-wide">Mecanismo De Diálogo, Queixas E
                Reclamações</span>
        </div>

        <h2 class="text-lg md:text-xl mb-2"><strong>Autenticação</strong></h2>
        <p class="text-gray-600 text-xs md:text-sm mb-6 text-center">Introduza os teus dados para continuar na
            plataforma.</p>

        <div class="w-full max-w-xs">
            <input type="text" name="username" placeholder="Digite o nome de usuário" required
                class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-lg my-1 outline-none focus:bg-white focus:border-brand focus:ring-0 focus:ring-brand transition-all duration-200 text-sm md:text-base shadow-sm"
                :class="{ 'border-red-500 bg-red-50': errors.username }" :disabled="loading" />
            <p v-if="errors.username" class="text-red-500 text-xs mt-1 w-full text-left">{{ errors.username }}</p>

            <div class="relative mt-2">
                <input :type="showPassword ? 'text' : 'password'" name="password" placeholder="Digite a senha" required
                    class="w-full py-3 px-4 pr-10 bg-gray-50 border border-gray-200 rounded-lg my-1 outline-none focus:bg-white focus:border-brand focus:ring-0 focus:ring-brand transition-all duration-200 text-sm md:text-base shadow-sm"
                    :class="{ 'border-red-500 bg-red-50': errors.password }" :disabled="loading" />
                <button type="button" @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600 hover:text-brand transition-colors">
                    <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                    <EyeSlashIcon v-else class="w-5 h-5" />
                </button>
            </div>
            <p v-if="errors.password" class="text-red-500 text-xs mt-1 w-full text-left">{{ errors.password }}</p>

            <div class="flex items-center justify-between w-full my-4">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                        class="h-4 w-4 text-[#F15F22] focus:ring-brand border-gray-300 rounded" :disabled="loading" />
                    <label for="remember" class="ml-2 block text-xs md:text-sm text-gray-900">Lembrar de mim</label>
                </div>
                <a href="#" class="text-xs md:text-sm text-gray-600 hover:text-gray-800">Esqueceu a senha?</a>
            </div>

            <button type="submit" :disabled="loading"
                class="w-full border border-[#F15F22] bg-[#F15F22] text-white font-bold py-3 rounded uppercase tracking-wider transition-transform duration-200 active:scale-95 disabled:opacity-50 mt-4 hover:bg-[#e5561a] text-sm md:text-base">
                <span v-if="loading">Entrando...</span>
                <span v-else>Entrar</span>
            </button>

            <!-- Link para cadastro -->
            <p class="text-xs md:text-sm text-gray-600 mt-6 text-center">
                Não tem uma conta?
                <button type="button" @click="$emit('switch-to-register')"
                    class="text-[#F15F22] font-medium hover:text-[#e5561a] ml-1 focus:outline-none">
                    Cadastre-se agora
                </button>
            </p>
        </div>
    </form>
</template>

<script setup>
import { ref } from 'vue'
import {
    EyeIcon,
    EyeSlashIcon
} from '@heroicons/vue/24/outline'


defineProps({
    loading: {
        type: Boolean,
        default: false
    },
    errors: {
        type: Object,
        default: () => ({})
    }
})

defineEmits(['submit', 'switch-to-register'])

const showPassword = ref(false)
</script>
