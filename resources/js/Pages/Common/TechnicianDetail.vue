<template>
  <UnifiedLayout>
    <div class="min-h-screen dark:bg-dark-primary p-4 sm:p-6">
      <!-- Breadcrumb - use URLs diretas -->
      <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm">
          <li>
            <Link
              :href="getDashboardLink()"
              class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
            >
              Dashboard
            </Link>
          </li>
          <li class="text-gray-400 dark:text-gray-500">/</li>
          <li>
            <Link
              :href="getBackLink()"
              class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
            >
              {{ getBackLabel() }}
            </Link>
          </li>
          <li class="text-gray-400 dark:text-gray-500">/</li>
          <li class="text-gray-700 dark:text-gray-300 font-semibold">
            {{ technician.name }}
          </li>
        </ol>
      </nav>

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
          <div class="flex-shrink-0">
            <div
              :class="[
                'h-16 w-16 rounded-full flex items-center justify-center',
                technician.role === 'manager'
                  ? 'bg-purple-100 dark:bg-purple-900/20'
                  : 'bg-blue-100 dark:bg-blue-900/20',
              ]"
            >
              <span
                :class="[
                  'text-2xl font-bold',
                  technician.role === 'manager'
                    ? 'text-purple-600 dark:text-purple-400'
                    : 'text-blue-600 dark:text-blue-400',
                ]"
              >
                {{ getInitials(technician.name) }}
              </span>
            </div>
          </div>
          <div>
            <div class="flex items-center gap-3 mb-2">
              <h1
                class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-dark-text-primary"
              >
                {{ technician.name }}
              </h1>
              <span
                :class="[
                  'px-3 py-1 text-xs font-semibold rounded-full',
                  technician.role === 'manager'
                    ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/20 dark:text-purple-300'
                    : 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300',
                ]"
              >
                {{ getRoleLabel(technician.role) }}
              </span>
            </div>
            <div class="flex items-center gap-3">
              <span
                :class="[
                  'px-3 py-1 text-sm font-semibold rounded-full',
                  technician.is_available
                    ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300'
                    : 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300',
                ]"
              >
                {{ technician.is_available ? "Disponível" : "Indisponível" }}
              </span>
              <span class="text-gray-500 dark:text-gray-400 text-sm">
                {{ technician.role === "manager" ? "Gestor" : "Técnico" }} desde
                {{ formatDate(technician.created_at) }}
              </span>
            </div>
          </div>
        </div>

        <div class="flex gap-2">
          <Link
            :href="
              technician.role === 'manager'
                ? '/gestor/technicians?role=Gestor'
                : '/gestor/technicians'
            "
            class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-300 rounded-lg transition-colors font-medium"
          >
            <ArrowLeftIcon class="w-5 h-5 mr-2" />
            Voltar
          </Link>
        </div>
      </div>

      <!-- Stats Cards - Usando KpiCard moderno -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total de Tarefas/Reclamações -->
        <KpiCard
          :title="
            technician.role === 'manager' ? 'Total de Reclamações' : 'Total de Tarefas'
          "
          :value="stats.total_assigned || 0"
          :description="
            technician.role === 'manager'
              ? 'Total atribuído ao departamento'
              : 'Total de tarefas atribuídas'
          "
          icon="DocumentTextIcon"
          :trend="stats.total_assigned_trend || 'stable'"
        />

        <!-- Concluídas -->
        <KpiCard
          :title="
            technician.role === 'manager'
              ? 'Reclamações Concluídas'
              : 'Tarefas Concluídas'
          "
          :value="stats.completed || 0"
          :description="`${stats.completion_rate || 0}% de taxa de conclusão`"
          icon="CheckCircleIcon"
          :trend="stats.completion_trend || 'up'"
        />

        <!-- Pendentes -->
        <KpiCard
          :title="
            technician.role === 'manager' ? 'Reclamações Pendentes' : 'Tarefas Pendentes'
          "
          :value="stats.pending || 0"
          :description="
            technician.role === 'manager'
              ? 'Aguardando resolução'
              : 'Em progresso ou atribuídas'
          "
          icon="ClockIcon"
          :trend="stats.pending_trend || 'stable'"
        />

        <!-- Tempo Médio -->
        <KpiCard
          :title="technician.role === 'manager' ? 'Tempo Médio Resolução' : 'Tempo Médio'"
          :value="stats.average_resolution_time || 0"
          :description="`Horas por ${
            technician.role === 'manager' ? 'reclamação' : 'tarefa'
          }`"
          icon="ExclamationCircleIcon"
          :trend="stats.time_trend || 'down'"
        />
      </div>

      <!-- Main Content -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Informações Pessoais -->
          <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
            <h2
              class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
            >
              Informações Pessoais
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Nome Completo
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ technician.name }}
                </p>
              </div>
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Username
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  @{{ technician.username }}
                </p>
              </div>
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Email
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ technician.email }}
                </p>
              </div>
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Telefone
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ technician.phone || "N/A" }}
                </p>
              </div>
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Data de Registo
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ formatDate(technician.created_at) }}
                </p>
              </div>
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Última Actualização
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ formatDate(technician.updated_at) }}
                </p>
              </div>
              <div v-if="technician.role === 'manager' && manager_info?.department">
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Departamento Gerido
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ manager_info.department.name }}
                </p>
              </div>
              <div v-if="technician.role === 'manager' && manager_info?.department">
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Descrição do Departamento
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ manager_info.department.description || "N/A" }}
                </p>
              </div>
            </div>
          </div>

          <!-- Localização -->
          <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
            <h2
              class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
            >
              Localização
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Província
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ technician.province }}
                </p>
              </div>
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Distrito
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ technician.district }}
                </p>
              </div>
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Bairro
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ technician.neighborhood }}
                </p>
              </div>
              <div>
                <label
                  class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1"
                >
                  Rua/Avenida
                </label>
                <p class="text-gray-800 dark:text-dark-text-primary">
                  {{ technician.street || "N/A" }}
                </p>
              </div>
            </div>
          </div>

          <!-- Tarefas Recentes (apenas para técnicos) -->
          <div
            v-if="technician.role === 'technician'"
            class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm overflow-hidden"
          >
            <!-- Cabeçalho fixo -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary">
                Tarefas Recentes
              </h2>
            </div>

            <!-- Conteúdo com scroll -->
            <div
              class="overflow-y-auto"
              style="max-height: calc(100vh - 82px); min-height: 200px"
            >
              <div class="p-6 pt-4">
                <div class="space-y-3">
                  <div
                    v-for="task in recent_tasks"
                    :key="task.id"
                    class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                  >
                    <div class="flex justify-between items-start">
                      <div class="flex-1">
                        <h3
                          class="font-medium text-gray-800 dark:text-dark-text-primary mb-1"
                        >
                          {{ task.title }}
                        </h3>
                        <div class="flex flex-wrap gap-2 mb-2">
                          <span
                            :class="[
                              'px-2 py-1 text-xs font-semibold rounded-full',
                              getStatusClass(task.status),
                            ]"
                          >
                            {{ getStatusLabel(task.status) }}
                          </span>
                          <span
                            :class="[
                              'px-2 py-1 text-xs font-semibold rounded-full',
                              getPriorityClass(task.priority),
                            ]"
                          >
                            {{ capitalizePriority(task.priority) || "Normal" }}
                          </span>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                          Criada em {{ formatDateTime(task.created_at) }}
                          <span v-if="task.completed_at" class="ml-2">
                            • Concluída em {{ formatDateTime(task.completed_at) }}
                          </span>
                        </div>
                        <div
                          v-if="task.user"
                          class="text-sm text-gray-500 dark:text-gray-500 mt-1"
                        >
                          Por: {{ task.user.name }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-if="recent_tasks.length === 0" class="text-center py-8">
                  <div
                    class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 mb-3"
                  >
                    <ClipboardDocumentListIcon
                      class="w-6 h-6 text-gray-400 dark:text-gray-500"
                    />
                  </div>
                  <p class="text-gray-500 dark:text-gray-400">Nenhuma tarefa recente</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Técnicos Geridos (apenas para gestores) -->
          <div
            v-if="
              technician.role === 'manager' &&
              manager_info?.managed_technicians?.length > 0
            "
            class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm overflow-hidden"
          >
            <!-- Cabeçalho fixo -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
              <h2
                class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
              >
                Técnicos Geridos
              </h2>
            </div>

            <!-- Conteúdo com scroll -->
            <div
              class="overflow-y-auto"
              style="max-height: calc(100vh - 500px); min-height: 200px"
            >
              <div class="p-6 pt-0">
                <div class="space-y-3">
                  <div
                    v-for="technician in manager_info.managed_technicians"
                    :key="technician.id"
                    class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                  >
                    <div class="flex items-center justify-between">
                      <div class="flex items-center">
                        <div
                          class="flex-shrink-0 h-10 w-10 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center"
                        >
                          <span class="text-blue-600 dark:text-blue-400 font-medium">
                            {{ getInitials(technician.name) }}
                          </span>
                        </div>
                        <div class="ml-4">
                          <h3
                            class="font-medium text-gray-800 dark:text-dark-text-primary"
                          >
                            {{ technician.name }}
                          </h3>
                          <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ technician.email }}
                          </p>
                        </div>
                      </div>
                      <Link
                        :href="`/technicians/${technician.id}`"
                        class="inline-flex items-center px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded text-xs font-medium transition-colors"
                      >
                        <EyeIcon class="w-4 h-4 mr-1" /> Ver
                      </Link>
                    </div>
                  </div>
                </div>
                <div
                  v-if="manager_info.managed_technicians.length === 0"
                  class="text-center py-8"
                >
                  <div
                    class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 mb-3"
                  >
                    <UserGroupIcon class="w-6 h-6 text-gray-400 dark:text-gray-500" />
                  </div>
                  <p class="text-gray-500 dark:text-gray-400">
                    Nenhum técnico gerenciado
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
          <!-- Performance -->
          <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
            <h2
              class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
            >
              {{
                technician.role === "manager"
                  ? "Performance do Departamento"
                  : "Performance"
              }}
            </h2>
            <div class="space-y-4">
              <!-- Taxa de Conclusão -->
              <div>
                <div class="flex justify-between items-center mb-1">
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{
                      technician.role === "manager"
                        ? "Taxa de Conclusão do Dept."
                        : "Taxa de Conclusão"
                    }}
                  </span>
                  <span class="text-sm font-bold text-gray-900 dark:text-white">
                    {{ stats.completion_rate }}%
                  </span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                  <div
                    :class="[
                      'h-2.5 rounded-full',
                      stats.completion_rate >= 80
                        ? 'bg-green-500'
                        : stats.completion_rate >= 60
                        ? 'bg-yellow-500'
                        : 'bg-red-500',
                    ]"
                    :style="{ width: `${Math.min(stats.completion_rate, 100)}%` }"
                  ></div>
                </div>
              </div>

              <!-- Tarefas/Reclamações por Status (apenas para técnicos) -->
              <div v-if="technician.role === 'technician' && tasks_by_status.length > 0">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Distribuição de Tarefas
                </h3>
                <div class="space-y-2">
                  <div v-for="item in tasks_by_status" :key="item.status">
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600 dark:text-gray-400">
                        {{ getStatusLabel(item.status) }}
                      </span>
                      <span class="font-medium text-gray-900 dark:text-white">
                        {{ item.count }}
                      </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                      <div
                        :class="[item.color || 'bg-gray-500', 'h-1.5 rounded-full']"
                        :style="{
                          width:
                            stats.total_assigned > 0
                              ? `${(item.count / stats.total_assigned) * 100}%`
                              : '0%',
                        }"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Técnicos no Departamento (apenas para gestores) -->
              <div v-if="technician.role === 'manager'">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Equipa no Departamento
                </h3>
                <div class="space-y-2">
                  <div>
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600 dark:text-gray-400">
                        Técnicos Ativos
                      </span>
                      <span class="font-medium text-gray-900 dark:text-white">
                        {{ stats.tecnicos_ativos || 0 }}
                      </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                      <div
                        class="h-1.5 rounded-full bg-green-500"
                        :style="{
                          width: '100%',
                        }"
                      ></div>
                    </div>
                  </div>
                  <div>
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600 dark:text-gray-400">
                        Técnicos Inativos
                      </span>
                      <span class="font-medium text-gray-900 dark:text-white">
                        {{ stats.tecnicos_inativos || 0 }}
                      </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                      <div
                        class="h-1.5 rounded-full bg-red-500"
                        :style="{
                          width: '100%',
                        }"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Performance Mensal (apenas para técnicos) -->
          <div
            v-if="technician.role === 'technician' && performance_by_month.length > 0"
            class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6"
          >
            <h2
              class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
            >
              Performance Mensal ({{ new Date().getFullYear() }})
            </h2>
            <div class="space-y-3">
              <div
                v-for="month in performance_by_month"
                :key="month.month"
                class="flex items-center"
              >
                <span class="w-16 text-sm text-gray-600 dark:text-gray-400">
                  {{ formatMonth(month.month) }}
                </span>
                <div class="flex-1 ml-2">
                  <div class="flex justify-between text-xs mb-1">
                    <span>{{ month.completed }}/{{ month.total }} tarefas</span>
                    <span class="font-medium">{{ month.rate }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                    <div
                      :class="[
                        'h-1.5 rounded-full',
                        month.rate >= 80
                          ? 'bg-green-500'
                          : month.rate >= 60
                          ? 'bg-yellow-500'
                          : 'bg-red-500',
                      ]"
                      :style="{ width: `${Math.min(month.rate, 100)}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tarefas por Prioridade (apenas para técnicos) -->
          <div
            v-if="technician.role === 'technician' && tasks_by_priority.length > 0"
            class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6"
          >
            <h2
              class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
            >
              Tarefas por Prioridade
            </h2>
            <div class="space-y-3">
              <div
                v-for="item in tasks_by_priority"
                :key="item.priority"
                class="flex items-center justify-between"
              >
                <div class="flex items-center">
                  <div
                    :class="[
                      'w-3 h-3 rounded-full mr-2',
                      getPriorityDotClass(item.priority),
                    ]"
                  ></div>
                  <span class="text-sm text-gray-700 dark:text-gray-300">
                    {{ capitalizePriority(item.priority) }}
                  </span>
                </div>
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ item.count }}
                </span>
              </div>
            </div>
          </div>

          <!-- Estatísticas Detalhadas -->
          <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
            <h2
              class="text-xl font-semibold text-gray-800 dark:text-dark-text-primary mb-4"
            >
              {{
                technician.role === "manager"
                  ? "Estatísticas do Departamento"
                  : "Estatísticas Detalhadas"
              }}
            </h2>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                  {{
                    technician.role === "manager"
                      ? "Total de Reclamações"
                      : "Total Atribuído"
                  }}
                </span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ stats.total_assigned }}
                </span>
              </div>
              <div v-if="technician.role === 'technician'" class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                  Em Progresso
                </span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ stats.in_progress }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                  {{
                    technician.role === "manager"
                      ? "Reclamações Canceladas"
                      : "Canceladas"
                  }}
                </span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ stats.cancelled }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                  {{
                    technician.role === "manager"
                      ? "Tempo Médio Resolução"
                      : "Tempo Médio Resolução"
                  }}
                </span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ stats.average_resolution_time }}h
                </span>
              </div>
              <div v-if="technician.role === 'manager'" class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                  Técnicos Ativos
                </span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ stats.tecnicos_ativos || 0 }}
                </span>
              </div>
              <div v-if="technician.role === 'manager'" class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                  Técnicos Inativos
                </span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ stats.tecnicos_inativos || 0 }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </UnifiedLayout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";
import UnifiedLayout from "@/Layouts/UnifiedLayout.vue";
import KpiCard from "@/Components/GestorReclamacoes/KpiCard.vue";
import { useAuth, usePermissions } from "@/Composables/useAuth";

import {
  ArrowLeftIcon,
  ClipboardDocumentListIcon,
  CheckCircleIcon,
  ClockIcon,
  ChartBarIcon,
  UserGroupIcon,
  EyeIcon,
} from "@heroicons/vue/24/outline";

const { role, checkRole } = useAuth();

const props = defineProps({
  technician: Object,
  stats: Object,
  recent_tasks: Array,
  performance_by_month: Array,
  tasks_by_status: Array,
  tasks_by_priority: Array,
  resolution_by_month: Array,
  manager_info: Object,
});

const canEdit = computed(() => {
  return checkRole("manager") || checkRole("admin") || checkRole("director");
});

const getInitials = (name) => {
  if (!name) return "NA";
  return name
    .split(" ")
    .map((word) => word[0])
    .join("")
    .toUpperCase()
    .slice(0, 2);
};

const getRoleLabel = (role) => {
  const labels = {
    technician: "Técnico",
    manager: "Gestor",
    director: "Director",
    admin: "Administrador",
    pca: "PCA",
    utente: "Utente",
  };

  return labels[role] || role;
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";

  if (dateString.includes("/")) return dateString;

  try {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;

    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();

    return `${day}/${month}/${year}`;
  } catch (error) {
    return dateString;
  }
};

const formatDateTime = (dateString) => {
  if (!dateString) return "N/A";

  try {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;

    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, "0");
    const minutes = String(date.getMinutes()).padStart(2, "0");

    return `${day}/${month}/${year} ${hours}:${minutes}`;
  } catch (error) {
    return dateString;
  }
};

const formatMonth = (monthNumber) => {
  const months = [
    "Jan",
    "Fev",
    "Mar",
    "Abr",
    "Mai",
    "Jun",
    "Jul",
    "Ago",
    "Set",
    "Out",
    "Nov",
    "Dez",
  ];

  const monthIndex = parseInt(monthNumber) - 1;
  return months[monthIndex] || monthNumber;
};

const capitalizePriority = (priority) => {
  if (!priority) return "Normal";

  const priorityMap = {
    alta: "Alta",
    media: "Média",
    baixa: "Baixa",
    high: "Alta",
    medium: "Média",
    low: "Baixa",
    urgent: "Urgente",
    normal: "Normal",
    urgente: "Urgente",
  };

  return (
    priorityMap[priority.toLowerCase()] ||
    priority.charAt(0).toUpperCase() + priority.slice(1).toLowerCase()
  );
};

const getStatusClass = (status) => {
  const classes = {
    completed: "bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300",
    resolved: "bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300",
    in_progress: "bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300",
    assigned: "bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300",
    submitted: "bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-300",
    pending_approval:
      "bg-purple-100 text-purple-700 dark:bg-purple-900/20 dark:text-purple-300",
    rejected: "bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300",
    cancelled: "bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300",
  };
  return (
    classes[status] || "bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-300"
  );
};

const getStatusLabel = (status) => {
  const labels = {
    completed: "Concluída",
    resolved: "Resolvida",
    in_progress: "Em Progresso",
    assigned: "Atribuída",
    submitted: "Submetida",
    pending_approval: "Aguardando Aprovação",
    rejected: "Rejeitada",
    cancelled: "Cancelada",
  };
  return labels[status] || status;
};

const getPriorityClass = (priority) => {
  const priorityKey = priority ? priority.toLowerCase() : "normal";

  const classes = {
    alta: "bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300",
    media: "bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300",
    baixa: "bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300",
    high: "bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300",
    medium: "bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300",
    low: "bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300",
    urgente: "bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300",
    urgent: "bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300",
    normal: "bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-300",
  };

  return (
    classes[priorityKey] ||
    "bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-300"
  );
};

const getPriorityDotClass = (priority) => {
  const priorityKey = priority ? priority.toLowerCase() : "normal";

  const classes = {
    alta: "bg-red-500",
    media: "bg-yellow-500",
    baixa: "bg-green-500",
    high: "bg-red-500",
    medium: "bg-yellow-500",
    low: "bg-green-500",
    urgente: "bg-red-500",
    urgent: "bg-red-500",
    normal: "bg-gray-400",
  };

  return classes[priorityKey] || "bg-gray-400";
};

const getDashboardLink = () => {
  const userRole = role.value || "manager";
  if (userRole === "director") {
    return "/director/dashboard";
  } else if (userRole === "manager") {
    return "/gestor/dashboard";
  }
  return "/dashboard";
};

const getBackLink = () => {
  const userRole = role.value || "manager";
  if (userRole === "director") {
    return "/director/employees";
  } else if (userRole === "manager") {
    return "/gestor/technicians";
  }
  return "/";
};

const getBackLabel = () => {
  const userRole = role.value || "manager";
  if (userRole === "director") {
    return "Equipa";
  } else if (userRole === "manager") {
    return "Técnicos";
  }
  return "Voltar";
};
</script>
