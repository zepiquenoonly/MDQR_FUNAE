<template>
  <div
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center max-h-[100vh] justify-center p-4 z-50"
  >
    <div
      class="bg-white dark:bg-dark-secondary rounded-xl shadow-lg max-w-6xl w-full max-h-[90vh] overflow-y-auto"
    >
      <!-- Header -->
      <div
        class="sticky top-0 bg-white dark:bg-dark-secondary border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center"
      >
        <div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary">
            Desempenho do Técnico
          </h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ tecnico.name }} - {{ tecnico.email }}
          </p>
        </div>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
        >
          <XMarkIcon class="w-6 h-6" />
        </button>
      </div>

      <div class="p-6">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-8">
          <div
            class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand mx-auto"
          ></div>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            A carregar dados de desempenho...
          </p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-8">
          <ExclamationTriangleIcon class="w-12 h-12 text-red-400 mx-auto mb-4" />
          <h4 class="text-lg font-semibold text-red-800 dark:text-red-400 mb-2">
            Erro ao carregar dados
          </h4>
          <button
            @click="carregarDadosDesempenho"
            class="px-4 py-2 bg-red-600 text-white rounded-lg mt-4 hover:bg-red-700 transition-colors"
          >
            Tentar Novamente
          </button>
        </div>

        <!-- Conteúdo Principal -->
        <div v-else class="space-y-6">
          <!-- Estatísticas Gerais -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-blue-600 dark:text-blue-400">
                    Total de Casos
                  </p>
                  <p class="text-2xl font-bold text-blue-900 dark:text-blue-300">
                    {{ dadosDesempenho.estatisticas_gerais?.total_casos || 0 }}
                  </p>
                </div>
                <ClipboardDocumentListIcon class="w-8 h-8 text-blue-400" />
              </div>
            </div>

            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-green-600 dark:text-green-400">
                    Resolvidos
                  </p>
                  <p class="text-2xl font-bold text-green-900 dark:text-green-300">
                    {{ dadosDesempenho.estatisticas_gerais?.casos_resolvidos || 0 }}
                  </p>
                </div>
                <CheckBadgeIcon class="w-8 h-8 text-green-400" />
              </div>
            </div>

            <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-orange-600 dark:text-orange-400">
                    Taxa de Sucesso
                  </p>
                  <p class="text-2xl font-bold text-orange-900 dark:text-orange-300">
                    {{ dadosDesempenho.estatisticas_gerais?.taxa_sucesso || 0 }}%
                  </p>
                </div>
                <ChartBarIcon class="w-8 h-8 text-orange-400" />
              </div>
            </div>

            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-purple-600 dark:text-purple-400">
                    Tempo Médio
                  </p>
                  <p class="text-2xl font-bold text-purple-900 dark:text-purple-300">
                    {{ dadosDesempenho.estatisticas_gerais?.tempo_medio || 0 }} dias
                  </p>
                </div>
                <ClockIcon class="w-8 h-8 text-purple-400" />
              </div>
            </div>
          </div>

          <!-- Filtro por Mês -->
          <div class="flex items-center justify-between">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary">
              Desempenho Mensal
            </h4>
            <select
              v-model="mesSelecionado"
              @change="carregarDadosMensais"
              class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand dark:bg-dark-accent dark:text-dark-text-primary"
            >
              <option v-for="mes in meses" :key="mes.value" :value="mes.value">
                {{ mes.label }}
              </option>
            </select>
          </div>

          <!-- Gráfico de Desempenho Mensal -->
          <div
            class="bg-white dark:bg-dark-secondary border border-gray-200 dark:border-gray-700 rounded-lg p-6"
          >
            <h5
              class="text-md font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
            >
              Estatísticas do Mês
            </h5>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <div
                class="text-center p-4 border border-gray-100 dark:border-gray-700 rounded-lg"
              >
                <p class="text-sm text-gray-600 dark:text-gray-400">Casos Atribuídos</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-dark-text-primary">
                  {{ dadosMensais.casos_atribuidos || 0 }}
                </p>
              </div>
              <div
                class="text-center p-4 border border-gray-100 dark:border-gray-700 rounded-lg"
              >
                <p class="text-sm text-gray-600 dark:text-gray-400">Casos Resolvidos</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                  {{ dadosMensais.casos_resolvidos || 0 }}
                </p>
              </div>
              <div
                class="text-center p-4 border border-gray-100 dark:border-gray-700 rounded-lg"
              >
                <p class="text-sm text-gray-600 dark:text-gray-400">Em Progresso</p>
                <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                  {{ dadosMensais.em_progresso || 0 }}
                </p>
              </div>
              <div
                class="text-center p-4 border border-gray-100 dark:border-gray-700 rounded-lg"
              >
                <p class="text-sm text-gray-600 dark:text-gray-400">Taxa do Mês</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                  {{ dadosMensais.taxa_resolucao || 0 }}%
                </p>
              </div>
            </div>
          </div>

          <!-- Tabela de Casos do Mês -->
          <div
            class="bg-white dark:bg-dark-secondary border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden"
          >
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <div class="flex justify-between items-center">
                <h5
                  class="text-md font-semibold text-gray-800 dark:text-dark-text-primary"
                >
                  Casos do Mês Selecionado
                </h5>
                <span class="text-sm text-gray-500 dark:text-gray-400"
                  >{{ casosMensais.length }} casos</span
                >
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-dark-accent">
                  <tr>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                    >
                      Caso ID
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                    >
                      Título
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                    >
                      Prioridade
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                    >
                      Status
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                    >
                      Data Atribuição
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                    >
                      Duração
                    </th>
                  </tr>
                </thead>
                <tbody
                  class="bg-white dark:bg-dark-secondary divide-y divide-gray-200 dark:divide-gray-700"
                >
                  <tr
                    v-for="caso in casosMensais"
                    :key="caso.id"
                    class="hover:bg-gray-50 dark:hover:bg-dark-accent"
                  >
                    <td
                      class="px-6 py-4 text-sm text-gray-900 dark:text-dark-text-primary"
                    >
                      #{{ caso.id }}
                    </td>
                    <td
                      class="px-6 py-4 text-sm text-gray-900 dark:text-dark-text-primary"
                    >
                      {{ caso.titulo }}
                    </td>
                    <td class="px-6 py-4">
                      <span
                        :class="[
                          'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                          caso.prioridade === 'alta'
                            ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300'
                            : caso.prioridade === 'media'
                            ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300'
                            : 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300',
                        ]"
                      >
                        {{ formatPriority(caso.prioridade) }}
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      <span
                        :class="[
                          'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                          caso.status === 'resolvido'
                            ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300'
                            : caso.status === 'em_progresso'
                            ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300'
                            : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                        ]"
                      >
                        {{ formatStatus(caso.status) }}
                      </span>
                    </td>
                    <td
                      class="px-6 py-4 text-sm text-gray-900 dark:text-dark-text-primary"
                    >
                      {{ formatDate(caso.data_atribuicao) }}
                    </td>
                    <td
                      class="px-6 py-4 text-sm text-gray-900 dark:text-dark-text-primary"
                    >
                      {{ caso.duracao }} dias
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Empty State -->
              <div v-if="casosMensais.length === 0" class="text-center py-8">
                <ClipboardDocumentListIcon
                  class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4"
                />
                <p class="text-gray-500 dark:text-gray-400">
                  Nenhum caso encontrado para este mês
                </p>
              </div>
            </div>
          </div>

          <!-- Histórico de Desempenho -->
          <div
            class="bg-white dark:bg-dark-secondary border border-gray-200 dark:border-gray-700 rounded-lg p-6"
          >
            <h5
              class="text-md font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
            >
              Histórico dos Últimos 6 Meses
            </h5>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-dark-accent">
                  <tr>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                    >
                      Mês
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                    >
                      Casos Atribuídos
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                    >
                      Casos Resolvidos
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                    >
                      Taxa de Resolução
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                    >
                      Tempo Médio
                    </th>
                  </tr>
                </thead>
                <tbody
                  class="bg-white dark:bg-dark-secondary divide-y divide-gray-200 dark:divide-gray-700"
                >
                  <tr
                    v-for="mes in historicoMensal"
                    :key="mes.mes"
                    class="hover:bg-gray-50 dark:hover:bg-dark-accent"
                  >
                    <td
                      class="px-6 py-4 text-sm text-gray-900 dark:text-dark-text-primary"
                    >
                      {{ formatMonth(mes.mes) }}
                    </td>
                    <td
                      class="px-6 py-4 text-sm text-gray-900 dark:text-dark-text-primary"
                    >
                      {{ mes.casos_atribuidos }}
                    </td>
                    <td
                      class="px-6 py-4 text-sm text-green-600 dark:text-green-400 font-medium"
                    >
                      {{ mes.casos_resolvidos }}
                    </td>
                    <td
                      class="px-6 py-4 text-sm text-blue-600 dark:text-blue-400 font-medium"
                    >
                      {{ mes.taxa_resolucao }}%
                    </td>
                    <td
                      class="px-6 py-4 text-sm text-gray-900 dark:text-dark-text-primary"
                    >
                      {{ mes.tempo_medio }} dias
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import {
  XMarkIcon,
  ClipboardDocumentListIcon,
  CheckBadgeIcon,
  ChartBarIcon,
  ClockIcon,
  ExclamationTriangleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  tecnico: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["close"]);

// Estado
const loading = ref(true);
const error = ref(null);
const mesSelecionado = ref(new Date().toISOString().slice(0, 7)); // YYYY-MM
const dadosDesempenho = ref({});
const dadosMensais = ref({});
const casosMensais = ref([]);
const historicoMensal = ref([]);

// Meses disponíveis para filtro
const meses = computed(() => {
  const meses = [];
  const hoje = new Date();
  for (let i = 0; i < 12; i++) {
    const data = new Date(hoje.getFullYear(), hoje.getMonth() - i, 1);
    meses.push({
      value: data.toISOString().slice(0, 7),
      label: data.toLocaleDateString("pt-PT", { month: "long", year: "numeric" }),
    });
  }
  return meses;
});

// Watcher para recarregar dados quando o técnico muda
watch(
  () => props.tecnico,
  () => {
    if (props.tecnico?.id) {
      carregarDadosDesempenho();
    }
  }
);

// Função para carregar dados gerais de desempenho
const carregarDadosDesempenho = async () => {
  loading.value = true;
  error.value = null;

    try {
        console.log('Carregando dados de desempenho para técnico:', props.tecnico.id)

        const response = await fetch(`/api/tecnicos/${props.tecnico.id}/desempenho`)

    if (!response.ok) {
      throw new Error(`Erro HTTP: ${response.status}`);
    }

        const data = await response.json()
        console.log('Dados de desempenho recebidos:', data)

    dadosDesempenho.value = data.estatisticas_gerais || {};
    historicoMensal.value = data.historico_mensal || [];

        // Carregar dados do mês atual
        await carregarDadosMensais()

    } catch (err) {
        console.error('❌ Erro ao carregar dados de desempenho:', err)
        error.value = err.message || 'Erro ao carregar dados do técnico'
    } finally {
        loading.value = false
    }
}

// Função para carregar dados específicos do mês
const carregarDadosMensais = async () => {
    try {
        console.log('Carregando dados do mês:', mesSelecionado.value)

        const response = await fetch(`/api/tecnicos/${props.tecnico.id}/desempenho/${mesSelecionado.value}`)

    if (!response.ok) {
      throw new Error(`Erro HTTP: ${response.status}`);
    }

        const data = await response.json()
        console.log('Dados mensais recebidos:', data)

        dadosMensais.value = data.dados_mensais || {}
        casosMensais.value = data.casos_mensais || []

    } catch (err) {
        console.error('❌ Erro ao carregar dados mensais:', err)
        // Não definir error global aqui para não quebrar a interface completa
        dadosMensais.value = {}
        casosMensais.value = []
    }
}

// Funções auxiliares de formatação
const formatStatus = (status) => {
  const statusMap = {
    pendente: "Pendente",
    em_progresso: "Em Progresso",
    resolvido: "Resolvido",
    fechado: "Fechado",
  };
  return statusMap[status] || status;
};

const formatPriority = (prioridade) => {
  const priorityMap = {
    alta: "Alta",
    media: "Média",
    baixa: "Baixa",
  };
  return priorityMap[prioridade] || prioridade;
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  return new Date(dateString).toLocaleDateString("pt-PT");
};

const formatMonth = (monthString) => {
  if (!monthString) return "N/A";
  const [year, month] = monthString.split("-");
  const date = new Date(parseInt(year), parseInt(month) - 1, 1);
  return date.toLocaleDateString("pt-PT", { month: "long", year: "numeric" });
};

// Carregar dados quando o componente é montado
onMounted(() => {
  carregarDadosDesempenho();
});
</script>

<style scoped>
/* Garantir que o overlay cubra toda a tela */
.fixed {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}

/* Melhorar a rolagem no modal */
.max-h-\[95vh\] {
  max-height: 95vh;
}
</style>
