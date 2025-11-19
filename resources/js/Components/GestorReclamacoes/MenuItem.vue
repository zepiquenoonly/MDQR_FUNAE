<template>
    <div class="relative">
        <Link :href="href" :class="[
            'flex items-center gap-3 px-5 py-3 text-white cursor-pointer transition-all duration-200 border-l-3',
            active
                ? 'bg-white bg-opacity-20 text-white border-white'
                : 'border-transparent hover:bg-white hover:bg-opacity-10'
        ]" @mouseenter="onMouseEnter" @mouseleave="onMouseLeave">
        <component :is="icon" :class="[
            'flex-shrink-0 w-5 h-5',
            active ? 'text-white' : 'text-white text-opacity-90'
        ]" />

        <span :class="[
            'transition-opacity duration-300 flex-1 text-sm font-medium text-left',
            isCollapsed ? 'opacity-0' : 'opacity-100'
        ]">
            {{ text }}
        </span>

        <!-- Badge -->
        <span v-if="badge" :class="[
            'bg-white text-orange-600 rounded-full px-2 py-1 text-xs font-bold transition-opacity duration-300 min-w-6 text-center',
            isCollapsed ? 'opacity-0' : 'opacity-100'
        ]">
            {{ badge }}
        </span>
        </Link>

        <!-- Popup para quando sidebar estiver fechado -->
        <div v-if="isCollapsed && showPopup"
            class="absolute left-full top-0 ml-2 bg-orange-500 rounded-lg shadow-lg px-4 py-3 min-w-48 z-50"
            @mouseenter="onPopupEnter" @mouseleave="onPopupLeave">
            <div class="flex items-center justify-between">
                <span class="text-white font-semibold text-sm">{{ text }}</span>
                <span v-if="badge" class="bg-white text-orange-600 rounded-full px-2 py-1 text-xs font-bold ml-2">
                    {{ badge }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, inject } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
    active: {
        type: Boolean,
        default: false
    },
    icon: {
        type: Object,
        required: true
    },
    text: {
        type: String,
        required: true
    },
    badge: {
        type: [String, Number],
        default: null
    },
    isCollapsed: {
        type: Boolean,
        default: false
    },
    href: {
        type: String,
        default: '#'
    }
})

const emit = defineEmits(['click'])

// Obter o gerenciador de dropdowns do contexto
const dropdownManager = inject('dropdownManager')

const showPopup = ref(false)
let popupTimer = null

const handleClick = () => {
    // Fechar todos os dropdowns ao clicar em um item regular
    if (dropdownManager) {
        dropdownManager.closeDropdown()
    }
    emit('click')
}

const onMouseEnter = () => {
    if (props.isCollapsed) {
        clearTimeout(popupTimer)
        popupTimer = setTimeout(() => {
            showPopup.value = true
        }, 200)
    }
}

const onMouseLeave = () => {
    if (props.isCollapsed) {
        clearTimeout(popupTimer)
        popupTimer = setTimeout(() => {
            showPopup.value = false
        }, 300)
    }
}

const onPopupEnter = () => {
    clearTimeout(popupTimer)
}

const onPopupLeave = () => {
    popupTimer = setTimeout(() => {
        showPopup.value = false
    }, 300)
}

// Cleanup timer on unmount
import { onUnmounted } from 'vue'
onUnmounted(() => {
    clearTimeout(popupTimer)
})
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>