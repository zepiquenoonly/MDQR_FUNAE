<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from "vue";
import {
  Chart,
  ArcElement,
  LineElement,
  BarElement,
  PointElement,
  BarController,
  BubbleController,
  DoughnutController,
  LineController,
  PieController,
  PolarAreaController,
  RadarController,
  ScatterController,
  CategoryScale,
  LinearScale,
  LogarithmicScale,
  RadialLinearScale,
  TimeScale,
  TimeSeriesScale,
  Decimation,
  Filler,
  Legend,
  Title,
  Tooltip,
  SubTitle,
} from "chart.js";

// Registrar todos os componentes do Chart.js
Chart.register(
  ArcElement,
  LineElement,
  BarElement,
  PointElement,
  BarController,
  BubbleController,
  DoughnutController,
  LineController,
  PieController,
  PolarAreaController,
  RadarController,
  ScatterController,
  CategoryScale,
  LinearScale,
  LogarithmicScale,
  RadialLinearScale,
  TimeScale,
  TimeSeriesScale,
  Decimation,
  Filler,
  Legend,
  Title,
  Tooltip,
  SubTitle
);

const props = defineProps({
  chartType: {
    type: String,
    default: "doughnut",
    validator: (value) => ["doughnut", "pie", "bar", "line"].includes(value),
  },
  chartData: {
    type: Object,
    required: true,
  },
  title: {
    type: String,
    default: "",
  },
  height: {
    type: Number,
    default: 300, // Aumentado de 256 para 300
  },
  legendPosition: {
    type: String,
    default: "bottom",
    validator: (value) => ["top", "bottom", "left", "right"].includes(value),
  },
});

const chartCanvas = ref(null);
let chartInstance = null;

// Configurações base para os gráficos
const chartConfigs = {
  doughnut: {
    type: "doughnut",
    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: {
        padding: {
          top: 20,
          bottom: 20,
          left: 10,
          right: 10,
        },
      },
      plugins: {
        legend: {
          position: props.legendPosition,
          labels: {
            padding: 25,
            usePointStyle: true,
            font: {
              size: 12,
              family: "'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', sans-serif",
            },
            boxWidth: 12,
          },
        },
        title: {
          display: props.title !== "",
          text: props.title,
          font: {
            size: 16,
            weight: "bold",
          },
          padding: {
            top: 0,
            bottom: 20,
          },
        },
        tooltip: {
          titleFont: {
            size: 13,
          },
          bodyFont: {
            size: 13,
          },
          padding: 10,
          callbacks: {
            label: function (context) {
              let label = context.label || "";
              if (label) {
                label += ": ";
              }
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = Math.round((context.raw / total) * 100);
              label += context.raw + " (" + percentage + "%)";
              return label;
            },
          },
        },
      },
      cutout: "60%", // Aumentado para fazer o donut maior
    },
  },
  pie: {
    type: "pie",
    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: {
        padding: {
          top: 20,
          bottom: 20,
          left: 10,
          right: 10,
        },
      },
      plugins: {
        legend: {
          position: props.legendPosition,
          labels: {
            padding: 25,
            usePointStyle: true,
            font: {
              size: 12,
              family: "'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', sans-serif",
            },
            boxWidth: 12,
          },
        },
        title: {
          display: props.title !== "",
          text: props.title,
          font: {
            size: 16,
            weight: "bold",
          },
          padding: {
            top: 0,
            bottom: 20,
          },
        },
        tooltip: {
          titleFont: {
            size: 13,
          },
          bodyFont: {
            size: 13,
          },
          padding: 10,
          callbacks: {
            label: function (context) {
              let label = context.label || "";
              if (label) {
                label += ": ";
              }
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = Math.round((context.raw / total) * 100);
              label += context.raw + " (" + percentage + "%)";
              return label;
            },
          },
        },
      },
    },
  },
  bar: {
    type: "bar",
    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: {
        padding: {
          top: 20,
          bottom: 10,
          left: 15,
          right: 15,
        },
      },
      plugins: {
        legend: {
          position: "top",
          labels: {
            font: {
              size: 12,
              family: "'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', sans-serif",
            },
            padding: 20,
          },
        },
        title: {
          display: props.title !== "",
          text: props.title,
          font: {
            size: 16,
            weight: "bold",
          },
          padding: {
            top: 0,
            bottom: 15,
          },
        },
        tooltip: {
          titleFont: {
            size: 13,
          },
          bodyFont: {
            size: 13,
          },
          padding: 10,
        },
      },
      scales: {
        x: {
          ticks: {
            font: {
              size: 11,
            },
            maxRotation: 45,
            minRotation: 0,
          },
          grid: {
            display: false,
          },
        },
        y: {
          beginAtZero: true,
          ticks: {
            font: {
              size: 11,
            },
            precision: 0,
          },
          grid: {
            color: "rgba(0, 0, 0, 0.05)",
          },
        },
      },
      barPercentage: 0.6,
      categoryPercentage: 0.8,
    },
  },
  line: {
    type: "line",
    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: {
        padding: {
          top: 20,
          bottom: 10,
          left: 15,
          right: 15,
        },
      },
      plugins: {
        legend: {
          position: "top",
          labels: {
            font: {
              size: 12,
              family: "'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', sans-serif",
            },
            padding: 20,
          },
        },
        title: {
          display: props.title !== "",
          text: props.title,
          font: {
            size: 16,
            weight: "bold",
          },
          padding: {
            top: 0,
            bottom: 15,
          },
        },
        tooltip: {
          titleFont: {
            size: 13,
          },
          bodyFont: {
            size: 13,
          },
          padding: 10,
        },
      },
      scales: {
        x: {
          ticks: {
            font: {
              size: 11,
            },
            maxRotation: 45,
            minRotation: 0,
          },
          grid: {
            display: false,
          },
        },
        y: {
          beginAtZero: true,
          ticks: {
            font: {
              size: 11,
            },
            precision: 0,
          },
          grid: {
            color: "rgba(0, 0, 0, 0.05)",
          },
        },
      },
      elements: {
        point: {
          radius: 4,
          hoverRadius: 6,
        },
        line: {
          tension: 0.3,
        },
      },
    },
  },
};

// Cores para os gráficos
const chartColors = {
  status: [
    "#3B82F6", // submitted - blue
    "#F59E0B", // under_review - yellow
    "#8B5CF6", // assigned - purple
    "#10B981", // in_progress - green
    "#EF4444", // pending_approval - red
    "#06B6D4", // resolved - cyan
    "#F97316", // rejected - orange
    "#6B7280", // closed - gray
  ],
  type: [
    "#EF4444", // complaints - red
    "#3B82F6", // grievances - blue
    "#10B981", // suggestions - green
  ],
  priority: [
    "#10B981", // low - green
    "#F59E0B", // medium - yellow
    "#EF4444", // high - red
    "#8B5CF6", // urgent - purple
  ],
  trends: [
    "#3B82F6", // submitted - blue
    "#10B981", // resolved - green
  ],
};

const prepareChartData = () => {
  // Primeiro, verifique se temos dados
  if (!props.chartData || Object.keys(props.chartData).length === 0) {
    console.warn("No chart data provided");
    return null;
  }

  const config = chartConfigs[props.chartType];

  if (!config) {
    console.error(`Invalid chart type: ${props.chartType}`);
    return null;
  }

  let data = {};

  if (props.chartType === "doughnut" || props.chartType === "pie") {
    // Verificar se chartData é um objeto com propriedades
    if (typeof props.chartData !== "object" || Array.isArray(props.chartData)) {
      console.error("Invalid data format for pie/doughnut chart");
      return null;
    }

    const labels = Object.keys(props.chartData);
    const values = Object.values(props.chartData);

    // Verificar se há dados
    if (labels.length === 0 || values.length === 0) {
      return null;
    }

    let colors = [];

    // Determinar cores baseado no tipo de dados
    if (
      labels.some((label) =>
        [
          "Submetidas",
          "Em Análise",
          "Atribuídas",
          "Em Andamento",
          "Pendentes Aprovação",
          "Resolvidas",
          "Rejeitadas",
          "Fechadas",
        ].includes(label)
      )
    ) {
      colors = chartColors.status;
    } else if (
      labels.some((label) => ["Reclamações", "Queixas", "Sugestões"].includes(label))
    ) {
      colors = chartColors.type;
    } else if (
      labels.some((label) => ["Baixa", "Média", "Alta", "Urgente"].includes(label))
    ) {
      colors = chartColors.priority;
    } else {
      // Cores padrão
      colors = [
        "#3B82F6",
        "#F59E0B",
        "#8B5CF6",
        "#10B981",
        "#EF4444",
        "#06B6D4",
        "#F97316",
        "#6B7280",
      ];
    }

    data = {
      labels: labels,
      datasets: [
        {
          data: values,
          backgroundColor: colors.slice(0, labels.length),
          borderColor: "#fff",
          borderWidth: 2,
          hoverOffset: 15,
          hoverBorderWidth: 3,
        },
      ],
    };
  } else if (props.chartType === "bar" || props.chartType === "line") {
    // Verificar se chartData é um array
    if (!Array.isArray(props.chartData)) {
      console.error("Invalid data format for bar/line chart");
      return null;
    }

    // Dados para gráfico de barras/linhas (tendências mensais)
    const labels = props.chartData.map((item) => item.month || item.label || "");
    const submittedData = props.chartData.map(
      (item) => item.submitted || item.value || 0
    );
    const resolvedData = props.chartData.map((item) => item.resolved || item.value2 || 0);

    // Verificar se há dados
    if (labels.length === 0 || submittedData.length === 0) {
      return null;
    }

    data = {
      labels: labels,
      datasets: [
        {
          label: "Submetidas",
          data: submittedData,
          backgroundColor: props.chartType === "bar" ? chartColors.trends[0] : undefined,
          borderColor: chartColors.trends[0],
          borderWidth: props.chartType === "bar" ? 1 : 2,
          tension: 0.3,
          fill: props.chartType === "line",
          pointBackgroundColor: chartColors.trends[0],
          pointBorderColor: "#fff",
          pointBorderWidth: 2,
        },
        {
          label: "Resolvidas",
          data: resolvedData,
          backgroundColor: props.chartType === "bar" ? chartColors.trends[1] : undefined,
          borderColor: chartColors.trends[1],
          borderWidth: props.chartType === "bar" ? 1 : 2,
          tension: 0.3,
          fill: props.chartType === "line",
          pointBackgroundColor: chartColors.trends[1],
          pointBorderColor: "#fff",
          pointBorderWidth: 2,
        },
      ],
    };
  }

  return {
    ...config,
    data: data,
  };
};

const initChart = () => {
  if (!chartCanvas.value) {
    console.warn("Chart canvas not found");
    return;
  }

  // Destruir gráfico anterior se existir
  if (chartInstance) {
    chartInstance.destroy();
    chartInstance = null;
  }

  const chartConfig = prepareChartData();

  if (!chartConfig || !chartConfig.data || Object.keys(chartConfig.data).length === 0) {
    console.warn("No valid chart data to display");
    return;
  }

  try {
    chartInstance = new Chart(chartCanvas.value, chartConfig);
  } catch (error) {
    console.error("Error creating chart:", error);
  }
};

onMounted(() => {
  // Aguardar próximo ciclo para garantir que o DOM está pronto
  nextTick(() => {
    initChart();
  });
});

onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy();
    chartInstance = null;
  }
});

watch(
  () => props.chartData,
  () => {
    nextTick(() => {
      initChart();
    });
  },
  { deep: true }
);

watch(
  () => props.chartType,
  () => {
    nextTick(() => {
      initChart();
    });
  }
);

watch(
  () => props.legendPosition,
  () => {
    nextTick(() => {
      initChart();
    });
  }
);
</script>

<template>
  <div class="w-full h-full relative flex flex-col" :style="{ minHeight: height + 'px' }">
    <div class="flex-grow">
      <canvas ref="chartCanvas" :height="height"></canvas>
    </div>
    <div
      v-if="!chartData || Object.keys(chartData).length === 0"
      class="absolute inset-0 flex items-center justify-center text-gray-500 dark:text-gray-400"
    >
      <div class="text-center">
        <p>Sem dados disponíveis</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.w-full {
  min-height: 300px;
}

@media (min-width: 768px) {
  .w-full {
    min-height: 350px;
  }
}

@media (min-width: 1024px) {
  .w-full {
    min-height: 400px;
  }
}
</style>
