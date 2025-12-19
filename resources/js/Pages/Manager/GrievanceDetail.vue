<template>
  <Layout :role="'manager'">
    <div class="space-y-4 sm:space-y-6">
      <!-- Breadcrumb & Header -->
      <div class="flex flex-col gap-3 sm:gap-4">
        <Link
          href="/gestor/dashboard"
          class="text-sm text-brand hover:text-orange-700 font-medium flex items-center gap-1"
        >
          ← Voltar ao Painel
        </Link>e
        <div
          class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
        >
          <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3"
          >
            <div class="flex-1">
              <h1
                class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-dark-text-primary"
              >
                {{ complaint.reference_number }}
              </h1>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ complaint.title }}
              </p>
            </div>
            <StatusBadge
              :status="complaint.status"
              :label="complaint.status_label"
              size="lg"
            />
          </div>
          <div class="flex flex-wrap gap-2">
            <span
              :class="priorityBadgeClass(complaint.priority)"
              class="rounded-full px-3 py-1 text-sm font-semibold"
            >
              {{ priorityLabel(complaint.priority) }}
            </span>
            <span
              class="rounded-full bg-blue-100 dark:bg-blue-900/20 px-3 py-1 text-sm text-blue-700 dark:text-blue-300 font-medium"
            >
              {{ complaint.category }}
            </span>
            <span
              v-if="complaint.district"
              class="rounded-full bg-gray-100 dark:bg-gray-700 px-3 py-1 text-sm text-gray-700 dark:text-gray-300 inline-flex items-center gap-2"
            >
              <MapPinIcon class="h-4 w-4 text-gray-600" />
              {{ complaint.district }}
            </span>
          </div>
        </div>
      </div>

      <!-- Main Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Left Column - 2/3 -->
        <div class="lg:col-span-2 space-y-4 sm:space-y-6">
          <!-- Description Card -->
          <div
            class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
          >
            <h2
              class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2"
            >
              <span
                class="flex items-center justify-center h-5 w-5 rounded-full bg-purple-100 dark:bg-purple-900/20 text-xs"
              >
                <DocumentTextIcon class="h-4 w-4 text-purple-600" />
              </span>
              Descrição da Reclamação
            </h2>
            <div
              class="prose prose-sm dark:prose-invert max-w-none bg-gray-50 dark:bg-dark-accent rounded-lg p-4"
              v-html="complaint.description"
            />
            <div
              class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"
            >
              <div class="text-sm">
                <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">
                  Submetida
                </p>
                <p class="text-gray-900 dark:text-dark-text-primary font-semibold">
                  {{ formatDate(complaint.submitted_at) }}
                </p>
              </div>
              <div class="text-sm">
                <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">
                  Atualizada
                </p>
                <p class="text-gray-900 dark:text-dark-text-primary font-semibold">
                  {{ formatRelative(complaint.updated_at) }}
                </p>
              </div>
              <div class="text-sm">
                <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">
                  Dias Aberto
                </p>
                <p class="text-gray-900 dark:text-dark-text-primary font-semibold">
                  {{ getDaysOpen() }}
                </p>
              </div>
            </div>
          </div>
          <GrievanceTimeline :complaint="complaint" />
          <GrievanceAttachments :complaint="complaint" />

          <GrievanceResolutionEvidence
            v-if="shouldShowResolution"
            :complaint="complaint"
          />
        </div>

        <!-- Right Column - 1/3 -->
        <div class="space-y-4 sm:space-y-6">
          <GrievanceQuickStatus :complaint="complaint" />
          <GrievanceUserInfo :complaint="complaint" />
          <GrievanceActions
            :complaint="complaint"
            :technicians="technicians"
            :loading="loading"
            :can-reassign-technician="canReassignTechnician"
            :can-update-priority="canUpdatePriority"
            :can-send-to-director="canSendToDirector"
            :can-mark-complete="canMarkComplete"
            @open-modal="handleOpenModal"
            @mark-complete="markComplete"
            @reject-completion="rejectCompletion"
            @reject-submission="rejectSubmission"
            @refresh="refreshComplaintData"
          />
        </div>
      </div>
    </div>
    <div class="mt-8">
      <GrievanceDirectorComments
        v-if="complaint.director_comments?.length > 0"
        :complaint="complaint"
      />
    </div>
    <!--Toast Notification -->
    <ToastNotification v-if="toast.show" :toast="toast" @close="toast.show = false" />

    <!-- Modals -->
    <PriorityModal
      v-if="showPriorityModal"
      :complaint="complaint"
      @close="closeModal('priority')"
      @update="updatePriority"
    />

    <ReassignModal
      v-if="showReassignModal"
      :complaint="complaint"
      :technicians="technicians"
      @close="closeModal('reassign')"
      @update="reassignTechnician"
    />

    <CommentModal
      v-if="showCommentModal"
      :complaint="complaint"
      @close="closeModal('comment')"
      @submit="submitComment"
    />

    <SendToDirectorModal
      v-if="showSendToDirectorModal"
      :complaint="complaint"
      @close="closeModal('sendToDirector')"
      @submit="sendToDirector"
    />
  </Layout>
</template>

<script setup>
import { router } from "@inertiajs/vue3";
import { ref, reactive, computed, onMounted } from "vue";
import { Link } from "@inertiajs/vue3";
import Layout from "@/Layouts/UnifiedLayout.vue";
import PriorityModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/PriorityModal.vue";
import ReassignModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/ReassignModal.vue";
import CommentModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/CommentModal.vue";
import SendToDirectorModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/SendToDirectorModal.vue";
import GrievanceDirectorComments from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceDirectorComments.vue";
import GrievanceTimeline from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceTimeline.vue";
import GrievanceAttachments from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceAttachments.vue";
import GrievanceResolutionEvidence from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceResolutionEvidence.vue";
import GrievanceActions from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceActions.vue";
import ToastNotification from "@/Components/GestorReclamacoes/GrievanceDetailComponents/ToastNotification.vue";
import StatusBadge from "@/Components/Grievance/StatusBadge.vue";
import {
  DocumentTextIcon,
  ClockIcon,
  MapPinIcon,
} from "@heroicons/vue/24/outline";

import { useGrievanceDetail } from "@/Components/GestorReclamacoes/Composables/useGrievanceDetail";

// Props do Inertia
const props = defineProps({
  complaint: Object,
  technicians: {
    type: Array,
    default: () => [],
  },
});

// Usar composable
const {
  complaint,
  technicians,
  showPriorityModal,
  showReassignModal,
  showCommentModal,
  showSendToDirectorModal,
  loading,
  toast,
  canSendToDirector,
  canMarkComplete,
  shouldShowResolution,

  // Métodos
  openPriorityModal,
  openReassignModal,
  openCommentModal,
  openSendToDirectorModal,
  closeModal,
  updatePriority,
  reassignTechnician,
  submitComment,
  sendToDirector,
  markComplete,
  refreshComplaintData,
  showToast,
  handleOpenModal,
} = useGrievanceDetail(props);

// Carregar comentários do gestor quando o componente é montado
onMounted(() => {
  if (complaint.value?.manager_comments) {
    // A lógica de carregamento de comentários pode ser movida para o composable
    console.log("Comentários do gestor carregados");
  }
});
</script>
