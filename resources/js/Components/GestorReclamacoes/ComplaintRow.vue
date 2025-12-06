<template>
  <div
    :class="[
      'bg-white dark:bg-dark-secondary border rounded-xl p-4 transition-all duration-200 cursor-pointer complaint-row',
      selected
        ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20 shadow-md'
        : 'border-gray-200 dark:border-gray-700 hover:border-orange-300 dark:hover:border-orange-500 hover:shadow-sm',
    ]"
    @click="handleRowClick"
  >
    <div class="flex items-start gap-4">
      <!-- User Avatar -->
      <div
        class="w-10 h-10 bg-brand rounded-lg flex items-center justify-center text-white font-semibold flex-shrink-0 text-sm"
      >
        {{ getUserInitials(complaint.user?.name || "Utente") }}
      </div>

      <!-- Complaint Content -->
      <div class="flex-1 min-w-0 overflow-hidden">
        <!-- Header -->
        <div class="flex items-center flex-wrap gap-2 mb-2">
          <h4
            class="font-semibold text-gray-800 dark:text-dark-text-primary text-sm truncate flex-1 min-w-0"
          >
            {{ complaint.title }}
          </h4>
          <div class="flex items-center gap-2 flex-wrap">
            <PriorityBadge :priority="complaint.priority" />
            <StatusBadge :status="complaint.status" />
          </div>
        </div>

        <!-- User Info -->
        <div class="mb-2">
          <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">
            {{ complaint.user?.name || "Utente" }}
          </span>
        </div>

        <!-- Description -->
        <p
          class="text-gray-600 dark:text-gray-400 text-sm mb-3 line-clamp-2 leading-relaxed"
        >
          {{ complaint.description }}
        </p>

        <!-- Metadata -->
        <div
          class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400"
        >
          <span
            class="flex items-center space-x-1 bg-gray-50 dark:bg-dark-accent px-2 py-1 rounded"
          >
            <HashtagIcon class="w-3 h-3 flex-shrink-0" />
            <span class="truncate">{{ complaint.id }}</span>
          </span>
          <span
            class="flex items-center space-x-1 bg-gray-50 dark:bg-dark-accent px-2 py-1 rounded capitalize"
          >
            <TagIcon class="w-3 h-3 flex-shrink-0" />
            <span class="truncate">{{ complaint.category }}</span>
          </span>
          <span
            class="flex items-center space-x-1 bg-gray-50 dark:bg-dark-accent px-2 py-1 rounded"
          >
            <DocumentTextIcon class="w-3 h-3 flex-shrink-0" />
            <span class="truncate">{{ getTypeText(complaint.type) }}</span>
          </span>
          <span
            class="flex items-center space-x-1 bg-gray-50 dark:bg-dark-accent px-2 py-1 rounded"
          >
            <CalendarIcon class="w-3 h-3 flex-shrink-0" />
            <span class="truncate">{{ formatDate(complaint.created_at) }}</span>
          </span>
        </div>

        <!-- Technician -->
        <div class="flex items-center justify-between gap-2 mt-3">
          <div
            class="flex items-center space-x-1 text-xs text-gray-500 dark:text-gray-400"
          >
            <UserIcon class="w-3 h-3 flex-shrink-0" />
            <span class="truncate">{{
              complaint.technician?.name || "Não atribuído"
            }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from "@inertiajs/vue3";
import {
  HashtagIcon,
  TagIcon,
  DocumentTextIcon,
  CalendarIcon,
  UserIcon,
} from "@heroicons/vue/24/outline";
import PriorityBadge from "./PriorityBadge.vue";
import StatusBadge from "./StatusBadge.vue";

const props = defineProps({
  complaint: {
    type: Object,
    required: true,
  },
  selected: Boolean,
});

const emit = defineEmits(["select"]);

const getUserInitials = (user) => {
  if (!user) return "U";
  return user
    .split(" ")
    .map((n) => n[0])
    .join("")
    .toUpperCase()
    .substring(0, 2);
};

// Função CORRIGIDA para mapear tipos
const getTypeText = (type) => {
  const types = {
    // Sugestões
    suggestion: "Sugestão",
    sugestão: "Sugestão",
    sugestao: "Sugestão",

    // Queixas
    grievance: "Queixa",
    queixa: "Queixa",

    // Reclamações
    complaint: "Reclamação",
    reclamação: "Reclamação",
    reclamacao: "Reclamação",
  };

  if (!type) {
    return "Tipo não definido";
  }

  // Converte para minúsculas e remove acentos
  const normalizedType = type
    .toString()
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "");
  return types[normalizedType] || type;
};

const formatDate = (dateString) => {
  if (!dateString) return "Data não definida";
  return new Date(dateString).toLocaleDateString("pt-BR");
};

const handleRowClick = () => {
  emit("select", props.complaint);
  router.get(route("complaints.grievance.show", { grievance: props.complaint.id }));
};
</script>

<style scoped>
.complaint-row {
  min-height: 120px;
}

.complaint-row {
  transform: translateZ(0);
  backface-visibility: hidden;
  perspective: 1000;
}

@media (max-width: 380px) {
  .complaint-row {
    min-height: 140px;
  }
}

@media (min-width: 768px) {
  .complaint-row {
    min-height: 130px;
  }
}

@media (min-width: 1024px) {
  .complaint-row {
    min-height: 120px;
  }
}
</style>
