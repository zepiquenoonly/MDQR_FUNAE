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
        <PaperClipIcon class="h-4 w-4 text-green-600" />
      </span>
      Anexos ({{ complaint.attachments?.length || 0 }})
    </h2>
    <div
      v-if="complaint.attachments?.length === 0"
      class="text-center py-8 text-gray-500 dark:text-gray-400"
    >
      <p class="text-sm">Sem anexos no momento</p>
    </div>
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <a
        v-for="attach in complaint.attachments"
        :key="attach.id"
        :href="attach.url"
        target="_blank"
        class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-dark-accent border border-gray-200 dark:border-gray-600 hover:border-brand dark:hover:border-orange-500 transition-all group"
      >
        <DocumentTextIcon class="h-8 w-8 text-gray-400" />
        <div class="flex-1 min-w-0">
          <p
            class="text-sm font-medium text-gray-900 dark:text-dark-text-primary truncate group-hover:text-brand"
          >
            {{ attach.name }}
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ attach.size || "N/A" }}
          </p>
        </div>
        <ArrowDownTrayIcon class="h-5 w-5 group-hover:text-brand" />
      </a>
    </div>
  </div>
</template>

<script setup>
import {
  PaperClipIcon,
  DocumentTextIcon,
  ArrowDownTrayIcon,
} from "@heroicons/vue/24/outline";

defineProps({
  complaint: {
    type: Object,
    required: true,
  },
});
</script>
