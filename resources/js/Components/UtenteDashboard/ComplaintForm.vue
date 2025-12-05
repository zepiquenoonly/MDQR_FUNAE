<template>
    <div v-if="visible" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <!-- Toast Notification -->
        <transition name="slide-fade">
            <div v-if="toast.show" :class="[
                'fixed top-5 right-5 z-[100] px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 min-w-[320px] max-w-md',
                toast.type === 'success' ? 'bg-green-500 text-white' :
                toast.type === 'error' ? 'bg-red-500 text-white' :
                toast.type === 'warning' ? 'bg-yellow-500 text-white' : 'bg-blue-500 text-white'
            ]">
                <component :is="toastIcon" class="w-6 h-6 flex-shrink-0" />
                <div class="flex-1">
                    <p class="font-semibold">{{ toast.title }}</p>
                    <p class="text-sm opacity-90">{{ toast.message }}</p>
                </div>
                <button @click="toast.show = false" class="ml-auto hover:opacity-75 p-1">
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>
        </transition>

        <!-- Error Modal (quando submissão falha) -->
        <transition name="zoom">
            <div v-if="showErrorModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-[60]">
                <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md mx-4 text-center transform relative">
                    <!-- Botão X no canto superior direito -->
                    <button @click="closeErrorModal"
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-full hover:bg-gray-100">
                        <XMarkIcon class="w-6 h-6" />
                    </button>

                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <ExclamationTriangleIcon class="w-12 h-12 text-red-500" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Erro na Submissão</h3>
                    <p class="text-gray-600 mb-4">{{ errorModalMessage }}</p>
                    <div v-if="Object.keys(validationErrors).length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-left">
                        <p class="text-sm font-semibold text-red-800 mb-2">Erros encontrados:</p>
                        <ul class="text-sm text-red-700 space-y-1">
                            <li v-for="(error, field) in validationErrors" :key="field" class="flex items-start gap-2">
                                <XCircleIcon class="w-4 h-4 flex-shrink-0 mt-0.5" />
                                <span>{{ Array.isArray(error) ? error[0] : error }}</span>
                            </li>
                        </ul>
                    </div>
                    <button @click="closeErrorModal"
                        class="w-full bg-red-500 text-white py-3 px-6 rounded-lg font-semibold hover:bg-red-600 transition-colors">
                        Tentar Novamente
                    </button>
                </div>
            </div>
        </transition>

        <!-- Success Modal -->
        <transition name="zoom">
            <div v-if="showSuccessModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-[60]">
                <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md mx-4 text-center transform relative">
                    <!-- Botão X no canto superior direito -->
                    <button @click="closeSuccessAndForm"
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-full hover:bg-gray-100">
                        <XMarkIcon class="w-6 h-6" />
                    </button>

                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <CheckCircleIcon class="w-12 h-12 text-green-500" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Submissão Enviada!</h3>
                    <p class="text-gray-600 mb-4">A sua {{ typeLabel }} foi submetida com sucesso.</p>
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
                        <p class="text-sm text-gray-600 mb-1">Código de Rastreio:</p>
                        <p class="text-2xl font-mono font-bold text-orange-600">{{ submissionResult.reference_number }}</p>
                    </div>
                    <p class="text-sm text-gray-500 mb-6">Guarde este código para acompanhar o estado da sua submissão.</p>
                    <button @click="closeSuccessAndForm"
                        class="w-full bg-orange-500 text-white py-3 px-6 rounded-lg font-semibold hover:bg-orange-600 transition-colors">
                        Entendido
                    </button>
                </div>
            </div>
        </transition>

        <!-- Main Form Modal -->
        <transition name="zoom">
            <div v-if="!showSuccessModal && !showErrorModal" class="bg-white rounded-2xl shadow-2xl w-full max-w-[900px] max-h-[90vh] flex flex-col overflow-hidden">

            <!-- Header -->
            <div class="p-6 flex justify-between items-center bg-gradient-to-r from-orange-500 to-orange-600 relative">
                <div class="flex-1 text-center">
                    <h2 class="text-2xl font-bold text-white">Nova Submissão</h2>
                    <p class="text-orange-100 text-sm mt-1">Preencha os dados abaixo para registar a sua submissão</p>
                </div>
                <button @click="$emit('close')" class="absolute right-4 top-4 text-white hover:text-orange-200 transition-colors p-2 rounded-full hover:bg-white/10">
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>

            <!-- Step Indicators -->
            <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-center">
                    <!-- Step 1 -->
                    <div class="flex items-center">
                        <div :class="[
                            'w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg transition-all duration-300 border-2',
                            currentStep === 1 ? 'bg-orange-500 text-white border-orange-500 shadow-lg shadow-orange-200' :
                            currentStep > 1 ? 'bg-green-500 text-white border-green-500' : 'bg-white text-gray-400 border-gray-300'
                        ]">
                            <CheckIcon v-if="currentStep > 1" class="w-6 h-6" />
                            <UserIcon v-else class="w-6 h-6" />
                        </div>
                        <div class="ml-3 hidden sm:block">
                            <p :class="['text-sm font-semibold', currentStep >= 1 ? 'text-gray-900' : 'text-gray-400']">Passo 1</p>
                            <p :class="['text-xs', currentStep === 1 ? 'text-orange-600' : currentStep > 1 ? 'text-green-600' : 'text-gray-400']">Dados Pessoais</p>
                        </div>
                    </div>

                    <div :class="['h-1 w-12 sm:w-20 mx-2 sm:mx-4 rounded transition-all', currentStep > 1 ? 'bg-green-500' : 'bg-gray-300']"></div>

                    <!-- Step 2 -->
                    <div class="flex items-center">
                        <div :class="[
                            'w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg transition-all duration-300 border-2',
                            currentStep === 2 ? 'bg-orange-500 text-white border-orange-500 shadow-lg shadow-orange-200' :
                            currentStep > 2 ? 'bg-green-500 text-white border-green-500' : 'bg-white text-gray-400 border-gray-300'
                        ]">
                            <CheckIcon v-if="currentStep > 2" class="w-6 h-6" />
                            <DocumentTextIcon v-else class="w-6 h-6" />
                        </div>
                        <div class="ml-3 hidden sm:block">
                            <p :class="['text-sm font-semibold', currentStep >= 2 ? 'text-gray-900' : 'text-gray-400']">Passo 2</p>
                            <p :class="['text-xs', currentStep === 2 ? 'text-orange-600' : currentStep > 2 ? 'text-green-600' : 'text-gray-400']">Detalhes</p>
                        </div>
                    </div>

                    <div :class="['h-1 w-12 sm:w-20 mx-2 sm:mx-4 rounded transition-all', currentStep > 2 ? 'bg-green-500' : 'bg-gray-300']"></div>

                    <!-- Step 3 -->
                    <div class="flex items-center">
                        <div :class="[
                            'w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg transition-all duration-300 border-2',
                            currentStep === 3 ? 'bg-orange-500 text-white border-orange-500 shadow-lg shadow-orange-200' : 'bg-white text-gray-400 border-gray-300'
                        ]">
                            <PaperClipIcon class="w-6 h-6" />
                        </div>
                        <div class="ml-3 hidden sm:block">
                            <p :class="['text-sm font-semibold', currentStep >= 3 ? 'text-gray-900' : 'text-gray-400']">Passo 3</p>
                            <p :class="['text-xs', currentStep === 3 ? 'text-orange-600' : 'text-gray-400']">Evidências</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 p-6 overflow-y-auto bg-gray-50">

                <!-- ==================== PASSO 1: DADOS PESSOAIS ==================== -->
                <template v-if="currentStep === 1">
                    <div class="max-w-2xl mx-auto space-y-6">

                        <!-- Toggle Anónimo -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                        <EyeSlashIcon class="w-5 h-5 text-orange-500" />
                                        Modo de Submissão
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">Escolha se deseja se identificar ou manter o anonimato</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <button type="button" @click="formData.is_anonymous = false"
                                    :class="[
                                        'relative flex flex-col items-center p-6 rounded-xl border-2 transition-all duration-200',
                                        !formData.is_anonymous
                                            ? 'border-green-500 bg-green-50 ring-2 ring-green-200'
                                            : 'border-gray-200 hover:border-green-300 hover:bg-green-50/50'
                                    ]">
                                    <div :class="['w-16 h-16 rounded-full flex items-center justify-center mb-3',
                                        !formData.is_anonymous ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-400']">
                                        <UserIcon class="w-8 h-8" />
                                    </div>
                                    <span class="font-bold text-lg text-gray-900">Identificado</span>
                                    <span class="text-xs text-gray-500 text-center mt-1">Os seus dados serão associados à submissão</span>
                                    <div v-if="!formData.is_anonymous" class="absolute top-3 right-3">
                                        <CheckCircleIcon class="w-6 h-6 text-green-500" />
                                    </div>
                                </button>

                                <button type="button" @click="formData.is_anonymous = true"
                                    :class="[
                                        'relative flex flex-col items-center p-6 rounded-xl border-2 transition-all duration-200',
                                        formData.is_anonymous
                                            ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-200'
                                            : 'border-gray-200 hover:border-orange-300 hover:bg-orange-50/50'
                                    ]">
                                    <div :class="['w-16 h-16 rounded-full flex items-center justify-center mb-3',
                                        formData.is_anonymous ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-400']">
                                        <EyeSlashIcon class="w-8 h-8" />
                                    </div>
                                    <span class="font-bold text-lg text-gray-900">Anónimo</span>
                                    <span class="text-xs text-gray-500 text-center mt-1">Submeta sem fornecer dados pessoais</span>
                                    <div v-if="formData.is_anonymous" class="absolute top-3 right-3">
                                        <CheckCircleIcon class="w-6 h-6 text-orange-500" />
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Formulário de Dados Pessoais (visível quando NÃO é anónimo) -->
                        <transition name="fade-slide">
                            <div v-if="!formData.is_anonymous" class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-5">
                                <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <UserIcon class="w-5 h-5 text-blue-600" />
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Seus Dados</h3>
                                        <p class="text-sm text-gray-500">Forneça as informações para contacto</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <!-- Nome -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-semibold text-gray-700">
                                            Nome Completo <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <UserIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                            <input v-model="formData.contact_name" @input="errors.contact_name = ''" type="text"
                                                :class="[
                                                    'w-full pl-10 pr-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all',
                                                    errors.contact_name ? 'border-red-500 focus:ring-red-500 bg-red-50' : 'border-gray-300 focus:ring-orange-500'
                                                ]"
                                                placeholder="Digite o seu nome completo" />
                                        </div>
                                        <p v-if="errors.contact_name" class="flex items-center text-xs text-red-500">
                                            <ExclamationCircleIcon class="w-4 h-4 mr-1" />
                                            {{ errors.contact_name }}
                                        </p>
                                    </div>

                                    <!-- Email -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-semibold text-gray-700">
                                            Email <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <EnvelopeIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                            <input v-model="formData.contact_email" @input="errors.contact_email = ''" type="email"
                                                :class="[
                                                    'w-full pl-10 pr-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all',
                                                    errors.contact_email ? 'border-red-500 focus:ring-red-500 bg-red-50' : 'border-gray-300 focus:ring-orange-500'
                                                ]"
                                                placeholder="seu.email@exemplo.com" />
                                        </div>
                                        <p v-if="errors.contact_email" class="flex items-center text-xs text-red-500">
                                            <ExclamationCircleIcon class="w-4 h-4 mr-1" />
                                            {{ errors.contact_email }}
                                        </p>
                                    </div>

                                    <!-- Telefone -->
                                    <div class="space-y-2 md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700">
                                            Telefone <span class="text-gray-400 font-normal">(opcional)</span>
                                        </label>
                                        <div class="relative">
                                            <PhoneIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                            <input v-model="formData.contact_phone" type="tel"
                                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                                                placeholder="+258 84 XXX XXXX" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </transition>

                        <!-- Mensagem de Anonimato -->
                        <transition name="fade-slide">
                            <div v-if="formData.is_anonymous" class="bg-orange-50 border border-orange-200 rounded-xl p-6">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                                        <ShieldCheckIcon class="w-6 h-6 text-orange-600" />
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-orange-900 mb-1">Submissão Anónima</h4>
                                        <p class="text-sm text-orange-700">
                                            A sua identidade será protegida. Não será necessário fornecer dados pessoais.
                                            No entanto, não poderemos contactá-lo directamente sobre o progresso da sua submissão.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </transition>

                    </div>
                </template>

                <!-- ==================== PASSO 2: DETALHES DA SUBMISSÃO ==================== -->
                <template v-else-if="currentStep === 2">
                    <div class="max-w-2xl mx-auto space-y-6">

                        <!-- Tipo de Submissão -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <TagIcon class="w-5 h-5 text-orange-500" />
                                Tipo de Submissão <span class="text-red-500">*</span>
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <button type="button" @click="formData.type = 'complaint'; errors.type = ''"
                                    :class="[
                                        'relative p-5 rounded-xl border-2 transition-all duration-200 text-center group',
                                        formData.type === 'complaint'
                                            ? 'border-red-500 bg-red-50 ring-2 ring-red-200'
                                            : 'border-gray-200 hover:border-red-300 hover:bg-red-50/50'
                                    ]">
                                    <div :class="['w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3',
                                        formData.type === 'complaint' ? 'bg-red-500 text-white' : 'bg-red-100 text-red-600']">
                                        <ExclamationCircleIcon class="w-7 h-7" />
                                    </div>
                                    <span class="font-bold text-gray-900 block">Reclamação</span>
                                    <span class="text-xs text-gray-500 mt-1 block">Reportar problema</span>
                                    <div v-if="formData.type === 'complaint'" class="absolute top-2 right-2">
                                        <CheckCircleIcon class="w-5 h-5 text-red-500" />
                                    </div>
                                </button>

                                <button type="button" @click="formData.type = 'grievance'; errors.type = ''"
                                    :class="[
                                        'relative p-5 rounded-xl border-2 transition-all duration-200 text-center group',
                                        formData.type === 'grievance'
                                            ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200'
                                            : 'border-gray-200 hover:border-blue-300 hover:bg-blue-50/50'
                                    ]">
                                    <div :class="['w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3',
                                        formData.type === 'grievance' ? 'bg-blue-500 text-white' : 'bg-blue-100 text-blue-600']">
                                        <QuestionMarkCircleIcon class="w-7 h-7" />
                                    </div>
                                    <span class="font-bold text-gray-900 block">Queixa</span>
                                    <span class="text-xs text-gray-500 mt-1 block">Submeter queixa</span>
                                    <div v-if="formData.type === 'grievance'" class="absolute top-2 right-2">
                                        <CheckCircleIcon class="w-5 h-5 text-blue-500" />
                                    </div>
                                </button>

                                <button type="button" @click="formData.type = 'suggestion'; errors.type = ''"
                                    :class="[
                                        'relative p-5 rounded-xl border-2 transition-all duration-200 text-center group',
                                        formData.type === 'suggestion'
                                            ? 'border-green-500 bg-green-50 ring-2 ring-green-200'
                                            : 'border-gray-200 hover:border-green-300 hover:bg-green-50/50'
                                    ]">
                                    <div :class="['w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3',
                                        formData.type === 'suggestion' ? 'bg-green-500 text-white' : 'bg-green-100 text-green-600']">
                                        <LightBulbIcon class="w-7 h-7" />
                                    </div>
                                    <span class="font-bold text-gray-900 block">Sugestão</span>
                                    <span class="text-xs text-gray-500 mt-1 block">Propor melhoria</span>
                                    <div v-if="formData.type === 'suggestion'" class="absolute top-2 right-2">
                                        <CheckCircleIcon class="w-5 h-5 text-green-500" />
                                    </div>
                                </button>
                            </div>
                            <p v-if="errors.type" class="text-red-500 text-xs mt-3 flex items-center">
                                <ExclamationCircleIcon class="w-4 h-4 mr-1" />
                                {{ errors.type }}
                            </p>
                        </div>

                        <!-- Projeto Relacionado -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <FolderIcon class="w-5 h-5 text-orange-500" />
                                Projeto Relacionado <span class="text-red-500">*</span>
                            </h3>

                            <select v-model="formData.project_id" @change="errors.project_id = ''"
                                :class="[
                                    'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 bg-white transition-all',
                                    errors.project_id ? 'border-red-500 focus:ring-red-500 bg-red-50' : 'border-gray-300 focus:ring-orange-500'
                                ]">
                                <option value="">-- Selecione um projeto --</option>
                                <option v-for="project in projects" :key="project.id" :value="project.id">
                                    {{ project.name }} {{ project.provincia ? '• ' + project.provincia : '' }}
                                </option>
                            </select>
                            <p v-if="errors.project_id" class="flex items-center text-xs text-red-500 mt-2">
                                <ExclamationCircleIcon class="w-4 h-4 mr-1" />
                                {{ errors.project_id }}
                            </p>
                            <p v-else class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                <InformationCircleIcon class="w-4 h-4" />
                                Selecione o projeto relacionado à sua submissão
                            </p>
                        </div>

                        <!-- Descrição -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <DocumentTextIcon class="w-5 h-5 text-orange-500" />
                                Descrição <span class="text-red-500">*</span>
                            </h3>

                            <textarea v-model="formData.description" @input="errors.description = ''" rows="6"
                                :class="[
                                    'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all resize-none',
                                    errors.description ? 'border-red-500 focus:ring-red-500 bg-red-50' : 'border-gray-300 focus:ring-orange-500'
                                ]"
                                maxlength="1500"
                                :placeholder="descriptionPlaceholder"></textarea>

                            <div class="flex items-center justify-between mt-3">
                                <p v-if="errors.description" class="flex items-center text-xs text-red-500">
                                    <ExclamationCircleIcon class="w-4 h-4 mr-1" />
                                    {{ errors.description }}
                                </p>
                                <div class="flex items-center gap-3 ml-auto">
                                    <div class="flex items-center gap-1">
                                        <div :class="[
                                            'w-2 h-2 rounded-full',
                                            formData.description.length < 50 ? 'bg-red-500' :
                                            formData.description.length > 1400 ? 'bg-yellow-500' : 'bg-green-500'
                                        ]"></div>
                                        <span :class="[
                                            'text-sm font-medium',
                                            formData.description.length < 50 ? 'text-red-600' :
                                            formData.description.length > 1400 ? 'text-yellow-600' : 'text-green-600'
                                        ]">
                                            {{ formData.description.length }}/1500
                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded">mín. 50 caracteres</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </template>

                <!-- ==================== PASSO 3: EVIDÊNCIAS ==================== -->
                <template v-else>
                    <div class="max-w-2xl mx-auto space-y-6">

                        <!-- Info -->
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
                            <InformationCircleIcon class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" />
                            <div>
                                <p class="text-sm text-blue-800 font-medium">Evidências são opcionais</p>
                                <p class="text-xs text-blue-600 mt-1">Adicione fotos, documentos ou gravações de áudio para ajudar a ilustrar a sua submissão.</p>
                            </div>
                        </div>

                        <!-- Upload de Ficheiros -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <PhotoIcon class="w-5 h-5 text-orange-500" />
                                Fotos e Documentos
                            </h3>

                            <div @drop.prevent="handleDrop" @dragover.prevent @click="triggerFileInput"
                                :class="[
                                    'p-8 text-center transition-all border-2 border-dashed rounded-xl cursor-pointer',
                                    files.length >= 5 ? 'bg-gray-100 border-gray-300 cursor-not-allowed' : 'hover:border-orange-500 hover:bg-orange-50 border-gray-300'
                                ]">
                                <DocumentArrowUpIcon :class="['w-12 h-12 mx-auto mb-3', files.length >= 5 ? 'text-gray-300' : 'text-gray-400']" />
                                <p v-if="files.length < 5" class="text-sm font-medium text-gray-700">Arraste ficheiros ou clique para selecionar</p>
                                <p v-else class="text-sm font-medium text-gray-500">Limite de ficheiros atingido</p>
                                <p class="text-xs text-gray-500 mt-1">PNG, JPG, PDF (máx. 10MB cada) • {{ files.length }}/5 ficheiros</p>
                            </div>

                            <input ref="fileInputRef" type="file" multiple class="hidden" @change="handleFileUpload"
                                accept=".png,.jpg,.jpeg,.pdf,.doc,.docx" />

                            <!-- Lista de ficheiros -->
                            <div v-if="files.length > 0" class="mt-4 space-y-2">
                                <div v-for="(file, index) in files" :key="index"
                                    class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-lg group hover:border-orange-300">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                                            <DocumentIcon class="w-5 h-5 text-orange-600" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 truncate max-w-[200px]">{{ file.name }}</p>
                                            <p class="text-xs text-gray-500">{{ (file.size / 1024).toFixed(1) }} KB</p>
                                        </div>
                                    </div>
                                    <button @click.stop="removeFile(index)"
                                        class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Gravação de Áudio -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <MicrophoneIcon class="w-5 h-5 text-orange-500" />
                                Mensagem de Voz
                            </h3>

                            <div class="bg-gray-50 rounded-xl p-6">
                                <!-- Controles de gravação -->
                                <div class="flex flex-col items-center">
                                    <template v-if="!isRecording && !audioBlob">
                                        <button type="button" @click="startRecording"
                                            class="w-20 h-20 rounded-full bg-red-500 text-white flex items-center justify-center hover:bg-red-600 transition-all hover:scale-105 shadow-lg">
                                            <MicrophoneIcon class="w-10 h-10" />
                                        </button>
                                        <p class="text-sm text-gray-600 mt-4">Clique para iniciar a gravação</p>
                                        <p class="text-xs text-gray-400 mt-1">Máximo 2 minutos</p>
                                    </template>

                                    <template v-if="isRecording">
                                        <div class="relative">
                                            <button type="button" @click="stopRecording"
                                                class="w-20 h-20 rounded-full bg-gray-800 text-white flex items-center justify-center hover:bg-gray-900 transition-all animate-pulse shadow-lg">
                                                <StopIcon class="w-10 h-10" />
                                            </button>
                                            <div class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full animate-ping"></div>
                                        </div>
                                        <p class="text-sm text-gray-900 font-semibold mt-4">A gravar... {{ recordingTime }}s</p>
                                        <div class="w-full max-w-xs bg-gray-200 rounded-full h-1.5 mt-3">
                                            <div class="bg-red-500 h-1.5 rounded-full transition-all" :style="{ width: `${(recordingTime / 120) * 100}%` }"></div>
                                        </div>
                                    </template>

                                    <template v-if="audioBlob && !isRecording">
                                        <div class="w-full space-y-4">
                                            <div class="flex items-center gap-3 bg-white p-4 rounded-xl border border-gray-200">
                                                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                                                    <CheckCircleIcon class="w-6 h-6 text-green-600" />
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-gray-900">Áudio gravado</p>
                                                    <p class="text-xs text-gray-500">{{ recordingTime }} segundos</p>
                                                </div>
                                            </div>
                                            <audio ref="audioPlayerRef" :src="audioUrl" controls class="w-full"></audio>
                                            <button type="button" @click="deleteRecording"
                                                class="w-full py-2 px-4 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors flex items-center justify-center gap-2">
                                                <TrashIcon class="w-4 h-4" />
                                                Eliminar gravação
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                <!-- Ou upload de áudio -->
                                <div v-if="!audioBlob && !isRecording" class="mt-6 pt-4 border-t border-gray-200">
                                    <label class="flex items-center justify-center gap-2 text-sm text-gray-600 cursor-pointer hover:text-orange-600 py-2">
                                        <input type="file" accept="audio/*" @change="handleAudioUpload" class="hidden" />
                                        <ArrowUpTrayIcon class="w-5 h-5" />
                                        Ou carregar ficheiro de áudio existente
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>
                </template>
            </div>

            <!-- Footer -->
            <div class="flex justify-between items-center p-6 bg-white border-t border-gray-200">
                <button @click="previousStep"
                    class="flex items-center gap-2 px-5 py-2.5 font-medium text-gray-700 transition-colors border border-gray-300 rounded-lg hover:bg-gray-50">
                    <ArrowLeftIcon class="w-4 h-4" />
                    <span class="hidden sm:inline">{{ currentStep === 1 ? 'Cancelar' : 'Voltar' }}</span>
                </button>

                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500 hidden sm:inline">Passo {{ currentStep }} de 3</span>
                </div>

                <button v-if="currentStep < 3" @click="nextStep"
                    class="flex items-center gap-2 px-5 py-2.5 font-medium text-white transition-colors bg-orange-500 rounded-lg shadow-md hover:bg-orange-600">
                    <span class="hidden sm:inline">Próximo</span>
                    <ArrowRightIcon class="w-4 h-4" />
                </button>

                <button v-else @click="handleSubmit" :disabled="isSubmitting"
                    :class="[
                        'flex items-center gap-2 px-6 py-2.5 font-medium text-white transition-colors rounded-lg shadow-md',
                        isSubmitting ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700'
                    ]">
                    <template v-if="isSubmitting">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>A submeter...</span>
                    </template>
                    <template v-else>
                        <span>Submeter</span>
                        <PaperAirplaneIcon class="w-5 h-5" />
                    </template>
                </button>
            </div>
        </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import {
    XMarkIcon,
    DocumentTextIcon,
    PaperClipIcon,
    DocumentArrowUpIcon,
    ArrowLeftIcon,
    ArrowRightIcon,
    CheckIcon,
    DocumentIcon,
    ExclamationCircleIcon,
    LightBulbIcon,
    QuestionMarkCircleIcon,
    FolderIcon,
    MicrophoneIcon,
    StopIcon,
    TrashIcon,
    ArrowUpTrayIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    EyeSlashIcon,
    UserIcon,
    EnvelopeIcon,
    PhoneIcon,
    ShieldCheckIcon,
    TagIcon,
    PhotoIcon,
    PaperAirplaneIcon,
    XCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    isAnonymous: {
        type: Boolean,
        default: false
    },
    visible: {
        type: Boolean,
        default: true
    }
})

const emit = defineEmits(['close', 'success'])

const currentStep = ref(1)
const fileInputRef = ref(null)
const isSubmitting = ref(false)

const formData = ref({
    project_id: '',
    type: 'complaint',
    description: '',
    province: '',
    district: '',
    location_details: '',
    is_anonymous: props.isAnonymous,
    contact_name: '',
    contact_email: '',
    contact_phone: ''
})
const projects = ref([])
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
const files = ref([])
const errors = ref({})

// Modals state
const showSuccessModal = ref(false)
const showErrorModal = ref(false)
const errorModalMessage = ref('')
const validationErrors = ref({})
const submissionResult = ref({})

// Toast notification state
const toast = ref({
    show: false,
    type: 'success',
    title: '',
    message: ''
})

// Audio recording state
const isRecording = ref(false)
const audioBlob = ref(null)
const audioUrl = ref(null)
const mediaRecorder = ref(null)
const audioChunks = ref([])
const recordingTime = ref(0)
const recordingInterval = ref(null)
const audioPlayerRef = ref(null)

// Computed
const typeLabel = computed(() => {
    switch (formData.value.type) {
        case 'complaint': return 'reclamação'
        case 'suggestion': return 'sugestão'
        case 'grievance': return 'queixa'
        default: return 'reclamação'
    }
})

const descriptionPlaceholder = computed(() => {
    switch (formData.value.type) {
        case 'complaint':
            return 'Descreva com detalhes o problema que deseja reportar. Inclua informações como local, data, pessoas envolvidas e qualquer outro detalhe relevante...'
        case 'suggestion':
            return 'Descreva a sua sugestão e como ela pode melhorar os serviços ou processos do FUNAE...'
        case 'grievance':
            return 'Descreva a sua queixa de forma clara e detalhada para que possamos ajudá-lo da melhor forma...'
        default:
            return 'Descreva com detalhes...'
    }
})

const toastIcon = computed(() => {
    switch (toast.value.type) {
        case 'success': return CheckCircleIcon
        case 'error': return ExclamationCircleIcon
        case 'warning': return ExclamationTriangleIcon
        default: return InformationCircleIcon
    }
})



// Audio recording functions
const startRecording = async () => {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true })
        mediaRecorder.value = new MediaRecorder(stream)
        audioChunks.value = []

        mediaRecorder.value.ondataavailable = (event) => {
            audioChunks.value.push(event.data)
        }

        mediaRecorder.value.onstop = () => {
            audioBlob.value = new Blob(audioChunks.value, { type: 'audio/webm' })
            audioUrl.value = URL.createObjectURL(audioBlob.value)
            stream.getTracks().forEach(track => track.stop())
        }

        mediaRecorder.value.start()
        isRecording.value = true
        recordingTime.value = 0

        recordingInterval.value = setInterval(() => {
            recordingTime.value++
            if (recordingTime.value >= 120) {
                stopRecording()
                showToast('warning', 'Limite atingido', 'A gravação foi parada após 2 minutos.')
            }
        }, 1000)

        showToast('info', 'Gravação iniciada', 'A gravar áudio...')
    } catch (error) {
        console.error('Error accessing microphone:', error)
        showToast('error', 'Erro', 'Não foi possível aceder ao microfone. Verifique as permissões.')
    }
}

const stopRecording = () => {
    if (mediaRecorder.value && isRecording.value) {
        mediaRecorder.value.stop()
        isRecording.value = false
        clearInterval(recordingInterval.value)
        showToast('success', 'Gravação concluída', `Áudio gravado (${recordingTime.value}s)`)
    }
}

const deleteRecording = () => {
    if (audioUrl.value) {
        URL.revokeObjectURL(audioUrl.value)
    }
    audioBlob.value = null
    audioUrl.value = null
    recordingTime.value = 0
    showToast('info', 'Áudio removido', 'A gravação foi eliminada.')
}

const handleAudioUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
        if (file.size > 10 * 1024 * 1024) {
            showToast('error', 'Ficheiro muito grande', 'O ficheiro de áudio deve ter no máximo 10MB.')
            return
        }
        audioBlob.value = file
        audioUrl.value = URL.createObjectURL(file)
        showToast('success', 'Áudio carregado', file.name)
    }
    event.target.value = ''
}

// File handling
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
    event.target.value = ''
}

const handleDrop = (event) => {
    if (files.value.length >= 5) return
    const newFiles = Array.from(event.dataTransfer.files)
    const remainingSlots = 5 - files.value.length
    const filesToAdd = newFiles.slice(0, remainingSlots)
    files.value = [...files.value, ...filesToAdd]
}

const removeFile = (index) => {
    files.value = files.value.filter((_, i) => i !== index)
}

// Navigation
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

// Validation
const validateStep = () => {
    errors.value = {}

    if (currentStep.value === 1) {
        // Validar dados pessoais apenas se NÃO for anónimo
        if (!formData.value.is_anonymous) {
            if (!formData.value.contact_name || formData.value.contact_name.trim().length < 3) {
                errors.value.contact_name = 'Nome é obrigatório (mínimo 3 caracteres)'
            }
            if (!formData.value.contact_email) {
                errors.value.contact_email = 'Email é obrigatório'
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.value.contact_email)) {
                errors.value.contact_email = 'Email inválido'
            }
        }
    }

    if (currentStep.value === 2) {
        if (!formData.value.type) {
            errors.value.type = 'Selecione o tipo de submissão'
        }
        if (!formData.value.project_id) {
            errors.value.project_id = 'Selecione o projeto relacionado'
        }
        if (!formData.value.description || formData.value.description.length < 50) {
            errors.value.description = 'A descrição deve ter pelo menos 50 caracteres'
        }
        if (formData.value.description && formData.value.description.length > 1500) {
            errors.value.description = 'A descrição não pode exceder 1500 caracteres'
        }
    }

    if (Object.keys(errors.value).length > 0) {
        showToast('error', 'Erro de validação', 'Por favor, corrija os campos indicados.')
    }

    return Object.keys(errors.value).length === 0
}

// Modal helpers
const closeSuccessAndForm = () => {
    showSuccessModal.value = false
    emit('success', submissionResult.value)
    emit('close')
}

const closeErrorModal = () => {
    showErrorModal.value = false
    errorModalMessage.value = ''
    validationErrors.value = {}
}

// Função para resetar o formulário
const resetForm = () => {
    formData.value = {
        project_id: '',
        type: 'complaint',
        description: '',
        province: '',
        district: '',
        location_details: '',
        is_anonymous: props.isAnonymous,
        contact_name: '',
        contact_email: '',
        contact_phone: ''
    }
    files.value = []
    errors.value = {}
    currentStep.value = 1

    // Limpar áudio
    if (audioBlob.value) {
        URL.revokeObjectURL(audioUrl.value)
        audioBlob.value = null
        audioUrl.value = null
        recordingTime.value = 0
    }

    // Limpar toast se estiver visível
    toast.value.show = false
}

// Submit
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

        // Adicionar áudio se existir
        if (audioBlob.value) {
            const audioFile = audioBlob.value instanceof File
                ? audioBlob.value
                : new File([audioBlob.value], 'voice_message.webm', { type: 'audio/webm' })
            formDataToSend.append('audio_attachment', audioFile)
        }

        const response = await fetch('/api/grievances', {
            method: 'POST',
            body: formDataToSend,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })

        let data
        const contentType = response.headers.get('content-type')

        if (contentType && contentType.includes('application/json')) {
            data = await response.json()
        } else {
            const text = await response.text()
            console.error('Resposta não é JSON:', text)
            throw new Error('Servidor retornou resposta inválida')
        }

        if (response.ok && data.success) {
            submissionResult.value = data

            // Resetar o formulário
            resetForm()

            // Fechar o modal do formulário e abrir o modal de sucesso
            emit('close')
            showSuccessModal.value = true

            // Fechar automaticamente após 5 segundos
            setTimeout(() => {
                if (showSuccessModal.value) {
                    closeSuccessAndForm()
                }
            }, 5000)
        } else {
            // Mostrar modal de erro com detalhes
            if (data.errors) {
                validationErrors.value = data.errors
                errorModalMessage.value = data.message || 'Foram encontrados erros de validação. Por favor, corrija e tente novamente.'

                // Também popular errors para marcar campos
                Object.keys(data.errors).forEach(key => {
                    errors.value[key] = Array.isArray(data.errors[key]) ? data.errors[key][0] : data.errors[key]
                })

                // Voltar ao passo apropriado
                if (errors.value.contact_name || errors.value.contact_email) {
                    currentStep.value = 1
                } else if (errors.value.type || errors.value.description) {
                    currentStep.value = 2
                }
            } else {
                errorModalMessage.value = data.message || 'Ocorreu um erro ao processar a sua submissão.'
            }
            showErrorModal.value = true
        }
    } catch (error) {
        console.error('Erro crítico ao submeter:', error)

        if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
            errorModalMessage.value = 'Erro de conexão. Verifique a sua ligação à internet e tente novamente.'
        } else {
            errorModalMessage.value = 'Ocorreu um erro inesperado ao processar a sua submissão. Por favor, tente novamente.'
        }
        showErrorModal.value = true
    } finally {
        isSubmitting.value = false
    }
}

// Cleanup
onUnmounted(() => {
    if (audioUrl.value) {
        URL.revokeObjectURL(audioUrl.value)
    }
    if (recordingInterval.value) {
        clearInterval(recordingInterval.value)
    }
})
</script>

<style scoped>
/* Toast slide animation */
.slide-fade-enter-active {
    transition: all 0.3s ease-out;
}
.slide-fade-leave-active {
    transition: all 0.2s ease-in;
}
.slide-fade-enter-from {
    transform: translateX(100%);
    opacity: 0;
}
.slide-fade-leave-to {
    transform: translateX(100%);
    opacity: 0;
}

/* Modal zoom animation */
.zoom-enter-active {
    transition: all 0.3s ease-out;
}
.zoom-leave-active {
    transition: all 0.2s ease-in;
}
.zoom-enter-from {
    transform: scale(0.9);
    opacity: 0;
}
.zoom-leave-to {
    transform: scale(0.9);
    opacity: 0;
}

/* Fade slide animation for form sections */
.fade-slide-enter-active {
    transition: all 0.3s ease-out;
}
.fade-slide-leave-active {
    transition: all 0.2s ease-in;
}
.fade-slide-enter-from {
    transform: translateY(-10px);
    opacity: 0;
}
.fade-slide-leave-to {
    transform: translateY(-10px);
    opacity: 0;
}
</style>
