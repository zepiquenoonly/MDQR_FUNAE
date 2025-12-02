<script setup>
import {
  ClockIcon,
  DocumentTextIcon,
  ArrowPathIcon,
  UserIcon,
  ArrowsRightLeftIcon,
  ChatBubbleLeftIcon,
  BoltIcon,
  PaperClipIcon,
  CheckCircleIcon,
  XCircleIcon,
  LockOpenIcon,
} from "@heroicons/vue/24/outline";

defineProps({
  updates: {
    type: Array,
    required: true,
  },
});

const getIconForActionType = (actionType) => {
  const icons = {
    created: DocumentTextIcon,
    status_changed: ArrowPathIcon,
    assigned: UserIcon,
    reassigned: ArrowsRightLeftIcon,
    comment_added: ChatBubbleLeftIcon,
    priority_changed: BoltIcon,
    attachment_added: PaperClipIcon,
    resolved: CheckCircleIcon,
    rejected: XCircleIcon,
    reopened: LockOpenIcon,
  };
  return icons[actionType] || ClockIcon;
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const diffInMs = now - date;
  const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60));

  if (diffInHours < 1) {
    const diffInMinutes = Math.floor(diffInMs / (1000 * 60));
    return `${diffInMinutes} minuto${diffInMinutes === 1 ? "" : "s"} atrás`;
  }

  if (diffInHours < 24) {
    return `${diffInHours} hora${diffInHours === 1 ? "" : "s"} atrás`;
  }

  const diffInDays = Math.floor(diffInHours / 24);
  if (diffInDays < 7) {
    return `${diffInDays} dia${diffInDays === 1 ? "" : "s"} atrás`;
  }

  return date.toLocaleDateString("pt-PT", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};
</script>

<template>
  <div class="space-y-4">
    <h3 class="text-lg font-semibold text-gray-900">Histórico de Atualizações</h3>

    <div v-if="updates.length === 0" class="text-center py-8 text-gray-500">
      <ClockIcon class="w-12 h-12 mx-auto mb-2 text-gray-400" />
      <p>Nenhuma atualização registada</p>
    </div>

    <div v-else class="relative">
      <!-- Timeline line -->
      <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200"></div>

      <!-- Updates -->
      <div class="space-y-6">
        <div v-for="(update, index) in updates" :key="update.id" class="relative pl-16">
          <!-- Timeline dot -->
          <div
            class="absolute left-3 w-6 h-6 rounded-full flex items-center justify-center text-sm"
            :class="[
              index === 0 ? 'bg-brand text-white' : 'bg-white border-2 border-gray-300',
            ]"
          >
            <component :is="getIconForActionType(update.action_type)" class="w-4 h-4" />
          </div>

          <!-- Update content -->
          <div
            class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow"
            :class="[index === 0 ? 'border-brand border-2' : '']"
          >
            <div class="flex items-start justify-between mb-2">
              <h4 class="font-semibold text-gray-900">
                {{ update.action_label }}
              </h4>
              <span class="text-xs text-gray-500">
                {{ formatDate(update.created_at) }}
              </span>
            </div>

            <p class="text-sm text-gray-700 mb-2">
              {{ update.description }}
            </p>

            <div v-if="update.comment" class="mt-3 bg-gray-50 rounded-md p-3">
              <p class="text-sm text-gray-600 italic">"{{ update.comment }}"</p>
            </div>

            <div v-if="update.user" class="mt-2 flex items-center text-xs text-gray-500">
              <svg
                class="w-4 h-4 mr-1"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                />
              </svg>
              Por: {{ update.user.name }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
