<!-- Components/UtenteDashboard/MenuItem.vue -->
<template>
  <div>
    <button
      v-if="!href && !route"
      @click="handleClick"
      :class="[
        'w-full flex items-center px-5 py-3 text-sm font-medium transition-all duration-200',
        active
          ? 'bg-primary-50 text-primary-700 border-r-4 border-primary-500'
          : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900 hover:border-r-4 border-gray-300',
        loading ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
      ]"
      :disabled="loading"
    >
      <component
        :is="icon"
        :class="[
          'mr-3 h-5 w-5 flex-shrink-0',
          active ? 'text-primary-500' : 'text-gray-400',
        ]"
      />
      {{ text }}
      <div v-if="loading" class="ml-2">
        <div
          class="animate-spin h-4 w-4 border-2 border-gray-300 border-t-gray-600 rounded-full"
        ></div>
      </div>
    </button>

    <Link
      v-else
      :href="href || route"
      :class="[
        'w-full flex items-center px-5 py-3 text-sm font-medium transition-all duration-200',
        active || $page.url === (href || route)
          ? 'bg-primary-50 text-primary-700 border-r-4 border-primary-500'
          : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900 hover:border-r-4 border-gray-300',
      ]"
      @click="handleLinkClick"
    >
      <component
        :is="icon"
        :class="[
          'mr-3 h-5 w-5 flex-shrink-0',
          active || $page.url === (href || route) ? 'text-primary-500' : 'text-gray-400',
        ]"
      />
      {{ text }}
    </Link>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { Link, router } from "@inertiajs/vue3";

const props = defineProps({
  active: Boolean,
  icon: [Object, Function],
  text: String,
  href: String,
  route: String,
  onClick: Function,
});

const emit = defineEmits(["click"]);
const loading = ref(false);

const handleClick = () => {
  if (props.onClick) {
    props.onClick();
  }
  emit("click");
};

const handleLinkClick = async (event) => {
  if (props.href || props.route) {
    loading.value = true;

    try {
      // Nada a fazer aqui - o Inertia cuidará da navegação
    } catch (error) {
      console.error("Erro na navegação:", error);

      // Se for erro 403, mostrar mensagem
      if (error.response?.status === 403) {
        alert("Você não tem permissão para acessar esta página.");
      }
    } finally {
      loading.value = false;
    }
  }
};

// Interceptar erros do Inertia
router.on("error", (error) => {
  console.error("Erro do Inertia:", error);

  if (error.detail.response?.status === 403) {
    alert("Acesso não autorizado. Você não tem permissão para acessar esta página.");
  }
});
</script>
