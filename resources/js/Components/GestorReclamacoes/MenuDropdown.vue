<template>
  <div class="relative">
    <div
      :class="[
        'flex items-center gap-3 px-5 py-3 text-white cursor-pointer transition-all duration-200 border-l-3 border-transparent hover:bg-white hover:bg-opacity-10',
        isOpen ? 'bg-white bg-opacity-20' : '',
      ]"
      @click="handleClick"
      @mouseenter="onMouseEnter"
      @mouseleave="onMouseLeave"
    >
      <component :is="icon" class="flex-shrink-0 w-5 h-5 text-white" />

      <span
        :class="[
          'transition-opacity duration-300 flex-1 text-sm font-medium text-left',
          isCollapsed ? 'opacity-0' : 'opacity-100',
        ]"
      >
        {{ text }}
      </span>

      <!-- Badge -->
      <span
        v-if="badge"
        :class="[
          'bg-white text-orange-600 rounded-full px-2 py-1 text-xs font-bold transition-opacity duration-300 min-w-6 text-center',
          isCollapsed ? 'opacity-0' : 'opacity-100',
        ]"
      >
        {{ badge }}
      </span>

      <!-- Arrow -->
      <ChevronRightIcon
        :class="[
          'text-white text-opacity-70 transition-all duration-300 w-4 h-4 flex-shrink-0',
          isCollapsed ? 'opacity-0' : 'opacity-100',
          isOpen ? 'rotate-90' : '',
        ]"
      />
    </div>

    <!-- Dropdown Items - Normal (quando sidebar aberta) -->
    <div
      v-if="!isCollapsed && isOpen"
      class="bg-white bg-opacity-10 overflow-hidden transition-all duration-300"
    >
      <Link
        v-for="(item, index) in items"
        :key="index"
        :href="item.href || '#'"
        :class="[
          'flex items-center gap-3 py-2.5 px-5 pl-12 text-white cursor-pointer transition-all duration-200 border-l-3 border-transparent hover:bg-white hover:bg-opacity-20',
          item.active ? 'bg-white bg-opacity-15' : '',
        ]"
        @click="handleItemClick(item)"
      >
        <component :is="item.icon" class="flex-shrink-0 w-4 h-4 text-white" />
        <span class="text-sm">
          {{ item.text }}
        </span>

        <!-- Badge para itens do dropdown -->
        <span
          v-if="item.badge"
          class="bg-white text-orange-600 rounded-full px-2 py-1 text-xs font-bold ml-auto"
        >
          {{ item.badge }}
        </span>
      </Link>
    </div>

    <!-- Dropdown Items - Popup (quando sidebar fechada) -->
    <div
      v-if="isCollapsed && showPopup"
      class="absolute left-full top-0 ml-2 bg-orange-500 rounded-lg shadow-lg py-2 min-w-48 z-50"
      @mouseenter="onPopupEnter"
      @mouseleave="onPopupLeave"
    >
      <!-- Header do Popup -->
      <div class="px-4 py-3 border-b border-orange-300">
        <div class="flex items-center justify-between">
          <span class="text-white font-semibold text-sm">{{ text }}</span>
          <span
            v-if="badge"
            class="bg-white text-orange-600 rounded-full px-2 py-1 text-xs font-bold ml-2"
          >
            {{ badge }}
          </span>
        </div>
      </div>

      <!-- Itens do Popup -->
      <Link
        v-for="(item, index) in items"
        :key="index"
        :href="item.href || '#'"
        class="flex items-center gap-3 px-4 py-2 text-white cursor-pointer transition-colors duration-200 hover:bg-orange-600"
        @click="handleItemClick(item)"
      >
        <component :is="item.icon" class="flex-shrink-0 w-4 h-4 text-white" />
        <span class="text-sm flex-1">
          {{ item.text }}
        </span>

        <!-- Badge para itens no popup -->
        <span
          v-if="item.badge"
          class="bg-white text-orange-600 rounded-full px-2 py-1 text-xs font-bold"
        >
          {{ item.badge }}
        </span>
      </Link>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onUnmounted } from "vue";
import { Link, router } from "@inertiajs/vue3";
import { ChevronRightIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  icon: {
    type: Object,
    required: true,
  },
  text: {
    type: String,
    required: true,
  },
  badge: {
    type: [String, Number],
    default: null,
  },
  isCollapsed: {
    type: Boolean,
    default: false,
  },
  items: {
    type: Array,
    default: () => [],
  },
  dropdownManager: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["item-clicked"]);

const showPopup = ref(false);
let popupTimer = null;

// Computed para verificar se este dropdown está aberto
const isOpen = computed(() => {
  return props.dropdownManager.isDropdownOpen(props.id);
});

const handleClick = () => {
  if (!props.isCollapsed) {
    props.dropdownManager.toggleDropdown(props.id);
  }
};

const onMouseEnter = () => {
  if (props.isCollapsed) {
    clearTimeout(popupTimer);
    popupTimer = setTimeout(() => {
      showPopup.value = true;
    }, 200);
  }
};

const onMouseLeave = () => {
  if (props.isCollapsed) {
    clearTimeout(popupTimer);
    popupTimer = setTimeout(() => {
      showPopup.value = false;
    }, 300);
  }
};

const onPopupEnter = () => {
  clearTimeout(popupTimer);
};

const onPopupLeave = () => {
  popupTimer = setTimeout(() => {
    showPopup.value = false;
  }, 300);
};

const handleItemClick = (item) => {
  // Fechar dropdown após clicar em um item
  props.dropdownManager.closeDropdown();
  showPopup.value = false;

  // Se o item tem href, o Inertia Link já cuida da navegação
  // Caso contrário, emitir evento
  if (!item.href) {
    const itemWithId = {
      text: item.text,
      id: item.id || item.text.toLowerCase().replace(/\s+/g, "-"),
      badge: item.badge,
    };
    emit("item-clicked", itemWithId);
  }
};

// Watch para fechar popup quando dropdown for fechado
watch(isOpen, (newValue) => {
  if (!newValue && !props.isCollapsed) {
    showPopup.value = false;
  }
});

// Cleanup timer on unmount
onUnmounted(() => {
  clearTimeout(popupTimer);
});
</script>
