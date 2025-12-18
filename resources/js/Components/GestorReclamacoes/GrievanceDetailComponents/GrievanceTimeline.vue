<!-- GrievanceTimeline.vue -->
<template>
  <div class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6">
    <h2
      class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2"
    >
      <span
        class="flex items-center justify-center h-5 w-5 rounded-full bg-blue-100 dark:bg-blue-900/20 text-xs flex-shrink-0"
      >
        <ClockIcon class="h-4 w-4 text-blue-600" />
      </span>
      Estado da Submissão
    </h2>

    <div
      v-if="complaint.activities?.length === 0"
      class="text-center py-8 text-gray-500 dark:text-gray-400"
    >
      <p class="text-sm">Nenhum estado registado ainda</p>
    </div>

    <div v-else class="space-y-4 max-h-96 overflow-y-auto pr-2">
      <!-- Timeline com estados organizados -->
      <div class="relative">
        <!-- Linha vertical da timeline - pode ter quebras para mostrar saltos -->
        <div
          class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"
          :class="{ 'dashed-line': complaint.status === 'rejected' }"
        ></div>

        <!-- Itens da timeline -->
        <div
          v-for="(state, index) in getStatusTimeline()"
          :key="state.status || state.type + index"
          class="relative flex gap-4 pb-6 last:pb-0"
        >
          <!-- Ponto da timeline -->
          <div
            :class="[
              'flex-shrink-0 w-8 h-8 rounded-full border-2 flex items-center justify-center z-10',
              state.isActive
                ? getTimelineDotClass(state.status)
                : state.type === 'rejection'
                ? 'bg-gray-200 border-gray-200 dark:bg-gray-700 dark:border-gray-700'
                : 'bg-gray-300 border-gray-300 dark:bg-gray-600 dark:border-gray-600',
            ]"
          >
            <CheckIcon
              v-if="
                state.isActive &&
                (state.status === 'closed' || state.status === 'resolved')
              "
              class="h-3 w-3 text-white"
            />
            <XMarkIcon
              v-else-if="state.isActive && state.status === 'rejected'"
              class="h-3 w-3 text-white"
            />
            <ClockIcon
              v-else-if="
                state.isActive &&
                (state.status === 'in_progress' || state.status === 'under_review')
              "
              class="h-3 w-3 text-white"
            />
            <ExclamationTriangleIcon
              v-else-if="state.isActive && state.status === 'rejected'"
              class="h-3 w-3 text-white"
            />
            <DocumentTextIcon v-else-if="state.isActive" class="h-3 w-3 text-white" />
            <div v-else class="w-2 h-2 bg-white rounded-full"></div>
          </div>

          <!-- Conteúdo do item -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <p
                :class="[
                  'font-semibold text-sm',
                  state.isActive
                    ? state.status === 'rejected'
                      ? 'text-red-600 dark:text-red-400'
                      : 'text-gray-900 dark:text-dark-text-primary'
                    : 'text-gray-400 dark:text-gray-500',
                ]"
              >
                {{ state.label }}
              </p>

              <!-- Badges para indicar estado especial -->
              <span
                v-if="state.isCurrent && state.status === 'rejected'"
                class="px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300 rounded-full"
              >
                Rejeitada
              </span>
              <span
                v-else-if="state.isCurrent"
                class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300 rounded-full"
              >
                Estado Actual
              </span>
              <span
                v-else-if="state.isActive && state.status === 'rejected'"
                class="px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300 rounded-full"
              >
                Rejeitada
              </span>
              <span
                v-else-if="state.isActive"
                class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300 rounded-full"
              >
                Concluído
              </span>
              <span
                v-else-if="state.type === 'rejection' && state.canSkipTo"
                class="px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300 rounded-full"
              >
                Pode saltar para aqui
              </span>
            </div>

            <p v-if="state.date" class="text-xs text-gray-600 dark:text-gray-400 mb-1">
              {{ state.date }}
            </p>

            <p
              v-if="state.description"
              class="text-sm text-gray-700 dark:text-gray-300 mt-1 bg-gray-50 dark:bg-dark-accent rounded-lg p-2"
              :class="{
                'border-l-4 border-red-500 dark:border-red-400':
                  state.status === 'rejected',
                'border-l-4 border-blue-500 dark:border-blue-400':
                  state.status !== 'rejected',
              }"
            >
              {{ state.description }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  ClockIcon,
  CheckIcon,
  ExclamationTriangleIcon,
  DocumentTextIcon,
  XMarkIcon,
  ArrowPathIcon,
} from "@heroicons/vue/24/outline";
import { useGrievanceUtils } from "@/Components/GestorReclamacoes/Composables/useGrievanceUtils";

const props = defineProps({
  complaint: {
    type: Object,
    required: true,
  },
});

const { formatDate, getStatusTimeline, getTimelineDotClass } = useGrievanceUtils(
  props.complaint
);
</script>

<style scoped>
.dashed-line {
  background-image: linear-gradient(to bottom, #9ca3af 50%, transparent 50%);
  background-size: 1px 10px;
  background-repeat: repeat-y;
}

.dark .dashed-line {
  background-image: linear-gradient(to bottom, #4b5563 50%, transparent 50%);
}
</style>
