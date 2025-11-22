<template>
    <div class="max-w-6xl mx-auto bg-white p-8 rounded-xl shadow-lg">
        <div v-if="notification.show" :class="['fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300',
            notification.success ? 'bg-green-500 text-white' : 'bg-red-500 text-white']">
            <div class="flex items-center gap-3">
                <div class="flex-1">
                    <p class="font-semibold">{{ notification.success ? '✅ Sucesso!' : '❌ Erro!' }}</p>
                    <p>{{ notification.message }}</p>
                    <p v-if="notification.errors" class="text-sm mt-1">
                        <span v-for="(error, field) in notification.errors" :key="field">
                            {{ field }}: {{ error.join(', ') }}
                        </span>
                    </p>
                </div>
                <button @click="notification.show = false" class="text-white hover:text-gray-200">
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>
        </div>

        <div class="flex justify-between items-center mb-8">
            <div class="text-lg font-semibold text-brand">
                Passo {{ currentStep }} de 4
            </div>
        </div>

        <!-- Progress Bar Melhorada -->
        <div class="mb-10">
            <div class="flex justify-between mb-4">
                <div v-for="step in steps" :key="step.number" class="text-center flex-1 relative">
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2 transition-all duration-300"
                            :class="currentStep >= step.number
                                ? 'bg-brand text-white shadow-lg shadow-orange-200'
                                : 'bg-gray-200 text-gray-500'">
                            <span class="font-bold text-sm">{{ step.number }}</span>
                        </div>
                        <div class="text-sm font-semibold px-2 h-4"
                            :class="currentStep >= step.number ? 'text-brand' : 'text-gray-500'">
                            {{ step.title }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 -mt-7 mx-10">
                <div class="bg-gradient-to-r from-orange-400 to-orange-600 h-3 rounded-full transition-all duration-500 ease-out"
                    :style="`width: ${((currentStep - 1) / 3) * 100}%`"></div>
            </div>
        </div>

        <form @submit.prevent="submitForm" enctype="multipart/form-data" class="space-y-8">
            <!-- ETAPA 1: Dados Básicos -->
            <div v-if="currentStep === 1" class="space-y-8">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Dados Básicos do Projecto</h2>
                    <p class="text-gray-600">Informe os dados fundamentais do seu projecto</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block font-semibold text-gray-700 mb-2 text-md ">Nome do Projecto *</label>
                        <input v-model="form.name" type="text"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            placeholder=" Digite o nome completo do projecto" required>
                    </div>

                    <div class="space-y-2">
                        <label class="block font-semibold text-gray-700 mb-2 text-md">Categoria *</label>
                        <select v-model="form.category"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            required>
                            <option value="">Selecione a categoria</option>
                            <option value="andamento">Em Andamento</option>
                            <option value="parados">Parados</option>
                            <option value="finalizados">Finalizados</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block font-semibold text-gray-700 mb-2 text-md">Província *</label>
                        <input v-model="form.provincia" type="text"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounderd outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            placeholder="Ex: Maputo, Gaza, Inhambane..." required>
                    </div>

                    <div class="space-y-2">
                        <label class="block font-semibold text-gray-700 mb-2 text-md">Distrito *</label>
                        <input v-model="form.distrito" type="text"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            placeholder="Ex: Matola, Boane, Marracuene..." required>
                    </div>

                    <div class="space-y-2">
                        <label class="block font-semibold text-gray-700 mb-2 text-md">Bairro *</label>
                        <input v-model="form.bairro" type="text"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            placeholder="Ex: Machava, Zimpeto, Khongolote..." required>
                    </div>

                    <div class="space-y-2">
                        <label class="block font-semibold text-gray-700 mb-2 text-md">Data de Criação *</label>
                        <input v-model="form.data_criacao" type="date"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            required>
                    </div>

                    <div class="lg:col-span-2 space-y-2">
                        <label class="block font-semibold text-gray-700 mb-2 text-md">Descrição do Projecto *</label>
                        <textarea v-model="form.description"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            rows="4"
                            placeholder="Descreva detalhadamente o objectivo, alcance e importância do projecto..."
                            required></textarea>
                    </div>

                    <div class="lg:col-span-2 space-y-2">
                        <label class="block font-semibold text-gray-700 mb-2 text-md">Imagem do Projecto</label>

                        <!-- Área de Upload Melhorada com Drag & Drop -->
                        <div @drop.prevent="handleDrop" @dragover.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false" :class="[
                                'border-2 border-dashed rounded p-6 text-center transition-all duration-200 cursor-pointer',
                                dragOver
                                    ? 'border-brand bg-orange-50'
                                    : imagePreview
                                        ? 'border-green-400 bg-green-50'
                                        : 'border-gray-300 hover:border-brand'
                            ]" @click="$refs.fileInput.click()">
                            <input type="file" @change="onImageChange" class="hidden" ref="fileInput" accept="image/*">

                            <div v-if="!imagePreview" class="flex flex-col items-center">
                                <PhotoIcon class="w-12 h-12 text-gray-400 mb-3" />
                                <span class="text-md font-medium text-gray-700">
                                    Clique ou arraste uma imagem aqui
                                </span>
                                <span class="text-sm text-gray-500 mt-1">PNG, JPG, GIF até 5MB</span>
                            </div>

                            <div v-else class="flex flex-col items-center">
                                <div class="relative">
                                    <img :src="imagePreview" class="w-32 h-32 object-cover rounded-lg shadow-md" />
                                    <button type="button" @click.stop="removeImage"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors">
                                        <XMarkIcon class="w-4 h-4" />
                                    </button>
                                </div>
                                <p class="text-green-600 text-sm mt-2 font-medium">
                                    ✓ Imagem selecionada: {{ form.image.name }}
                                </p>
                                <p class="text-gray-500 text-xs mt-1">
                                    Clique para alterar ou arraste uma nova imagem
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ETAPA 2: Objectivos -->
            <div v-if="currentStep === 2" class="space-y-8">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Objectivos do Projecto</h2>
                    <p class="text-gray-600">Defina os objectivos e metas do seu projecto</p>
                </div>

                <div class="space-y-6">
                    <div v-for="(objective, index) in form.objectives" :key="index"
                        class="bg-gray-50 border border-gray-200 p-6 rounded-xl hover:shadow-md transition-all">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-gray-800 text-md"> Objectivo {{ index + 1 }}</h3>
                            <button v-if="form.objectives.length > 1" type="button" @click="removeObjective(index)"
                                class="btn-danger text-sm">
                                <TrashIcon class="w-4 h-4" />
                                Remover
                            </button>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block font-semibold text-gray-700 mb-2">Título do Objectivo *</label>
                                <input v-model="objective.title" type="text"
                                    class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                                    placeholder=" Ex: Produção de Energia Renovável" required>
                            </div>

                            <div class="lg:col-span-2 space-y-2">
                                <label class="block font-semibold text-gray-700 mb-2">Descrição Detalhada *</label>
                                <textarea v-model="objective.description"
                                    class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                                    rows="3"
                                    placeholder="Descreva em detalhe este objectivo, incluindo métricas e resultados esperados..."
                                    required></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" @click="addObjective" class="btn-secondary-large  py-4 text-md">
                    <PlusIcon class="w-5 h-5" />
                    Adicionar Novo Objectivo
                </button>
            </div>

            <!-- ETAPA 3: Financiamento -->
            <div v-if="currentStep === 3" class="space-y-8">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Informações de Financiamento</h2>
                    <p class="text-gray-600">Detalhes sobre o financiamento e recursos do projecto</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block font-semibold text-gray-700 mb-2 text-md">Financiador *</label>
                        <input v-model="form.financiador" type="text"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            placeholder="Ex: Enabel, Banco Mundial, Governo..." required>
                    </div>

                    <div class="space-y-2">
                        <label class="block font-semibold text-gray-700 mb-2 text-md">Beneficiário *</label>
                        <input v-model="form.beneficiario" type="text"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            placeholder="Ex: Município de Pemba, Comunidade Local..." required>
                    </div>

                    <div class="space-y-2">
                        <label class="block font-semibold text-gray-700 mb-2 text-md">Responsável *</label>
                        <input v-model="form.responsavel" type="text"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            placeholder="Ex: FUNAE, FP, INIR, IP..." required>
                    </div>

                    <div class="space-y-2">
                        <label class="block font-semibold text-gray-700 mb-2 text-md">Valor Financiado *</label>
                        <input v-model="form.valor_financiado" type="text"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            placeholder="Ex: USD 15,6 Milhões, MT 25 Milhões..." required>
                    </div>

                    <div class="lg:col-span-2 space-y-2">
                        <label class="block font-semibold text-gray-700 mb-2 text-md">Código do Projecto *</label>
                        <input v-model="form.codigo" type="text"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            placeholder="Ex: #2024/ENABEL/FP, #PROJ/2024/001" required>
                    </div>
                </div>
            </div>

            <!-- ETAPA 4: Prazos -->
            <div v-if="currentStep === 4" class="space-y-8">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Cronograma e Prazos</h2>
                    <p class="text-gray-600">Defina as datas importantes do cronograma do projecto</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block font-semibold text-gray-700 mb-2 text-md">Data de Aprovação *</label>
                        <input v-model="form.data_aprovacao" type="date"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            required>
                    </div>

                    <div class="space-y-2">
                        <label class="block font-semibold text-gray-700 mb-2 text-md">Data de Início *</label>
                        <input v-model="form.data_inicio" type="date"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            required>
                    </div>

                    <div class="space-y-2">
                        <label class="block font-semibold text-gray-700 mb-2 text-md">Data de Inspecção *</label>
                        <input v-model="form.data_inspecao" type="date"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            required>
                    </div>

                    <div class="space-y-2">
                        <label class="block font-semibold text-gray-700 mb-2 text-md">Data de Finalização *</label>
                        <input v-model="form.data_finalizacao" type="date"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            required>
                    </div>

                    <div class="lg:col-span-2 space-y-2">
                        <label class="block font-semibold text-gray-700 mb-2 text-md">Data de Inauguração *</label>
                        <input v-model="form.data_inauguracao" type="date"
                            class="w-full py-2 px-4 bg-gray-100 border border-gray-200 rounded outline-none focus:ring-0 focus:border-brand transition-all duration-200 text-sm md:text-base"
                            required>
                    </div>
                </div>

                <!-- Resumo do Projecto -->
                <div class="mt-8 p-6 bg-gray-100 rounded border border-blue-200">
                    <h3 class="font-bold text-2xl text-gray-800 mb-4 text-center">Resumo do Projecto</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4 text-base">
                        <div class="bg-white p-4 rounded shadow-sm">
                            <strong class="text-brand">Nome:</strong>
                            <div class="mt-1 text-gray-700">{{ form.name || 'Não definido' }}</div>
                        </div>
                        <div class="bg-white p-4 rounded shadow-sm">
                            <strong class="text-brand">Categoria:</strong>
                            <div class="mt-1 text-gray-700">{{ getCategoryLabel(form.category) || 'Não definida' }}
                            </div>
                        </div>
                        <div class="bg-white p-4 rounded shadow-sm">
                            <strong class="text-brand">Localização:</strong>
                            <div class="mt-1 text-gray-700">{{ form.bairro }}, {{ form.distrito }}, {{ form.provincia }}
                            </div>
                        </div>
                        <div class="bg-white p-4 rounded shadow-sm">
                            <strong class="text-brand">Financiador:</strong>
                            <div class="mt-1 text-gray-700">{{ form.financiador || 'Não definido' }}</div>
                        </div>
                        <div class="bg-white p-4 rounded shadow-sm">
                            <strong class="text-brand">Valor:</strong>
                            <div class="mt-1 text-gray-700">{{ form.valor_financiado || 'Não definido' }}</div>
                        </div>
                        <div class="bg-white p-4 rounded shadow-sm">
                            <strong class="text-brand">Objectivos:</strong>
                            <div class="mt-1 text-gray-700">{{ form.objectives.length }} definidos</div>
                        </div>
                        <div class="bg-white p-4 rounded shadow-sm lg:col-span-3">
                            <strong class="text-brand">Cronograma:</strong>
                            <div class="mt-1 text-gray-700 grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                <div><strong>Aprovação:</strong> {{ formatDate(form.data_aprovacao) || 'Não definida' }}
                                </div>
                                <div><strong>Início:</strong> {{ formatDate(form.data_inicio) || 'Não definida' }}</div>
                                <div><strong>Inspecção:</strong> {{ formatDate(form.data_inspecao) || 'Não definida' }}
                                </div>
                                <div><strong>Finalização:</strong> {{ formatDate(form.data_finalizacao) || 'Não definida' }}</div>
                                <div><strong>Inauguração:</strong> {{ formatDate(form.data_inauguracao) || 'Não definida' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navegação entre Etapas -->
            <div class="flex justify-between items-center mt-12 pt-8 border-t border-gray-200">
                <button type="button" v-if="currentStep > 1" @click="previousStep"
                    class="btn-secondary-large text-md px-8 py-3">
                    Voltar
                </button>
                <div v-else class="w-32"></div>

                <div class="flex gap-4">
                    <button type="button" @click="$emit('cancel')" class="btn-gray-large text-md px-8 py-3">
                        Cancelar
                    </button>

                    <button type="button" v-if="currentStep < 4" @click="nextStep"
                        class="btn-primary-large text-md px-8 py-3">
                        Continuar
                    </button>

                    <button type="submit" v-if="currentStep === 4" :disabled="loading"
                        class="btn-success-large text-md px-8 py-3 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span v-if="loading">Processando...</span>
                        <span v-else>Finalizar Registo</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
    PlusIcon,
    XMarkIcon,
    PhotoIcon,
    TrashIcon
} from '@heroicons/vue/24/outline'

const emit = defineEmits(['project-created', 'cancel'])

// Estado do formulário
const currentStep = ref(1)
const loading = ref(false)
const dragOver = ref(false)
const fileInput = ref(null)

const steps = [
    { number: 1, title: 'Dados Básicos' },
    { number: 2, title: 'Objectivos' },
    { number: 3, title: 'Financiamento' },
    { number: 4, title: 'Prazos' }
]

// Notification system
const notification = ref({
    show: false,
    success: false,
    message: '',
    errors: null
})

// Preview da imagem
const imagePreview = computed(() => {
    if (form.value.image && form.value.image instanceof File) {
        return URL.createObjectURL(form.value.image)
    }
    return null
})

// Função para formatar data no formato dd/MM/yyyy
const formatDate = (dateString) => {
    if (!dateString) return 'Não definida'
    try {
        const date = new Date(dateString)

        // Verificar se a data é válida
        if (isNaN(date.getTime())) {
            return 'Data inválida'
        }

        // Formatar para dd/MM/yyyy
        const day = String(date.getDate()).padStart(2, '0')
        const month = String(date.getMonth() + 1).padStart(2, '0') // Meses são 0-based
        const year = date.getFullYear()

        return `${day}/${month}/${year}`
    } catch (error) {
        return 'Data inválida'
    }
}

const showNotification = (success, message, errors = null) => {
    notification.value = {
        show: true,
        success,
        message,
        errors
    }

    // Auto-hide after 5 seconds
    setTimeout(() => {
        notification.value.show = false
    }, 5000)
}

const form = ref({
    name: "",
    description: "",
    image: null,
    provincia: "",
    distrito: "",
    bairro: "",
    category: "",
    data_criacao: "",

    objectives: [
        { title: "", description: "" }
    ],

    financiador: "",
    beneficiario: "",
    responsavel: "",
    valor_financiado: "",
    codigo: "",

    data_aprovacao: "",
    data_inicio: "",
    data_inspecao: "",
    data_finalizacao: "",
    data_inauguracao: "",
})

// Navegação entre etapas
const nextStep = () => {
    if (validateCurrentStep()) {
        currentStep.value++
    }
}

const previousStep = () => {
    currentStep.value--
}

// Validação por etapa
const validateCurrentStep = () => {
    switch (currentStep.value) {
        case 1:
            if (!form.value.name || !form.value.category || !form.value.provincia ||
                !form.value.distrito || !form.value.bairro || !form.value.data_criacao ||
                !form.value.description) {
                showNotification(false, 'Por favor, preencha todos os campos obrigatórios da etapa 1.')
                return false
            }
            break

        case 2:
            for (let objective of form.value.objectives) {
                if (!objective.title || !objective.description) {
                    showNotification(false, 'Por favor, preencha todos os campos dos objectivos.')
                    return false
                }
            }
            break

        case 3:
            if (!form.value.financiador || !form.value.beneficiario || !form.value.responsavel ||
                !form.value.valor_financiado || !form.value.codigo) {
                showNotification(false, 'Por favor, preencha todos os campos de financiamento.')
                return false
            }
            break

        case 4:
            if (!form.value.data_aprovacao || !form.value.data_inicio || !form.value.data_inspecao ||
                !form.value.data_finalizacao || !form.value.data_inauguracao) {
                showNotification(false, 'Por favor, preencha todas as datas do cronograma.')
                return false
            }
            break
    }
    return true
}

// Funções do formulário
const addObjective = () => {
    form.value.objectives.push({ title: "", description: "" })
}

const removeObjective = (index) => {
    if (form.value.objectives.length > 1) {
        form.value.objectives.splice(index, 1)
    }
}

// Funções de manipulação de imagem
const handleDrop = (e) => {
    dragOver.value = false
    const files = e.dataTransfer.files
    if (files.length > 0) {
        processImageFile(files[0])
    }
}

const onImageChange = (e) => {
    if (e.target.files && e.target.files[0]) {
        processImageFile(e.target.files[0])
    }
}

const processImageFile = (file) => {
    // Validar tipo de arquivo
    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
    if (!validTypes.includes(file.type)) {
        showNotification(false, 'Por favor, selecione uma imagem válida (JPEG, PNG, GIF, WebP).')
        return
    }

    // Validar tamanho do arquivo (5MB)
    const maxSize = 5 * 1024 * 1024 // 5MB em bytes
    if (file.size > maxSize) {
        showNotification(false, 'A imagem deve ter no máximo 5MB.')
        return
    }

    form.value.image = file
}

const removeImage = () => {
    form.value.image = null
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

const getCategoryLabel = (category) => {
    const labels = {
        'andamento': 'Em Andamento',
        'parados': 'Parados',
        'finalizados': 'Finalizados'
    }
    return labels[category] || category
}

const submitForm = async () => {
    if (!validateCurrentStep()) {
        return
    }

    loading.value = true

    const formData = new FormData()

    // Adicionar CSRF token para evitar erro 403
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (csrfToken) {
        formData.append('_token', csrfToken)
    }

    // Campos do projecto
    Object.keys(form.value).forEach(key => {
        if (key !== "objectives" && form.value[key] !== null) {
            if (key === "image" && form.value[key] instanceof File) {
                formData.append('image', form.value[key])
            } else if (form.value[key] !== null && form.value[key] !== '') {
                formData.append(key, form.value[key])
            }
        }
    })

    // OBJECTIVOS (array)
    form.value.objectives.forEach((obj, i) => {
        formData.append(`objectives[${i}][title]`, obj.title)
        formData.append(`objectives[${i}][description]`, obj.description)
    })

    try {
        const response = await axios.post("/api/projects", formData, {
            headers: {
                "Content-Type": "multipart/form-data",
                "X-Requested-With": "XMLHttpRequest"
            },
        })

        showNotification(true, response.data.message || '✅ Projecto criado com sucesso!')
        emit('project-created', response.data.project)

        // Reset form after successful submission
        setTimeout(() => {
            resetForm()
            emit('cancel')
        }, 2000)

    } catch (error) {
        console.error('Erro ao criar projecto:', error)

        if (error.response?.data?.errors) {
            showNotification(false, error.response.data.message || '❌ Erro ao criar projecto.', error.response.data.errors)
        } else {
            showNotification(false, error.response?.data?.message || '❌ Erro ao criar projecto.')
        }
    } finally {
        loading.value = false
    }
}

const resetForm = () => {
    // Limpar preview da imagem
    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value)
    }

    form.value = {
        name: "",
        description: "",
        image: null,
        provincia: "",
        distrito: "",
        bairro: "",
        category: "",
        data_criacao: "",
        objectives: [{ title: "", description: "" }],
        financiador: "",
        beneficiario: "",
        responsavel: "",
        valor_financiado: "",
        codigo: "",
        data_aprovacao: "",
        data_inicio: "",
        data_inspecao: "",
        data_finalizacao: "",
        data_inauguracao: "",
    }
    currentStep.value = 1
    dragOver.value = false
}
</script>

<style scoped>
.btn-primary-large {
    @apply bg-brand hover:bg-orange-600 text-white px-6 py-3 rounded font-semibold transition-all duration-200 shadow-lg hover:shadow-xl flex items-center gap-2;
}

.btn-success-large {
    @apply bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded font-semibold transition-all duration-200 shadow-lg hover:shadow-xl flex items-center gap-2;
}

.btn-secondary-large {
    @apply bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded font-semibold transition-all duration-200 shadow-lg hover:shadow-xl flex items-center gap-2;
}

.btn-gray-large {
    @apply bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded font-semibold transition-all duration-200 shadow-lg hover:shadow-xl flex items-center gap-2;
}

.btn-danger {
    @apply bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded font-medium transition-colors flex items-center gap-1;
}
</style>