<template>
    <span :class="[
        'px-2 py-1 rounded-full text-xs font-semibold',
        priorityClasses[priority]
    ]">
        {{ priorityTexts[priority] }}
    </span>
</template>

<script setup>
defineProps({
    priority: String
})

const priorityClasses = {
    high: 'bg-red-100 text-red-700',
    medium: 'bg-yellow-100 text-yellow-700',
    low: 'bg-green-100 text-green-700'
}

const priorityTexts = {
    high: 'Alta',
    medium: 'Média',
    low: 'Baixa'
}
</script>