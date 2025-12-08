import { ref, computed } from 'vue'

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
    activePanel,
    activeDropdown,
    complaintsData,
    
    // Funções
    setActivePanel,
    setActiveDropdown,
    closeDropdown,
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

