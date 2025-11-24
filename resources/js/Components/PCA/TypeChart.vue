<template>
    <div class="h-64">
        <canvas ref="chartCanvas"></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps({
    data: {
        type: Object,
        default: () => ({})
    }
})

const chartCanvas = ref(null)
let chartInstance = null

const typeLabels = {
    'complaint': 'Reclamações',
    'grievance': 'Queixas',
    'suggestion': 'Sugestões',
}

const typeColors = {
    'complaint': '#EF4444', // Red
    'grievance': '#F59E0B', // Orange
    'suggestion': '#10B981', // Green
}

const createChart = () => {
    if (!chartCanvas.value) return

    const labels = Object.keys(props.data).map(key => typeLabels[key] || key)
    const values = Object.values(props.data)
    const colors = Object.keys(props.data).map(key => typeColors[key] || '#9CA3AF')

    if (chartInstance) {
        chartInstance.destroy()
    }

    chartInstance = new Chart(chartCanvas.value, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total de Submissões',
                data: values,
                backgroundColor: colors,
                borderColor: colors,
                borderWidth: 2,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    callbacks: {
                        label: function (context) {
                            const label = context.label || ''
                            const value = context.parsed.y || 0
                            const total = context.dataset.data.reduce((a, b) => a + b, 0)
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0
                            return `${label}: ${value} (${percentage}%)`
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
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
