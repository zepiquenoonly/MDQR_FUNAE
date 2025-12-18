<template>
  <div class="modern-kpi-card group">
    <!-- Background gradient overlay -->
    <div class="absolute inset-0 bg-gradient-to-br from-white/80 via-white/60 to-white/40 dark:from-gray-800/80 dark:via-gray-800/60 dark:to-gray-800/40 rounded-2xl backdrop-blur-sm"></div>

    <!-- Animated border -->
    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-primary-400 via-orange-400 to-primary-500 opacity-0 group-hover:opacity-100 transition-opacity duration-500 p-[1px]">
      <div class="w-full h-full bg-white dark:bg-gray-800 rounded-2xl"></div>
    </div>

    <!-- Main content -->
    <div class="relative z-10 p-4">
      <div class="flex items-start justify-between">
        <div class="flex-1 min-w-0">
          <!-- Title with subtle animation -->
          <h3 class="text-gray-600 dark:text-gray-300 text-sm font-semibold mb-1 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-300">
            {{ title }}
          </h3>

          <!-- Value with enhanced typography -->
          <div class="text-3xl font-bold bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 dark:from-white dark:via-gray-100 dark:to-white bg-clip-text text-transparent mb-1 group-hover:from-primary-600 group-hover:via-orange-500 group-hover:to-primary-600 dark:group-hover:from-primary-400 dark:group-hover:via-orange-400 dark:group-hover:to-primary-400 transition-all duration-500">
            {{ formattedValue }}
          </div>

          <!-- Description -->
          <p class="text-gray-500 dark:text-gray-400 text-xs leading-relaxed group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors duration-300">
            {{ description }}
          </p>
        </div>

        <!-- Enhanced Icon Section -->
        <div class="flex flex-col items-end ml-3">
          <!-- Icon with modern design -->
          <div class="relative">
            <div class="w-12 h-12 bg-gradient-to-br from-primary-500 via-orange-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl group-hover:shadow-primary-500/25 transition-all duration-500 transform group-hover:scale-110 group-hover:rotate-3">
              <!-- Inner glow effect -->
              <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent rounded-xl"></div>

              <!-- Icon with enhanced styling -->
              <component
                :is="dynamicIcon"
                class="w-6 h-6 text-white relative z-10 drop-shadow-sm"
              />
            </div>

            <!-- Floating particles effect -->
            <div class="absolute -top-1 -right-1 w-2 h-2 bg-orange-400 rounded-full opacity-0 group-hover:opacity-100 animate-ping transition-opacity duration-300"></div>
            <div class="absolute -bottom-1 -left-1 w-1.5 h-1.5 bg-primary-400 rounded-full opacity-0 group-hover:opacity-100 animate-pulse transition-opacity duration-500 delay-100"></div>
          </div>

          <!-- Enhanced Trend Indicator -->
          <div
            v-if="trend"
            :class="[
              'flex items-center gap-1 mt-2 text-xs font-semibold px-2 py-1 rounded-full backdrop-blur-sm border transition-all duration-300 transform group-hover:scale-105',
              trend === 'up'
                ? 'bg-green-50/80 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800'
                : trend === 'down'
                ? 'bg-red-50/80 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800'
                : 'bg-gray-50/80 text-gray-700 border-gray-200 dark:bg-gray-900/30 dark:text-gray-300 dark:border-gray-800',
            ]"
          >
            <ArrowTrendingUpIcon v-if="trend === 'up'" class="w-3 h-3" />
            <ArrowTrendingDownIcon v-else-if="trend === 'down'" class="w-3 h-3" />
            <MinusIcon v-else class="w-3 h-3" />
            <span class="font-bold">{{ trendText }}</span>
          </div>
        </div>
      </div>

      <!-- Progress bar for visual enhancement -->
      <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1 overflow-hidden">
          <div
            class="h-full bg-gradient-to-r from-primary-500 to-orange-500 rounded-full transition-all duration-1000 ease-out"
            :style="{ width: progressWidth }"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  ExclamationTriangleIcon,
  ClockIcon,
  ExclamationCircleIcon,
  CheckCircleIcon,
  DocumentTextIcon,
  PaperAirplaneIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  MinusIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  title: String,
  value: [String, Number],
  description: String,
  icon: String,
  trend: String, // 'up', 'down', 'stable'
});

// Mapeamento de ícones atualizado
const iconMap = {
  ExclamationTriangleIcon,
  ClockIcon,
  ExclamationCircleIcon,
  CheckCircleIcon,
  DocumentTextIcon,
  PaperAirplaneIcon,
};

const dynamicIcon = computed(() => {
  return iconMap[props.icon] || DocumentTextIcon;
});

const trendText = computed(() => {
  const texts = {
    up: "+12%",
    down: "-5%",
    stable: "0%",
  };
  return texts[props.trend] || "";
});

// Formatar valor com separadores de milhares
const formattedValue = computed(() => {
  if (typeof props.value === 'number') {
    return props.value.toLocaleString('pt-PT');
  }
  return props.value || '0';
});

// Largura da barra de progresso baseada no valor (simulação visual)
const progressWidth = computed(() => {
  const numValue = typeof props.value === 'number' ? props.value : parseInt(props.value) || 0;

  // Normalizar para uma porcentagem entre 20% e 100%
  if (numValue === 0) return '20%';
  if (numValue <= 10) return '35%';
  if (numValue <= 50) return '55%';
  if (numValue <= 100) return '75%';
  return '90%';
});
</script>

<style scoped>
.modern-kpi-card {
  @apply relative overflow-hidden transition-all duration-500 ease-out;
  @apply bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl;
  @apply border border-gray-200/50 dark:border-gray-700/50;
  @apply rounded-2xl shadow-lg hover:shadow-2xl;
  @apply transform hover:-translate-y-1;
  min-height: 110px;
}

/* Enhanced hover effects */
.modern-kpi-card:hover {
  @apply shadow-primary-500/10;
}

/* Smooth transitions for all elements */
.modern-kpi-card * {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Icon container with enhanced effects */
.modern-kpi-card .icon-container {
  filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
}

.modern-kpi-card:hover .icon-container {
  filter: drop-shadow(0 8px 16px rgba(249, 115, 22, 0.3));
}

/* Progress bar animation */
.modern-kpi-card .progress-bar {
  background: linear-gradient(90deg, #f97316 0%, #ea580c 100%);
  animation: progress-fill 1.5s ease-out;
}

@keyframes progress-fill {
  0% {
    width: 0%;
  }
  100% {
    width: var(--progress-width);
  }
}

/* Floating particles animation */
@keyframes float-particle {
  0%, 100% {
    transform: translateY(0px) scale(1);
    opacity: 0.7;
  }
  50% {
    transform: translateY(-4px) scale(1.1);
    opacity: 1;
  }
}

.modern-kpi-card:hover .floating-particle {
  animation: float-particle 2s ease-in-out infinite;
}

/* Enhanced focus states for accessibility */
.modern-kpi-card:focus-within {
  @apply ring-2 ring-primary-500 ring-offset-2;
  outline: none;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .modern-kpi-card {
    min-height: 120px;
  }

  .modern-kpi-card .text-4xl {
    @apply text-3xl;
  }

  .modern-kpi-card .w-16.h-16 {
    @apply w-14 h-14;
  }
}

/* Dark mode enhancements */
.dark .modern-kpi-card {
  @apply bg-gray-800/80 border-gray-700/50;
}

.dark .modern-kpi-card:hover {
  @apply shadow-orange-500/10;
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .modern-kpi-card {
    transition: none;
  }

  .modern-kpi-card:hover {
    transform: none;
  }

  .modern-kpi-card .progress-bar {
    animation: none;
  }

  .floating-particle {
    animation: none !important;
  }
}
</style>
