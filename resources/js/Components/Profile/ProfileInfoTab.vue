<template>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-semibold text-gray-800">Informações Pessoais</h3>
        </div>

        <!-- Mensagens de Status -->
        <div v-if="$page.props.flash.success" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center space-x-2 text-green-800">
                <CheckCircleIcon class="w-5 h-5" />
                <span class="font-medium">{{ $page.props.flash.success }}</span>
            </div>
        </div>

        <div v-if="form.errors && Object.keys(form.errors).length > 0"
            class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center space-x-2 text-red-800 mb-2">
                <ExclamationTriangleIcon class="w-5 h-5" />
                <span class="font-medium">Por favor, corrija os seguintes erros:</span>
            </div>
            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                <li v-for="error in form.errors" :key="error">{{ error }}</li>
            </ul>
        </div>

        <form @submit.prevent="submitForm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nome Completo</label>
                    <input type="text" v-model="form.name" :class="[
                        'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-0 transition-colors',
                        form.errors.name ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-orange-500 focus:border-brand'
                    ]">
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nome de Utilizador</label>
                    <input type="text" v-model="form.username" :class="[
                        'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-0 transition-colors',
                        form.errors.username ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-orange-500 focus:border-brand'
                    ]">
                    <p v-if="form.errors.username" class="mt-1 text-sm text-red-600">{{ form.errors.username }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" v-model="form.email" :class="[
                        'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-0 transition-colors',
                        form.errors.email ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-orange-500 focus:border-brand'
                    ]">
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                    <input type="tel" v-model="form.phone" :class="[
                        'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-0 transition-colors',
                        form.errors.phone ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-orange-500 focus:border-brand'
                    ]">
                    <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                </div>
            </div>

            <h4 class="text-lg font-semibold text-gray-800 mb-4">Informações de Localização</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Província</label>
                    <select v-model="form.province" :class="[
                        'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-0 transition-colors',
                        form.errors.province ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-orange-500 focus:border-brand'
                    ]">
                        <option value="">Selecionar Província</option>
                        <option value="Maputo">Maputo</option>
                        <option value="Gaza">Gaza</option>
                        <option value="Inhambane">Inhambane</option>
                        <option value="Sofala">Sofala</option>
                        <option value="Manica">Manica</option>
                        <option value="Zambézia">Zambézia</option>
                        <option value="Nampula">Nampula</option>
                        <option value="Cabo Delgado">Cabo Delgado</option>
                        <option value="Niassa">Niassa</option>
                        <option value="Tete">Tete</option>
                    </select>
                    <p v-if="form.errors.province" class="mt-1 text-sm text-red-600">{{ form.errors.province }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Distrito</label>
                    <select v-model="form.district" :class="[
                        'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-0 transition-colors',
                        form.errors.district ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-orange-500 focus:border-brand'
                    ]">
                        <option value="">Selecionar Distrito</option>
                        <option value="Matola">Matola</option>
                        <option value="Boane">Boane</option>
                        <option value="Marracuene">Marracuene</option>
                        <option value="Manhiça">Manhiça</option>
                        <option value="Magude">Magude</option>
                        <option value="Moamba">Moamba</option>
                        <option value="Namaacha">Namaacha</option>
                    </select>
                    <p v-if="form.errors.district" class="mt-1 text-sm text-red-600">{{ form.errors.district }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bairro</label>
                    <input type="text" v-model="form.neighborhood" :class="[
                        'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-0 transition-colors',
                        form.errors.neighborhood ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-orange-500 focus:border-brand'
                    ]">
                    <p v-if="form.errors.neighborhood" class="mt-1 text-sm text-red-600">{{ form.errors.neighborhood }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rua</label>
                    <input type="text" v-model="form.street" :class="[
                        'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-0 transition-colors',
                        form.errors.street ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-orange-500 focus:border-brand'
                    ]">
                    <p v-if="form.errors.street" class="mt-1 text-sm text-red-600">{{ form.errors.street }}</p>
                </div>
            </div>

            <div class="flex justify-end mt-8">
                <button @click="submitForm" :disabled="form.processing" :class="[
                    'px-6 py-2 rounded font-medium transition-colors flex items-center space-x-2 text-white',
                    form.processing
                        ? 'bg-orange-400 cursor-not-allowed'
                        : 'bg-orange-500 hover:bg-orange-600'
                ]">
                    <CheckIcon class="w-4 h-4" />
                    <span>{{ form.processing ? 'A Guardar...' : 'Guardar Alterações' }}</span>
                </button>
            </div>

        </form>
    </div>
</template>

<script setup>
import { watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import {
    CheckIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object
})

// Usar useForm do Inertia para gestão de estado e submissão
const form = useForm({
    name: props.user.name,
    username: props.user.username,
    email: props.user.email,
    phone: props.user.phone,
    province: props.user.province,
    district: props.user.district,
    neighborhood: props.user.neighborhood,
    street: props.user.street
})

// Watch for user prop changes
watch(() => props.user, (newUser) => {
    form.defaults({
        name: newUser.name,
        username: newUser.username,
        email: newUser.email,
        phone: newUser.phone,
        province: newUser.province,
        district: newUser.district,
        neighborhood: newUser.neighborhood,
        street: newUser.street
    })

    form.reset()
}, { deep: true })

const submitForm = () => {
    // Enviar todos os dados em uma única requisição
    form.patch('/profile/info', {
        preserveScroll: true,
        onSuccess: () => {
            // Limpar erros após sucesso
            form.clearErrors()
        }
    })
}
</script>