<template>
  <header class="relative px-4 py-3 h-20 transition-all duration-300 bg-black/10 dark:bg-black/20 backdrop-blur-3xl border border-white/20 dark:border-white/10 shadow-2xl shadow-black/20 dark:shadow-black/40 rounded-2xl mx-2 mt-2">
    <!-- Transparente com efeito glass puro -->

    <div class="relative z-50 flex items-center justify-between gap-2">
      <!-- Left Section -->
      <div class="flex items-center flex-1 min-w-0 gap-2 sm:gap-4">
        <!-- Botão Menu Mobile -->
        <button @click="$emit('toggle-sidebar')" class="flex-shrink-0 p-2.5 bg-white/10 dark:bg-white/5 backdrop-blur-lg text-white transition-all duration-200 sm:hidden hover:text-primary-300 hover:bg-white/20 dark:hover:bg-white/10 rounded-xl hover:scale-105 active:scale-95 border border-white/20 dark:border-white/10 shadow-lg">
          <Bars3Icon class="w-6 h-6" />
        </button>

        <!-- Logo ou título -->
        <div class="flex items-center gap-2">
          <div class="hidden sm:block">
            <h1 class="font-bold text-lg text-primary-600 dark:text-white drop-shadow-sm">Dashboard</h1>
            <p class=" dark:text-primary-300 text-sm font-medium">{{ getRoleTitle() }}</p>
          </div>
        </div>
      </div>

      <!-- Right Section -->
      <div class="flex items-center flex-shrink-0 gap-3 sm:gap-4">

        <!-- User Profile com alta prioridade z-index -->
        <div class="relative z-[99999]">
          <UserDropdown :user="user" />
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import {
  Bars3Icon,
} from '@heroicons/vue/24/outline'
import UserDropdown from '@/Components/UtenteDashboard/UserDropdown.vue'
import { computed } from 'vue'
import { detectRole } from '@/utils/roles'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

// Pegar o role dinamicamente do objeto user
const userRole = computed(() => {
  // Usar a função detectRole para obter o role correto
  return detectRole(props.user)
})

const getRoleTitle = () => {
  const titles = {
    technician: 'Painel do Técnico',
    manager: 'Painel do Gestor',
    pca: 'Painel do PCA',
    director: 'Painel do Director',
    admin: 'Painel do Administrador',
    utente: 'Painel do Utente',
  }

  // Converte para lowercase para garantir match
  const roleKey = userRole.value.toLowerCase()
  return titles[roleKey] || `Painel do ${capitalizeFirstLetter(userRole.value)}`
}

const capitalizeFirstLetter = (string) => {
  if (!string) return 'Utilizador'
  return string.charAt(0).toUpperCase() + string.slice(1)
}

defineEmits(['toggle-sidebar'])
</script>

<style scoped>
header {
  background: linear-gradient(135deg,
    rgba(255, 255, 255, 0.05) 0%,
    rgba(255, 255, 255, 0.02) 100%);
  box-shadow:
    0 20px 60px -30px rgba(0, 0, 0, 0.3),
    0 10px 40px -20px rgba(0, 0, 0, 0.2),
    inset 0 1px 0 0 rgba(255, 255, 255, 0.15),
    inset 0 -1px 0 0 rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(32px);
  -webkit-backdrop-filter: blur(32px);
}

.dark header {
  background: linear-gradient(135deg,
    rgba(255, 255, 255, 0.03) 0%,
    rgba(255, 255, 255, 0.01) 100%);
  box-shadow:
    0 20px 60px -30px rgba(0, 0, 0, 0.5),
    0 10px 40px -20px rgba(0, 0, 0, 0.4),
    inset 0 1px 0 0 rgba(255, 255, 255, 0.1),
    inset 0 -1px 0 0 rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.15);
}

/* Efeito de brilho nas bordas */
header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border-radius: inherit;
  padding: 1px;
  background: linear-gradient(135deg,
    rgba(255, 255, 255, 0.3) 0%,
    rgba(255, 255, 255, 0.1) 50%,
    rgba(255, 255, 255, 0) 100%);
  -webkit-mask:
    linear-gradient(#fff 0 0) content-box,
    linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
  z-index: 0;
}

/* Garantir que o dropdown fique acima de tudo */
:deep(.dropdown-container),
:deep(.user-dropdown-menu) {
  z-index: 999999 !important;
  position: relative;
}

/* Efeito de vidro fosco intenso nos botões */
button {
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Efeito de glow no hover */
button:hover {
  box-shadow:
    0 0 30px rgba(59, 130, 246, 0.2),
    inset 0 1px 0 0 rgba(255, 255, 255, 0.3),
    inset 0 -1px 0 0 rgba(0, 0, 0, 0.1);
}

.dark button:hover {
  box-shadow:
    0 0 40px rgba(96, 165, 250, 0.3),
    inset 0 1px 0 0 rgba(255, 255, 255, 0.2),
    inset 0 -1px 0 0 rgba(0, 0, 0, 0.2);
}





/* Tooltip do botão de notificações */
span.hidden.group-hover\:block {
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Melhorar visibilidade em mobile */
@media (max-width: 640px) {
  header {
    margin: 0.5rem;
    border-radius: 1rem;
    backdrop-filter: blur(28px);
  }
}

/* Garantir que o conteúdo fique acima dos efeitos */
header > * {
  position: relative;
  z-index: 2;
}

/* Efeito de transparência com profundidade */
header::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border-radius: inherit;
  background: radial-gradient(
    ellipse at 50% 0%,
    rgba(255, 255, 255, 0.3) 0%,
    transparent 70%
  );
  pointer-events: none;
  z-index: 1;
}


</style>
