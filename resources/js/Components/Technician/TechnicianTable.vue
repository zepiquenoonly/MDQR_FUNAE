<template>
  <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <!-- Tabela -->
      <table class="w-full min-w-[800px]" v-if="hasData">
        <thead>
          <tr class="border-b-2 border-gray-200 dark:border-gray-700 text-left">
            <th
              class="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
            >
              #
            </th>
            <th
              class="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
            >
              Técnico
            </th>
            <th
              class="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
            >
              Contacto
            </th>
            <th
              class="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
            >
              Status
            </th>
            <th
              class="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
            >
              Estatísticas
            </th>
            <th
              class="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
            >
              Performance
            </th>
            <th
              class="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
            >
              Ações
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr
            v-for="(technician, index) in technicianList"
            :key="technician.id"
            class="hover:bg-gray-50 dark:hover:bg-dark-accent transition-colors"
          >
            <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">
              {{ index + 1 }}
            </td>
            <td class="py-3 px-4">
              <div class="flex items-center">
                <div
                  class="flex-shrink-0 h-10 w-10 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center"
                >
                  <span class="text-gray-600 dark:text-gray-300 font-medium">
                    {{ getInitials(technician.name) }}
                  </span>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ technician.name }}
                  </div>
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    @{{ technician.username }}
                  </div>
                </div>
              </div>
            </td>
            <td class="py-3 px-4">
              <div class="text-sm text-gray-900 dark:text-white">
                {{ technician.email }}
              </div>
              <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ technician.phone || "N/A" }}
              </div>
            </td>
            <td class="py-3 px-4">
              <span :class="statusClass(technician.is_available)">
                {{ technician.is_available ? "Disponível" : "Indisponível" }}
              </span>
            </td>
            <td class="py-3 px-4">
              <div class="grid grid-cols-2 gap-1">
                <div class="text-center">
                  <div class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                    {{ technician.tasks_assigned || 0 }}
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Atribuídas</div>
                </div>
                <div class="text-center">
                  <div class="text-lg font-semibold text-green-600 dark:text-green-400">
                    {{ technician.tasks_completed || 0 }}
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Concluídas</div>
                </div>
              </div>
            </td>
            <td class="py-3 px-4">
              <div class="flex items-center">
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                  <div
                    :class="performanceClass(technician.performance_rate)"
                    :style="{
                      width: `${Math.min(technician.performance_rate || 0, 100)}%`,
                    }"
                  ></div>
                </div>
                <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300"
                  >{{ technician.performance_rate || 0 }}%</span
                >
              </div>
            </td>
            <td class="py-3 px-4">
              <div class="flex flex-col space-y-2">
                <!-- Use o componente Link do Inertia para navegação -->
                <Link
                  :href="`/technicians/${technician.id}`"
                  class="w-32 inline-flex items-center px-3 py-1.5 bg-brand hover:bg-orange-600 text-white rounded text-xs font-medium transition-colors"
                >
                  <EyeIcon class="w-4 h-4 mr-1" /> Ver Detalhes
                </Link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-if="!hasData && !loading" class="text-center py-12">
        <UserGroupIcon class="w-16 h-16 text-gray-400 dark:text-gray-600 mx-auto mb-4" />
        <p class="text-gray-600 dark:text-gray-400 text-lg font-medium mb-2">
          Nenhum técnico encontrado
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div
          class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"
        ></div>
        <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm">
          A carregar técnicos...
        </p>
      </div>
    </div>

    <!-- Pagination - use URLs diretas se route() não funcionar -->
    <div
      v-if="hasData && pagination"
      class="px-4 py-3 border-t border-gray-200 dark:border-gray-700"
    >
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700 dark:text-gray-300">
          Mostrando {{ technicians.from || 1 }} a
          {{ technicians.to || technicianList.length }} de
          {{ technicians.total || technicianList.length }} técnicos
        </div>
        <div class="flex space-x-2">
          <Link
            v-if="technicians.prev_page_url"
            :href="technicians.prev_page_url"
            class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
            preserve-state
          >
            Anterior
          </Link>
          <Link
            v-if="technicians.next_page_url"
            :href="technicians.next_page_url"
            class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
            preserve-state
          >
            Próxima
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import { EyeIcon, UserGroupIcon } from "@heroicons/vue/24/outline";
import { computed } from "vue";

const props = defineProps({
  technicians: {
    type: [Object, Array],
    default: () => ({}),
  },
  loading: {
    type: Boolean,
    default: false,
  },
  canEdit: {
    type: Boolean,
    default: false,
  },
});

console.log("TechnicianTable - technicians prop:", props.technicians);

const technicianList = computed(() => {
  // Se já for um array, retorna diretamente
  if (Array.isArray(props.technicians)) {
    return props.technicians;
  }
  // Se for um objeto paginado, retorna a propriedade data
  if (
    props.technicians &&
    props.technicians.data &&
    Array.isArray(props.technicians.data)
  ) {
    return props.technicians.data;
  }
  // Se for um objeto vazio ou null, retorna array vazio
  return [];
});

const hasData = computed(() => {
  return technicianList.value.length > 0;
});

const pagination = computed(() => {
  return (
    props.technicians &&
    typeof props.technicians === "object" &&
    (props.technicians.current_page !== undefined ||
      props.technicians.total !== undefined)
  );
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

const statusClass = (isAvailable) => {
  return isAvailable
    ? "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300"
    : "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300";
};

const performanceClass = (rate) => {
  const performance = rate || 0;
  return [
    "h-2.5 rounded-full",
    performance >= 80
      ? "bg-green-500"
      : performance >= 60
      ? "bg-yellow-500"
      : "bg-red-500",
  ];
};
</script>
