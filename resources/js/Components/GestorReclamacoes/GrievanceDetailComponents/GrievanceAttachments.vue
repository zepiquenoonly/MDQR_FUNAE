<template>
  <div
    class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
  >
    <AttachmentsGallery
      :attachments="complaint.attachments"
      :title="`Anexos (${complaint.attachments?.length || 0})`"
      empty-message="Sem anexos no momento"
    />
  </div>
</template>

<script setup>
import AttachmentsGallery from '@/Components/Grievance/AttachmentsGallery.vue';

defineProps({
  complaint: {
    type: Object,
    required: true,
  },
});
</script>
