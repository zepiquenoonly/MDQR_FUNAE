<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="border-b border-gray-200 p-6 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">Nova Sugestão</h2>
                <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>

            <!-- Form -->
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sua sugestão</label>
                    <textarea v-model="formData.suggestion" rows="8" placeholder="Descreva sua sugestão..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent resize-none">
                    </textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Projeto relacionado (opcional)</label>
                    <select v-model="formData.project" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Selecione um projeto</option>
                        <option value="parque-eolico">Parque Eólico de Pemba</option>
                        <option value="sistema-agua">Sistema de Água Potável</option>
                    </select>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-200 p-6 flex justify-end gap-4">
                <button @click="$emit('close')"
                    class="px-6 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">
                    Cancelar
                </button>
                <button @click="handleSubmit" class="px-6 py-2 bg-brand text-white rounded-md hover:bg-orange-600">
                    Enviar Sugestão
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const emit = defineEmits(['close', 'submit'])

const formData = ref({
    suggestion: '',
    project: ''
})

const handleSubmit = () => {
    if (!formData.value.suggestion.trim()) {
        console.warn('Por favor, descreva sua sugestão')
        return
    }

    emit('submit', formData.value)
}
</script>
