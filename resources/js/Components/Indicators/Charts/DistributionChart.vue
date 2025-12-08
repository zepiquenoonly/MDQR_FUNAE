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
    PieController, // Certifique-se que está importando PieController
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
    PieController, // Certifique-se que está registrando PieController
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
        default: 256,
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
            plugins: {
                legend: {
                    position: "bottom",
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 11,
                        },
                    },
                },
                title: {
                    display: true,
                    text: props.title,
                    font: {
                        size: 16,
                        weight: "bold",
                    },
                    padding: {
                        top: 10,
                        bottom: 30,
                    },
                },
                tooltip: {
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
            cutout: "50%",
        },
    },
    pie: {
        type: "pie", // Certifique-se que está como "pie"
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "bottom",
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 11,
                        },
                    },
                },
                title: {
                    display: true,
                    text: props.title,
                    font: {
                        size: 16,
                        weight: "bold",
                    },
                    padding: {
                        top: 10,
                        bottom: 30,
                    },
                },
                tooltip: {
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
            plugins: {
                legend: {
                    position: "top",
                    labels: {
                        font: {
                            size: 11,
                        },
                    },
                },
                title: {
                    display: true,
                    text: props.title,
                    font: {
                        size: 16,
                        weight: "bold",
                    },
                    padding: {
                        top: 10,
                        bottom: 30,
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                    },
                },
            },
        },
    },
    line: {
        type: "line",
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "top",
                    labels: {
                        font: {
                            size: 11,
                        },
                    },
                },
                title: {
                    display: true,
                    text: props.title,
                    font: {
                        size: 16,
                        weight: "bold",
                    },
                    padding: {
                        top: 10,
                        bottom: 30,
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                    },
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
        "#EF4444", // submitted - red
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
        if (typeof props.chartData !== 'object' || Array.isArray(props.chartData)) {
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
        if (labels.some(label =>
            ["Submetidas", "Em Análise", "Atribuídas", "Em Andamento", "Pendentes Aprovação", "Resolvidas", "Rejeitadas", "Fechadas"].includes(label)
        )) {
            colors = chartColors.status;
        } else if (labels.some(label => ["Reclamações", "Queixas", "Sugestões"].includes(label))) {
            colors = chartColors.type;
        } else if (labels.some(label => ["Baixa", "Média", "Alta", "Urgente"].includes(label))) {
            colors = chartColors.priority;
        } else {
            // Cores padrão
            colors = [
                "#3B82F6", "#F59E0B", "#8B5CF6", "#10B981",
                "#EF4444", "#06B6D4", "#F97316", "#6B7280"
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
        const labels = props.chartData.map((item) => item.month || item.label || '');
        const submittedData = props.chartData.map((item) => item.submitted || item.value || 0);
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
                    borderWidth: 2,
                    tension: 0.4,
                    fill: props.chartType === "line",
                },
                {
                    label: "Resolvidas",
                    data: resolvedData,
                    backgroundColor: props.chartType === "bar" ? chartColors.trends[1] : undefined,
                    borderColor: chartColors.trends[1],
                    borderWidth: 2,
                    tension: 0.4,
                    fill: props.chartType === "line",
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
</script>

<template>
    <div class="w-full h-full relative">
        <canvas ref="chartCanvas" :height="height"></canvas>
        <div v-if="!chartData || Object.keys(chartData).length === 0"
            class="absolute inset-0 flex items-center justify-center text-gray-500 dark:text-gray-400">
            <div class="text-center">
                <p>Sem dados disponíveis</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Estilos específicos do componente */
</style>