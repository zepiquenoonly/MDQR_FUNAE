<template>
    <div class="bg-green-50 rounded-lg overflow-hidden">
        <button @click="isOpen = !isOpen"
            class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-green-100 transition-colors"
            :class="{ 'bg-green-100': isOpen }">
            <span class="font-semibold text-black text-lg">{{ question }}</span>
            <ChevronDownIcon class="h-5 w-5 text-black transition-transform duration-300"
                :class="{ 'rotate-180': isOpen }" />
        </button>

        <div class="transition-all duration-300 overflow-hidden"
            :class="isOpen ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'">
            <div class="px-6 py-4">
                <p class="text-gray-700 leading-relaxed">{{ answer }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { ChevronDownIcon } from '@heroicons/vue/24/outline'

defineProps({
    question: String,
    answer: String
})

const isOpen = ref(false)
</script>