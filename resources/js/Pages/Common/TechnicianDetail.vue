<template>
  <UnifiedLayout :user="user" :role="role">
    <div class="min-h-screen bg-gray-50 dark:bg-dark-primary p-4 sm:p-6">
      <!-- Breadcrumb - use URLs diretas -->
      <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm">
          <li>
            <Link
              href="/gestor/dashboard"
              class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
            >
              Dashboard
            </Link>
          </li>
          <li class="text-gray-400 dark:text-gray-500">/</li>
          <li>
            <Link
              href="/gestor/technicians"
              class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
            >
              Técnicos
            </Link>
          </li>
          <li class="text-gray-400 dark:text-gray-500">/</li>
          <li class="text-gray-700 dark:text-gray-300 font-semibold">
            {{ technician.name }}
          </li>
        </ol>
      </nav>

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
          <div class="flex-shrink-0">
            <div
              class="h-16 w-16 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center"
            >
              <span class="text-blue-600 dark:text-blue-400 text-2xl font-bold">
                {{ getInitials(technician.name) }}
              </span>
            </div>
          </div>
          <div>
            <h1
              class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-dark-text-primary"
            >
              {{ technician.name }}
            </h1>
            <div class="flex items-center gap-3 mt-2">
              <span
                :class="[
                  'px-3 py-1 text-sm font-semibold rounded-full',
                  technician.is_available
                    ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300'
                    : 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300',
                ]"
              >
                {{ technician.is_available ? "Disponível" : "Indisponível" }}
              </span>
              <span class="text-gray-500 dark:text-gray-400 text-sm">
                Técnico desde {{ formatDate(technician.created_at) }}
              </span>
            </div>
          </div>
        </div>

        <div class="flex gap-2">
          <!--<button
            v-if="canEdit"
            @click="toggleStatus"
            :class="[
              'inline-flex items-center px-4 py-2 rounded-lg transition-colors font-medium',
              technician.is_available
                ? 'bg-yellow-500 hover:bg-yellow-600 text-white'
                : 'bg-green-500 hover:bg-green-600 text-white',
            ]"
          >
            <ArrowPathIcon class="w-5 h-5 mr-2" />
            {{ technician.is_available ? "Desativar" : "Ativar" }}
          </button>-->
          <Link
            href="/gestor/technicians"
            class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-300 rounded-lg transition-colors font-medium"
          >
            <ArrowLeftIcon class="w-5 h-5 mr-2" />
            Voltar
          </Link>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total de Tarefas -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900/20 rounded-lg p-3">
              <ClipboardDocumentListIcon
                class="h-6 w-6 text-blue-600 dark:text-blue-400"
              />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Total de Tarefas
              </p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ stats.total_assigned }}
              </p>
            </div>
          </div>
        </div>

        <!-- Concluídas -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-100 dark:bg-green-900/20 rounded-lg p-3">
              <CheckCircleIcon class="h-6 w-6 text-green-600 dark:text-green-400" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Concluídas
              </p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ stats.completed }}
                <span class="text-sm text-green-600 dark:text-green-400 ml-1">
                  ({{ stats.completion_rate }}%)
                </span>
              </p>
            </div>
          </div>
        </div>

        <!-- Pendentes -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg p-3">
              <ClockIcon class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Pendentes
              </p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ stats.pending }}
              </p>
            </div>
          </div>
        </div>

        <!-- Tempo Médio -->
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-purple-100 dark:bg-purple-900/20 rounded-lg p-3">
              <ChartBarIcon class="h-6 w-6 text-purple-600 dark:text-purple-400" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Tempo Médio
              </p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ stats.average_resolution_time }}h
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Informações Pessoais -->
          <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
            <h2
              class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
            >
              Informações Pessoais
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Nome Completo
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ technician.name }}
                </p>
              </div>
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Username
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  @{{ technician.username }}
                </p>
              </div>
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Email
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ technician.email }}
                </p>
              </div>
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Telefone
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ technician.phone || "N/A" }}
                </p>
              </div>
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Data de Registo
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ formatDate(technician.created_at) }}
                </p>
              </div>
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Última Actualização
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ formatDate(technician.updated_at) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Localização -->
          <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
            <h2
              class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
            >
              Localização
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Província
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ technician.province }}
                </p>
              </div>
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Distrito
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ technician.district }}
                </p>
              </div>
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Bairro
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ technician.neighborhood }}
                </p>
              </div>
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Rua/Avenida
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ technician.street || "N/A" }}
                </p>
              </div>
            </div>
          </div>

          <!-- Tarefas Recentes -->
          <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
            <h2
              class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
            >
              Tarefas Recentes
            </h2>
            <div class="space-y-3">
              <div
                v-for="task in recent_tasks"
                :key="task.id"
                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
              >
                <div class="flex justify-between items-start">
                  <div class="flex-1">
                    <h3
                      class="font-medium text-gray-800 dark:text-dark-text-primary mb-1"
                    >
                      {{ task.title }}
                    </h3>
                    <div class="flex flex-wrap gap-2 mb-2">
                      <span
                        :class="[
                          'px-2 py-1 text-xs font-semibold rounded-full',
                          getStatusClass(task.status),
                        ]"
                      >
                        {{ getStatusLabel(task.status) }}
                      </span>
                      <span
                        :class="[
                          'px-2 py-1 text-xs font-semibold rounded-full',
                          getPriorityClass(task.priority),
                        ]"
                      >
                        {{ task.priority || "Normal" }}
                      </span>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                      Criada em {{ formatDateTime(task.created_at) }}
                      <span v-if="task.completed_at" class="ml-2">
                        • Concluída em {{ formatDateTime(task.completed_at) }}
                      </span>
                    </div>
                    <div
                      v-if="task.user"
                      class="text-sm text-gray-500 dark:text-gray-500 mt-1"
                    >
                      Por: {{ task.user.name }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="recent_tasks.length === 0" class="text-center py-8">
              <div
                class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 mb-3"
              >
                <ClipboardDocumentListIcon
                  class="w-6 h-6 text-gray-400 dark:text-gray-500"
                />
              </div>
              <p class="text-gray-500 dark:text-gray-400">Nenhuma tarefa recente</p>
            </div>
          </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
          <!-- Performance -->
          <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
            <h2
              class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
            >
              Performance
            </h2>
            <div class="space-y-4">
              <!-- Taxa de Conclusão -->
              <div>
                <div class="flex justify-between items-center mb-1">
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Taxa de Conclusão
                  </span>
                  <span class="text-sm font-bold text-gray-900 dark:text-white">
                    {{ stats.completion_rate }}%
                  </span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                  <div
                    :class="[
                      'h-2.5 rounded-full',
                      stats.completion_rate >= 80
                        ? 'bg-green-500'
                        : stats.completion_rate >= 60
                        ? 'bg-yellow-500'
                        : 'bg-red-500',
                    ]"
                    :style="{ width: `${Math.min(stats.completion_rate, 100)}%` }"
                  ></div>
                </div>
              </div>

              <!-- Tarefas por Status -->
              <div>
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Distribuição de Tarefas
                </h3>
                <div class="space-y-2">
                  <div v-for="item in tasks_by_status" :key="item.status">
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600 dark:text-gray-400">{{
                        item.status
                      }}</span>
                      <span class="font-medium text-gray-900 dark:text-white">{{
                        item.count
                      }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                      <div
                        :class="[item.color || 'bg-gray-500', 'h-1.5 rounded-full']"
                        :style="{
                          width:
                            stats.total_assigned > 0
                              ? `${(item.count / stats.total_assigned) * 100}%`
                              : '0%',
                        }"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Performance Mensal -->
          <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
            <h2
              class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
            >
              Performance Mensal ({{ new Date().getFullYear() }})
            </h2>
            <div class="space-y-3">
              <div
                v-for="month in performance_by_month"
                :key="month.month"
                class="flex items-center"
              >
                <span class="w-16 text-sm text-gray-600 dark:text-gray-400">{{
                  month.month
                }}</span>
                <div class="flex-1 ml-2">
                  <div class="flex justify-between text-xs mb-1">
                    <span>{{ month.completed }}/{{ month.total }} tarefas</span>
                    <span class="font-medium">{{ month.rate }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                    <div
                      :class="[
                        'h-1.5 rounded-full',
                        month.rate >= 80
                          ? 'bg-green-500'
                          : month.rate >= 60
                          ? 'bg-yellow-500'
                          : 'bg-red-500',
                      ]"
                      :style="{ width: `${Math.min(month.rate, 100)}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tarefas por Prioridade -->
          <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
            <h2
              class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
            >
              Tarefas por Prioridade
            </h2>
            <div class="space-y-3">
              <div
                v-for="item in tasks_by_priority"
                :key="item.priority"
                class="flex items-center justify-between"
              >
                <div class="flex items-center">
                  <div
                    :class="[
                      'w-3 h-3 rounded-full mr-2',
                      item.priority === 'Alta'
                        ? 'bg-red-500'
                        : item.priority === 'Média'
                        ? 'bg-yellow-500'
                        : item.priority === 'Baixa'
                        ? 'bg-green-500'
                        : 'bg-gray-400',
                    ]"
                  ></div>
                  <span class="text-sm text-gray-700 dark:text-gray-300">{{
                    item.priority
                  }}</span>
                </div>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                  item.count
                }}</span>
              </div>
            </div>
          </div>

          <!-- Estatísticas Detalhadas -->
          <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
            <h2
              class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
            >
              Estatísticas Detalhadas
            </h2>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400"
                  >Total Atribuído</span
                >
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                  stats.total_assigned
                }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Em Progresso</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                  stats.in_progress
                }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Canceladas</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                  stats.cancelled
                }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400"
                  >Tempo Médio Resolução</span
                >
                <span class="text-sm font-medium text-gray-900 dark:text-white"
                  >{{ stats.average_resolution_time }}h</span
                >
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </UnifiedLayout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import UnifiedLayout from "@/Layouts/UnifiedLayout.vue";
import {
  ArrowLeftIcon,
  ArrowPathIcon,
  ClipboardDocumentListIcon,
  CheckCircleIcon,
  ClockIcon,
  ChartBarIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  user: Object,
  role: String,
  technician: Object,
  stats: Object,
  recent_tasks: Array,
  performance_by_month: Array,
  tasks_by_status: Array,
  tasks_by_priority: Array,
  resolution_by_month: Array,
  canEdit: Boolean,
});

const getInitials = (name) => {
  if (!name) return "NA";
  return name
    .split(" ")
    .map((word) => word[0])
    .join("")
    .toUpperCase()
    .slice(0, 2);
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";

  if (dateString.includes("/")) return dateString;

  try {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;

    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();

    return `${day}/${month}/${year}`;
  } catch (error) {
    return dateString;
  }
};

const formatDateTime = (dateString) => {
  if (!dateString) return "N/A";

  try {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;

    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, "0");
    const minutes = String(date.getMinutes()).padStart(2, "0");

    return `${day}/${month}/${year} ${hours}:${minutes}`;
  } catch (error) {
    return dateString;
  }
};

const getStatusClass = (status) => {
  const classes = {
    completed: "bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300",
    resolved: "bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300",
    in_progress: "bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300",
    assigned: "bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300",
    submitted: "bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-300",
    pending_approval:
      "bg-purple-100 text-purple-700 dark:bg-purple-900/20 dark:text-purple-300",
    rejected: "bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300",
    cancelled: "bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300",
  };
  return (
    classes[status] || "bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-300"
  );
};

const getStatusLabel = (status) => {
  const labels = {
    completed: "Concluída",
    resolved: "Resolvida",
    in_progress: "Em Progresso",
    assigned: "Atribuída",
    submitted: "Submetida",
    pending_approval: "Aguardando Aprovação",
    rejected: "Rejeitada",
    cancelled: "Cancelada",
  };
  return labels[status] || status;
};

const getPriorityClass = (priority) => {
  const classes = {
    Alta: "bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300",
    Média: "bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300",
    Baixa: "bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300",
  };
  return (
    classes[priority] ||
    "bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-300"
  );
};

const toggleStatus = async () => {
  const newStatus = !props.technician.is_available;
  const action = newStatus ? "ativar" : "desativar";

  if (!confirm(`Tem certeza que deseja ${action} este técnico?`)) {
    return;
  }

  try {
    // URL direta para a API
    const response = await fetch(`/gestor/technicians/${props.technician.id}/status`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document
          .querySelector('meta[name="csrf-token"]')
          .getAttribute("content"),
      },
      body: JSON.stringify({
        is_available: newStatus,
      }),
    });

    if (response.ok) {
      // Recarrega a página para atualizar os dados
      window.location.reload();
    } else {
      console.error("Erro ao atualizar status:", response.statusText);
    }
  } catch (error) {
    console.error("Erro ao atualizar status:", error);
  }
};
</script>
