<template>
  <Modal :is-open="isOpen" @close="$emit('close')">
    <template #header>
      <h3 class="text-lg font-medium text-gray-900 dark:text-white">
        {{ employee ? 'Editar Funcionário' : 'Novo Funcionário' }}
      </h3>
    </template>

    <template #content>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Nome -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Nome Completo *
          </label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-dark-secondary dark:text-white"
          />
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Email *
          </label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-dark-secondary dark:text-white"
          />
        </div>

        <!-- Username -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Nome de Usuário *
          </label>
          <input
            v-model="form.username"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-dark-secondary dark:text-white"
          />
        </div>

        <!-- Telefone -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Telefone *
          </label>
          <input
            v-model="form.phone"
            type="tel"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-dark-secondary dark:text-white"
          />
        </div>

        <!-- Cargo/Role -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Cargo *
          </label>
          <select
            v-model="form.role"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-dark-secondary dark:text-white"
          >
            <option value="">Selecione um cargo</option>
            <option value="gestor">Gestor</option>
            <option value="Técnico">Técnico</option>
          </select>
        </div>

        <!-- Departamento -->
        <div v-if="departments && departments.length > 0">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Departamento
          </label>
          <select
            v-model="form.department_id"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-dark-secondary dark:text-white"
          >
            <option value="">Selecione um departamento</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>

        <!-- Capacidade de Trabalho (apenas para técnicos) -->
        <div v-if="form.role === 'Técnico'">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Capacidade de Trabalho (1-50)
          </label>
          <input
            v-model="form.workload_capacity"
            type="number"
            min="1"
            max="50"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-dark-secondary dark:text-white"
          />
        </div>

        <!-- Senha (apenas para novo funcionário) -->
        <div v-if="!employee">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Senha *
          </label>
          <input
            v-model="form.password"
            type="password"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-dark-secondary dark:text-white"
          />
        </div>

        <div v-if="!employee">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Confirmar Senha *
          </label>
          <input
            v-model="form.password_confirmation"
            type="password"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-dark-secondary dark:text-white"
          />
        </div>
      </form>
    </template>

    <template #footer>
      <div class="flex justify-end space-x-2">
        <button
          type="button"
          @click="$emit('close')"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Cancelar
        </button>
        <button
          type="button"
          @click="handleSubmit"
          class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          {{ employee ? 'Atualizar' : 'Criar' }}
        </button>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  isOpen: Boolean,
  employee: Object,
  departments: Array,
});

const emit = defineEmits(['close', 'save']);

const form = ref({
  name: '',
  email: '',
  username: '',
  phone: '',
  role: 'Técnico',
  department_id: '',
  workload_capacity: 20,
  password: '',
  password_confirmation: '',
});

// Preencher form quando employee for passado
watch(() => props.employee, (newEmployee) => {
  if (newEmployee) {
    form.value = {
      name: newEmployee.name,
      email: newEmployee.email,
      username: newEmployee.username || newEmployee.employee_id,
      phone: newEmployee.phone,
      role: newEmployee.position || newEmployee.primary_role,
      department_id: newEmployee.department_id,
      workload_capacity: newEmployee.workload_capacity || 20,
      password: '',
      password_confirmation: '',
    };
  } else {
    // Resetar form
    form.value = {
      name: '',
      email: '',
      username: '',
      phone: '',
      role: 'Técnico',
      department_id: '',
      workload_capacity: 20,
      password: '',
      password_confirmation: '',
    };
  }
}, { immediate: true });

const handleSubmit = () => {
  emit('save', form.value);
};
</script>