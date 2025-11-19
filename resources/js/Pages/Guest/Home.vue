<template>
    <GuestLayout :auth-routes="authRoutes">
        <HeroSection :auth-routes="authRoutes" />
        <CardsSection />
        <PlatformSection />
        <StatsSection />
        <FaqSection />
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import HeroSection from '@/Components/Guest/HeroSection.vue'
import CardsSection from '@/Components/Guest/CardsSection.vue'
import PlatformSection from '@/Components/Guest/PlatformSection.vue'
import StatsSection from '@/Components/Guest/StatsSection.vue'
import FaqSection from '@/Components/Guest/FaqSection.vue'

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

