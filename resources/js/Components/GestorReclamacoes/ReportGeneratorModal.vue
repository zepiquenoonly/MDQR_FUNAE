<template>
  <Modal :show="show" @close="closeModal" max-width="2xl">
    <div class="p-6">
      <!-- Cabeçalho -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Gerar Relatório</h2>
        <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>

      <!-- Formulário -->
      <form @submit.prevent="generateReport" class="space-y-6">
        <!-- Tipo de Relatório -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Tipo de Relatório
          </label>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <button
              type="button"
              v-for="type in reportTypes"
              :key="type.value"
              @click="form.report_type = type.value"
              :class="[
                'p-3 rounded-lg border text-center transition-all',
                form.report_type === type.value
                  ? 'bg-primary-50 border-primary-500 text-primary-700'
                  : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50',
              ]"
            >
              <span class="font-medium">{{ type.label }}</span>
            </button>
          </div>
        </div>

        <!-- Período -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Data Inicial
            </label>
            <input
              type="date"
              v-model="form.start_date"
              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
              required
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Data Final
            </label>
            <input
              type="date"
              v-model="form.end_date"
              :min="form.start_date"
              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
              required
            />
          </div>
        </div>

        <!-- Formato -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Formato do Relatório
          </label>
          <div class="flex space-x-4">
            <label
              v-for="format in reportFormats"
              :key="format.value"
              class="flex items-center space-x-2 cursor-pointer"
            >
              <input
                type="radio"
                v-model="form.format"
                :value="format.value"
                class="text-primary-600 focus:ring-primary-500"
              />
              <span class="text-gray-700">{{ format.label }}</span>
            </label>
          </div>
        </div>

        <!-- Indicadores -->
        <div v-if="indicators.length > 0">
          <div class="flex justify-between items-center mb-2">
            <label class="block text-sm font-medium text-gray-700">
              Indicadores a Incluir
            </label>
            <button
              type="button"
              @click="toggleSelectAll"
              class="text-sm text-primary-600 hover:text-primary-800"
            >
              {{ allSelected ? "Desmarcar Todos" : "Selecionar Todos" }}
            </button>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto p-2">
            <label
              v-for="indicator in indicators"
              :key="indicator.id"
              class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer"
            >
              <input
                type="checkbox"
                v-model="form.indicators"
                :value="indicator.id"
                class="rounded text-primary-600 focus:ring-primary-500"
              />
              <div class="flex-1">
                <span class="text-sm font-medium text-gray-900">{{
                  indicator.name
                }}</span>
                <p class="text-xs text-gray-500 truncate">{{ indicator.description }}</p>
              </div>
            </label>
          </div>
        </div>

        <!-- Seções -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Seções do Relatório
          </label>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <label
              v-for="section in reportSections"
              :key="section.value"
              class="flex items-center space-x-2 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer"
            >
              <input
                type="checkbox"
                v-model="form.sections"
                :value="section.value"
                class="rounded text-primary-600 focus:ring-primary-500"
              />
              <span class="text-sm text-gray-700">{{ section.label }}</span>
            </label>
          </div>
        </div>

        <!-- Filtros Adicionais -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Filtros Adicionais (Opcional)
          </label>
          <textarea
            v-model="form.filters"
            rows="3"
            placeholder="Ex: status=resolved, priority=high"
            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
          ></textarea>
        </div>

        <!-- Ações -->
        <div class="flex justify-end space-x-3 pt-6 border-t">
          <button
            type="button"
            @click="closeModal"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="generating"
            :class="[
              'px-4 py-2 rounded-lg font-medium transition-colors',
              generating
                ? 'bg-primary-400 cursor-not-allowed'
                : 'bg-primary-600 hover:bg-primary-700 text-white',
            ]"
          >
            <span v-if="generating">
              <svg
                class="animate-spin h-5 w-5 inline-block mr-2"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                />
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                />
              </svg>
              Gerando...
            </span>
            <span v-else>Gerar Relatório</span>
          </button>
        </div>
      </form>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  indicators: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["close", "report-generated"]);

// Estados
const generating = ref(false);
const form = ref({
  report_type: "monthly",
  start_date: getDefaultDate("start"),
  end_date: getDefaultDate("end"),
  format: "pdf",
  indicators: [],
  sections: ["summary", "charts", "detailed"],
  filters: "",
});

// Configurações
const reportTypes = [
  { value: "daily", label: "Diário" },
  { value: "weekly", label: "Semanal" },
  { value: "monthly", label: "Mensal" },
  { value: "quarterly", label: "Trimestral" },
  { value: "annual", label: "Anual" },
  { value: "custom", label: "Personalizado" },
];

const reportFormats = [
  { value: "pdf", label: "PDF" },
  { value: "excel", label: "Excel" },
  { value: "html", label: "HTML" },
];

const reportSections = [
  { value: "summary", label: "Resumo Executivo" },
  { value: "charts", label: "Gráficos" },
  { value: "detailed", label: "Detalhado" },
  { value: "technicians", label: "Desempenho de Técnicos" },
  { value: "categories", label: "Análise por Categoria" },
  { value: "timeline", label: "Linha do Tempo" },
];

// Computed
const allSelected = computed(() => {
  return form.value.indicators.length === props.indicators.length;
});

// Métodos
function getDefaultDate(type) {
  const today = new Date();
  if (type === "start") {
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    return firstDay.toISOString().split("T")[0];
  } else {
    return today.toISOString().split("T")[0];
  }
}

function toggleSelectAll() {
  if (allSelected.value) {
    form.value.indicators = [];
  } else {
    form.value.indicators = props.indicators.map((ind) => ind.id);
  }
}

function closeModal() {
  form.value = {
    report_type: "monthly",
    start_date: getDefaultDate("start"),
    end_date: getDefaultDate("end"),
    format: "pdf",
    indicators: [],
    sections: ["summary", "charts", "detailed"],
    filters: "",
  };
  generating.value = false;
  emit("close");
}

async function generateReport() {
  if (generating.value) return;

  generating.value = true;

  try {
    const response = await router.post(
      route("manager.indicators.generate-report"),
      form.value,
      {
        preserveScroll: true,
        onSuccess: () => {
          // Mostrar mensagem de sucesso
          showNotification(
            "Relatório em geração! Você será notificado quando estiver pronto.",
            "success"
          );
          closeModal();
          emit("report-generated");
        },
        onError: (errors) => {
          // Mostrar erros
          if (errors.message) {
            showNotification(errors.message, "error");
          } else {
            showNotification("Erro ao gerar relatório. Verifique os dados.", "error");
          }
        },
        onFinish: () => {
          generating.value = false;
        },
      }
    );
  } catch (error) {
    console.error("Erro ao gerar relatório:", error);
    showNotification("Erro ao conectar com o servidor.", "error");
    generating.value = false;
  }
}

function showNotification(message, type = "info") {
  // Você pode usar um sistema de notificações ou alert simples
  alert(`${type === "success" ? "✅" : "❌"} ${message}`);
}

// Watchers
watch(
  () => props.show,
  (newVal) => {
    if (newVal) {
      // Quando o modal abre, seleciona todos os indicadores por padrão
      form.value.indicators = props.indicators.map((ind) => ind.id);
    }
  }
);

// Inicialização
onMounted(() => {
  // Seleciona todos os indicadores por padrão
  form.value.indicators = props.indicators.map((ind) => ind.id);
});
</script>

<style scoped>
/* Estilos personalizados se necessário */
.max-h-60 {
  max-height: 15rem;
}
</style>
