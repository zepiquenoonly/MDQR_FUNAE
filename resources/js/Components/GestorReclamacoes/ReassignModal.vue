<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-lg max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-4">Reatribuir Técnico</h3>

            <div class="space-y-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Atribuir técnico para a reclamação #{{ complaint.id }}
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Selecionar Técnico
                    </label>
                    <select v-model="selectedTechnician"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200 dark:bg-dark-accent dark:text-dark-text-primary">
                        <option value="">Não atribuído</option>
                        <option v-for="technician in technicians" :key="technician.id" :value="technician.id">
                            {{ technician.name }} - {{ technician.specialization || 'Geral' }}
                        </option>
                    </select>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button @click="$emit('close')"
                        class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300 transition-colors duration-200">
                        Cancelar
                    </button>
                    <button @click="confirmReassign"
                        class="px-4 py-2 bg-orange-500 text-white rounded-lg font-medium hover:bg-orange-600 transition-all duration-200">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'


const props = defineProps({
  complaint: Object,
  technicians: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'update'])

const selectedTechnician = ref(props.complaint?.technician_id || '')

const confirmReassign = () => {
  emit('update', selectedTechnician.value)
}
</script>