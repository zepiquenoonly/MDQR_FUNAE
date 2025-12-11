<template>
  <div class="flex flex-col xs:flex-row gap-2 sm:gap-3 w-full sm:w-auto items-stretch">
    <!-- Campo de pesquisa -->
    <div class="relative flex-1 min-w-0">
      <input
        v-model="searchQuery"
        type="text"
        :placeholder="placeholder"
        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 sm:pl-4 pr-8 sm:pr-10 focus:ring-2 focus:ring-brand focus:outline-none focus:border-brand dark:bg-dark-accent dark:text-dark-text-primary text-sm h-full"
        @input="$emit('search', searchQuery)"
      />
      <MagnifyingGlassIcon
        class="w-3 h-3 sm:w-4 sm:h-4 text-gray-500 dark:text-gray-400 absolute right-2 sm:right-3 top-1/2 transform -translate-y-1/2"
      />
    </div>

    <!-- Botão Novo/Registar -->
    <button
      v-if="showCreateButton"
      @click="$emit('create')"
      class="bg-brand hover:bg-orange-600 text-white px-3 sm:px-4 py-2 rounded-lg font-semibold flex items-center justify-center gap-1 sm:gap-2 transition-colors text-sm whitespace-nowrap min-h-[42px] flex-shrink-0"
    >
      <PlusIcon class="w-3 h-3 sm:w-4 sm:h-4 flex-shrink-0" />
      <span class="hidden xs:inline">Registar</span>
      <span class="xs:hidden">Novo</span>
    </button>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { MagnifyingGlassIcon, PlusIcon } from "@heroicons/vue/24/outline";

const emit = defineEmits(["search", "create"]);

const props = defineProps({
  placeholder: {
    type: String,
    default: "Pesquisar projectos...",
  },
  showCreateButton: {
    type: Boolean,
    default: false,
  },
});

const searchQuery = ref("");
</script>
