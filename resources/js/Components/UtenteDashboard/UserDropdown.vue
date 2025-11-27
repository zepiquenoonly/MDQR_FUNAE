<template>
  <div class="relative">
    <!-- Spinner para logout -->
    <div v-if="logoutLoading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60]">
      <div class="text-center py-8">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-brand mx-auto mb-4"></div>
        <p class="text-gray-300 text-sm mt-2">A Sair...</p>
      </div>
    </div>

    <div class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-gray-50 transition-colors"
      @click="toggleDropdown">
      <div class="w-9 h-9 bg-gray-400 rounded-full flex items-center justify-center text-white">
        <UserIcon class="w-5 h-5" />
      </div>
      <span class="text-sm font-medium text-gray-700 hidden sm:block">
        {{ user?.name || 'Usuário' }}
      </span>
      <ChevronDownIcon class="text-gray-500 w-4 h-4" />
    </div>

    <!-- Dropdown Menu -->
    <div v-if="isOpen"
      class="absolute top-full right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">

      <!-- Perfil - Ocultar quando hideProfile for true -->
      <Link v-if="!hideProfile" href="/profile/info"
        class="flex items-center gap-3 px-4 py-2 text-sm transition-colors cursor-pointer text-gray-700 hover:bg-gray-50"
        @click="isOpen = false">
      <UserIcon class="w-4 h-4" />
      <span>Perfil</span>
      </Link>

      <!-- Bloqueio de Tela -->
      <a class="flex items-center gap-3 px-4 py-2 text-sm transition-colors cursor-pointer text-gray-700 hover:bg-gray-50"
        @click="handleItemClick({ text: 'Bloqueio de Tela' })">
        <LockClosedIcon class="w-4 h-4" />
        <span>Bloqueio de Tela</span>
      </a>

      <!-- Sair - URL direta -->
      <button :disabled="logoutLoading"
        class="flex items-center gap-3 px-4 py-2 text-sm transition-colors cursor-pointer text-red-600 hover:bg-red-50 w-full text-left disabled:opacity-50 disabled:cursor-not-allowed"
        @click="handleLogout">
        <ArrowRightOnRectangleIcon class="w-4 h-4" />
        <span v-if="!logoutLoading">Sair</span>
        <span v-else>Saindo...</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { useToast } from '@/Composables/useToast'
import {
  UserIcon,
  ChevronDownIcon,
  LockClosedIcon,
  ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline'

const { error } = useToast()

const props = defineProps({
  user: {
    type: Object,
    default: () => ({
      name: 'Usuário',
      email: ''
    })
  },
  hideProfile: {
    type: Boolean,
    default: false
  },
  bgColor: {
    type: String,
    default: 'hover:bg-gray-50'
  },
  textColor: {
    type: String,
    default: 'text-gray-700'
  }
})

const isOpen = ref(false)
const logoutLoading = ref(false)

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
}

const handleLogout = async () => {


  logoutLoading.value = true
  isOpen.value = false

  try {
    await router.post('/logout', {}, {
      onStart: () => {
        logoutLoading.value = true
      },
      onSuccess: () => {
        // O redirecionamento será feito automaticamente pelo Laravel
        logoutLoading.value = false
      },
      onError: () => {
        logoutLoading.value = false
        error('Erro ao fazer logout. Tente novamente.')
      },
      onFinish: () => {
        // Garante que o loading seja desativado mesmo em caso de erro
        setTimeout(() => {
          logoutLoading.value = false
        }, 1000)
      }
    })
  } catch (err) {
    logoutLoading.value = false
    console.error('Erro no logout:', err)
    error('Erro ao fazer logout. Tente novamente.')
  }
}

const handleItemClick = (item) => {
  console.log('Action:', item.text)
  isOpen.value = false
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  const dropdown = event.target.closest('.relative')
  if (!dropdown) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
