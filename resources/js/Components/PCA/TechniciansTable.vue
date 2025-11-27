<template>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                        Técnico
                    </th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                        Total
                    </th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                        Resolvidas
                    </th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                        Taxa Resolução
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="(tech, index) in technicians" :key="tech.id"
                    class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-full bg-brand text-white flex items-center justify-center font-semibold text-sm">
                                {{ index + 1 }}
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ tech.name }}
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-sm text-gray-900 dark:text-white">
                            {{ tech.total_count }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-sm text-green-600 dark:text-green-400 font-medium">
                            {{ tech.resolved_count }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-brand h-2 rounded-full transition-all duration-300"
                                    :style="{ width: `${tech.resolution_rate}%` }"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ tech.resolution_rate }}%
                            </span>
                        </div>
                    </td>
                </tr>
                <tr v-if="!technicians.length">
                    <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                        Nenhum técnico com atividade no período selecionado
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
defineProps({
    technicians: {
        type: Array,
        default: () => []
    }
})
</script>
