<template>
  <AppLayout :title="`Submissão #${submission?.reference_number || 'Carregando...'}`">
    <div class="space-y-4 sm:space-y-6">
      <!-- Estado de carregamento -->
      <div v-if="!complaint" class="text-center py-12">
        <div
          class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand mx-auto"
        ></div>
        <p class="mt-4 text-gray-600 dark:text-gray-400">
          A carregar detalhes da submissão...
        </p>
      </div>

      <!-- Conteúdo principal -->
      <div v-else>
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col gap-3 sm:gap-4">
          <Link
            href="/director/complaints-overview"
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
                  {{ complaint.reference_number || "N/A" }}
                </h1>
              </div>
              <StatusBadge
                :status="submission.status"
                :label="getStatusLabel(submission.status)"
                size="lg"
              />
            </div>
            <div class="flex flex-wrap gap-2">
              <span :class="getTypeBadgeClass(complaint.type)">
                {{ getTypeLabel(complaint.type) }}
              </span>
              <span :class="getPriorityBadgeClass(complaint.priority)">
                {{ getPriorityLabel(complaint.priority) }}
              </span>
              <span :class="getStatusBadgeClass(complaint.status)">
                {{ getStatusLabel(complaint.status) }}
              </span>
              <!-- Badge para casos escalados -->
              <span
                v-if="isEscalatedToDirector"
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400"
              >
                <PaperAirplaneIcon class="h-4 w-4 mr-1" />
                {{ escalationStatusText }}
              </span>
              <!-- Badge para Director View -->
              <span
                v-if="isDirector"
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400"
              >
                <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path
                    fill-rule="evenodd"
                    d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                    clip-rule="evenodd"
                  />
                </svg>
                Visão do Director
              </span>
            </div>
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
              v-html="complaint.description || 'Sem descrição disponível'"
            />
            <div
              class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"
            >
              <div class="text-sm">
                <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">
                  Submetido por
                </p>
                <p class="text-gray-900 dark:text-dark-text-primary font-semibold">
                  {{ complaint.user?.name || complaint.contact_name || "Anónimo" }}
                </p>
              </div>
              <div class="text-sm">
                <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">
                  Email
                </p>
                <p class="text-gray-900 dark:text-dark-text-primary font-semibold">
                  {{ complaint.user?.email || complaint.contact_email || "N/A" }}
                </p>
              </div>
              <div class="text-sm">
                <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">
                  Data de Criação
                </p>
                <p class="text-gray-900 dark:text-dark-text-primary font-semibold">
                  {{ formatDate(complaint.created_at) }}
                </p>
              </div>
              <div class="text-sm">
                <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">
                  Categoria
                </p>
                <p class="text-gray-900 dark:text-dark-text-primary font-semibold">
                  {{ complaint.category || "N/A" }}
                </p>
              </div>
              <div class="text-sm">
                <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">
                  Telefone
                </p>
                <p class="text-gray-900 dark:text-dark-text-primary font-semibold">
                  {{ complaint.contact_phone || "N/A" }}
                </p>
              </div>
              <div class="text-sm">
                <p class="text-gray-600 dark:text-gray-400 text-xs uppercase font-medium">
                  Localização
                </p>
                <p class="text-gray-900 dark:text-dark-text-primary font-semibold">
                  {{ complaint.province || "N/A" }}, {{ complaint.district || "N/A" }}
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
                <div
                  class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center"
                >
                  <ClockIcon class="h-8 w-8 text-gray-400" />
                </div>
                <p class="text-sm font-medium">Nenhuma atualização registada ainda</p>
                <p class="text-xs text-gray-400">
                  As atualizações aparecerão aqui quando houver atividade
                </p>
              </div>
            </div>
            <div v-else class="relative">
              <!-- Timeline line -->
              <div
                class="absolute left-6 top-0 bottom-0 w-0.5 bg-gradient-to-b from-blue-500 via-blue-400 to-blue-300 dark:from-blue-600 dark:via-blue-500 dark:to-blue-400"
              ></div>

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
                        <h4
                          class="font-semibold text-gray-900 dark:text-white text-sm leading-tight"
                        >
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
                      <div
                        class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 mb-3"
                      >
                        <UserCircleIcon class="h-3 w-3" />
                        <span class="font-medium">{{
                          update.user?.name || "Sistema"
                        }}</span>
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
                          <ChatBubbleLeftIcon
                            class="h-4 w-4 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0"
                          />
                          <p
                            class="text-sm text-blue-800 dark:text-blue-200 italic leading-relaxed"
                          >
                            "{{ update.comment }}"
                          </p>
                        </div>
                      </div>

                      <!-- Attachments -->
                      <div v-if="update.attachments?.length" class="space-y-2">
                        <div
                          class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 font-medium"
                        >
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
                            <span class="truncate max-w-32">{{
                              attach.original_filename
                            }}</span>
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
              Anexos ({{ complaint.attachments?.length || 0 }})
            </h2>
            <div
              v-if="!complaint.attachments || complaint.attachments.length === 0"
              class="text-center py-8 text-gray-500 dark:text-gray-400"
            >
              <p class="text-sm">Sem anexos no momento</p>
            </div>
            <div v-else class="space-y-2">
              <a
                v-for="attach in complaint.attachments"
                :key="attach.id"
                :href="attach.url"
                target="_blank"
                class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-dark-accent border border-gray-200 dark:border-gray-600 hover:border-brand dark:hover:border-orange-500 transition-all group"
              >
                <DocumentTextIcon
                  class="h-6 w-6 text-gray-400 group-hover:text-brand transition-colors duration-300"
                />
                <div class="flex-1 min-w-0">
                  <p
                    class="text-sm font-medium text-gray-900 dark:text-dark-text-primary group-hover:text-brand transition-colors duration-300"
                  >
                    {{
                      attach.original_filename ||
                      attach.filename ||
                      attach.name ||
                      "Arquivo sem nome"
                    }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatFileSize(attach.size) }}
                  </p>
                </div>
                <ArrowDownTrayIcon
                  class="h-5 w-5 text-gray-400 group-hover:text-brand transition-colors duration-300"
                />
              </a>
            </div>
          </div>

          <!-- SECÇÃO DE INFORMAÇÕES DO ESCALAMENTO -->
          <div
            v-if="isEscalatedToDirector"
            class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
          >
            <div class="flex items-center gap-2 mb-4">
              <PaperAirplaneIcon class="h-6 w-6 text-brand dark:text-purple-400" />
              <h3 class="text-lg font-bold text-brand dark:text-purple-300">
                Solicitação do Gestor
              </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Quem escalou -->
              <div class="space-y-1">
                <p class="text-sm text-brand dark:text-purple-400 font-medium">
                  Solicitado por:
                </p>
                <div
                  class="flex items-center gap-2 p-3 bg-white dark:bg-dark-secondary rounded-lg border"
                >
                  <UserCircleIcon class="h-5 w-5 text-gray-500" />
                  <div>
                    <span class="font-medium text-gray-800 dark:text-gray-200">
                      {{ escalationDetails.escalated_by?.name || "Gestor" }}
                    </span>
                    <p
                      v-if="escalationDetails.escalated_by?.email"
                      class="text-xs text-gray-500"
                    >
                      {{ escalationDetails.escalated_by.email }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Data do escalamento -->
              <div class="space-y-1">
                <p class="text-sm text-brand dark:text-purple-400 font-medium">
                  Data da Solicitação:
                </p>

                <div
                  class="flex items-center gap-2 p-3 bg-white dark:bg-dark-secondary rounded-lg border"
                >
                  <CalendarIcon class="h-5 w-5 text-gray-500" />
                  <span class="font-medium text-gray-800 dark:text-gray-200">
                    {{ formatDateTime(escalationDetails.escalated_at) }}
                  </span>
                </div>
              </div>

              <!-- Motivo do escalamento -->
              <div class="space-y-1 md:col-span-2">
                <p class="text-sm text-brand dark:text-purple-400 font-medium">
                  Motivo da Solicitação:
                </p>
                <div
                  class="p-4 bg-white dark:bg-dark-secondary rounded-lg border border-gray-200 dark:border-gray-700"
                >
                  <div class="flex items-start gap-2">
                    <ChatBubbleLeftIcon class="h-5 w-5 text-gray-500 mt-0.5" />

                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                      {{
                        escalationDetails.escalation_reason ||
                        complaint.escalation_reason ||
                        "Motivo não especificado"
                      }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Comentário do escalamento -->
              <div v-if="getManagerComment()" class="space-y-1 md:col-span-2">
                <p class="text-sm text-brand dark:text-purple-400 font-medium">
                  Comentário do Gestor:
                </p>
                <div
                  class="p-4 bg-white dark:bg-dark-secondary rounded-lg border border-gray-200 dark:border-gray-700"
                >
                  <div class="flex items-start gap-2">
                    <ChatBubbleLeftIcon class="h-5 w-5 text-gray-500 mt-0.5" />
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                      {{ getManagerComment() }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- ========== RESPOSTA DO DIRECTOR ========== -->
              <!-- RESPOSTA DO DIRECTOR - VISÍVEL PARA TODOS -->
              <div
                v-if="hasDirectorValidation"
                class="md:col-span-2 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700"
              >
                <div class="space-y-4">
                  <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                      <CheckBadgeIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                      <h4 class="text-lg font-bold text-gray-900 dark:text-white">
                        Resposta do Director
                      </h4>
                    </div>
                  </div>

                  <!-- Card de resposta -->
                  <div
                    class="p-5 rounded-xl border"
                    :class="getValidationCardClass(directorValidationStatus)"
                  >
                    <!-- Cabeçalho da resposta -->
                    <div class="flex items-start justify-between mb-4">
                      <div class="flex items-center gap-3">
                        <!-- Status da resposta -->
                        <span
                          :class="getValidationStatusBadgeClass(directorValidationStatus)"
                        >
                          {{ getValidationLabel(directorValidationStatus) }}
                        </span>

                        <!-- Informações de quem validou -->
                        <div class="flex items-center gap-2">
                          <UserCircleIcon class="h-5 w-5 text-gray-500" />
                          <div>
                            <p
                              class="text-sm font-medium text-gray-800 dark:text-gray-200"
                            >
                              {{ getValidatorName(complaint.director_validation) }}
                            </p>
                            <p class="text-xs text-gray-500">
                              Respondeu em
                              {{
                                formatDateTime(
                                  complaint.director_validation?.validated_at ||
                                    complaint.metadata?.director_validation?.validated_at
                                )
                              }}
                              <span
                                v-if="
                                  complaint.director_validation?.updated_at &&
                                  complaint.director_validation.updated_at !==
                                    complaint.director_validation.validated_at
                                "
                              >
                                • Editado em
                                {{
                                  formatDateTime(complaint.director_validation.updated_at)
                                }}
                              </span>
                            </p>
                          </div>
                        </div>
                      </div>

                      <!-- Ícone do status -->
                      <CheckBadgeIcon
                        v-if="directorValidationStatus === 'approved'"
                        class="h-8 w-8 text-green-600 dark:text-green-400"
                      />
                      <ExclamationTriangleIcon
                        v-else-if="directorValidationStatus === 'rejected'"
                        class="h-8 w-8 text-red-600 dark:text-red-400"
                      />
                      <InformationCircleIcon
                        v-else
                        class="h-8 w-8 text-yellow-600 dark:text-yellow-400"
                      />
                    </div>

                    <!-- Comentário da resposta -->
                    <div
                      v-if="
                        complaint.director_validation?.comment ||
                        complaint.metadata?.director_validation?.comment
                      "
                      class="mt-4 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700"
                    >
                      <div class="flex items-start justify-between mb-2">
                        <p
                          class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                          Comentário do Director:
                        </p>

                        <!-- BOTÃO EDITAR RESPOSTA (dentro do comentário) -->
                        <button
                          v-if="isDirector && !isResolved && !isRejected"
                          @click="openValidationModalForEdit"
                          class="ml-4 inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors shadow-sm"
                          title="Editar resposta"
                        >
                          <PencilSquareIcon class="h-3 w-3 mr-1.5" />
                          Editar
                        </button>
                      </div>

                      <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                        {{
                          complaint.director_validation?.comment ||
                          complaint.metadata?.director_validation?.comment
                        }}
                      </p>
                    </div>

                    <!-- Botão Editar Resposta (alternativo - abaixo do comentário) -->
                    <div
                      v-if="
                        isDirector &&
                        !isResolved &&
                        !isRejected &&
                        !complaint.director_validation?.comment &&
                        !complaint.metadata?.director_validation?.comment
                      "
                      class="mt-4 flex justify-end"
                    >
                      <button
                        @click="openValidationModalForEdit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm transition-colors shadow-sm"
                      >
                        <PencilSquareIcon class="h-4 w-4 mr-2" />
                        Editar Resposta
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- BOTÃO VALIDAR - APENAS PARA DIRECTOR (se não houver validação ainda) -->
              <div
                v-else-if="isDirector && isEscalatedToDirector"
                class="md:col-span-2 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700"
              >
                <div
                  class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700"
                >
                  <div class="flex items-start gap-3">
                    <InformationCircleIcon
                      class="h-6 w-6 text-blue-600 dark:text-blue-400 mt-0.5"
                    />
                    <div class="flex-1">
                      <h4 class="font-medium text-blue-800 dark:text-blue-300 mb-2">
                        Solicitação do Gestor
                      </h4>
                      <p class="text-sm text-blue-700 dark:text-blue-400 mb-3">
                        O gestor solicitou sua intervenção neste caso. Por favor, analise
                        e forneça uma resposta.
                      </p>
                      <button
                        @click="openValidationModal"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm transition-colors shadow-sm"
                      >
                        <CheckBadgeIcon class="h-5 w-5 mr-2" />
                        Validar Solicitação
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column - 1/3 -->
        <div class="space-y-4 sm:space-y-6">
          <!-- Quick Actions -->
          <div
            class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
          >
            <h2
              class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-6 flex items-center gap-2"
            >
              <span
                class="flex items-center justify-center h-6 w-6 rounded-full bg-gradient-to-br from-orange-500 to-orange-600 text-white text-xs flex-shrink-0 shadow-lg"
              >
                <BoltIcon class="h-4 w-4" />
              </span>
              Ações Rápidas
            </h2>
            <div class="space-y-4">
              <!-- Comment Button -->
              <button
                v-if="canComment"
                @click="openCommentModal"
                class="group relative w-full overflow-hidden rounded-2xl bg-gradient-to-r from-slate-50 via-gray-50 to-slate-100 dark:from-slate-800 dark:via-gray-800 dark:to-slate-700 p-5 text-left shadow-sm ring-1 ring-gray-200/50 dark:ring-gray-700/50 transition-all duration-500 hover:shadow-xl hover:shadow-brand/10 dark:hover:shadow-brand/20 hover:ring-brand/30 dark:hover:ring-brand/40 hover:-translate-y-1"
              >
                <!-- Background gradient animation -->
                <div
                  class="absolute inset-0 bg-gradient-to-r from-brand/5 via-transparent to-brand/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                ></div>

                <!-- Animated border -->
                <div
                  class="absolute inset-0 rounded-2xl bg-gradient-to-r from-brand via-orange-500 to-brand opacity-0 transition-opacity duration-500 group-hover:opacity-20 blur-sm"
                ></div>

                <div class="relative flex items-center gap-4">
                  <!-- Icon container with enhanced styling -->
                  <div class="relative flex-shrink-0">
                    <div
                      class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand to-orange-600 shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-brand/30"
                    >
                      <ChatBubbleLeftIcon
                        class="h-6 w-6 text-white transition-transform duration-300 group-hover:scale-110"
                      />
                    </div>
                    <!-- Subtle pulse effect -->
                    <div
                      class="absolute -inset-1 rounded-xl bg-brand/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100 group-hover:animate-ping"
                    ></div>
                  </div>

                  <!-- Content -->
                  <div class="flex-1 min-w-0">
                    <h3
                      class="text-base font-bold text-gray-900 dark:text-white transition-colors duration-300 group-hover:text-brand"
                    >
                      Adicionar Comentário
                    </h3>
                    <p
                      class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-300 group-hover:text-orange-600"
                    >
                      {{ localComments?.length || 0 }} comentários existentes
                    </p>
                  </div>

                  <!-- Arrow indicator -->
                  <div
                    class="flex-shrink-0 opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-1"
                  >
                    <svg
                      class="h-5 w-5 text-brand"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5l7 7-7 7"
                      />
                    </svg>
                  </div>
                </div>
              </button>

              <!-- Actions for Director -->
              <template v-if="isDirector && shouldShowActions">
                <!-- Validate Request Button -->
                <button
                  v-if="isEscalatedToDirector && !hasDirectorValidation"
                  @click="openValidationModal"
                  class="group relative w-full overflow-hidden rounded-2xl bg-gradient-to-r from-blue-50 via-indigo-50 to-blue-100 dark:from-blue-900/30 dark:via-indigo-900/30 dark:to-blue-800/40 p-5 text-left shadow-sm ring-1 ring-blue-200/50 dark:ring-blue-700/50 transition-all duration-500 hover:shadow-xl hover:shadow-blue-500/10 dark:hover:shadow-blue-500/20 hover:ring-blue-300/60 dark:hover:ring-blue-600/60 hover:-translate-y-1"
                >
                  <!-- Background gradient animation -->
                  <div
                    class="absolute inset-0 bg-gradient-to-r from-blue-500/5 via-transparent to-blue-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                  ></div>

                  <!-- Animated border -->
                  <div
                    class="absolute inset-0 rounded-2xl bg-gradient-to-r from-blue-500 via-indigo-500 to-blue-500 opacity-0 transition-opacity duration-500 group-hover:opacity-20 blur-sm"
                  ></div>

                  <div class="relative flex items-center gap-4">
                    <!-- Icon container -->
                    <div class="relative flex-shrink-0">
                      <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-blue-500/30"
                      >
                        <CheckBadgeIcon
                          class="h-6 w-6 text-white transition-transform duration-300 group-hover:scale-110"
                        />
                      </div>
                      <!-- Pulse effect -->
                      <div
                        class="absolute -inset-1 rounded-xl bg-blue-500/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100 group-hover:animate-ping"
                      ></div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                      <h3
                        class="text-base font-bold text-blue-900 dark:text-blue-100 transition-colors duration-300 group-hover:text-blue-700 dark:group-hover:text-blue-200"
                      >
                        Validar Solicitação
                      </h3>
                      <p
                        class="text-sm text-blue-700 dark:text-blue-400 transition-colors duration-300 group-hover:text-blue-600 dark:group-hover:text-blue-300"
                      >
                        Responder ao gestor sobre este caso
                      </p>
                    </div>

                    <!-- Arrow indicator -->
                    <div
                      class="flex-shrink-0 opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-1"
                    >
                      <svg
                        class="h-5 w-5 text-blue-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M9 5l7 7-7 7"
                        />
                      </svg>
                    </div>
                  </div>
                </button>

                <!-- Edit Response Button -->
                <button
                  v-if="hasDirectorValidation && !isResolved && !isRejected"
                  @click="openValidationModalForEdit"
                  class="group relative w-full overflow-hidden rounded-2xl bg-gradient-to-r from-amber-50 via-orange-50 to-amber-100 dark:from-amber-900/30 dark:via-orange-900/30 dark:to-amber-800/40 p-5 text-left shadow-sm ring-1 ring-amber-200/50 dark:ring-amber-700/50 transition-all duration-500 hover:shadow-xl hover:shadow-amber-500/10 dark:hover:shadow-amber-500/20 hover:ring-amber-300/60 dark:hover:ring-amber-600/60 hover:-translate-y-1"
                >
                  <!-- Background gradient animation -->
                  <div
                    class="absolute inset-0 bg-gradient-to-r from-amber-500/5 via-transparent to-amber-500/5 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                  ></div>

                  <!-- Animated border -->
                  <div
                    class="absolute inset-0 rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-amber-500 opacity-0 transition-opacity duration-500 group-hover:opacity-20 blur-sm"
                  ></div>

                  <div class="relative flex items-center gap-4">
                    <!-- Icon container -->
                    <div class="relative flex-shrink-0">
                      <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-amber-500/30"
                      >
                        <PencilSquareIcon
                          class="h-6 w-6 text-white transition-transform duration-300 group-hover:scale-110"
                        />
                      </div>
                      <!-- Pulse effect -->
                      <div
                        class="absolute -inset-1 rounded-xl bg-amber-500/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100 group-hover:animate-ping"
                      ></div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                      <h3
                        class="text-base font-bold text-amber-900 dark:text-amber-100 transition-colors duration-300 group-hover:text-amber-700 dark:group-hover:text-amber-200"
                      >
                        Editar Resposta
                      </h3>
                      <p
                        class="text-sm text-amber-700 dark:text-amber-400 transition-colors duration-300 group-hover:text-amber-600 dark:group-hover:text-amber-300"
                      >
                        Modificar validação existente
                      </p>
                    </div>

                    <!-- Arrow indicator -->
                    <div
                      class="flex-shrink-0 opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-1"
                    >
                      <svg
                        class="h-5 w-5 text-amber-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M9 5l7 7-7 7"
                        />
                      </svg>
                    </div>
                  </div>
                </button>
              </template>

              <!-- Actions for Manager -->
              <template v-if="isManager && shouldManagerSeeActions">
                <GrievanceActions
                  :complaint="complaint"
                  :technicians="technicians"
                  :loading="loading"
                  :user="user"
                  :can-comment="canComment"
                  :is-resolved="isResolved"
                  :is-rejected="isRejected"
                  :is-pending-approval="isPendingApproval"
                  :is-approved="isApproved"
                  :is-escalated-to-director="isEscalatedToDirector"
                  :has-director-validation="hasDirectorValidation"
                  :is-director="isDirector"
                  :is-manager="isManager"
                  :has-director-assumed-case="isCaseAssumedByDirector"
                  :has-director-commented-and-returned="isCaseReturnedToManager"
                  :is-waiting-director-intervention="isWaitingDirectorIntervention"
                  :is-case-assumed-by-director="isCaseAssumedByDirector"
                  :is-case-returned-to-manager="isCaseReturnedToManager"
                  :should-show-actions="shouldShowActions"
                  @open-modal="handleOpenModal"
                />
              </template>

              <!-- Message when actions are suspended -->
              <div
                v-if="!isDirector && !shouldManagerSeeActions"
                class="group relative overflow-hidden rounded-2xl bg-gradient-to-r from-yellow-50 via-amber-50 to-yellow-100 dark:from-yellow-900/20 dark:via-amber-900/20 dark:to-yellow-800/30 p-5 shadow-sm ring-1 ring-yellow-200/50 dark:ring-yellow-700/50 transition-all duration-300 hover:shadow-lg hover:shadow-yellow-500/10 dark:hover:shadow-yellow-500/20 hover:ring-yellow-300/60 dark:hover:ring-yellow-600/60"
              >
                <!-- Background animation -->
                <div
                  class="absolute inset-0 bg-gradient-to-r from-yellow-500/5 via-transparent to-yellow-500/5 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                ></div>

                <div class="relative flex items-start gap-4">
                  <!-- Icon container -->
                  <div class="relative flex-shrink-0">
                    <div
                      class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-yellow-500 to-amber-600 shadow-lg transition-all duration-300 group-hover:scale-105"
                    >
                      <ClockIcon
                        class="h-6 w-6 text-white transition-transform duration-300 group-hover:scale-105"
                      />
                    </div>
                  </div>

                  <!-- Content -->
                  <div class="flex-1 min-w-0">
                    <h3
                      class="text-base font-bold text-yellow-900 dark:text-yellow-100 transition-colors duration-300 group-hover:text-yellow-800 dark:group-hover:text-yellow-200"
                    >
                      <template v-if="isWaitingDirectorIntervention">
                        Aguardando Director
                      </template>
                      <template v-else-if="hasDirectorAssumedCase">
                        Caso Assumido
                      </template>
                    </h3>
                    <p
                      class="text-sm text-yellow-700 dark:text-yellow-400 mt-1 leading-relaxed transition-colors duration-300 group-hover:text-yellow-600 dark:group-hover:text-yellow-300"
                    >
                      <template v-if="isWaitingDirectorIntervention">
                        As ações estão suspensas até resposta do Director sobre este caso
                      </template>
                      <template v-else-if="hasDirectorAssumedCase">
                        O Director assumiu total responsabilidade por este caso
                      </template>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Status -->
          <div
            class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
          >
            <h2
              class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2"
            >
              <span
                class="flex items-center justify-center h-5 w-5 rounded-full bg-green-100 dark:bg-green-900/20 text-xs flex-shrink-0"
              >
                <CheckCircleIcon class="h-4 w-4 text-green-600" />
              </span>
              Estado Atual
            </h2>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Status</span>
                <StatusBadge
                  :status="submission.status"
                  :label="getStatusLabel(submission.status)"
                  size="sm"
                />
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Prioridade</span>
                <span :class="getPriorityBadgeClass(complaint.priority)">
                  {{ getPriorityLabel(complaint.priority) }}
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Tipo</span>
                <span :class="getTypeBadgeClass(complaint.type)">
                  {{ getTypeLabel(complaint.type) }}
                </span>
              </div>
              <div v-if="isEscalatedToDirector" class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Escalado</span>
                <span
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400"
                >
                  <PaperAirplaneIcon class="h-3 w-3 mr-1" />
                  Director
                </span>
              </div>
            </div>
          </div>

          <!-- Utente Info -->
          <div
            class="bg-white dark:bg-dark-secondary rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700"
          >
            <h2
              class="text-lg font-bold text-gray-900 dark:text-dark-text-primary mb-4 flex items-center gap-2"
            >
              <span
                class="flex items-center justify-center h-5 w-5 rounded-full bg-blue-100 dark:bg-blue-900/20 text-xs flex-shrink-0"
              >
                <UserIcon class="h-4 w-4 text-blue-600" />
              </span>
              Informações do Utente
            </h2>
            <div class="space-y-3">
              <div>
                <p
                  class="text-xs text-gray-600 dark:text-gray-400 uppercase font-medium mb-1"
                >
                  Nome
                </p>
                <p
                  class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary"
                >
                  {{ complaint.user?.name || complaint.contact_name || "Anónimo" }}
                </p>
              </div>
              <div>
                <p
                  class="text-xs text-gray-600 dark:text-gray-400 uppercase font-medium mb-1"
                >
                  Email
                </p>
                <p
                  class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary"
                >
                  {{ complaint.user?.email || complaint.contact_email || "N/A" }}
                </p>
              </div>
              <div>
                <p
                  class="text-xs text-gray-600 dark:text-gray-400 uppercase font-medium mb-1"
                >
                  Telefone
                </p>
                <a
                  v-if="complaint.contact_phone"
                  :href="`tel:${complaint.contact_phone}`"
                  class="text-sm font-semibold text-brand hover:text-orange-700 transition-colors"
                >
                  {{ complaint.contact_phone }}
                </a>
                <p
                  v-else
                  class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary"
                >
                  N/A
                </p>
              </div>
              <div>
                <p
                  class="text-xs text-gray-600 dark:text-gray-400 uppercase font-medium mb-1"
                >
                  Localização
                </p>
                <p
                  class="text-sm font-semibold text-gray-900 dark:text-dark-text-primary"
                >
                  {{ complaint.province || "N/A" }}, {{ complaint.district || "N/A" }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- MODAL DE COMENTÁRIOS (ESTILO WHATSAPP/FACEBOOK) -->
      <CommentModal
        v-if="showCommentModal"
        ref="commentModalRef"
        :complaint="complaint"
        :comments="localComments"
        :is-open="showCommentModal"
        @close="handleCloseCommentModal"
        @submit="handleCommentSubmit"
        @mark-read="handleMarkCommentsRead"
        @comment-added="handleCommentAdded"
      />

      <!-- Modals -->
      <PriorityModal
        v-if="showPriorityModal"
        :complaint="complaint"
        @close="closeModal('priority')"
        @update="updatePriority"
      />

      <ReassignModal
        v-if="showReassignModal"
        :complaint="complaint"
        :technicians="technicians"
        @close="closeModal('reassign')"
        @update="reassignTechnician"
      />

      <SendToDirectorModal
        v-if="showSendToDirectorModal"
        :complaint="complaint"
        @close="closeModal('sendToDirector')"
        @submit="sendToDirector"
      />

      <ApprovalDirectorModal
        v-if="showValidationModal"
        :is-open="showValidationModal"
        :submission="complaint"
        :edit-data="editValidationData"
        @close="closeValidationModal"
        @submit="handleValidationSubmit"
      />

      <RejectSubmissionsModal
        v-if="showRejectModal"
        :is-open="showRejectModal"
        :complaint="complaint"
        :loading="loading.reject"
        @close="closeModal('reject')"
        @submit="rejectSubmission"
      />

      <ValidateSubmissionModal
        v-if="showValidateSubmissionModal"
        :submission="complaint"
        :loading="loading.submitValidation"
        @close="closeModal('validateSubmission')"
        @submit="validateSubmission"
      />

      <ReopenSubmissionModal
        v-if="showReopenModal"
        :is-open="showReopenModal"
        :complaint="complaint"
        :loading="loading.reopen"
        @close="closeModal('reopen')"
        @submit="reopenSubmission"
      />

      <ToastNotification v-if="toast.show" :toast="toast" @close="toast.show = false" />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/UnifiedLayout.vue";
import CommentModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/CommentModal.vue";
import ValidateSubmissionModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/ValidateSubmissionModal.vue";
import RejectSubmissionsModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/RejectSubmissionsModal.vue";
import ApprovalDirectorModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/ApprovalDirectorModal.vue";
import GrievanceTimeline from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceTimeline.vue";
import GrievanceActions from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceActions.vue";
import GrievanceAttachments from "@/Components/GestorReclamacoes/GrievanceDetailComponents/GrievanceAttachments.vue";
import PriorityModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/PriorityModal.vue";
import ReassignModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/ReassignModal.vue";
import SendToDirectorModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/SendToDirectorModal.vue";
import ReopenSubmissionModal from "@/Components/GestorReclamacoes/GrievanceDetailComponents/ReopenSubmissionModal.vue";
import ToastNotification from "@/Components/Director/ToastNotification.vue";
import { useGrievanceDetail } from "@/Components/GestorReclamacoes/Composables/useGrievanceDetail";
import {
  ArrowLeftIcon,
  ArrowDownTrayIcon,
  CheckBadgeIcon,
  PaperAirplaneIcon,
  UserCircleIcon,
  CalendarIcon,
  ChatBubbleLeftIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  PaperClipIcon,
  PencilSquareIcon,
  ClockIcon,
  UserIcon,
  CheckCircleIcon,
  BoltIcon,
} from "@heroicons/vue/24/outline";
import { StarIcon } from "@heroicons/vue/24/solid";

import { useAuth } from "@/Composables/useAuth";

// Inicializar o composable de autenticação
const auth = useAuth();

// Agora use as propriedades computadas do composable
const user = auth.user;
const role = auth.role;
const isDirector = auth.isDirector;
const isManager = auth.isManager;
const isTechnician = auth.isTechnician;
const checkRole = auth.checkRole;

const props = defineProps({
  complaint: {
    type: Object,
    required: true,
  },
  submission: {
    type: Object,
    required: true,
  },
  comments: {
    type: Array,
    default: () => [],
  },
  technicians: {
    type: Array,
    default: () => [],
  },
  managers: {
    type: Array,
    default: () => [],
  },
  projects: {
    type: Array,
    default: () => [],
  },
  timeline_data: {
    type: Array,
    default: () => [],
  },
  user: {
    type: Object,
    default: () => ({}),
  },
  user_role: {
    type: String,
    default: "",
  },
});

// Usar o composable
const {
  // Estados reativos
  complaint: complaintRef,
  technicians: techniciansRef,
  showPriorityModal,
  showReassignModal,
  showCommentModal,
  showReopenModal,
  showSendToDirectorModal,
  showApprovalDirectorModal,
  showMarkCompleteModal,
  showValidateSubmissionModal,
  showRejectModal,
  loading,
  toast,

  // Computed properties de estado
  isPendingApproval,
  isRejected,
  isResolved,
  isApproved,
  isEscalatedToDirector,
  isWaitingDirectorIntervention,
  hasDirectorValidation,
  directorValidationStatus,
  isDirectorRejected,
  isDirectorApproved,
  isDirectorNeedsRevision,

  // Computed properties para botões
  canUpdatePriority,
  canReassignTechnician,
  canSendToDirector,
  canMarkComplete,
  isCaseAssumedByDirector,
  isCaseReturnedToManager,
  canComment,
  canRejectSubmission,
  markCompleteButtonText,
  showSendToDirectorButton,
  escalationStatusText,
  shouldShowActions,
  shouldManagerSeeActions,
  shouldDirectorSeeActions,
  hasDirectorAssumedCase,
  hasDirectorCommentedAndReturned,
  directorResponseStatusText,

  // Métodos auxiliares
  formatDate,
  priorityLabel,
  showToast,

  // Métodos de UI
  openPriorityModal,
  openReassignModal,
  openCommentModal,
  openSendToDirectorModal,
  openApprovalDirectorModal,
  processDirectorResponse,
  openMarkCompleteModal,
  closeModal,
  handleOpenModal,

  // Ações
  updatePriority,
  reassignTechnician,
  sendToDirector,
  markComplete,
  validateSubmission,
  submitDirectorValidation,
  sendComment,
  fetchComments,
  refreshComplaintData,
  rejectSubmission,
  updateDirectorValidation,
} = useGrievanceDetail(props);

// ========== COMPUTED PROPERTIES LOCAIS ==========

const editValidationData = ref(null);
const showValidationModal = ref(false);
const filterComments = ref("all");
const commentModalRef = ref(null);
const localComments = ref([]);

const getValidatorName = (validation) => {
  if (!validation) return "Director";

  if (isDirector.value && validation.validated_by === props.user?.id) {
    return "Eu (Director)";
  }

  return validation.validated_by_name || validation.validated_by?.name || "Director";
};

// Link de retorno baseado no role
const backUrl = computed(() => {
  if (isDirector.value) {
    return "/director/complaints-overview";
  } else if (isManager.value) {
    return "/dashboard";
  }
  return "/dashboard";
});

// Dados da timeline
const timelineData = computed(() => {
  if (!complaintRef.value) return [];

  if (props.timeline_data?.length > 0) {
    return props.timeline_data;
  }

  if (complaintRef.value?.updates?.length > 0) {
    return complaintRef.value.updates;
  }

  if (complaintRef.value?.activities?.length > 0) {
    return complaintRef.value.activities;
  }

  return [];
});

const goBack = () => {
  router.visit(window.history.state?.back || "/dashboard", {
    preserveScroll: true,
    preserveState: true,
  });
};

// Detalhes do escalamento
const escalationDetails = computed(() => {
  if (!isEscalatedToDirector.value || !complaintRef.value) {
    return {};
  }

  if (complaintRef.value.escalation_details) {
    return complaintRef.value.escalation_details;
  }

  return {
    escalated_by: complaintRef.value.escalated_by,
    escalated_at: complaintRef.value.escalated_at,
    escalation_reason: complaintRef.value.escalation_reason,
    escalation_comment: complaintRef.value.updates?.find(
      (u) => u.action_type === "escalated_to_director"
    )?.comment,
    escalation_metadata: complaintRef.value.updates?.find(
      (u) => u.action_type === "escalated_to_director"
    )?.metadata,
  };
});

// Título da página
const pageTitle = computed(() => {
  return `Submissão #${complaintRef.value?.reference_number || "Detalhes"}`;
});

// ========== FUNÇÕES AUXILIARES DE UI ==========

// Funções de formatação de badges
const getTypeBadgeClass = (type) => {
  const classes = {
    submission:
      "bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400",
    complaint: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
    suggestion: "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400",
  };
  return `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
    classes[type] || "bg-gray-100 text-gray-800"
  }`;
};

const getPriorityBadgeClass = (priority) => {
  const classes = {
    low: "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
    medium: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
    high: "bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400",
    critical: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
  };
  return `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
    classes[priority] || "bg-gray-100 text-gray-800"
  }`;
};

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
    in_progress: "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400",
    resolved: "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
    closed: "bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400",
    submitted: "bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400",
    under_review:
      "bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400",
    assigned: "bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-400",
    escalated: "bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400",
    rejected: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
    pending_approval:
      "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
    approved: "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
  };
  return `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
    classes[status] || "bg-gray-100 text-gray-800"
  }`;
};

const getValidationStatusBadgeClass = (status) => {
  const classes = {
    approved: "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
    rejected: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
    needs_revision:
      "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
    commented: "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400",
  };
  return `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
    classes[status] || "bg-gray-100 text-gray-800"
  }`;
};

const getValidationCardClass = (status) => {
  const classes = {
    approved: "border-green-200 dark:border-green-700 bg-green-50 dark:bg-green-900/20",
    rejected: "border-red-200 dark:border-red-700 bg-red-50 dark:bg-red-900/20",
    needs_revision:
      "border-yellow-200 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20",
    commented: "border-blue-200 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/20",
  };
  return (
    classes[status] ||
    "border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20"
  );
};

const getManagerComment = () => {
  if (!isEscalatedToDirector.value || !complaintRef.value) {
    return null;
  }

  // 1. Primeiro tentar do escalation_details
  if (escalationDetails.value?.escalation_comment) {
    return escalationDetails.value.escalation_comment;
  }

  // 2. Tentar do comment no escalation_details
  if (escalationDetails.value?.comment) {
    return escalationDetails.value.comment;
  }

  // 3. Buscar no histórico de updates (updates do tipo "escalated_to_director")
  const escalationUpdate = complaintRef.value.updates?.find(
    (u) => u.action_type === "escalated_to_director" && u.comment
  );

  if (escalationUpdate?.comment) {
    return escalationUpdate.comment;
  }

  // 4. Buscar no complaint.director_validation (se houver)
  if (complaintRef.value.director_validation?.comment) {
    return complaintRef.value.director_validation.comment;
  }

  // 5. Último recurso: mostrar mensagem padrão
  return "Comentário não especificado pelo gestor.";
};

// Funções de label
const getTypeLabel = (type) => {
  if (!type) return "Tipo não definido";
  const labels = {
    grievance: "Queixa",
    complaint: "Reclamação",
    suggestion: "Sugestão",
    submission: "Submissão",
  };
  return labels[type] || type;
};

const getPriorityLabel = (priority) => {
  if (!priority) return "Prioridade não definida";
  const labels = {
    low: "Baixa",
    medium: "Média",
    high: "Alta",
    critical: "Crítica",
  };
  return labels[priority] || priority;
};

const getStatusLabel = (status) => {
  if (!status) return "Estado não definido";
  const labels = {
    pending: "Pendente",
    in_progress: "Em Análise",
    resolved: "Resolvido",
    closed: "Fechado",
    submitted: "Submetida",
    under_review: "Em Revisão",
    assigned: "Atribuída",
    escalated: "Escalada para Director",
    rejected: "Rejeitada",
    pending_approval: "Pendente de Aprovação",
    approved: "Aprovado",
  };
  return labels[status] || status;
};

const getValidationLabel = (status) => {
  if (!status) return "Validação";
  const labels = {
    approved: "Aprovado pelo Director",
    rejected: "Rejeitado pelo Director",
    needs_revision: "Revisão Solicitada pelo Director",
    commented: "Comentado pelo Director",
  };
  return labels[status] || "Validação";
};

// Formatação de data/hora
const formatDateTime = (dateString) => {
  if (!dateString) return "N/A";
  try {
    const date = new Date(dateString);
    return date.toLocaleString("pt-PT", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch (error) {
    return "Data/hora inválida";
  }
};

const formatShortDate = (dateString) => {
  if (!dateString) return "N/A";
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString("pt-PT", {
      day: "2-digit",
      month: "2-digit",
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch (error) {
    return "Data inválida";
  }
};

const formatFileSize = (bytes) => {
  if (!bytes) return "0 Bytes";
  if (bytes === 0) return "0 Bytes";
  const k = 1024;
  const sizes = ["Bytes", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};

const getCommentTypeLabel = (comment) => {
  if (comment.type === "director_validation") return "Validação Director";
  if (comment.user?.role === "Director") return "Director";
  if (comment.type === "public") return "Público";
  if (comment.type === "director_only") return "Apenas Director";
  return "Interno";
};

// ========== FUNÇÕES AUXILIARES DA TIMELINE ==========

const getTimelineIcon = (update) => {
  // Determinar ícone baseado no tipo de ação
  const actionType = update.action_type || update.type || "";

  if (actionType.includes("escalated") || actionType.includes("director")) {
    return PaperAirplaneIcon;
  }
  if (actionType.includes("assigned") || actionType.includes("technician")) {
    return UserCircleIcon;
  }
  if (
    actionType.includes("status") ||
    actionType.includes("resolved") ||
    actionType.includes("closed")
  ) {
    return CheckCircleIcon;
  }
  if (actionType.includes("comment") || actionType.includes("message")) {
    return ChatBubbleLeftIcon;
  }
  if (actionType.includes("attachment") || actionType.includes("file")) {
    return PaperClipIcon;
  }
  if (actionType.includes("priority")) {
    return ExclamationTriangleIcon;
  }

  // Fallback baseado no role do usuário
  if (update.user?.role === "Director") {
    return CheckBadgeIcon;
  }
  if (update.user?.role === "Manager") {
    return UserIcon;
  }

  return ClockIcon;
};

const getTimelineIconBg = (update) => {
  // Determinar cor do fundo baseada no tipo de ação
  const actionType = update.action_type || update.type || "";

  if (actionType.includes("escalated") || actionType.includes("director")) {
    return "bg-purple-500";
  }
  if (actionType.includes("assigned") || actionType.includes("technician")) {
    return "bg-blue-500";
  }
  if (
    actionType.includes("status") ||
    actionType.includes("resolved") ||
    actionType.includes("closed")
  ) {
    return "bg-green-500";
  }
  if (actionType.includes("comment") || actionType.includes("message")) {
    return "bg-indigo-500";
  }
  if (actionType.includes("attachment") || actionType.includes("file")) {
    return "bg-orange-500";
  }
  if (actionType.includes("priority")) {
    return "bg-red-500";
  }

  // Fallback baseado no role do usuário
  if (update.user?.role === "Director") {
    return "bg-emerald-500";
  }
  if (update.user?.role === "Manager") {
    return "bg-cyan-500";
  }

  return "bg-gray-500";
};

const getTimelineBadgeText = (update) => {
  // Determinar texto do badge baseado no tipo de ação
  const actionType = update.action_type || update.type || "";

  if (actionType.includes("escalated_to_director")) {
    return "Escalado";
  }
  if (actionType.includes("assigned_to_technician")) {
    return "Atribuído";
  }
  if (actionType.includes("status_changed")) {
    return "Status";
  }
  if (actionType.includes("comment_added")) {
    return "Comentário";
  }
  if (actionType.includes("attachment_added")) {
    return "Anexo";
  }
  if (actionType.includes("priority_changed")) {
    return "Prioridade";
  }
  if (actionType.includes("director_validation")) {
    return "Director";
  }

  // Fallback baseado no role do usuário
  if (update.user?.role === "Director") {
    return "Director";
  }
  if (update.user?.role === "Manager") {
    return "Gestor";
  }
  if (update.user?.role === "Technician") {
    return "Técnico";
  }

  return "Sistema";
};

const getTimelineBadgeClass = (update) => {
  // Determinar classe do badge baseada no tipo de ação
  const actionType = update.action_type || update.type || "";

  if (actionType.includes("escalated") || actionType.includes("director")) {
    return "bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400";
  }
  if (actionType.includes("assigned") || actionType.includes("technician")) {
    return "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400";
  }
  if (
    actionType.includes("status") ||
    actionType.includes("resolved") ||
    actionType.includes("closed")
  ) {
    return "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400";
  }
  if (actionType.includes("comment") || actionType.includes("message")) {
    return "bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400";
  }
  if (actionType.includes("attachment") || actionType.includes("file")) {
    return "bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400";
  }
  if (actionType.includes("priority")) {
    return "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400";
  }

  // Fallback baseado no role do usuário
  if (update.user?.role === "Director") {
    return "bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400";
  }
  if (update.user?.role === "Manager") {
    return "bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-400";
  }

  return "bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400";
};

// ========== FUNÇÕES DE VALIDAÇÃO ==========

// ADICIONAR: Função para abrir modal de validação
const openValidationModal = () => {
  if (!isDirector.value) {
    showToast("Apenas o Director pode validar solicitações", "warning");
    return;
  }

  if (!isEscalatedToDirector.value || hasDirectorValidation.value) {
    showToast("Não é possível validar esta solicitação", "warning");
    return;
  }

  showValidationModal.value = true;
  editValidationData.value = null;
};

const openValidationModalForEdit = () => {
  if (!isDirector.value) {
    showToast("Apenas o Director pode editar a resposta", "warning");
    return;
  }

  if (!hasDirectorValidation.value) {
    showToast("Não há resposta para editar", "warning");
    return;
  }

  // DEBUG DETALHADO
  console.log("=== DEBUG DETALHADO ===");
  console.log("1. complaintRef.value:", JSON.parse(JSON.stringify(complaintRef.value)));
  console.log("2. hasDirectorValidation:", hasDirectorValidation.value);
  console.log("3. director_validation:", complaintRef.value.director_validation);
  console.log("4. metadata:", complaintRef.value.metadata);

  // Verificar diferentes possibilidades de onde pode estar a validação
  let validationData = null;

  // Possibilidade 1: Direto no complaint.director_validation
  if (
    complaintRef.value.director_validation &&
    typeof complaintRef.value.director_validation === "object"
  ) {
    validationData = complaintRef.value.director_validation;
    console.log("Encontrado em director_validation");
  }
  // Possibilidade 2: No metadata
  else if (
    complaintRef.value.metadata &&
    complaintRef.value.metadata.director_validation
  ) {
    validationData = complaintRef.value.metadata.director_validation;
    console.log("Encontrado em metadata.director_validation");
  }

  if (!validationData) {
    console.error("Não foi possível encontrar dados de validação");
    showToast("Erro: Dados da validação não encontrados", "error");
    return;
  }

  console.log("Dados da validação encontrados:", validationData);

  // Converter 'needs_revision' do backend para 'commented' no frontend
  const frontendStatus =
    validationData.status === "needs_revision" ? "commented" : validationData.status;

  editValidationData.value = {
    id: validationData.id || complaintRef.value.id,
    status: frontendStatus,
    comment: validationData.comment || validationData.comments || "",
    validated_at: validationData.validated_at || validationData.created_at,
    validated_by: validationData.validated_by,
    notify_manager:
      validationData.notify_manager !== undefined ? validationData.notify_manager : true,
    notify_technician:
      validationData.notify_technician !== undefined
        ? validationData.notify_technician
        : true,
    notify_user:
      validationData.notify_user !== undefined ? validationData.notify_user : false,
  };

  // Abrir modal
  showValidationModal.value = true;
  console.log("Abrindo modal de edição com dados preparados:", editValidationData.value);
};

// ADICIONAR: Função para fechar modal de validação
const closeValidationModal = () => {
  showValidationModal.value = false;
  editValidationData.value = null;
};

const handleValidationSubmit = async (formData) => {
  console.log("handleValidationSubmit chamado com:", formData);

  // Verificar se é uma nova validação ou edição
  if (!editValidationData.value) {
    // Nova validação - usar submitDirectorValidation
    try {
      await submitDirectorValidation({
        status: formData.status, // 'approved' ou 'commented'
        comment: formData.comment,
      });

      // Fechar modal
      showValidationModal.value = false;
    } catch (error) {
      console.error("Erro na validação:", error);
      showToast("Erro ao enviar resposta: " + error.message, "error");
    }
  } else {
    // Edição de validação existente
    console.log("Modo de edição - ID:", editValidationData.value.id);

    try {
      // Enviar 'commented' direto para o backend
      const statusToSend = formData.status; // 'approved' ou 'commented'

      await updateDirectorValidation({
        status: statusToSend,
        comment: formData.comment,
        validationId: editValidationData.value.id,
      });

      // Limpar dados de edição
      editValidationData.value = null;
      showValidationModal.value = false;
    } catch (error) {
      console.error("Erro na edição:", error);
      if (!toast.show) {
        showToast("Erro ao atualizar resposta: " + error.message, "error");
      }
    }
  }
};

const confirmMarkComplete = async () => {
  await markComplete();
};

const rejectCompletion = async () => {
  showToast("Função de rejeição de conclusão não implementada nesta versão", "warning");
};

const markAsViewedByDirector = async () => {
  showToast("Função de marcar como visualizado não implementada nesta versão", "warning");
};

const handleDirectorResponse = async (responseData) => {
  await submitDirectorValidation({
    ...responseData,
    status: responseData.status === "commented" ? "needs_revision" : responseData.status,
    assumed_by_director: responseData.status === "approved",
  });
};

const debugInfo = computed(() => {
  return {
    userRole: role.value,
    isDirector: isDirector.value,
    isManager: isManager.value,
    isEscalated: isEscalatedToDirector.value,
    hasDirectorValidation: hasDirectorValidation.value,
    directorValidationStatus: directorValidationStatus.value,
    hasDirectorAssumedCase: hasDirectorAssumedCase.value,
    hasDirectorCommentedAndReturned: hasDirectorCommentedAndReturned.value,
    complaintStatus: complaintRef.value?.status,
    directorValidation: complaintRef.value?.director_validation,
    metadata: complaintRef.value?.metadata,
  };
});

// Inicialização
onMounted(() => {
  console.log("=== SHOW COMPONENT MOUNTED ===");
  console.log("isCaseAssumedByDirector:", isCaseAssumedByDirector?.value);
  console.log("isCaseReturnedToManager:", isCaseReturnedToManager?.value);
  console.log("shouldShowActions:", shouldShowActions?.value);
  console.log("canComment:", canComment?.value);
  console.log("User role:", role?.value);

  loadComments();

  // Verificar os updates para ver se há informação do director
  console.log("Updates:", complaintRef.value?.updates);

  // Verificar se há algum update relacionado ao director
  const directorUpdates = timelineData.value?.filter(
    (update) =>
      update.user?.role === "director" ||
      update.action_type?.includes("director") ||
      update.action_type?.includes("validation")
  );
  console.log("Director Updates Count:", directorUpdates?.length);

  // Mostrar detalhes de cada director update
  if (directorUpdates && directorUpdates.length > 0) {
    directorUpdates.forEach((update, index) => {
      console.log(`Director Update ${index + 1}:`, {
        id: update.id,
        action_type: update.action_type,
        status: update.status,
        description: update.description,
        comment: update.comment,
        metadata: update.metadata,
        user: update.user,
        created_at: update.created_at,
      });
    });
  }

  // Verificar o metadata do complaint
  console.log("Complaint Metadata:", complaintRef.value?.metadata);

  // Verificar director_validation field
  console.log("Director Validation Field:", complaintRef.value?.director_validation);
  console.log(
    "Type of director_validation:",
    typeof complaintRef.value?.director_validation
  );
});

const loadComments = async () => {
  try {
    const comments = await fetchComments();
    localComments.value = comments;
    console.log("Comentários carregados:", comments.length);
  } catch (error) {
    console.error("Erro ao carregar comentários:", error);
  }
};

// Watch para quando o modal abrir
watch(
  () => [isCaseAssumedByDirector?.value, isCaseReturnedToManager?.value],
  ([assumed, returned]) => {
    console.log("Estado atualizado:");
    console.log("isCaseAssumedByDirector:", assumed);
    console.log("isCaseReturnedToManager:", returned);
  }
);

// Função para enviar comentário
const handleCommentSubmit = async (commentData) => {
  console.log("=== HANDLE COMMENT SUBMIT ===");
  console.log("Comment data:", commentData);

  try {
    // Enviar comentário usando a função do composable
    const newComment = await sendComment(commentData);
    console.log("New comment from server:", newComment);

    if (newComment) {
      // Adicionar comentário localmente
      const formattedComment = {
        id: newComment.id,
        content: newComment.content,
        comment: newComment.comment,
        type: newComment.type,
        action_type: newComment.action_type,
        created_at: newComment.created_at,
        user: newComment.user,
        attachments: newComment.attachments,
        metadata: newComment.metadata,
      };

      // Adicionar ao array local
      localComments.value.push(formattedComment);

      // Se o modal estiver aberto, atualizar via ref
      if (commentModalRef.value && commentModalRef.value.addLocalComment) {
        commentModalRef.value.addLocalComment(formattedComment);
      }

      // Atualizar contador de comentários
      if (complaint.value) {
        complaint.value.comments_count = (complaint.value.comments_count || 0) + 1;
      }

      console.log("Comentário adicionado localmente:", formattedComment);

      // Não fechar o modal imediatamente - deixar o usuário ver o comentário
      // O modal será fechado quando o usuário clicar no X
    }
  } catch (error) {
    console.error("Erro ao enviar comentário:", error);
    // O erro já foi tratado no sendComment
  }
};

// Handler para quando um comentário é adicionado no modal
const handleCommentAdded = (comment) => {
  console.log("Comentário adicionado via evento:", comment);
  // Já tratamos no handleCommentSubmit, mas mantemos para consistência
};

// Handler para fechar o modal de comentários
// Em Show.vue, modifique a função
const handleCloseCommentModal = () => {
  // Apenas fechar o modal, o composable já cuida do resto
  closeModal("comment");
};

const complaintAttachments = computed(() => {
  if (!complaint.value) return [];

  // Retornar apenas anexos diretos da reclamação, não de comentários
  return (
    complaint.value.attachments?.filter((attach) => {
      // Excluir anexos de comentários
      return !attach.comment_id && !attach.comment;
    }) || []
  );
});

// Expor a função para recarregar comentários (pode ser chamada de outros lugares)
defineExpose({
  loadComments,
});
</script>

<style scoped>
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.6s ease-out forwards;
  opacity: 0;
}

/* Custom scrollbar for timeline */
.max-h-96::-webkit-scrollbar {
  width: 6px;
}

.max-h-96::-webkit-scrollbar-track {
  background: transparent;
}

.max-h-96::-webkit-scrollbar-thumb {
  background: rgb(156 163 175);
  border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb:hover {
  background: rgb(107 114 128);
}

.dark .max-h-96::-webkit-scrollbar-thumb {
  background: rgb(75 85 99);
}

.dark .max-h-96::-webkit-scrollbar-thumb:hover {
  background: rgb(107 114 128);
}

/* Timeline line animation */
@keyframes line-draw {
  from {
    height: 0;
  }
  to {
    height: 100%;
  }
}

.timeline-line {
  animation: line-draw 1.5s ease-out forwards;
}

/* Enhanced hover effects */
.timeline-item:hover .timeline-dot {
  transform: scale(1.1);
  box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
}

.timeline-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.timeline-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Pulse animation for timeline dots */
@keyframes pulse-ring {
  0% {
    transform: scale(0.33);
  }
  40%,
  50% {
    opacity: 1;
  }
  100% {
    opacity: 0;
    transform: scale(1.5);
  }
}

.timeline-pulse {
  animation: pulse-ring 2s cubic-bezier(0.455, 0.03, 0.515, 0.955) infinite;
}
</style>
