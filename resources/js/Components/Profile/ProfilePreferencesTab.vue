<template>
    <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
        <h3 class="text-2xl font-semibold text-gray-800 dark:text-dark-text-primary mb-6">Preferências do Sistema</h3>

        <div class="space-y-6">
            <!-- Idioma e Região -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-4">Idioma e Região</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Idioma</label>
                        <select v-model="preferences.language"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-0 focus:ring-orange-500 focus:border-brand transition-colors dark:bg-dark-accent dark:text-dark-text-primary">
                            <option value="pt">Português (PT)</option>
                            <option value="en">English (EN)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fuso
                            Horário</label>
                        <select v-model="preferences.timezone"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-0 focus:ring-orange-500 focus:border-brand transition-colors dark:bg-dark-accent dark:text-dark-text-primary">
                            <option value="Africa/Maputo">Africa/Maputo (UTC+2)</option>
                            <option value="UTC">UTC</option>
                            <option value="Europe/Lisbon">Europe/Lisbon (UTC+1)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Aparência -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-4">Aparência</h4>
                <div class="flex space-x-4">
                    <button @click="preferences.theme = 'light'" :class="[
                        'flex items-center space-x-2 px-4 py-2 border-2 rounded-lg font-medium transition-colors',
                        preferences.theme === 'light'
                            ? 'border-orange-500 text-orange-500 bg-orange-50 dark:bg-orange-900/20'
                            : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-400 dark:hover:border-gray-500'
                    ]">
                        <SunIcon class="w-4 h-4" />
                        <span>Claro</span>
                    </button>

                    <button @click="preferences.theme = 'dark'" :class="[
                        'flex items-center space-x-2 px-4 py-2 border-2 rounded-lg font-medium transition-colors',
                        preferences.theme === 'dark'
                            ? 'border-orange-500 text-orange-500 bg-orange-50 dark:bg-orange-900/20'
                            : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-400 dark:hover:border-gray-500'
                    ]">
                        <MoonIcon class="w-4 h-4" />
                        <span>Escuro</span>
                    </button>

                    <button @click="preferences.theme = 'auto'" :class="[
                        'flex items-center space-x-2 px-4 py-2 border-2 rounded-lg font-medium transition-colors',
                        preferences.theme === 'auto'
                            ? 'border-orange-500 text-orange-500 bg-orange-50 dark:bg-orange-900/20'
                            : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-400 dark:hover:border-gray-500'
                    ]">
                        <ComputerDesktopIcon class="w-4 h-4" />
                        <span>Automático</span>
                    </button>
                </div>
            </div>

            <!-- Zona Perigosa -->
            <div class="border border-red-200 dark:border-red-800 rounded-lg p-6 bg-red-50 dark:bg-red-900/20">
                <h4 class="text-lg font-semibold text-red-800 dark:text-red-300 mb-4">Zona Perigosa</h4>
                <div class="space-y-4">
                    <p class="text-red-700 dark:text-red-300">Uma vez que apague a sua conta, não há retorno. Por favor,
                        tenha certeza.
                    </p>
                    <button @click="confirmAccountDeletion"
                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        Apagar Conta
                    </button>
                </div>
            </div>

            <!-- Botão de Salvar -->
            <div class="flex justify-end">
                <button @click="savePreferences"
                    class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    Guardar Preferências
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import {
    SunIcon,
    MoonIcon,
    ComputerDesktopIcon
} from '@heroicons/vue/24/outline'

// Dados de exemplo para preferências
const preferences = ref({
    language: 'pt',
    timezone: 'Africa/Maputo',
    theme: 'light'
})

const savePreferences = () => {
    // Aqui você pode implementar a lógica para salvar as preferências
    console.log('Preferências guardadas!')
    // Em produção, você faria uma chamada API para salvar estas configurações
}

const confirmAccountDeletion = () => {
    console.log('Confirmando exclusão da conta')
    router.delete($route('profile.destroy'))
}
</script>
