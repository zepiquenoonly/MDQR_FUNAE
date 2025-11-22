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
    high: 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300',
    medium: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300',
    low: 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300'
}

const priorityTexts = {
    high: 'Alta',
    medium: 'Média',
    low: 'Baixa'
}
</script>