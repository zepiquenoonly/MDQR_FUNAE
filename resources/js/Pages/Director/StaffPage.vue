<template>
  <AppLayout title="Gestão de Funcionários">
    <div class="max-w-full mx-auto">
      <!-- Cabeçalho -->
      <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Gestão de Funcionários
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Adicione, remova e atualize funcionários do departamento
          </p>
        </div>
        <button
          @click="openCreateModal"
          class="px-4 py-2 bg-brand text-white rounded-lg hover:bg-brand/90 flex items-center gap-2"
        >
          <UserPlusIcon class="h-5 w-5" />
          Novo Funcionário
        </button>
      </div>

      <!-- Estatísticas -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="glass p-4 rounded-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Total Funcionários
              </p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ stats.total }}
              </p>
            </div>
            <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
              <UsersIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
        </div>

        <div class="glass p-4 rounded-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ativos</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ stats.active }}
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
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Inativos</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ stats.inactive }}
              </p>
            </div>
            <div class="p-3 bg-gray-100 dark:bg-gray-900/30 rounded-lg">
              <UserMinusIcon class="h-6 w-6 text-gray-600 dark:text-gray-400" />
            </div>
          </div>
        </div>
      </div>

      <!-- Filtros e Busca -->
      <div class="glass p-4 mb-6 rounded-xl">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="flex-1">
            <div class="relative">
              <MagnifyingGlassIcon
                class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400"
              />
              <input
                v-model="search"
                type="text"
                placeholder="Buscar funcionários..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-secondary focus:ring-2 focus:ring-brand focus:border-transparent"
              />
            </div>
          </div>
          <div class="flex gap-2">
            <select
              v-model="filterStatus"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-dark-secondary focus:ring-2 focus:ring-brand focus:border-transparent"
            >
              <option value="">Todos Status</option>
              <option value="active">Ativo</option>
              <option value="inactive">Inativo</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Tabela de Funcionários -->
      <div class="glass rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-dark-secondary">
              <tr>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                >
                  Funcionário
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                >
                  Email
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                >
                  Cargo
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                >
                  Status
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                >
                  Data de Admissão
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                >
                  Acção
                </th>
              </tr>
            </thead>
            <tbody
              class="bg-white dark:bg-dark-primary divide-y divide-gray-200 dark:divide-gray-700"
            >
              <tr
                v-for="employee in filteredEmployees"
                :key="employee.id"
                class="hover:bg-gray-50 dark:hover:bg-dark-secondary transition-colors"
              >
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="h-10 w-10 flex-shrink-0">
                      <img
                        v-if="employee.photo"
                        :src="employee.photo"
                        :alt="employee.name"
                        class="h-10 w-10 rounded-full object-cover"
                      />
                      <div
                        v-else
                        class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center"
                      >
                        <UserIcon class="h-6 w-6 text-gray-500 dark:text-gray-400" />
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ employee.name }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">
                    {{ employee.email }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400"
                  >
                    {{ getRoleDisplayName(employee.position) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="
                      employee.status === 'active'
                        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                        : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'
                    "
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  >
                    {{ employee.status === "active" ? "Ativo" : "Inativo" }}
                  </span>
                </td>
                <td
                  class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                >
                  {{ formatDate(employee.hire_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    @click="viewDetails(employee)"
                    class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 flex items-center gap-1 px-3 py-1 border border-green-300 dark:border-green-700 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors"
                  >
                    <EyeIcon class="h-4 w-4" />
                    <span>Ver Detalhes</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Estado vazio -->
        <div v-if="filteredEmployees.length === 0" class="text-center py-12">
          <UsersIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
            Nenhum funcionário encontrado
          </h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{
              search
                ? "Tente ajustar os termos da busca."
                : "Comece adicionando um novo funcionário."
            }}
          </p>
          <div class="mt-6">
            <button
              @click="openCreateModal"
              class="px-4 py-2 bg-brand text-white rounded-lg hover:bg-brand/90 inline-flex items-center gap-2"
            >
              <UserPlusIcon class="h-5 w-5" />
              Adicionar Funcionário
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para Criar/Editar Funcionário -->
    <EmployeeModal
      :is-open="showModal"
      :employee="selectedEmployee"
      :departments="departments"
      @close="closeModal"
      @save="handleSave"
    />

    <!-- Modal de Confirmação para Apagar -->
    <ConfirmationModal
      :is-open="showDeleteModal"
      :title="`Remover ${employeeToDelete?.name}?`"
      message="Esta ação não pode ser desfeita. O funcionário será removido permanentemente do sistema."
      confirm-text="Remover"
      confirm-variant="danger"
      @close="showDeleteModal = false"
      @confirm="handleDelete"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/UnifiedLayout.vue";
import EmployeeModal from "@/Components/Director/EmployeeModal.vue";
import ConfirmationModal from "@/Components/Director/ConfirmationModal.vue";
import { useAuth, usePermissions } from "@/Composables/useAuth";
import {
  UserPlusIcon,
  UsersIcon,
  CheckCircleIcon,
  UserMinusIcon,
  MagnifyingGlassIcon,
  UserIcon,
  EyeIcon,
} from "@heroicons/vue/24/outline";

const { role, isDirector, isAdmin, checkRole } = useAuth();
const { can } = usePermissions();

const props = defineProps({
  employees: {
    type: Object,
    default: () => ({
      data: [],
      links: [],
      meta: {},
    }),
  },
  departments: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Object,
    default: () => ({}),
  },
});

const canAddEmployee = computed(() => {
  return can("canManageUsers") || checkRole("director") || checkRole("admin");
});

const search = ref("");
const filterDepartment = ref("");
const filterStatus = ref("");
const showModal = ref(false);
const showDeleteModal = ref(false);
const selectedEmployee = ref(null);
const employeeToDelete = ref(null);

const filteredEmployees = computed(() => {
  // Use employees.data em vez de employees diretamente
  let filtered = [...(props.employees.data || [])];

  // Aplicar filtro de busca
  if (search.value) {
    const searchTerm = search.value.toLowerCase();
    filtered = filtered.filter(
      (employee) =>
        employee.name?.toLowerCase().includes(searchTerm) ||
        employee.email?.toLowerCase().includes(searchTerm) ||
        employee.employee_id?.toLowerCase().includes(searchTerm) ||
        employee.position?.toLowerCase().includes(searchTerm)
    );
  }

  // Aplicar filtro de departamento
  if (filterDepartment.value) {
    filtered = filtered.filter(
      (employee) => employee.department_id === parseInt(filterDepartment.value)
    );
  }

  // Aplicar filtro de status
  if (filterStatus.value) {
    filtered = filtered.filter((employee) => employee.status === filterStatus.value);
  }

  return filtered;
});

const resetFilters = () => {
  search.value = "";
  filterDepartment.value = "";
  filterStatus.value = "";
};

// Funções temporárias até criar os modais
const openCreateModal = () => {
  alert("Funcionalidade em desenvolvimento");
};

const openEditModal = (employee) => {
  alert(`Editar funcionário: ${employee.name}`);
};

const toggleStatus = (employee) => {
  if (
    confirm(
      `Deseja ${employee.status === "active" ? "desativar" : "ativar"} ${employee.name}?`
    )
  ) {
    router.patch(route("director.team.toggle-status", employee.id), {
      onSuccess: () => window.location.reload(),
    });
  }
};

const confirmDelete = (employee) => {
  if (confirm(`Tem certeza que deseja remover ${employee.name} permanentemente?`)) {
    router.delete(route("director.team.destroy", employee.id), {
      onSuccess: () => window.location.reload(),
    });
  }
};

const getRoleBadgeClass = (role) => {
  switch (role?.toLowerCase()) {
    case "gestor":
      return "bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400";
    case "técnico":
      return "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400";
    case "director":
      return "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400";
    default:
      return "bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400";
  }
};

// Método para exibir nome formatado do cargo
const getRoleDisplayName = (role) => {
  if (!role) return "Sem Cargo";

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

const viewDetails = (employee) => {
  router.visit(route("director.team.show", employee.id));
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  return new Date(dateString).toLocaleDateString("pt-PT");
};
</script>
