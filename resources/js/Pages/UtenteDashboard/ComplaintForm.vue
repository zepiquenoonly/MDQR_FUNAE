<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-[1200px] h-[90vh] flex flex-col">

            <!-- Header -->
            <div class="border-b border-gray-200 p-6 flex justify-between items-center bg-gradient-to-r from-orange-500 to-orange-600">
                <h2 class="text-2xl font-bold text-white flex-1 text-center">Nova Submissão</h2>
                <button @click="$emit('close')" class="text-white hover:text-gray-200 transition-colors ml-4">
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>

            <!-- Step Indicators -->
            <div class="border-b border-gray-200 px-6 py-5 bg-gray-50">
                <div class="flex items-center justify-center space-x-2">
                    <!-- Step 1 -->
                    <div class="flex items-center space-x-2">
                        <div :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all duration-300',
                            currentStep === 1 ? 'bg-orange-500 text-white shadow-lg scale-110' : currentStep > 1 ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600'
                        ]">
                            <DocumentTextIcon class="w-5 h-5" />
                        </div>
                        <span :class="['text-xs font-medium transition-colors hidden sm:inline',
                            currentStep === 1 ? 'text-orange-600' : currentStep > 1 ? 'text-green-600' : 'text-gray-500']">
                            Informações
                        </span>
                    </div>

                    <div :class="['h-1 w-16 rounded transition-all', currentStep > 1 ? 'bg-green-500' : 'bg-gray-300']"></div>

                    <!-- Step 2 -->
                    <div class="flex items-center space-x-2">
                        <div :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all duration-300',
                            currentStep === 2 ? 'bg-orange-500 text-white shadow-lg scale-110' : currentStep > 2 ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600'
                        ]">
                            <MapPinIcon class="w-5 h-5" />
                        </div>
                        <span :class="['text-xs font-medium transition-colors hidden sm:inline',
                            currentStep === 2 ? 'text-orange-600' : currentStep > 2 ? 'text-green-600' : 'text-gray-500']">
                            Localização
                        </span>
                    </div>

                    <div :class="['h-1 w-16 rounded transition-all', currentStep > 2 ? 'bg-green-500' : 'bg-gray-300']"></div>

                    <!-- Step 3 -->
                    <div class="flex items-center space-x-2">
                        <div :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all duration-300',
                            currentStep === 3 ? 'bg-orange-500 text-white shadow-lg scale-110' : 'bg-gray-300 text-gray-600'
                        ]">
                            <PaperClipIcon class="w-5 h-5" />
                        </div>
                        <span :class="['text-xs font-medium transition-colors hidden sm:inline',
                            currentStep === 3 ? 'text-orange-600' : 'text-gray-500']">
                            Evidências
                        </span>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <!-- Step 1: Informações Básicas -->
                <template v-if="currentStep === 1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                        <div class="md:col-span-2 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm text-blue-800">
                                <strong>Importante:</strong> Todas as informações fornecidas serão tratadas com confidencialidade.
                            </p>
                        </div>

                        <!-- Tipo de Reclamação -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Tipo de Reclamação <span class="text-red-500">*</span>
                            </label>
                            <select v-model="formData.type" @change="errors.type = ''"
                                :class="[
                                    'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all',
                                    errors.type ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-orange-500'
                                ]">
                                <option value="complaint">Reclamação</option>
                                <option value="grievance">Queixa</option>
                                <option value="suggestion">Sugestão</option>
                            </select>
                            <p v-if="errors.type" class="text-red-500 text-xs mt-1 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                {{ errors.type }}
                            </p>
                        </div>

                        <!-- Projeto -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Projeto Relacionado (Opcional)</label>
                            <select v-model="formData.project_id" @change="errors.project_id = ''"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <option value="">Selecione um projeto (opcional)</option>
                                <option v-for="project in projects" :key="project.id" :value="project.id">
                                    {{ project.name }} {{ project.location ? '(' + project.location + ')' : '' }}
                                </option>
                            </select>
                            <p class="text-xs text-gray-500">Selecione o projeto relacionado à sua submissão, se aplicável.</p>
                        </div>

                        <!-- Categoria -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Categoria <span class="text-red-500">*</span>
                            </label>
                            <select v-model="formData.category" @change="formData.subcategory = ''; errors.category = ''"
                                :class="[
                                    'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all',
                                    errors.category ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-orange-500'
                                ]">
                                <option value="">Selecione uma categoria</option>
                                <option v-for="(subs, cat) in categories" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                            <p v-if="errors.category" class="text-red-500 text-xs mt-1 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                {{ errors.category }}
                            </p>
                        </div>

                        <!-- Subcategoria -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Subcategoria</label>
                            <select v-model="formData.subcategory" :disabled="!formData.category"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 disabled:bg-gray-100">
                                <option value="">Selecione uma subcategoria</option>
                                <option v-for="sub in (categories[formData.category] || [])" :key="sub" :value="sub">{{ sub }}</option>
                            </select>
                        </div>

                        <!-- Anonimato -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700">Prefere submeter anonimamente?</label>
                            <div class="flex gap-6">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" :value="false" v-model="formData.is_anonymous"
                                        class="mr-3 text-orange-500 focus:ring-orange-500 w-5 h-5" />
                                    <span class="text-gray-700">Não, quero me identificar</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" :value="true" v-model="formData.is_anonymous"
                                        class="mr-3 text-orange-500 focus:ring-orange-500 w-5 h-5" />
                                    <span class="text-gray-700">Sim, prefiro o anonimato</span>
                                </label>
                            </div>
                        </div>

                        <!-- Campos para reclamação anônima -->
                        <template v-if="formData.is_anonymous">
                            <div class="md:col-span-2 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <p class="text-sm text-yellow-800">
                                    <strong>Reclamação Anônima:</strong> Forneça informações de contato para atualizações.
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Nome <span class="text-red-500">*</span></label>
                                <input v-model="formData.contact_name" @input="errors.contact_name = ''" type="text"
                                    :class="[
                                        'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all',
                                        errors.contact_name ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-orange-500'
                                    ]"
                                    placeholder="Como devemos chamá-lo?" />
                                <p v-if="errors.contact_name" class="text-red-500 text-xs mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ errors.contact_name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Email <span class="text-red-500">*</span></label>
                                <input v-model="formData.contact_email" @input="errors.contact_email = ''" type="email"
                                    :class="[
                                        'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all',
                                        errors.contact_email ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-orange-500'
                                    ]"
                                    placeholder="seu@email.com" />
                                <p v-if="errors.contact_email" class="text-red-500 text-xs mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ errors.contact_email }}
                                </p>
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700">Telefone (Opcional)</label>
                                <input v-model="formData.contact_phone" type="tel"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                                    placeholder="+258 XX XXX XXXX" />
                            </div>
                        </template>

                        <!-- Descrição -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Descrição da Reclamação <span class="text-red-500">*</span>
                            </label>
                            <p class="text-xs text-gray-500 mb-2">Descreva detalhadamente a sua reclamação (mínimo 10 caracteres).</p>
                            <textarea v-model="formData.description" @input="errors.description = ''" rows="6"
                                :class="[
                                    'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all',
                                    errors.description ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-orange-500'
                                ]"
                                placeholder="Descreva sua reclamação com o máximo de detalhes possível..."></textarea>
                            <div class="flex justify-between items-center">
                                <p v-if="errors.description" class="text-red-500 text-xs flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ errors.description }}
                                </p>
                                <p class="text-xs text-gray-500">{{ formData.description.length }} caracteres</p>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Step 2: Localização -->
                <template v-else-if="currentStep === 2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                        <div class="md:col-span-2 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm text-blue-800">
                                <strong>Localização:</strong> Informe onde ocorreu o problema para melhor atendimento.
                            </p>
                        </div>

                        <!-- Província -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Província</label>
                            <select v-model="formData.province" @change="formData.district = ''"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <option value="">Selecione a província</option>
                                <option v-for="(districts, prov) in locations" :key="prov" :value="prov">{{ prov }}</option>
                            </select>
                        </div>

                        <!-- Distrito -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Distrito</label>
                            <select v-model="formData.district" :disabled="!formData.province"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 disabled:bg-gray-100">
                                <option value="">Selecione o distrito</option>
                                <option v-for="dist in (locations[formData.province] || [])" :key="dist" :value="dist">{{ dist }}</option>
                            </select>
                        </div>

                        <!-- Detalhes da Localização -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700">Detalhes da Localização</label>
                            <textarea v-model="formData.location_details" rows="4"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                                placeholder="Ex: Rua, bairro, referências, coordenadas GPS, etc."></textarea>
                            <p class="text-xs text-gray-500">Inclua pontos de referência ou coordenadas GPS se possível.</p>
                        </div>
                    </div>
                </template>

                <!-- Step 3: Evidências -->
                <template v-else>
                    <div class="max-w-4xl mx-auto space-y-4">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm text-blue-800">
                                <strong>Evidências:</strong> Adicione fotos, documentos ou outros arquivos que comprovem sua reclamação (opcional).
                            </p>
                        </div>

                        <div @drop.prevent="handleDrop" @dragover.prevent @click="triggerFileInput"
                            class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center bg-white cursor-pointer hover:border-orange-500 hover:bg-orange-50 transition-all">
                            <DocumentArrowUpIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                            <p class="text-base text-gray-700 font-semibold mb-2">Arraste arquivos para esta área ou clique para selecionar</p>
                            <p class="text-sm text-gray-500">Formatos aceitos: PNG, JPG, PDF (máx. 2MB por arquivo)</p>
                            <p class="text-xs text-gray-400 mt-2">Máximo de 5 arquivos</p>
                        </div>

                        <input ref="fileInputRef" type="file" multiple class="hidden" @change="handleFileUpload"
                            accept=".png,.jpg,.jpeg,.pdf" />

                        <div v-if="files.length > 0" class="space-y-2">
                            <h4 class="font-semibold text-gray-700">Arquivos Selecionados ({{ files.length }}/5):</h4>
                            <div v-for="(file, index) in files" :key="index"
                                class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg hover:border-orange-500 transition-all">
                                <div class="flex items-center gap-3">
                                    <DocumentIcon class="w-6 h-6 text-orange-500" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">{{ file.name }}</p>
                                        <p class="text-xs text-gray-500">{{ (file.size / 1024).toFixed(1) }} KB</p>
                                    </div>
                                </div>
                                <button @click.stop="removeFile(index)"
                                    class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded transition-colors">
                                    <XMarkIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-200 p-6 flex justify-between bg-white">
                <button @click="previousStep"
                    class="px-6 py-3 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center gap-2 font-medium">
                    <ArrowLeftIcon class="w-4 h-4" />
                    Voltar
                </button>

                <button v-if="currentStep < 3" @click="nextStep"
                    class="px-6 py-3 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors flex items-center gap-2 font-medium shadow-md">
                    Próximo
                    <ArrowRightIcon class="w-4 h-4" />
                </button>

                <button v-else @click="handleSubmit" :disabled="isSubmitting"
                    class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2 font-medium shadow-md disabled:bg-gray-400 disabled:cursor-not-allowed">
                    <span v-if="isSubmitting">Submetendo...</span>
                    <span v-else>Submeter Reclamação</span>
                    <CheckIcon v-if="!isSubmitting" class="w-5 h-5" />
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import {
    XMarkIcon,
    DocumentTextIcon,
    MapPinIcon,
    PaperClipIcon,
    DocumentArrowUpIcon,
    ArrowLeftIcon,
    ArrowRightIcon,
    CheckIcon,
    DocumentIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    isAnonymous: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['close', 'success'])

const currentStep = ref(1)
const fileInputRef = ref(null)
const isSubmitting = ref(false)

const formData = ref({
    project_id: '',
    type: 'complaint',
    category: '',
    subcategory: '',
    description: '',
    province: '',
    district: '',
    location_details: '',
    is_anonymous: props.isAnonymous,
    contact_name: '',
    contact_email: '',
    contact_phone: ''
})

const files = ref([])
const errors = ref({})
const projects = ref([])

const categories = ref({
    'Serviços Públicos': ['Fornecimento de Energia', 'Qualidade do Serviço', 'Atendimento ao Cliente', 'Faturação'],
    'Infraestrutura': ['Instalação de Equipamentos', 'Manutenção', 'Construção'],
    'Ambiental': ['Impacto Ambiental', 'Poluição', 'Gestão de Resíduos'],
    'Social': ['Reassentamento', 'Compensação', 'Consulta Comunitária'],
    'Administração': ['Processos Administrativos', 'Documentação', 'Outros']
})

const locations = ref({
    'Maputo': ['KaMpfumu', 'Nlhamankulu', 'KaMaxaquene', 'KaMavota', 'KaMubukwana', 'KaTembe', 'Kanyaka'],
    'Gaza': ['Chókwè', 'Chibuto', 'Xai-Xai', 'Manjacaze', 'Bilene'],
    'Inhambane': ['Inhambane', 'Maxixe', 'Vilankulo', 'Massinga', 'Zavala'],
    'Sofala': ['Beira', 'Dondo', 'Nhamatanda', 'Búzi', 'Gorongosa'],
    'Manica': ['Chimoio', 'Gondola', 'Manica', 'Báruè', 'Sussundenga'],
    'Tete': ['Tete', 'Moatize', 'Angónia', 'Cahora-Bassa', 'Changara'],
    'Zambézia': ['Quelimane', 'Mocuba', 'Alto Molócuè', 'Gurúè', 'Milange'],
    'Nampula': ['Nampula', 'Nacala', 'Ilha de Moçambique', 'Angoche', 'Monapo'],
    'Cabo Delgado': ['Pemba', 'Mocímboa da Praia', 'Palma', 'Mueda', 'Montepuez'],
    'Niassa': ['Lichinga', 'Cuamba', 'Mandimba', 'Marrupa', 'Majune']
})

const nextStep = () => {
    if (validateStep()) {
        currentStep.value++
    }
}

const previousStep = () => {
    if (currentStep.value === 1) {
        emit('close')
    } else {
        currentStep.value--
    }
}

const validateStep = () => {
    errors.value = {}

    if (currentStep.value === 1) {
        if (!formData.value.category) {
            errors.value.category = 'Selecione uma categoria'
        }
        if (!formData.value.description || formData.value.description.length < 10) {
            errors.value.description = 'A descrição deve ter pelo menos 10 caracteres'
        }
        if (formData.value.is_anonymous) {
            if (!formData.value.contact_name) {
                errors.value.contact_name = 'Nome é obrigatório para reclamações anônimas'
            }
            if (!formData.value.contact_email) {
                errors.value.contact_email = 'Email é obrigatório para reclamações anônimas'
            }
        }
    }

    return Object.keys(errors.value).length === 0
}

const triggerFileInput = () => {
    if (files.value.length < 5) {
        fileInputRef.value?.click()
    }
}

const handleFileUpload = (event) => {
    const newFiles = Array.from(event.target.files)
    const remainingSlots = 5 - files.value.length
    const filesToAdd = newFiles.slice(0, remainingSlots)

    files.value = [...files.value, ...filesToAdd]
    event.target.value = '' // Reset input
}

const handleDrop = (event) => {
    const newFiles = Array.from(event.dataTransfer.files)
    const remainingSlots = 5 - files.value.length
    const filesToAdd = newFiles.slice(0, remainingSlots)

    files.value = [...files.value, ...filesToAdd]
}

const removeFile = (index) => {
    files.value = files.value.filter((_, i) => i !== index)
}

const fetchProjects = async () => {
    try {
        const response = await fetch('/api/grievances/projects', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        if (response.ok) {
            projects.value = await response.json()
        }
    } catch (error) {
        console.error('Erro ao carregar projetos:', error)
    }
}

// Fetch projects on component mount
onMounted(() => {
    fetchProjects()
})

const handleSubmit = async () => {
    if (!validateStep()) {
        return
    }

    isSubmitting.value = true
    errors.value = {}

    try {
        const formDataToSend = new FormData()

        // Adicionar dados do formulário
        Object.keys(formData.value).forEach(key => {
            let value = formData.value[key]
            if (value !== null && value !== '' && value !== undefined) {
                // Converter boolean para "1" ou "0" para compatibilidade com Laravel
                if (typeof value === 'boolean') {
                    value = value ? '1' : '0'
                }
                formDataToSend.append(key, value)
            }
        })

        // Adicionar arquivos
        files.value.forEach((file, index) => {
            formDataToSend.append(`attachments[${index}]`, file)
        })

        console.log('Enviando reclamação...', {
            category: formData.value.category,
            is_anonymous: formData.value.is_anonymous,
            description_length: formData.value.description?.length || 0,
            filesCount: files.value.length,
            contact_name: formData.value.contact_name,
            contact_email: formData.value.contact_email
        })

        const response = await fetch('/api/grievances', {
            method: 'POST',
            body: formDataToSend,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })

        console.log('Status da resposta:', response.status, response.statusText)

        // Tentar parsear a resposta
        let data
        const contentType = response.headers.get('content-type')

        if (contentType && contentType.includes('application/json')) {
            data = await response.json()
        } else {
            const text = await response.text()
            console.error('Resposta não é JSON:', text)
            throw new Error('Servidor retornou resposta inválida')
        }

        console.log('Dados recebidos:', data)

        if (response.ok && data.success) {
            console.log('Sucesso! Número:', data.reference_number)
            emit('submitted', data)
        } else {
            console.error('Erro na resposta:', data)

            // Tratar erros de validação
            if (data.errors) {
                errors.value = data.errors
                console.log('Erros de validação:', errors.value)

                // Voltar para o primeiro passo se houver erros
                currentStep.value = 1

                // Scroll para o topo do formulário para ver os erros
                setTimeout(() => {
                    const firstError = document.querySelector('.border-red-500')
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' })
                    }
                }, 100)
            }
        }
    } catch (error) {
        console.error('Erro crítico ao submeter:', error)

        // Mostrar erro genérico no console
        if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
            console.error('Problema de conexão - Servidor pode não estar rodando')
        }
    } finally {
        isSubmitting.value = false
    }
}
</script>

