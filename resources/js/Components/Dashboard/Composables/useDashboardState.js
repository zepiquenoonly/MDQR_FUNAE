// resources/js/Composables/useDashboardState.js
import { ref } from 'vue'

let activePanel = ref('dashboard')

export function useDashboardState() {
    const setActivePanel = (panel) => {
        activePanel.value = panel
    }

    return {
        activePanel,
        setActivePanel
    }
}