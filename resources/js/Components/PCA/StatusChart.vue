<template>
    <div class="h-64">
        <canvas ref="chartCanvas"></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps({
    data: {
        type: Object,
        default: () => ({})
    }
})

const chartCanvas = ref(null)
let chartInstance = null

const statusLabels = {
    'submitted': 'Submetida',
    'under_review': 'Em Análise',
    'in_progress': 'Em Progresso',
    'resolved': 'Resolvida',
    'closed': 'Concluída',
    'rejected': 'Rejeitada',
}

const statusColors = {
    'submitted': '#60A5FA',
    'under_review': '#FCD34D',
    'in_progress': '#A78BFA',
    'resolved': '#34D399',
    'closed': '#10B981',
    'rejected': '#F87171',
}

const createChart = () => {
    if (!chartCanvas.value) return

    const labels = Object.keys(props.data).map(key => statusLabels[key] || key)
    const values = Object.values(props.data)
    const colors = Object.keys(props.data).map(key => statusColors[key] || '#9CA3AF')

    if (chartInstance) {
        chartInstance.destroy()
    }

    chartInstance = new Chart(chartCanvas.value, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const label = context.label || ''
                            const value = context.parsed || 0
                            const total = context.dataset.data.reduce((a, b) => a + b, 0)
                            const percentage = ((value / total) * 100).toFixed(1)
                            return `${label}: ${value} (${percentage}%)`
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
