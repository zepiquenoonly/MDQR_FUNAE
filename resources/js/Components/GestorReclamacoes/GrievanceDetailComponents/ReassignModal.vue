<template>
  <!-- Root -->
  <div class="fixed inset-0 z-50">
    <!-- Overlay -->
    <div class="fixed inset-0 bg-black/70 z-40" @click.self="$emit('close')"></div>

    <!-- Wrapper do modal -->
    <div
      class="fixed inset-0 z-50 flex items-center justify-center p-6 pointer-events-none"
    >
      <!-- Modal -->
      <div
        class="pointer-events-auto bg-white dark:bg-dark-secondary rounded-2xl w-full max-w-xl max-h-[95vh] flex flex-col shadow-2xl"
      >
        <!-- Header -->
        <div class="p-8 flex-shrink-0 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-xl font-bold text-gray-900 dark:text-dark-text-primary">
                Reatribuir Técnico
              </h2>
              <p class="text-base text-gray-700 dark:text-gray-300 mt-2">
                Alterar técnico responsável pela reclamação
                <span class="font-bold">#{{ complaint.reference_number }}</span>
              </p>
            </div>
            <button
              @click="$emit('close')"
              class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full p-2 transition-all duration-200"
            >
              <XMarkIcon class="h-6 w-6" />
            </button>
          </div>
        </div>

        <!-- Form -->
        <div class="p-8 flex-1 overflow-y-auto">
          <div class="space-y-6">
            <!-- Selecione o técnico -->
            <div>
              <h3
                class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4"
              >
                Selecione o técnico responsável
              </h3>

              <div class="relative">
                <select
                  v-model="selectedTechnician"
                  :disabled="isLoading"
                  class="w-full px-4 py-3 text-base border-2 border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-600 dark:focus:border-blue-600 bg-white dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary transition-all duration-300 appearance-none cursor-pointer hover:border-gray-400 dark:hover:border-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <option value="" disabled selected class="text-gray-500 py-3">
                    Selecione um técnico
                  </option>
                  <option value="">Não atribuído</option>

                  <optgroup
                    v-for="specialty in groupedTechnicians"
                    :key="specialty.name"
                    :label="specialty.name"
                    class="text-base font-semibold text-gray-900 dark:text-gray-300"
                  >
                    <option
                      v-for="technician in specialty.technicians"
                      :key="technician.id"
                      :value="technician.id"
                      class="py-3 text-base hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                      {{ technician.name }}
                    </option>
                  </optgroup>
                </select>

                <div
                  class="absolute right-4 top-1/2 transform -translate-y-1/2 pointer-events-none"
                >
                  <ChevronDownIcon class="h-5 w-5 text-gray-500" />
                </div>
              </div>

              <!-- Técnico selecionado -->
              <div
                v-if="selectedTechnician && selectedTechnician !== ''"
                class="mt-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-200 dark:border-blue-800"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center"
                  >
                    <UserIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                  </div>
                  <p class="font-medium text-gray-900 dark:text-white">
                    {{ getSelectedTechnician()?.name || "Nenhum Técnico Selecionado" }}
                  </p>
                </div>
              </div>

              <!-- Não atribuído -->
              <div
                v-if="selectedTechnician === ''"
                class="mt-4 p-4 bg-gradient-to-r from-gray-50 to-slate-50 dark:from-gray-800/20 dark:to-slate-800/20 rounded-xl border border-gray-200 dark:border-gray-700"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-900/30 flex items-center justify-center"
                  >
                    <UserGroupIcon class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                  </div>
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">Não atribuído</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                      Este caso ficará disponível para todos os técnicos da equipa
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Informações da decisão -->
            <div
              class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700"
            >
              <h4 class="font-medium text-gray-900 dark:text-white mb-2">
                Consequências da reatribuição:
              </h4>
              <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-2">
                <li class="flex items-start gap-2">
                  <ArrowRightCircleIcon
                    class="h-4 w-4 text-blue-500 mt-0.5 flex-shrink-0"
                  />
                  <div>
                    <span class="font-medium">
                      O técnico actual será notificado da alteração
                    </span>
                    <p class="text-xs text-gray-500 mt-0.5">
                      Receberá um email com a informação
                    </p>
                  </div>
                </li>

                <li class="flex items-start gap-2">
                  <BellIcon class="h-4 w-4 text-orange-500 mt-0.5 flex-shrink-0" />
                  <div>
                    <span class="font-medium">
                      O novo técnico será notificado da atribuição
                    </span>
                    <p class="text-xs text-gray-500 mt-0.5">
                      Receberá todas as informações do caso
                    </p>
                  </div>
                </li>

                <li class="flex items-start gap-2">
                  <DocumentTextIcon class="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
                  <div>
                    <span class="font-medium"> O histórico do caso será atualizado </span>
                    <p class="text-xs text-gray-500 mt-0.5">
                      A alteração ficará registrada permanentemente
                    </p>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Footer / Ações -->
        <div class="p-8 border-t border-gray-200 dark:border-gray-700">
          <div class="flex gap-4">
            <button
              @click="$emit('close')"
              :disabled="isLoading"
              class="flex-1 px-6 py-3 border border-gray-300 dark:border-gray-700 text-gray-800 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Cancelar
            </button>

            <button
              @click="confirmReassign"
              :disabled="isLoading || selectedTechnician === undefined"
              :class="[
                'flex-1 px-6 py-3 rounded-lg font-medium transition-colors flex items-center justify-center gap-2',
                isLoading || selectedTechnician === undefined
                  ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                  : 'bg-blue-600 text-white hover:bg-blue-700',
              ]"
            >
              <template v-if="isLoading">
                <div
                  class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"
                ></div>
                <span>A processar...</span>
              </template>
              <template v-else>
                <CheckIcon class="h-5 w-5" />
                <span>Confirmar</span>
              </template>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import {
  XMarkIcon,
  CheckIcon,
  ChevronDownIcon,
  UserIcon,
  UserGroupIcon,
  ArrowRightCircleIcon,
  BellIcon,
  DocumentTextIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  complaint: Object,
  technicians: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["close", "update"]);

const selectedTechnician = ref(
  props.complaint?.technician?.id || props.complaint?.assigned_to || ""
);
const isLoading = ref(false);

// Agrupar técnicos por especialização
const groupedTechnicians = computed(() => {
  const groups = {};

  props.technicians.forEach((tech) => {
    const specialty = tech.specialization || "Geral";
    if (!groups[specialty]) {
      groups[specialty] = [];
    }
    groups[specialty].push(tech);
  });

  return Object.keys(groups).map((specialty) => ({
    name: specialty,
    technicians: groups[specialty],
  }));
});

const getSelectedTechnician = () => {
  return props.technicians.find((t) => t.id === selectedTechnician.value);
};

watch(
  () => props.complaint,
  (newComplaint) => {
    selectedTechnician.value =
      newComplaint?.technician?.id || newComplaint?.assigned_to || "";
  }
);

const confirmReassign = async () => {
  if (selectedTechnician.value === undefined) return;

  isLoading.value = true;

  try {
    // Emitir o valor selecionado (pode ser "" para "Não atribuído" ou um ID de técnico)
    emit("update", selectedTechnician.value);
  } catch (error) {
    console.error("Erro ao reatribuir:", error);
    isLoading.value = false;
  }
};
</script>
