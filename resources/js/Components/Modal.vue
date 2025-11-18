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
            <div v-if="show" class="fixed inset-0 z-50 px-4 py-6 overflow-y-auto sm:px-0" @click="handleBackdropClick">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm"></div>

                <!-- Modal -->
                <Transition
                    enter-active-class="transition duration-300 ease-out transform"
                    enter-from-class="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
                    enter-to-class="translate-y-0 opacity-100 sm:scale-100"
                    leave-active-class="transition duration-200 ease-in transform"
                    leave-from-class="translate-y-0 opacity-100 sm:scale-100"
                    leave-to-class="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
                >
                    <div
                        v-if="show"
                        class="relative flex items-end justify-center min-h-screen sm:items-center"
                        @click.stop
                    >
                        <div
                            class="w-full max-w-md overflow-hidden transition-all transform bg-white shadow-2xl rounded-t-3xl sm:rounded-2xl"
                            :class="maxWidth"
                        >
                            <!-- Icon & Content -->
                            <div class="px-6 pt-8 pb-6 text-center">
                                <!-- Icon -->
                                <div v-if="type" class="flex items-center justify-center mx-auto mb-4">
                                    <!-- Success Icon -->
                                    <div
                                        v-if="type === 'success'"
                                        class="flex items-center justify-center w-16 h-16 bg-green-100 rounded-full"
                                    >
                                        <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>

                                    <!-- Error Icon -->
                                    <div
                                        v-if="type === 'error'"
                                        class="flex items-center justify-center w-16 h-16 bg-red-100 rounded-full"
                                    >
                                        <svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </div>

                                    <!-- Warning Icon -->
                                    <div
                                        v-if="type === 'warning'"
                                        class="flex items-center justify-center w-16 h-16 bg-yellow-100 rounded-full"
                                    >
                                        <svg class="w-8 h-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>

                                    <!-- Info Icon -->
                                    <div
                                        v-if="type === 'info'"
                                        class="flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full"
                                    >
                                        <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Title -->
                                <h3 v-if="title" class="mb-2 text-xl font-bold text-gray-900">
                                    {{ title }}
                                </h3>

                                <!-- Message -->
                                <div v-if="message" class="mb-6 text-base text-gray-600">
                                    {{ message }}
                                </div>

                                <!-- Slot for custom content -->
                                <slot />
                            </div>

                            <!-- Actions -->
                            <div class="px-6 py-4 space-y-2 bg-gray-50">
                                <button
                                    v-if="confirmText"
                                    @click="handleConfirm"
                                    type="button"
                                    class="w-full rounded-xl px-4 py-3.5 text-base font-semibold text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all"
                                    :class="confirmButtonClass"
                                >
                                    {{ confirmText }}
                                </button>

                                <button
                                    v-if="cancelText"
                                    @click="handleCancel"
                                    type="button"
                                    class="w-full rounded-xl bg-white px-4 py-3.5 text-base font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 transition-all"
                                >
                                    {{ cancelText }}
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
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
    confirmText: {
        type: String,
        default: 'OK',
    },
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
