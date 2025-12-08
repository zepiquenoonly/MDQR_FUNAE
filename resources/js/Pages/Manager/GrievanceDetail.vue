<template>
  <Layout>
    <div class="space-y-4 sm:space-y-6">
      <!-- Breadcrumb & Header -->
      <div class="flex flex-col gap-3 sm:gap-4">
        <GrievanceBreadcrumb />
        <GrievanceHeader :complaint="complaint" />
      </div>

      <!-- Main Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Left Column - 2/3 -->
        <div class="lg:col-span-2 space-y-4 sm:space-y-6">
          <GrievanceDescription :complaint="complaint" />
          <GrievanceTimeline :complaint="complaint" />
          <GrievanceAttachments :complaint="complaint" />

          <GrievanceResolutionEvidence v-if="shouldShowResolution" :complaint="complaint" />
        </div>

        <!-- Right Column - 1/3 -->
        <div class="space-y-4 sm:space-y-6">
          <GrievanceQuickStatus :complaint="complaint" />
          <GrievanceUserInfo :complaint="complaint" />
          <GrievanceActions :complaint="complaint" :technicians="technicians" :loading="loading"
            :can-reassign-technician="canReassignTechnician" :can-update-priority="canUpdatePriority"
            :can-send-to-director="canSendToDirector" :can-mark-complete="canMarkComplete" @open-modal="handleOpenModal"
            @mark-complete="markComplete" @reject-completion="rejectCompletion" @reject-submission="rejectSubmission"
            @refresh="refreshComplaintData" />
        </div>
      </div>
    </div>
    <div class="mt-8">
      <GrievanceDirectorComments v-if="complaint.director_comments?.length > 0" :complaint="complaint" />
    </div>
    <!--Toast Notification -->
    <ToastNotification v-if="toast.show" :toast="toast" @close="toast.show = false" />

    <!-- Modals -->
    <PriorityModal v-if="showPriorityModal" :complaint="complaint" @close="closeModal('priority')"
      @update="updatePriority" />

    <ReassignModal v-if="showReassignModal" :complaint="complaint" :technicians="technicians"
      @close="closeModal('reassign')" @update="reassignTechnician" />

    <CommentModal v-if="showCommentModal" :complaint="complaint" @close="closeModal('comment')"
      @submit="submitComment" />

    <SendToDirectorModal v-if="showSendToDirectorModal" :complaint="complaint" @close="closeModal('sendToDirector')"
      @submit="sendToDirector" />
  </Layout>
</template>

<script setup>
import { router } from "@inertiajs/vue3";
import { ref, reactive, computed, onMounted } from "vue";
import Layout from "@/Layouts/ManagerLayout.vue";
import PriorityModal from "@/Components/GestorReclamacoes/PriorityModal.vue";
import ReassignModal from "@/Components/GestorReclamacoes/ReassignModal.vue";
import CommentModal from "@/Components/GestorReclamacoes/CommentModal.vue";
import SendToDirectorModal from "@/Components/GestorReclamacoes/SendToDirectorModal.vue";
import GrievanceDirectorComments from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceDirectorComments.vue";
import GrievanceBreadcrumb from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceBreadcrumb.vue";
import GrievanceHeader from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceHeader.vue";
import GrievanceDescription from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceDescription.vue";
import GrievanceTimeline from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceTimeline.vue";
import GrievanceAttachments from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceAttachments.vue";
import GrievanceResolutionEvidence from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceResolutionEvidence.vue";
import GrievanceQuickStatus from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceQuickStatus.vue";
import GrievanceUserInfo from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceUserInfo.vue";
import GrievanceActions from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceActions.vue";
import ToastNotification from "@/Components/GestorReclamacoes/GrievanceDetailComponents/ToastNotification.vue";

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
