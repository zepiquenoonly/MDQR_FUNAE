<template>
  <div
    class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6"
  >
    <!-- Header compacto - APENAS VISÍVEL NA VISUALIZAÇÃO RESUMIDA -->
    <div
      v-if="!showAllComplaints"
      class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4 sm:mb-6"
    >
      <h3 class="text-lg font-semibold text-gray-800 dark:text-dark-text-primary">
        {{ headerTitle }}
      </h3>
      <div class="flex gap-2 self-end sm:self-auto">
        <!-- Botão Ver Todos com notificações dinâmicas -->
        <button
          @click="toggleAllComplaints"
          class="text-brand hover:text-orange-600 text-sm font-medium flex items-center gap-1.5 group relative"
          :disabled="loading"
        >
          <span>Ver Todos</span>

          <!-- Badge de notificações dinâmico -->
          <span
            v-if="unseenCount > 0 && !showAllComplaints"
            class="inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-medium leading-none rounded-full transform group-hover:scale-110 transition-all duration-200 animate-pulse"
            :class="[
              isDirector && unseenManagerRequests > 0
                ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            ]"
          >
            {{ unseenCount > 99 ? "99+" : unseenCount }}

            <!-- Tooltip explicativo -->
            <span
              class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-10"
            >
              <span v-if="isDirector && unseenManagerRequests > 0">
                {{ unseenManagerRequests }} nova{{
                  unseenManagerRequests !== 1 ? "s" : ""
                }}
                solicitação{{ unseenManagerRequests !== 1 ? "ões" : "" }} do gestor
              </span>
              <span v-else-if="isManager && unseenDirectorInterventions > 0">
                {{ unseenDirectorInterventions }} nova{{
                  unseenDirectorInterventions !== 1 ? "s" : ""
                }}
                intervenção{{ unseenDirectorInterventions !== 1 ? "ões" : "" }} do
                director
              </span>
              <span v-else>
                {{ unseenCount }} nova{{ unseenCount !== 1 ? "s" : "" }} submissão{{
                  unseenCount !== 1 ? "es" : ""
                }}
              </span>
            </span>
          </span>

          <!-- Ícone dinâmico -->
          <svg
            :class="[
              'w-4 h-4 transition-transform duration-300',
              showAllComplaints ? 'rotate-180' : '',
            ]"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 9l-7 7-7-7"
            />
          </svg>
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div
        class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand mx-auto"
      ></div>
      <p class="text-gray-500 dark:text-gray-400 mt-2">Carregando dados...</p>
    </div>

    <!-- Conteúdo principal -->
    <div v-else>
      <!-- Visualização Resumida - TABELA -->
      <div v-if="!showAllComplaints">
        <!-- Container com rolagem interna para tabela -->
        <div class="table-scroll-container">
          <div class="overflow-x-auto -mx-4 sm:mx-0">
            <div class="min-w-full inline-block align-middle">
              <table
                class="min-w-full divide-y divide-gray-300 dark:divide-gray-600 text-xs sm:text-sm"
              >
                <thead class="table-header-sticky bg-gray-50 dark:bg-dark-accent">
                  <tr>
                    <!-- Coluna especial para Solicitações do Gestor -->
                    <th
                      v-if="isDirector && activeTab === 'manager_requests'"
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Gestor
                    </th>

                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      ID
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-32"
                    >
                      Título
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Tipo
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Prioridade
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Estado
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Data
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Ações
                    </th>
                  </tr>
                </thead>
                <tbody
                  class="bg-white dark:bg-dark-secondary divide-y divide-gray-200 dark:divide-gray-700"
                >
                  <tr
                    v-for="item in recentSubmissions.slice(0, 4)"
                    :key="item.id"
                    :class="[
                      'hover:bg-gray-50 dark:hover:bg-dark-accent transition-colors',
                      item.has_director_intervention ? 'has-director-response' : '',
                      item.manager_request || item.is_escalated_to_director
                        ? 'has-manager-request'
                        : '',
                      isNewSubmission(item) ? 'new-submission' : '',
                    ]"
                  >
                    <!-- Coluna do Gestor que escalou (apenas para tab manager_requests do Director) -->
                    <td
                      v-if="isDirector && activeTab === 'manager_requests'"
                      class="px-3 py-2 whitespace-nowrap"
                    >
                      <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-1">
                          <UserIcon class="w-3 h-3 text-blue-500 flex-shrink-0" />
                          <span
                            class="text-xs font-medium text-blue-700 dark:text-blue-300 truncate max-w-24"
                          >
                            {{ getEscalatedBy(item) }}
                          </span>
                        </div>
                        <span
                          class="text-xs text-gray-500"
                          :title="getEscalationReason(item)"
                        >
                          {{ truncateText(getEscalationReason(item), 30) }}
                        </span>
                      </div>
                    </td>

                    <!-- ID com indicador de novo -->
                    <td
                      class="px-3 py-2 whitespace-nowrap font-medium relative"
                      :class="[
                        isNewSubmission(item)
                          ? 'text-green-700 dark:text-green-300'
                          : 'text-gray-900 dark:text-dark-text-primary',
                      ]"
                    >
                      <div class="flex items-center gap-1">
                        {{ item.reference_number }}
                        <!-- Indicador de nova solicitação do gestor -->
                        <span
                          v-if="isDirector && isNewManagerRequest(item)"
                          class="inline-flex items-center justify-center w-3 h-3 text-[8px] font-bold text-white bg-blue-500 rounded-full animate-pulse"
                          title="Nova solicitação do gestor"
                        >
                          !
                        </span>
                        <!-- Indicador de nova intervenção do director -->
                        <span
                          v-if="isManager && isNewDirectorIntervention(item)"
                          class="inline-flex items-center justify-center w-3 h-3 text-[8px] font-bold text-white bg-purple-500 rounded-full animate-pulse"
                          title="Nova intervenção do director"
                        >
                          !
                        </span>
                        <!-- Indicador de nova submissão -->
                        <span
                          v-if="isNewSubmission(item)"
                          class="inline-flex items-center justify-center w-3 h-3 text-[8px] font-bold text-white bg-green-500 rounded-full animate-pulse"
                          title="Nova submissão"
                        >
                          N
                        </span>
                      </div>
                    </td>

                    <!-- Título -->
                    <td
                      class="px-3 py-2 text-gray-900 dark:text-dark-text-primary max-w-32 truncate"
                    >
                      {{ item.title || item.description }}
                    </td>

                    <!-- Tipo -->
                    <td class="px-3 py-2 whitespace-nowrap">
                      <span :class="getTypeBadgeClass(item.type)">
                        {{ getTypeLabel(item.type) }}
                      </span>
                    </td>

                    <!-- Prioridade -->
                    <td class="px-3 py-2 whitespace-nowrap">
                      <span :class="getPriorityBadgeClass(item.priority)">
                        {{ getPriorityLabel(item.priority) }}
                      </span>
                    </td>

                    <!-- Estado -->
                    <td class="px-3 py-2 whitespace-nowrap">
                      <div class="flex flex-col gap-1">
                        <span :class="getStatusBadgeClass(item.status)">
                          {{ getStatusLabel(item.status) }}
                        </span>
                      </div>
                    </td>

                    <!-- Data -->
                    <td
                      class="px-3 py-2 whitespace-nowrap text-gray-500 dark:text-gray-400"
                    >
                      {{ formatDate(item.created_at) }}
                    </td>

                    <!-- Ações -->
                    <td class="px-3 py-2 whitespace-nowrap font-medium">
                      <button
                        @click="handleRowClick(item)"
                        class="text-brand hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300 text-xs px-3 py-1.5 bg-brand/10 hover:bg-brand/20 rounded transition-colors duration-200"
                        :disabled="loading"
                      >
                        Detalhes
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Empty State para tabela -->
              <div v-if="recentSubmissions.length === 0" class="text-center py-8">
                <DocumentMagnifyingGlassIcon
                  class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4"
                />
                <p class="text-gray-500 dark:text-gray-400">
                  Nenhuma submissão encontrada
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Visualização Completa -->
      <div v-else class="space-y-4">
        <!-- Header da visualização completa -->
        <div
          class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4"
        >
          <h3
            class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-dark-text-primary"
          >
            {{ fullViewTitle }}
            <span class="text-sm font-normal text-gray-500">
              (Total: {{ filteredComplaints.length }})
            </span>
          </h3>

          <!-- Botão Ver Resumo -->
          <div class="flex gap-2 self-end sm:self-auto">
            <button
              @click="toggleAllComplaints"
              class="text-brand hover:text-orange-600 text-sm font-medium flex items-center gap-1.5"
              :disabled="loading"
            >
              <span>Ver Resumo</span>
              <svg
                :class="[
                  'w-4 h-4 transition-transform duration-300',
                  showAllComplaints ? 'rotate-180' : '',
                ]"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </button>
          </div>
        </div>

        <!-- Tabs DINÂMICAS -->
        <div class="border-b border-gray-200 dark:border-gray-700">
          <nav class="-mb-px flex space-x-4 overflow-x-auto">
            <!-- Tabs comuns para todos os roles -->
            <button
              @click="changeTab('suggestions')"
              :class="[
                activeTab === 'suggestions'
                  ? 'border-brand text-brand dark:text-orange-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
                loading ? 'opacity-50 cursor-not-allowed' : '',
              ]"
              :disabled="loading"
            >
              Sugestões
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ getTabCount("suggestions") }}
              </span>
            </button>

            <button
              @click="changeTab('grievances')"
              :class="[
                activeTab === 'grievances'
                  ? 'border-brand text-brand dark:text-orange-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
                loading ? 'opacity-50 cursor-not-allowed' : '',
              ]"
              :disabled="loading"
            >
              Queixas
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ getTabCount("grievances") }}
              </span>
            </button>

            <button
              @click="changeTab('complaints')"
              :class="[
                activeTab === 'complaints'
                  ? 'border-brand text-brand dark:text-orange-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
                loading ? 'opacity-50 cursor-not-allowed' : '',
              ]"
              :disabled="loading"
            >
              Reclamações
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ getTabCount("complaints") }}
              </span>
            </button>

            <!-- Tab específica para Director: Solicitações do Gestor -->
            <button
              v-if="isDirector"
              @click="changeTab('manager_requests')"
              :class="[
                activeTab === 'manager_requests'
                  ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
                loading ? 'opacity-50 cursor-not-allowed' : '',
              ]"
              :disabled="loading"
            >
              Solicitações do Gestor
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ getTabCount("manager_requests") }}
              </span>
            </button>

            <!-- Tab específica para Director: Minhas Intervenções -->
            <button
              v-if="isDirector"
              @click="changeTab('director_interventions')"
              :class="[
                activeTab === 'director_interventions'
                  ? 'border-purple-500 text-purple-600 dark:text-purple-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
                loading ? 'opacity-50 cursor-not-allowed' : '',
              ]"
              :disabled="loading"
            >
              Minhas Intervenções
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ getTabCount("director_interventions") }}
              </span>
            </button>

            <!-- Tab específica para Manager: Intervenções do Director -->
            <button
              v-if="isManager"
              @click="changeTab('director_interventions')"
              :class="[
                activeTab === 'director_interventions'
                  ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
                loading ? 'opacity-50 cursor-not-allowed' : '',
              ]"
              :disabled="loading"
            >
              Intervenções do Director
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ getTabCount("director_interventions") }}
              </span>
            </button>

            <!-- NOVA TAB PARA MANAGER: Minhas Submissões ao Director -->
            <button
              v-if="isManager"
              @click="changeTab('my_submissions_to_director')"
              :class="[
                activeTab === 'my_submissions_to_director'
                  ? 'border-green-500 text-green-600 dark:text-green-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
                loading ? 'opacity-50 cursor-not-allowed' : '',
              ]"
              :disabled="loading"
            >
              Minhas Submissões ao Director
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ getTabCount("my_submissions_to_director") }}
              </span>
            </button>

            <!-- Tab Concluídos -->
            <button
              @click="changeTab('resolved')"
              :class="[
                activeTab === 'resolved'
                  ? 'border-green-500 text-green-600 dark:text-green-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
                loading ? 'opacity-50 cursor-not-allowed' : '',
              ]"
              :disabled="loading"
            >
              Concluídos
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ getTabCount("resolved") }}
              </span>
            </button>

            <!-- Tab Rejeitados -->
            <button
              @click="changeTab('rejected')"
              :class="[
                activeTab === 'rejected'
                  ? 'border-red-500 text-red-600 dark:text-red-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
                loading ? 'opacity-50 cursor-not-allowed' : '',
              ]"
              :disabled="loading"
            >
              Rejeitados
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ getTabCount("rejected") }}
              </span>
            </button>

            <!-- Tab "Todos" -->
            <button
              @click="changeTab('all')"
              :class="[
                activeTab === 'all'
                  ? 'border-brand text-brand dark:text-orange-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1',
                loading ? 'opacity-50 cursor-not-allowed' : '',
              ]"
              :disabled="loading"
            >
              Todos
              <span
                class="bg-gray-100 dark:bg-dark-accent text-gray-900 dark:text-dark-text-primary py-0.5 px-1.5 rounded-full text-xs"
              >
                {{ filteredComplaints.length }}
              </span>
            </button>
          </nav>
        </div>

        <!-- Container com rolagem interna para tabela -->
        <div class="table-scroll-container">
          <div class="overflow-x-auto -mx-4 sm:mx-0">
            <div class="min-w-full inline-block align-middle">
              <table
                class="min-w-full divide-y divide-gray-300 dark:divide-gray-600 text-xs sm:text-sm"
              >
                <thead class="table-header-sticky bg-gray-50 dark:bg-dark-accent">
                  <tr>
                    <!-- Coluna especial para Solicitações do Gestor -->
                    <th
                      v-if="isDirector && activeTab === 'manager_requests'"
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Gestor
                    </th>

                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      ID
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-32"
                    >
                      Título
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Tipo
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Categoria
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Prioridade
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Estado
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Data
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Intervenção
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >
                      Ações
                    </th>
                  </tr>
                </thead>
                <tbody
                  class="bg-white dark:bg-dark-secondary divide-y divide-gray-200 dark:divide-gray-700"
                >
                  <tr
                    v-for="item in currentTabData"
                    :key="item.id"
                    :class="[
                      'hover:bg-gray-50 dark:hover:bg-dark-accent transition-colors',
                      item.has_director_intervention ? 'has-director-response' : '',
                      item.manager_request || item.is_escalated_to_director
                        ? 'has-manager-request'
                        : '',
                      isNewSubmission(item) ? 'new-submission' : '',
                    ]"
                  >
                    <!-- Coluna do Gestor que escalou (apenas para tab manager_requests do Director) -->
                    <td
                      v-if="isDirector && activeTab === 'manager_requests'"
                      class="px-3 py-2 whitespace-nowrap"
                    >
                      <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-1">
                          <UserIcon class="w-3 h-3 text-blue-500 flex-shrink-0" />
                          <span
                            class="text-xs font-medium text-blue-700 dark:text-blue-300 truncate max-w-24"
                          >
                            {{ getEscalatedBy(item) }}
                          </span>
                        </div>
                        <span
                          class="text-xs text-gray-500"
                          :title="getEscalationReason(item)"
                        >
                          {{ truncateText(getEscalationReason(item), 30) }}
                        </span>
                      </div>
                    </td>

                    <!-- ID com indicador de novo -->
                    <td
                      class="px-3 py-2 whitespace-nowrap font-medium relative"
                      :class="[
                        isNewSubmission(item)
                          ? 'text-green-700 dark:text-green-300'
                          : 'text-gray-900 dark:text-dark-text-primary',
                      ]"
                    >
                      <div class="flex items-center gap-1">
                        {{ item.reference_number }}
                        <!-- Indicador de nova solicitação do gestor -->
                        <span
                          v-if="isDirector && isNewManagerRequest(item)"
                          class="inline-flex items-center justify-center w-3 h-3 text-[8px] font-bold text-white bg-blue-500 rounded-full animate-pulse"
                          title="Nova solicitação do gestor"
                        >
                          !
                        </span>
                        <!-- Indicador de nova intervenção do director -->
                        <span
                          v-if="isManager && isNewDirectorIntervention(item)"
                          class="inline-flex items-center justify-center w-3 h-3 text-[8px] font-bold text-white bg-purple-500 rounded-full animate-pulse"
                          title="Nova intervenção do director"
                        >
                          !
                        </span>
                        <!-- Indicador de nova submissão -->
                        <span
                          v-if="isNewSubmission(item)"
                          class="inline-flex items-center justify-center w-3 h-3 text-[8px] font-bold text-white bg-green-500 rounded-full animate-pulse"
                          title="Nova submissão"
                        >
                          N
                        </span>
                      </div>
                    </td>

                    <!-- Título -->
                    <td
                      class="px-3 py-2 text-gray-900 dark:text-dark-text-primary max-w-32 truncate"
                    >
                      {{ item.title || item.description }}
                    </td>

                    <!-- Tipo -->
                    <td class="px-3 py-2 whitespace-nowrap">
                      <span :class="getTypeBadgeClass(item.type)">
                        {{ getTypeLabel(item.type) }}
                      </span>
                    </td>

                    <!-- Categoria -->
                    <td class="px-3 py-2 whitespace-nowrap">
                      <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400"
                      >
                        {{ item.category || item.department || "N/A" }}
                      </span>
                    </td>

                    <!-- Prioridade -->
                    <td class="px-3 py-2 whitespace-nowrap">
                      <span :class="getPriorityBadgeClass(item.priority)">
                        {{ getPriorityLabel(item.priority) }}
                      </span>
                    </td>

                    <!-- Estado -->
                    <td class="px-3 py-2 whitespace-nowrap">
                      <div class="flex flex-col gap-1">
                        <span :class="getStatusBadgeClass(item.status)">
                          {{ getStatusLabel(item.status) }}
                        </span>
                        <span
                          v-if="isDirector && activeTab === 'manager_requests'"
                          class="text-xs text-blue-600 dark:text-blue-400"
                        >
                          Solicitado há {{ getTimeSinceEscalation(item) }}
                        </span>
                      </div>
                    </td>

                    <!-- Data -->
                    <td
                      class="px-3 py-2 whitespace-nowrap text-gray-500 dark:text-gray-400"
                    >
                      {{ formatDate(item.created_at) }}
                    </td>

                    <!-- Coluna de Intervenção -->
                    <td class="px-3 py-2 whitespace-nowrap">
                      <div
                        v-if="
                          item.has_director_intervention ||
                          item.director_interventions?.length > 0
                        "
                        class="flex flex-col gap-1"
                      >
                        <span
                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-300"
                        >
                          <svg
                            class="w-3 h-3 mr-1"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                          >
                            <path
                              fill-rule="evenodd"
                              d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                              clip-rule="evenodd"
                            />
                          </svg>
                          {{ getInterventionType(item) }}
                        </span>
                        <span
                          v-if="item.director_comments_count > 0"
                          class="text-xs text-gray-500"
                        >
                          {{ item.director_comments_count }} comentário(s)
                        </span>
                      </div>
                      <span
                        v-else-if="item.manager_request"
                        class="text-xs text-blue-600 dark:text-blue-400"
                      >
                        Aguardando análise
                      </span>
                      <span v-else class="text-gray-400 text-xs">Sem intervenção</span>
                    </td>

                    <!-- Ações -->
                    <td class="px-3 py-2 whitespace-nowrap font-medium">
                      <button
                        @click="handleRowClick(item)"
                        class="text-brand hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300 text-xs px-3 py-1.5 bg-brand/10 hover:bg-brand/20 rounded transition-colors duration-200"
                        :disabled="loading"
                      >
                        Detalhes
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Empty State para tabela -->
              <div v-if="currentTabData.length === 0" class="text-center py-8">
                <DocumentMagnifyingGlassIcon
                  class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4"
                />
                <p class="text-gray-500 dark:text-gray-400">Nenhum dado encontrado</p>
                <p
                  v-if="activeTab === 'director_interventions' && isDirector"
                  class="text-sm text-gray-400 mt-2"
                >
                  Nenhuma intervenção sua encontrada
                </p>
                <p
                  v-if="activeTab === 'director_interventions' && isManager"
                  class="text-sm text-gray-400 mt-2"
                >
                  Nenhuma intervenção do director encontrada
                </p>
                <p
                  v-if="activeTab === 'manager_requests' && isDirector"
                  class="text-sm text-gray-400 mt-2"
                >
                  Nenhuma solicitação do gestor encontrada
                </p>
                <p
                  v-if="activeTab === 'my_submissions_to_director' && isManager"
                  class="text-sm text-gray-400 mt-2"
                >
                  Nenhuma submissão sua enviada ao director encontrada
                </p>
                <p v-if="activeTab === 'resolved'" class="text-sm text-gray-400 mt-2">
                  Nenhuma submissão concluída encontrada
                </p>
                <p v-if="activeTab === 'rejected'" class="text-sm text-gray-400 mt-2">
                  Nenhuma submissão rejeitada encontrada
                </p>
              </div>
            </div>
          </div>

          <!-- Paginação -->
          <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-4 border-t border-gray-200 dark:border-gray-700"
          >
            <p class="text-xs text-gray-700 dark:text-gray-300">
              Mostrando <span class="font-medium">{{ currentTabData.length }}</span> de
              {{ filteredComplaints.length }} resultados
            </p>
            <div class="flex gap-2 self-end">
              <button
                @click="handleBulkAssign"
                class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-gray-700 dark:text-gray-300 text-xs font-medium hover:bg-gray-50 dark:hover:bg-dark-accent transition-all duration-200"
                v-if="isManager && activeTab !== 'director_interventions'"
                :disabled="loading"
              >
                Atribuição Auto.
              </button>

              <button
                v-if="showAllComplaints"
                @click="exportToPdf"
                class="px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded text-xs font-medium flex items-center gap-1.5 transition-all duration-200"
                :disabled="loading || filteredComplaints.length === 0"
                :title="
                  filteredComplaints.length === 0
                    ? 'Não há dados para exportar'
                    : 'Exportar todas as submissões para PDF'
                "
              >
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                  />
                </svg>
                Exportar PDF ({{ filteredComplaints.length }})
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import { DocumentMagnifyingGlassIcon, UserIcon } from "@heroicons/vue/24/outline";
import ComplaintRow from "./ComplaintRow.vue";

const props = defineProps({
  complaints: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  allComplaints: { type: Array, default: () => [] },
  role: { type: String, default: "manager" },
  recentSubmissions: { type: Array, default: () => [] },
  director_interventions: {
    type: Array,
    default: () => [],
  },
  my_submissions_to_director: {
    type: Array,
    default: () => [],
  },
  counts: {
    type: Object,
    default: () => ({
      suggestions: 0,
      grievances: 0,
      complaints: 0,
      director_interventions: 0,
      manager_requests: 0,
      my_submissions_to_director: 0,
      total: 0,
    }),
  },
  debug_info: { type: Object, default: () => ({}) },
  user: { type: Object, default: () => ({}) },
});

// Debug mode
const debugMode = ref(true);

const debugProps = () => {
  console.log("=== 🔍 DEBUG PROPS PARA GESTOR ===");
  console.log("Role:", props.role);
  console.log("Is Manager?", isManager.value);
  console.log("Is Director?", isDirector.value);

  console.log("\n📊 Dados específicos para Gestor:");
  console.log(
    "director_interventions (props):",
    props.director_interventions?.length || 0
  );
  console.log(
    "my_submissions_to_director (props):",
    props.my_submissions_to_director?.length || 0
  );

  if (props.director_interventions && props.director_interventions.length > 0) {
    console.log("\n📋 Exemplo de director_interventions[0]:");
    const sample = props.director_interventions[0];
    console.log("ID:", sample.id);
    console.log("Reference:", sample.reference_number);
    console.log("has_director_intervention:", sample.has_director_intervention);
    console.log("escalated:", sample.escalated);
    console.log("director_updates:", sample.director_updates?.length || 0);
  }

  if (props.my_submissions_to_director && props.my_submissions_to_director.length > 0) {
    console.log("\n📋 Exemplo de my_submissions_to_director[0]:");
    const sample = props.my_submissions_to_director[0];
    console.log("ID:", sample.id);
    console.log("Reference:", sample.reference_number);
    console.log("escalated:", sample.escalated);
    console.log("escalated_by:", sample.escalated_by);
    console.log("escalated_at:", sample.escalated_at);
  }
};

// Estado
const showAllComplaints = ref(false);
const activeTab = ref("all");
const selectedComplaintId = ref(null);
const loading = ref(false);
const unseenCount = ref(0);
const unseenDirectorInterventions = ref(0);
const unseenManagerRequests = ref(0);
const hasNewData = ref(false);

// Armazenar histórico de notificações vistas
const seenSubmissions = ref(new Set());
const seenInterventions = ref(new Set());
const seenRequests = ref(new Set());

// Filtros locais
const localFilters = ref({
  category: "",
  type: "",
  priority: "",
  status: "",
});

// Computed properties
const isDirector = computed(() => props.role?.toLowerCase() === "director");
const isManager = computed(() => props.role?.toLowerCase() === "manager");

const headerTitle = computed(() => {
  if (isDirector.value) return "Submissões Recentes";
  if (isManager.value) return "Submissões Atribuídas";
  return "Minhas Submissões";
});

const fullViewTitle = computed(() => {
  if (isDirector.value) {
    if (activeTab.value === "manager_requests") return "Solicitações do Gestor";
    if (activeTab.value === "director_interventions") return "Minhas Intervenções";
    if (activeTab.value === "resolved") return "Submissões Concluídas";
    if (activeTab.value === "rejected") return "Submissões Rejeitadas";
    return "Submissões Ativas";
  }
  if (isManager.value) {
    if (activeTab.value === "director_interventions") return "Intervenções do Director";
    if (activeTab.value === "my_submissions_to_director")
      return "Minhas Submissões ao Director";
    if (activeTab.value === "resolved") return "Submissões Concluídas";
    if (activeTab.value === "rejected") return "Submissões Rejeitadas";
    return "Submissões Ativas";
  }
  if (activeTab.value === "resolved") return "Submissões Concluídas";
  if (activeTab.value === "rejected") return "Submissões Rejeitadas";
  return "Minhas Submissões Ativas";
});

// **OBTER AS 4 SUBMISSÕES MAIS RECENTES - ATUALIZADO**
const recentSubmissions = computed(() => {
  // Se temos recentSubmissions específicas do props, usamos essas
  if (props.recentSubmissions && props.recentSubmissions.length > 0) {
    // Filtrar para remover resolved/rejected
    return [...props.recentSubmissions]
      .filter((item) => item.status !== "resolved" && item.status !== "rejected")
      .slice(0, 4);
  }

  // Caso contrário, usamos allComplaints e pegamos as 4 mais recentes
  const data = allComplaints.value;

  if (data.length === 0) return [];

  // Ordenar por data de criação (mais recente primeiro)
  // E filtrar para remover resolved/rejected
  return [...data]
    .filter((item) => item.status !== "resolved" && item.status !== "rejected")
    .sort((a, b) => {
      const dateA = new Date(a.created_at || a.submitted_at || 0);
      const dateB = new Date(b.created_at || b.submitted_at || 0);
      return dateB - dateA;
    })
    .slice(0, 4); // Sempre pegar as 4 mais recentes
});

// Calcular notificações não vistas
const calculateUnseenCounts = () => {
  const data = allComplaints.value;

  // Reset counters
  unseenCount.value = 0;
  unseenDirectorInterventions.value = 0;
  unseenManagerRequests.value = 0;

  data.forEach((item) => {
    // Ignorar itens com status resolved ou rejected
    if (item.status === "resolved" || item.status === "rejected") {
      return;
    }

    const itemId = item.id || item.reference_number;

    // Verificar se é uma nova submissão (menos de 24 horas)
    if (isNewSubmission(item) && !seenSubmissions.value.has(itemId)) {
      unseenCount.value++;
    }

    // Verificar intervenções do director não vistas (para Manager)
    if (
      isManager.value &&
      isNewDirectorIntervention(item) &&
      !seenInterventions.value.has(itemId)
    ) {
      unseenDirectorInterventions.value++;
    }

    // Verificar solicitações do gestor não vistas (para Director)
    if (
      isDirector.value &&
      isNewManagerRequest(item) &&
      !seenRequests.value.has(itemId)
    ) {
      unseenManagerRequests.value++;
    }
  });

  // Atualizar contador total para badge
  if (isDirector.value && unseenManagerRequests.value > 0) {
    hasNewData.value = true;
  } else if (isManager.value && unseenDirectorInterventions.value > 0) {
    hasNewData.value = true;
  } else if (unseenCount.value > 0) {
    hasNewData.value = true;
  } else {
    hasNewData.value = false;
  }
};

// Filtro aplicado às reclamações
const filteredComplaints = computed(() => {
  if (!allComplaints.value || allComplaints.value.length === 0) return [];

  let filtered = [...allComplaints.value];

  // Aplicar filtros locais
  if (localFilters.value.category) {
    filtered = filtered.filter(
      (item) =>
        item.category === localFilters.value.category ||
        item.department === localFilters.value.category
    );
  }

  if (localFilters.value.type) {
    filtered = filtered.filter((item) => {
      const itemType = item.type?.toLowerCase() || "";
      const filterType = localFilters.value.type.toLowerCase();

      if (filterType === "suggestion") {
        return itemType.includes("suggestion") || itemType.includes("sugest");
      } else if (filterType === "complaint") {
        return itemType.includes("complaint") || itemType.includes("reclam");
      } else if (filterType === "grievance") {
        return itemType.includes("grievance") || itemType.includes("queixa");
      }
      return true;
    });
  }

  if (localFilters.value.priority) {
    filtered = filtered.filter((item) => item.priority === localFilters.value.priority);
  }

  if (localFilters.value.status) {
    filtered = filtered.filter((item) => item.status === localFilters.value.status);
  }

  return filtered;
});

// Contar filtros ativos
const activeFiltersCount = computed(() => {
  return Object.values(localFilters.value).filter((value) => value !== "").length;
});

const hasActiveFilters = computed(() => {
  return activeFiltersCount.value > 0;
});

// Método para aplicar filtros
const applyFilters = () => {
  console.log("Filtros aplicados:", localFilters.value);
  calculateUnseenCounts();
};

// Método para resetar filtros
const resetFilters = () => {
  localFilters.value = {
    category: "",
    type: "",
    priority: "",
    status: "",
  };
  applyFilters();
};

// Contadores - usar dados filtrados
const getTabCount = (tab) => {
  const data = getTabData(tab);
  return data.length;
};

// **MÉTODO CORRIGIDO: getTabData**
const getTabData = (tab) => {
  console.log(
    `🔍 getTabData: ${tab} | isManager: ${isManager.value} | isDirector: ${isDirector.value}`
  );

  // Primeiro, vamos obter os dados base filtrados
  const baseData = filteredComplaints.value;

  // Para as tabs principais (suggestions, grievances, complaints, all),
  // vamos EXCLUIR os status "rejected" e "resolved"
  const excludeResolvedRejected = (data) => {
    return data.filter(
      (item) => item.status !== "resolved" && item.status !== "rejected"
    );
  };

  // **TAB ESPECÍFICA PARA GESTOR: Intervenções do Director**
  if (isManager.value && tab === "director_interventions") {
    // Se temos os dados específicos do props, usamos
    if (props.director_interventions && props.director_interventions.length > 0) {
      console.log(
        "📋 Usando props.director_interventions:",
        props.director_interventions.length
      );
      return props.director_interventions;
    }

    // Caso contrário, filtramos da base de dados
    const interventions = baseData.filter((item) => {
      // **CRÍTICO: Apenas onde o Director JÁ RESPONDEU**
      return (
        item.has_director_intervention === true &&
        item.director_response_type !== null &&
        item.director_response_date !== null
      );
    });

    console.log("📋 Filtrado da base:", interventions.length);
    return excludeResolvedRejected(interventions);
  }

  // **TAB ESPECÍFICA PARA GESTOR: Minhas Submissões ao Director**
  if (isManager.value && tab === "my_submissions_to_director") {
    // Se temos os dados específicos do props, usamos
    if (props.my_submissions_to_director && props.my_submissions_to_director.length > 0) {
      console.log(
        "📋 Usando props.my_submissions_to_director:",
        props.my_submissions_to_director.length
      );
      return props.my_submissions_to_director;
    }

    // Caso contrário, filtramos da base de dados
    const mySubmissions = baseData.filter((item) => {
      // **CRÍTICO: Todas as submissões que o gestor enviou ao Director**
      return (
        (item.escalated === true || item.is_escalated_to_director === true) &&
        (item.escalated_by_me === true || item.assigned_to === props.user?.id)
      );
    });

    console.log("📋 Filtrado da base:", mySubmissions.length);
    return excludeResolvedRejected(mySubmissions);
  }

  // Para Director: Solicitações do Gestor
  if (isDirector.value && tab === "manager_requests") {
    const filtered = getManagerRequests();
    return excludeResolvedRejected(filtered);
  }

  // Para Director: Minhas Intervenções
  if (isDirector.value && tab === "director_interventions") {
    const filtered = getDirectorInterventions();
    return excludeResolvedRejected(filtered);
  }

  // Para as tabs Concluídos e Rejeitados, mostramos APENAS esses status
  if (tab === "resolved") {
    return baseData.filter((item) => item.status === "resolved");
  }

  if (tab === "rejected") {
    return baseData.filter((item) => item.status === "rejected");
  }

  // Para as outras tabs, excluímos resolved/rejected
  const data = baseData;
  const filteredByStatus = excludeResolvedRejected(data);

  switch (tab) {
    case "suggestions":
      return filteredByStatus.filter(
        (item) =>
          item.type?.toLowerCase().includes("suggestion") ||
          item.type?.toLowerCase().includes("sugest")
      );
    case "grievances":
      return filteredByStatus.filter(
        (item) =>
          item.type?.toLowerCase().includes("grievance") ||
          item.type?.toLowerCase().includes("queixa")
      );
    case "complaints":
      return filteredByStatus.filter(
        (item) =>
          item.type?.toLowerCase().includes("complaint") ||
          item.type?.toLowerCase().includes("reclam")
      );
    case "all":
    default:
      return data;
  }
};

const getMySubmissionsToDirector = () => {
  if (props.my_submissions_to_director && props.my_submissions_to_director.length > 0) {
    return props.my_submissions_to_director;
  }
  return filteredComplaints.value.filter(
    (item) =>
      item.escalated === true ||
      item.is_escalated_to_director === true ||
      item.manager_request ||
      (item.metadata && item.metadata.is_escalated_to_director === true)
  );
};

// Métodos auxiliares para obter dados específicos
const getManagerRequests = () => {
  if (props.manager_requests && props.manager_requests.length > 0) {
    return props.manager_requests;
  }
  return filteredComplaints.value.filter(
    (item) =>
      item.escalated === true ||
      item.is_escalated_to_director === true ||
      item.manager_request ||
      (item.metadata && item.metadata.is_escalated_to_director === true)
  );
};

const getDirectorInterventions = () => {
  if (props.director_interventions && props.director_interventions.length > 0) {
    return props.director_interventions;
  }
  return filteredComplaints.value.filter(
    (item) =>
      item.has_director_intervention === true ||
      item.director_updates?.length > 0 ||
      item.director_validation ||
      item.director_comments_count > 0
  );
};

// Dados da tab atual (usando dados filtrados)
const currentTabData = computed(() => {
  console.log(`🔄 currentTabData computed chamado, activeTab: ${activeTab.value}`);
  const data = getTabData(activeTab.value);
  console.log(
    `📋 currentTabData retornando: ${data?.length || 0} itens para tab ${activeTab.value}`
  );

  // Log de exemplo dos primeiros itens
  if (data && data.length > 0) {
    console.log(`📄 Primeiro item da tab ${activeTab.value}:`, {
      id: data[0].id,
      reference_number: data[0].reference_number,
      title: data[0].title,
      has_director_intervention: data[0].has_director_intervention,
      escalated: data[0].escalated,
      director_updates_count: data[0].director_updates?.length || 0,
    });
  }

  return data || [];
});

const allComplaints = computed(() => {
  console.log("📦 allComplaints computed chamado");
  console.log("props.allComplaints:", props.allComplaints?.length || 0);
  console.log("props.complaints:", props.complaints?.length || 0);

  // Se temos allComplaints, usamos esses
  if (props.allComplaints && props.allComplaints.length > 0) {
    console.log("✅ Usando props.allComplaints");
    return props.allComplaints;
  }

  // Caso contrário, usamos complaints
  if (props.complaints && props.complaints.length > 0) {
    console.log("✅ Usando props.complaints");
    return props.complaints;
  }

  console.log("⚠️ Nenhum dado encontrado, retornando array vazio");
  return [];
});

// **MÉTODO PRINCIPAL: Mudar de tab sem redirecionamento**
const changeTab = async (tab) => {
  if (loading.value) return;
  if (activeTab.value === tab) return;

  activeTab.value = tab;
  loading.value = true;

  try {
    // Determinar URL baseada no role
    let apiUrl;
    if (isDirector.value) {
      apiUrl = "/director/api/tab-data";
    } else if (isManager.value) {
      apiUrl = "/gestor/api/tab-data";
    } else {
      apiUrl = "/api/submissions/tab-data";
    }

    // Fazer requisição para obter dados específicos da tab
    /* const response = await fetch(`${apiUrl}?tab=${tab}`, {
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
        "X-CSRF-TOKEN":
          document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ||
          "",
      },
    });

    if (response.ok) {
      const result = await response.json();
      console.log(`Dados da tab ${tab}:`, result.data.length);

      // Aqui você precisaria atualizar os dados localmente
      // Dependendo da sua implementação, você pode:
      // 1. Atualizar a propriedade allComplaints
      // 2. Usar um estado reativo para cada tab
      // 3. Redirecionar para uma nova rota com filtros

      // Para simplificar, vamos apenas logar os resultados
      if (result.success) {
        console.log(`Tab ${tab} carregada com ${result.count} itens`);

        // Marcar como visto quando o usuário muda para a tab
        if (tab === "director_interventions" && isManager.value) {
          markDirectorInterventionsAsSeen(result.data);
        } else if (tab === "manager_requests" && isDirector.value) {
          markManagerRequestsAsSeen(result.data);
        } else if (tab === "my_submissions_to_director" && isManager.value) {
          markMySubmissionsAsSeen(result.data);
        }
      }
    }*/
  } catch (error) {
    console.error(`Erro ao carregar tab ${tab}:`, error);
    // Fallback para dados locais
  } finally {
    loading.value = false;
  }

  // Atualizar contadores
  calculateUnseenCounts();
};

const debugData = () => {
  console.log("=== DEBUG DATA ===");
  console.log("Active Tab:", activeTab.value);
  console.log("Filtered Complaints:", filteredComplaints.value.length);
  console.log("Current Tab Data:", currentTabData.value.length);

  // Verificar dados específicos
  if (isManager.value) {
    console.log(
      "Manager - Director Interventions from props:",
      props.director_interventions?.length || 0
    );
    console.log(
      "Manager - My Submissions from props:",
      props.my_submissions_to_director?.length || 0
    );
  }

  if (isDirector.value) {
    console.log(
      "Director - Manager Requests from props:",
      props.manager_requests?.length || 0
    );
    console.log(
      "Director - My Interventions from props:",
      props.director_interventions?.length || 0
    );
  }

  // Testar filtro
  const testData = filteredComplaints.value;
  const withDirectorIntervention = testData.filter(
    (item) =>
      item.has_director_intervention === true ||
      item.director_validation ||
      item.director_updates?.length > 0
  );

  console.log("Items with director intervention:", withDirectorIntervention.length);
  console.log(
    "Sample:",
    withDirectorIntervention.slice(0, 3).map((item) => ({
      id: item.id,
      has_director_intervention: item.has_director_intervention,
      director_updates: item.director_updates?.length,
    }))
  );
};

// Métodos para marcar como visto
const markDirectorInterventionsAsSeen = (interventions) => {
  interventions.forEach((item) => {
    const itemId = item.id || item.reference_number;
    seenInterventions.value.add(itemId);
  });
  calculateUnseenCounts();
};

const markManagerRequestsAsSeen = (requests) => {
  requests.forEach((item) => {
    const itemId = item.id || item.reference_number;
    seenRequests.value.add(itemId);
  });
  calculateUnseenCounts();
};

const markMySubmissionsAsSeen = (submissions) => {
  submissions.forEach((item) => {
    const itemId = item.id || item.reference_number;
    seenSubmissions.value.add(itemId);
  });
  calculateUnseenCounts();
};

const toggleAllComplaints = () => {
  showAllComplaints.value = !showAllComplaints.value;

  // Quando mostrar todos, marcar tudo como visto
  if (showAllComplaints.value) {
    markAllAsSeen();
  }
};

const markAllAsSeen = async () => {
  if (loading.value) return;

  try {
    loading.value = true;

    // Filtrar apenas submissões novas
    const newSubmissions = recentSubmissions.value.filter(
      (item) => isNewSubmission(item) && !seenSubmissions.value.has(item.id)
    );

    if (newSubmissions.length === 0) {
      console.log("Nenhuma submissão nova para marcar como vista");
      return;
    }

    console.log(`Marcando ${newSubmissions.length} submissões como vistas...`);

    // Determinar a URL correta
    let markAllUrl;
    if (isDirector.value) {
      markAllUrl = `/director/api/mark-all-as-seen`;
    } else if (isManager.value) {
      markAllUrl = `/gestor/api/mark-all-as-seen`;
    } else {
      markAllUrl = `/api/submissions/mark-all-as-seen`;
    }

    // Enviar IDs das submissões para marcar como vistas
    const submissionIds = newSubmissions.map((item) => item.id);

    const response = await fetch(markAllUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN":
          document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ||
          "",
        Accept: "application/json",
      },
      body: JSON.stringify({
        submission_ids: submissionIds,
        status: "under_review",
      }),
    });

    if (response.ok) {
      const result = await response.json();

      if (result.success) {
        // Atualizar todas as submissões localmente
        newSubmissions.forEach((item) => {
          const index = allComplaints.value.findIndex((c) => c.id === item.id);
          if (index !== -1) {
            allComplaints.value[index].status = "under_review";
            allComplaints.value[index].reviewed_at = new Date().toISOString();
            allComplaints.value[index].reviewed_by = props.user?.id;

            // Adicionar ao histórico de vistos
            seenSubmissions.value.add(item.id);
          }
        });

        // Resetar contadores
        unseenCount.value = 0;
        unseenDirectorInterventions.value = 0;
        unseenManagerRequests.value = 0;
        hasNewData.value = false;

        showToast(
          `${newSubmissions.length} submissões marcadas como 'Em Análise'`,
          "success"
        );
      }
    }
  } catch (error) {
    console.error("Erro ao marcar todas como vistas:", error);
    showToast("Erro ao marcar submissões como vistas", "error");
  } finally {
    loading.value = false;
  }
};

// **NOVO MÉTODO: Marcar submissão específica como vista**
const markSubmissionAsSeen = async (item) => {
  if (loading.value) return;

  console.log("Marcando submissão como vista:", item.id);

  try {
    loading.value = true;

    // Determinar a URL correta baseada no role
    let markSeenUrl;
    if (isDirector.value) {
      markSeenUrl = `/director/grievances/${item.id}/mark-as-seen`;
    } else if (isManager.value) {
      markSeenUrl = `/gestor/grievances/${item.id}/mark-as-seen`;
    } else {
      markSeenUrl = `/api/grievances/${item.id}/mark-as-seen`;
    }

    // Fazer requisição para marcar como visto
    const response = await fetch(markSeenUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN":
          document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ||
          "",
        Accept: "application/json",
      },
      body: JSON.stringify({
        status: "under_review", // Atualizar status para "Em Análise"
      }),
    });

    if (!response.ok) {
      throw new Error("Erro ao atualizar status");
    }

    const result = await response.json();

    if (result.success) {
      console.log(`Submissão ${item.id} marcada como vista:`, result);

      // Atualizar o item localmente
      const index = allComplaints.value.findIndex((c) => c.id === item.id);
      if (index !== -1) {
        // Remover o indicador "NOVO"
        allComplaints.value[index].status = "under_review";
        allComplaints.value[index].reviewed_at = new Date().toISOString();
        allComplaints.value[index].reviewed_by = props.user?.id;
      }

      // Atualizar contadores
      calculateUnseenCounts();

      // Mostrar feedback
      showToast("Submissão marcada como 'Em Análise'", "success");
    }
  } catch (error) {
    console.error("Erro ao marcar como visto:", error);
    showToast("Erro ao atualizar submissão", "error");
  } finally {
    loading.value = false;
  }
};

// ComplaintsList.vue - método handleRowClick ATUALIZADO
const handleRowClick = async (item) => {
  if (loading.value) return;
  console.log("=== DEBUG CLICK ===");
  console.log("props.role:", props.role);
  console.log("isDirector.value:", isDirector.value);
  console.log("isManager.value:", isManager.value);
  console.log("Item reference:", item.reference_number);

  selectedComplaintId.value = item.id;
  // Marcar como visto
  const itemId = item.id || item.reference_number;
  seenSubmissions.value.add(itemId);

  if (isNewDirectorIntervention(item)) {
    seenInterventions.value.add(itemId);
  }

  if (isNewManagerRequest(item)) {
    seenRequests.value.add(itemId);
  }

  // Atualizar contadores
  calculateUnseenCounts();

  // **SE A SUBMISSÃO É NOVA E TEM LABEL "NOVO", ATUALIZAR STATUS PARA "EM ANÁLISE"**
  if (isNewSubmission(item) && props.role !== "utente") {
    try {
      loading.value = true;

      // Determinar a URL correta baseada no role
      let markSeenUrl;
      if (isDirector.value) {
        markSeenUrl = `/director/grievances/${item.id}/mark-as-seen`;
      } else if (isManager.value) {
        markSeenUrl = `/gestor/grievances/${item.id}/mark-as-seen`;
      } else {
        // Rota API comum
        markSeenUrl = `/api/grievances/${item.id}/mark-as-seen`;
      }

      // Fazer requisição para atualizar o status
      const response = await fetch(markSeenUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN":
            document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ||
            "",
          Accept: "application/json",
        },
        body: JSON.stringify({
          status: "under_review",
        }),
      });

      if (!response.ok) {
        throw new Error("Erro ao atualizar status");
      }

      const result = await response.json();
      console.log(
        `Status atualizado para "Em Análise" para submissão ${item.id}:`,
        result
      );

      // Atualizar o item localmente
      if (result.success && result.grievance) {
        const index = allComplaints.value.findIndex((c) => c.id === item.id);
        if (index !== -1) {
          allComplaints.value[index].status = "under_review";
        }
      }
    } catch (error) {
      console.error("Erro ao atualizar status:", error);
      // Não impedir a navegação mesmo se houver erro
    } finally {
      loading.value = false;
    }
  }

  // URL baseada no role
  let url;
  if (isDirector.value) {
    // Para Director, use a rota do director
    url = `/director/grievances/${item.reference_number || item.id}`;
  } else if (isManager.value) {
    // Para Manager, use a rota do gestor
    url = `/gestor/grievances/${item.reference_number || item.id}`;
  } else {
    // Para utente ou outros
    url = `/complaints/${item.reference_number || item.id}`;
  }
  console.log(`Navegando para: ${url}`);
  router.get(url);
};

const handleBulkAssign = () => {
  if (loading.value) return;
  // Filtrar apenas submissões que não estão resolved/rejected
  const assignableItems = currentTabData.value.filter(
    (item) => item.status !== "resolved" && item.status !== "rejected"
  );

  if (assignableItems.length === 0) {
    alert("Não há submissões disponíveis para atribuição automática.");
    return;
  }

  // Implementar lógica de atribuição automática
  console.log(`Atribuindo automaticamente ${assignableItems.length} submissões...`);
};

const getExportLabel = () => {
  const labels = {
    suggestions: "Sugestões",
    grievances: "Queixas",
    complaints: "Reclamações",
    manager_requests: "Solicitações do Gestor",
    director_interventions: isDirector.value
      ? "Minhas Intervenções"
      : "Intervenções do Director",
    my_submissions_to_director: "Minhas Submissões ao Director",
    resolved: "Concluídos",
    rejected: "Rejeitados",
    all: "Todas as Submissões Ativas",
  };
  return labels[activeTab.value] || "Dados";
};

// Helper para obter tipo de intervenção
const getInterventionType = (item) => {
  if (!item) return "Sem intervenção";

  // Verificar validação do director
  if (item.director_validation) {
    const status = item.director_validation.status;
    return status === "approved"
      ? "Aprovado"
      : status === "rejected"
      ? "Rejeitado"
      : status === "needs_revision"
      ? "Revisão Solicitada"
      : "Validado";
  }

  // Verificar se foi reencaminhado
  if (
    item.escalated ||
    item.metadata?.is_escalated_to_director ||
    item.is_escalated_to_director
  ) {
    return "Reencaminhado";
  }

  // Verificar se tem updates do director
  if (item.director_updates && item.director_updates.length > 0) {
    const lastUpdate = item.director_updates[0];
    if (lastUpdate.action_type === "director_comment") return "Comentário do Director";
    if (lastUpdate.action_type === "director_validation_approved")
      return "Aprovado pelo Director";
    if (lastUpdate.action_type === "director_validation_rejected")
      return "Rejeitado pelo Director";
    if (lastUpdate.action_type === "director_validation_needs_revision")
      return "Revisão Solicitada";
    return "Intervenção do Director";
  }

  // Verificar em updates gerais
  if (item.updates && item.updates.length > 0) {
    const directorUpdates = item.updates.filter(
      (u) => u.action_type?.includes("director") || (u.user && u.user.role === "Director")
    );

    if (directorUpdates.length > 0) {
      const lastUpdate = directorUpdates[0];
      if (lastUpdate.action_type === "director_comment") return "Comentário do Director";
      if (lastUpdate.action_type?.includes("validation")) return "Validação do Director";
      return "Intervenção do Director";
    }
  }

  // Verificar contagem de comentários
  if (item.director_comments_count > 0) {
    return `${item.director_comments_count} Comentário(s) do Director`;
  }

  return "Intervenção do Director";
};

// Métodos auxiliares de validação
const isNewSubmission = (item) => {
  if (!item.created_at) return false;
  const createdAt = new Date(item.created_at);
  const now = new Date();
  const hoursDiff = (now - createdAt) / (1000 * 60 * 60);
  return hoursDiff < 24; // Considerar "novo" se tiver menos de 24 horas
};

const isNewDirectorIntervention = (item) => {
  if (!item.director_updates || item.director_updates.length === 0) return false;

  const lastIntervention = item.director_updates[0];
  if (!lastIntervention.created_at) return false;

  const interventionDate = new Date(lastIntervention.created_at);
  const now = new Date();
  const hoursDiff = (now - interventionDate) / (1000 * 60 * 60);

  return hoursDiff < 24; // Considerar "nova" se tiver menos de 24 horas
};

const isNewManagerRequest = (item) => {
  if (!item.escalated_at && !item.manager_request?.escalated_at) return false;

  const escalatedAt = item.escalated_at || item.manager_request?.escalated_at;
  const escalationDate = new Date(escalatedAt);
  const now = new Date();
  const hoursDiff = (now - escalationDate) / (1000 * 60 * 60);

  return hoursDiff < 24; // Considerar "nova" se tiver menos de 24 horas
};

const isRecentSubmission = (item) => {
  return (
    isNewSubmission(item) || isNewDirectorIntervention(item) || isNewManagerRequest(item)
  );
};

// Métodos para Solicitações do Gestor (Director)
const getEscalatedBy = (item) => {
  if (item.manager_request?.escalated_by?.name) {
    return item.manager_request.escalated_by.name;
  }
  if (item.escalated_by?.name) {
    return item.escalated_by.name;
  }
  return "Gestor";
};

const getEscalationReason = (item) => {
  if (item.manager_request?.escalation_reason) {
    return item.manager_request.escalation_reason;
  }
  if (item.escalation_reason) {
    return item.escalation_reason;
  }
  return "Sem motivo especificado";
};

const getTimeSinceEscalation = (item) => {
  const escalatedAt = item.escalated_at || item.manager_request?.escalated_at;
  if (!escalatedAt) return "desconhecido";

  const escalationDate = new Date(escalatedAt);
  const now = new Date();
  const diffMs = now - escalationDate;
  const diffHours = Math.floor(diffMs / (1000 * 60 * 60));

  if (diffHours < 1) {
    const diffMinutes = Math.floor(diffMs / (1000 * 60));
    return `${diffMinutes} min`;
  } else if (diffHours < 24) {
    return `${diffHours} h`;
  } else {
    const diffDays = Math.floor(diffHours / 24);
    return `${diffDays} dias`;
  }
};

// Métodos auxiliares de formatação
const getTypeBadgeClass = (type) => {
  const normalizedType = type?.toLowerCase() || "";
  const classes = {
    suggestion: "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400",
    grievance: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
    complaint: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
  };
  const defaultClass = "bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400";

  return `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
    classes[normalizedType] || defaultClass
  }`;
};

const getPriorityBadgeClass = (priority) => {
  const classes = {
    low: "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
    medium: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
    high: "bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400",
    critical: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
  };
  const defaultClass = "bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400";

  return `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
    classes[priority] || defaultClass
  }`;
};

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
    submitted: "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400",
    in_progress:
      "bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400",
    resolved: "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
    closed: "bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400",
    escalated: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
    under_review:
      "bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400",
    pending_approval:
      "bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400",
    assigned: "bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-400",
    rejected: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
  };
  const defaultClass = "bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400";

  return `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
    classes[status] || defaultClass
  }`;
};

const getTypeLabel = (type) => {
  if (!type) return "Tipo não definido";

  const normalizedType = type.toLowerCase();
  const labels = {
    suggestion: "Sugestão",
    grievance: "Queixa",
    complaint: "Reclamação",
    sugestão: "Sugestão",
    sugestao: "Sugestão",
    queixa: "Queixa",
    reclamação: "Reclamação",
    reclamacao: "Reclamação",
  };

  return labels[normalizedType] || type.charAt(0).toUpperCase() + type.slice(1);
};

const getPriorityLabel = (priority) => {
  if (!priority) return "Prioridade não definida";

  const labels = {
    low: "Baixa",
    medium: "Média",
    high: "Alta",
    critical: "Crítica",
  };

  return labels[priority] || priority.charAt(0).toUpperCase() + priority.slice(1);
};

const getStatusLabel = (status) => {
  if (!status) return "Estado não definido";

  const labels = {
    pending: "Pendente",
    submitted: "Submetida",
    in_progress: "Em Progresso",
    resolved: "Resolvida",
    closed: "Fechada",
    escalated: "Escalada",
    under_review: "Em Análise",
    pending_approval: "Pendente Aprovação",
    assigned: "Atribuída",
    rejected: "Rejeitada",
  };

  return labels[status] || status.charAt(0).toUpperCase() + status.slice(1);
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString("pt-PT", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  } catch (error) {
    console.error("Erro ao formatar data:", error);
    return "Data inválida";
  }
};

const truncateText = (text, maxLength) => {
  if (!text) return "";
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength) + "...";
};

// Inicializar filtros com base nos props
watch(
  () => props.filters,
  (newFilters) => {
    if (newFilters) {
      // Actualizar filtros locais baseados nos filtros recebidos
      if (newFilters.category) localFilters.value.category = newFilters.category;
      if (newFilters.type) localFilters.value.type = newFilters.type;
      if (newFilters.priority) localFilters.value.priority = newFilters.priority;
      if (newFilters.status) localFilters.value.status = newFilters.status;
    }
  },
  { immediate: true }
);

// Atualizar contadores quando os dados mudam
watch(
  () => props.allComplaints,
  () => {
    calculateUnseenCounts();
  },
  { deep: true }
);

onMounted(() => {
  console.log("=== 🚨 VERIFICAÇÃO COMPLETA DE TODOS OS PROPS NO COMPLAINTSLIST ===");

  // Mostrar TODOS os props
  const allProps = Object.keys(props);
  console.log("📋 TODOS OS PROPS DISPONÍVEIS:", allProps);

  allProps.forEach((propName) => {
    const propValue = props[propName];
    console.log(`\n📌 Prop: ${propName}`);
    console.log(`   Tipo: ${typeof propValue}`);

    if (Array.isArray(propValue)) {
      console.log(`   É array? ✅ SIM`);
      console.log(`   Tamanho: ${propValue.length}`);
      if (propValue.length > 0) {
        console.log(`   Primeiro item:`, propValue[0]);
        console.log(`   Campos do primeiro item:`, Object.keys(propValue[0]));
      }
    } else if (typeof propValue === "object" && propValue !== null) {
      console.log(`   É objeto? ✅ SIM`);
      console.log(`   Chaves:`, Object.keys(propValue));
      console.log(`   Valores:`, propValue);
    } else {
      console.log(`   Valor:`, propValue);
    }
  });

  // Verificar especificamente os props que nos interessam
  console.log("\n=== 🔍 PROPS ESPECÍFICOS DETALHADOS ===");

  const specificProps = [
    "director_interventions",
    "my_submissions_to_director",
    "manager_requests",
    "counts",
  ];
  specificProps.forEach((propName) => {
    console.log(`\n🎯 ${propName}:`);
    console.log(`   Disponível? ${propName in props ? "✅ SIM" : "❌ NÃO"}`);
    console.log(`   Valor:`, props[propName]);

    if (props[propName] && Array.isArray(props[propName])) {
      console.log(`   Tamanho do array: ${props[propName].length}`);
      if (props[propName].length > 0) {
        console.log(`   Estrutura do primeiro item:`);
        const firstItem = props[propName][0];
        Object.keys(firstItem).forEach((key) => {
          console.log(`     - ${key}: ${firstItem[key]} (${typeof firstItem[key]})`);
        });
      }
    }
  });

  // Calcular contadores não vistos
  calculateUnseenCounts();
});

const exportToPdf = () => {
  if (loading.value) return;

  try {
    loading.value = true;

    console.log("=== EXPORTAÇÃO DE RELATÓRIO COMPLETO ===");
    console.log("Role:", props.role);
    console.log("Tab ativa:", activeTab.value);
    console.log("Filtros locais:", localFilters.value);

    // Preparar parâmetros
    const params = new URLSearchParams();

    // Usar tab atual (somente se não for "all")
    if (activeTab.value && activeTab.value !== "all") {
      params.append("tab", activeTab.value);
      console.log("Adicionando tab aos parâmetros:", activeTab.value);
    }

    // Adicionar filtros atuais APENAS se não forem vazios
    if (localFilters.value.type && localFilters.value.type.trim() !== "") {
      params.append("type", localFilters.value.type);
      console.log("Adicionando type:", localFilters.value.type);
    }

    if (localFilters.value.status && localFilters.value.status.trim() !== "") {
      params.append("status", localFilters.value.status);
      console.log("Adicionando status:", localFilters.value.status);
    }

    if (localFilters.value.priority && localFilters.value.priority.trim() !== "") {
      params.append("priority", localFilters.value.priority);
      console.log("Adicionando priority:", localFilters.value.priority);
    }

    if (localFilters.value.category && localFilters.value.category.trim() !== "") {
      params.append("category", localFilters.value.category);
      console.log("Adicionando category:", localFilters.value.category);
    }

    // Adicionar período fixo (opcional)
    // params.append('period', 'month'); // Descomente se quiser período fixo

    // Adicionar data para evitar cache
    params.append("_", Date.now());

    // DETERMINAR URL BASEADA NO ROLE
    let baseUrl;
    let roleName;

    if (isDirector.value) {
      baseUrl = "/director/export/complete-report";
      roleName = "Director";
    } else if (isManager.value) {
      baseUrl = "/gestor/export/complete-report";
      roleName = "Gestor";
    } else {
      baseUrl = "/export/complete-report";
      roleName = "Usuário";
    }

    const url = `${baseUrl}${params.toString() ? "?" + params.toString() : ""}`;

    console.log(`📤 URL de exportação para ${roleName}:`, url);
    console.log("Parâmetros:", params.toString());

    // Verificar se há dados para exportar
    const hasData = currentTabData.value.length > 0;
    if (!hasData) {
      showToast(
        `Não há dados disponíveis na tab "${activeTab.value}" para exportar`,
        "warning"
      );
      loading.value = false;
      return;
    }

    // Mostrar mensagem informativa
    let tabName = "";
    switch (activeTab.value) {
      case "suggestions":
        tabName = "Sugestões";
        break;
      case "grievances":
        tabName = "Queixas";
        break;
      case "complaints":
        tabName = "Reclamações";
        break;
      case "resolved":
        tabName = "Concluídos";
        break;
      case "rejected":
        tabName = "Rejeitados";
        break;
      case "manager_requests":
        tabName = "Solicitações do Gestor";
        break;
      case "director_interventions":
        tabName = "Intervenções do Director";
        break;
      case "my_submissions_to_director":
        tabName = "Minhas Submissões ao Director";
        break;
      default:
        tabName = "Todas as Submissões";
    }

    const itemCount = currentTabData.value.length;
    const message = `Exportando ${itemCount} ${
      itemCount === 1 ? "item" : "itens"
    } da tab "${tabName}" para ${roleName}...`;

    showToast(message, "info");

    // Criar link temporário para download
    const link = document.createElement("a");
    link.href = url;
    link.target = "_blank";
    link.rel = "noopener noreferrer";

    // Adicionar ao DOM, clicar e remover
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Mostrar mensagem de sucesso após um tempo
    setTimeout(() => {
      loading.value = false;
      const successMsg = `Relatório de ${tabName} exportado com sucesso! ${itemCount} ${
        itemCount === 1 ? "registro" : "registros"
      }.`;
      showToast(successMsg, "success");

      // Log adicional para debug
      console.log("✅ Exportação concluída com sucesso");
      console.log(`📊 Dados exportados: ${itemCount} itens`);
      console.log(`🏷️ Tab: ${tabName}`);
      console.log(`👤 Role: ${roleName}`);
    }, 2000);
  } catch (error) {
    console.error("❌ Erro ao exportar relatório:", error);

    // Mensagem de erro específica
    let errorMessage = "Erro ao gerar relatório";
    if (isDirector.value) {
      errorMessage = "Erro ao gerar relatório do Director";
    } else if (isManager.value) {
      errorMessage = "Erro ao gerar relatório do Gestor";
    }

    showToast(`${errorMessage}: ${error.message}`, "error");
    loading.value = false;
  }
};

const showToast = (message, type = "info") => {
  // Remover toast anterior se existir
  const existingToast = document.querySelector(".custom-toast");
  if (existingToast) {
    existingToast.remove();
  }

  // Criar novo toast
  const toast = document.createElement("div");
  toast.className = `custom-toast fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transform transition-all duration-300 ${
    type === "success"
      ? "bg-green-500 text-white"
      : type === "error"
      ? "bg-red-500 text-white"
      : type === "warning"
      ? "bg-yellow-500 text-white"
      : "bg-blue-500 text-white"
  }`;

  toast.innerHTML = `
        <div class="flex items-center gap-2">
            ${
              type === "success"
                ? "✓"
                : type === "error"
                ? "✗"
                : type === "warning"
                ? "⚠"
                : "ℹ"
            }
            <span>${message}</span>
        </div>
    `;

  document.body.appendChild(toast);

  // Mostrar toast
  setTimeout(() => {
    toast.classList.add("opacity-100", "translate-y-0");
  }, 10);

  // Remover após 5 segundos
  setTimeout(() => {
    toast.classList.remove("opacity-100", "translate-y-0");
    toast.classList.add("opacity-0", "translate-y-2");

    setTimeout(() => {
      if (toast.parentNode) {
        toast.remove();
      }
    }, 300);
  }, 5000);
};
</script>

<style scoped>
.custom-toast {
  opacity: 0;
  transform: translateY(-20px);
  transition: all 0.3s ease;
}

.custom-toast.opacity-100 {
  opacity: 1;
  transform: translateY(0);
}

/* Container principal da tabela */
.table-wrapper {
  position: relative;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  overflow: hidden;
}

.dark .table-wrapper {
  border-color: #374151;
}

/* Header sticky para tabelas */
.table-header-sticky {
  position: sticky;
  top: 0;
  z-index: 10;
}

/* Container de scroll precisa ser relativo para sticky funcionar */
.table-scroll-container {
  position: relative;
  max-height: 600px;
  overflow-y: auto;
}

/* Container do cabeçalho fixo */
.table-header-wrapper {
  position: sticky;
  top: 0;
  z-index: 50;
  background-color: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.dark .table-header-wrapper {
  background-color: #1e293b;
  border-bottom-color: #374151;
}

/* Container do corpo da tabela com scroll */
.table-body-scroll-container {
  max-height: 400px;
  overflow-y: auto;
  overflow-x: hidden;
}

/* Tabela - configuração geral */
.complaints-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
}

/* Cabeçalho da tabela */
.complaints-table thead {
  position: sticky;
  top: 0;
  z-index: 50;
}

/* Larguras específicas para cada coluna */
.complaints-table th:nth-child(1),
.complaints-table td:nth-child(1) {
  width: 10%;
  min-width: 80px;
}

.complaints-table th:nth-child(2),
.complaints-table td:nth-child(2) {
  width: 12%;
  min-width: 100px;
}

.complaints-table th:nth-child(3),
.complaints-table td:nth-child(3) {
  width: 12%;
  min-width: 100px;
}

.complaints-table th:nth-child(4),
.complaints-table td:nth-child(4) {
  width: 15%;
  min-width: 120px;
}

.complaints-table th:nth-child(5),
.complaints-table td:nth-child(5) {
  width: 10%;
  min-width: 90px;
}

.complaints-table th:nth-child(6),
.complaints-table td:nth-child(6) {
  width: 20%;
  min-width: 150px;
}

.complaints-table th:nth-child(7),
.complaints-table td:nth-child(7) {
  width: 12%;
  min-width: 100px;
}

/* Ajuste para as células */
.complaints-table th,
.complaints-table td {
  padding: 0.5rem 0.75rem;
  vertical-align: middle;
  white-space: nowrap;
}

/* Destaque para intervenções do director */
tr.has-director-response {
  background-color: rgba(147, 51, 234, 0.05) !important;
}

tr.has-director-response:hover {
  background-color: rgba(147, 51, 234, 0.1) !important;
}

/* Destaque para solicitações do gestor */
tr.has-manager-request {
  background-color: rgba(59, 130, 246, 0.05) !important;
  border-left: 3px solid #3b82f6;
}

tr.has-manager-request:hover {
  background-color: rgba(59, 130, 246, 0.1) !important;
}

/* Estilo para novas submissões */
tr.new-submission {
  background-color: rgba(34, 197, 94, 0.05) !important;
  border-left: 3px solid #10b981;
}

tr.new-submission:hover {
  background-color: rgba(34, 197, 94, 0.1) !important;
}

/* Estilo para botões desabilitados */
button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Animação para novos itens */
@keyframes highlight {
  0% {
    background-color: rgba(34, 197, 94, 0.1);
  }

  100% {
    background-color: transparent;
  }
}

tr:has(.text-green-700) {
  animation: highlight 2s ease-in-out;
}

/* Badge de notificação pulsante */
@keyframes pulse {
  0% {
    transform: scale(1);
    opacity: 1;
  }

  50% {
    transform: scale(1.1);
    opacity: 0.8;
  }

  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.animate-pulse {
  animation: pulse 2s infinite;
}

/* Scrollbar personalizada */
.table-body-scroll-container::-webkit-scrollbar {
  width: 8px;
}

.table-body-scroll-container::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.table-body-scroll-container::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.table-body-scroll-container::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Estilos para modo escuro */
.dark .table-body-scroll-container::-webkit-scrollbar-track {
  background: #2d3748;
}

.dark .table-body-scroll-container::-webkit-scrollbar-thumb {
  background: #4a5568;
}

.dark .table-body-scroll-container::-webkit-scrollbar-thumb:hover {
  background: #718096;
}

/* Estilos para os selects */
select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
}
</style>
