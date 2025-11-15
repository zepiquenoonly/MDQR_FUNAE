<template>
    <Layout>
        <div class="p-6">
            <!-- Welcome Message -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Bem-vindo, {{ $page.props.user.name }}!</h1>
                <p class="text-gray-600">Role: {{ $page.props.user.role }}</p>
            </div>

            <!-- Breadcrumb -->
            <Breadcrumb />

            <!-- Stats Grid -->
            <StatsGrid />

            <!-- Submissions Section -->
            <SubmissionsSection />
        </div>
    </Layout>
</template>

<script setup>
import Layout from '@/Layouts/Layout.vue'
import Breadcrumb from '@/Components/Dashboard/Breadcrumb.vue'
import StatsGrid from '@/Components/Dashboard/StatsGrid.vue'
import SubmissionsSection from '@/Components/Dashboard/SubmissionsSection.vue'
</script>
