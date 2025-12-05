<template>
    <GuestLayout :auth-routes="authRoutes">
        <HeroSection :auth-routes="authRoutes" @open-complaint="openGuestComplaintModal" />

        <Modal :show="showGuestComplaintModal" @close="closeGuestComplaintModal" :closeable="true" max-width="max-w-2xl">
            <ComplaintForm :is-anonymous="true" :embedded="true" @submitted="() => { closeGuestComplaintModal(); }" />
        </Modal>

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
import Modal from '@/Components/Modal.vue'
import ComplaintForm from '@/Components/UtenteDashboard/ComplaintForm.vue'

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

import { ref } from 'vue'

const showGuestComplaintModal = ref(false)

const openGuestComplaintModal = () => {
    showGuestComplaintModal.value = true
}

const closeGuestComplaintModal = () => {
    showGuestComplaintModal.value = false
}
</script>

