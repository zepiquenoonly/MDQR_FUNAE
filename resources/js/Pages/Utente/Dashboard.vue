<template>
    <Layout>
        <div class="p-6">
            <!-- Breadcrumb -->
            <Breadcrumb />

            <!-- Renderizar ProjectDetails se houver projectId, caso contrário mostrar a lista -->
            <ProjectDetails v-if="$page.props.projectId" :project-id="$page.props.projectId" />
            <SubmissionsSection v-else />
        </div>
    </Layout>
</template>

<script setup>
import Layout from '@/Layouts/UtenteDashboardLayout.vue'
import Breadcrumb from '@/Components/UtenteDashboard/Breadcrumb.vue'
import SubmissionsSection from '@/Components/UtenteDashboard/TabSection.vue'
import ProjectDetails from '@/Components/UtenteDashboard/ProjectDetails.vue'

const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    projectId: {
        type: Number,
        default: null
    }
})

console.log('🚀 Dashboard - User data:', props.user)
console.log('🚀 Dashboard - Project ID:', props.projectId)
</script>