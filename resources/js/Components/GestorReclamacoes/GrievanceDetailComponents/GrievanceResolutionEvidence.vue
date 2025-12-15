<template>
  <div
    class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
  >
    <h2
      class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2"
    >
      <span
        class="flex items-center justify-center h-5 w-5 rounded-full bg-green-100 dark:bg-green-900/20 text-xs flex-shrink-0"
      >
        <CheckCircleIcon class="h-4 w-4 text-green-600" />
      </span>
      Evidências de Conclusão
    </h2>

    <div v-if="complaint.resolution_notes" class="mb-4">
      <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-2">
        Notas de Resolução:
      </p>
      <div
        class="bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800 rounded-lg p-4"
      >
        <p class="text-sm text-gray-700 dark:text-gray-300">
          {{ complaint.resolution_notes }}
        </p>
      </div>
    </div>

    <!-- Evidências visuais/arquivos -->
    <div v-if="complaint.resolution_attachments?.length > 0" class="mt-4">
      <AttachmentsGallery
        :attachments="complaint.resolution_attachments"
        title="Arquivos de Evidência"
        empty-message="Sem evidências de conclusão disponíveis"
      />
    </div>

    <div
      v-if="
        !complaint.resolution_notes &&
        (!complaint.resolution_attachments ||
          complaint.resolution_attachments.length === 0)
      "
      class="text-center py-6 text-gray-500 dark:text-gray-400"
    >
      <p class="text-sm">Sem evidências de conclusão disponíveis</p>
    </div>
  </div>
</template>

<script setup>
import {
  CheckCircleIcon,
} from "@heroicons/vue/24/outline";
import AttachmentsGallery from '@/Components/Grievance/AttachmentsGallery.vue';

const props = defineProps({
  complaint: {
    type: Object,
    required: true,
  },
});
</script>
