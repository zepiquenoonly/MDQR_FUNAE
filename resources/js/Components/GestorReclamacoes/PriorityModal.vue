<template>
  <div
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
  >
    <div class="bg-white dark:bg-dark-secondary rounded shadow-lg max-w-md w-full p-6">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-4">
        Definir Prioridade
      </h3>

      <div class="space-y-4">
        <div class="text-sm text-gray-600 dark:text-gray-400">
          Alterar prioridade para a reclamação {{ complaint.reference_number }}
        </div>

        <div class="space-y-2">
          <button
            v-for="priority in priorities"
            :key="priority.value"
            @click="selectPriority(priority.value)"
            :class="[
              'w-full flex items-center justify-between p-3 rounded-lg border transition-all duration-200',
              selectedPriority === priority.value
                ? priority.activeClass
                : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500',
            ]"
          >
            <div class="flex items-center space-x-3">
              <div :class="priority.iconClass" class="w-3 h-3 rounded-full"></div>
              <span
                :class="[
                  'font-medium',
                  selectedPriority === priority.value
                    ? 'text-gray-500 dark:text-gray-400'
                    : 'text-gray-900 dark:text-dark-text-primary',
                ]"
              >
                {{ priority.label }}
              </span>
            </div>
            <CheckIcon
              v-if="selectedPriority === priority.value"
              class="w-5 h-5 text-green-500"
            />
          </button>
        </div>

        <div class="flex justify-end space-x-3 pt-4">
          <button
            @click="$emit('close')"
            class="bg-gray-500 rounded px-4 py-2 text-white dark:text-white hover:bg-gray-600 dark:hover:text-gray-300 transition-colors duration-200"
          >
            Cancelar
          </button>
          <button
            @click="confirmPriority"
            :disabled="!selectedPriority"
            :class="[
              'px-4 py-2 bg-orange-500 text-white rounded font-medium transition-all duration-200',
              selectedPriority ? 'hover:bg-orange-600' : 'opacity-50 cursor-not-allowed',
            ]"
          >
            Confirmar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { CheckIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
  complaint: Object,
});

const emit = defineEmits(["close", "update"]);

const selectedPriority = ref(props.complaint?.priority || "");

const priorities = [
  {
    value: "high",
    label: "Alta Prioridade",
    iconClass: "bg-red-500",
    activeClass: "border-red-500 bg-red-50",
  },
  {
    value: "medium",
    label: "Média Prioridade",
    iconClass: "bg-yellow-500",
    activeClass: "border-yellow-500 bg-yellow-50",
  },
  {
    value: "low",
    label: "Baixa Prioridade",
    iconClass: "bg-green-500",
    activeClass: "border-green-500 bg-green-50",
  },
];

const selectPriority = (priority) => {
  selectedPriority.value = priority;
};

const confirmPriority = () => {
  if (selectedPriority.value) {
    emit("update", selectedPriority.value);
  }
};
</script>
