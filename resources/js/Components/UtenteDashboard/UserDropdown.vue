<template>
  <div class="relative z-[100]">
    <!-- Spinner para logout -->
    <div v-if="logoutLoading" class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-[999]">
      <div class="py-8 px-12 text-center glass-card border border-white/40">
        <div class="w-16 h-16 mx-auto mb-4 border-b-4 rounded-full animate-spin border-primary-500"></div>
        <p class="mt-2 text-base font-medium text-gray-700">A Sair...</p>
      </div>
    </div>

    <div class="flex items-center gap-2 p-2 transition-all duration-200 rounded-xl cursor-pointer hover:bg-primary-50 group"
      @click="toggleDropdown">
      <div class="flex items-center justify-center text-white bg-gradient-to-br from-primary-500 to-orange-600 rounded-full w-9 h-9 shadow-md group-hover:scale-110 transition-transform duration-300 ring-2 ring-white/30">
        <UserIcon class="w-5 h-5" />
      </div>
      <span class="hidden text-sm font-semibold text-gray-700 sm:block group-hover:text-primary-600 transition-colors">
        {{ user?.name || 'Usuário' }}
      </span>
      <ChevronDownIcon :class="['w-4 h-4 text-gray-500 group-hover:text-primary-600 transition-all duration-300', isOpen ? 'rotate-180' : '']" />
    </div>

    <!-- Dropdown Menu -->
    <div v-if="isOpen"
      class="absolute right-0 z-[150] w-56 py-2 mt-2 glass-card backdrop-blur-xl border border-white/40 rounded-xl shadow-2xl top-full animate-slide-in-top">

      <!-- Perfil - Ocultar quando hideProfile for true -->
      <Link v-if="!hideProfile" href="/profile/info"
        class="flex items-center gap-3 px-4 py-3 mx-2 text-sm font-medium text-gray-700 transition-all duration-200 cursor-pointer hover:bg-primary-50 rounded-lg group"
        @click="isOpen = false">
        <div class="p-1.5 bg-primary-50 rounded-lg group-hover:bg-primary-100 transition-colors">
          <UserIcon class="w-4 h-4 text-primary-600" />
        </div>
        <span class="group-hover:text-primary-700">Perfil</span>
      </Link>

      <!-- Divider -->
      <div class="my-2 border-t border-gray-200/50"></div>

      <!-- Sair - URL direta -->
      <button :disabled="logoutLoading"
        class="flex items-center w-full gap-3 px-4 py-3 mx-2 text-sm font-semibold text-left text-red-600 transition-all duration-200 cursor-pointer hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg group"
        @click="handleLogout">
        <div class="p-1.5 bg-red-50 rounded-lg group-hover:bg-red-100 transition-colors">
          <ArrowRightOnRectangleIcon class="w-4 h-4 text-red-600" />
        </div>
        <span v-if="!logoutLoading" class="group-hover:text-red-700">Sair</span>
        <span v-else class="flex items-center gap-2">
          <span class="w-3 h-3 border-2 border-red-600 border-t-transparent rounded-full animate-spin"></span>
          Saindo...
        </span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router, Link } from '@inertiajs/vue3'
// import { useToast } from '@/Composables/useToast'
import {
  UserIcon,
  ChevronDownIcon,
  ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline'

// const { error } = useToast()

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
