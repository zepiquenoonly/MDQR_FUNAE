<template>
  <nav class="py-4">
    <!-- Navigation Label -->
    <div class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide">
      Navegação
    </div>

    <!-- Dashboard -->
    <MenuItem
      :active="false"
      :icon="HomeIcon"
      :text="'Dashboard'"
      @click="() => emitItem('dashboard')"
    />

    <!-- Role-specific sections -->
    <template v-if="role === 'technician'">
      <!-- MDQR Section -->
      <div class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-3">MDQR</div>

      <!-- Submeter Reclamação/Queixa/Sugestão -->
      <MenuDropdown
        :icon="DocumentPlusIcon"
        :text="'Nova Submissão'"
        :items="[
          { id: 'reclamacoes', text: 'Reclamação', icon: ExclamationCircleIcon },
          { id: 'queixas', text: 'Queixa', icon: ExclamationTriangleIcon },
          { id: 'sugestoes', text: 'Sugestão', icon: LightBulbIcon }
        ]"
        @item-clicked="emitItem"
      />

      <!-- Acompanhamento -->
      <MenuItem
        :active="false"
        :icon="MagnifyingGlassIcon"
        :text="'Acompanhamento'"
        @click="() => navigateToTracking()"
      />
    </template>

    <template v-if="role === 'manager'">
      <!-- Projectos -->
      <MenuItem
        :active="false"
        :icon="BriefcaseIcon"
        :text="'Projectos'"
        @click="() => emitItem('projectos')"
      />

      <!-- Técnicos -->
      <MenuItem
        :active="false"
        :icon="UserGroupIcon"
        :text="'Técnicos'"
        @click="() => emitItem('tecnicos')"
      />

      <!-- Estatísticas -->
      <div class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-3">Relatórios</div>
      <MenuItem
        :active="false"
        :icon="ChartBarIcon"
        :text="'Estatísticas'"
        @click="() => emitItem('estatisticas')"
      />
    </template>

    <template v-if="role === 'pca'">
      <!-- Projectos -->
      <MenuItem
        :active="false"
        :icon="BriefcaseIcon"
        :text="'Projectos'"
        @click="() => emitItem('projectos')"
      />

      <!-- Estatísticas -->
      <div class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-3">Relatórios</div>
      <MenuItem
        :active="false"
        :icon="ChartBarIcon"
        :text="'Estatísticas'"
        @click="() => emitItem('estatisticas')"
      />

      <!-- Gestão de Usuários -->
      <MenuItem
        :active="false"
        :icon="UsersIcon"
        :text="'Usuários'"
        @click="() => emitItem('usuarios')"
      />
    </template>

    <!-- Conta / Perfil -->
    <div class="px-5 py-3 text-xs text-gray-600 font-semibold uppercase tracking-wide mt-4">Conta</div>

    <!-- Meu Perfil -->
    <MenuItem
      :active="false"
      :icon="UserCircleIcon"
      :text="'Meu Perfil'"
      @click="() => navigateToProfile()"
    />

    <!-- Logout -->
    <MenuItem
      :active="false"
      :icon="ArrowRightOnRectangleIcon"
      :text="'Sair'"
      @click="() => handleLogout()"
    />
  </nav>
</template>

<script setup>
import {
  HomeIcon,
  BriefcaseIcon,
  DocumentPlusIcon,
  ExclamationCircleIcon,
  ExclamationTriangleIcon,
  LightBulbIcon,
  MagnifyingGlassIcon,
  UserCircleIcon,
  ArrowRightOnRectangleIcon,
  UserGroupIcon,
  ChartBarIcon,
  UsersIcon
} from '@heroicons/vue/24/outline'
import { router } from '@inertiajs/vue3'
import MenuItem from '@/Components/UtenteDashboard/MenuItem.vue'
import MenuDropdown from '@/Components/UtenteDashboard/MenuDropdown.vue'

const props = defineProps({
  role: {
    type: String,
    default: 'technician'
  }
})

const emit = defineEmits(['item-clicked'])

const emitItem = (item) => {
  emit('item-clicked', item)
}

const navigateToProfile = () => {
  router.visit('/profile')
}

const navigateToTracking = () => {
  router.visit('/track')
}

const handleLogout = () => {
  router.post('/logout')
}
</script>
