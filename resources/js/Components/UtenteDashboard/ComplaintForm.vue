<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-[1100px] h-[90vh] flex flex-col">

            <!-- Header -->
            <div class="border-b border-gray-200 p-6 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800 flex-1 text-center">Descrição da Reclamação</h2>
                <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700 transition-colors ml-4">
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>

            <!-- Step Indicators -->
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-center space-x-4">
                    <div class="flex items-center space-x-3">
                        <div :class="[
                            'w-12 h-12 rounded-full flex items-center justify-center text-sm font-semibold',
                            currentStep === 1 ? 'bg-brand text-white' : 'bg-gray-400 text-white'
                        ]">
                            <DocumentTextIcon class="w-6 h-6" />
                        </div>
                        <span :class="[
                            'text-sm font-medium',
                            currentStep === 1 ? 'text-brand' : 'text-gray-500'
                        ]">
                            Descrição
                        </span>
                    </div>

                    <div class="flex-1 h-0.5 bg-gray-300 max-w-[200px]"></div>

                    <div class="flex items-center space-x-3">
                        <div :class="[
                            'w-12 h-12 rounded-full flex items-center justify-center text-sm font-semibold',
                            currentStep === 2 ? 'bg-brand text-white' : 'bg-gray-400 text-white'
                        ]">
                            <PaperClipIcon class="w-6 h-6" />
                        </div>
                        <span :class="[
                            'text-sm font-medium',
                            currentStep === 2 ? 'text-brand' : 'text-gray-500'
                        ]">
                            Evidências
                        </span>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6">
                <template v-if="currentStep === 1">
                    <div class="grid grid-cols-1 md:grid-cols-10 gap-6 h-full">
                        <!-- Left Column -->
                        <div class="space-y-6 md:col-span-3">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Tipo de impacto</label>
                                <select v-model="formData.impactType"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-0 focus:ring-brand focus:border-brand text-gray-500">
                                    <option value="" disabled>- selecione o impacto -</option>
                                    <option value="ambiental">Impacto Ambiental</option>
                                    <option value="social">Impacto Social</option>
                                    <option value="economico">Desenvolvimento dos projectos</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Prefere anonimato?</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center">
                                        <input type="radio" name="anonimato" value="nao" v-model="formData.anonymous"
                                            class="mr-2 text-brand focus:ring-brand border-gray-300" />
                                        <span class="text-gray-700">Não</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="anonimato" value="sim" v-model="formData.anonymous"
                                            class="mr-2 text-brand focus:ring-brand border-gray-300" />
                                        <span class="text-gray-700">Sim</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Editor -->
                        <div class="space-y-3 md:col-span-7 flex flex-col">
                            <h3 class="font-semibold text-gray-800">Descrição</h3>

                            <!-- Toolbar -->
                            <div
                                class="border border-gray-300 rounded-t-md bg-gray-50 p-2 flex flex-wrap gap-1 items-center">
                                <!-- Paragraph Dropdown -->
                                <select @change="executeCommand('formatBlock', $event.target.value)"
                                    class="px-2 py-1 border border-gray-300 rounded text-sm bg-white focus:outline-none focus:ring-1 focus:ring-brand w-[110px]">
                                    <option value="p">Paragraph</option>
                                    <option value="h1">Heading 1</option>
                                    <option value="h2">Heading 2</option>
                                    <option value="h3">Heading 3</option>
                                </select>

                                <div class="w-px h-6 bg-gray-300 mx-1"></div>

                                <!-- Text Formatting -->
                                <button @click="executeCommand('bold')"
                                    class="p-1.5 hover:bg-gray-200 rounded hover:text-brand transition-colors"
                                    title="Negrito">
                                    <BoldIcon class="w-4 h-4" />
                                </button>
                                <button @click="executeCommand('italic')"
                                    class="p-1.5 hover:bg-gray-200 rounded hover:text-brand transition-colors"
                                    title="Itálico">
                                    <ItalicIcon class="w-4 h-4" />
                                </button>
                                <button @click="insertLink"
                                    class="p-1.5 hover:bg-gray-200 rounded hover:text-brand transition-colors"
                                    title="Link">
                                    <LinkIcon class="w-4 h-4" />
                                </button>

                                <div class="w-px h-6 bg-gray-300 mx-1"></div>

                                <!-- Lists -->
                                <button @click="executeCommand('insertUnorderedList')"
                                    class="p-1.5 hover:bg-gray-200 rounded hover:text-brand transition-colors"
                                    title="Lista não ordenada">
                                    <ListBulletIcon class="w-4 h-4" />
                                </button>
                                <button @click="executeCommand('insertOrderedList')"
                                    class="p-1.5 hover:bg-gray-200 rounded hover:text-brand transition-colors"
                                    title="Lista ordenada">
                                    <!-- SVG para lista ordenada -->
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </button>

                                <div class="w-px h-6 bg-gray-300 mx-1"></div>

                                <!-- Alignment -->
                                <button @click="executeCommand('justifyLeft')"
                                    class="p-1.5 hover:bg-gray-200 rounded hover:text-brand transition-colors"
                                    title="Alinhar à esquerda">
                                    <!-- SVG para alinhar à esquerda -->
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M3 14h18M3 18h18M3 6h18" />
                                    </svg>
                                </button>
                                <button @click="executeCommand('justifyCenter')"
                                    class="p-1.5 hover:bg-gray-200 rounded hover:text-brand transition-colors"
                                    title="Centralizar">
                                    <!-- SVG para centralizar -->
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 6h18M6 12h12M3 18h18" />
                                    </svg>
                                </button>
                                <button @click="executeCommand('justifyRight')"
                                    class="p-1.5 hover:bg-gray-200 rounded hover:text-brand transition-colors"
                                    title="Alinhar à direita">
                                    <!-- SVG para alinhar à direita -->
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 6h18M9 12h12M3 18h18" />
                                    </svg>
                                </button>

                                <div class="w-px h-6 bg-gray-300 mx-1"></div>

                                <!-- Insert -->
                                <button @click="insertImage"
                                    class="p-1.5 hover:bg-gray-200 rounded hover:text-brand transition-colors"
                                    title="Inserir imagem">
                                    <PhotoIcon class="w-4 h-4" />
                                </button>
                                <button @click="executeCommand('formatBlock', 'blockquote')"
                                    class="p-1.5 hover:bg-gray-200 rounded hover:text-brand transition-colors"
                                    title="Citação">
                                    <ChatBubbleLeftRightIcon class="w-4 h-4" />
                                </button>
                                <button @click="insertTable"
                                    class="p-1.5 hover:bg-gray-200 rounded hover:text-brand transition-colors"
                                    title="Inserir tabela">
                                    <TableCellsIcon class="w-4 h-4" />
                                </button>

                                <div class="w-px h-6 bg-gray-300 mx-1"></div>

                                <!-- Undo/Redo -->
                                <button @click="executeCommand('undo')"
                                    class="p-1.5 hover:bg-gray-200 rounded hover:text-brand transition-colors"
                                    title="Desfazer">
                                    <ArrowUturnLeftIcon class="w-4 h-4" />
                                </button>
                                <button @click="executeCommand('redo')"
                                    class="p-1.5 hover:bg-gray-200 rounded hover:text-brand transition-colors"
                                    title="Refazer">
                                    <ArrowUturnRightIcon class="w-4 h-4" />
                                </button>
                            </div>

                            <!-- Editor Area -->
                            <div ref="editorRef" contenteditable="true" @input="updateDescription"
                                class="flex-1 border border-t-0 border-gray-300 rounded-b-md p-3 focus:outline-none focus:ring-1 focus:ring-brand focus:border-transparent min-h-[300px] overflow-y-auto bg-white"
                                :style="{ minHeight: '300px' }">
                                <p class="text-gray-400">Descrição da Reclamação</p>
                            </div>
                        </div>
                    </div>
                </template>

                <template v-else>
                    <div class="space-y-3">
                        <h3 class="font-semibold text-gray-800">Evidências</h3>

                        <div @drop="handleDrop" @dragover.prevent @click="triggerFileInput"
                            class="border border-dashed border-gray-300 rounded-lg p-8 text-center bg-gray-50 cursor-pointer hover:bg-gray-100 transition-colors h-[350px]">
                            <DocumentArrowUpIcon class="w-12 h-12 text-gray-400 mx-auto mb-3 mt-12" />
                            <p class="text-sm text-gray-600 mb-1 mt-12">Arraste para esta área todas evidências
                                relevantes.
                            </p>
                            <p class="text-xs text-gray-500">PNG, JPG, PDF até 10MB</p>
                        </div>

                        <input ref="fileInputRef" type="file" multiple class="hidden" @change="handleFileUpload"
                            accept=".png,.jpg,.jpeg,.pdf" />

                        <div v-if="files.length > 0" class="space-y-2 mt-4">
                            <div v-for="(file, index) in files" :key="index"
                                class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded">
                                <div class="flex items-center gap-2">
                                    <DocumentIcon class="w-5 h-5 text-gray-500" />
                                    <span class="text-sm text-gray-700">{{ file.name }}</span>
                                    <span class="text-xs text-gray-500">({{ (file.size / 1024).toFixed(1) }} KB)</span>
                                </div>
                                <button @click="removeFile(index)"
                                    class="text-red-500 hover:text-red-700 transition-colors">
                                    <XMarkIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-200 p-6 flex justify-between">
                <button @click="currentStep === 2 ? currentStep = 1 : $emit('close')"
                    class="px-6 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors flex items-center gap-2">
                    <ArrowLeftIcon class="w-4 h-4" />
                    Voltar
                </button>

                <button v-if="currentStep === 1" @click="currentStep = 2"
                    class="px-6 py-2 bg-brand text-white rounded-md hover:bg-orange-600 transition-colors flex items-center gap-2">
                    Próximo
                    <ArrowRightIcon class="w-4 h-4" />
                </button>

                <button v-else @click="handleSubmit"
                    class="px-6 py-2 bg-brand text-white rounded-md hover:bg-orange-600 transition-colors flex items-center gap-2">
                    Finalizar registo
                    <CheckIcon class="w-4 h-4" />
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
    PaperClipIcon,
    DocumentArrowUpIcon,
    ArrowLeftIcon,
    ArrowRightIcon,
    CheckIcon,
    DocumentIcon,
    BoldIcon,
    ItalicIcon,
    LinkIcon,
    ListBulletIcon,
    PhotoIcon,
    TableCellsIcon,
    ArrowUturnLeftIcon,
    ArrowUturnRightIcon,
    ChatBubbleLeftRightIcon
} from '@heroicons/vue/24/outline'

const emit = defineEmits(['close', 'submit'])

const currentStep = ref(1)
const editorRef = ref(null)
const fileInputRef = ref(null)

const formData = ref({
    impactType: '',
    anonymous: 'nao',
    description: '',
    evidence: []
})

const files = ref([])

const executeCommand = (command, value = null) => {
    document.execCommand(command, false, value)
    editorRef.value?.focus()
}

const insertTable = () => {
    const rows = prompt('Número de linhas:', '3')
    const cols = prompt('Número de colunas:', '3')
    if (rows && cols) {
        let tableHTML = '<table border="1" style="border-collapse: collapse; width: 100%;"><tbody>'
        for (let i = 0; i < parseInt(rows); i++) {
            tableHTML += '<tr>'
            for (let j = 0; j < parseInt(cols); j++) {
                tableHTML += `<td style="border: 1px solid #ddd; padding: 8px;">&nbsp;</td>`
            }
            tableHTML += '</tr>'
        }
        tableHTML += '</tbody></table>'
        executeCommand('insertHTML', tableHTML)
    }
}

const insertLink = () => {
    const url = prompt('Digite a URL:')
    if (url) {
        executeCommand('createLink', url)
    }
}

const insertImage = () => {
    const url = prompt('Digite a URL da imagem:')
    if (url) {
        executeCommand('insertImage', url)
    }
}

const updateDescription = () => {
    if (editorRef.value) {
        formData.value.description = editorRef.value.innerHTML
    }
}

const triggerFileInput = () => {
    fileInputRef.value?.click()
}

const handleFileUpload = (event) => {
    const newFiles = Array.from(event.target.files)
    files.value = [...files.value, ...newFiles]
}

const handleDrop = (event) => {
    event.preventDefault()
    const newFiles = Array.from(event.dataTransfer.files)
    files.value = [...files.value, ...newFiles]
}

const removeFile = (index) => {
    files.value = files.value.filter((_, i) => i !== index)
}

const handleSubmit = () => {
    const content = editorRef.value?.innerHTML
    emit('submit', {
        ...formData.value,
        files: files.value
    })
}

onMounted(() => {
    // Inicializar o editor com conteúdo vazio
    if (editorRef.value) {
        editorRef.value.innerHTML = '<p class="text-gray-400">Descrição da Reclamação</p>'
    }
})
</script>