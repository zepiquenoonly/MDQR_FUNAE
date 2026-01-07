import { createI18n } from 'vue-i18n'

/**
 * Carrega automaticamente TODOS os JSON
 */
const messages = Object.fromEntries(
    Object.entries(import.meta.glob('../../lang/*.json', { eager: true })).map(
        ([key, value]) => {
            const locale = key.split('/').pop()?.replace('.json', '')
            return [locale, value.default]
        }
    )
)


/**
 * Idioma inicial
 */
const defaultLocale =
    localStorage.getItem('locale') || document.documentElement.lang || 'pt'

export const i18n = createI18n({
    legacy: false,
    locale: defaultLocale,
    fallbackLocale: 'en',
    messages
})

export const availableLocales = Object.keys(messages)
