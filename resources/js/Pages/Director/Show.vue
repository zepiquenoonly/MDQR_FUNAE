<template>
  <AppLayout :title="`Submissão #${submission?.reference_number || 'Carregando...'}`">
    <div class="space-y-4 sm:space-y-6">
      <!-- Estado de carregamento -->
      <div v-if="!complaint" class="text-center py-12">
        <div
          class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand mx-auto"
        ></div>
        <p class="mt-4 text-gray-600 dark:text-gray-400">
          A carregar detalhes da submissão...
        </p>
      </div>

      <!-- Conteúdo principal -->
      <div v-else>
        <!-- Cabeçalho -->
        <div class="mb-8">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                  Submissão #{{ complaint.reference_number || "N/A" }}
                </h1>
                <div class="flex items-center gap-2 mt-2">
                  <span :class="getTypeBadgeClass(complaint.type)">
                    {{ getTypeLabel(complaint.type) }}
                  </span>
                  <span :class="getPriorityBadgeClass(complaint.priority)">
                    {{ getPriorityLabel(complaint.priority) }}
                  </span>
                  <span :class="getStatusBadgeClass(complaint.status)">
                    {{ getStatusLabel(complaint.status) }}
                  </span>
                  <!-- Badge para casos escalados -->
                  <span
                    v-if="isEscalatedToDirector"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400"
                  >
                    <PaperAirplaneIcon class="h-4 w-4 mr-1" />
                    {{ escalationStatusText }}
                  </span>
                  <!-- Badge para Director View -->
                  <span
                    v-if="isDirector"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400"
                  >
                    <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path
                        fill-rule="evenodd"
                        d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                        clip-rule="evenodd"
                      />
                    </svg>
                    Visão do Director
                  </span>
                </div>
              </div>
              <StatusBadge
                :status="submission.status"
                :label="getStatusLabel(submission.status)"
                size="lg"
              />
            </div>

            <button
              @click="goBack"
              class="text-sm text-brand hover:text-orange-700 font-medium flex items-center p-2 cursor-pointer"
            >
              ← Voltar ao Painel
            </button>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Conteúdo principal -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Detalhes -->
          <div class="glass p-6 rounded-xl">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
              Descrição
            </h2>
            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
              {{ complaint.description || "Sem descrição disponível" }}
            </p>

            <div
              class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 grid grid-cols-2 gap-4"
            >
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Submetido por</p>
                <p class="font-medium">
                  {{ complaint.user?.name || complaint.contact_name || "Anónimo" }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                <p class="font-medium">
                  {{ complaint.user?.email || complaint.contact_email || "N/A" }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Data de Criação</p>
                <p class="font-medium">{{ formatDate(complaint.created_at) }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Categoria</p>
                <p class="font-medium">{{ complaint.category || "N/A" }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Telefone</p>
                <p class="font-medium">{{ complaint.contact_phone || "N/A" }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Localização</p>
                <p class="font-medium">
                  {{ complaint.province || "N/A" }}, {{ complaint.district || "N/A" }}
                </p>
              </div>
            </div>
          </div>

          <!-- SECÇÃO DE INFORMAÇÕES DO ESCALAMENTO -->
          <div
            v-if="isEscalatedToDirector"
            class="mt-6 p-6 dark:border-purple-700 bg-white shadow-sm border border-gray-100 dark:bg-purple-900/20 rounded-lg"
          >
            <div class="flex items-center gap-2 mb-4">
              <PaperAirplaneIcon class="h-6 w-6 text-brand dark:text-purple-400" />
              <h3 class="text-lg font-bold text-brand dark:text-purple-300">
                Solicitação do Gestor
              </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Quem escalou -->
              <div class="space-y-1">
                <p class="text-sm text-brand dark:text-purple-400 font-medium">
                  Solicitado por:
                </p>
                <div
                  class="flex items-center gap-2 p-3 bg-white dark:bg-dark-secondary rounded-lg border"
                >
                  <UserCircleIcon class="h-5 w-5 text-gray-500" />
                  <div>
                    <span class="font-medium text-gray-800 dark:text-gray-200">
                      {{ escalationDetails.escalated_by?.name || "Gestor" }}
                    </span>
                    <p
                      v-if="escalationDetails.escalated_by?.email"
                      class="text-xs text-gray-500"
                    >
                      {{ escalationDetails.escalated_by.email }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Data do escalamento -->
              <div class="space-y-1">
                <p class="text-sm text-brand dark:text-purple-400 font-medium">
                  Data da Solicitação:
                </p>

                <div
                  class="flex items-center gap-2 p-3 bg-white dark:bg-dark-secondary rounded-lg border"
                >
                  <CalendarIcon class="h-5 w-5 text-gray-500" />
                  <span class="font-medium text-gray-800 dark:text-gray-200">
                    {{ formatDateTime(escalationDetails.escalated_at) }}
                  </span>
                </div>
              </div>

              <!-- Motivo do escalamento -->
              <div class="space-y-1 md:col-span-2">
                <p class="text-sm text-brand dark:text-purple-400 font-medium">
                  Motivo da Solicitação:
                </p>
                <div
                  class="p-4 bg-white dark:bg-dark-secondary rounded-lg border border-gray-200 dark:border-gray-700"
                >
                  <div class="flex items-start gap-2">
                    <ChatBubbleLeftIcon class="h-5 w-5 text-gray-500 mt-0.5" />

                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                      {{
                        escalationDetails.escalation_reason ||
                        complaint.escalation_reason ||
                        "Motivo não especificado"
                      }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Comentário do escalamento -->
              <div v-if="getManagerComment()" class="space-y-1 md:col-span-2">
                <p class="text-sm text-brand dark:text-purple-400 font-medium">
                  Comentário do Gestor:
                </p>
                <div
                  class="p-4 bg-white dark:bg-dark-secondary rounded-lg border border-gray-200 dark:border-gray-700"
                >
                  <div class="flex items-start gap-2">
                    <ChatBubbleLeftIcon class="h-5 w-5 text-gray-500 mt-0.5" />
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                      {{ getManagerComment() }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- ========== RESPOSTA DO DIRECTOR ========== -->
              <!-- RESPOSTA DO DIRECTOR - VISÍVEL PARA TODOS -->
              <div
                v-if="hasDirectorValidation"
                class="md:col-span-2 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700"
              >
                <div class="space-y-4">
                  <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                      <CheckBadgeIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                      <h4 class="text-lg font-bold text-gray-900 dark:text-white">
                        Resposta do Director
                      </h4>
                    </div>
                  </div>

                  <!-- Card de resposta -->
                  <div
                    class="p-5 rounded-xl border"
                    :class="getValidationCardClass(directorValidationStatus)"
                  >
                    <!-- Cabeçalho da resposta -->
                    <div class="flex items-start justify-between mb-4">
                      <div class="flex items-center gap-3">
                        <!-- Status da resposta -->
                        <span
                          :class="getValidationStatusBadgeClass(directorValidationStatus)"
                        >
                          {{ getValidationLabel(directorValidationStatus) }}
                        </span>

                        <!-- Informações de quem validou -->
                        <div class="flex items-center gap-2">
                          <UserCircleIcon class="h-5 w-5 text-gray-500" />
                          <div>
                            <p
                              class="text-sm font-medium text-gray-800 dark:text-gray-200"
                            >
                              {{ getValidatorName(complaint.director_validation) }}
                            </p>
                            <p class="text-xs text-gray-500">
                              Respondeu em
                              {{
                                formatDateTime(
                                  complaint.director_validation?.validated_at ||
                                    complaint.metadata?.director_validation?.validated_at
                                )
                              }}
                              <span
                                v-if="
                                  complaint.director_validation?.updated_at &&
                                  complaint.director_validation.updated_at !==
                                    complaint.director_validation.validated_at
                                "
                              >
                                • Editado em
                                {{
                                  formatDateTime(complaint.director_validation.updated_at)
                                }}
                              </span>
                            </p>
                          </div>
                        </div>
                      </div>

                      <!-- Ícone do status -->
                      <CheckBadgeIcon
                        v-if="directorValidationStatus === 'approved'"
                        class="h-8 w-8 text-green-600 dark:text-green-400"
                      />
                      <ExclamationTriangleIcon
                        v-else-if="directorValidationStatus === 'rejected'"
                        class="h-8 w-8 text-red-600 dark:text-red-400"
                      />
                      <InformationCircleIcon
                        v-else
                        class="h-8 w-8 text-yellow-600 dark:text-yellow-400"
                      />
                    </div>

                    <!-- Comentário da resposta -->
                    <div
                      v-if="
                        complaint.director_validation?.comment ||
                        complaint.metadata?.director_validation?.comment
                      "
                      class="mt-4 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700"
                    >
                      <div class="flex items-start justify-between mb-2">
                        <p
                          class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                          Comentário do Director:
                        </p>

                        <!-- BOTÃO EDITAR RESPOSTA (dentro do comentário) -->
                        <button
                          v-if="isDirector && !isResolved && !isRejected"
                          @click="openValidationModalForEdit"
                          class="ml-4 inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors shadow-sm"
                          title="Editar resposta"
                        >
                          <PencilSquareIcon class="h-3 w-3 mr-1.5" />
                          Editar
                        </button>
                      </div>

                      <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                        {{
                          complaint.director_validation?.comment ||
                          complaint.metadata?.director_validation?.comment
                        }}
                      </p>
                    </div>

                    <!-- Botão Editar Resposta (alternativo - abaixo do comentário) -->
                    <div
                      v-if="
                        isDirector &&
                        !isResolved &&
                        !isRejected &&
                        !complaint.director_validation?.comment &&
                        !complaint.metadata?.director_validation?.comment
                      "
                      class="mt-4 flex justify-end"
                    >
                      <button
                        @click="openValidationModalForEdit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm transition-colors shadow-sm"
                      >
                        <PencilSquareIcon class="h-4 w-4 mr-2" />
                        Editar Resposta
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- BOTÃO VALIDAR - APENAS PARA DIRECTOR (se não houver validação ainda) -->
              <div
                v-else-if="isDirector && isEscalatedToDirector"
                class="md:col-span-2 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700"
              >
                <div
                  class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700"
                >
                  <div class="flex items-start gap-3">
                    <InformationCircleIcon
                      class="h-6 w-6 text-blue-600 dark:text-blue-400 mt-0.5"
                    />
                    <div class="flex-1">
                      <h4 class="font-medium text-blue-800 dark:text-blue-300 mb-2">
                        Solicitação do Gestor
                      </h4>
                      <p class="text-sm text-blue-700 dark:text-blue-400 mb-3">
                        O gestor solicitou sua intervenção neste caso. Por favor, analise
                        e forneça uma resposta.
                      </p>
                      <button
                        @click="openValidationModal"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm transition-colors shadow-sm"
                      >
                        <CheckBadgeIcon class="h-5 w-5 mr-2" />
                        Validar Solicitação
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Anexos -->
          <div v-if="complaint.attachments?.length > 0" class="glass rounded-xl">
            <GrievanceAttachments :complaint="complaint" />
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- CARD DE AÇÕES (agora no lugar da card Informações da Submissão) -->
          <template v-if="shouldShowActions">
            <GrievanceActions
              :complaint="complaint"
              :technicians="technicians"
              :loading="loading"
              :user="user"
              :can-comment="canComment"
              :is-resolved="isResolved"
              :is-rejected="isRejected"
              :is-pending-approval="isPendingApproval"
              :is-approved="isApproved"
              :is-escalated-to-director="isEscalatedToDirector"
              :has-director-validation="hasDirectorValidation"
              :is-director="isDirector"
              :is-manager="isManager"
              :has-director-assumed-case="isCaseAssumedByDirector"
              :has-director-commented-and-returned="isCaseReturnedToManager"
              :is-waiting-director-intervention="isWaitingDirectorIntervention"
              :is-case-assumed-by-director="isCaseAssumedByDirector"
              :is-case-returned-to-manager="isCaseReturnedToManager"
              :should-show-actions="shouldShowActions"
              @open-modal="handleOpenModal"
            />
          </template>

          <!-- CARD DE INFORMAÇÕES DA SUBMISSÃO (agora no lugar da card Timeline) -->
          <div class="glass p-6 rounded-xl">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
              Informações da Submissão
            </h3>
            <div class="space-y-3">
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Projecto</p>
                <p class="font-medium">{{ complaint.project?.name || "N/A" }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Atribuído a</p>
                <p class="font-medium">
                  {{ complaint.assigned_to?.name || "Não atribuído" }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Data de Atribuição</p>
                <p class="font-medium">
                  {{ formatDate(complaint.assigned_at) || "N/A" }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Data de Resolução</p>
                <p class="font-medium">
                  {{ formatDate(complaint.resolved_at) || "N/A" }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Data de Fecho</p>
                <p class="font-medium">
                  {{ formatDate(complaint.closed_at) || "N/A" }}
                </p>
              </div>

              <!-- Informações do escalamento -->
              <div
                v-if="isEscalatedToDirector"
                class="pt-4 border-t border-gray-200 dark:border-gray-700"
              >
                <h4 class="text-sm font-medium text-brand dark:text-brand mb-2">
                  Informações da Solicitação
                </h4>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Status:</span>
                    <span class="font-medium dark:text-orange-400">
                      {{ hasDirectorValidation ? "Respondido" : "Solicitação Recebida" }}
                    </span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Por:</span>
                    <span class="font-medium">{{
                      escalationDetails.escalated_by?.name || "Gestor"
                    }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Data:</span>
                    <span class="font-medium">{{
                      formatShortDate(escalationDetails.escalated_at)
                    }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Mensagem informativa quando não é director e não deve ver ações -->
          <template v-if="!isDirector && !shouldManagerSeeActions">
            <div class="glass p-6 rounded-xl">
              <div class="text-center p-8">
                <div
                  class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-full mb-4"
                >
                  <ClockIcon class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                  <template v-if="isWaitingDirectorIntervention">
                    Aguardando intervenção do Director
                  </template>
                  <template v-else-if="hasDirectorAssumedCase">
                    Caso assumido pelo Director
                  </template>
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                  <template v-if="isWaitingDirectorIntervention">
                    Esta submissão foi enviada ao Director para análise. As ações estão
                    temporariamente suspensas até que o Director responda.
                  </template>
                  <template v-else-if="hasDirectorAssumedCase">
                    O Director assumiu a responsabilidade por este caso. O acompanhamento
                    está agora a cargo do Director.
                  </template>
                </p>
                <div
                  class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium"
                >
                  <InformationCircleIcon class="h-5 w-5 mr-2" />
                  Apenas comentários disponíveis
                </div>
              </div>
            </div>
          </template>

          <!-- CARD DE TIMELINE (agora no fim) -->
          <div class="glass rounded-xl">
            <GrievanceTimeline :complaint="complaint" :timelineData="timelineData" />
          </div>
        </div>
      </div>

      <!-- MODAL DE COMENTÁRIOS (ESTILO WHATSAPP/FACEBOOK) -->
      <CommentModal
        v-if="showCommentModal"
        ref="commentModalRef"
        :complaint="complaint"
        :comments="localComments"
        :is-open="showCommentModal"
        @close="handleCloseCommentModal"
        @submit="handleCommentSubmit"
        @mark-read="handleMarkCommentsRead"
        @comment-added="handleCommentAdded"
      />

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

      <SendToDirectorModal
        v-if="showSendToDirectorModal"
        :complaint="complaint"
        @close="closeModal('sendToDirector')"
        @submit="sendToDirector"
      />

      <ApprovalDirectorModal
        v-if="showValidationModal"
        :is-open="showValidationModal"
        :submission="complaint"
        :edit-data="editValidationData"
        @close="closeValidationModal"
        @submit="handleValidationSubmit"
      />

      <RejectSubmissionsModal
        v-if="showRejectModal"
        :is-open="showRejectModal"
        :complaint="complaint"
        :loading="loading.reject"
        @close="closeModal('reject')"
        @submit="rejectSubmission"
      />

      <ValidateSubmissionModal
        v-if="showValidateSubmissionModal"
        :submission="complaint"
        :loading="loading.submitValidation"
        @close="closeModal('validateSubmission')"
        @submit="validateSubmission"
      />

      <ReopenSubmissionModal
        v-if="showReopenModal"
        :is-open="showReopenModal"
        :complaint="complaint"
        :loading="loading.reopen"
        @close="closeModal('reopen')"
        @submit="reopenSubmission"
      />

      <ToastNotification v-if="toast.show" :toast="toast" @close="toast.show = false" />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/UnifiedLayout.vue";
import CommentModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/CommentModal.vue";
import ValidateSubmissionModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/ValidateSubmissionModal.vue";
import RejectSubmissionsModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/RejectSubmissionsModal.vue";
import ApprovalDirectorModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/ApprovalDirectorModal.vue";
import GrievanceTimeline from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceTimeline.vue";
import GrievanceActions from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceActions.vue";
import GrievanceAttachments from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceAttachments.vue";
import PriorityModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/PriorityModal.vue";
import ReassignModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/ReassignModal.vue";
import SendToDirectorModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/SendToDirectorModal.vue";
import ReopenSubmissionModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/ReopenSubmissionModal.vue";
import ToastNotification from "@/Components/Director/ToastNotification.vue";
import { useGrievanceDetail } from "@/Components/GestorReclamacoes/Composables/useGrievanceDetail";
import {
  ArrowLeftIcon,
  CheckBadgeIcon,
  PaperAirplaneIcon,
  UserCircleIcon,
  CalendarIcon,
  ChatBubbleLeftIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  PaperClipIcon,
  PencilSquareIcon,
  ClockIcon,
  UserIcon,
} from "@heroicons/vue/24/outline";
import { StarIcon } from "@heroicons/vue/24/solid";

import { useAuth } from "@/Composables/useAuth";

// Inicializar o composable de autenticação
const auth = useAuth();

// Agora use as propriedades computadas do composable
const user = auth.user;
const role = auth.role;
const isDirector = auth.isDirector;
const isManager = auth.isManager;
const isTechnician = auth.isTechnician;
const checkRole = auth.checkRole;

const props = defineProps({
  complaint: {
    type: Object,
    required: true,
  },
  submission: {
    type: Object,
    required: true,
  },
  comments: {
    type: Array,
    default: () => [],
  },
  technicians: {
    type: Array,
    default: () => [],
  },
  managers: {
    type: Array,
    default: () => [],
  },
  projects: {
    type: Array,
    default: () => [],
  },
  timeline_data: {
    type: Array,
    default: () => [],
  },
  user: {
    type: Object,
    default: () => ({}),
  },
  user_role: {
    type: String,
    default: "",
  },
});

// Usar o composable
const {
  // Estados reativos
  complaint: complaintRef,
  technicians: techniciansRef,
  showPriorityModal,
  showReassignModal,
  showCommentModal,
  showReopenModal,
  showSendToDirectorModal,
  showApprovalDirectorModal,
  showMarkCompleteModal,
  showValidateSubmissionModal,
  showRejectModal,
  loading,
  toast,

  // Computed properties de estado
  isPendingApproval,
  isRejected,
  isResolved,
  isApproved,
  isEscalatedToDirector,
  isWaitingDirectorIntervention,
  hasDirectorValidation,
  directorValidationStatus,
  isDirectorRejected,
  isDirectorApproved,
  isDirectorNeedsRevision,

  // Computed properties para botões
  canUpdatePriority,
  canReassignTechnician,
  canSendToDirector,
  canMarkComplete,
  isCaseAssumedByDirector,
  isCaseReturnedToManager,
  canComment,
  canRejectSubmission,
  markCompleteButtonText,
  showSendToDirectorButton,
  escalationStatusText,
  shouldShowActions,
  shouldManagerSeeActions,
  shouldDirectorSeeActions,
  hasDirectorAssumedCase,
  hasDirectorCommentedAndReturned,
  directorResponseStatusText,

  // Métodos auxiliares
  formatDate,
  priorityLabel,
  showToast,

  // Métodos de UI
  openPriorityModal,
  openReassignModal,
  openCommentModal,
  openSendToDirectorModal,
  openApprovalDirectorModal,
  processDirectorResponse,
  openMarkCompleteModal,
  closeModal,
  handleOpenModal,

  // Ações
  updatePriority,
  reassignTechnician,
  sendToDirector,
  markComplete,
  validateSubmission,
  submitDirectorValidation,
  sendComment,
  fetchComments,
  refreshComplaintData,
  rejectSubmission,
  updateDirectorValidation,
} = useGrievanceDetail(props);

// ========== COMPUTED PROPERTIES LOCAIS ==========

const editValidationData = ref(null);
const showValidationModal = ref(false);
const filterComments = ref("all");
const commentModalRef = ref(null);
const localComments = ref([]);

const getValidatorName = (validation) => {
  if (!validation) return "Director";

  if (isDirector.value && validation.validated_by === props.user?.id) {
    return "Eu (Director)";
  }

  return validation.validated_by_name || validation.validated_by?.name || "Director";
};

// Link de retorno baseado no role
const backUrl = computed(() => {
  if (isDirector.value) {
    return "/director/complaints-overview";
  } else if (isManager.value) {
    return "/dashboard";
  }
  return "/dashboard";
});

// Dados da timeline
const timelineData = computed(() => {
  if (!complaintRef.value) return [];

  if (props.timeline_data?.length > 0) {
    return props.timeline_data;
  }

  if (complaintRef.value?.updates?.length > 0) {
    return complaintRef.value.updates;
  }

  if (complaintRef.value?.activities?.length > 0) {
    return complaintRef.value.activities;
  }

  return [];
});

const goBack = () => {
  router.visit(window.history.state?.back || "/dashboard", {
    preserveScroll: true,
    preserveState: true,
  });
};

// Detalhes do escalamento
const escalationDetails = computed(() => {
  if (!isEscalatedToDirector.value || !complaintRef.value) {
    return {};
  }

  if (complaintRef.value.escalation_details) {
    return complaintRef.value.escalation_details;
  }

  return {
    escalated_by: complaintRef.value.escalated_by,
    escalated_at: complaintRef.value.escalated_at,
    escalation_reason: complaintRef.value.escalation_reason,
    escalation_comment: complaintRef.value.updates?.find(
      (u) => u.action_type === "escalated_to_director"
    )?.comment,
    escalation_metadata: complaintRef.value.updates?.find(
      (u) => u.action_type === "escalated_to_director"
    )?.metadata,
  };
});

// Título da página
const pageTitle = computed(() => {
  return `Submissão #${complaintRef.value?.reference_number || "Detalhes"}`;
});

// ========== FUNÇÕES AUXILIARES DE UI ==========

// Funções de formatação de badges
const getTypeBadgeClass = (type) => {
  const classes = {
    submission:
      "bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400",
    complaint: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
    suggestion: "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400",
  };
  return `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
    classes[type] || "bg-gray-100 text-gray-800"
  }`;
};

const getPriorityBadgeClass = (priority) => {
  const classes = {
    low: "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
    medium: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
    high: "bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400",
    critical: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
  };
  return `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
    classes[priority] || "bg-gray-100 text-gray-800"
  }`;
};

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
    in_progress: "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400",
    resolved: "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
    closed: "bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400",
    submitted: "bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400",
    under_review:
      "bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400",
    assigned: "bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-400",
    escalated: "bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400",
    rejected: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
    pending_approval:
      "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
    approved: "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
  };
  return `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
    classes[status] || "bg-gray-100 text-gray-800"
  }`;
};

const getValidationStatusBadgeClass = (status) => {
  const classes = {
    approved: "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
    rejected: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
    needs_revision:
      "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
    commented: "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400",
  };
  return `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
    classes[status] || "bg-gray-100 text-gray-800"
  }`;
};

const getValidationCardClass = (status) => {
  const classes = {
    approved: "border-green-200 dark:border-green-700 bg-green-50 dark:bg-green-900/20",
    rejected: "border-red-200 dark:border-red-700 bg-red-50 dark:bg-red-900/20",
    needs_revision:
      "border-yellow-200 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20",
    commented: "border-blue-200 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/20",
  };
  return (
    classes[status] ||
    "border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20"
  );
};

const getManagerComment = () => {
  if (!isEscalatedToDirector.value || !complaintRef.value) {
    return null;
  }

  // 1. Primeiro tentar do escalation_details
  if (escalationDetails.value?.escalation_comment) {
    return escalationDetails.value.escalation_comment;
  }

  // 2. Tentar do comment no escalation_details
  if (escalationDetails.value?.comment) {
    return escalationDetails.value.comment;
  }

  // 3. Buscar no histórico de updates (updates do tipo "escalated_to_director")
  const escalationUpdate = complaintRef.value.updates?.find(
    (u) => u.action_type === "escalated_to_director" && u.comment
  );

  if (escalationUpdate?.comment) {
    return escalationUpdate.comment;
  }

  // 4. Buscar no complaint.director_validation (se houver)
  if (complaintRef.value.director_validation?.comment) {
    return complaintRef.value.director_validation.comment;
  }

  // 5. Último recurso: mostrar mensagem padrão
  return "Comentário não especificado pelo gestor.";
};

// Funções de label
const getTypeLabel = (type) => {
  if (!type) return "Tipo não definido";
  const labels = {
    grievance: "Queixa",
    complaint: "Reclamação",
    suggestion: "Sugestão",
    submission: "Submissão",
  };
  return labels[type] || type;
};

const getPriorityLabel = (priority) => {
  if (!priority) return "Prioridade não definida";
  const labels = {
    low: "Baixa",
    medium: "Média",
    high: "Alta",
    critical: "Crítica",
  };
  return labels[priority] || priority;
};

const getStatusLabel = (status) => {
  if (!status) return "Estado não definido";
  const labels = {
    pending: "Pendente",
    in_progress: "Em Análise",
    resolved: "Resolvido",
    closed: "Fechado",
    submitted: "Submetida",
    under_review: "Em Revisão",
    assigned: "Atribuída",
    escalated: "Escalada para Director",
    rejected: "Rejeitada",
    pending_approval: "Pendente de Aprovação",
    approved: "Aprovado",
  };
  return labels[status] || status;
};

const getValidationLabel = (status) => {
  if (!status) return "Validação";
  const labels = {
    approved: "Aprovado pelo Director",
    rejected: "Rejeitado pelo Director",
    needs_revision: "Revisão Solicitada pelo Director",
    commented: "Comentado pelo Director",
  };
  return labels[status] || "Validação";
};

// Formatação de data/hora
const formatDateTime = (dateString) => {
  if (!dateString) return "N/A";
  try {
    const date = new Date(dateString);
    return date.toLocaleString("pt-PT", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch (error) {
    return "Data/hora inválida";
  }
};

const formatShortDate = (dateString) => {
  if (!dateString) return "N/A";
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString("pt-PT", {
      day: "2-digit",
      month: "2-digit",
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch (error) {
    return "Data inválida";
  }
};

const formatFileSize = (bytes) => {
  if (!bytes) return "0 Bytes";
  if (bytes === 0) return "0 Bytes";
  const k = 1024;
  const sizes = ["Bytes", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};

const getCommentTypeLabel = (comment) => {
  if (comment.type === "director_validation") return "Validação Director";
  if (comment.user?.role === "Director") return "Director";
  if (comment.type === "public") return "Público";
  if (comment.type === "director_only") return "Apenas Director";
  return "Interno";
};

// ========== FUNÇÕES DE VALIDAÇÃO ==========

// ADICIONAR: Função para abrir modal de validação
const openValidationModal = () => {
  if (!isDirector.value) {
    showToast("Apenas o Director pode validar solicitações", "warning");
    return;
  }

  if (!isEscalatedToDirector.value || hasDirectorValidation.value) {
    showToast("Não é possível validar esta solicitação", "warning");
    return;
  }

  showValidationModal.value = true;
  editValidationData.value = null;
};

const openValidationModalForEdit = () => {
  if (!isDirector.value) {
    showToast("Apenas o Director pode editar a resposta", "warning");
    return;
  }

  if (!hasDirectorValidation.value) {
    showToast("Não há resposta para editar", "warning");
    return;
  }

  // DEBUG DETALHADO
  console.log("=== DEBUG DETALHADO ===");
  console.log("1. complaintRef.value:", JSON.parse(JSON.stringify(complaintRef.value)));
  console.log("2. hasDirectorValidation:", hasDirectorValidation.value);
  console.log("3. director_validation:", complaintRef.value.director_validation);
  console.log("4. metadata:", complaintRef.value.metadata);

  // Verificar diferentes possibilidades de onde pode estar a validação
  let validationData = null;

  // Possibilidade 1: Direto no complaint.director_validation
  if (
    complaintRef.value.director_validation &&
    typeof complaintRef.value.director_validation === "object"
  ) {
    validationData = complaintRef.value.director_validation;
    console.log("Encontrado em director_validation");
  }
  // Possibilidade 2: No metadata
  else if (
    complaintRef.value.metadata &&
    complaintRef.value.metadata.director_validation
  ) {
    validationData = complaintRef.value.metadata.director_validation;
    console.log("Encontrado em metadata.director_validation");
  }

  if (!validationData) {
    console.error("Não foi possível encontrar dados de validação");
    showToast("Erro: Dados da validação não encontrados", "error");
    return;
  }

  console.log("Dados da validação encontrados:", validationData);

  // Converter 'needs_revision' do backend para 'commented' no frontend
  const frontendStatus =
    validationData.status === "needs_revision" ? "commented" : validationData.status;

  editValidationData.value = {
    id: validationData.id || complaintRef.value.id,
    status: frontendStatus,
    comment: validationData.comment || validationData.comments || "",
    validated_at: validationData.validated_at || validationData.created_at,
    validated_by: validationData.validated_by,
    notify_manager:
      validationData.notify_manager !== undefined ? validationData.notify_manager : true,
    notify_technician:
      validationData.notify_technician !== undefined
        ? validationData.notify_technician
        : true,
    notify_user:
      validationData.notify_user !== undefined ? validationData.notify_user : false,
  };

  // Abrir modal
  showValidationModal.value = true;
  console.log("Abrindo modal de edição com dados preparados:", editValidationData.value);
};

// ADICIONAR: Função para fechar modal de validação
const closeValidationModal = () => {
  showValidationModal.value = false;
  editValidationData.value = null;
};

const handleValidationSubmit = async (formData) => {
  console.log("handleValidationSubmit chamado com:", formData);

  // Verificar se é uma nova validação ou edição
  if (!editValidationData.value) {
    // Nova validação - usar submitDirectorValidation
    try {
      await submitDirectorValidation({
        status: formData.status, // 'approved' ou 'commented'
        comment: formData.comment,
      });

      // Fechar modal
      showValidationModal.value = false;
    } catch (error) {
      console.error("Erro na validação:", error);
      showToast("Erro ao enviar resposta: " + error.message, "error");
    }
  } else {
    // Edição de validação existente
    console.log("Modo de edição - ID:", editValidationData.value.id);

    try {
      // Enviar 'commented' direto para o backend
      const statusToSend = formData.status; // 'approved' ou 'commented'

      await updateDirectorValidation({
        status: statusToSend,
        comment: formData.comment,
        validationId: editValidationData.value.id,
      });

      // Limpar dados de edição
      editValidationData.value = null;
      showValidationModal.value = false;
    } catch (error) {
      console.error("Erro na edição:", error);
      if (!toast.show) {
        showToast("Erro ao atualizar resposta: " + error.message, "error");
      }
    }
  }
};

const confirmMarkComplete = async () => {
  await markComplete();
};

const rejectCompletion = async () => {
  showToast("Função de rejeição de conclusão não implementada nesta versão", "warning");
};

const markAsViewedByDirector = async () => {
  showToast("Função de marcar como visualizado não implementada nesta versão", "warning");
};

const handleDirectorResponse = async (responseData) => {
  await submitDirectorValidation({
    ...responseData,
    status: responseData.status === "commented" ? "needs_revision" : responseData.status,
    assumed_by_director: responseData.status === "approved",
  });
};

const debugInfo = computed(() => {
  return {
    userRole: role.value,
    isDirector: isDirector.value,
    isManager: isManager.value,
    isEscalated: isEscalatedToDirector.value,
    hasDirectorValidation: hasDirectorValidation.value,
    directorValidationStatus: directorValidationStatus.value,
    hasDirectorAssumedCase: hasDirectorAssumedCase.value,
    hasDirectorCommentedAndReturned: hasDirectorCommentedAndReturned.value,
    complaintStatus: complaintRef.value?.status,
    directorValidation: complaintRef.value?.director_validation,
    metadata: complaintRef.value?.metadata,
  };
});

// Inicialização
onMounted(() => {
  console.log("=== SHOW COMPONENT MOUNTED ===");
  console.log("isCaseAssumedByDirector:", isCaseAssumedByDirector?.value);
  console.log("isCaseReturnedToManager:", isCaseReturnedToManager?.value);
  console.log("shouldShowActions:", shouldShowActions?.value);
  console.log("canComment:", canComment?.value);
  console.log("User role:", role?.value);

  loadComments();

  // Verificar os updates para ver se há informação do director
  console.log("Updates:", complaintRef.value?.updates);

  // Verificar se há algum update relacionado ao director
  const directorUpdates = timelineData.value?.filter(
    (update) =>
      update.user?.role === "director" ||
      update.action_type?.includes("director") ||
      update.action_type?.includes("validation")
  );
  console.log("Director Updates Count:", directorUpdates?.length);

  // Mostrar detalhes de cada director update
  if (directorUpdates && directorUpdates.length > 0) {
    directorUpdates.forEach((update, index) => {
      console.log(`Director Update ${index + 1}:`, {
        id: update.id,
        action_type: update.action_type,
        status: update.status,
        description: update.description,
        comment: update.comment,
        metadata: update.metadata,
        user: update.user,
        created_at: update.created_at,
      });
    });
  }

  // Verificar o metadata do complaint
  console.log("Complaint Metadata:", complaintRef.value?.metadata);

  // Verificar director_validation field
  console.log("Director Validation Field:", complaintRef.value?.director_validation);
  console.log(
    "Type of director_validation:",
    typeof complaintRef.value?.director_validation
  );
});

const loadComments = async () => {
  try {
    const comments = await fetchComments();
    localComments.value = comments;
    console.log("Comentários carregados:", comments.length);
  } catch (error) {
    console.error("Erro ao carregar comentários:", error);
  }
};

// Watch para quando o modal abrir
watch(
  () => [isCaseAssumedByDirector?.value, isCaseReturnedToManager?.value],
  ([assumed, returned]) => {
    console.log("Estado atualizado:");
    console.log("isCaseAssumedByDirector:", assumed);
    console.log("isCaseReturnedToManager:", returned);
  }
);

// Função para enviar comentário
const handleCommentSubmit = async (commentData) => {
  console.log("=== HANDLE COMMENT SUBMIT ===");
  console.log("Comment data:", commentData);

  try {
    // Enviar comentário usando a função do composable
    const newComment = await sendComment(commentData);
    console.log("New comment from server:", newComment);

    if (newComment) {
      // Adicionar comentário localmente
      const formattedComment = {
        id: newComment.id,
        content: newComment.content,
        comment: newComment.comment,
        type: newComment.type,
        action_type: newComment.action_type,
        created_at: newComment.created_at,
        user: newComment.user,
        attachments: newComment.attachments,
        metadata: newComment.metadata,
      };

      // Adicionar ao array local
      localComments.value.push(formattedComment);

      // Se o modal estiver aberto, atualizar via ref
      if (commentModalRef.value && commentModalRef.value.addLocalComment) {
        commentModalRef.value.addLocalComment(formattedComment);
      }

      // Atualizar contador de comentários
      if (complaint.value) {
        complaint.value.comments_count = (complaint.value.comments_count || 0) + 1;
      }

      console.log("Comentário adicionado localmente:", formattedComment);

      // Não fechar o modal imediatamente - deixar o usuário ver o comentário
      // O modal será fechado quando o usuário clicar no X
    }
  } catch (error) {
    console.error("Erro ao enviar comentário:", error);
    // O erro já foi tratado no sendComment
  }
};

// Handler para quando um comentário é adicionado no modal
const handleCommentAdded = (comment) => {
  console.log("Comentário adicionado via evento:", comment);
  // Já tratamos no handleCommentSubmit, mas mantemos para consistência
};

// Handler para fechar o modal de comentários
const handleCloseCommentModal = () => {
  console.log("Fechando modal de comentários");
  closeModal("comment");

  // Recarregar os dados da reclamação para garantir sincronização
  setTimeout(() => {
    refreshComplaintData();
  }, 500);
};

// Função para marcar comentários como lidos
const handleMarkCommentsRead = () => {
  console.log("Marcando comentários como lidos");
  showToast("Comentários marcados como lidos", "success");
};

const complaintAttachments = computed(() => {
  if (!complaint.value) return [];

  // Retornar apenas anexos diretos da reclamação, não de comentários
  return (
    complaint.value.attachments?.filter((attach) => {
      // Excluir anexos de comentários
      return !attach.comment_id && !attach.comment;
    }) || []
  );
});

// Expor a função para recarregar comentários (pode ser chamada de outros lugares)
defineExpose({
  loadComments,
});
</script>
