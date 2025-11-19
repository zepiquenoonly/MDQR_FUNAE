// resources/js/Composables/useDashboardState.js
import { ref, computed } from 'vue'

// Estado global do dashboard
const activePanel = ref('dashboard')
const complaintsData = ref([])

export function useDashboardState() {
  const setActivePanel = (panel) => {
    activePanel.value = panel
  }

  const setComplaintsData = (data) => {
    complaintsData.value = data
  }

  // Computed properties para dados dinâmicos
  const pendingComplaints = computed(() => {
    return complaintsData.value.filter(complaint => 
      complaint.status !== 'closed' && complaint.status !== 'resolved'
    ).length
  })

  const pendingCompletion = computed(() => {
    return complaintsData.value.filter(complaint => 
      complaint.status === 'requested'
    ).length
  })

  const highPriorityCount = computed(() => {
    return complaintsData.value.filter(complaint => 
      complaint.priority === 'high'
    ).length
  })

  return {
    activePanel,
    pendingComplaints,
    pendingCompletion,
    highPriorityCount,
    setActivePanel,
    setComplaintsData
  }
}