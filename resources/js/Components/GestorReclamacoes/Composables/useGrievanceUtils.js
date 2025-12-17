import { ref, computed } from 'vue'

export const useGrievanceUtils = complaint => {
    const formatDate = dateString => {
        if (!dateString) return 'N/D'
        return new Date(dateString).toLocaleDateString('pt-PT', {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        })
    }

    const formatFileSize = bytes => {
        if (!bytes || bytes === 0) return '0 Bytes'
        const k = 1024
        const sizes = ['Bytes', 'KB', 'MB', 'GB']
        const i = Math.floor(Math.log(bytes) / Math.log(k))
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    }

    const getLastUpdate = () => {
        if (!complaint?.activities?.length) return 'Nunca'
        const lastActivity =
            complaint.activities[complaint.activities.length - 1]
        return formatRelativeTime(lastActivity.created_at)
    }

    const formatRelativeTime = dateString => {
        const date = new Date(dateString)
        const now = new Date()
        const diffInHours = Math.floor((now - date) / (1000 * 60 * 60))

        if (diffInHours < 1) return 'Agora mesmo'
        if (diffInHours < 24) return `Há ${diffInHours}h`
        if (diffInHours < 168) return `Há ${Math.floor(diffInHours / 24)}d`
        return `Há ${Math.floor(diffInHours / 168)}sem`
    }

    const getDaysOpen = () => {
        if (!complaint?.created_at) return 0
        const created = new Date(complaint.created_at)
        const now = new Date()
        return Math.floor((now - created) / (1000 * 60 * 60 * 24))
    }

    const getStatusTimeline = () => {
        // Fluxo de estados normal (antes do "Em Análise")
        const preAnalysisFlow = [
            { status: 'submitted', label: 'Submetida' },
            { status: 'under_review', label: 'Em Análise' }
        ]

        // Fluxo de estados após "Em Análise" (estados paralelos)
        const postAnalysisFlow = [
            // Fluxo normal de progressão
            { status: 'assigned', label: 'Atribuída' },
            { status: 'in_progress', label: 'Em Andamento' },
            { status: 'pending_approval', label: 'Pendente de Aprovação' },
            { status: 'resolved', label: 'Resolvida' },
            { status: 'closed', label: 'Concluída' },
            // Estado de rejeição (pode ocorrer diretamente após "Em Análise")
            { status: 'rejected', label: 'Rejeitada' }
        ]

        const currentStatus = complaint.status
        const timelineItems = []

        // Adicionar estados antes do "Em Análise" (sempre seguem sequencialmente)
        preAnalysisFlow.forEach((state, index) => {
            const isActive = isStatusActive(state.status, currentStatus)
            const isCurrent = state.status === currentStatus

            // Encontrar atividade relacionada a este status
            const statusActivity = complaint.activities?.find(
                activity =>
                    activity.type === 'status_changed' &&
                    (activity.metadata?.new_status === state.status ||
                        activity.metadata?.status === state.status)
            )

            timelineItems.push({
                ...state,
                type: 'status',
                isActive,
                isCurrent,
                date: statusActivity
                    ? formatDate(statusActivity.created_at)
                    : isActive && state.status === 'submitted'
                    ? formatDate(complaint.created_at)
                    : null,
                description: statusActivity
                    ? statusActivity.description
                    : isCurrent
                    ? 'Estado atual da reclamação'
                    : null
            })
        })

        // Adicionar estados após "Em Análise" (podem ser paralelos/saltados)
        postAnalysisFlow.forEach(state => {
            // Verificar se este estado foi atingido na timeline real
            const isActive = isStatusActive(state.status, currentStatus)
            const isCurrent = state.status === currentStatus

            // Encontrar atividade relacionada a este status
            const statusActivity = complaint.activities?.find(
                activity =>
                    activity.type === 'status_changed' &&
                    (activity.metadata?.new_status === state.status ||
                        activity.metadata?.status === state.status)
            )

            // Se for um estado de rejeição e estiver ativo, mostrar
            if (state.status === 'rejected' && isActive) {
                timelineItems.push({
                    ...state,
                    type: 'rejection',
                    isActive,
                    isCurrent,
                    date: statusActivity
                        ? formatDate(statusActivity.created_at)
                        : null,
                    description: statusActivity
                        ? statusActivity.description
                        : 'Submissão rejeitada pelo gestor',
                    canSkipTo: true // Marcar que pode saltar para aqui
                })
            }
            // Para outros estados pós-análise, mostrar apenas se realmente ocorreram
            else if (isActive && state.status !== 'rejected') {
                timelineItems.push({
                    ...state,
                    type: 'status',
                    isActive,
                    isCurrent,
                    date: statusActivity
                        ? formatDate(statusActivity.created_at)
                        : null,
                    description: statusActivity
                        ? statusActivity.description
                        : isCurrent
                        ? 'Estado atual da reclamação'
                        : null
                })
            }
        })

        // Adicionar atividades de reatribuição de técnico
        complaint.activities?.forEach(activity => {
            if (activity.type === 'technician_assigned') {
                timelineItems.push({
                    type: 'activity',
                    status: 'technician_assigned',
                    label: 'Técnico Atribuído',
                    description: activity.description || `Técnico reatribuído`,
                    date: formatDate(activity.created_at),
                    isActive: true,
                    isCurrent: false,
                    metadata: activity.metadata
                })
            }
        })

        // Ordenar por data (do mais recente para o mais antigo)
        return timelineItems.sort((a, b) => {
            const dateA = a.date ? new Date(a.date) : new Date(0)
            const dateB = b.date ? new Date(b.date) : new Date(0)
            return dateB - dateA // Ordem decrescente (mais recente primeiro)
        })
    }

    // Função auxiliar para determinar se um estado está ativo
    const isStatusActive = (stateStatus, currentStatus) => {
        // Mapeamento de precedência dos estados
        const statusPrecedence = {
            submitted: 1,
            under_review: 2,
            assigned: 3,
            in_progress: 4,
            pending_approval: 5,
            resolved: 6,
            closed: 7,
            rejected: 99 // Estado especial que pode ocorrer a qualquer momento após "Em Análise"
        }

        const currentPrecedence = statusPrecedence[currentStatus] || 0
        const statePrecedence = statusPrecedence[stateStatus] || 0

        // Se o estado atual for "rejected", apenas mostrar "rejected" como ativo
        if (currentStatus === 'rejected') {
            return stateStatus === 'rejected'
        }

        // Para outros estados, usar a lógica normal de precedência
        return statePrecedence <= currentPrecedence
    }

    const getTimelineDotClass = status => {
        const statusMap = {
            submitted: 'bg-blue-500 border-blue-500',
            under_review: 'bg-yellow-500 border-yellow-500',
            assigned: 'bg-purple-500 border-purple-500',
            in_progress: 'bg-orange-500 border-orange-500',
            pending_approval: 'bg-indigo-500 border-indigo-500',
            resolved: 'bg-green-500 border-green-500',
            rejected: 'bg-red-500 border-red-500',
            closed: 'bg-green-600 border-green-600',
            technician_assigned: 'bg-purple-500 border-purple-500'
        }

        return statusMap[status] || 'bg-gray-500 border-gray-500'
    }

    return {
        formatDate,
        formatFileSize,
        getLastUpdate,
        getDaysOpen,
        getStatusTimeline,
        getTimelineDotClass,
        formatRelativeTime
    }
}
