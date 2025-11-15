<template>
    <div class="bg-white flex justify-center items-center flex-col text-center h-full py-0 px-4 md:px-12">
        <!-- Header -->
        <div class="text-center mb-6">
            <img src="/images/Emblem_of_Mozambique.svg-2.png" alt="Ícone de autenticação"
                class="h-20 w-20 md:h-24 md:w-24 mt-4 md:mt-0 mx-auto object-contain" />
            <span class="text-base md:text-lg font-semibold block mt-3 md:mt-4 tracking-wide">Mecanismo De Diálogo,
                Queixas E
                Reclamações</span>
        </div>

        <h2 class="text-lg md:text-xl mb-4"><strong>Complete seu Registro</strong></h2>

        <!-- Progress Steps -->
        <div class="progress-steps w-full max-w-lg mb-8">
            <div class="progress-line"></div>
            <div class="progress-line-active" :style="{ width: progressWidth }"></div>

            <div class="step" :class="{ active: currentStep === 1, completed: currentStep > 1 }">
                <div class="step-circle">
                    <UserIcon class="w-6 h-6" />
                </div>
                <div class="step-label">Dados Pessoais</div>
            </div>

            <div class="step" :class="{ active: currentStep === 2, completed: currentStep > 2 }">
                <div class="step-circle">
                    <MapPinIcon class="w-6 h-6" />
                </div>
                <div class="step-label">Endereço</div>
            </div>

            <div class="step" :class="{ active: currentStep === 3, completed: currentStep > 3 }">
                <div class="step-circle">
                    <DocumentTextIcon class="w-6 h-6" />
                </div>
                <div class="step-label">Documento</div>
            </div>
        </div>

        <!-- Step 1: Dados Pessoais -->
        <div v-if="currentStep === 1" class="form-section w-full max-w-lg">
            <div class="section-title">Dados do Munícipe</div>
            <div class="section-description text-sm text-gray-600 mb-6">
                Complete seu perfil para acessar informações detalhadas sobre os projetos em andamento no seu bairro.
                Aproveite esta oportunidade para sugerir melhorias, apresentar queixas ou compartilhar ideias que possam
                contribuir para o desenvolvimento da sua comunidade. Sua participação é fundamental para alcançarmos
                resultados ainda melhores!
            </div>

            <div class="form-grid-2x2">
                <div class="form-group">
                    <label for="email" class="text-left text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" :value="props.basicData.email" placeholder="Email"
                        class="w-full py-3 px-4 bg-gray-50 border border-gray-200 my-1 outline-none text-sm md:text-base rounded cursor-not-allowed"
                        readonly>
                </div>

                <div class="form-group">
                    <label for="nome" class="text-left text-sm font-medium text-gray-700">Nome</label>
                    <input type="text" id="nome" v-model="formData.nome" placeholder="Digite o seu nome" class="w-full py-3 px-4 bg-gray-100 border border-transparent my-1 outline-none
                        focus:border-[#F15F22] focus:ring-2 focus:ring-[#F15F22]/30
                        transition-all duration-200 text-sm md:text-base rounded"
                        :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.nome }" required>
                    <p v-if="errors.nome" class="text-red-500 text-xs mt-1 text-left">{{ errors.nome }}</p>
                </div>

                <div class="form-group">
                    <label for="apelido" class="text-left text-sm font-medium text-gray-700">Apelido</label>
                    <input type="text" id="apelido" v-model="formData.apelido" placeholder="Digite o seu apelido" class="w-full py-3 px-4 bg-gray-100 border border-transparent my-1 outline-none
                        focus:border-[#F15F22] focus:ring-2 focus:ring-[#F15F22]/30
                        transition-all duration-200 text-sm md:text-base rounded"
                        :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.apelido }" required>
                    <p v-if="errors.apelido" class="text-red-500 text-xs mt-1 text-left">{{ errors.apelido }}</p>
                </div>

                <div class="form-group">
                    <label for="celular" class="text-left text-sm font-medium text-gray-700">Celular</label>
                    <input type="tel" id="celular" v-model="formData.celular"
                        placeholder="Digite o seu número de celular" class="w-full py-3 px-4 bg-gray-100 border border-transparent my-1 outline-none
                        focus:border-[#F15F22] focus:ring-2 focus:ring-[#F15F22]/30
                        transition-all duration-200 text-sm md:text-base rounded"
                        :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.celular }"
                        @input="formatPhoneNumber" required>
                    <p v-if="errors.celular" class="text-red-500 text-xs mt-1 text-left">{{ errors.celular }}</p>
                </div>
            </div>

            <div class="button-container mt-8">
                <button type="button" class="btn btn-next" @click="nextStep" :disabled="!isStep1Valid">
                    Próximo
                    <span>›</span>
                </button>
            </div>
        </div>

        <!-- Step 2: Endereço -->
        <div v-if="currentStep === 2" class="form-section w-full max-w-lg">
            <div class="section-title">Endereço do Munícipe</div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="provincia" class="text-left text-sm font-medium text-gray-700">Província</label>
                    <select id="provincia" v-model="formData.provincia" class="w-full py-3 px-4 bg-gray-100 border border-transparent my-1 outline-none
                        focus:border-[#F15F22] focus:ring-2 focus:ring-[#F15F22]/30
                        transition-all duration-200 text-sm md:text-base rounded"
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
                    <p v-if="errors.provincia" class="text-red-500 text-xs mt-1 text-left">{{ errors.provincia }}</p>
                </div>

                <div class="form-group">
                    <label for="distrito" class="text-left text-sm font-medium text-gray-700">Distrito</label>
                    <select id="distrito" v-model="formData.distrito" class="w-full py-3 px-4 bg-gray-100 border border-transparent my-1 outline-none
                        focus:border-[#F15F22] focus:ring-2 focus:ring-[#F15F22]/30
                        transition-all duration-200 text-sm md:text-base rounded"
                        :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.distrito }" required>
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

                <div class="form-group">
                    <label for="bairro" class="text-left text-sm font-medium text-gray-700">Bairro</label>
                    <select id="bairro" v-model="formData.bairro" class="w-full py-3 px-4 bg-gray-100 border border-transparent my-1 outline-none
                        focus:border-[#F15F22] focus:ring-2 focus:ring-[#F15F22]/30
                        transition-all duration-200 text-sm md:text-base rounded"
                        :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.bairro }" required>
                        <option value="">Selecione...</option>
                        <option value="Magoanine">Magoanine</option>
                        <option value="Zimpeto">Zimpeto</option>
                        <option value="Albazine">Albazine</option>
                        <option value="Hulene">Hulene</option>
                        <option value="Mahotas">Mahotas</option>
                    </select>
                    <p v-if="errors.bairro" class="text-red-500 text-xs mt-1 text-left">{{ errors.bairro }}</p>
                </div>

                <div class="form-group">
                    <label for="rua" class="text-left text-sm font-medium text-gray-700">Rua</label>
                    <input type="text" id="rua" v-model="formData.rua" placeholder="Digite a sua rua" class="w-full py-3 px-4 bg-gray-100 border border-transparent my-1 outline-none
                        focus:border-[#F15F22] focus:ring-2 focus:ring-[#F15F22]/30
                        transition-all duration-200 text-sm md:text-base rounded"
                        :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-300': errors.rua }" required>
                    <p v-if="errors.rua" class="text-red-500 text-xs mt-1 text-left">{{ errors.rua }}</p>
                </div>
            </div>

            <div class="button-container mt-8">
                <button type="button" class="btn btn-back" @click="previousStep">
                    <span>‹</span>
                    Voltar
                </button>
                <button type="button" class="btn btn-next" @click="nextStep" :disabled="!isStep2Valid">
                    Próximo
                    <span>›</span>
                </button>
            </div>
        </div>

        <!-- Step 3: Documento -->
        <div v-if="currentStep === 3" class="form-section w-full max-w-lg">
            <div class="section-title">Documento (Opcional)</div>
            <div class="section-description text-sm text-gray-600 mb-6">
                Anexe documentos relevantes se desejar (PDF, DOC, imagens).
            </div>

            <div class="upload-area" @click="triggerFileInput" @dragover.prevent @drop.prevent @drop="handleDrop">
                <CloudArrowUpIcon class="upload-icon w-16 h-16 text-[#F15F22] mb-4" />
                <div class="upload-text text-sm text-gray-600 mb-2">Arraste para esta área todos documentos relevantes
                    ou clique para selecionar</div>
                <input ref="fileInput" type="file" class="file-input" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                    @change="handleFileSelect">
            </div>

            <div class="file-list mt-4">
                <div v-for="(file, index) in uploadedFiles" :key="index"
                    class="file-item flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded mb-2">
                    <div class="file-info flex items-center gap-2">
                        <DocumentIcon class="file-icon w-5 h-5 text-gray-500" />
                        <span class="file-name text-sm text-gray-800">{{ file.name }}</span>
                    </div>
                    <button @click="removeFile(index)" class="remove-file text-red-500 hover:text-red-700">
                        <XMarkIcon class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <div class="button-container mt-8">
                <button type="button" class="btn btn-back" @click="previousStep">
                    <span>‹</span>
                    Voltar
                </button>
                <button type="button" class="btn btn-next" @click="completeRegistration" :disabled="loading">
                    <span v-if="loading">Finalizando...</span>
                    <span v-else>Finalizar Registro</span>
                </button>
            </div>
        </div>

        <p class="text-xs md:text-sm text-gray-600 mt-6 text-center">
            <button type="button" @click="$emit('back-to-basic')"
                class="text-[#F15F22] font-medium hover:text-[#e5561a] focus:outline-none">
                ← Voltar para dados básicos
            </button>
        </p>

        <!-- Footer Branding -->
        <div class="mt-8 pt-6 border-t border-gray-200 text-center">
            <p class="text-xs text-gray-500">
                Sistema MDQR - Mecanismo De Diálogo, Queixas e Reclamações
            </p>
            <p class="text-xs text-gray-400 mt-1">
                Governo de Moçambique
            </p>
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

<style scoped>
.progress-steps {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.progress-line {
    position: absolute;
    top: 25px;
    left: 15%;
    right: 15%;
    height: 2px;
    background: #e0e0e0;
    z-index: 0;
}

.progress-line-active {
    position: absolute;
    top: 25px;
    left: 15%;
    width: 0%;
    height: 2px;
    background: #F15F22;
    z-index: 1;
    transition: width 0.3s ease;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
    flex: 1;
}

.step-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: white;
    border: 3px solid #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #999;
    margin-bottom: 10px;
    transition: all 0.3s ease;
}

.step.active .step-circle {
    background: #F15F22;
    border-color: #F15F22;
    color: white;
}

.step.completed .step-circle {
    background: #F15F22;
    border-color: #F15F22;
    color: white;
}

.step-label {
    font-size: 12px;
    color: #666;
    text-align: center;
    max-width: 100px;
}

.section-title {
    font-size: 18px;
    color: #333;
    font-weight: 600;
    margin-bottom: 20px;
    text-align: center;
}

.section-description {
    font-size: 14px;
    color: #666;
    line-height: 1.6;
    margin-bottom: 30px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
    max-width: 100%;
}

.form-grid-2x2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
    max-width: 100%;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.upload-area {
    border: 2px dashed #ccc;
    border-radius: 8px;
    padding: 40px 20px;
    text-align: center;
    background: #fafafa;
    transition: all 0.3s;
    cursor: pointer;
}

.upload-area:hover {
    border-color: #F15F22;
    background: #fef7f5;
}

.button-container {
    display: flex;
    justify-content: space-between;
    margin-top: 40px;
}

.btn {
    padding: 12px 32px;
    border: none;
    border-radius: 4px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-back {
    background: #6b7280;
    color: white;
}

.btn-back:hover {
    background: #4b5563;
}

.btn-next {
    background: #F15F22;
    color: white;
    margin-left: auto;
}

.btn-next:hover {
    background: #e5561a;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn:active {
    transform: scale(0.98);
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .progress-steps {
        flex-direction: column;
        gap: 15px;
    }

    .progress-line,
    .progress-line-active {
        display: none;
    }

    .step {
        flex-direction: row;
        width: 100%;
        justify-content: flex-start;
        gap: 15px;
    }

    .step-circle {
        margin-bottom: 0;
    }

    .step-label {
        text-align: left;
    }

    .form-section {
        padding: 20px;
    }

    .section-title {
        font-size: 16px;
    }

    .section-description {
        font-size: 13px;
    }
}
</style>
