<template>
  <div class="w-full bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6">
    <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4">
      Acções
    </h2>

    <div v-if="!complaint" class="text-center py-4 text-gray-500">
      A carregar dados...
    </div>

    <div v-else class="space-y-2">
      <button
        @click="handleCommentClick"
        :disabled="isCommentButtonDisabled"
        :class="[
          'group relative w-full overflow-hidden rounded-2xl p-5 text-left shadow-sm ring-1 transition-all duration-500 hover:-translate-y-1',
          !isCommentButtonDisabled
            ? 'bg-gradient-to-r from-slate-50 via-gray-50 to-slate-100 dark:from-slate-800 dark:via-gray-800 dark:to-slate-700 ring-gray-200/50 dark:ring-gray-700/50 hover:shadow-xl hover:shadow-brand/10 dark:hover:shadow-brand/20 hover:ring-brand/30 dark:hover:ring-brand/40'
            : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 cursor-not-allowed ring-gray-200/50 dark:ring-gray-700/50',
        ]"
        :title="commentButtonTitle"
      >
        <!-- Background gradient animation -->
        <div
          v-if="!isCommentButtonDisabled"
          class="absolute inset-0 bg-gradient-to-r from-brand/5 via-transparent to-brand/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
        ></div>

        <!-- Animated border -->
        <div
          v-if="!isCommentButtonDisabled"
          class="absolute inset-0 rounded-2xl bg-gradient-to-r from-brand via-orange-500 to-brand opacity-0 transition-opacity duration-500 group-hover:opacity-20 blur-sm"
        ></div>

        <div class="relative flex items-center gap-4">
          <!-- Icon container with enhanced styling -->
          <div
            v-if="!isCommentButtonDisabled"
            class="relative flex-shrink-0"
          >
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand to-orange-600 shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-brand/30">
              <ChatBubbleLeftIcon class="h-6 w-6 text-white transition-transform duration-300 group-hover:scale-110" />
            </div>
            <!-- Subtle pulse effect -->
            <div class="absolute -inset-1 rounded-xl bg-brand/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100 group-hover:animate-ping"></div>
          </div>
          <div v-else class="relative flex-shrink-0">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-400 dark:bg-gray-600 shadow-lg">
              <ChatBubbleLeftIcon class="h-6 w-6 text-white" />
            </div>
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <h3 class="text-base font-bold text-gray-900 dark:text-white transition-colors duration-300 group-hover:text-brand">
              Adicionar Comentário
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-300 group-hover:text-orange-600">
              {{ complaint.comments_count || 0 }} comentários existentes
            </p>
          </div>

          <!-- Arrow indicator -->
          <div
            v-if="!isCommentButtonDisabled"
            class="flex-shrink-0 opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-1"
          >
            <svg class="h-5 w-5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>

          <!-- Debug indicator -->
          <span
            v-if="isCommentButtonDisabled"
            class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full"
            title="Desativado: resolved ou rejected"
          ></span>
        </div>
      </button>

      <!-- Botões condicionais -->
      <template v-if="shouldShowActions">
        <!-- Definir Prioridade -->
        <button
          v-if="showPriorityButton"
          @click="handlePriorityClick"
          :disabled="isButtonDisabled('priority')"
          :class="[
            'group relative w-full overflow-hidden rounded-2xl p-5 text-left shadow-sm ring-1 transition-all duration-500 hover:-translate-y-1',
            !isButtonDisabled('priority')
              ? 'bg-gradient-to-r from-amber-50 via-orange-50 to-amber-100 dark:from-amber-900/30 dark:via-orange-900/30 dark:to-amber-800/40 ring-amber-200/50 dark:ring-amber-700/50 hover:shadow-xl hover:shadow-amber-500/10 dark:hover:shadow-amber-500/20 hover:ring-amber-300/60 dark:hover:ring-amber-600/60'
              : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 cursor-not-allowed ring-gray-200/50 dark:ring-gray-700/50',
          ]"
        >
          <!-- Background gradient animation -->
          <div
            v-if="!isButtonDisabled('priority')"
            class="absolute inset-0 bg-gradient-to-r from-amber-500/5 via-transparent to-amber-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
          ></div>

          <!-- Animated border -->
          <div
            v-if="!isButtonDisabled('priority')"
            class="absolute inset-0 rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-amber-500 opacity-0 transition-opacity duration-500 group-hover:opacity-20 blur-sm"
          ></div>

          <div class="relative flex items-center gap-4">
            <!-- Icon container -->
            <div
              v-if="!isButtonDisabled('priority')"
              class="relative flex-shrink-0"
            >
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-amber-500/30">
                <FlagIcon class="h-6 w-6 text-white transition-transform duration-300 group-hover:scale-110" />
              </div>
              <!-- Pulse effect -->
              <div class="absolute -inset-1 rounded-xl bg-amber-500/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100 group-hover:animate-ping"></div>
            </div>
            <div v-else class="relative flex-shrink-0">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-400 dark:bg-gray-600 shadow-lg">
                <FlagIcon class="h-6 w-6 text-white" />
              </div>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <h3 class="text-base font-bold text-amber-900 dark:text-amber-100 transition-colors duration-300 group-hover:text-amber-700 dark:group-hover:text-amber-200">
                Definir Prioridade
              </h3>
              <p class="text-sm text-amber-700 dark:text-amber-400 transition-colors duration-300 group-hover:text-amber-600 dark:group-hover:text-amber-300">
                Ajustar nível de urgência
              </p>
            </div>

            <!-- Arrow indicator -->
            <div
              v-if="!isButtonDisabled('priority')"
              class="flex-shrink-0 opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-1"
            >
              <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </div>
          </div>
        </button>

        <!-- Reatribuir Técnico -->
        <button
          v-if="showReassignButton"
          @click="handleReassignClick"
          :disabled="isButtonDisabled('reassign')"
          :class="[
            'group relative w-full overflow-hidden rounded-2xl p-5 text-left shadow-sm ring-1 transition-all duration-500 hover:-translate-y-1',
            !isButtonDisabled('reassign')
              ? 'bg-gradient-to-r from-cyan-50 via-blue-50 to-cyan-100 dark:from-cyan-900/30 dark:via-blue-900/30 dark:to-cyan-800/40 ring-cyan-200/50 dark:ring-cyan-700/50 hover:shadow-xl hover:shadow-cyan-500/10 dark:hover:shadow-cyan-500/20 hover:ring-cyan-300/60 dark:hover:ring-cyan-600/60'
              : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 cursor-not-allowed ring-gray-200/50 dark:ring-gray-700/50',
          ]"
        >
          <!-- Background gradient animation -->
          <div
            v-if="!isButtonDisabled('reassign')"
            class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-transparent to-cyan-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
          ></div>

          <!-- Animated border -->
          <div
            v-if="!isButtonDisabled('reassign')"
            class="absolute inset-0 rounded-2xl bg-gradient-to-r from-cyan-500 via-blue-500 to-cyan-500 opacity-0 transition-opacity duration-500 group-hover:opacity-20 blur-sm"
          ></div>

          <div class="relative flex items-center gap-4">
            <!-- Icon container -->
            <div
              v-if="!isButtonDisabled('reassign')"
              class="relative flex-shrink-0"
            >
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-cyan-500/30">
                <UserGroupIcon class="h-6 w-6 text-white transition-transform duration-300 group-hover:scale-110" />
              </div>
              <!-- Pulse effect -->
              <div class="absolute -inset-1 rounded-xl bg-cyan-500/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100 group-hover:animate-ping"></div>
            </div>
            <div v-else class="relative flex-shrink-0">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-400 dark:bg-gray-600 shadow-lg">
                <UserGroupIcon class="h-6 w-6 text-white" />
              </div>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <h3 class="text-base font-bold text-cyan-900 dark:text-cyan-100 transition-colors duration-300 group-hover:text-cyan-700 dark:group-hover:text-cyan-200">
                Reatribuir Técnico
              </h3>
              <p class="text-sm text-cyan-700 dark:text-cyan-400 transition-colors duration-300 group-hover:text-cyan-600 dark:group-hover:text-cyan-300">
                Alterar responsável técnico
              </p>
            </div>

            <!-- Arrow indicator -->
            <div
              v-if="!isButtonDisabled('reassign')"
              class="flex-shrink-0 opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-1"
            >
              <svg class="h-5 w-5 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </div>
          </div>
        </button>

        <!-- Enviar ao Director (apenas para Gestor quando não escalado) -->
        <button
          v-if="showSendToDirectorButton && !isEscalatedToDirector"
          @click="handleSendToDirectorClick"
          :disabled="isButtonDisabled('sendToDirector')"
          :class="[
            'group relative w-full overflow-hidden rounded-2xl p-5 text-left shadow-sm ring-1 transition-all duration-500 hover:-translate-y-1 mb-3',
            !isButtonDisabled('sendToDirector')
              ? 'bg-gradient-to-r from-emerald-50 via-green-50 to-emerald-100 dark:from-emerald-900/30 dark:via-green-900/30 dark:to-emerald-800/40 ring-emerald-200/50 dark:ring-emerald-700/50 hover:shadow-xl hover:shadow-emerald-500/10 dark:hover:shadow-emerald-500/20 hover:ring-emerald-300/60 dark:hover:ring-emerald-600/60'
              : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 cursor-not-allowed ring-gray-200/50 dark:ring-gray-700/50',
          ]"
        >
          <!-- Background gradient animation -->
          <div
            v-if="!isButtonDisabled('sendToDirector')"
            class="absolute inset-0 bg-gradient-to-r from-emerald-500/5 via-transparent to-emerald-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
          ></div>

          <!-- Animated border -->
          <div
            v-if="!isButtonDisabled('sendToDirector')"
            class="absolute inset-0 rounded-2xl bg-gradient-to-r from-emerald-500 via-green-500 to-emerald-500 opacity-0 transition-opacity duration-500 group-hover:opacity-20 blur-sm"
          ></div>

          <div class="relative flex items-center gap-4">
            <!-- Icon container -->
            <div
              v-if="!isButtonDisabled('sendToDirector')"
              class="relative flex-shrink-0"
            >
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-emerald-500/30">
                <PaperAirplaneIcon class="h-6 w-6 text-white transition-transform duration-300 group-hover:scale-110" />
              </div>
              <!-- Pulse effect -->
              <div class="absolute -inset-1 rounded-xl bg-emerald-500/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100 group-hover:animate-ping"></div>
            </div>
            <div v-else class="relative flex-shrink-0">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-400 dark:bg-gray-600 shadow-lg">
                <PaperAirplaneIcon class="h-6 w-6 text-white" />
              </div>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <h3 class="text-base font-bold text-emerald-900 dark:text-emerald-100 transition-colors duration-300 group-hover:text-emerald-700 dark:group-hover:text-emerald-200">
                Enviar ao Director
              </h3>
              <p class="text-sm text-emerald-700 dark:text-emerald-400 transition-colors duration-300 group-hover:text-emerald-600 dark:group-hover:text-emerald-300">
                Escalar para supervisão superior
              </p>
            </div>

            <!-- Arrow indicator -->
            <div
              v-if="!isButtonDisabled('sendToDirector')"
              class="flex-shrink-0 opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-1"
            >
              <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </div>
          </div>
        </button>

        <!-- Botão de Validação -->
        <button
          v-if="showValidationButton"
          @click="handleValidationClick"
          :disabled="isButtonDisabled('markComplete')"
          :class="[
            'group relative w-full overflow-hidden rounded-2xl p-5 text-left shadow-sm ring-1 transition-all duration-500 hover:-translate-y-1 mb-3',
            !isButtonDisabled('markComplete')
              ? 'bg-gradient-to-r from-emerald-50 via-green-50 to-emerald-100 dark:from-emerald-900/30 dark:via-green-900/30 dark:to-emerald-800/40 ring-emerald-200/50 dark:ring-emerald-700/50 hover:shadow-xl hover:shadow-emerald-500/10 dark:hover:shadow-emerald-500/20 hover:ring-emerald-300/60 dark:hover:ring-emerald-600/60'
              : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 cursor-not-allowed ring-gray-200/50 dark:ring-gray-700/50',
          ]"
        >
          <!-- Background gradient animation -->
          <div
            v-if="!isButtonDisabled('markComplete')"
            class="absolute inset-0 bg-gradient-to-r from-emerald-500/5 via-transparent to-emerald-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
          ></div>

          <!-- Animated border -->
          <div
            v-if="!isButtonDisabled('markComplete')"
            class="absolute inset-0 rounded-2xl bg-gradient-to-r from-emerald-500 via-green-500 to-emerald-500 opacity-0 transition-opacity duration-500 group-hover:opacity-20 blur-sm"
          ></div>

          <div class="relative flex items-center gap-4">
            <!-- Icon container -->
            <div
              v-if="!isButtonDisabled('markComplete')"
              class="relative flex-shrink-0"
            >
              <div v-if="loading.markComplete || loading.submitValidation" class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-400 dark:bg-gray-600 shadow-lg">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-white"></div>
              </div>
              <div v-else class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-emerald-500/30">
                <CheckCircleIcon class="h-6 w-6 text-white transition-transform duration-300 group-hover:scale-110" />
              </div>
              <!-- Pulse effect -->
              <div v-if="!isButtonDisabled('markComplete') && !(loading.markComplete || loading.submitValidation)" class="absolute -inset-1 rounded-xl bg-emerald-500/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100 group-hover:animate-ping"></div>
            </div>
            <div v-else class="relative flex-shrink-0">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-400 dark:bg-gray-600 shadow-lg">
                <CheckCircleIcon class="h-6 w-6 text-white" />
              </div>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <h3 class="text-base font-bold text-emerald-900 dark:text-emerald-100 transition-colors duration-300 group-hover:text-emerald-700 dark:group-hover:text-emerald-200">
                {{ loading.markComplete || loading.submitValidation ? 'A Processar...' : validationButtonText }}
              </h3>
              <p class="text-sm text-emerald-700 dark:text-emerald-400 transition-colors duration-300 group-hover:text-emerald-600 dark:group-hover:text-emerald-300">
                {{ loading.markComplete || loading.submitValidation ? 'Validando solicitação...' : 'Aprovar e finalizar processo' }}
              </p>
            </div>

            <!-- Arrow indicator -->
            <div
              v-if="!isButtonDisabled('markComplete') && !(loading.markComplete || loading.submitValidation)"
              class="flex-shrink-0 opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-1"
            >
              <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </div>
          </div>
        </button>

        <!-- Botão de Rejeição -->
        <button
          v-if="showRejectButton"
          @click="handleRejectClick"
          :disabled="isButtonDisabled('reject')"
          :class="[
            'group relative w-full overflow-hidden rounded-2xl p-5 text-left shadow-sm ring-1 transition-all duration-500 hover:-translate-y-1',
            !isButtonDisabled('reject')
              ? 'bg-gradient-to-r from-red-50 via-rose-50 to-red-100 dark:from-red-900/30 dark:via-rose-900/30 dark:to-red-800/40 ring-red-200/50 dark:ring-red-700/50 hover:shadow-xl hover:shadow-red-500/10 dark:hover:shadow-red-500/20 hover:ring-red-300/60 dark:hover:ring-red-600/60'
              : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 cursor-not-allowed ring-gray-200/50 dark:ring-gray-700/50',
          ]"
        >
          <!-- Background gradient animation -->
          <div
            v-if="!isButtonDisabled('reject')"
            class="absolute inset-0 bg-gradient-to-r from-red-500/5 via-transparent to-red-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
          ></div>

          <!-- Animated border -->
          <div
            v-if="!isButtonDisabled('reject')"
            class="absolute inset-0 rounded-2xl bg-gradient-to-r from-red-500 via-rose-500 to-red-500 opacity-0 transition-opacity duration-500 group-hover:opacity-20 blur-sm"
          ></div>

          <div class="relative flex items-center gap-4">
            <!-- Icon container -->
            <div
              v-if="!isButtonDisabled('reject')"
              class="relative flex-shrink-0"
            >
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-red-500 to-rose-600 shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-red-500/30">
                <XCircleIcon class="h-6 w-6 text-white transition-transform duration-300 group-hover:scale-110" />
              </div>
              <!-- Pulse effect -->
              <div class="absolute -inset-1 rounded-xl bg-red-500/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100 group-hover:animate-ping"></div>
            </div>
            <div v-else class="relative flex-shrink-0">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-400 dark:bg-gray-600 shadow-lg">
                <XCircleIcon class="h-6 w-6 text-white" />
              </div>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <h3 class="text-base font-bold text-red-900 dark:text-red-100 transition-colors duration-300 group-hover:text-red-700 dark:group-hover:text-red-200">
                Rejeitar Submissão
              </h3>
              <p class="text-sm text-red-700 dark:text-red-400 transition-colors duration-300 group-hover:text-red-600 dark:group-hover:text-red-300">
                Recusar e finalizar processo
              </p>
            </div>

            <!-- Arrow indicator -->
            <div
              v-if="!isButtonDisabled('reject')"
              class="flex-shrink-0 opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-1"
            >
              <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </div>
          </div>
        </button>
      </template>

      <!-- Botão para Director responder à solicitação -->
      <button
        v-if="isDirector && isEscalatedToDirector && !hasDirectorValidation"
        @click="handleValidationClick"
        class="group relative w-full overflow-hidden rounded-2xl p-5 text-left shadow-sm ring-1 bg-gradient-to-r from-slate-50 via-gray-50 to-slate-100 dark:from-slate-800 dark:via-gray-800 dark:to-slate-700 ring-gray-200/50 dark:ring-gray-700/50 hover:shadow-xl hover:shadow-purple-500/10 dark:hover:shadow-purple-500/20 hover:ring-purple-300/60 dark:hover:ring-purple-600/60 hover:-translate-y-1 transition-all duration-500 mb-3"
      >
        <!-- Background gradient animation -->
        <div class="absolute inset-0 bg-gradient-to-r from-purple-500/5 via-transparent to-purple-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>

        <!-- Animated border -->
        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-purple-500 via-violet-500 to-purple-500 opacity-0 transition-opacity duration-500 group-hover:opacity-20 blur-sm"></div>

        <div class="relative flex items-center gap-4">
          <!-- Icon container with enhanced styling -->
          <div class="relative flex-shrink-0">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-violet-600 shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-purple-500/30">
              <PaperAirplaneIcon class="h-6 w-6 text-white transition-transform duration-300 group-hover:scale-110" />
            </div>
            <!-- Pulse effect -->
            <div class="absolute -inset-1 rounded-xl bg-purple-500/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100 group-hover:animate-ping"></div>
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <h3 class="text-base font-bold text-gray-900 dark:text-white transition-colors duration-300 group-hover:text-purple-600 dark:group-hover:text-purple-400">
              Responder à Solicitação
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-300 group-hover:text-purple-500 dark:group-hover:text-purple-300">
              Revisar e aprovar solicitação do gestor
            </p>
          </div>

          <!-- Arrow indicator -->
          <div class="flex-shrink-0 opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-1">
            <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
        </div>
      </button>
    </div>
  </div>
</template>

<script setup>
import {
  ChatBubbleLeftIcon,
  FlagIcon,
  UserGroupIcon,
  PaperAirplaneIcon,
  CheckCircleIcon,
  XCircleIcon,
  ClockIcon,
  UserIcon,
} from "@heroicons/vue/24/outline";
import { computed } from "vue";

const props = defineProps({
  complaint: {
    type: Object,
    required: true,
  },
  technicians: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Object,
    default: () => ({}),
  },
  user: {
    type: Object,
    default: () => ({}),
  },
  isPendingApproval: {
    type: Boolean,
    default: false,
  },
  isRejected: {
    type: Boolean,
    default: false,
  },
  isResolved: {
    type: Boolean,
    default: false,
  },
  isApproved: {
    type: Boolean,
    default: false,
  },
  isEscalatedToDirector: {
    type: Boolean,
    default: false,
  },
  hasDirectorValidation: {
    type: Boolean,
    default: false,
  },
  isDirector: {
    type: Boolean,
    default: false,
  },
  isManager: {
    type: Boolean,
    default: false,
  },
  hasDirectorAssumedCase: {
    type: Boolean,
    default: false,
  },
  hasDirectorCommentedAndReturned: {
    type: Boolean,
    default: false,
  },
  isWaitingDirectorIntervention: {
    type: Boolean,
    default: false,
  },
  isCaseAssumedByDirector: {
    type: Boolean,
    default: false,
  },
  isCaseReturnedToManager: {
    type: Boolean,
    default: false,
  },
  shouldShowActions: {
    type: Boolean,
    default: true,
  },
  canComment: {
    type: Boolean,
    require: true,
  },
});

const emit = defineEmits(["open-modal", "refresh"]);

// ========== COMPUTED PROPERTIES ==========

const validationButtonText = computed(() => {
  if (props.isDirector && props.isEscalatedToDirector && !props.hasDirectorValidation) {
    return "Validar Solicitação";
  } else if (props.isPendingApproval) {
    return "Validar Aprovação";
  } else {
    return "Validar";
  }
});

const showPriorityButton = computed(() => {
  return props.shouldShowActions;
});

const showReassignButton = computed(() => {
  return props.shouldShowActions;
});

const showRejectButton = computed(() => {
  return props.shouldShowActions && !props.isRejected;
});

const showValidationButton = computed(() => {
  if (props.isDirector) {
    return props.isCaseAssumedByDirector && props.isPendingApproval;
  }

  if (props.isManager) {
    return props.shouldShowActions && props.isPendingApproval;
  }

  return false;
});

const showSendToDirectorButton = computed(() => {
  // Apenas Gestor pode enviar ao Director
  if (!props.isManager) return false;

  // Não mostrar se já foi escalado
  if (props.isEscalatedToDirector) return false;

  // Não mostrar se caso está resolvido ou rejeitado
  if (props.isResolved || props.isRejected || props.isApproved) return false;

  return props.shouldShowActions;
});

// ========== FUNÇÕES DE CLIQUE ==========

const isCommentButtonDisabled = computed(() => {
  console.log("=== DEBUG isCommentButtonDisabled ===");
  console.log("loading.comment:", props.loading.comment);
  console.log("canComment (from props):", props.canComment);
  console.log("isResolved:", props.isResolved);
  console.log("isRejected:", props.isRejected);
  console.log("status:", props.complaint?.status);

  // Se está em loading, desabilita
  if (props.loading.comment) {
    console.log("Desabilitado porque está em loading");
    return true;
  }

  // Usa a prop canComment que vem do composable
  // (que deve retornar false apenas para resolved/rejected)
  if (!props.canComment) {
    console.log("Desabilitado porque canComment é false");
    return true;
  }

  console.log("Habilitado - pode comentar");
  return false;
});

const handleCommentClick = () => {
  if (isCommentButtonDisabled.value) {
    console.log("Botão de comentários desabilitado, ignorando clique");
    console.log(
      "Debug: canComment=",
      props.canComment,
      "loading.comment=",
      props.loading.comment
    );
    return;
  }

  console.log("Abrindo modal de comentários");
  emit("open-modal", "comment");
};

const handlePriorityClick = () => {
  if (!props.shouldShowActions) return;
  emit("open-modal", "priority");
};

const handleReassignClick = () => {
  if (!props.shouldShowActions) return;
  emit("open-modal", "reassign");
};

const handleSendToDirectorClick = () => {
  if (!props.shouldShowActions) return;
  emit("open-modal", "sendToDirector");
};

const handleValidationClick = () => {
  if (props.isDirector && props.isEscalatedToDirector && !props.hasDirectorValidation) {
    // Director respondendo à solicitação do gestor pela primeira vez
    emit("open-modal", "approvalDirector");
  } else if (props.isDirector && props.isCaseAssumedByDirector) {
    // Director com caso assumido validando aprovação
    emit("open-modal", "validateSubmission");
  } else if (props.isManager) {
    // Gestor validando aprovação de técnico
    emit("open-modal", "validateSubmission");
  }
};

const handleRejectClick = () => {
  if (!props.shouldShowActions) return;
  emit("open-modal", "reject");
};

// ========== FUNÇÃO PARA VERIFICAR SE BOTÃO ESTÁ DESABILITADO ==========

const isButtonDisabled = (buttonType) => {
  // NOTA: Botão de comentários NÃO usa esta função - tem sua própria lógica

  // Verificar se está em loading
  if (props.loading[buttonType]) return true;

  // Se não deve mostrar ações, desabilita outros botões
  if (!props.shouldShowActions) return true;

  // Verificar estado final para outros botões
  if (props.isResolved || props.isRejected || props.isApproved) return true;

  return false;
};
</script>
