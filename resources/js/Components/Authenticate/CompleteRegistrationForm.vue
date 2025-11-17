<template>
    <div class="min-h-[70vh] w-full">
        <!-- Container branco central sem sombra -->
        <div class="bg-white flex justify-center items-center flex-col text-center py-6 px-4 md:px-12">
            <!-- Conteúdo do formulário (mantido igual) -->
            <h1 class="text-lg md:text-xl mb-4 font-bold">Bem-vindo ao mecanismo de diálogo, queixas e
                reclamações (MDQR)</h1>

            <!-- Progress Steps Corrigido -->
            <div class="flex justify-between items-center relative w-full max-w-4xl mt-4 mb-6">
                <!-- Linha de fundo cinza -->
                <div class="absolute top-4 left-[10%] right-[10%] h-0.5 bg-gray-300 z-0"></div>

                <!-- Linha de progresso brand -->
                <div class="absolute top-4 left-[10%] h-0.5 bg-brand z-10 transition-all duration-300"
                    :style="{ width: progressWidth }"></div>

                <!-- Step 1 - CORRIGIDO -->
                <div class="flex flex-col items-center relative z-20 flex-1 -mt-3">
                    <div class="w-16 h-16 rounded-full border-2 flex items-center justify-center mb-2 transition-all duration-300"
                        :class="currentStep >= 1
                            ? 'border-brand bg-brand text-white'
                            : 'border-gray-300 bg-white text-gray-500'">
                        <UserIcon class="w-8 h-8" />
                    </div>
                    <div class="text-sm text-center max-w-24 transition-colors duration-300"
                        :class="currentStep >= 1 ? 'text-brand font-medium' : 'text-gray-600'">
                        Dados Pessoais
                    </div>
                </div>

                <!-- Step 2 - CORRIGIDO -->
                <div class="flex flex-col items-center relative z-20 flex-1 -ml-8">
                    <div class="w-16 h-16 rounded-full border-2 flex items-center justify-center mb-2 transition-all duration-300 -mt-10 "
                        :class="currentStep >= 2
                            ? 'border-brand bg-brand text-white'
                            : 'border-gray-300 bg-white text-gray-500'">
                        <MapPinIcon class="w-8 h-8" />
                    </div>
                    <div class="text-sm text-center max-w-24 transition-colors duration-300"
                        :class="currentStep >= 2 ? 'text-brand font-medium' : 'text-gray-600'">
                        Endereço
                    </div>
                </div>

                <!-- Step 3 - CORRIGIDO -->
                <div class="flex flex-col items-center relative z-20 flex-1">
                    <div class="w-16 h-16 rounded-full border-2 flex items-center justify-center mb-2 transition-all duration-300 -mt-10"
                        :class="currentStep >= 3
                            ? 'border-brand bg-brand text-white'
                            : 'border-gray-300 bg-white text-gray-500'">
                        <DocumentTextIcon class="w-8 h-8" />
                    </div>
                    <div class="text-sm text-center max-w-24 transition-colors duration-300"
                        :class="currentStep >= 3 ? 'text-brand font-medium' : 'text-gray-600'">
                        Documento
                    </div>
                </div>
            </div>

            <!-- Step 1: Dados Pessoais -->
            <div v-if="currentStep === 1" class="w-full max-w-4xl">
                <div class="text-lg text-gray-800 font-semibold mb-4 text-center">Dados do Munícipe</div>
                <div class="text-sm text-gray-600 mb-6 leading-relaxed text-justify">
                    Preencha o formulário para acessar informações detalhadas sobre os projetos em andamento no seu
                    bairro. Aproveite esta oportunidade para sugerir melhorias, apresentar queixas ou compartilhar
                    ideias que possam contribuir para o desenvolvimento da sua comunidade. Sua participação é
                    fundamental para alcançarmos resultados ainda melhores!
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <div class="flex flex-col">
                        <label for="nome" class="text-left text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" :value="props.basicData.email" placeholder="Email"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded-lg outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            readonly>
                    </div>

                    <div class="flex flex-col">
                        <label for="nome" class="text-left text-sm font-medium text-gray-700 mb-1">Nome</label>
                        <input type="text" id="nome" v-model="formData.nome" placeholder="Digite o seu nome"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded-lg outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.nome }" required>
                        <p v-if="errors.nome" class="text-red-500 text-xs mt-1 text-left">{{ errors.nome }}</p>
                    </div>

                    <div class="flex flex-col">
                        <label for="nome" class="text-left text-sm font-medium text-gray-700 mb-1">Apelido</label>
                        <input type="text" id="apelido" v-model="formData.apelido" placeholder="Digite o seu apelido"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded-lg outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.apelido }"
                            required>
                        <p v-if="errors.apelido" class="text-red-500 text-xs mt-1 text-left">{{ errors.apelido }}</p>
                    </div>

                    <div class="flex flex-col">
                        <label for="nome" class="text-left text-sm font-medium text-gray-700 mb-1">Celular</label>
                        <input type="tel" id="celular" v-model="formData.celular"
                            placeholder="Digite o seu número de celular"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded-lg outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.celular }"
                            @input="formatPhoneNumber" required>
                        <p v-if="errors.celular" class="text-red-500 text-xs mt-1 text-left">{{ errors.celular }}</p>
                    </div>
                </div>

                <div class="flex justify-end mt-8">
                    <button type="button"
                        class="bg-brand hover:bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 text-base"
                        @click="nextStep" :disabled="!isStep1Valid">
                        Próximo
                        <span class="text-lg">›</span>
                    </button>
                </div>
            </div>

            <!-- Step 2: Endereço -->
            <div v-if="currentStep === 2" class="w-full max-w-4xl">
                <div class="text-lg text-gray-800 font-semibold mb-4 text-center">Endereço do Munícipe</div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <div class="flex flex-col">
                        <label for="provincia"
                            class="text-left text-sm font-medium text-gray-700 mb-1">Província</label>
                        <select id="provincia" v-model="formData.provincia"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded-lg outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.provincia }"
                            required>
                            <option value="">Selecione...</option>
                            <option value="Maputo">Maputo</option>
                            <option value="Gaza">Gaza</option>
                            <option value="Inhambane">Inhambane</option>
                            <option value="Sofala">Sofala</option>
                            <option value="Manica">Manica</option>
                            <option value="Tete">Tete</option>
                            <option value="Zambézia">Zambézia</option>
                            <option value="Nampula">Nampula</option>
                            <option value="Niassa">Niassa</option>
                            <option value="Cabo Delgado">Cabo Delgado</option>
                        </select>
                        <p v-if="errors.provincia" class="text-red-500 text-xs mt-1 text-left">{{ errors.provincia }}
                        </p>
                    </div>

                    <div class="flex flex-col">
                        <label for="distrito" class="text-left text-sm font-medium text-gray-700 mb-1">Distrito</label>
                        <select id="distrito" v-model="formData.distrito"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded-lg outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.distrito }"
                            required>
                            <option value="">Selecione...</option>
                            <option value="KaMavota">KaMavota</option>
                            <option value="Nlhamankulu">Nlhamankulu</option>
                            <option value="KaMaxakeni">KaMaxakeni</option>
                            <option value="KaMubukwana">KaMubukwana</option>
                            <option value="KaMpfumo">KaMpfumo</option>
                            <option value="KaTembe">KaTembe</option>
                            <option value="KaNyaka">KaNyaka</option>
                        </select>
                        <p v-if="errors.distrito" class="text-red-500 text-xs mt-1 text-left">{{ errors.distrito }}</p>
                    </div>

                    <div class="flex flex-col">
                        <label for="bairro" class="text-left text-sm font-medium text-gray-700 mb-1">Bairro</label>
                        <select id="bairro" v-model="formData.bairro"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded-lg outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.bairro }"
                            required>
                            <option value="">Selecione...</option>
                            <option value="Magoanine">Magoanine</option>
                            <option value="Zimpeto">Zimpeto</option>
                            <option value="Albazine">Albazine</option>
                            <option value="Hulene">Hulene</option>
                            <option value="Mahotas">Mahotas</option>
                        </select>
                        <p v-if="errors.bairro" class="text-red-500 text-xs mt-1 text-left">{{ errors.bairro }}</p>
                    </div>

                    <div class="flex flex-col">
                        <label for="rua" class="text-left text-sm font-medium text-gray-700 mb-1">Rua</label>
                        <input type="text" id="rua" v-model="formData.rua" placeholder="Digite a sua rua"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded-lg outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.rua }" required>
                        <p v-if="errors.rua" class="text-red-500 text-xs mt-1 text-left">{{ errors.rua }}</p>
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <button type="button"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition-all duration-200 active:scale-95 text-base"
                        @click="previousStep">
                        <span class="text-lg">‹</span>
                        Voltar
                    </button>
                    <button type="button"
                        class="bg-brand hover:bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 text-base"
                        @click="nextStep" :disabled="!isStep2Valid">
                        Próximo
                        <span class="text-lg">›</span>
                    </button>
                </div>
            </div>

            <!-- Step 3: Documento -->
            <div v-if="currentStep === 3" class="w-full max-w-4xl">
                <div class="text-lg text-gray-800 font-semibold mb-4 text-center">Documento (Opcional)</div>
                <div class="text-sm text-gray-600 mb-6">
                    Anexe documentos relevantes se desejar (PDF, DOC, imagens).
                </div>

                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center bg-gray-50 cursor-pointer transition-all duration-300 hover:border-brand hover:bg-orange-50"
                    @click="triggerFileInput" @dragover.prevent @drop.prevent @drop="handleDrop">
                    <CloudArrowUpIcon class="w-16 h-16 text-brand mx-auto mb-4" />
                    <div class="text-sm text-gray-600 mb-2">Arraste para esta área todos documentos relevantes ou
                        clique para selecionar</div>
                    <input ref="fileInput" type="file" class="hidden" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                        @change="handleFileSelect">
                </div>

                <div class="mt-4 space-y-2">
                    <div v-for="(file, index) in uploadedFiles" :key="index"
                        class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-lg">
                        <div class="flex items-center gap-2">
                            <DocumentIcon class="w-5 h-5 text-gray-500" />
                            <span class="text-sm text-gray-800">{{ file.name }}</span>
                        </div>
                        <button @click="removeFile(index)" class="text-red-500 hover:text-red-700 transition-colors">
                            <XMarkIcon class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <button type="button"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition-all duration-200 active:scale-95 text-base"
                        @click="previousStep">
                        <span class="text-lg">‹</span>
                        Voltar
                    </button>
                    <button type="button"
                        class="bg-brand hover:bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 text-base"
                        @click="completeRegistration" :disabled="loading">
                        <span v-if="loading">Finalizando...</span>
                        <span v-else>Finalizar Registro</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import {
    UserIcon,
    MapPinIcon,
    DocumentTextIcon,
    CloudArrowUpIcon,
    DocumentIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    basicData: {
        type: Object,
        required: true
    },
    loading: {
        type: Boolean,
        default: false
    },
    errors: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(['submit', 'back-to-basic'])

const currentStep = ref(1)
const uploadedFiles = ref([])

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

const progressWidth = computed(() => {
    if (currentStep.value === 1) return '0%'
    if (currentStep.value === 2) return '35%'
    if (currentStep.value === 3) return '70%'
    return '0%'
})

const isStep1Valid = computed(() => {
    return formData.value.nome && formData.value.apelido && formData.value.celular
})

const isStep2Valid = computed(() => {
    return formData.value.provincia && formData.value.distrito && formData.value.bairro && formData.value.rua
})

const nextStep = () => {
    if (currentStep.value < 3) {
        currentStep.value++
    }
}

const previousStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--
    }
}

const formatPhoneNumber = (event) => {
    let value = event.target.value.replace(/\D/g, '')

    if (value.length > 0) {
        if (value.length <= 2) {
            value = value
        } else if (value.length <= 5) {
            value = value.slice(0, 2) + ' ' + value.slice(2)
        } else if (value.length <= 8) {
            value = value.slice(0, 2) + ' ' + value.slice(2, 5) + ' ' + value.slice(5)
        } else {
            value = value.slice(0, 2) + ' ' + value.slice(2, 5) + ' ' + value.slice(5, 8) + ' ' + value.slice(8, 11)
        }
    }

    formData.value.celular = value
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

const completeRegistration = () => {
    const completeData = {
        ...props.basicData,
        ...formData.value,
        documents: uploadedFiles.value
    }
    emit('submit', completeData)
}

onMounted(() => {
    // Pre-fill with any existing data if available
})
</script>