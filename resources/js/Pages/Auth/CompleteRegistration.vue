<template>
    <header class="bg-white">
        <div class="flex flex-col items-center justify-center py-4">
            <div class="flex items-center">
                <img src="/images/Emblem_of_Mozambique.svg-2.png" alt="Brasão de Moçambique"
                    class="h-20 w-20 md:h-24 md:w-24 object-contain" />
            </div>
            <h1 class="mt-4 text-xl font-semibold text-gray-900"><strong>Mecanismo de Diálogo, Queixas e
                    Reclamações</strong></h1>
        </div>
    </header>

    <div class="min-h-screen bg-white flex items-center justify-center p-4 -mt-12">
        <div class="bg-white overflow-hidden w-[1400px] max-w-6xl border border-gray">
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
