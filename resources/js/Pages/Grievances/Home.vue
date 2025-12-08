<template>
    <div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-orange-50">
        <!-- Header Mobile-First -->
        <header class="sticky top-0 z-40 bg-white shadow-sm">
            <div class="px-4 py-4">
                <div class="flex items-center justify-between">
                    <a href="/">
                        <div class="flex items-center space-x-3">

                            <img src="/images/Logotipo-scaled.png" class="w-20" />
                        </div>
                    </a>
                    <a
                        href="/login"
                        class="flex items-center px-3 py-2 space-x-2 text-sm font-medium text-gray-700 transition-all bg-gray-100 rounded-xl hover:bg-gray-200 active:scale-95"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Entrar</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="pb-20">
            <!-- Hero Section - Mobile Optimized -->
            <section v-if="!showComplaintForm && !showTracking" class="px-4 pt-6 pb-8">
                <div class="mb-8 text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 mb-4 shadow-xl rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600">
                        <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h2 class="mb-2 text-2xl font-bold text-gray-900">
                        A sua voz importa
                    </h2>
                    <p class="max-w-md mx-auto text-base text-gray-600">
                        Submeta a sua reclamação de forma rápida e acompanhe o progresso em tempo real
                    </p>
                </div>

                <!-- Action Cards -->
                <div class="mb-8 space-y-3">
                    <!-- Nova Reclamação -->
                    <button
                        @click="openAnonymousComplaint"
                        class="w-full p-5 transition-all bg-white border-2 border-orange-100 shadow-lg rounded-2xl hover:shadow-xl active:scale-95"
                    >
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-12 h-12 bg-orange-100 rounded-xl">
                                    <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 text-left">
                                <h3 class="mb-1 text-lg font-bold text-gray-900">Nova Reclamação</h3>
                                <p class="text-sm text-gray-600">Submeta uma nova reclamação de forma anónima ou identificada</p>
                            </div>
                            <svg class="flex-shrink-0 w-5 h-5 mt-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </button>

                    <!-- Rastrear Reclamação -->
                     <a href="/track">


                    <button

                        class="w-full p-5 mt-5 transition-all bg-white border-2 border-transparent shadow-lg rounded-2xl hover:shadow-xl active:scale-95 hover:border-orange-100"
                    >
                    <!-- <button
                        @click="openTracking"
                        class="w-full p-5 transition-all bg-white border-2 border-transparent shadow-lg rounded-2xl hover:shadow-xl active:scale-95 hover:border-orange-100"
                    > -->
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-xl">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 text-left">
                                <h3 class="mb-1 text-lg font-bold text-gray-900">Rastrear Reclamação</h3>
                                <p class="text-sm text-gray-600">Consulte o estado da sua reclamação usando o número de referência</p>
                            </div>
                            <svg class="flex-shrink-0 w-5 h-5 mt-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </button>
                         </a>
                </div>

                <!-- Info Cards -->
                <div class="p-5 mb-6 bg-blue-50 rounded-2xl">
                    <div class="flex items-start space-x-3">
                        <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1">
                            <h4 class="mb-1 font-semibold text-blue-900">Como funciona?</h4>
                            <ol class="space-y-2 text-sm text-blue-800">
                                <li class="flex items-start">
                                    <span class="mr-2 font-bold">1.</span>
                                    <span>Submeta a sua reclamação preenchendo o formulário</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="mr-2 font-bold">2.</span>
                                    <span>Receba um número de referência para rastreamento</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="mr-2 font-bold">3.</span>
                                    <span>Acompanhe o progresso da resolução</span>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Stats -->

            </section>

            <!-- Complaint Form Section -->
            <section v-if="showComplaintForm" class="px-4 pt-4">
                <div class="mb-4">
                    <button
                        @click="closeComplaintForm"
                        class="flex items-center space-x-2 text-gray-600 transition-colors hover:text-gray-900"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span class="font-medium">Voltar</span>
                    </button>
                </div>
                <ComplaintForm :is-anonymous="isAnonymousSubmission" :embedded="true" @submitted="handleSubmitted" />
            </section>

            <!-- Tracking Section -->
            <section v-if="showTracking" class="px-4 pt-4">
                <div class="mb-6">
                    <button
                        @click="closeTracking"
                        class="flex items-center mb-4 space-x-2 text-gray-600 transition-colors hover:text-gray-900"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span class="font-medium">Voltar</span>
                    </button>

                    <div class="p-6 bg-white shadow-lg rounded-2xl">
                        <h3 class="mb-6 text-xl font-bold text-center text-gray-900">
                            Rastrear Reclamação
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">
                                    Número de Referência
                                </label>
                                <input
                                    v-model="trackingNumber"
                                    type="text"
                                    placeholder="GRM-2025-XXXXXXXX"
                                    class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-orange-500 text-base uppercase transition-all"
                                    @input="trackingNumber = trackingNumber.toUpperCase()"
                                />
                            </div>

                            <button
                                @click="trackGrievance"
                                :disabled="!trackingNumber || isTracking"
                                class="w-full py-4 font-semibold text-white transition-all bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed active:scale-95"
                            >
                                <span v-if="!isTracking">Consultar Estado</span>
                                <span v-else class="flex items-center justify-center space-x-2">
                                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Consultando...</span>
                                </span>
                            </button>
                        </div>

                        <div v-if="trackingError" class="p-4 mt-4 border-2 border-red-200 bg-red-50 rounded-xl">
                            <p class="text-sm text-center text-red-800">{{ trackingError }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Success Modal -->
        <Modal
            :show="showSuccessModal"
            type="success"
            :title="modalTitle"
            :message="modalMessage"
            confirm-text="Ver Detalhes"
            cancel-text="Fechar"
            @confirm="handleModalConfirm"
            @close="showSuccessModal = false"
        >
            <div v-if="referenceNumber" class="p-4 mt-4 bg-gray-50 rounded-xl">
                <p class="mb-1 text-xs text-center text-gray-600">Número de Referência:</p>
                <p class="font-mono text-lg font-bold text-center text-orange-600">{{ referenceNumber }}</p>
                <p class="mt-2 text-xs text-center text-gray-500">Guarde este número para consultar o estado</p>
            </div>
        </Modal>

        <!-- Tracking Result Modal -->
        <TrackingResultModal
            v-if="trackingResult"
            :show="showTrackingResult"
            :grievance="trackingResult"
            @close="showTrackingResult = false"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import ComplaintForm from '@/Components/UtenteDashboard/ComplaintForm.vue'
import TrackingResultModal from '@/Components/Grievances/TrackingResultModal.vue'
import Modal from '@/Components/Modal.vue'

// State
const showComplaintForm = ref(false)
const showTracking = ref(false)
const isAnonymousSubmission = ref(true)
const trackingNumber = ref('')
const isTracking = ref(false)
const trackingError = ref('')
const trackingResult = ref(null)
const showTrackingResult = ref(false)
const showSuccessModal = ref(false)
const modalTitle = ref('')
const modalMessage = ref('')
const referenceNumber = ref('')

// Methods
const openAnonymousComplaint = () => {
    isAnonymousSubmission.value = true
    showComplaintForm.value = true
    showTracking.value = false
}

const openTracking = () => {
    showTracking.value = true
    showComplaintForm.value = false
    trackingNumber.value = ''
    trackingError.value = ''
}

const closeComplaintForm = () => {
    showComplaintForm.value = false
}

const closeTracking = () => {
    showTracking.value = false
}

const handleSubmitted = (data) => {
    showComplaintForm.value = false
    referenceNumber.value = data.reference_number
    modalTitle.value = 'Reclamação Submetida!'
    modalMessage.value = 'A sua reclamação foi recebida com sucesso. Guarde o número de referência para rastreamento.'
    showSuccessModal.value = true
}

const handleModalConfirm = () => {
    if (referenceNumber.value) {
        trackingNumber.value = referenceNumber.value
        showSuccessModal.value = false
        openTracking()
        setTimeout(() => trackGrievance(), 500)
    }
}

const trackGrievance = async () => {
    if (!trackingNumber.value.trim()) {
        trackingError.value = 'Por favor, insira o número de referência.'
        return
    }

    isTracking.value = true
    trackingError.value = ''

    try {
        const response = await fetch('/api/grievances/track', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                reference_number: trackingNumber.value.trim().toUpperCase()
            })
        })

        const data = await response.json()

        if (response.ok && data.success) {
            trackingResult.value = data.grievance
            showTrackingResult.value = true
            trackingError.value = ''
        } else {
            trackingError.value = data.message || 'Reclamação não encontrada.'
        }
    } catch (error) {
        console.error('Erro ao rastrear reclamação:', error)
        trackingError.value = 'Erro ao consultar. Por favor, tente novamente.'
    } finally {
        isTracking.value = false
    }
}

// Open complaint form automatically if URL contains ?new=1 or ?new=true
onMounted(() => {
    try {
        const params = new URLSearchParams(window.location.search)
        const newParam = params.get('new')
        if (newParam === '1' || newParam === 'true') {
            openAnonymousComplaint()

            // remove the query param to avoid reopening on refresh
            params.delete('new')
            const newSearch = params.toString()
            const newUrl = window.location.pathname + (newSearch ? `?${newSearch}` : '') + window.location.hash
            window.history.replaceState({}, '', newUrl)
        }
    } catch (e) {
        // noop - if URL APIs are not available, ignore
        console.error('Error handling new param', e)
    }
})
</script>
