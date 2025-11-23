import { ref, onMounted, watch } from 'vue'

export function useTheme() {
    const isDark = ref(false)

    // Carregar tema do localStorage ou preferência do sistema
    const loadTheme = () => {
        const savedTheme = localStorage.getItem('theme')
        if (savedTheme) {
            isDark.value = savedTheme === 'dark'
        } else {
            // Verificar preferência do sistema
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
            isDark.value = prefersDark
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
        localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
    }

    // Alternar tema
    const toggleTheme = () => {
        isDark.value = !isDark.value
        applyTheme()
    }

    // Inicializar tema quando o componente é montado
    onMounted(() => {
        loadTheme()
        
        // Observar mudanças na preferência do sistema
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
        mediaQuery.addEventListener('change', (e) => {
            if (!localStorage.getItem('theme')) {
                isDark.value = e.matches
                applyTheme()
            }
        })
    })

    return {
        isDark,
        toggleTheme
    }
}