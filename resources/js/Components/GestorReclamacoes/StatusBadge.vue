<template>
    <span :class="[
        'px-3 py-1 rounded-full text-xs font-semibold border backdrop-blur-xl transition-all duration-300 inline-flex items-center gap-1.5',
        statusClasses[status]
    ]">
        <span :class="['w-1.5 h-1.5 rounded-full animate-pulse', dotColors[status]]" />
        {{ statusTexts[status] }}
    </span>
</template>

<script setup>
defineProps({
    status: String
})

const statusClasses = {
    open: 'bg-gradient-to-r from-red-100 to-rose-100 text-red-700 border-red-200',
    in_progress: 'bg-gradient-to-r from-primary-100 to-orange-100 text-primary-700 border-primary-200',
    pending_completion: 'bg-gradient-to-r from-yellow-100 to-amber-100 text-yellow-700 border-yellow-200',
    closed: 'bg-gradient-to-r from-green-100 to-emerald-100 text-green-700 border-green-200'
}

const dotColors = {
    open: 'bg-red-500',
    in_progress: 'bg-primary-500',
    pending_completion: 'bg-yellow-500',
    closed: 'bg-green-500'
}

const statusTexts = {
    open: 'Aberto',
    in_progress: 'Em Progresso',
    pending_completion: 'Solicitado',
    closed: 'Concluído'
}
</script>