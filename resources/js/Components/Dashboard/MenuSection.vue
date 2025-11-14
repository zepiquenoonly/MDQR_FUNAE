<template>
  <nav class="py-4">
    <!-- Menu Label -->
    <div 
      :class="[
        'px-5 py-4 text-xs text-gray-500 font-semibold uppercase tracking-wide transition-opacity duration-300',
        isCollapsed ? 'opacity-0' : 'opacity-100'
      ]"
    >
      Menu
    </div>

    <!-- Menu Items -->
    <MenuItem
      :active="true"
      :icon="'🏠'"
      :text="'Início'"
      :is-collapsed="isCollapsed"
      @click="$emit('item-clicked', 'inicio')"
    />

    <!-- MDQR Dropdown -->
    <MenuDropdown
      :icon="'📄'"
      :text="'MDQR'"
      :badge="5"
      :is-collapsed="isCollapsed"
      :items="mdqrItems"
      @item-clicked="$emit('item-clicked', $event)"
    />

    <MenuItem
      :icon="'📁'"
      :text="'Gestão dos Projectos'"
      :is-collapsed="isCollapsed"
      @click="$emit('item-clicked', 'gestao-projetos')"
    />

    <!-- Estatísticas Dropdown -->
    <MenuDropdown
      :icon="'📊'"
      :text="'Estatísticas'"
      :is-collapsed="isCollapsed"
      :items="estatisticasItems"
      @item-clicked="$emit('item-clicked', $event)"
    />

    <MenuItem
      :icon="'👥'"
      :text="'Gestão dos Usuários'"
      :is-collapsed="isCollapsed"
      @click="$emit('item-clicked', 'gestao-usuarios')"
    />
  </nav>
</template>

<script setup>
import MenuItem from './MenuItem.vue'
import MenuDropdown from './MenuDropdown.vue'

defineProps({
  isCollapsed: Boolean
})

defineEmits(['item-clicked'])

const mdqrItems = [
  { icon: '💡', text: 'Sugestões' },
  { icon: '📝', text: 'Queixas' },
  { icon: '⚠️', text: 'Reclamações' }
]

const estatisticasItems = [
  { icon: '📈', text: 'Visão geral de utilização' },
  { icon: '📊', text: 'Gráficos de utilização' }
]
</script>