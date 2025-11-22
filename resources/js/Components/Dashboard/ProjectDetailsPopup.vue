<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-dark-secondary rounded-lg shadow-xl w-full max-w-4xl max-h-[94vh] overflow-hidden">
            <!-- Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-dark-text-primary">
                    {{ isEditing ? 'Editar Projecto' : 'Detalhes do Projecto' }}
                </h2>
                <div class="flex items-center gap-2">
                    <button @click="closePopup"
                        class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                <!-- Loading State -->
                <div v-if="loading" class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand mx-auto"></div>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">A carregar detalhes...</p>
                </div>

                <!-- Project Details -->
                <div v-else-if="project" class="space-y-6">
                    <!-- Project Header -->
                    <div class="flex flex-col lg:flex-row gap-6">
                        <!-- Image -->
                        <div class="lg:w-1/3">
                            <div class="relative">
                                <img :src="project?.image_url || '/images/default-project.png'" :alt="project?.name"
                                    class="w-full h-48 lg:h-64 object-cover rounded-lg shadow-md" />
                                <div v-if="isEditing"
                                    class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center rounded-lg">
                                    <button @click="triggerImageUpload"
                                        class="bg-white dark:bg-dark-accent text-gray-800 dark:text-dark-text-primary px-4 py-2 rounded font-semibold flex items-center gap-2">
                                        <PhotoIcon class="w-4 h-4" />
                                        Alterar Imagem
                                    </button>
                                    <input type="file" ref="imageInput" @change="handleImageChange" class="hidden"
                                        accept="image/*" />
                                </div>
                            </div>
                        </div>

                        <!-- Basic Info -->
                        <div class="lg:w-2/3 space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Nome do
                                    Projecto</label>
                                <input v-if="isEditing" v-model="editForm.name" type="text"
                                    class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-brand focus:border-brand focus:ring-0 dark:bg-dark-accent dark:text-dark-text-primary">
                                <p v-else class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary">{{
                                    project?.name || 'N/A' }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Categoria</label>
                                    <select v-if="isEditing" v-model="editForm.category"
                                        class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-brand focus:border-brand focus:ring-0 dark:bg-dark-accent dark:text-dark-text-primary">
                                        <option value="andamento">Em Andamento</option>
                                        <option value="parados">Parados</option>
                                        <option value="finalizados">Finalizados</option>
                                    </select>
                                    <span v-else
                                        :class="['px-3 py-1 text-xs font-semibold rounded-full', getStatusClass(project?.category)]">
                                        {{ getStatusLabel(project?.category) }}
                                    </span>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Data
                                        de
                                        Criação</label>
                                    <input v-if="isEditing" v-model="editForm.data_criacao" type="date"
                                        class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-brand focus:border-brand focus:ring-0 dark:bg-dark-accent dark:text-dark-text-primary">
                                    <p v-else class="text-gray-600 dark:text-gray-400">{{
                                        formatDate(project?.data_criacao) }}</p>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Localização</label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                    <input v-if="isEditing" v-model="editForm.bairro" type="text" placeholder="Bairro"
                                        class="p-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-brand focus:border-brand focus:ring-0 dark:bg-dark-accent dark:text-dark-text-primary">
                                    <input v-if="isEditing" v-model="editForm.distrito" type="text"
                                        placeholder="Distrito"
                                        class="p-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-brand focus:border-brand focus:ring-0 dark:bg-dark-accent dark:text-dark-text-primary">
                                    <input v-if="isEditing" v-model="editForm.provincia" type="text"
                                        placeholder="Província"
                                        class="p-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-brand focus:border-brand focus:ring-0 dark:bg-dark-accent dark:text-dark-text-primary">
                                    <template v-else>
                                        <span class="text-gray-600 dark:text-gray-400">{{ project?.bairro || 'N/A' }},
                                            {{ project?.distrito
                                                || 'N/A' }}, {{
                                                project?.provincia || 'N/A' }}</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label
                            class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Descrição</label>
                        <textarea v-if="isEditing" v-model="editForm.description" rows="4"
                            class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-brand focus:border-brand focus:ring-0 dark:bg-dark-accent dark:text-dark-text-primary"
                            placeholder="Descrição do projecto..."></textarea>
                        <p v-else class="text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ project?.description
                            || 'N/A' }}</p>
                    </div>

                    <!-- Objectives -->
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary">Objectivos</h3>
                            <button v-if="isEditing" @click="addObjective"
                                class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm flex items-center gap-1">
                                <PlusIcon class="w-4 h-4" />
                                Adicionar
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div v-for="(objective, index) in (isEditing ? editForm.objectives : (project?.objectives || []))"
                                :key="index" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-semibold text-gray-700 dark:text-gray-300">Objectivo {{ index + 1 }}
                                    </h4>
                                    <button v-if="isEditing && editForm.objectives.length > 1"
                                        @click="removeObjective(index)"
                                        class="text-red-500 hover:text-red-700 dark:hover:text-red-400">
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <label
                                            class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Título</label>
                                        <input v-if="isEditing" v-model="objective.title" type="text"
                                            class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-brand focus:border-brand focus:ring-0 dark:bg-dark-accent dark:text-dark-text-primary">
                                        <p v-else class="font-medium text-gray-800 dark:text-dark-text-primary">{{
                                            objective?.title || 'N/A'
                                            }}</p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Descrição</label>
                                        <textarea v-if="isEditing" v-model="objective.description" rows="2"
                                            class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-brand focus:border-brand focus:ring-0 dark:bg-dark-accent dark:text-dark-text-primary"></textarea>
                                        <p v-else class="text-gray-600 dark:text-gray-400">{{ objective?.description ||
                                            'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty state para objetivos -->
                            <div v-if="!isEditing && (!project?.objectives || project.objectives.length === 0)"
                                class="text-center py-4 text-gray-500 dark:text-gray-400">
                                Nenhum objectivo definido
                            </div>
                        </div>
                    </div>

                    <!-- Finance Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-4">
                                Financiamento</h3>
                            <div class="space-y-3">
                                <div>
                                    <label
                                        class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Financiador</label>
                                    <input v-if="isEditing" v-model="editForm.finance.financiador" type="text"
                                        class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-brand focus:border-brand focus:ring-0 dark:bg-dark-accent dark:text-dark-text-primary">
                                    <p v-else class="text-gray-800 dark:text-dark-text-primary">{{
                                        project?.finance?.financiador || 'N/A' }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Beneficiário</label>
                                    <input v-if="isEditing" v-model="editForm.finance.beneficiario" type="text"
                                        class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-brand focus:border-brand focus:ring-0 dark:bg-dark-accent dark:text-dark-text-primary">
                                    <p v-else class="text-gray-800 dark:text-dark-text-primary">{{
                                        project?.finance?.beneficiario || 'N/A'
                                        }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Valor
                                        Financiado</label>
                                    <input v-if="isEditing" v-model="editForm.finance.valor_financiado" type="text"
                                        class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-brand focus:border-brand focus:ring-0 dark:bg-dark-accent dark:text-dark-text-primary">
                                    <p v-else class="text-gray-800 dark:text-dark-text-primary">{{
                                        project?.finance?.valor_financiado ||
                                        'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary mb-4">Prazos</h3>
                            <div class="space-y-3">
                                <div v-for="dateField in dateFields" :key="dateField.key">
                                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{
                                        dateField.label }}</label>
                                    <input v-if="isEditing" v-model="editForm.deadline[dateField.key]" type="date"
                                        class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-brand focus:border-brand focus:ring-0 dark:bg-dark-accent dark:text-dark-text-primary">
                                    <p v-else class="text-gray-800 dark:text-dark-text-primary">{{
                                        formatDate(project?.deadline?.[dateField.key]) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="!loading" class="text-center py-8">
                    <p class="text-red-600 dark:text-red-400">Erro ao carregar detalhes do projecto.</p>
                    <button @click="loadProjectDetails(props.projectId)"
                        class="mt-4 bg-brand hover:bg-orange-600 text-white px-4 py-2 rounded">
                        Tentar Novamente
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <div
                class="flex justify-between items-center p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-dark-accent">
                <div>
                    <button v-if="isEditing" @click="cancelEditing"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded font-semibold transition-colors">
                        Cancelar
                    </button>
                </div>

                <div class="flex gap-3">
                    <button v-if="!isEditing && project && canEdit" @click="startEditing"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm font-semibold flex items-center gap-2 transition-colors">
                        <PencilIcon class="w-4 h-4" />
                        Editar
                    </button>

                    <button v-if="!isEditing && project && canEdit" @click="deleteProject"
                        class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded font-semibold flex items-center gap-2 transition-colors">
                        <TrashIcon class="w-4 h-4" />
                        Eliminar
                    </button>

                    <button v-if="isEditing && canEdit" @click="saveProject" :disabled="saving"
                        class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded font-semibold flex items-center gap-2 transition-colors disabled:opacity-50">
                        <CheckIcon class="w-4 h-4" />
                        {{ saving ? 'A guardar...' : 'Guardar' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import {
    XMarkIcon,
    PencilIcon,
    TrashIcon,
    CheckIcon,
    PlusIcon,
    PhotoIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    show: Boolean,
    projectId: Number,
    canEdit: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['close', 'project-updated', 'project-deleted'])

// Estado do componente
const loading = ref(false)
const isEditing = ref(false)
const saving = ref(false)
const project = ref(null)
const imageInput = ref(null)

// Formulário de edição
const editForm = reactive({
    name: '',
    description: '',
    category: '',
    provincia: '',
    distrito: '',
    bairro: '',
    data_criacao: '',
    objectives: [],
    finance: {
        financiador: '',
        beneficiario: '',
        responsavel: '',
        valor_financiado: '',
        codigo: ''
    },
    deadline: {
        data_aprovacao: '',
        data_inicio: '',
        data_inspecao: '',
        data_finalizacao: '',
        data_inauguracao: ''
    }
})

// Campos de data
const dateFields = [
    { key: 'data_aprovacao', label: 'Data de Aprovação' },
    { key: 'data_inicio', label: 'Data de Início' },
    { key: 'data_inspecao', label: 'Data de Inspecção' },
    { key: 'data_finalizacao', label: 'Data de Finalização' },
    { key: 'data_inauguracao', label: 'Data de Inauguração' }
]

// Watcher para carregar dados quando o projectId muda
watch(() => props.projectId, async (newId) => {
    if (newId) {
        await loadProjectDetails(newId)
    }
}, { immediate: true })

// Carregar detalhes do projeto
const loadProjectDetails = async (projectId) => {
    if (!projectId) return

    loading.value = true
    try {
        const response = await axios.get(`/api/projects/${projectId}`)
        project.value = response.data
    } catch (error) {
        console.error('Erro ao carregar detalhes do projecto:', error)
        project.value = null
    } finally {
        loading.value = false
    }
}

// Iniciar edição
const startEditing = () => {
    if (!project.value) return

    isEditing.value = true
    // Preencher formulário de edição com dados atuais
    Object.assign(editForm, {
        name: project.value.name || '',
        description: project.value.description || '',
        category: project.value.category || '',
        provincia: project.value.provincia || '',
        distrito: project.value.distrito || '',
        bairro: project.value.bairro || '',
        data_criacao: project.value.data_criacao || '',
        objectives: project.value.objectives ? [...project.value.objectives] : [],
        finance: project.value.finance ? { ...project.value.finance } : {
            financiador: '',
            beneficiario: '',
            responsavel: '',
            valor_financiado: '',
            codigo: ''
        },
        deadline: project.value.deadline ? { ...project.value.deadline } : {
            data_aprovacao: '',
            data_inicio: '',
            data_inspecao: '',
            data_finalizacao: '',
            data_inauguracao: ''
        }
    })
}

// Cancelar edição
const cancelEditing = () => {
    isEditing.value = false
}

// Salvar alterações
const saveProject = async () => {
    if (!props.projectId) return

    saving.value = true
    try {
        const formData = new FormData()

        // Adicionar CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        if (csrfToken) {
            formData.append('_token', csrfToken)
        }

        // Adicionar método PUT para Laravel
        formData.append('_method', 'PUT')

        // Adicionar campos básicos que foram alterados
        const basicFields = ['name', 'description', 'category', 'provincia', 'distrito', 'bairro', 'data_criacao']
        basicFields.forEach(field => {
            if (editForm[field] !== undefined && editForm[field] !== null && editForm[field] !== '') {
                formData.append(field, editForm[field])
            }
        })

        // Adicionar imagem se foi selecionada uma nova
        if (imageInput.value?.files[0]) {
            formData.append('image', imageInput.value.files[0])
        }

        // Adicionar objectivos
        if (editForm.objectives && editForm.objectives.length > 0) {
            editForm.objectives.forEach((obj, index) => {
                if (obj.title && obj.description) {
                    formData.append(`objectives[${index}][title]`, obj.title)
                    formData.append(`objectives[${index}][description]`, obj.description)
                    if (obj.id) {
                        formData.append(`objectives[${index}][id]`, obj.id)
                    }
                }
            })
        }

        // Adicionar financiamento - CORREÇÃO: campos diretos, não aninhados
        if (editForm.finance) {
            const financeFields = ['financiador', 'beneficiario', 'responsavel', 'valor_financiado', 'codigo']
            financeFields.forEach(field => {
                if (editForm.finance[field] && editForm.finance[field] !== '') {
                    formData.append(field, editForm.finance[field])
                }
            })
        }

        // Adicionar prazos - CORREÇÃO: campos diretos, não aninhados
        if (editForm.deadline) {
            const deadlineFields = ['data_aprovacao', 'data_inicio', 'data_inspecao', 'data_finalizacao', 'data_inauguracao']
            deadlineFields.forEach(field => {
                if (editForm.deadline[field] && editForm.deadline[field] !== '') {
                    formData.append(field, editForm.deadline[field])
                }
            })
        }

        console.log('Enviando dados para actualização...')

        const response = await axios.post(`/api/projects/${props.projectId}`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })

        if (response.data.success) {
            // Atualizar o projeto localmente
            project.value = response.data.project
            isEditing.value = false

            // Emitir o projeto actualizado
            emit('project-updated', response.data.project)

            alert('Projecto actualizado com sucesso!')
        } else {
            throw new Error(response.data.message || 'Erro ao actualizar projecto')
        }
    } catch (error) {
        console.error('Erro ao actualizar projecto:', error)
        if (error.response?.data?.errors) {
            alert('Erro ao actualizar projecto: ' + JSON.stringify(error.response.data.errors))
        } else {
            alert('Erro ao actualizar projecto: ' + error.message)
        }
    } finally {
        saving.value = false
    }
}

// Eliminar projeto
const deleteProject = async () => {
    if (!props.projectId) return

    if (confirm('Tem certeza que deseja eliminar este projecto? Esta acção não pode ser desfeita.')) {
        try {
            await axios.delete(`/api/projects/${props.projectId}`)
            emit('project-deleted', props.projectId)
            closePopup()
            alert('Projecto eliminado com sucesso!')
        } catch (error) {
            console.error('Erro ao eliminar projecto:', error)
            alert('Erro ao eliminar projecto')
        }
    }
}

// Funções para objetivos
const addObjective = () => {
    editForm.objectives.push({ title: '', description: '' })
}

const removeObjective = (index) => {
    editForm.objectives.splice(index, 1)
}

// Funções para imagem
const triggerImageUpload = () => {
    imageInput.value?.click()
}

const handleImageChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        // Aqui você pode implementar o upload da imagem
        console.log('Nova imagem selecionada:', file)
        // Adicionar ao formData quando salvar
    }
}

// Função para formatar data no formato dd/MM/yyyy
const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
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

// Funções auxiliares
const closePopup = () => {
    isEditing.value = false
    project.value = null
    emit('close')
}

const getStatusClass = (category) => {
    if (!category) return 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300'

    const classes = {
        'finalizados': 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300',
        'andamento': 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300',
        'parados': 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300'
    }
    return classes[category] || 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300'
}

const getStatusLabel = (category) => {
    if (!category) return 'N/A'

    const labels = {
        'finalizados': 'Finalizado',
        'andamento': 'Em Andamento',
        'parados': 'Parado'
    }
    return labels[category] || category
}
</script>

<style scoped></style>