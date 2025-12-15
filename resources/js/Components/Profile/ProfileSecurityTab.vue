<template>
    <div class="p-6 bg-white shadow-sm dark:bg-dark-secondary rounded-xl">
        <h3 class="mb-6 text-2xl font-semibold text-gray-800 dark:text-dark-text-primary">Segurança da Conta</h3>

        <div class="space-y-6">
            <!-- Alterar Password -->
            <div class="p-6 border border-gray-200 rounded-lg dark:border-gray-700">
                <h4 class="mb-4 text-lg font-semibold text-gray-800 dark:text-dark-text-primary">Alterar Password</h4>
                <div class="space-y-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Password
                            Actual</label>
                        <div class="relative">
                            <input :type="showCurrentPassword ? 'text' : 'password'"
                                v-model="passwordForm.current_password"
                                class="w-full px-4 py-3 pr-10 transition-colors border border-gray-300 rounded-lg dark:border-gray-600 focus:outline-none focus:ring-0 focus:ring-orange-500 focus:border-brand dark:bg-dark-accent dark:text-dark-text-primary">
                            <button @click="showCurrentPassword = !showCurrentPassword"
                                class="absolute text-gray-400 transform -translate-y-1/2 right-3 top-1/2 hover:text-gray-600 dark:hover:text-gray-300">
                                <EyeIcon v-if="!showCurrentPassword" class="w-4 h-4" />
                                <EyeSlashIcon v-else class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Nova
                            Password</label>
                        <div class="relative">
                            <input :type="showNewPassword ? 'text' : 'password'" v-model="passwordForm.new_password"
                                @input="checkPasswordStrength"
                                class="w-full px-4 py-3 pr-10 transition-colors border border-gray-300 rounded-lg dark:border-gray-600 focus:outline-none focus:ring-0 focus:ring-orange-500 focus:border-brand dark:bg-dark-accent dark:text-dark-text-primary">
                            <button @click="showNewPassword = !showNewPassword"
                                class="absolute text-gray-400 transform -translate-y-1/2 right-3 top-1/2 hover:text-gray-600 dark:hover:text-gray-300">
                                <EyeIcon v-if="!showNewPassword" class="w-4 h-4" />
                                <EyeSlashIcon v-else class="w-4 h-4" />
                            </button>
                        </div>
                        <div class="mt-2 space-y-1">
                            <div class="w-full h-2 bg-gray-200 rounded-full dark:bg-gray-700">
                                <div class="h-2 transition-all duration-300 rounded-full"
                                    :class="passwordStrength.class" :style="{ width: passwordStrength.width }"></div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ passwordStrength.text }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Confirmar Nova
                            Password</label>
                        <div class="relative">
                            <input :type="showConfirmPassword ? 'text' : 'password'"
                                v-model="passwordForm.new_password_confirmation"
                                class="w-full px-4 py-3 pr-10 transition-colors border border-gray-300 rounded-lg dark:border-gray-600 focus:outline-none focus:ring-0 focus:ring-orange-500 focus:border-brand dark:bg-dark-accent dark:text-dark-text-primary">
                            <button @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute text-gray-400 transform -translate-y-1/2 right-3 top-1/2 hover:text-gray-600 dark:hover:text-gray-300">
                                <EyeIcon v-if="!showConfirmPassword" class="w-4 h-4" />
                                <EyeSlashIcon v-else class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <button
                        class="px-6 py-3 font-medium text-white transition-colors bg-orange-500 rounded-lg hover:bg-orange-600">
                        Actualizar Password
                    </button>
                </div>
            </div>

            <!-- Autenticação de Dois Fatores -->
            <!-- <div class="p-6 border border-gray-200 rounded-lg dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary">Autenticação de Dois
                            Fatores</h4>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Adicione uma camada extra de segurança
                            à sua conta</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="twoFactorEnabled" class="sr-only peer">
                        <div
                            class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500">
                        </div>
                    </label>
                </div>
            </div> -->

            <!-- Sessões Ativas -->
            <!-- <div class="p-6 border border-gray-200 rounded-lg dark:border-gray-700">
                <h4 class="mb-4 text-lg font-semibold text-gray-800 dark:text-dark-text-primary">Sessões Ativas</h4>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 rounded-lg bg-gray-50 dark:bg-dark-accent">
                        <div class="flex items-center space-x-4">
                            <ComputerDesktopIcon class="w-6 h-6 text-gray-400" />
                            <div>
                                <p class="font-medium text-gray-800 dark:text-dark-text-primary">Chrome no Windows</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Maputo, Mozambique • Ativo agora</p>
                            </div>
                        </div>
                        <button
                            class="text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                            Terminar Sessão
                        </button>
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-lg bg-gray-50 dark:bg-dark-accent">
                        <div class="flex items-center space-x-4">
                            <DevicePhoneMobileIcon class="w-6 h-6 text-gray-400" />
                            <div>
                                <p class="font-medium text-gray-800 dark:text-dark-text-primary">Safari no iPhone</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Maputo, Mozambique • 2 horas atrás
                                </p>
                            </div>
                        </div>
                        <button
                            class="text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                            Terminar Sessão
                        </button>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import {
    EyeIcon,
    EyeSlashIcon,
    ComputerDesktopIcon,
    DevicePhoneMobileIcon
} from '@heroicons/vue/24/outline'

const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)
const twoFactorEnabled = ref(false)

const passwordForm = reactive({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
})

const passwordStrength = reactive({
    width: '0%',
    class: 'bg-gray-200',
    text: 'A password deve ter pelo menos 8 caracteres'
})

const checkPasswordStrength = () => {
    const password = passwordForm.new_password
    let strength = 0
    let text = ''
    let color = ''

    if (password.length === 0) {
        strength = 0
        text = 'A password deve ter pelo menos 8 caracteres'
        color = 'bg-gray-200'
    } else if (password.length < 4) {
        strength = 25
        text = 'Muito fraca'
        color = 'bg-red-500'
    } else if (password.length < 8) {
        strength = 50
        text = 'Fraca'
        color = 'bg-orange-500'
    } else if (password.length < 12) {
        strength = 75
        text = 'Boa'
        color = 'bg-yellow-500'
    } else {
        strength = 100
        text = 'Forte'
        color = 'bg-green-500'
    }

    passwordStrength.width = `${strength}%`
    passwordStrength.class = color
    passwordStrength.text = text
}
</script>
