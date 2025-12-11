<template>
  <div class="chart-container">
    <canvas ref="chartCanvas"></canvas>
    <div v-if="!hasData" class="no-data-message">
      <p class="text-gray-500 dark:text-gray-400">
        Nenhum dado disponível para este período
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from "vue";
import Chart from "chart.js/auto";

const props = defineProps({
  type: {
    type: String,
    required: true,
    validator: (value) => ["line", "bar", "pie", "doughnut"].includes(value),
  },
  data: {
    type: Object,
    required: true,
  },
  options: {
    type: Object,
    default: () => ({}),
  },
});

const chartCanvas = ref(null);
let chartInstance = null;
const hasData = ref(false);

// Configurações padrão por tipo de gráfico
const defaultOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: "top",
      labels: {
        color: "#6B7280", // text-gray-500
        font: {
          size: 12,
        },
      },
    },
    tooltip: {
      backgroundColor: "rgba(0, 0, 0, 0.7)",
      titleColor: "#F9FAFB",
      bodyColor: "#F9FAFB",
      padding: 10,
      cornerRadius: 6,
    },
  },
};

// Configurações específicas para cada tipo
const typeSpecificOptions = {
  line: {
    scales: {
      x: {
        grid: {
          color: "rgba(229, 231, 235, 0.3)", // gray-200
        },
        ticks: {
          color: "#6B7280", // gray-500
        },
      },
      y: {
        beginAtZero: true,
        grid: {
          color: "rgba(229, 231, 235, 0.3)", // gray-200
        },
        ticks: {
          color: "#6B7280", // gray-500
          callback: function (value) {
            return Number.isInteger(value) ? value : "";
          },
        },
      },
    },
    elements: {
      line: {
        tension: 0.3,
      },
      point: {
        radius: 4,
        hoverRadius: 6,
      },
    },
  },
  bar: {
    scales: {
      x: {
        grid: {
          display: false,
        },
        ticks: {
          color: "#6B7280",
        },
      },
      y: {
        beginAtZero: true,
        grid: {
          color: "rgba(229, 231, 235, 0.3)",
        },
        ticks: {
          color: "#6B7280",
          callback: function (value) {
            return Number.isInteger(value) ? value : "";
          },
        },
      },
    },
  },
  pie: {
    cutout: "0%",
  },
  doughnut: {
    cutout: "50%",
  },
};

// Verificar se há dados para exibir
const checkForData = () => {
  if (!props.data || !props.data.datasets || props.data.datasets.length === 0) {
    hasData.value = false;
    return false;
  }

  const hasValidData = props.data.datasets.some(
    (dataset) =>
      dataset.data && dataset.data.length > 0 && dataset.data.some((value) => value > 0)
  );

  hasData.value = hasValidData;
  return hasValidData;
};

// Inicializar gráfico
const initChart = () => {
  if (!chartCanvas.value || !checkForData()) {
    return;
  }

  // Destruir gráfico anterior se existir
  if (chartInstance) {
    chartInstance.destroy();
  }

  // Combinar opções
  const finalOptions = {
    ...defaultOptions,
    ...typeSpecificOptions[props.type],
    ...props.options,
  };

  // Aplicar tema escuro se necessário
  if (document.documentElement.classList.contains("dark")) {
    finalOptions.plugins.legend.labels.color = "#9CA3AF"; // gray-400
    if (finalOptions.scales) {
      if (finalOptions.scales.x) {
        finalOptions.scales.x.ticks.color = "#9CA3AF";
      }
      if (finalOptions.scales.y) {
        finalOptions.scales.y.ticks.color = "#9CA3AF";
      }
    }
  }

  chartInstance = new Chart(chartCanvas.value, {
    type: props.type,
    data: props.data,
    options: finalOptions,
  });
};

// Observar mudanças nos dados
watch(
  () => props.data,
  (newData) => {
    checkForData();
    if (chartInstance && hasData.value) {
      chartInstance.data = newData;
      chartInstance.update();
    } else if (hasData.value) {
      initChart();
    }
  },
  { deep: true }
);

// Observar mudanças de tema (dark/light)
const observer = new MutationObserver(() => {
  if (chartInstance) {
    chartInstance.update();
  }
});

onMounted(() => {
  initChart();
  observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ["class"],
  });
});

onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy();
  }
  observer.disconnect();
});
</script>

<style scoped>
.chart-container {
  position: relative;
  width: 100%;
  height: 100%;
}

.no-data-message {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
}
</style>
