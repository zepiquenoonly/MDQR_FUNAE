<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click="handleBackdropClick">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>

                <!-- Centered modal container -->
                <div class="relative min-h-screen px-4 py-10 flex items-center justify-center" @click.stop>
                    <div :class="['w-full px-4', maxWidth]">
                        <div class="relative overflow-hidden bg-white shadow-xl rounded-2xl">
                            <!-- Header / Icon -->
                            <div class="px-6 pt-6 pb-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 pr-4">
                                        <h3 v-if="title" class="mb-1 text-lg font-semibold text-gray-900">{{ title }}</h3>
                                        <div v-if="message" class="text-sm text-gray-600">{{ message }}</div>
                                    </div>
                                    <div class="flex-shrink-0 ml-2">
                                        <button
                                            v-if="props.closeable"
                                            @click="handleCancel"
                                            type="button"
                                            aria-label="Fechar"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200"
                                        >
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div v-if="type" class="flex items-center justify-center mt-4">
                                    <div v-if="type === 'success'" class="flex items-center justify-center w-14 h-14 bg-green-100 rounded-full">
                                        <svg class="w-7 h-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div v-if="type === 'error'" class="flex items-center justify-center w-14 h-14 bg-red-100 rounded-full">
                                        <svg class="w-7 h-7 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </div>
                                    <div v-if="type === 'warning'" class="flex items-center justify-center w-14 h-14 bg-yellow-100 rounded-full">
                                        <svg class="w-7 h-7 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/>
                                        </svg>
                                    </div>
                                    <div v-if="type === 'info'" class="flex items-center justify-center w-14 h-14 bg-blue-100 rounded-full">
                                        <svg class="w-7 h-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Content slot -->
                            <div class="px-6 pb-6">
                                <slot />
                            </div>

                            <!-- Actions -->
                            <!-- <div class="px-6 py-4 bg-gray-50">
                                <div class="grid grid-cols-1 gap-3">
                                    <button
                                        v-if="confirmText"
                                        @click="handleConfirm"
                                        type="button"
                                        class="w-full rounded-lg px-4 py-3 text-base font-semibold text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all"
                                        :class="confirmButtonClass"
                                    >
                                        {{ confirmText }}
                                    </button>

                                    <button
                                        v-if="cancelText"
                                        @click="handleCancel"
                                        type="button"
                                        class="w-full rounded-lg bg-white px-4 py-3 text-base font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 transition-all"
                                    >
                                        {{ cancelText }}
                                    </button>
                                </div>
                            </div> -->

                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    type: {
        type: String,
        default: null, // 'success', 'error', 'warning', 'info'
    },
    title: {
        type: String,
        default: null,
    },
    message: {
        type: String,
        default: null,
    },
    // confirmText: {
    //     type: String,
    //     default: 'OK',
    // },
    cancelText: {
        type: String,
        default: null,
    },
    maxWidth: {
        type: String,
        default: 'max-w-md',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
})

const emit = defineEmits(['close', 'confirm', 'cancel'])

const confirmButtonClass = computed(() => {
    switch (props.type) {
        case 'success':
            return 'bg-green-600 hover:bg-green-700 focus:ring-green-500'
        case 'error':
            return 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
        case 'warning':
            return 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500'
        case 'info':
            return 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'
        default:
            return 'bg-orange-600 hover:bg-orange-700 focus:ring-orange-500'
    }
})

const handleBackdropClick = () => {
    if (props.closeable) {
        emit('close')
    }
}

const handleConfirm = () => {
    emit('confirm')
    emit('close')
}

const handleCancel = () => {
    emit('cancel')
    emit('close')
}

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show && props.closeable) {
        emit('close')
    }
}

onMounted(() => document.addEventListener('keydown', closeOnEscape))
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape))
</script>
