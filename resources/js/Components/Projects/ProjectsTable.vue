<template>
  <div class="table-scroll-container">
    <!-- TABELA RESPONSIVA MELHORADA -->
    <div class="overflow-x-auto">
      <table class="w-full min-w-[600px] sm:min-w-[800px]">
        <thead>
          <tr class="border-b-2 border-gray-200 dark:border-gray-700 text-left">
            <th
              class="py-2 px-1 sm:px-2 md:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-8 sm:w-12"
            >
              #
            </th>
            <th
              class="py-2 px-1 sm:px-2 md:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[100px] sm:min-w-[120px]"
            >
              Responsável
            </th>
            <th
              class="py-2 px-1 sm:px-2 md:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[120px] sm:min-w-[150px]"
            >
              Nome do Projecto
            </th>
            <th
              class="py-2 px-1 sm:px-2 md:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[80px] sm:min-w-[100px]"
            >
              Bairro
            </th>
            <th
              class="py-2 px-1 sm:px-2 md:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16 sm:w-24"
            >
              Estado
            </th>
            <th
              class="py-2 px-1 sm:px-2 md:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12 sm:w-20 text-center"
            >
              Recl.
            </th>
            <th
              class="py-2 px-1 sm:px-2 md:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12 sm:w-20 text-center"
            >
              Sug.
            </th>
            <th
              class="py-2 px-1 sm:px-2 md:px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-20 sm:w-28"
            >
              Ações
            </th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="(project, index) in projects"
            :key="project.id"
            class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-dark-accent transition-colors"
          >
            <td
              class="py-2 px-1 sm:px-2 md:px-4 text-xs sm:text-sm text-gray-800 dark:text-dark-text-primary font-medium"
            >
              {{ index + 1 }}
            </td>

            <td
              class="py-2 px-1 sm:px-2 md:px-4 text-xs sm:text-sm font-semibold text-gray-800 dark:text-dark-text-primary"
            >
              <div class="flex items-center gap-1 sm:gap-2 min-w-0">
                <img
                  :src="project.image_url || '/images/default-project.png'"
                  class="w-6 h-6 sm:w-8 sm:h-8 md:w-10 md:h-10 rounded object-cover flex-shrink-0"
                />
                <span class="truncate text-xs sm:text-sm">{{
                  project.finance?.responsavel || "N/A"
                }}</span>
              </div>
            </td>

            <td
              class="py-2 px-1 sm:px-2 md:px-4 text-xs sm:text-sm text-gray-800 dark:text-dark-text-primary"
            >
              <span class="truncate-2-lines block leading-tight text-xs sm:text-sm">{{
                project.name
              }}</span>
            </td>

            <td
              class="py-2 px-1 sm:px-2 md:px-4 text-xs sm:text-sm text-gray-600 dark:text-gray-400"
            >
              <span class="truncate block text-xs sm:text-sm">{{ project.bairro }}</span>
            </td>

            <td class="py-2 px-1 sm:px-2 md:px-4">
              <span
                :class="[
                  'px-1.5 py-0.5 sm:px-2 sm:py-1 text-xs font-semibold rounded-full whitespace-nowrap',
                  getStatusClass(project.category),
                ]"
              >
                {{ getStatusLabel(project.category) }}
              </span>
            </td>

            <td
              class="py-2 px-1 sm:px-2 md:px-4 text-center text-xs sm:text-sm font-bold text-gray-700 dark:text-dark-text-primary"
            >
              0
            </td>

            <td
              class="py-2 px-1 sm:px-2 md:px-4 text-center text-xs sm:text-sm font-bold text-gray-700 dark:text-dark-text-primary"
            >
              0
            </td>

            <td class="py-2 px-1 sm:px-2 md:px-4">
              <Link
                :href="`/manager/projects/${project.id}`"
                class="inline-block bg-brand hover:bg-orange-600 text-white px-2 py-1 sm:px-3 sm:py-1.5 rounded text-xs font-semibold flex items-center gap-1 w-full justify-center whitespace-nowrap transition-colors"
              >
                <EyeIcon class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                <span class="hidden xs:inline">Detalhes</span>
                <span class="xs:hidden">Ver</span>
              </Link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-6 sm:py-8">
      <div
        class="animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-brand mx-auto"
      ></div>
      <p class="text-gray-600 dark:text-gray-400 mt-2 text-xs sm:text-sm">
        A carregar projectos...
      </p>
    </div>

    <!-- Empty State -->
    <div v-else-if="projects.length === 0 && !loading" class="text-center py-6 sm:py-8">
      <FolderIcon
        class="w-8 h-8 sm:w-12 sm:h-12 text-gray-400 dark:text-gray-600 mx-auto mb-2 sm:mb-3"
      />
      <p class="text-gray-600 dark:text-gray-400 text-xs sm:text-sm">
        Nenhum projecto encontrado.
      </p>
    </div>

    <!-- No Results State -->
    <div
      v-else-if="projects.length === 0 && search && !loading"
      class="text-center py-6 sm:py-8"
    >
      <MagnifyingGlassIcon
        class="w-8 h-8 sm:w-12 sm:h-12 text-gray-400 dark:text-gray-600 mx-auto mb-2 sm:mb-3"
      />
      <p class="text-gray-600 dark:text-gray-400 text-xs sm:text-sm">
        Nenhum projecto encontrado para "{{ search }}"
      </p>
    </div>
  </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import { EyeIcon, FolderIcon, MagnifyingGlassIcon } from "@heroicons/vue/24/outline";

const emit = defineEmits(["view-details"]);

const props = defineProps({
  projects: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  search: {
    type: String,
    default: "",
  },
});

// Badge de estado
const getStatusClass = (category) => {
  switch (category) {
    case "finalizados":
      return "bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300";
    case "andamento":
      return "bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300";
    case "parados":
      return "bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300";
    default:
      return "bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300";
  }
};

const getStatusLabel = (category) => {
  const labels = {
    finalizados: "Finalizado",
    andamento: "Em Andamento",
    parados: "Parado",
  };
  return labels[category] || category;
};
</script>

<style scoped>
/* Container de rolagem para a tabela */
.table-scroll-container {
  max-height: calc(100vh - 280px);
  overflow-y: auto;
  overflow-x: auto;
}

/* Scrollbar personalizada */
.table-scroll-container::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.table-scroll-container::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.table-scroll-container::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.table-scroll-container::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Dark mode para scrollbar */
.dark .table-scroll-container::-webkit-scrollbar-track {
  background: #374151;
}

.dark .table-scroll-container::-webkit-scrollbar-thumb {
  background: #6b7280;
}

.dark .table-scroll-container::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

/* Truncate para 2 linhas */
.truncate-2-lines {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.2;
}

/* Ajustes para diferentes tamanhos de tela */
@media (max-width: 640px) {
  .table-scroll-container {
    max-height: calc(100vh - 220px);
  }
}

@media (min-width: 641px) and (max-width: 1024px) {
  .table-scroll-container {
    max-height: calc(100vh - 260px);
  }
}

@media (min-width: 1025px) {
  .table-scroll-container {
    max-height: 600px;
  }
}
</style>
