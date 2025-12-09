import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import {
  CheckCircleIcon,
  ClockIcon,
  ChartBarIcon,
  UserGroupIcon,
} from "@heroicons/vue/24/outline";

export const useIndicators = (props) => {
  // Refs
  const timeRange = ref(props.timeRange);
  const categoryFilter = ref(props.categoryFilter);
  const showReportModal = ref(false);
  const loading = ref({
    exporting: false,
    generatingReport: false,
  });
  const exportFormat = ref(null);

  // Computed properties
  const filteredIndicators = computed(() => {
    let indicators = props.indicators;

    if (categoryFilter.value !== "all") {
      indicators = indicators.filter((ind) => ind.category === categoryFilter.value);
    }

    return indicators;
  });

  const translatedCategoryDistribution = computed(() => {
    return props.categoryDistribution.map((item) => ({
      ...item,
      category: translateCategory(item.category),
    }));
  });

  const summaryStats = computed(() => [
    {
      title: "Taxa de Resolução",
      value: `${(props.grievanceStats.resolution_rate || 0).toFixed(1)}%`,
      description: "Reclamações resolvidas",
      trend: props.grievanceStats.resolution_rate_trend || 0,
      icon: CheckCircleIcon,
      iconBg: "bg-green-100 dark:bg-green-900/20",
      iconColor: "text-green-600 dark:text-green-400",
    },
    {
      title: "Tempo Médio",
      value: `${(props.grievanceStats.avg_resolution_time || 0).toFixed(1)} dias`,
      description: "Resolução média",
      trend: props.grievanceStats.resolution_time_trend || 0,
      icon: ClockIcon,
      iconBg: "bg-blue-100 dark:bg-blue-900/20",
      iconColor: "text-blue-600 dark:text-blue-400",
    },
    {
      title: "Casos Pendentes",
      value: props.grievanceStats.pending || 0,
      description: "Aguardando resolução",
      trend: props.grievanceStats.pending_trend || 0,
      icon: ChartBarIcon,
      iconBg: "bg-yellow-100 dark:bg-yellow-900/20",
      iconColor: "text-yellow-600 dark:text-yellow-400",
    },
    {
      title: "Técnicos Activos",
      value: props.grievanceStats.active_technicians || 0,
      description: "Com casos atribuídos",
      trend: props.grievanceStats.technicians_trend || 0,
      icon: UserGroupIcon,
      iconBg: "bg-purple-100 dark:bg-purple-900/20",
      iconColor: "text-purple-600 dark:text-purple-400",
    },
  ]);

  const quickStats = computed(() => [
    { label: "Total de Reclamações", value: props.grievanceStats.total || 0 },
    { label: "Resolvidas", value: props.grievanceStats.resolved || 0 },
    { label: "Em Andamento", value: props.grievanceStats.pending || 0 },
    {
      label: "Técnicos Activos",
      value: props.grievanceStats.active_technicians || 0,
    },
  ]);

  // Methods
  const updateFilters = () => {
    router.get(
      route("gestor.dashboard.indicadores"),
      {
        time_range: timeRange.value,
        category: categoryFilter.value,
      },
      {
        preserveState: true,
        preserveScroll: true,
      }
    );
  };

  const translateCategory = (category) => {
    if (!category) return "Não Categorizado";

    const translations = {
      // Categorias de indicadores
      performance: "Performance",
      satisfaction: "Satisfação",
      efficiency: "Eficiência",
      quality: "Qualidade",
      productivity: "Produtividade",
      effectiveness: "Efetividade",
      timeliness: "Pontualidade",
      accuracy: "Precisão",

      // Categorias de reclamações (caso venha do categoryDistribution)
      "Serviços Públicos": "Serviços Públicos",
      Infraestrutura: "Infraestrutura",
      Ambiental: "Ambiental",
      Social: "Social",
      Administração: "Administração",
      "Não categorizado": "Não Categorizado",
      uncategorized: "Não Categorizado",
    };

    const categoryLower = category.toLowerCase();
    const translated =
      translations[categoryLower] ||
      translations[category] ||
      category.charAt(0).toUpperCase() + category.slice(1).toLowerCase();

    return translated.charAt(0).toUpperCase() + translated.slice(1);
  };

  const getIndicatorStatusClass = (category) => {
    const categoryLower = category.toLowerCase();
    const classes = {
      performance: "bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300",
      satisfaction: "bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300",
      efficiency:
        "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300",
      quality: "bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-300",
    };

    return (
      classes[categoryLower] ||
      "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300"
    );
  };

  const getPerformanceColor = (percentage) => {
    if (percentage >= 90) return "bg-green-500";
    if (percentage >= 70) return "bg-yellow-500";
    return "bg-red-500";
  };

  const formatValue = (value, unit) => {
    try {
      const numValue = parseFloat(value);
      if (isNaN(numValue) || !isFinite(numValue)) {
        return "N/A";
      }

      const formats = {
        percentage: () => `${numValue.toFixed(1)}%`,
        days: () => `${numValue.toFixed(1)} dias`,
        count: () => Math.round(numValue).toString(),
        rating: () => `${numValue.toFixed(1)}/5`,
        default: () => `${numValue.toFixed(2)} ${unit || ""}`.trim(),
      };

      const formatter = formats[unit] || formats.default;
      return formatter();
    } catch (error) {
      return "Erro";
    }
  };

  const getInitials = (name) => {
    if (!name) return "?";
    return name
      .split(" ")
      .map((part) => part.charAt(0))
      .join("")
      .toUpperCase()
      .substring(0, 2);
  };

  const openReportGenerator = () => {
    showReportModal.value = true;
  };

  const closeReportModal = () => {
    showReportModal.value = false;
  };

  const exportIndicators = (format) => {
    loading.value.exporting = true;
    exportFormat.value = format;
    
    // Simulate export
    setTimeout(() => {
      loading.value.exporting = false;
      exportFormat.value = null;
      // Show success message
      if (toastRef.value) {
        toastRef.value.show("Indicadores exportados com sucesso!", "success");
      }
    }, 1500);
  };

  const generateReport = (reportData) => {
    loading.value.generatingReport = true;
    
    // Generate report logic here
    setTimeout(() => {
      loading.value.generatingReport = false;
      closeReportModal();
      // Show success message
      if (toastRef.value) {
        toastRef.value.show("Relatório gerado com sucesso!", "success");
      }
    }, 2000);
  };

  return {
    // Refs
    timeRange,
    categoryFilter,
    showReportModal,
    loading,
    exportFormat,
    
    // Computed
    filteredIndicators,
    translatedCategoryDistribution,
    summaryStats,
    quickStats,
    
    // Methods
    updateFilters,
    openReportGenerator,
    closeReportModal,
    exportIndicators,
    generateReport,
    translateCategory,
    getIndicatorStatusClass,
    getPerformanceColor,
    formatValue,
    getInitials,
  };
};