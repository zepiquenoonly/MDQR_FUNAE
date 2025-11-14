<template>
  <Layout>
    <div class="p-6">
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