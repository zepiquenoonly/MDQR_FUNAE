import { ref, onMounted } from "vue";
import Chart from "chart.js/auto";

export const useIndicatorCharts = (
  categoryDistribution,
  resolutionTimeline,
  indicators
) => {
  const categoryChartInstance = ref(null);
  const timelineChartInstance = ref(null);
  
  const initCharts = () => {
    destroyCharts();
    initCategoryChart();
    initTimelineChart();
  };
  
  const destroyCharts = () => {
    if (categoryChartInstance.value) {
      categoryChartInstance.value.destroy();
      categoryChartInstance.value = null;
    }
    if (timelineChartInstance.value) {
      timelineChartInstance.value.destroy();
      timelineChartInstance.value = null;
    }
  };
  
  const initCategoryChart = () => {
    const categoryChartCanvas = document.getElementById('category-chart-canvas');
    if (!categoryChartCanvas || !categoryDistribution.value?.length) return;
    
    const ctx = categoryChartCanvas.getContext("2d");
    const categories = categoryDistribution.value.map((item) => item.category);
    const counts = categoryDistribution.value.map((item) => item.count);
    
    categoryChartInstance.value = new Chart(ctx, {
      type: "doughnut",
      data: {
        labels: categories,
        datasets: [
          {
            data: counts,
            backgroundColor: [
              "#3B82F6",
              "#10B981",
              "#F59E0B",
              "#8B5CF6",
              "#EF4444",
              "#EC4899",
              "#14B8A6",
            ],
            borderWidth: 1,
            borderColor: "#fff",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              padding: 20,
              usePointStyle: true,
            },
          },
        },
      },
    });
  };
  
  const initTimelineChart = () => {
    const timelineChartCanvas = document.getElementById('timeline-chart-canvas');
    if (!timelineChartCanvas || !resolutionTimeline?.length) return;
    
    const ctx = timelineChartCanvas.getContext("2d");
    const labels = resolutionTimeline.map((item) => item.date);
    const submittedData = resolutionTimeline.map((item) => item.submitted);
    const resolvedData = resolutionTimeline.map((item) => item.resolved);
    const pendingData = resolutionTimeline.map((item) => item.pending);
    
    timelineChartInstance.value = new Chart(ctx, {
      type: "line",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Submetidas",
            data: submittedData,
            borderColor: "#3B82F6",
            backgroundColor: "rgba(59, 130, 246, 0.1)",
            tension: 0.4,
            fill: true,
          },
          {
            label: "Resolvidas",
            data: resolvedData,
            borderColor: "#10B981",
            backgroundColor: "rgba(16, 185, 129, 0.1)",
            tension: 0.4,
            fill: true,
          },
          {
            label: "Pendentes",
            data: pendingData,
            borderColor: "#F59E0B",
            backgroundColor: "rgba(245, 158, 11, 0.1)",
            tension: 0.4,
            fill: true,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: "index",
          intersect: false,
        },
        scales: {
          x: {
            grid: {
              display: false,
            },
          },
          y: {
            beginAtZero: true,
            grid: {
              color: "rgba(0, 0, 0, 0.05)",
            },
          },
        },
        plugins: {
          tooltip: {
            mode: "index",
            intersect: false,
          },
        },
      },
    });
  };
  
  const initIndicatorCharts = () => {
    indicators?.forEach((indicator) => {
      if (indicator.records && indicator.records.length > 1) {
        const canvas = document.querySelector(`[data-indicator-chart="${indicator.id}"]`);
        if (canvas) {
          const ctx = canvas.getContext("2d");
          const dates = indicator.records.map((r) => r.date.split("-").slice(1).join("/"));
          const values = indicator.records.map((r) => r.value);
          
          new Chart(ctx, {
            type: "line",
            data: {
              labels: dates,
              datasets: [
                {
                  data: values,
                  borderColor: "#F97316",
                  backgroundColor: "rgba(249, 115, 22, 0.1)",
                  borderWidth: 2,
                  tension: 0.4,
                  fill: true,
                  pointRadius: 0,
                },
              ],
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: { display: false },
                tooltip: { enabled: false },
              },
              scales: {
                x: { display: false },
                y: { display: false },
              },
            },
          });
        }
      }
    });
  };
  
  // Cleanup on unmount
  onMounted(() => {
    return () => {
      destroyCharts();
    };
  });
  
  return {
    initCharts,
    initIndicatorCharts,
    destroyCharts,
  };
};