<template>
    <div
        class="min-h-screen bg-gradient-to-br from-[#F15F22]/10 via-white to-[#F15F22]/5 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-2xl overflow-hidden w-full max-w-4xl border border-[#F15F22]/10">
            <CompleteRegistrationForm :basic-data="basicData" :loading="loading" :errors="errors"
                @submit="handleCompleteRegistration" @back-to-basic="backToBasicRegistration" />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import CompleteRegistrationForm from '../../Components/Authenticate/CompleteRegistrationForm.vue'

const page = usePage()
const loading = ref(false)
const basicData = ref(page.props.basicData || null)
const errors = ref(page.props.errors || {})

const handleCompleteRegistration = async (completeData) => {
    loading.value = true

    try {
        await router.post('/register/complete', completeData)
    } catch (err) {
        console.error("Erro de cadastro completo:", err)
    } finally {
        loading.value = false
    }
}

const backToBasicRegistration = () => {
    router.get('/register')
}

onMounted(() => {
    // Redirect to basic registration if no basic data
    if (!basicData.value) {
        router.get('/register')
    }
})
</script>
