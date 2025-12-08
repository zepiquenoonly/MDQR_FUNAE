<template>
  <AppLayout title="Detalhes do Funcionário">
    <div class="max-w-full mx-auto">
      <!-- Cabeçalho -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <button
            @click="router.get(route('director.managers.index'))"
            class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-2 mb-4"
          >
            <ArrowLeftIcon class="h-4 w-4" />
            Voltar à lista
          </button>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Detalhes do Funcionário
          </h1>
        </div>
      </div>

      <!-- Card principal -->
      <div class="glass rounded-xl p-6 mb-6">
        <div class="flex flex-col md:flex-row gap-6">
          <!-- Avatar e informações básicas -->
          <div class="md:w-1/3">
            <div class="flex flex-col items-center">
              <div class="h-40 w-40 mb-4">
                <img
                  v-if="employee.avatar_url"
                  :src="employee.avatar_url"
                  :alt="employee.name"
                  class="h-full w-full rounded-full object-cover border-4 border-white dark:border-gray-800"
                />
                <div
                  v-else
                  class="h-full w-full rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center border-4 border-white dark:border-gray-800"
                >
                  <UserIcon class="h-20 w-20 text-gray-500 dark:text-gray-400" />
                </div>
              </div>

              <div class="text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ employee.name }}
                </h2>
                <p class="text-gray-600 dark:text-gray-400">
                  {{ getRoleDisplayName(employee.position) }}
                </p>

                <!-- Status Badge -->
                <div class="mt-4">
                  <span
                    :class="
                      employee.is_available
                        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                        : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'
                    "
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                  >
                    {{ employee.is_available ? "Ativo" : "Inativo" }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Informações detalhadas -->
          <div class="md:w-2/3">
            <div v-if="!isEditing">
              <!-- Modo visualização - 3 colunas -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Coluna 1: Informações Pessoais -->
                <div class="space-y-4">
                  <h3
                    class="text-lg font-semibold text-gray-900 dark:text-white border-b pb-2"
                  >
                    Informações Pessoais
                  </h3>
                  <div>
                    <label
                      class="block text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                      Nome Completo
                    </label>
                    <p class="mt-1 text-base text-gray-900 dark:text-white">
                      {{ employee.name }}
                    </p>
                  </div>
                  <div>
                    <label
                      class="block text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                      Email
                    </label>
                    <p class="mt-1 text-base text-gray-900 dark:text-white">
                      {{ employee.email }}
                    </p>
                  </div>
                  <div>
                    <label
                      class="block text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                      Username
                    </label>
                    <p class="mt-1 text-base text-gray-900 dark:text-white">
                      {{ employee.username }}
                    </p>
                  </div>
                  <div>
                    <label
                      class="block text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                      Telefone
                    </label>
                    <p class="mt-1 text-base text-gray-900 dark:text-white">
                      {{ employee.phone || "Não informado" }}
                    </p>
                  </div>
                </div>

                <!-- Coluna 2: Informações Profissionais e Morada -->
                <div class="space-y-4">
                  <h3
                    class="text-lg font-semibold text-gray-900 dark:text-white border-b pb-2"
                  >
                    Informações Profissionais
                  </h3>
                  <div>
                    <label
                      class="block text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                      Cargo
                    </label>
                    <p class="mt-1 text-base text-gray-900 dark:text-white">
                      {{ getRoleDisplayName(employee.primary_role) }}
                    </p>
                  </div>
                  <div>
                    <label
                      class="block text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                      Data de Admissão
                    </label>
                    <p class="mt-1 text-base text-gray-900 dark:text-white">
                      {{ formatDate(employee.hire_date) }}
                    </p>
                  </div>

                  <div>
                    <label
                      class="block text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                      Província
                    </label>
                    <p class="mt-1 text-base text-gray-900 dark:text-white">
                      {{ employee.province || "N/A" }}
                    </p>
                  </div>
                  <div>
                    <label
                      class="block text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                      Distrito
                    </label>
                    <p class="mt-1 text-base text-gray-900 dark:text-white">
                      {{ employee.district || "N/A" }}
                    </p>
                  </div>
                </div>

                <!-- Coluna 3: Morada Detalhada -->
                <div class="space-y-4">
                  <h3
                    class="text-lg font-semibold text-gray-900 dark:text-white border-b pb-2"
                  >
                    Morada Detalhada
                  </h3>
                  <div>
                    <label
                      class="block text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                      Bairro
                    </label>
                    <p class="mt-1 text-base text-gray-900 dark:text-white">
                      {{ employee.neighborhood || "N/A" }}
                    </p>
                  </div>
                  <div>
                    <label
                      class="block text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                      Rua/Avenida
                    </label>
                    <p class="mt-1 text-base text-gray-900 dark:text-white">
                      {{ employee.street || "N/A" }}
                    </p>
                  </div>
                  <div>
                    <label
                      class="block text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                      Quarteirão
                    </label>
                    <p class="mt-1 text-base text-gray-900 dark:text-white">
                      {{ employee.block || "N/A" }}
                    </p>
                  </div>
                  <div>
                    <label
                      class="block text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                      Casa Nº
                    </label>
                    <p class="mt-1 text-base text-gray-900 dark:text-white">
                      {{ employee.house_number || "N/A" }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Formulário de edição -->
            <form v-else @submit.prevent="updateEmployee" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Coluna 1: Informações Pessoais Editáveis -->
                <div class="space-y-4">
                  <h3
                    class="text-lg font-semibold text-gray-900 dark:text-white border-b pb-2"
                  >
                    Informações Pessoais
                  </h3>
                  <div>
                    <label
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                    >
                      Nome Completo *
                    </label>
                    <input
                      v-model="form.name"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-secondary focus:ring-2 focus:ring-brand focus:border-transparent"
                    />
                    <div
                      v-if="form.errors.name"
                      class="text-sm text-red-600 dark:text-red-400 mt-1"
                    >
                      {{ form.errors.name }}
                    </div>
                  </div>

                  <div>
                    <label
                      class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"
                    >
                      Email
                    </label>
                    <p
                      class="mt-1 text-base text-gray-900 dark:text-white px-3 py-2 bg-gray-50 dark:bg-dark-secondary rounded-lg"
                    >
                      {{ employee.email }}
                    </p>
                  </div>

                  <div>
                    <label
                      class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"
                    >
                      Username
                    </label>
                    <p
                      class="mt-1 text-base text-gray-900 dark:text-white px-3 py-2 bg-gray-50 dark:bg-dark-secondary rounded-lg"
                    >
                      {{ employee.username }}
                    </p>
                  </div>

                  <div>
                    <label
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                    >
                      Telefone *
                    </label>
                    <input
                      v-model="form.phone"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-secondary focus:ring-2 focus:ring-brand focus:border-transparent"
                    />
                    <div
                      v-if="form.errors.phone"
                      class="text-sm text-red-600 dark:text-red-400 mt-1"
                    >
                      {{ form.errors.phone }}
                    </div>
                  </div>
                </div>

                <!-- Coluna 2: Informações Profissionais e Morada Editáveis -->
                <div class="space-y-4">
                  <h3
                    class="text-lg font-semibold text-gray-900 dark:text-white border-b pb-2"
                  >
                    Informações Profissionais
                  </h3>
                  <div>
                    <label
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                    >
                      Cargo *
                    </label>
                    <select
                      v-model="form.role"
                      required
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-secondary focus:ring-2 focus:ring-brand focus:border-transparent"
                    >
                      <option value="">Selecione um cargo</option>
                      <option value="gestor">Gestor</option>
                      <option value="Técnico">Técnico</option>
                    </select>
                    <div
                      v-if="form.errors.role"
                      class="text-sm text-red-600 dark:text-red-400 mt-1"
                    >
                      {{ form.errors.role }}
                    </div>
                  </div>

                  <div>
                    <label
                      class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"
                    >
                      Data de Admissão
                    </label>
                    <p
                      class="mt-1 text-base text-gray-900 dark:text-white px-3 py-2 bg-gray-50 dark:bg-dark-secondary rounded-lg"
                    >
                      {{ formatDate(employee.hire_date) }}
                    </p>
                  </div>

                  <div>
                    <label
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                    >
                      Província *
                    </label>
                    <input
                      v-model="form.province"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-secondary focus:ring-2 focus:ring-brand focus:border-transparent"
                    />
                    <div
                      v-if="form.errors.province"
                      class="text-sm text-red-600 dark:text-red-400 mt-1"
                    >
                      {{ form.errors.province }}
                    </div>
                  </div>

                  <div>
                    <label
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                    >
                      Distrito *
                    </label>
                    <input
                      v-model="form.district"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-secondary focus:ring-2 focus:ring-brand focus:border-transparent"
                    />
                    <div
                      v-if="form.errors.district"
                      class="text-sm text-red-600 dark:text-red-400 mt-1"
                    >
                      {{ form.errors.district }}
                    </div>
                  </div>
                </div>

                <!-- Coluna 3: Morada Detalhada Editável -->
                <div class="space-y-4">
                  <h3
                    class="text-lg font-semibold text-gray-900 dark:text-white border-b pb-2"
                  >
                    Morada Detalhada
                  </h3>
                  <div>
                    <label
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                    >
                      Bairro *
                    </label>
                    <input
                      v-model="form.neighborhood"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-secondary focus:ring-2 focus:ring-brand focus:border-transparent"
                    />
                    <div
                      v-if="form.errors.neighborhood"
                      class="text-sm text-red-600 dark:text-red-400 mt-1"
                    >
                      {{ form.errors.neighborhood }}
                    </div>
                  </div>

                  <div>
                    <label
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                    >
                      Rua/Avenida
                    </label>
                    <input
                      v-model="form.street"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-secondary focus:ring-2 focus:ring-brand focus:border-transparent"
                    />
                    <div
                      v-if="form.errors.street"
                      class="text-sm text-red-600 dark:text-red-400 mt-1"
                    >
                      {{ form.errors.street }}
                    </div>
                  </div>

                  <div>
                    <label
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                    >
                      Quarteirão
                    </label>
                    <input
                      v-model="form.block"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-secondary focus:ring-2 focus:ring-brand focus:border-transparent"
                    />
                    <div
                      v-if="form.errors.block"
                      class="text-sm text-red-600 dark:text-red-400 mt-1"
                    >
                      {{ form.errors.block }}
                    </div>
                  </div>

                  <div>
                    <label
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                    >
                      Casa Nº
                    </label>
                    <input
                      v-model="form.house_number"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-secondary focus:ring-2 focus:ring-brand focus:border-transparent"
                    />
                    <div
                      v-if="form.errors.house_number"
                      class="text-sm text-red-600 dark:text-red-400 mt-1"
                    >
                      {{ form.errors.house_number }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Mensagens gerais de erro -->
              <div
                v-if="form.hasErrors"
                class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"
              >
                <p class="text-red-600 dark:text-red-400 text-sm">
                  Por favor, corrija os erros no formulário.
                </p>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Estatísticas -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="glass p-4 rounded-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Casos Totais
              </p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ stats.total_cases }}
              </p>
            </div>
            <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
              <FolderIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
        </div>

        <div class="glass p-4 rounded-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Casos Pendentes
              </p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ stats.pending_cases }}
              </p>
            </div>
            <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
              <ClockIcon class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
            </div>
          </div>
        </div>

        <div class="glass p-4 rounded-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Casos Resolvidos
              </p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ stats.resolved_cases }}
              </p>
            </div>
            <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
              <CheckCircleIcon class="h-6 w-6 text-green-600 dark:text-green-400" />
            </div>
          </div>
        </div>

        <div class="glass p-4 rounded-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Taxa de Sucesso
              </p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ stats.success_rate }}%
              </p>
            </div>
            <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
              <ChartBarIcon class="h-6 w-6 text-purple-600 dark:text-purple-400" />
            </div>
          </div>
        </div>
      </div>

      <!-- Botões de ação -->
      <div
        class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700"
      >
        <button
          v-if="!isEditing"
          @click="isEditing = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2 transition-colors"
        >
          <PencilIcon class="h-5 w-5" />
          Editar Funcionário
        </button>

        <button
          v-if="!isEditing"
          @click="showDeleteModal = true"
          class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center gap-2 transition-colors"
        >
          <TrashIcon class="h-5 w-5" />
          Remover Funcionário
        </button>

        <button
          v-if="!isEditing"
          @click="showToggleStatusModal = true"
          :class="
            employee.is_available
              ? 'bg-yellow-600 hover:bg-yellow-700'
              : 'bg-green-600 hover:bg-green-700'
          "
          class="px-4 py-2 text-white rounded-lg flex items-center gap-2 transition-colors"
        >
          <ArrowsRightLeftIcon class="h-5 w-5" />
          {{ employee.is_available ? "Desativar" : "Ativar" }}
        </button>

        <!-- Botões durante edição -->
        <button
          v-if="isEditing"
          @click="cancelEditing"
          class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2 transition-colors"
        >
          <XMarkIcon class="h-5 w-5" />
          Cancelar
        </button>

        <button
          v-if="isEditing"
          @click="updateEmployee"
          :disabled="loading.update || form.processing"
          class="px-4 py-2 bg-brand text-white rounded-lg hover:bg-brand/90 flex items-center gap-2 transition-colors disabled:opacity-50"
        >
          <template v-if="loading.update">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
            <span>Processando...</span>
          </template>
          <template v-else>
            <CheckIcon class="h-5 w-5" />
            <span>{{ form.processing ? "Salvando..." : "Salvar Alterações" }}</span>
          </template>
        </button>
      </div>
    </div>

    <ConfirmationModal
      :is-open="showDeleteModal"
      :title="`Remover ${employee.name}?`"
      :message="'Esta ação não pode ser desfeita. O funcionário será removido permanentemente do sistema.'"
      confirm-text="Remover"
      confirm-variant="danger"
      :is-processing="loading.delete"
      @close="showDeleteModal = false"
      @confirm="handleDelete"
    >
      <template v-if="loading.delete" #processing>
        <div class="flex items-center justify-center gap-2 py-2">
          <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
          <span>Removendo funcionário...</span>
        </div>
      </template>
    </ConfirmationModal>

    <!-- Modal de Confirmação para Ativar/Desativar -->
    <ConfirmationModal
      :is-open="showToggleStatusModal"
      :title="`${employee.is_available ? 'Desativar' : 'Ativar'} ${employee.name}?`"
      :message="
        employee.is_available
          ? 'O funcionário não poderá receber novos casos enquanto estiver desativado.'
          : 'O funcionário voltará a receber novos casos e estar disponível para atribuições.'
      "
      :confirm-text="employee.is_available ? 'Desativar' : 'Ativar'"
      :confirm-variant="employee.is_available ? 'warning' : 'success'"
      :is-processing="loading.toggleStatus"
      @close="showToggleStatusModal = false"
      @confirm="handleToggleStatus"
    >
      <template v-if="loading.toggleStatus" #processing>
        <div class="flex items-center justify-center gap-2 py-2">
          <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
          <span>{{ employee.is_available ? "Desativando..." : "Ativando..." }}</span>
        </div>
      </template>
    </ConfirmationModal>

    <!-- Modal de Confirmação para Cancelar Edição -->
    <ConfirmationModal
      :is-open="showCancelEditModal"
      title="Descartar alterações?"
      message="Tem certeza que deseja cancelar a edição? Todas as alterações não salvas serão perdidas."
      confirm-text="Descartar"
      confirm-variant="warning"
      @close="showCancelEditModal = false"
      @confirm="cancelEditing"
    />

    <Toast ref="toastRef" />
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/ManagerLayout.vue";
import ConfirmationModal from "@/Components/Director/ConfirmationModal.vue";
import Toast from "@/Components/Toast.vue"; // Certifique-se que este caminho está correto
import {
  ArrowLeftIcon,
  UserIcon,
  PencilIcon,
  TrashIcon,
  ArrowsRightLeftIcon,
  XMarkIcon,
  CheckIcon,
  FolderIcon,
  ClockIcon,
  CheckCircleIcon,
  ChartBarIcon,
} from "@heroicons/vue/24/outline";

const toastRef = ref(null);

// Função para exibir toast
const showToast = (message, type = "success") => {
  if (toastRef.value) {
    toastRef.value.showToast(message, type);
  }
};

const props = defineProps({
  employee: {
    type: Object,
    required: true,
  },
  stats: {
    type: Object,
    default: () => ({
      total_cases: 0,
      pending_cases: 0,
      resolved_cases: 0,
      success_rate: 0,
      avg_resolution_time: 0,
    }),
  },
  monthlyPerformance: {
    type: Array,
    default: () => [],
  },
  recent_cases: {
    type: Array,
    default: () => [],
  },
});

const isEditing = ref(false);
const showDeleteModal = ref(false);
const showToggleStatusModal = ref(false);
const showCancelEditModal = ref(false);

// Estados de carregamento
const loading = reactive({
  update: false,
  delete: false,
  toggleStatus: false,
});

// Formulário de edição com todos os campos
const form = useForm({
  name: props.employee.name,
  email: props.employee.email,
  username: props.employee.username,
  phone: props.employee.phone || "",
  role: props.employee.primary_role,
  is_available: props.employee.is_available,
  province: props.employee.province || "",
  district: props.employee.district || "",
  neighborhood: props.employee.neighborhood || "",
  street: props.employee.street || "",
  block: props.employee.block || "",
  house_number: props.employee.house_number || "",
});

const startEditing = () => {
  form.name = props.employee.name;
  form.email = props.employee.email;
  form.username = props.employee.username;
  form.phone = props.employee.phone || "";
  form.role = props.employee.primary_role;
  form.is_available = props.employee.is_available;
  form.province = props.employee.province || "";
  form.district = props.employee.district || "";
  form.neighborhood = props.employee.neighborhood || "";
  form.street = props.employee.street || "";
  form.block = props.employee.block || "";
  form.house_number = props.employee.house_number || "";
  isEditing.value = true;
  showToast("Modo de edição ativado", "success");
};

const cancelEditing = () => {
  if (form.isDirty) {
    showCancelEditModal.value = true;
  } else {
    isEditing.value = false;
    form.reset();
    showToast("Edição cancelada", "success");
  }
};

const confirmCancelEditing = () => {
  isEditing.value = false;
  form.reset();
  form.clearErrors();
  showCancelEditModal.value = false;
  showToast("Edição cancelada", "success");
};

const updateEmployee = () => {
  loading.update = true;

  // Remover email e username pois não são editáveis
  const updateData = { ...form.data() };
  delete updateData.email;
  delete updateData.username;

  form.patch(route("director.team.update", props.employee.id), {
    preserveScroll: true,
    onSuccess: () => {
      isEditing.value = false;
      loading.update = false;
      showToast("Funcionário atualizado com sucesso!", "success");
      // Recarrega apenas os dados do funcionário
      router.reload({
        only: ["employee", "stats", "monthlyPerformance", "recent_cases"],
        onSuccess: () => {
          // Resetar o formulário com os novos dados
          form.name = props.employee.name;
          form.email = props.employee.email;
          form.username = props.employee.username;
          form.phone = props.employee.phone || "";
          form.role = props.employee.primary_role;
          form.is_available = props.employee.is_available;
          form.province = props.employee.province || "";
          form.district = props.employee.district || "";
          form.neighborhood = props.employee.neighborhood || "";
          form.street = props.employee.street || "";
          form.block = props.employee.block || "";
          form.house_number = props.employee.house_number || "";
        },
      });
    },
    onError: (errors) => {
      loading.update = false;
      showToast("Erro ao atualizar funcionário. Verifique os campos.", "error");
    },
    onFinish: () => {
      loading.update = false;
    },
  });
};

const handleToggleStatus = () => {
  loading.toggleStatus = true;

  router.patch(route("director.team.toggle-status", props.employee.id), {
    preserveScroll: true,
    onSuccess: () => {
      loading.toggleStatus = false;
      showToggleStatusModal.value = false;
      const status = props.employee.is_available ? "desativado" : "ativado";
      showToast(`Funcionário ${status} com sucesso!`, "success");
      // Recarrega a página para atualizar o status
      router.reload();
    },
    onError: (errors) => {
      loading.toggleStatus = false;
      showToggleStatusModal.value = false;
      showToast("Erro ao alterar status do funcionário.", "error");
    },
    onFinish: () => {
      loading.toggleStatus = false;
    },
  });
};

const handleDelete = () => {
  loading.delete = true;

  router.delete(route("director.team.destroy", props.employee.id), {
    preserveScroll: true,
    onSuccess: () => {
      loading.delete = false;
      showDeleteModal.value = false;
      showToast("Funcionário removido com sucesso!", "success");
      // Aguarda um pouco para mostrar o toast e depois redireciona
      setTimeout(() => {
        router.visit(route("director.managers.index"));
      }, 1500);
    },
    onError: (errors) => {
      loading.delete = false;
      showDeleteModal.value = false;
      const errorMessage = errors?.error || "Erro ao remover funcionário.";
      showToast(errorMessage, "error");
    },
    onFinish: () => {
      loading.delete = false;
    },
  });
};

const getRoleDisplayName = (role) => {
  if (!role) return "Sem cargo";

  switch (role.toLowerCase()) {
    case "gestor":
      return "Gestor";
    case "técnico":
      return "Técnico";
    case "director":
      return "Director";
    default:
      return role.charAt(0).toUpperCase() + role.slice(1);
  }
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return "Data inválida";
  return date.toLocaleDateString("pt-PT");
};
</script>
