<template>
    <div
        class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4">
            Utente
        </h2>
        <div class="space-y-3">
            <div>
                <p class="text-xs text-gray-600 dark:text-gray-400 uppercase font-medium">
                    Nome
                </p>
                <p class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary">
                    {{ complaint.user?.name || "Anónimo" }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    complaint: {
        type: Object,
        required: true,
    },
});
</script>