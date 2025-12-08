export const getInitials = (name) => {
  if (!name) return "?";
  return name
    .split(" ")
    .map((part) => part.charAt(0))
    .join("")
    .toUpperCase()
    .substring(0, 2);
};

export const getPerformanceColor = (percentage) => {
  if (percentage >= 90) return "bg-green-500";
  if (percentage >= 70) return "bg-yellow-500";
  return "bg-red-500";
};

export const translateCategory = (category) => {
  if (!category) return "Não Categorizado";

  const translations = {
    performance: "Performance",
    satisfaction: "Satisfação",
    efficiency: "Eficiência",
    quality: "Qualidade",
    productivity: "Produtividade",
    effectiveness: "Efetividade",
    timeliness: "Pontualidade",
    accuracy: "Precisão",
  };

  const categoryLower = category.toLowerCase();
  const translated =
    translations[categoryLower] ||
    category.charAt(0).toUpperCase() + category.slice(1).toLowerCase();

  return translated.charAt(0).toUpperCase() + translated.slice(1);
};

export const formatValue = (value, unit) => {
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