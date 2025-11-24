import { ref, onMounted, watch } from 'vue'

export function useTheme() {
    const isDark = ref(false)

    // Carregar preferência salva do localStorage
    const loadTheme = () => {
        const savedTheme = localStorage.getItem('theme')
        if (savedTheme) {
            isDark.value = savedTheme === 'dark'
        } else {
            // Verificar preferência do sistema
            isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches
        }
        applyTheme()
    }

    // Aplicar tema ao documento
    const applyTheme = () => {
        if (isDark.value) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    }

    // Alternar tema
    const toggleTheme = () => {
        isDark.value = !isDark.value
        localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
        applyTheme()
    }

    // Definir tema específico
    const setTheme = (theme) => {
        isDark.value = theme === 'dark'
        localStorage.setItem('theme', theme)
        applyTheme()
    }

    // Watch para mudanças no isDark
    watch(isDark, () => {
        applyTheme()
    })

    // Carregar tema ao montar
    onMounted(() => {
        loadTheme()
    })

    return {
        isDark,
        toggleTheme,
        setTheme
    }
}
