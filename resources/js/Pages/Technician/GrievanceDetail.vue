<template>
  <Layout :role="'technician'">
    <div class="space-y-4 sm:space-y-6">
      <!-- Breadcrumb & Header -->
      <div class="flex flex-col gap-3 sm:gap-4">
        <Link
          href="/tecnico/dashboard"
          class="text-sm text-brand hover:text-orange-700 font-medium flex items-center gap-1"
        >
          ← Voltar ao Painel
        </Link>
        <div
          class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
        >
          <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3"
          >
            <div class="flex-1">
              <h1
                class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-dark-text-primary"
              >
                {{ grievance.reference_number }}
              </h1>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ grievance.title }}
              </p>
            </div>
            <StatusBadge
              :status="grievance.status"
              :label="grievance.status_label"
              size="lg"
            />
          </div>
          <div class="flex flex-wrap gap-2">
            <span
              :class="priorityBadgeClass(grievance.priority)"
              class="rounded-full px-3 py-1 text-sm font-semibold"
            >
              {{ priorityLabel(grievance.priority) }}
            </span>
            <span
              class="rounded-full bg-blue-100 dark:bg-blue-900/20 px-3 py-1 text-sm text-blue-700 dark:text-blue-300 font-medium"
            >
              {{ grievance.category }}
            </span>
            <span
              v-if="grievance.district"
              class="rounded-full bg-gray-100 dark:bg-gray-700 px-3 py-1 text-sm text-gray-700 dark:text-gray-300 inline-flex items-center gap-2"
            >
              <MapPinIcon class="h-4 w-4 text-gray-600" />
              {{ grievance.district }}
            </span>
          </div>
        </div>
      </div>

      <!-- Main Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Left Column - 2/3 -->
        <div class="lg:col-span-2 space-y-4 sm:space-y-6">
          <!-- Description Card -->
          <div
            class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
          >
            <h2
              class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2"
            >
              <span
                class="flex items-center justify-center h-5 w-5 rounded-full bg-purple-100 dark:bg-purple-900/20 text-xs"
              >
                <DocumentTextIcon class="h-4 w-4 text-purple-600" />
              </span>
              Descrição da Reclamação
            </h2>
            <div
              class="prose prose-sm dark:prose-invert max-w-none bg-gray-50 dark:bg-dark-accent rounded-lg p-4"
              v-html="grievance.description"
            />
            <div
              class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"
            >
              <div class="text-sm">
                <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">
                  Submetida
                </p>
                <p class="text-gray-900 dark:text-dark-text-primary font-semibold">
                  {{ formatDate(grievance.submitted_at) }}
                </p>
              </div>
              <div class="text-sm">
                <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">
                  Atualizada
                </p>
                <p class="text-gray-900 dark:text-dark-text-primary font-semibold">
                  {{ formatRelative(grievance.updated_at) }}
                </p>
              </div>
              <div class="text-sm">
                <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">
                  Dias Aberto
                </p>
                <p class="text-gray-900 dark:text-dark-text-primary font-semibold">
                  {{ getDaysOpen() }}
                </p>
              </div>
            </div>
          </div>

          <!-- Timeline de Atualizações -->
          <div
            class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
          >
            <h2
              class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-6 flex items-center gap-2"
            >
              <span
                class="flex items-center justify-center h-6 w-6 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 text-white text-xs flex-shrink-0 shadow-lg"
              >
                <ClockIcon class="h-4 w-4" />
              </span>
              Histórico de Atualizações ({{ timelineData?.length || 0 }})
            </h2>
            <div
              v-if="!timelineData || timelineData.length === 0"
              class="text-center py-12 text-gray-500 dark:text-gray-400"
            >
              <div class="flex flex-col items-center gap-3">
                <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                  <ClockIcon class="h-8 w-8 text-gray-400" />
                </div>
                <p class="text-sm font-medium">Nenhuma atualização registada ainda</p>
                <p class="text-xs text-gray-400">As atualizações aparecerão aqui quando houver atividade</p>
              </div>
            </div>
            <div v-else class="relative">
              <!-- Timeline line -->
              <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gradient-to-b from-blue-500 via-blue-400 to-blue-300 dark:from-blue-600 dark:via-blue-500 dark:to-blue-400"></div>

              <div class="space-y-6 max-h-96 overflow-y-auto pr-2">
                <div
                  v-for="(update, idx) in timelineData"
                  :key="update.id"
                  class="relative flex gap-4 group animate-fade-in"
                  :style="{ animationDelay: `${idx * 100}ms` }"
                >
                  <!-- Timeline dot -->
                  <div
                    class="relative z-10 flex-shrink-0 w-12 h-12 rounded-full shadow-lg transition-all duration-300 group-hover:scale-110"
                    :class="getTimelineIconBg(update)"
                  >
                    <div class="flex items-center justify-center w-full h-full">
                      <component
                        :is="getTimelineIcon(update)"
                        class="h-5 w-5 text-white transition-transform duration-300 group-hover:scale-110"
                      />
                    </div>
                    <!-- Pulse effect -->
                    <div
                      class="absolute inset-0 rounded-full animate-ping opacity-20"
                      :class="getTimelineIconBg(update)"
                    ></div>
                  </div>

                  <!-- Content card -->
                  <div class="flex-1 min-w-0 pb-6">
                    <div
                      class="bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-700 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-600 transition-all duration-300 group-hover:shadow-lg group-hover:border-gray-300 dark:group-hover:border-gray-500 group-hover:-translate-y-1"
                    >
                      <!-- Header -->
                      <div class="flex items-start justify-between mb-2">
                        <h4 class="font-semibold text-gray-900 dark:text-white text-sm leading-tight">
                          {{ update.description }}
                        </h4>
                        <span
                          class="text-xs text-gray-500 dark:text-gray-400 font-medium px-2 py-1 rounded-full"
                          :class="getTimelineBadgeClass(update)"
                        >
                          {{ getTimelineBadgeText(update) }}
                        </span>
                      </div>

                      <!-- Meta info -->
                      <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 mb-3">
                        <UserCircleIcon class="h-3 w-3" />
                        <span class="font-medium">{{ update.user?.name || "Sistema" }}</span>
                        <span class="text-gray-400">•</span>
                        <span>{{ formatDate(update.created_at) }}</span>
                        <span class="text-gray-400">•</span>
                        <span>{{ formatShortDate(update.created_at) }}</span>
                      </div>

                      <!-- Comment -->
                      <div
                        v-if="update.comment"
                        class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 dark:border-blue-600 p-3 rounded-r-lg mb-3 transition-all duration-300 hover:bg-blue-100 dark:hover:bg-blue-900/30"
                      >
                        <div class="flex items-start gap-2">
                          <ChatBubbleLeftIcon class="h-4 w-4 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
                          <p class="text-sm text-blue-800 dark:text-blue-200 italic leading-relaxed">
                            "{{ update.comment }}"
                          </p>
                        </div>
                      </div>

                      <!-- Attachments -->
                      <div v-if="update.attachments?.length" class="space-y-2">
                        <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 font-medium">
                          <PaperClipIcon class="h-3 w-3" />
                          <span>Anexos ({{ update.attachments.length }})</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                          <a
                            v-for="attach in update.attachments"
                            :key="attach.id"
                            :href="attach.url"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 text-xs font-medium rounded-lg hover:bg-orange-200 dark:hover:bg-orange-900/50 transition-all duration-200 hover:shadow-sm transform hover:scale-105"
                          >
                            <PaperClipIcon class="h-3 w-3" />
                            <span class="truncate max-w-32">{{ attach.original_filename }}</span>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Anexos -->
          <div
            class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
          >
            <h2
              class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2"
            >
              <span
                class="flex items-center justify-center h-5 w-5 rounded-full bg-green-100 dark:bg-green-900/20 text-xs flex-shrink-0"
              >
                <PaperClipIcon class="h-4 w-4 text-green-600" />
              </span>
              Anexos ({{ grievance.attachments?.length || 0 }})
            </h2>
            <div
              v-if="grievance.attachments?.length === 0"
              class="text-center py-8 text-gray-500 dark:text-gray-400"
            >
              <p class="text-sm">Sem anexos no momento</p>
            </div>
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <a
                v-for="attach in grievance.attachments"
                :key="attach.id"
                :href="attach.url"
                target="_blank"
                class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-dark-accent border border-gray-200 dark:border-gray-600 hover:border-brand dark:hover:border-orange-500 transition-all group"
              >
                <DocumentTextIcon class="h-8 w-8 text-gray-400" />
                <div class="flex-1 min-w-0">
                  <p
                    class="text-sm font-medium text-gray-900 dark:text-dark-text-primary truncate group-hover:text-brand"
                  >
                    {{ attach.original_filename }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatFileSize(attach.size) }}
                  </p>
                </div>
                <ArrowDownTrayIcon class="h-5 w-5 group-hover:text-brand" />
              </a>
            </div>
          </div>
        </div>

        <!-- Right Column - 1/3 -->
        <div class="space-y-4 sm:space-y-6">
          <!-- Quick Status -->
          <div
            class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
          >
            <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4">
              Status
            </h2>
            <div class="space-y-3">
              <div
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg"
              >
                <span class="text-sm text-gray-600 dark:text-gray-400">Estado</span>
                <StatusBadge
                  :status="grievance.status"
                  :label="grievance.status_label"
                  size="sm"
                />
              </div>
              <div
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-accent rounded-lg"
              >
                <span class="text-sm text-gray-600 dark:text-gray-400">Prioridade</span>
                <span
                  :class="priorityBadgeClass(grievance.priority)"
                  class="px-2 py-1 text-xs font-semibold rounded"
                >
                  {{ priorityLabel(grievance.priority) }}
                </span>
              </div>
              <div
                v-if="grievance.is_pending_approval"
                class="rounded-lg border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/10 p-3 text-sm text-orange-900 dark:text-orange-300 flex items-center gap-2"
              >
                <ClockIcon class="h-4 w-4" />
                Aguardando aprovação do Gestor
              </div>
            </div>
          </div>

          <!-- Utente Info -->
          <div
            class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
          >
            <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4">
              Utente
            </h2>
            <div class="space-y-3">
              <div>
                <p class="text-xs text-gray-600 dark:text-gray-400 uppercase font-medium">
                  Nome
                </p>
                <p
                  class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary"
                >
                  {{ grievance.contact_name || "Anónimo" }}
                </p>
              </div>
              <div v-if="grievance.contact_email">
                <p class="text-xs text-gray-600 dark:text-gray-400 uppercase font-medium">
                  Email
                </p>
                <a
                  :href="`mailto:${grievance.contact_email}`"
                  class="text-sm text-brand hover:text-orange-700 font-medium"
                >
                  {{ grievance.contact_email }}
                </a>
              </div>
              <div v-if="grievance.contact_phone">
                <p class="text-xs text-gray-600 dark:text-gray-400 uppercase font-medium">
                  Telefone
                </p>
                <a
                  :href="`tel:${grievance.contact_phone}`"
                  class="text-sm text-brand hover:text-orange-700 font-medium"
                >
                  {{ grievance.contact_phone }}
                </a>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div
            class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
          >
            <h2 class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4">
              Ações
            </h2>
            <div class="space-y-2">
              <button
                v-if="grievance.can_start"
                type="button"
                @click="startWork"
                class="group relative w-full overflow-hidden rounded-2xl p-5 text-left shadow-sm ring-1 bg-gradient-to-r from-slate-50 via-gray-50 to-slate-100 dark:from-slate-800 dark:via-gray-800 dark:to-slate-700 ring-gray-200/50 dark:ring-gray-700/50 hover:shadow-xl hover:shadow-brand/10 dark:hover:shadow-brand/20 hover:ring-brand/30 dark:hover:ring-brand/40 hover:-translate-y-1 transition-all duration-500"
              >
                <!-- Background gradient animation -->
                <div class="absolute inset-0 bg-gradient-to-r from-brand/5 via-transparent to-brand/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>

                <!-- Animated border -->
                <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-brand via-orange-500 to-brand opacity-0 transition-opacity duration-500 group-hover:opacity-20 blur-sm"></div>

                <div class="relative flex items-center gap-4">
                  <!-- Icon container with enhanced styling -->
                  <div class="relative flex-shrink-0">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand to-orange-600 shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-brand/30">
                      <RocketLaunchIcon class="h-6 w-6 text-white transition-transform duration-300 group-hover:scale-110" />
                    </div>
                    <!-- Subtle pulse effect -->
                    <div class="absolute -inset-1 rounded-xl bg-brand/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100 group-hover:animate-ping"></div>
                  </div>

                  <!-- Content -->
                  <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white transition-colors duration-300 group-hover:text-brand">
                      Iniciar Trabalho
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-300 group-hover:text-orange-600">
                      Começar a trabalhar nesta reclamação
                    </p>
                  </div>

                  <!-- Arrow indicator -->
                  <div class="flex-shrink-0 opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-1">
                    <svg class="h-5 w-5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </div>
                </div>
              </button>
              <p v-else class="text-sm text-gray-600 dark:text-gray-400 text-center py-2">
                <span class="inline-flex items-center gap-2 justify-center">
                  <CheckIcon class="h-4 w-4 text-emerald-600" />
                  Trabalho iniciado
                </span>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Forms Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- Registar Atualização -->
        <div
          class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
        >
          <h2
            class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2"
          >
            <span
              class="flex items-center justify-center h-5 w-5 rounded-full bg-blue-100 dark:bg-blue-900/20 text-xs flex-shrink-0"
            >
              <PencilSquareIcon class="h-4 w-4 text-blue-600" />
            </span>
            Registar Atualização
          </h2>
          <form @submit.prevent="submitUpdate" class="space-y-4">
            <div>
              <label
                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
              >
                Descrição Curta *
              </label>
              <input
                v-model="updateForm.description"
                type="text"
                placeholder="Ex: Inspeção inicial realizada"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary placeholder:text-gray-500 focus:border-brand focus:ring-2 focus:ring-brand/20"
              />
              <p v-if="updateForm.errors.description" class="text-xs text-red-600 mt-1">
                {{ updateForm.errors.description }}
              </p>
            </div>

            <div>
              <label
                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
              >
                Comentário Detalhado *
              </label>
              <textarea
                v-model="updateForm.comment"
                rows="4"
                placeholder="Explique o que foi feito, descoberto e os próximos passos..."
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary placeholder:text-gray-500 focus:border-brand focus:ring-2 focus:ring-brand/20"
              ></textarea>
              <p v-if="updateForm.errors.comment" class="text-xs text-red-600 mt-1">
                {{ updateForm.errors.comment }}
              </p>
            </div>

            <div>
              <label
                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
              >
                Anexar Evidências (Fotos, PDFs, etc)
              </label>
              <input
                ref="updateFilesInput"
                type="file"
                multiple
                @change="handleUpdateFiles"
                class="w-full px-4 py-2 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 focus:border-brand focus:ring-2 focus:ring-brand/20 cursor-pointer"
              />
              <p
                class="text-xs text-gray-500 dark:text-gray-400 mt-1 inline-flex items-center gap-2"
              >
                <FolderIcon class="h-4 w-4" />
                Máx 10 arquivos, 2MB cada
              </p>
              <p v-if="updateForm.errors.attachments" class="text-xs text-red-600 mt-1">
                {{ updateForm.errors.attachments }}
              </p>
              <div v-if="updateForm.attachments.length > 0" class="mt-2 space-y-1">
                <p class="text-xs font-medium text-gray-700 dark:text-gray-300">
                  Selecionados:
                </p>
                <div class="space-y-1">
                  <p
                    v-for="(file, idx) in updateForm.attachments"
                    :key="idx"
                    class="text-xs bg-green-50 dark:bg-green-900/10 text-green-700 dark:text-green-400 px-2 py-1 rounded inline-flex items-center gap-2"
                  >
                    <CheckIcon class="h-4 w-4" />
                    {{ file.name }}
                  </p>
                </div>
              </div>
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
              <input
                v-model="updateForm.is_public"
                type="checkbox"
                class="rounded border-gray-300 dark:border-gray-600 dark:bg-dark-accent"
              />
              <span class="text-sm text-gray-700 dark:text-gray-300">
                Visível ao utente
              </span>
            </label>

            <button
              type="submit"
              :disabled="isProcessing"
              class="group relative w-full overflow-hidden rounded-2xl p-5 text-left shadow-sm ring-1 bg-gradient-to-r from-slate-50 via-gray-50 to-slate-100 dark:from-slate-800 dark:via-gray-800 dark:to-slate-700 ring-gray-200/50 dark:ring-gray-700/50 hover:shadow-xl hover:shadow-blue-500/10 dark:hover:shadow-blue-500/20 hover:ring-blue-300/60 dark:hover:ring-blue-600/60 hover:-translate-y-1 transition-all duration-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none"
            >
              <!-- Background gradient animation -->
              <div v-if="!isProcessing" class="absolute inset-0 bg-gradient-to-r from-blue-500/5 via-transparent to-blue-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>

              <!-- Animated border -->
              <div v-if="!isProcessing" class="absolute inset-0 rounded-2xl bg-gradient-to-r from-blue-500 via-cyan-500 to-blue-500 opacity-0 transition-opacity duration-500 group-hover:opacity-20 blur-sm"></div>

              <div class="relative flex items-center gap-4">
                <!-- Icon container with enhanced styling -->
                <div class="relative flex-shrink-0">
                  <div v-if="isProcessing" class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-400 dark:bg-gray-600 shadow-lg">
                    <ClockIcon class="h-6 w-6 text-white animate-spin" />
                  </div>
                  <div v-else class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-blue-500/30">
                    <CheckIcon class="h-6 w-6 text-white transition-transform duration-300 group-hover:scale-110" />
                  </div>
                  <!-- Pulse effect -->
                  <div v-if="!isProcessing" class="absolute -inset-1 rounded-xl bg-blue-500/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100 group-hover:animate-ping"></div>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                  <h3 class="text-base font-bold text-gray-900 dark:text-white transition-colors duration-300 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                    {{ isProcessing ? 'A Registar...' : 'Registar Atualização' }}
                  </h3>
                  <p class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-300 group-hover:text-blue-500 dark:group-hover:text-blue-300">
                    {{ isProcessing ? 'Salvando atualização...' : 'Adicionar progresso e comentários' }}
                  </p>
                </div>

                <!-- Arrow indicator -->
                <div v-if="!isProcessing" class="flex-shrink-0 opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-1">
                  <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </div>
              </div>
            </button>
          </form>
        </div>

        <!-- Solicitar Conclusão -->
        <div
          class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
        >
          <h2
            class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2"
          >
            <span
              class="flex items-center justify-center h-5 w-5 rounded-full bg-green-100 dark:bg-green-900/20 text-xs flex-shrink-0"
            >
              <CheckBadgeIcon class="h-4 w-4 text-green-600" />
            </span>
            Solicitar Conclusão
          </h2>
          <form @submit.prevent="requestCompletion" class="space-y-4">
            <div>
              <label
                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
              >
                Resumo da Resolução *
              </label>
              <textarea
                v-model="completionForm.resolution_summary"
                rows="4"
                placeholder="Descreva as ações finais realizadas, resultados e motivo da conclusão..."
                :disabled="!grievance.can_request_completion"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary placeholder:text-gray-500 focus:border-brand focus:ring-2 focus:ring-brand/20 disabled:bg-gray-100 dark:disabled:bg-gray-700"
              ></textarea>
              <p
                v-if="completionForm.errors.resolution_summary"
                class="text-xs text-red-600 mt-1"
              >
                {{ completionForm.errors.resolution_summary }}
              </p>
            </div>

            <div>
              <label
                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
              >
                Anexos Finais (Comprovantes, Relatórios, etc)
              </label>
              <input
                ref="completionFilesInput"
                type="file"
                multiple
                :disabled="!grievance.can_request_completion"
                @change="handleCompletionFiles"
                class="w-full px-4 py-2 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 focus:border-brand focus:ring-2 focus:ring-brand/20 cursor-pointer disabled:bg-gray-100 dark:disabled:bg-gray-700"
              />
              <p
                class="text-xs text-gray-500 dark:text-gray-400 mt-1 inline-flex items-center gap-2"
              >
                <FolderIcon class="h-4 w-4" />
                Máx 10 arquivos, 2MB cada
              </p>
              <p
                v-if="completionForm.errors.attachments"
                class="text-xs text-red-600 mt-1"
              >
                {{ completionForm.errors.attachments }}
              </p>
              <div v-if="completionForm.attachments.length > 0" class="mt-2 space-y-1">
                <p class="text-xs font-medium text-gray-700 dark:text-gray-300">
                  Selecionados:
                </p>
                <div class="space-y-1">
                  <p
                    v-for="(file, idx) in completionForm.attachments"
                    :key="idx"
                    class="text-xs bg-green-50 dark:bg-green-900/10 text-green-700 dark:text-green-400 px-2 py-1 rounded inline-flex items-center gap-2"
                  >
                    <CheckIcon class="h-4 w-4" />
                    {{ file.name }}
                  </p>
                </div>
              </div>
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
              <input
                v-model="completionForm.notify_user"
                type="checkbox"
                :disabled="!grievance.can_request_completion"
                class="rounded border-gray-300 dark:border-gray-600 dark:bg-dark-accent disabled:opacity-50"
              />
              <span class="text-sm text-gray-700 dark:text-gray-300">
                Notificar utente sobre a conclusão
              </span>
            </label>

            <div
              v-if="!grievance.can_request_completion"
              class="rounded-lg border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/10 p-3 text-sm text-orange-900 dark:text-orange-300 flex items-start gap-2"
            >
              <InformationCircleIcon class="h-5 w-5" />
              <div>
                Complete o trabalho registando atualizações antes de solicitar conclusão
              </div>
            </div>

            <button
              type="submit"
              :disabled="!grievance.can_request_completion || isProcessing"
              class="group relative w-full overflow-hidden rounded-2xl p-5 text-left shadow-sm ring-1 bg-gradient-to-r from-slate-50 via-gray-50 to-slate-100 dark:from-slate-800 dark:via-gray-800 dark:to-slate-700 ring-gray-200/50 dark:ring-gray-700/50 hover:shadow-xl hover:shadow-emerald-500/10 dark:hover:shadow-emerald-500/20 hover:ring-emerald-300/60 dark:hover:ring-emerald-600/60 hover:-translate-y-1 transition-all duration-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none"
            >
              <!-- Background gradient animation -->
              <div v-if="!isProcessing && grievance.can_request_completion" class="absolute inset-0 bg-gradient-to-r from-emerald-500/5 via-transparent to-emerald-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>

              <!-- Animated border -->
              <div v-if="!isProcessing && grievance.can_request_completion" class="absolute inset-0 rounded-2xl bg-gradient-to-r from-emerald-500 via-green-500 to-emerald-500 opacity-0 transition-opacity duration-500 group-hover:opacity-20 blur-sm"></div>

              <div class="relative flex items-center gap-4">
                <!-- Icon container with enhanced styling -->
                <div class="relative flex-shrink-0">
                  <div v-if="isProcessing" class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-400 dark:bg-gray-600 shadow-lg">
                    <ClockIcon class="h-6 w-6 text-white animate-spin" />
                  </div>
                  <div v-else-if="grievance.can_request_completion" class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-emerald-500/30">
                    <CheckIcon class="h-6 w-6 text-white transition-transform duration-300 group-hover:scale-110" />
                  </div>
                  <div v-else class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-400 dark:bg-gray-600 shadow-lg">
                    <CheckIcon class="h-6 w-6 text-white" />
                  </div>
                  <!-- Pulse effect -->
                  <div v-if="!isProcessing && grievance.can_request_completion" class="absolute -inset-1 rounded-xl bg-emerald-500/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100 group-hover:animate-ping"></div>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                  <h3 class="text-base font-bold text-gray-900 dark:text-white transition-colors duration-300 group-hover:text-emerald-600 dark:group-hover:text-emerald-400">
                    {{ isProcessing ? 'A Enviar...' : 'Solicitar Conclusão' }}
                  </h3>
                  <p class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-300 group-hover:text-emerald-500 dark:group-hover:text-emerald-300">
                    {{ isProcessing ? 'Enviando solicitação...' : 'Finalizar trabalho e solicitar aprovação' }}
                  </p>
                </div>

                <!-- Arrow indicator -->
                <div v-if="!isProcessing && grievance.can_request_completion" class="flex-shrink-0 opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-1">
                  <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </div>
              </div>
            </button>
          </form>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import Layout from "@/Layouts/UnifiedLayout.vue";
import StatusBadge from "@/Components/Grievance/StatusBadge.vue";
import {
  DocumentTextIcon,
  ClockIcon,
  PaperClipIcon,
  ArrowDownTrayIcon,
  RocketLaunchIcon,
  CheckIcon,
  PencilSquareIcon,
  FolderIcon,
  InformationCircleIcon,
  MapPinIcon,
  CheckBadgeIcon,
  UserCircleIcon,
  ChatBubbleLeftIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  UserIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  grievance: {
    type: Object,
    required: true,
  },
});

const isProcessing = ref(false);
const updateFilesInput = ref(null);
const completionFilesInput = ref(null);

// Dados da timeline
const timelineData = computed(() => {
  if (!props.grievance) return [];

  if (props.grievance.updates?.length > 0) {
    return props.grievance.updates;
  }

  if (props.grievance.activities?.length > 0) {
    return props.grievance.activities;
  }

  return [];
});

const updateForm = useForm({
  description: "",
  comment: "",
  is_public: true,
  attachments: [],
});

const completionForm = useForm({
  resolution_summary: "",
  notify_user: true,
  attachments: [],
});

const handleUpdateFiles = (event) => {
  updateForm.attachments = Array.from(event.target.files || []);
};

const handleCompletionFiles = (event) => {
  completionForm.attachments = Array.from(event.target.files || []);
};

const startWork = async () => {
  isProcessing.value = true;
  try {
    const routeUrl = route("technician.grievances.start", props.grievance.id);
    console.log("Route URL:", routeUrl);
    await router.patch(
      route("technician.grievances.start", props.grievance.id),
      {},
      {
        preserveScroll: true,
      }
    );
  } finally {
    isProcessing.value = false;
  }
};

const submitUpdate = async () => {
  isProcessing.value = true;
  try {
    await updateForm.post(
      route("technician.grievances.updates.store", props.grievance.id),
      {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
          updateForm.reset();
          if (updateFilesInput.value) updateFilesInput.value.value = null;
        },
        onError: () => {
          // Erro ao registar atualização
        },
      }
    );
  } finally {
    isProcessing.value = false;
  }
};

const requestCompletion = async () => {
  isProcessing.value = true;
  try {
    await completionForm.post(
      route("technician.grievances.request-completion", props.grievance.id),
      {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
          completionForm.reset();
          if (completionFilesInput.value) completionFilesInput.value.value = null;
        },
        onError: () => {
          // Erro ao solicitar conclusão
        },
      }
    );
  } finally {
    isProcessing.value = false;
  }
};

const priorityLabel = (priority) => {
  const map = {
    low: "Baixa",
    medium: "Média",
    high: "Alta",
  };
  return map[priority] ?? priority ?? "N/D";
};

const priorityBadgeClass = (priority) => {
  const map = {
    low: "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400",
    medium: "bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400",
    high: "bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400",
  };
  return map[priority] ?? "bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300";
};

const formatDate = (dateString) => {
  if (!dateString) return "N/D";
  return new Date(dateString).toLocaleDateString("pt-PT", {
    day: "2-digit",
    month: "long",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const formatRelative = (dateString) => {
  if (!dateString) return "N/D";
  const date = new Date(dateString);
  const now = new Date();
  const diff = now - date;
  const hours = Math.floor(diff / (1000 * 60 * 60));
  if (hours < 1) return "Agora";
  if (hours < 24) return `${hours}h atrás`;
  const days = Math.floor(hours / 24);
  if (days < 7) return `${days}d atrás`;
  return date.toLocaleDateString("pt-PT", { month: "short", day: "2-digit" });
};

const getDaysOpen = () => {
  if (!props.grievance?.created_at) return 0;
  const created = new Date(props.grievance.created_at);
  const now = new Date();
  return Math.floor((now - created) / (1000 * 60 * 60 * 24));
};

const formatFileSize = (bytes) => {
  if (!bytes) return "N/D";
  const k = 1024;
  const sizes = ["B", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + " " + sizes[i];
};

const formatShortDate = (dateString) => {
  if (!dateString) return "N/A";
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString("pt-PT", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch (error) {
    return "Data inválida";
  }
};

const getTimelineIcon = (update) => {
  // Determinar ícone baseado no tipo de ação
  const actionType = update.action_type || update.type || '';

  if (actionType.includes('escalated') || actionType.includes('director')) {
    return PaperAirplaneIcon;
  }
  if (actionType.includes('assigned') || actionType.includes('technician')) {
    return UserCircleIcon;
  }
  if (actionType.includes('status') || actionType.includes('resolved') || actionType.includes('closed')) {
    return CheckCircleIcon;
  }
  if (actionType.includes('comment') || actionType.includes('message')) {
    return ChatBubbleLeftIcon;
  }
  if (actionType.includes('attachment') || actionType.includes('file')) {
    return PaperClipIcon;
  }
  if (actionType.includes('priority')) {
    return ExclamationTriangleIcon;
  }

  // Fallback baseado no role do usuário
  if (update.user?.role === 'Director') {
    return CheckBadgeIcon;
  }
  if (update.user?.role === 'Manager') {
    return UserIcon;
  }

  return ClockIcon;
};

const getTimelineIconBg = (update) => {
  // Determinar cor do fundo baseada no tipo de ação
  const actionType = update.action_type || update.type || '';

  if (actionType.includes('escalated') || actionType.includes('director')) {
    return 'bg-purple-500';
  }
  if (actionType.includes('assigned') || actionType.includes('technician')) {
    return 'bg-blue-500';
  }
  if (actionType.includes('status') || actionType.includes('resolved') || actionType.includes('closed')) {
    return 'bg-green-500';
  }
  if (actionType.includes('comment') || actionType.includes('message')) {
    return 'bg-indigo-500';
  }
  if (actionType.includes('attachment') || actionType.includes('file')) {
    return 'bg-orange-500';
  }
  if (actionType.includes('priority')) {
    return 'bg-red-500';
  }

  // Fallback baseado no role do usuário
  if (update.user?.role === 'Director') {
    return 'bg-emerald-500';
  }
  if (update.user?.role === 'Manager') {
    return 'bg-cyan-500';
  }

  return 'bg-gray-500';
};

const getTimelineBadgeText = (update) => {
  // Determinar texto do badge baseado no tipo de ação
  const actionType = update.action_type || update.type || '';

  if (actionType.includes('escalated_to_director')) {
    return 'Escalado';
  }
  if (actionType.includes('assigned_to_technician')) {
    return 'Atribuído';
  }
  if (actionType.includes('status_changed')) {
    return 'Status';
  }
  if (actionType.includes('comment_added')) {
    return 'Comentário';
  }
  if (actionType.includes('attachment_added')) {
    return 'Anexo';
  }
  if (actionType.includes('priority_changed')) {
    return 'Prioridade';
  }
  if (actionType.includes('director_validation')) {
    return 'Director';
  }

  // Fallback baseado no role do usuário
  if (update.user?.role === 'Director') {
    return 'Director';
  }
  if (update.user?.role === 'Manager') {
    return 'Gestor';
  }
  if (update.user?.role === 'Technician') {
    return 'Técnico';
  }

  return 'Sistema';
};

const getTimelineBadgeClass = (update) => {
  // Determinar classe do badge baseada no tipo de ação
  const actionType = update.action_type || update.type || '';

  if (actionType.includes('escalated') || actionType.includes('director')) {
    return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400';
  }
  if (actionType.includes('assigned') || actionType.includes('technician')) {
    return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
  }
  if (actionType.includes('status') || actionType.includes('resolved') || actionType.includes('closed')) {
    return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
  }
  if (actionType.includes('comment') || actionType.includes('message')) {
    return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400';
  }
  if (actionType.includes('attachment') || actionType.includes('file')) {
    return 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400';
  }
  if (actionType.includes('priority')) {
    return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
  }

  // Fallback baseado no role do usuário
  if (update.user?.role === 'Director') {
    return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400';
  }
  if (update.user?.role === 'Manager') {
    return 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-400';
  }

  return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
};
</script>
