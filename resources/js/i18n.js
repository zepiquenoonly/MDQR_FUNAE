import { createI18n } from 'vue-i18n'

// Import locale messages
import enMessages from '../locales/en.json'
import esMessages from '../locales/es.json'
import ptMessages from '../locales/pt.json'

// Define locale messages
const messages = {
  en: enMessages,
  es: esMessages,
  pt: ptMessages
}

// Get the user's preferred locale from localStorage or default to 'en'
const getLocale = () => {
  const stored = localStorage.getItem('locale')
  return stored || 'en'
}

// Create i18n instance
const i18n = createI18n({
  legacy: false,
  locale: getLocale(),
  fallbackLocale: 'en',
  messages,
  globalInjection: true,
  missingWarn: false,
  missingFallbackWarn: false
})

export default i18n
