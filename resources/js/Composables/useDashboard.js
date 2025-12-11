import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

// Estado global compartilhado do dashboard
const activePanel = ref('dashboard')
const activeDropdown = ref(null)
const complaintsData = ref([])

/**
 * Composable unificado para gerenciamento de estado do dashboard
 * Suporta diferentes tipos de dashboards (Utente, Manager, Technician, PCA)
 * 
 * @returns {Object} Objeto com estado e funções do dashboard
 */
export function useDashboard() {
  const page = usePage()
  
  /**
   * Computed property para sincronizar o painel ativo com a URL
   */
  const syncedActivePanel = computed(() => {
    // Se já temos um painel ativo definido por clique, usá-lo
    if (activePanel.value && activePanel.value !== 'dashboard') {
      return activePanel.value
    }
    
    // Caso contrário, determinar baseado na URL
    const path = page.url
    
    // Mapeamento de URLs para painéis
    const urlMappings = {
      '/track': 'tracking',
      '/profile': 'profile',
      '/projectos': 'projectos',
      '/director/complaints-overview': 'submissions',
      '/gestor/technicians': 'technicians',
      '/director/technicians': 'technicians',
      '/gestor/estatisticas': 'estatisticas',
      '/director/estatisticas': 'estatisticas',
      '/pca/usuarios': 'usuarios',
    }
    
    // Encontrar correspondência
    for (const [url, panel] of Object.entries(urlMappings)) {
      if (path.startsWith(url)) {
        return panel
      }
    }
    
    return 'dashboard'
  })

  /**
   * Define o painel ativo
   * @param {string} panel - Nome do painel (ex: 'dashboard', 'mdqr', 'projectos')
   */
  const setActivePanel = (panel) => {
    activePanel.value = panel
    // Fechar dropdown quando um painel principal é selecionado (exceto mdqr)
    if (panel !== 'mdqr') {
      activeDropdown.value = null
    }
  }

  /**
   * Define o dropdown ativo
   * @param {string|null} dropdownId - ID do dropdown ou null para fechar
   */
  const setActiveDropdown = (dropdownId) => {
    activeDropdown.value = dropdownId
  }

  /**
   * Fecha o dropdown ativo
   */
  const closeDropdown = () => {
    activeDropdown.value = null
  }

  /**
   * Sincroniza o painel ativo com a URL atual
   * Deve ser chamado quando a página é carregada
   */
  const syncWithUrl = () => {
    const synced = syncedActivePanel.value
    if (synced !== activePanel.value) {
      activePanel.value = synced
    }
  }

  /**
   * Define os dados de reclamações (para dashboards que precisam)
   * @param {Array} data - Array de reclamações
   */
  const setComplaintsData = (data) => {
    complaintsData.value = Array.isArray(data) ? data : []
  }

  // Computed properties para estatísticas (se necessário)
  const pendingComplaints = computed(() => {
    if (!Array.isArray(complaintsData.value)) return 0
    return complaintsData.value.filter(complaint => 
      complaint.status !== 'closed' && complaint.status !== 'resolved'
    ).length
  })

  const pendingCompletion = computed(() => {
    if (!Array.isArray(complaintsData.value)) return 0
    return complaintsData.value.filter(complaint => 
      complaint.status === 'requested' || complaint.status === 'pending_completion'
    ).length
  })

  const highPriorityCount = computed(() => {
    if (!Array.isArray(complaintsData.value)) return 0
    return complaintsData.value.filter(complaint => 
      complaint.priority === 'high' || complaint.priority === 'alta'
    ).length
  })

  return {
    // Estado reativo
    activePanel: syncedActivePanel,
    activeDropdown,
    complaintsData,
    
    // Funções
    setActivePanel,
    setActiveDropdown,
    closeDropdown,
    syncWithUrl,
    setComplaintsData,
    
    // Computed properties (opcionais, para compatibilidade)
    pendingComplaints,
    pendingCompletion,
    highPriorityCount
  }
}

/**
 * Alias para compatibilidade com código existente
 */
export const useDashboardState = useDashboard