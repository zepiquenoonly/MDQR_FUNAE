<template>
    <div class="w-full">
        <!-- Spinner global para loading -->
        <div v-if="inertiaLoading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60]">
            <div class="text-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 sm:h-16 sm:w-16 border-b-2 border-brand mx-auto mb-4">
                </div>
                <p class="text-gray-300 text-sm mt-2" v-if="!showRedirectMessage">Por favor, aguarde...</p>
                <p class="text-gray-300 text-sm mt-2" v-else>A redirecionar...</p>
            </div>
        </div>

        <!-- Popup de sucesso -->
        <div v-if="showSuccessPopup"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60] p-4">
            <div class="bg-white rounded-lg p-6 sm:p-8 w-full max-w-xs sm:max-w-sm mx-auto text-center">
                <div
                    class="w-12 h-12 sm:w-16 sm:h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-green-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-2">Registo realizado com sucesso</h3>
                <p class="text-gray-600 text-xs sm:text-sm">A redirecionar...</p>
                <!-- Spinner no popup -->
                <div class="mt-4 flex justify-center">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-500"></div>
                </div>
            </div>
        </div>

        <!-- Container principal -->
        <div
            class="bg-white flex justify-center items-center flex-col text-center py-4 sm:py-6 px-3 sm:px-6 md:px-8 lg:px-12">
            <!-- Título -->
            <h1 class="text-base sm:text-lg md:text-xl font-bold mb-3 sm:mb-4 px-2">
                Bem-vindo ao mecanismo de diálogo, queixas e reclamações (MDQR)
            </h1>

            <!-- Progress Steps Responsivo -->
            <div
                class="flex justify-between items-center relative w-full max-w-4xl mt-3 sm:mt-4 mb-4 sm:mb-6 px-2 sm:px-0">
                <!-- Linha de fundo cinza -->
                <div
                    class="absolute top-3 sm:top-4 left-[5%] sm:left-[10%] right-[5%] sm:right-[10%] h-0.5 bg-gray-300 z-0">
                </div>

                <!-- Linha de progresso brand -->
                <div class="absolute top-3 sm:top-4 left-[5%] sm:left-[10%] h-0.5 bg-brand z-10 transition-all duration-300"
                    :style="{ width: progressWidth }"></div>

                <!-- Step 1 -->
                <div class="flex flex-col items-center relative z-20 flex-1 -mt-2 sm:-mt-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 md:w-16 md:h-16 rounded-full border-2 flex items-center justify-center mb-1 sm:mb-2 transition-all duration-300"
                        :class="currentStep >= 1
                            ? 'border-brand bg-brand text-white'
                            : 'border-gray-300 bg-white text-gray-500'">
                        <UserIcon class="w-4 h-4 sm:w-5 sm:h-5 md:w-8 md:h-8" />
                    </div>
                    <div class="text-xs sm:text-sm text-center max-w-16 sm:max-w-20 md:max-w-24 transition-colors duration-300 leading-tight"
                        :class="currentStep >= 1 ? 'text-brand font-medium' : 'text-gray-600'">
                        Dados Pessoais
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="flex flex-col items-center relative z-20 flex-1 -ml-4 sm:-ml-6 md:-ml-8">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 md:w-16 md:h-16 rounded-full border-2 flex items-center justify-center mb-1 sm:mb-2 transition-all duration-300 -mt-6 sm:-mt-8 md:-mt-10"
                        :class="currentStep >= 2
                            ? 'border-brand bg-brand text-white'
                            : 'border-gray-300 bg-white text-gray-500'">
                        <MapPinIcon class="w-4 h-4 sm:w-5 sm:h-5 md:w-8 md:h-8" />
                    </div>
                    <div class="text-xs sm:text-sm text-center max-w-16 sm:max-w-20 md:max-w-24 transition-colors duration-300 leading-tight"
                        :class="currentStep >= 2 ? 'text-brand font-medium' : 'text-gray-600'">
                        Endereço
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="flex flex-col items-center relative z-20 flex-1">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 md:w-16 md:h-16 rounded-full border-2 flex items-center justify-center mb-1 sm:mb-2 transition-all duration-300 -mt-6 sm:-mt-8 md:-mt-10"
                        :class="currentStep >= 3
                            ? 'border-brand bg-brand text-white'
                            : 'border-gray-300 bg-white text-gray-500'">
                        <DocumentTextIcon class="w-4 h-4 sm:w-5 sm:h-5 md:w-8 md:h-8" />
                    </div>
                    <div class="text-xs sm:text-sm text-center max-w-16 sm:max-w-20 md:max-w-24 transition-colors duration-300 leading-tight"
                        :class="currentStep >= 3 ? 'text-brand font-medium' : 'text-gray-600'">
                        Documento
                    </div>
                </div>
            </div>

            <!-- Step 1: Dados Pessoais -->
            <div v-if="currentStep === 1" class="w-full max-w-4xl px-2 sm:px-0">
                <div class="text-sm sm:text-base md:text-lg text-gray-800 font-semibold mb-3 sm:mb-4 text-center">Dados
                    do Munícipe</div>
                <div class="text-xs sm:text-sm text-gray-600 mb-4 sm:mb-6 leading-relaxed text-justify sm:text-center">
                    Preencha o formulário para acessar informações detalhadas sobre os projetos em andamento no seu
                    bairro. Aproveite esta oportunidade para sugerir melhorias, apresentar queixas ou compartilhar
                    ideias que possam contribuir para o desenvolvimento da sua comunidade. Sua participação é
                    fundamental para alcançarmos resultados ainda melhores!
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 md:gap-5 mb-4 sm:mb-6">
                    <div class="flex flex-col">
                        <label for="email"
                            class="text-left text-xs sm:text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" :value="props.basicData.email" placeholder="Email"
                            class="w-full py-2 px-3 sm:px-4 bg-gray-100 border border-gray-200 rounded-lg outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-xs sm:text-sm md:text-base"
                            readonly>
                    </div>

                    <div class="flex flex-col">
                        <label for="nome"
                            class="text-left text-xs sm:text-sm font-medium text-gray-700 mb-1">Nome</label>
                        <input type="text" id="nome" v-model="formData.nome" placeholder="Digite o seu nome"
                            class="w-full py-2 px-3 sm:px-4 bg-gray-100 border border-gray-200 rounded-lg outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-xs sm:text-sm md:text-base"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.nome }" required>
                        <p v-if="errors.nome" class="text-red-500 text-xs mt-1 text-left">{{ errors.nome }}</p>
                    </div>

                    <div class="flex flex-col">
                        <label for="apelido"
                            class="text-left text-xs sm:text-sm font-medium text-gray-700 mb-1">Apelido</label>
                        <input type="text" id="apelido" v-model="formData.apelido" placeholder="Digite o seu apelido"
                            class="w-full py-2 px-3 sm:px-4 bg-gray-100 border border-gray-200 rounded-lg outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-xs sm:text-sm md:text-base"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.apelido }"
                            required>
                        <p v-if="errors.apelido" class="text-red-500 text-xs mt-1 text-left">{{ errors.apelido }}</p>
                    </div>

                    <div class="flex flex-col">
                        <label for="celular"
                            class="text-left text-xs sm:text-sm font-medium text-gray-700 mb-1">Celular</label>
                        <input type="tel" id="celular" v-model="formData.celular" placeholder="+258 84 123 4567"
                            class="w-full py-2 px-3 sm:px-4 bg-gray-100 border border-gray-200 rounded-lg outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-xs sm:text-sm md:text-base"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.celular }"
                            @input="formatPhoneNumber" @keydown="preventInvalidInput" maxlength="16" required>
                        <p v-if="errors.celular" class="text-red-500 text-xs mt-1 text-left">{{ errors.celular }}</p>
                    </div>
                </div>

                <div class="flex justify-end mt-6 sm:mt-8">
                    <button type="button"
                        class="bg-brand hover:bg-orange-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg font-semibold flex items-center gap-1 sm:gap-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 text-xs sm:text-sm md:text-base"
                        @click="nextStep" :disabled="!isStep1Valid || stepLoading">
                        <div v-if="stepLoading"
                            class="animate-spin rounded-full h-4 w-4 sm:h-5 sm:w-5 border-b-2 border-white mr-1 sm:mr-2">
                        </div>
                        <span v-if="!stepLoading">Próximo</span>
                        <span v-if="!stepLoading" class="text-sm sm:text-lg">›</span>
                    </button>
                </div>
            </div>

            <!-- Step 2: Endereço -->
            <div v-if="currentStep === 2" class="w-full max-w-4xl px-2 sm:px-0">
                <div class="text-sm sm:text-base md:text-lg text-gray-800 font-semibold mb-3 sm:mb-4 text-center">
                    Endereço do Munícipe</div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 md:gap-5 mb-4 sm:mb-6">
                    <div class="flex flex-col">
                        <label for="provincia"
                            class="text-left text-xs sm:text-sm font-medium text-gray-700 mb-1">Província</label>
                        <select id="provincia" v-model="formData.provincia"
                            class="w-full py-2 px-3 sm:px-4 bg-gray-100 border border-gray-200 rounded-lg outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-xs sm:text-sm md:text-base"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.provincia }"
                            required>
                            <option value="">Selecione...</option>
                            <option v-for="province in provinces" :key="province" :value="province">{{ province }}</option>
                        </select>
                        <p v-if="errors.provincia" class="text-red-500 text-xs mt-1 text-left">{{ errors.provincia }}
                        </p>
                    </div>

                    <div class="flex flex-col">
                        <label for="distrito"
                            class="text-left text-xs sm:text-sm font-medium text-gray-700 mb-1">Distrito</label>
                        <select id="distrito" v-model="formData.distrito"
                            class="w-full py-2 px-3 sm:px-4 bg-gray-100 border border-gray-200 rounded-lg outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-xs sm:text-sm md:text-base"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.distrito }"
                            required>
                            <option value="">Selecione...</option>
                            <option v-for="district in availableDistricts" :key="district" :value="district">{{ district }}</option>
                        </select>
                    </div>

                    <div class="flex flex-col">
                        <label for="bairro"
                            class="text-left text-xs sm:text-sm font-medium text-gray-700 mb-1">Bairro</label>
                        <select id="bairro" v-model="formData.bairro"
                            class="w-full py-2 px-3 sm:px-4 bg-gray-100 border border-gray-200 rounded-lg outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-xs sm:text-sm md:text-base"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.bairro }"
                            required>
                            <option value="">Selecione...</option>
                            <option v-for="neighborhood in availableNeighborhoods" :key="neighborhood" :value="neighborhood">{{ neighborhood }}</option>
                        </select>
                        <p v-if="errors.distrito" class="text-red-500 text-xs mt-1 text-left">{{ errors.distrito }}</p>
                    </div>

                    

                    <div class="flex flex-col">
                        <label for="rua" class="text-left text-xs sm:text-sm font-medium text-gray-700 mb-1">Rua</label>
                        <input type="text" id="rua" v-model="formData.rua" placeholder="Digite a sua rua (opcional)"
                            class="w-full py-2 px-3 sm:px-4 bg-gray-100 border border-gray-200 rounded-lg outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-xs sm:text-sm md:text-base"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.rua }">
                        <p v-if="errors.rua" class="text-red-500 text-xs mt-1 text-left">{{ errors.rua }}</p>
                    </div>
                </div>

                <div class="flex justify-between mt-6 sm:mt-8 gap-2 sm:gap-0">
                    <button type="button"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg font-semibold flex items-center gap-1 sm:gap-2 transition-all duration-200 active:scale-95 text-xs sm:text-sm md:text-base"
                        @click="previousStep" :disabled="stepLoading">
                        <span class="text-sm sm:text-lg">‹</span>
                        Anterior
                    </button>
                    <button type="button"
                        class="bg-brand hover:bg-orange-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg font-semibold flex items-center gap-1 sm:gap-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 text-xs sm:text-sm md:text-base"
                        @click="nextStep" :disabled="!isStep2Valid || stepLoading">
                        <div v-if="stepLoading"
                            class="animate-spin rounded-full h-4 w-4 sm:h-5 sm:w-5 border-b-2 border-white mr-1 sm:mr-2">
                        </div>
                        <span v-if="!stepLoading">Próximo</span>
                        <span v-if="!stepLoading" class="text-sm sm:text-lg">›</span>
                    </button>
                </div>
            </div>

            <!-- Step 3: Documento -->
            <div v-if="currentStep === 3" class="w-full max-w-4xl px-2 sm:px-0">
                <div class="text-sm sm:text-base md:text-lg text-gray-800 font-semibold mb-3 sm:mb-4 text-center">
                    Documento (Opcional)</div>
                <div class="text-xs sm:text-sm text-gray-600 mb-4 sm:mb-6 text-center px-2">
                    Anexe documentos relevantes se desejar (PDF, DOC, imagens).
                </div>

                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 sm:p-6 md:p-8 text-center bg-gray-50 cursor-pointer transition-all duration-300 hover:border-brand hover:bg-orange-50 mx-2 sm:mx-0"
                    @click="triggerFileInput" @dragover.prevent @drop.prevent @drop="handleDrop">
                    <CloudArrowUpIcon
                        class="w-10 h-10 sm:w-12 sm:h-12 md:w-16 md:h-16 text-brand mx-auto mb-3 sm:mb-4" />
                    <div class="text-xs sm:text-sm text-gray-600 mb-2 leading-relaxed">
                        Arraste para esta área todos documentos relevantes ou clique para selecionar
                    </div>
                    <input ref="fileInput" type="file" class="hidden" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                        @change="handleFileSelect">
                </div>

                <div class="mt-3 sm:mt-4 space-y-2 mx-2 sm:mx-0">
                    <div v-for="(file, index) in uploadedFiles" :key="index"
                        class="flex items-center justify-between p-2 sm:p-3 bg-gray-50 border border-gray-200 rounded-lg">
                        <div class="flex items-center gap-2 min-w-0 flex-1">
                            <DocumentIcon class="w-4 h-4 sm:w-5 sm:h-5 text-gray-500 flex-shrink-0" />
                            <span class="text-xs sm:text-sm text-gray-800 truncate">{{ file.name }}</span>
                        </div>
                        <button @click="removeFile(index)"
                            class="text-red-500 hover:text-red-700 transition-colors flex-shrink-0 ml-2">
                            <XMarkIcon class="w-3 h-3 sm:w-4 sm:h-4" />
                        </button>
                    </div>
                </div>

                <div class="flex justify-between mt-6 sm:mt-8 gap-2 sm:gap-0">
                    <button type="button"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg font-semibold flex items-center gap-1 sm:gap-2 transition-all duration-200 active:scale-95 text-xs sm:text-sm md:text-base"
                        @click="previousStep" :disabled="loading">
                        <span class="text-sm sm:text-lg">‹</span>
                        Anterior
                    </button>
                    <button type="button"
                        class="bg-brand hover:bg-orange-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg font-semibold flex items-center gap-1 sm:gap-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 text-xs sm:text-sm md:text-base"
                        @click="completeRegistration" :disabled="loading">
                        <div v-if="loading"
                            class="animate-spin rounded-full h-4 w-4 sm:h-5 sm:w-5 border-b-2 border-white mr-1 sm:mr-2">
                        </div>
                        <span v-if="!loading">Finalizar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { useToast } from '@/Composables/useToast'
import { useMozambiqueLocations } from '@/composables/useMozambiqueLocations'
import {
    UserIcon,
    MapPinIcon,
    DocumentTextIcon,
    CloudArrowUpIcon,
    DocumentIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'

const { error, warning } = useToast()

const props = defineProps({
    basicData: {
        type: Object,
        required: true
    },
    errors: {
        type: Object,
        default: () => ({})
    }
})

const currentStep = ref(1)
const uploadedFiles = ref([])
const stepLoading = ref(false)
const inertiaLoading = ref(false)
const showSuccessPopup = ref(false)
const showRedirectMessage = ref(false)
const registrationCompleted = ref(false)

const formData = ref({
    nome: '',
    apelido: '',
    celular: '',
    provincia: '',
    distrito: '',
    bairro: '',
    rua: ''
})

const fileInput = ref(null)

// Usar composable dinâmico de localizações
const { provinces, getDistrictsForProvince, getNeighborhoodsForDistrict, isValidDistrictForProvince } = useMozambiqueLocations()

// Computed para distritos da província selecionada
const availableDistricts = computed(() => {
    console.log('CompleteRegistration - Computing available districts for province:', formData.value.provincia)
    return getDistrictsForProvince(formData.value.provincia)
})

// Computed para bairros do distrito selecionado
const availableNeighborhoods = computed(() => {
    console.log('CompleteRegistration - Computing available neighborhoods for province:', formData.value.provincia, 'district:', formData.value.distrito)
    return getNeighborhoodsForDistrict(formData.value.provincia, formData.value.distrito)
})

const progressWidth = computed(() => {
    if (currentStep.value === 1) return '0%'
    if (currentStep.value === 2) return '35%'
    if (currentStep.value === 3) return '70%'
    return '0%'
})

const isStep1Valid = computed(() => {
    const celularValid = formData.value.celular.replace(/\D/g, '').length === 12
    return formData.value.nome && formData.value.apelido && celularValid
})

// Watch para limpar distrito quando província muda
watch(() => formData.value.provincia, (newProvince, oldProvince) => {
    console.log('CompleteRegistration - Province changed from', oldProvince, 'to', newProvince)
    if (newProvince !== oldProvince && newProvince) {
        console.log('CompleteRegistration - Clearing district and neighborhood')
        formData.value.distrito = ''
        formData.value.bairro = ''
    }
})

// Watch para limpar bairro quando distrito muda
watch(() => formData.value.distrito, (newDistrict, oldDistrict) => {
    console.log('CompleteRegistration - District changed from', oldDistrict, 'to', newDistrict)
    if (newDistrict !== oldDistrict && newDistrict) {
        console.log('CompleteRegistration - Clearing neighborhood')
        formData.value.bairro = ''
    }
})

const isStep2Valid = computed(() => {
    const basicFields = formData.value.provincia && formData.value.distrito && formData.value.bairro

    // Validação adicional: garantir que distrito pertence à província e bairro ao distrito
    const locationValid = (!formData.value.provincia || !formData.value.distrito ||
                          isValidDistrictForProvince(formData.value.provincia, formData.value.distrito)) &&
                         (!formData.value.distrito || !formData.value.bairro ||
                          availableNeighborhoods.value.includes(formData.value.bairro))

    console.log('CompleteRegistration - Step 2 validation:', {
        basicFields,
        locationValid,
        provincia: formData.value.provincia,
        distrito: formData.value.distrito,
        bairro: formData.value.bairro,
        availableDistricts: availableDistricts.value.length,
        availableNeighborhoods: availableNeighborhoods.value.length
    })

    return basicFields && locationValid
})

const nextStep = async () => {
    stepLoading.value = true
    await new Promise(resolve => setTimeout(resolve, 800))

    if (currentStep.value < 3) {
        currentStep.value++
    }

    stepLoading.value = false
}

const previousStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--
    }
}

const formatPhoneNumber = (event) => {
    let value = event.target.value.replace(/\D/g, '')

    if (value.startsWith('258')) {
        value = value.substring(3)
    }

    if (value.length > 0) {
        if (value[0] !== '8') {
            value = '8' + value.substring(1)
        }

        if (value.length > 1) {
            const validSecondDigits = ['2', '3', '4', '5', '6', '7']
            if (!validSecondDigits.includes(value[1])) {
                value = value[0] + (value[1] || '')
            }
        }

        value = value.substring(0, 9)

        let formattedValue = '+258 '
        if (value.length > 0) {
            formattedValue += value[0]
            if (value.length > 1) {
                formattedValue += value[1]
            }
            if (value.length > 2) {
                formattedValue += ' ' + value.substring(2, 5)
            }
            if (value.length > 5) {
                formattedValue += ' ' + value.substring(5, 8)
            }
            if (value.length > 8) {
                formattedValue += ' ' + value.substring(8, 9)
            }
        }

        formData.value.celular = formattedValue
    }
}

const preventInvalidInput = (event) => {
    const allowedKeys = [8, 9, 13, 27, 37, 38, 39, 40, 46]

    if (allowedKeys.includes(event.keyCode)) {
        return
    }

    if (event.ctrlKey && [65, 67, 86, 88].includes(event.keyCode)) {
        return
    }

    if (!/[0-9]/.test(event.key)) {
        event.preventDefault()
    }
}

const triggerFileInput = () => {
    fileInput.value.click()
}

const handleFileSelect = (event) => {
    handleFiles(event.target.files)
}

const handleDrop = (event) => {
    event.preventDefault()
    handleFiles(event.dataTransfer.files)
}

const handleFiles = (files) => {
    Array.from(files).forEach(file => {
        uploadedFiles.value.push(file)
    })
}

const removeFile = (index) => {
    uploadedFiles.value.splice(index, 1)
}

const completeRegistration = async () => {
    // Validação final do celular
    const phoneDigits = formData.value.celular.replace(/\D/g, '')
    if (phoneDigits.length !== 12 || !phoneDigits.startsWith('258')) {
        console.warn('Por favor, insira um número de celular válido de Moçambique')
        return
    }

    const actualPhone = phoneDigits.substring(3)
    if (actualPhone[0] !== '8' || !['2', '3', '4', '5', '6', '7'].includes(actualPhone[1])) {
        console.warn('Número de celular inválido. O número deve seguir o formato: +258 8X XXX XXXX')
        return
    }

    // Prepara os dados para envio
    const completeData = new FormData()

    // Adiciona os dados do formulário
    Object.keys(formData.value).forEach(key => {
        if (formData.value[key]) {
            completeData.append(key, formData.value[key])
        }
    })

    // Adiciona os dados básicos
    completeData.append('email', props.basicData.email)
    completeData.append('username', props.basicData.username)
    completeData.append('password', props.basicData.password)

    // Adiciona os documentos
    uploadedFiles.value.forEach(file => {
        completeData.append('documents[]', file)
    })

    // Usa o router do Inertia para fazer a submissão
    router.post('/register/complete', completeData, {
        onStart: () => {
            inertiaLoading.value = true
        },
        onSuccess: (page) => {
            // Registro bem-sucedido - mostra popup de sucesso
            inertiaLoading.value = false
            showSuccessPopup.value = true
            registrationCompleted.value = true

            // Aguarda 3 segundos e redireciona para o dashboard
            setTimeout(() => {
                showSuccessPopup.value = false
                showRedirectMessage.value = true
                inertiaLoading.value = true

                // Redireciona após mostrar a mensagem de redirecionamento
                setTimeout(() => {
                    router.visit('/home')
                }, 1000)
            }, 3000)
        },
        onError: (errors) => {
            inertiaLoading.value = false
            console.error('Erro no registro:', errors)

            // Mostra o primeiro erro encontrado
            if (errors && Object.keys(errors).length > 0) {
                const firstError = errors[Object.keys(errors)[0]]
                console.error(`Erro: ${firstError}`)
            } else {
                console.error('Erro ao completar registro. Verifique os dados e tente novamente.')
            }
        },
        onFinish: () => {
            // Só desativa o loading se não estiver no processo de redirecionamento
            if (!registrationCompleted.value) {
                inertiaLoading.value = false
            }
        }
    })
}

onMounted(() => {
    // Pre-fill with any existing data if available
})
</script>
