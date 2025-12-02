<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <!-- Toast Notification -->
        <transition name="slide-fade">
            <div v-if="toast.show" :class="[
                'fixed top-5 right-5 z-[100] px-6 py-4 rounded-lg shadow-2xl flex items-center gap-3 min-w-[300px]',
                toast.type === 'success' ? 'bg-green-500 text-white' :
                toast.type === 'error' ? 'bg-red-500 text-white' :
                toast.type === 'warning' ? 'bg-yellow-500 text-white' : 'bg-blue-500 text-white'
            ]">
                <component :is="toastIcon" class="w-6 h-6 flex-shrink-0" />
                <div>
                    <p class="font-semibold">{{ toast.title }}</p>
                    <p class="text-sm opacity-90">{{ toast.message }}</p>
                </div>
                <button @click="toast.show = false" class="ml-auto hover:opacity-75">
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>
        </transition>

        <!-- Success Modal -->
        <transition name="zoom">
            <div v-if="showSuccessModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-[60]">
                <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md mx-4 text-center transform">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <CheckCircleIcon class="w-12 h-12 text-green-500" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Submissão Enviada!</h3>
                    <p class="text-gray-600 mb-4">A sua reclamação foi submetida com sucesso.</p>
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
                        <p class="text-sm text-gray-600 mb-1">Código de Rastreio:</p>
                        <p class="text-2xl font-mono font-bold text-orange-600">{{ submissionResult.reference_number }}</p>
                    </div>
                    <p class="text-sm text-gray-500 mb-6">Guarde este código para acompanhar o estado da sua reclamação.</p>
                    <button @click="closeSuccessAndForm"
                        class="w-full bg-orange-500 text-white py-3 px-6 rounded-lg font-semibold hover:bg-orange-600 transition-colors">
                        Entendido
                    </button>
                </div>
            </div>
        </transition>

        <div class="bg-white rounded-lg shadow-2xl w-full max-w-[1200px] h-[90vh] flex flex-col">

            <!-- Header -->
            <div class="border-b border-gray-200 p-6 flex justify-between items-center bg-gradient-to-r from-orange-500 to-orange-600">
                <h2 class="text-2xl font-bold text-white flex-1 text-center">Nova Submissão</h2>
                <button @click="$emit('close')" class="text-white hover:text-gray-200 transition-colors ml-4">
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>

            <!-- Step Indicators -->
            <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
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
            <div class="flex-1 p-6 overflow-y-auto bg-gray-50">
                <!-- Step 1: Informações Básicas -->
                <template v-if="currentStep === 1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                        <div class="md:col-span-2 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm text-blue-800">
                                <strong>Importante:</strong> Todas as informações fornecidas serão tratadas com confidencialidade.
                            </p>
                        </div>

                        <!-- Tipo de Submissão com Cards -->
                        <div class="space-y-3 md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Tipo de Submissão <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <button type="button" @click="formData.type = 'complaint'; errors.type = ''"
                                    :class="[
                                        'p-4 rounded-xl border-2 transition-all duration-200 text-left group',
                                        formData.type === 'complaint'
                                            ? 'border-red-500 bg-red-50 ring-2 ring-red-200'
                                            : 'border-gray-200 hover:border-red-300 hover:bg-red-25'
                                    ]">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div :class="['w-10 h-10 rounded-full flex items-center justify-center',
                                            formData.type === 'complaint' ? 'bg-red-500 text-white' : 'bg-red-100 text-red-600']">
                                            <ExclamationCircleIcon class="w-6 h-6" />
                                        </div>
                                        <span class="font-semibold text-gray-900">Reclamação</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Reportar um problema ou insatisfação com serviços</p>
                                </button>

                                <button type="button" @click="formData.type = 'suggestion'; errors.type = ''"
                                    :class="[
                                        'p-4 rounded-xl border-2 transition-all duration-200 text-left group',
                                        formData.type === 'suggestion'
                                            ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200'
                                            : 'border-gray-200 hover:border-blue-300 hover:bg-blue-25'
                                    ]">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div :class="['w-10 h-10 rounded-full flex items-center justify-center',
                                            formData.type === 'suggestion' ? 'bg-blue-500 text-white' : 'bg-blue-100 text-blue-600']">
                                            <LightBulbIcon class="w-6 h-6" />
                                        </div>
                                        <span class="font-semibold text-gray-900">Sugestão</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Propor melhorias ou ideias para os serviços</p>
                                </button>

                                <button type="button" @click="formData.type = 'question'; errors.type = ''"
                                    :class="[
                                        'p-4 rounded-xl border-2 transition-all duration-200 text-left group',
                                        formData.type === 'question'
                                            ? 'border-green-500 bg-green-50 ring-2 ring-green-200'
                                            : 'border-gray-200 hover:border-green-300 hover:bg-green-25'
                                    ]">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div :class="['w-10 h-10 rounded-full flex items-center justify-center',
                                            formData.type === 'question' ? 'bg-green-500 text-white' : 'bg-green-100 text-green-600']">
                                            <QuestionMarkCircleIcon class="w-6 h-6" />
                                        </div>
                                        <span class="font-semibold text-gray-900">Dúvida</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Esclarecer informações sobre serviços ou projetos</p>
                                </button>
                            </div>
                            <p v-if="errors.type" class="text-red-500 text-xs mt-1 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                {{ errors.type }}
                            </p>
                        </div>

                        <!-- Projeto com destaque -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <span class="flex items-center gap-2">
                                    <FolderIcon class="w-5 h-5 text-orange-500" />
                                    Projeto Relacionado
                                </span>
                            </label>
                            <select v-model="formData.project_id" @change="errors.project_id = ''"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 bg-white">
                                <option value="">-- Selecione um projeto (opcional) --</option>
                                <option v-for="project in projects" :key="project.id" :value="project.id">
                                    {{ project.name }} {{ project.provincia ? '• ' + project.provincia : '' }} {{ project.distrito ? '/ ' + project.distrito : '' }}
                                </option>
                            </select>
                            <p class="text-xs text-gray-500">Se a sua submissão está relacionada a um projeto específico do FUNAE, selecione-o aqui.</p>
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
                                        class="w-5 h-5 mr-3 text-orange-500 focus:ring-orange-500" />
                                    <span class="text-gray-700">Não, quero me identificar</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" :value="true" v-model="formData.is_anonymous"
                                        class="w-5 h-5 mr-3 text-orange-500 focus:ring-orange-500" />
                                    <span class="text-gray-700">Sim, prefiro o anonimato</span>
                                </label>
                            </div>
                        </div>

                        <!-- Campos para reclamação anônima -->
                        <template v-if="formData.is_anonymous">
                            <div class="p-4 border border-yellow-200 rounded-lg md:col-span-2 bg-yellow-50">
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
                                <p v-if="errors.contact_name" class="flex items-center mt-1 text-xs text-red-500">
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
                                <p v-if="errors.contact_email" class="flex items-center mt-1 text-xs text-red-500">
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
                                Descrição <span class="text-red-500">*</span>
                            </label>
                            <p class="mb-2 text-xs text-gray-500">Descreva detalhadamente a sua {{ formData.type === 'complaint' ? 'reclamação' : formData.type === 'suggestion' ? 'sugestão' : 'dúvida' }} (mínimo 10 caracteres).</p>
                            <textarea v-model="formData.description" @input="errors.description = ''" rows="4"
                                :class="[
                                    'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all',
                                    errors.description ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-orange-500'
                                ]"
                                :placeholder="descriptionPlaceholder"></textarea>
                            <div class="flex items-center justify-between">
                                <p v-if="errors.description" class="flex items-center text-xs text-red-500">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ errors.description }}
                                </p>
                                <p class="text-xs text-gray-500">{{ formData.description.length }} caracteres</p>
                            </div>
                        </div>

                        <!-- Gravação de Áudio -->
                        <div class="space-y-3 md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <span class="flex items-center gap-2">
                                    <MicrophoneIcon class="w-5 h-5 text-orange-500" />
                                    Mensagem de Voz (Opcional)
                                </span>
                            </label>
                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                                <p class="text-xs text-gray-500 mb-3">Grave uma mensagem de voz para complementar ou substituir a descrição escrita.</p>

                                <!-- Audio Recording Controls -->
                                <div class="flex flex-wrap items-center gap-3">
                                    <button v-if="!isRecording && !audioBlob" type="button" @click="startRecording"
                                        class="flex items-center gap-2 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                                        <MicrophoneIcon class="w-5 h-5" />
                                        Iniciar Gravação
                                    </button>

                                    <button v-if="isRecording" type="button" @click="stopRecording"
                                        class="flex items-center gap-2 px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition-colors animate-pulse">
                                        <StopIcon class="w-5 h-5" />
                                        Parar ({{ recordingTime }}s)
                                    </button>

                                    <div v-if="audioBlob && !isRecording" class="flex items-center gap-3 flex-1">
                                        <audio ref="audioPlayerRef" :src="audioUrl" controls class="flex-1 h-10"></audio>
                                        <button type="button" @click="deleteRecording"
                                            class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                            <TrashIcon class="w-5 h-5" />
                                        </button>
                                    </div>

                                    <!-- Or upload audio file -->
                                    <div class="w-full mt-2 pt-2 border-t border-gray-200">
                                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer hover:text-orange-600">
                                            <input type="file" accept="audio/*" @change="handleAudioUpload" class="hidden" />
                                            <ArrowUpTrayIcon class="w-4 h-4" />
                                            Ou carregar ficheiro de áudio
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Step 2: Localização -->
                <template v-else-if="currentStep === 2">
                    <div class="grid max-w-4xl grid-cols-1 gap-6 mx-auto md:grid-cols-2">
                        <div class="p-4 border border-blue-200 rounded-lg md:col-span-2 bg-blue-50">
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
                        <div class="p-4 border border-blue-200 rounded-lg bg-blue-50">
                            <p class="text-sm text-blue-800">
                                <strong>Evidências:</strong> Adicione fotos, documentos ou outros arquivos que comprovem sua reclamação (opcional).
                            </p>
                        </div>

                        <div @drop.prevent="handleDrop" @dragover.prevent @click="triggerFileInput"
                            class="p-12 text-center transition-all bg-white border-2 border-gray-300 border-dashed rounded-lg cursor-pointer hover:border-orange-500 hover:bg-orange-50">
                            <DocumentArrowUpIcon class="w-16 h-16 mx-auto mb-4 text-gray-400" />
                            <p class="mb-2 text-base font-semibold text-gray-700">Arraste arquivos para esta área ou clique para selecionar</p>
                            <p class="text-sm text-gray-500">Formatos aceitos: PNG, JPG, PDF (máx. 10MB por arquivo)</p>
                            <p class="mt-2 text-xs text-gray-400">Máximo de 5 arquivos</p>
                        </div>

                        <input ref="fileInputRef" type="file" multiple class="hidden" @change="handleFileUpload"
                            accept=".png,.jpg,.jpeg,.pdf" />

                        <div v-if="files.length > 0" class="space-y-2">
                            <h4 class="font-semibold text-gray-700">Arquivos Selecionados ({{ files.length }}/5):</h4>
                            <div v-for="(file, index) in files" :key="index"
                                class="flex items-center justify-between p-4 transition-all bg-white border border-gray-200 rounded-lg hover:border-orange-500">
                                <div class="flex items-center gap-3">
                                    <DocumentIcon class="w-6 h-6 text-orange-500" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">{{ file.name }}</p>
                                        <p class="text-xs text-gray-500">{{ (file.size / 1024).toFixed(1) }} KB</p>
                                    </div>
                                </div>
                                <button @click.stop="removeFile(index)"
                                    class="p-2 text-red-500 transition-colors rounded hover:text-red-700 hover:bg-red-50">
                                    <XMarkIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer -->
            <div class="flex justify-between p-6 bg-white border-t border-gray-200">
                <button @click="previousStep"
                    class="flex items-center gap-2 px-6 py-3 font-medium text-gray-700 transition-colors border border-gray-300 rounded-lg hover:bg-gray-50">
                    <ArrowLeftIcon class="w-4 h-4" />
                    Voltar
                </button>

                <button v-if="currentStep < 3" @click="nextStep"
                    class="flex items-center gap-2 px-6 py-3 font-medium text-white transition-colors bg-orange-500 rounded-lg shadow-md hover:bg-orange-600">
                    Próximo
                    <ArrowRightIcon class="w-4 h-4" />
                </button>

                <button v-else @click="handleSubmit" :disabled="isSubmitting"
                    class="flex items-center gap-2 px-6 py-3 font-medium text-white transition-colors bg-green-600 rounded-lg shadow-md hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                    <span v-if="isSubmitting">Submetendo...</span>
                    <span v-else>Submeter Reclamação</span>
                    <CheckIcon v-if="!isSubmitting" class="w-5 h-5" />
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import {
    XMarkIcon,
    DocumentTextIcon,
    MapPinIcon,
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
    InformationCircleIcon
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

// Toast notification state
const toast = ref({
    show: false,
    type: 'success',
    title: '',
    message: ''
})

// Success modal state
const showSuccessModal = ref(false)
const submissionResult = ref({})

// Audio recording state
const isRecording = ref(false)
const audioBlob = ref(null)
const audioUrl = ref(null)
const mediaRecorder = ref(null)
const audioChunks = ref([])
const recordingTime = ref(0)
const recordingInterval = ref(null)
const audioPlayerRef = ref(null)

// Computed properties
const descriptionPlaceholder = computed(() => {
    switch (formData.value.type) {
        case 'complaint':
            return 'Descreva sua reclamação com o máximo de detalhes possível...'
        case 'suggestion':
            return 'Descreva sua sugestão e como ela pode melhorar os serviços...'
        case 'question':
            return 'Descreva sua dúvida de forma clara...'
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

// Toast helper function
const showToast = (type, title, message, duration = 4000) => {
    toast.value = { show: true, type, title, message }
    setTimeout(() => {
        toast.value.show = false
    }, duration)
}

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
            if (recordingTime.value >= 120) { // Max 2 minutes
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
        if (file.size > 10 * 1024 * 1024) { // 10MB limit
            showToast('error', 'Ficheiro muito grande', 'O ficheiro de áudio deve ter no máximo 10MB.')
            return
        }
        audioBlob.value = file
        audioUrl.value = URL.createObjectURL(file)
        showToast('success', 'Áudio carregado', file.name)
    }
    event.target.value = ''
}

const closeSuccessAndForm = () => {
    showSuccessModal.value = false
    emit('success', submissionResult.value)
    emit('close')
}

// Cleanup on unmount
onUnmounted(() => {
    if (audioUrl.value) {
        URL.revokeObjectURL(audioUrl.value)
    }
    if (recordingInterval.value) {
        clearInterval(recordingInterval.value)
    }
})

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
        if (!formData.value.type) {
            errors.value.type = 'Selecione o tipo de submissão'
        }
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

        // Adicionar áudio se existir
        if (audioBlob.value) {
            const audioFile = audioBlob.value instanceof File
                ? audioBlob.value
                : new File([audioBlob.value], 'voice_message.webm', { type: 'audio/webm' })
            formDataToSend.append('audio_attachment', audioFile)
        }

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
            submissionResult.value = data
            showSuccessModal.value = true
            showToast('success', 'Submissão enviada!', 'A sua reclamação foi registada com sucesso.')
        } else {
            console.error('Erro na resposta:', data)

            // Tratar erros de validação
            if (data.errors) {
                errors.value = data.errors
                console.log('Erros de validação:', errors.value)

                // Voltar para o primeiro passo se houver erros
                currentStep.value = 1
                showToast('error', 'Erro de validação', 'Por favor, corrija os erros indicados.')

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

        // Mostrar erro genérico
        if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
            showToast('error', 'Erro de conexão', 'Verifique a sua ligação à internet e tente novamente.')
        } else {
            showToast('error', 'Erro inesperado', 'Ocorreu um erro ao processar a sua submissão.')
        }
    } finally {
        isSubmitting.value = false
    }
}
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

/* Success modal zoom animation */
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

/* Recording pulse animation */
@keyframes pulse-recording {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
.animate-pulse {
    animation: pulse-recording 1.5s ease-in-out infinite;
}
</style>
