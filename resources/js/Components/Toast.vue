<template>
    <transition name="fade">
        <div v-if="visible" :class="[
            'fixed top-5 right-5 px-4 py-3 rounded-lg shadow-lg text-white',
            type === 'error' ? 'bg-red-600' : 'bg-green-600'
        ]">
            {{ message }}
        </div>
    </transition>
</template>

<script setup>
import { ref } from 'vue'


const visible = ref(false)
const message = ref('')
const type = ref('success')

function showToast(msg, t = 'success') {
    message.value = msg
    type.value = t
    visible.value = true
    setTimeout(() => (visible.value = false), 3000)
}

defineExpose({ showToast })
</script>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity .3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
