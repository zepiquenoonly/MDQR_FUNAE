Estou no ambiente Laravel + Vue + Inertia, sendo um sistema monolítico já a funcionar e
quero acrescentar a funcionalidade de poder mudar idioma para pt, pt_MZ, en, en_US, a
partir do componente ProfilePreferencesTab.vue:
<template>
  <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-sm p-6">
    <h3 class="text-2xl font-semibold text-gray-800 dark:text-dark-text-primary mb-6">
      {{ t("system_preferences") }}
    </h3>

    <form @submit.prevent="savePreferences" class="space-y-6">
      <!-- Idioma e Região -->
      <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <h4 class="text-lg font-semibold mb-4">
          {{ t("language_region") }}
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Idioma -->
          <div>
            <label class="block text-sm font-medium mb-2">
              {{ t("language") }}
            </label>

            <select
              v-model="preferences.language"
              class="w-full px-4 py-3 border rounded-lg dark:bg-dark-accent"
            >
              <option value="pt">Português (PT)</option>
              <option value="pt_MZ">Português (Moçambique)</option>
              <option value="en">English</option>
              <option value="en_US">English (US)</option>
            </select>

            <p class="mt-1 text-sm text-gray-500">
              {{ t("language_change_hint") }}
            </p>
          </div>

          <!-- Timezone -->
          <div>
            <label class="block text-sm font-medium mb-2">
              {{ t("timezone") }}
            </label>

            <select
              v-model="preferences.timezone"
              class="w-full px-4 py-3 border rounded-lg dark:bg-dark-accent"
            >
              <option value="Africa/Maputo">Africa/Maputo (UTC+2)</option>
              <option value="UTC">UTC</option>
              <option value="Europe/Lisbon">Europe/Lisbon</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Aparência -->
      <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <h4 class="text-lg font-semibold mb-4">
          {{ t("appearance") }}
        </h4>

        <div class="flex gap-4">
          <button
            v-for="option in themes"
            :key="option.value"
            type="button"
            @click="preferences.theme = option.value"
            :class="themeButtonClass(option.value)"
          >
            {{ t(option.label) }}
          </button>
        </div>
      </div>

      <!-- Guardar -->
      <div class="flex justify-end">
        <button
          type="submit"
          :disabled="saving"
          class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg"
        >
          {{ saving ? t("saving") : t("save_preferences") }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";

const { t, locale } = useI18n();
const page = usePage();

const saving = ref(false);

const preferences = ref({
  language: page.props.auth.user?.locale ?? page.props.locale,
  timezone: "Africa/Maputo",
  theme: localStorage.getItem("theme") || "light",
});

const themes = [
  { value: "light", label: "light" },
  { value: "dark", label: "dark" },
  { value: "auto", label: "auto" },
];

const themeButtonClass = (value) => [
  "px-4 py-2 border rounded-lg font-medium",
  preferences.value.theme === value
    ? "border-orange-500 text-orange-500"
    : "border-gray-300 text-gray-500",
];

// Guardar preferências
const savePreferences = () => {
  saving.value = true;

  router.patch(
    "/profile/info",
    {
      locale: preferences.value.language,
      timezone: preferences.value.timezone,
      theme: preferences.value.theme,
    },
    {
      preserveScroll: true,
      onSuccess: (page) => {
        // 🔥 Atualiza idioma global sem reload
        locale.value = page.props.locale;

        localStorage.setItem("theme", preferences.value.theme);
      },
      onFinish: () => {
        saving.value = false;
      },
    }
  );
};

// Tema
const applyTheme = (theme) => {
  const html = document.documentElement;

  if (
    theme === "dark" ||
    (theme === "auto" && window.matchMedia("(prefers-color-scheme: dark)").matches)
  ) {
    html.classList.add("dark");
  } else {
    html.classList.remove("dark");
  }
};

watch(
  () => preferences.value.theme,
  (newTheme) => {
    localStorage.setItem("theme", newTheme);
    applyTheme(newTheme);
  }
);

onMounted(() => {
  applyTheme(preferences.value.theme);
});
</script>

<style scoped>
.dark\:bg-dark-secondary {
  background-color: var(--dark-secondary);
}

.dark\:bg-dark-accent {
  background-color: var(--dark-accent);
}
</style>

E que essa acção de mudar o idioma deve se refletir no sistema como um TODO (componentes,
páginas, etc). Não quero hardcoded.
