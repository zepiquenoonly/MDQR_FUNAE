<template>
    <div class="relative">
        <component :is="href && href !== '#' ? Link : 'button'" :href="href && href !== '#' ? href : undefined" :class="[
            'flex items-center gap-3 px-5 py-3 text-white cursor-pointer transition-all duration-200 border-l-3 w-full text-left group',
            active
                ? 'bg-white bg-opacity-20 text-white border-white'
                : 'border-transparent hover:bg-white hover:bg-opacity-10',
        ]" @mouseenter="onMouseEnter" @mouseleave="onMouseLeave" @click="handleClick">
            <!-- Ícone -->
            <component :is="icon" :class="[
                'flex-shrink-0 w-5 h-5 transition-colors',
                active
                    ? 'text-white'
                    : 'text-white text-opacity-90 group-hover:text-opacity-100',
            ]" />

            <!-- Texto - visível quando sidebar aberta -->
            <span :class="[
                'transition-all duration-300 flex-1 text-sm font-medium text-left whitespace-nowrap',
                isCollapsed ? 'opacity-0 w-0' : 'opacity-100 w-auto',
            ]">
                {{ text }}
            </span>

            <!-- Badge -->
            <span v-if="badge" :class="[
                'bg-white text-orange-600 rounded-full px-2 py-1 text-xs font-bold transition-all duration-300 min-w-6 text-center',
                isCollapsed ? 'opacity-0 scale-0' : 'opacity-100 scale-100',
            ]">
                {{ badge }}
            </span>
        </component>

        <!-- Popup Tooltip quando sidebar fechada - MODIFICADO: fundo branco e texto preto -->
        <div v-if="isCollapsed && showPopup"
            class="absolute left-full top-1/2 transform -translate-y-1/2 ml-3 bg-white dark:bg-gray-800 rounded-lg shadow-xl px-3 py-2 min-w-48 z-50 border border-gray-200 dark:border-gray-600"
            @mouseenter="onPopupEnter" @mouseleave="onPopupLeave">
            <div class="flex items-center justify-between">
                <span class="text-gray-900 dark:text-white font-semibold text-sm">{{ text }}</span>
                <span v-if="badge" class="bg-orange-500 text-white rounded-full px-2 py-1 text-xs font-bold ml-2">
                    {{ badge }}
                </span>
            </div>

            <!-- Seta do tooltip - MODIFICADO: cor branca -->
            <div class="absolute -left-1 top-1/2 transform -translate-y-1/2">
                <div
                    class="w-2 h-2 bg-white dark:bg-gray-800 rotate-45 border-l border-t border-gray-200 dark:border-gray-600">
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, inject, onUnmounted } from "vue";
import { Link, router } from "@inertiajs/vue3";

const props = defineProps({
    active: {
        type: Boolean,
        default: false,
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
    href: {
        type: String,
        default: "#",
    },
});

const emit = defineEmits(["click"]);

// Obter o gerenciador de dropdowns do contexto
const dropdownManager = inject("dropdownManager", null);

const showPopup = ref(false);
let popupTimer = null;

const handleClick = (e) => {
    // Se não tem href válido, prevenir navegação e emitir evento
    if (!props.href || props.href === "#") {
        e.preventDefault();
        emit("click", e);
    }
    // Fechar dropdowns se existe o gerenciador
    if (dropdownManager) {
        dropdownManager.closeDropdown();
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

// Cleanup timer on unmount
onUnmounted(() => {
    clearTimeout(popupTimer);
});
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>