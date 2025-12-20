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
        class="pointer-events-auto bg-white dark:bg-dark-secondary rounded-2xl w-full max-w-4xl max-h-[95vh] flex flex-col shadow-2xl"
      >
        <!-- Header -->
        <div class="p-8 flex-shrink-0">
          <div class="flex items-center justify-between mb-4">
            <div class="flex-1">
              <h2
                class="text-xl font-bold text-gray-900 dark:text-dark-text-primary leading-tight"
              >
                Encaminhar Submissão ao Director
              </h2>
              <p class="text-base text-gray-700 dark:text-gray-300 mt-3 leading-relaxed">
                Esta submissão será encaminhada diretamente ao Director para análise e
                deliberação especializada.
              </p>
            </div>
            <button
              @click="$emit('close')"
              class="ml-4 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full p-2 transition-all duration-200"
            >
              <XMarkIcon class="h-8 w-8" />
            </button>
          </div>

          <!-- Informação da Submissão -->
          <div
            class="mt-6 p-5 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl"
          >
            <div class="flex items-center gap-4">
              <div class="flex-1">
                <p class="text-base font-bold text-gray-900 dark:text-gray-100 mb-1">
                  Submissão Nº:
                  <span class="text-brand text-lg">
                    {{ complaint.reference_number }}
                  </span>
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                  {{
                    complaint.description?.substring(0, 150) || "Descrição não disponível"
                  }}
                  <span v-if="complaint.description?.length > 150">...</span>
                </p>
              </div>
              <div class="flex-shrink-0">
                <span
                  class="px-4 py-2 text-sm font-bold rounded-full bg-gradient-to-r from-brand to-orange-600 text-white shadow-md"
                >
                  {{ getStatusText(complaint.status) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Form -->
        <div class="p-8 flex-1 overflow-y-auto">
          <div class="space-y-8">
            <!-- Motivo do Encaminhamento -->
            <div>
              <div class="mb-4">
                <h3
                  class="text-xl font-bold text-gray-900 dark:text-dark-text-primary mb-2"
                >
                  Motivo do Encaminhamento
                  <span class="text-red-500 ml-1 text-xl">*</span>
                </h3>
                <p class="text-base text-gray-700 dark:text-gray-400 leading-relaxed">
                  Selecione cuidadosamente o motivo principal que justifica o
                  encaminhamento desta submissão para análise directorial.
                </p>
              </div>

              <div class="relative">
                <select
                  v-model="form.reason"
                  class="w-full px-6 py-4 text-lg border-3 border-gray-300 dark:border-gray-700 rounded-xl focus:ring-0 focus:ring-brand focus:border-brand dark:focus:ring-orange-500/40 dark:focus:border-orange-500 bg-white dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary transition-all duration-300 appearance-none cursor-pointer hover:border-brand/50 dark:hover:border-orange-500/50"
                  :class="{ 'border-red-500 ring-4 ring-red-500/30': errors.reason }"
                >
                  <option value="" disabled selected class="text-gray-500 py-3">
                    Selecione o motivo de encaminhamento
                  </option>

                  <optgroup
                    label="MOTIVOS TÉCNICOS E OPERACIONAIS"
                    class="text-base font-semibold text-gray-900 dark:text-gray-300"
                  >
                    <option
                      value="Caso Complexo"
                      class="py-3 text-base hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                      Caso Complexo - Requer análise técnica especializada e aprofundada
                    </option>
                    <option
                      value="Alto Impacto Operacional
                    interessadas"
                      class="py-3 text-base hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                      Alto Impacto Operacional - Afeta significativamente múltiplas partes
                      interessadas
                    </option>
                    <option
                      value="Disputa Técnica"
                      class="py-3 text-base hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                      Disputa Técnica - Necessita arbitragem e decisão de alto nível
                      técnico
                    </option>
                  </optgroup>
                  <optgroup
                    label="MOTIVOS ADMINISTRATIVOS E FINANCEIROS"
                    class="text-base font-semibold text-gray-900 dark:text-gray-300"
                  >
                    <option
                      value="Aprovação de Orçamento"
                      class="py-3 text-base hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                      Aprovação de Orçamento - Requer autorização financeira
                      extraordinária
                    </option>
                    <option
                      value="Alocação de Recursos Estratégicos"
                      class="py-3 text-base hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                      Alocação de Recursos Estratégicos - Necessita decisão sobre alocação
                      de recursos significativos
                    </option>
                  </optgroup>
                  <optgroup
                    label="MOTIVOS JURÍDICOS E DE POLÍTICA"
                    class="text-base font-semibold text-gray-900 dark:text-gray-300"
                  >
                    <option
                      value="Questões Legais e Conformidade"
                      class="py-3 text-base hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                      Questões Legais e Conformidade - Envolve aspectos jurídicos ou de
                      conformidade regulatória
                    </option>
                    <option
                      value="Decisão de Política Organizacional"
                      class="py-3 text-base hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                      Decisão de Política Organizacional - Impacta diretrizes e políticas
                      institucionais
                    </option>
                    <option
                      value="Relações Externas e Institucionais"
                      class="py-3 text-base hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                      Relações Externas e Institucionais - Envolve parceiros estratégicos
                      ou entidades governamentais
                    </option>
                  </optgroup>
                  <optgroup
                    label="OUTROS MOTIVOS ESPECIAIS"
                    class="text-base font-semibold text-gray-900 dark:text-gray-300"
                  >
                    <option
                      value="Problema Recorrente Crítico"
                      class="py-3 text-base hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                      Problema Recorrente Crítico - Necessita intervenção estratégica para
                      resolução definitiva
                    </option>
                    <option
                      value="Matéria Altamente Sensível"
                      class="py-3 text-base hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                      Matéria Altamente Sensível - Requer confidencialidade máxima e
                      tratamento especializado
                    </option>
                    <option
                      value="Atentado à vida/saúde"
                      class="py-3 text-base hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                      Atentado à vida/saúde - Requer um tratamento extremamente delicado
                    </option>
                    <option
                      value="Importância Estratégica"
                      class="py-3 text-base hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                      Importância Estratégica - Tem implicações de longo prazo para a
                      organização
                    </option>
                    <option
                      value="Outro Motivo Específico"
                      class="py-3 text-base hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                      Outro Motivo Específico - Por favor, especifique detalhadamente no
                      campo de comentários abaixo
                    </option>
                  </optgroup>
                </select>
                <div
                  class="absolute right-4 top-1/2 transform -translate-y-1/2 pointer-events-none"
                >
                  <ChevronDownIcon class="h-6 w-6 text-gray-500" />
                </div>
              </div>

              <div
                v-if="errors.reason"
                class="mt-4 p-4 bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 border-2 border-red-200 dark:border-red-800 rounded-xl"
              >
                <div class="flex items-start gap-3">
                  <ExclamationCircleIcon
                    class="h-6 w-6 text-red-500 dark:text-red-400 flex-shrink-0 mt-0.5"
                  />
                  <div>
                    <p
                      class="text-base font-semibold text-red-700 dark:text-red-300 mb-1"
                    >
                      Campo obrigatório
                    </p>
                    <p class="text-sm text-red-600 dark:text-red-400">
                      {{ errors.reason }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Comentário para o Director -->
            <div>
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h3
                    class="text-xl font-bold text-gray-900 dark:text-dark-text-primary mb-1"
                  >
                    Comentário para o Director
                    <span class="text-red-500 ml-1 text-xl">*</span>
                  </h3>
                  <p class="text-base text-gray-700 dark:text-gray-400">
                    Forneça informações detalhadas e contextuais para orientar a análise
                    do Director
                  </p>
                </div>
              </div>

              <div class="relative">
                <textarea
                  v-model="form.comment"
                  rows="10"
                  class="w-full px-6 py-5 text-sm border-3 border-gray-300 dark:border-gray-700 rounded-2xl focus:ring-0 focus:ring-brand focus:border-brand dark:focus:ring-orange-500/40 dark:focus:border-orange-500 bg-white dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary resize-y transition-all duration-300 leading-relaxed"
                  :class="{ 'border-red-500 ring-4 ring-red-500/30': errors.comment }"
                  placeholder="Por favor, descreva em detalhe as razões específicas que justificam o encaminhamento desta submissão ao Director. Considere incluir:

1. Contexto detalhado da situação ou problema
2. Impacto potencial e implicações da decisão
3. Análise de riscos e considerações críticas
4. Recomendações específicas ou cursos de ação sugeridos
5. Qualquer informação adicional relevante para a análise do director"
                  @input="updateCharacterCount"
                ></textarea>

                <!-- Contador de caracteres em tempo real -->
                <div class="absolute bottom-3 right-3">
                  <div
                    :class="[
                      'px-2 py-1 text-xs font-bold rounded transition-all duration-300',
                      form.comment.length > 2200
                        ? 'bg-gradient-to-r from-red-500 to-pink-500 text-white'
                        : form.comment.length > 1800
                        ? 'text-white'
                        : 'bg-gradient-to-r from-green-500 to-emerald-500 text-white',
                    ]"
                  >
                    {{ 2500 - form.comment.length }}
                  </div>
                </div>
              </div>
            </div>

            <div
              v-if="errors.comment"
              class="mt-4 p-4 bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 border-2 border-red-200 dark:border-red-800 rounded-xl"
            >
              <div class="flex items-start gap-3">
                <ExclamationCircleIcon
                  class="h-6 w-6 text-red-500 dark:text-red-400 flex-shrink-0 mt-0.5"
                />
                <div>
                  <p class="text-base font-semibold text-red-700 dark:text-red-300 mb-1">
                    Requisitos de conteúdo não atendidos
                  </p>
                  <p class="text-sm text-red-600 dark:text-red-400">
                    {{ errors.comment }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Ações -->
          <div
            class="mt-10 pt-8 border-t-2 border-gray-100 dark:border-gray-800 flex-shrink-0"
          >
            <div class="flex gap-6">
              <button
                @click="$emit('close')"
                class="flex-1 px-8 py-4 border-3 border-gray-300 dark:border-gray-700 text-gray-800 dark:text-gray-300 rounded font-bold hover:bg-gray-50 dark:hover:bg-gray-800 transition-all duration-300 text-lg flex items-center justify-center gap-3 hover:shadow-lg"
              >
                <XMarkIcon class="h-6 w-6" />
                Cancelar Operação
              </button>
              <button
                @click="submit"
                :disabled="loading || !isFormValid"
                :class="[
                  'flex-1 px-8 py-4 rounded font-bold transition-all duration-300 text-lg flex items-center justify-center gap-3',
                  loading || !isFormValid
                    ? 'bg-green-600 text-gray-300 cursor-not-allowed shadow-inner'
                    : 'bg-green-700 text-white hover:bg-green-800 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 active:translate-y-0',
                ]"
              >
                <template v-if="loading">
                  <div
                    class="animate-spin rounded-full h-6 w-6 border-b-3 border-white"
                  ></div>
                  <span>A processar...</span>
                </template>
                <template v-else>
                  <PaperAirplaneIcon class="h-6 w-6" />
                  <span>Enviar ao Director</span>
                </template>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from "vue";
import {
  XMarkIcon,
  PaperAirplaneIcon,
  ExclamationCircleIcon,
  CheckCircleIcon,
  ChevronDownIcon,
} from "@heroicons/vue/24/outline";

const emit = defineEmits(["close", "submit"]);
const props = defineProps({
  complaint: Object,
});

const loading = ref(false);
const errors = reactive({});

const form = reactive({
  reason: "",
  comment: "",
});

const reasonLabels = {
  complex_case: "Caso Complexo",
  high_impact: "Alto Impacto Operacional",
  technical_dispute: "Disputa Técnica",
  budget_approval: "Aprovação de Orçamento",
  resource_allocation: "Alocação de Recursos Estratégicos",
  legal_issues: "Questões Legais e Conformidade",
  policy_decision: "Decisão de Política Organizacional",
  external_relations: "Relações Externas e Institucionais",
  recurring_issue: "Problema Recorrente Crítico",
  sensitive_matter: "Matéria Altamente Sensível",
  strategic_importance: "Importância Estratégica",
  other: "Outro Motivo Específico",
};

// Função para traduzir status para português
const getStatusText = (status) => {
  const statusMap = {
    submitted: "Submetida",
    under_review: "Em Análise",
    assigned: "Atribuída",
    in_progress: "Em Andamento",
    pending_approval: "Pendente de Aprovação",
    resolved: "Resolvida",
    rejected: "Rejeitada",
    open: "Aberta",
    pending_completion: "Pendente de Conclusão",
    closed: "Concluída",
    escalated: "Enviada ao Director",
    pending_director_review: "Pendente de Revisão do Director",
    director_reviewed: "Revisada pelo Director",
    status_changed: "Estado Alterado",
    priority_changed: "Prioridade Alterada",
    technician_assigned: "Técnico Atribuído",
    created: "Criada",
    draft: "Rascunho",
    archived: "Arquivada",
    on_hold: "Em Espera",
    waiting_response: "Aguardando Resposta",
    forwarded: "Encaminhada",
    processing: "Em Processamento",
    completed: "Concluída",
    cancelled: "Cancelada",
  };

  return statusMap[status] || status;
};

const getReasonLabel = (reason) => {
  return reasonLabels[reason] || "Aguardando seleção do motivo de encaminhamento";
};

const countWords = () => {
  if (!form.comment.trim()) return 0;
  return form.comment
    .trim()
    .split(/\s+/)
    .filter((word) => word.length > 0).length;
};

const updateCharacterCount = () => {
  const maxChars = 2500;
  if (form.comment.length > maxChars) {
    form.comment = form.comment.substring(0, maxChars);
  }
};

const isFormValid = computed(() => {
  return form.reason && form.comment.trim().length >= 10 && countWords() >= 10;
});

const validateForm = () => {
  errors.reason = "";
  errors.comment = "";

  if (!form.reason) {
    errors.reason =
      "A seleção de um motivo de encaminhamento é obrigatória. Por favor, escolha uma opção da lista que melhor descreva a razão para encaminhar esta submissão ao Director.";
    return false;
  }

  if (!form.comment.trim()) {
    errors.comment =
      "O campo de comentário é obrigatório. Forneça informações detalhadas que auxiliem o Director na compreensão do contexto e na tomada de decisão informada sobre esta submissão.";
    return false;
  }

  if (form.comment.length < 10) {
    errors.comment =
      "O comentário deve conter pelo menos 10 caracteres para fornecer contexto adequado e informações suficientes para a análise directorial. Atualmente possui " +
      form.comment.length +
      " caracteres.";
    return false;
  }

  if (form.comment.length > 2500) {
    errors.comment =
      "O comentário excede o limite máximo de 2500 caracteres. Por favor, revise e condense suas observações para garantir clareza e concisão. Atualmente possui " +
      form.comment.length +
      " caracteres.";
    return false;
  }

  if (countWords() < 10) {
    errors.comment =
      "O comentário deve conter pelo menos 10 palavras para ser significativo e informativo. Atualmente contém " +
      countWords() +
      " palavras.";
    return false;
  }

  return true;
};

const submit = () => {
  if (!validateForm()) return;

  loading.value = true;
  setTimeout(() => {
    emit("submit", { ...form });
    loading.value = false;
  }, 5000);
};
</script>
