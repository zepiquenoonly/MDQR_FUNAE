<template>
    <span :class="[
        'px-2 py-1 rounded-full text-xs font-semibold',
        statusClasses[status]
    ]">
        {{ statusTexts[status] }}
    </span>
</template>

<script setup>
defineProps({
    status: String
})

const statusClasses = {
    open: 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300',
    in_progress: 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300',
    pending_completion: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300',
    closed: 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300'
}

const statusTexts = {
    open: 'Aberto',
    in_progress: 'Em Progresso',
    pending_completion: 'Solicitado',
    closed: 'Concluído'
}
</script>