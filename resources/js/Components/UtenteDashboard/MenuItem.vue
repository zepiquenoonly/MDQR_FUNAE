<template>
  <a
    :class="[
      'flex items-center gap-3 px-5 py-3 cursor-pointer transition-all duration-200 border-l-4 relative',
      active
        ? 'bg-gradient-to-r from-primary-50 to-orange-50 text-primary-700 border-primary-500'
        : 'border-transparent hover:bg-primary-50/50 text-gray-700 hover:text-primary-600',
    ]"
    @click="handleClick"
    :disabled="loading"
  >
    <!-- Spinner de loading -->
    <div v-if="loading" class="absolute left-2 flex items-center justify-center">
      <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary-500"></div>
    </div>

    <component
      :is="icon"
      :class="[
        'flex-shrink-0 w-5 h-5 transition-all duration-200',
        active ? 'text-primary-600' : 'text-gray-600',
        loading ? 'opacity-0' : 'opacity-100',
      ]"
    />

    <span
      class="flex-1 text-sm font-medium transition-all duration-200"
      :class="loading ? 'opacity-70' : ''"
    >
      {{ text }}
      <span v-if="loading" class="text-xs text-gray-500 ml-2">(carregando...)</span>
    </span>

    <!-- Badge -->
    <span
      v-if="badge"
      :class="[
        'inline-flex items-center justify-center px-2 py-0.5 text-xs font-semibold rounded-full',
        active ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-600',
      ]"
    >
      {{ badge }}
    </span>
  </a>
</template>

<script setup>
import { inject } from "vue";
import { useDashboard } from "@/Composables/useDashboard";

const props = defineProps({
  active: {
    type: Boolean,
    default: false,
  },
  icon: Object,
  text: String,
  loading: {
    type: Boolean,
    default: false,
  },
  badge: {
    type: [String, Number],
    default: null,
  },
});

const emit = defineEmits(["click"]);

const { closeDropdown } = useDashboard();

// Obter o gerenciador de dropdowns do contexto
const dropdownManager = inject("dropdownManager");

const handleClick = () => {
  if (props.loading) return; // Não fazer nada se estiver carregando

  // Fechar todos os dropdowns e resetar estado ao clicar em um item regular
  if (dropdownManager) {
    dropdownManager.closeDropdown();
  }
  closeDropdown();

  // Emitir o evento click para o MenuSection
  emit("click");
};
</script>

<style scoped>
a:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  pointer-events: none;
}
</style>
