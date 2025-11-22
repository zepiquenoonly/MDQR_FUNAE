// resources/js/Composables/useDashboardState.js
import { ref } from 'vue'

let activePanel = ref('dashboard')
let activeDropdown = ref(null)

export function useDashboardState() {
    const setActivePanel = (panel) => {
        activePanel.value = panel
        // Fechar dropdown quando um painel principal é selecionado
        if (panel !== 'mdqr') {
            activeDropdown.value = null
        }
    }

    const setActiveDropdown = (dropdownId) => {
        activeDropdown.value = dropdownId
    }

    const closeDropdown = () => {
        activeDropdown.value = null
    }

    return {
        activePanel,
        activeDropdown,
        setActivePanel,
        setActiveDropdown,
        closeDropdown
    }
}