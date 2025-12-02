<template>
    <div style="zoom: 90%;" class="min-h-screen bg-white">
        <Header :auth-routes="authRoutes" />
        <main class="pt-20">
            <slot />
        </main>
        <Footer />
    </div>
</template>

<script setup>
import Header from '@/Components/Guest/Header.vue'
import Footer from '@/Components/Guest/Footer.vue'

defineProps({
    authRoutes: {
        type: Object,
        required: true,
        default: () => ({
            login: '#',
            register: '#'
        })
    }
})
</script>
