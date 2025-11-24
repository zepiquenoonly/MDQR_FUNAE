<template>
    <div class="h-80">
        <canvas ref="chartCanvas"></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps({
    data: {
        type: Array,
        default: () => []
    }
})

const chartCanvas = ref(null)
let chartInstance = null

const createChart = () => {
    if (!chartCanvas.value || !props.data.length) return

    const labels = props.data.map(item => item.name)
    const values = props.data.map(item => item.total)

    if (chartInstance) {
        chartInstance.destroy()
    }

    chartInstance = new Chart(chartCanvas.value, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total de Reclamações',
                data: values,
                backgroundColor: 'rgba(99, 102, 241, 0.8)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                y: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    })
}

onMounted(() => {
    createChart()
})

watch(() => props.data, () => {
    createChart()
}, { deep: true })
</script>
