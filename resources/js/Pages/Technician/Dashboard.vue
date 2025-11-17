<template>
    <Layout>
        <div class="p-6">
            <!-- Welcome Message -->
            <div class="mb-6">
                <p class="text-gray-600">Role: <strong>{{ $page.props.user.role }}</strong></p>
            </div>

            <!-- Breadcrumb -->
            <Breadcrumb />

            <!-- Conditional Rendering based on active panel -->
            <SugestoesPanel v-if="activePanel === 'sugestoes'" />
            <QueixasPanel v-else-if="activePanel === 'queixas'" />
            <ReclamacoesPanel v-else-if="activePanel === 'reclamacoes'" />
            <div v-else>
                <!-- Default Dashboard Content -->
                <StatsGrid />
                <SubmissionsSection />
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { ref, provide } from 'vue'
import Layout from '@/Layouts/Layout.vue'
import Breadcrumb from '@/Components/Dashboard/Breadcrumb.vue'
import StatsGrid from '@/Components/Dashboard/StatsGrid.vue'
import SubmissionsSection from '@/Components/Dashboard/SubmissionsSection.vue'
import SugestoesPanel from '@/Components/Dashboard/SuggestionsPanel.vue'
import QueixasPanel from '@/Components/Dashboard/ComplaintsPanel.vue'
import ReclamacoesPanel from '@/Components/Dashboard/ClaimsPanel.vue'

// Estado local simples
const activePanel = ref('dashboard')

// Fornecer função para mudar o painel ativo
provide('setActivePanel', (panel) => {
    activePanel.value = panel
    console.log('Painel ativo alterado para:', panel)
})
</script>