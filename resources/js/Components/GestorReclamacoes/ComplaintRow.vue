<template>
  <Link
    :href="detailUrl"
    :class="[
      'bg-white dark:bg-dark-secondary border rounded-xl p-4 transition-all duration-200 cursor-pointer complaint-row block',
      selected
        ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20 shadow-md'
        : 'border-gray-200 dark:border-gray-700 hover:border-orange-300 dark:hover:border-orange-500 hover:shadow-sm',
    ]"
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
          <h4 class="...">
            {{ complaint.title || complaint.description || `Submissão #${complaint.id}` }}
          </h4>
          <div class="flex items-center gap-2 flex-wrap">
            <!-- Badge para intervenção do director - MELHORADO -->
            <span
              v-if="hasDirectorIntervention"
              class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-300"
              :title="getDirectorInterventionTooltip"
            >
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path
                  fill-rule="evenodd"
                  d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                  clip-rule="evenodd"
                />
              </svg>
              {{ getDirectorInterventionBadgeText }}
            </span>

            <PriorityBadge :priority="complaint.priority" />
            <StatusBadge :status="complaint.status" />
          </div>
        </div>

        <!-- User Info -->
        <div class="mb-2">
          <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">
            {{ complaint.user?.name || complaint.contact_name || "Utente" }}
          </span>
        </div>

        <!-- Description -->
        <p
          class="text-gray-600 dark:text-gray-400 text-sm mb-3 line-clamp-2 leading-relaxed"
        >
          {{ complaint.description || "Sem descrição fornecida" }}
        </p>

        <!-- Metadata -->
        <div
          class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400"
        >
          <span
            class="flex items-center space-x-1 bg-gray-50 dark:bg-dark-accent px-2 py-1 rounded"
          >
            <HashtagIcon class="w-3 h-3 flex-shrink-0" />
            <span class="truncate">{{ complaint.reference_number || complaint.id }}</span>
          </span>
          <span
            class="flex items-center space-x-1 bg-gray-50 dark:bg-dark-accent px-2 py-1 rounded capitalize"
          >
            <TagIcon class="w-3 h-3 flex-shrink-0" />
            <span class="truncate">{{
              complaint.category || complaint.department || "N/A"
            }}</span>
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
              complaint.technician?.name || complaint.assigned_to?.name || "Não atribuído"
            }}</span>
          </div>
        </div>
      </div>
    </div>
  </Link>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import {
  HashtagIcon,
  TagIcon,
  DocumentTextIcon,
  CalendarIcon,
  UserIcon,
} from "@heroicons/vue/24/outline";
import PriorityBadge from "./PriorityBadge.vue";
import StatusBadge from "./StatusBadge.vue";
import { computed } from "vue";

const props = defineProps({
  complaint: {
    type: Object,
    required: true,
  },
  selected: Boolean,
  role: {
    type: String,
    default: "manager", // 'director' ou 'manager'
  },
});

// URL de detalhes - usando a mesma abordagem do seu código funcional
const detailUrl = computed(() => {
  // Obter o ID da reclamação
  const complaintId = props.complaint.reference_number || props.complaint.id;

  if (!complaintId) {
    console.error("Não foi possível encontrar o ID da reclamação:", props.complaint);
    return "#";
  }

  // Determinar a rota baseada no role - SIMPLIFICADO
  if (props.role?.toLowerCase() === "director") {
    // Rota para Director
    //return `/director/complaints-overview/${complaintId}`;
  } else {
    const url = `/complaints/grievance/${complaintId}`;
    console.log("  - Manager URL:", url);
    return url;
  }
});

const hasDirectorIntervention = computed(() => {
  const complaint = props.complaint;

  // Verificar múltiplas fontes de dados
  return (
    complaint.has_director_intervention === true ||
    complaint.director_comments_count > 0 ||
    complaint.director_updates?.length > 0 ||
    complaint.director_validation ||
    (complaint.metadata && complaint.metadata.director_validation) ||
    complaint.escalated === true
  );
});

const getDirectorInterventionBadgeText = computed(() => {
  const complaint = props.complaint;

  if (complaint.director_validation) {
    const status = complaint.director_validation.status;
    return status === "approved"
      ? "Aprovado"
      : status === "rejected"
      ? "Rejeitado"
      : status === "needs_revision"
      ? "Revisão"
      : "Validado";
  }

  return complaint.director_comments_count > 0
    ? `${complaint.director_comments_count} intervenções`
    : "Director";
});

const getDirectorInterventionTooltip = computed(() => {
  const complaint = props.complaint;
  let tooltip = "Intervenção do Director\n";

  if (complaint.director_validation) {
    const val = complaint.director_validation;
    tooltip += `Status: ${val.status}\n`;
    if (val.validated_by_name) tooltip += `Por: ${val.validated_by_name}\n`;
    if (val.validated_at)
      tooltip += `Em: ${new Date(val.validated_at).toLocaleDateString("pt-PT")}\n`;
  }

  if (complaint.director_comments_count > 0) {
    tooltip += `Comentários: ${complaint.director_comments_count}\n`;
  }

  if (complaint.escalated) {
    tooltip += "Caso escalado\n";
  }

  return tooltip.trim();
});

const getUserInitials = (user) => {
  if (!user) return "U";
  return user
    .split(" ")
    .map((n) => n[0])
    .join("")
    .toUpperCase()
    .substring(0, 2);
};

const getTypeText = (type) => {
  const types = {
    suggestion: "Sugestão",
    sugestão: "Sugestão",
    sugestao: "Sugestão",
    grievance: "Queixa",
    queixa: "Queixa",
    complaint: "Reclamação",
    reclamação: "Reclamação",
    reclamacao: "Reclamação",
  };

  if (!type) {
    return "Tipo não definido";
  }

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
</script>

<style scoped>
.complaint-row {
  min-height: 120px;
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
