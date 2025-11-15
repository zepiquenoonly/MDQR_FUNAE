<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded shadow-lg w-11/12 md:w-[1100px] h-4/5 md:h-[600px] flex flex-col">
            <!-- Header -->
            <div class="border-b border-gray-200 p-6 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">Descrição da Queixa</h2>
                <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>


            <!-- Form -->
            <div class="p-6 space-y-6">
                <!-- Prefere anonimato -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Prefere anonimato?</label>
                    <div class="flex gap-6">
                        <label class="flex items-center">
                            <input type="radio" name="anonimato" value="nao" class="mr-2" v-model="formData.anonymous">
                            <span class="text-gray-700">Não</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="anonimato" value="sim" class="mr-2" v-model="formData.anonymous">
                            <span class="text-gray-700">Sim</span>
                        </label>
                    </div>
                </div>

                <!-- Descrição da queixa -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descrição da queixa</label>
                    <textarea rows="6" placeholder="Digite aqui sua queixa..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent resize-none md:h-[300px]"
                        v-model="formData.description"></textarea>
                </div>
            </div>

            <!-- Footer -->
            <div class=" p-6 flex justify-between -mt-8">
                <button @click="$emit('close')"
                    class="px-6 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                    Fechar
                </button>
                <button @click="handleSubmit"
                    class="px-6 py-2 bg-brand text-white rounded-md hover:bg-green-600 transition-colors">
                    Finalizar registo
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const emit = defineEmits(['close', 'submit'])

const formData = ref({
    anonymous: 'nao',
    description: ''
})

const handleSubmit = () => {
    emit('submit', formData.value)
}
</script>