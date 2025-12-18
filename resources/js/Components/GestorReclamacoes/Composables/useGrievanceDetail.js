import { router } from '@inertiajs/vue3'
import { ref, reactive, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useAuth } from '@/Composables/useAuth'

export const useGrievanceDetail = props => {
    const page = usePage()

    // Usar o composable de auth
    const {
        role,
        isDirector,
        isManager,
        isTechnician,
        checkRole,
        user: authUser
    } = useAuth({
        user: props.user,
        debug: false
    })

    // Estados reativos
    const complaint = ref(props.complaint)
    const technicians = ref(props.technicians)

    // Estados de modais
    const showPriorityModal = ref(false)
    const showReassignModal = ref(false)
    const showCommentModal = ref(false)
    const showSendToDirectorModal = ref(false)
    const showApprovalDirectorModal = ref(false)
    const showMarkCompleteModal = ref(false)
    const showRejectModal = ref(false)
    const showValidateSubmissionModal = ref(false)
    const showReopenModal = ref(false)

    // Estados de loading
    const loading = reactive({
        priority: false,
        reassign: false,
        sendToDirector: false,
        comment: false,
        markComplete: false,
        submitValidation: false,
        markViewed: false,
        reject: false,
        reopen: false
    })

    // Toast notification
    const toast = reactive({
        show: false,
        message: '',
        type: 'success'
    })

    // ========== COMPUTED PROPERTIES SIMPLIFICADAS ==========

    const hasDirectorValidation = computed(() => {
        if (!complaint.value) return false

        console.log('=== DEBUG hasDirectorValidation ===')
        console.log(
            'Director Validation Field:',
            complaint.value.director_validation
        )
        console.log('Type:', typeof complaint.value.director_validation)

        // Se director_validation é uma string (data), significa que há validação
        if (
            typeof complaint.value.director_validation === 'string' &&
            complaint.value.director_validation.length > 0
        ) {
            console.log(
                'Director validation is date string:',
                complaint.value.director_validation
            )
            return true
        }

        // Se for um objeto com status
        if (
            complaint.value.director_validation &&
            typeof complaint.value.director_validation === 'object'
        ) {
            return !!complaint.value.director_validation.status
        }

        // Verificar no metadata
        if (complaint.value.metadata?.director_validation) {
            return !!complaint.value.metadata.director_validation.status
        }

        return false
    })

    const isEscalatedToDirector = computed(() => {
        return (
            complaint.value?.escalated === true ||
            complaint.value?.status === 'escalated' ||
            (complaint.value?.updates &&
                complaint.value.updates.some(
                    u => u.action_type === 'escalated_to_director'
                ))
        )
    })

    const directorValidationStatus = computed(() => {
        console.log('=== DEBUG directorValidationStatus ===')

        let validation = null
        let status = null

        // 1. Verificar no campo director_validation
        if (
            complaint.value.director_validation &&
            typeof complaint.value.director_validation === 'object'
        ) {
            validation = complaint.value.director_validation
            status = validation.status?.toLowerCase()
            console.log(
                'Found validation object:',
                validation,
                'Status:',
                status
            )
        }
        // 2. Verificar no metadata
        else if (complaint.value.metadata?.director_validation) {
            validation = complaint.value.metadata.director_validation
            status = validation.status?.toLowerCase()
            console.log(
                'Found validation in metadata:',
                validation,
                'Status:',
                status
            )
        }

        if (!status) {
            console.log('No validation status found')
            return null
        }

        // Converter qualquer status para 'approved' ou 'commented'
        if (
            status.includes('approve') ||
            status === 'assumed' ||
            status === 'assumed_by_director'
        ) {
            return 'approved'
        } else if (
            status.includes('comment') ||
            status.includes('revision') ||
            status.includes('return') ||
            status === 'needs_revision'
        ) {
            return 'commented'
        } else if (status === 'approved' || status === 'commented') {
            return status // Já está no formato correto
        }

        console.log('Unknown status:', status)
        return null
    })

    // ========== COMPUTED PROPERTIES PARA CONTROLE DE BOTÕES ==========

    const isPendingApproval = computed(() => {
        return complaint.value?.status === 'pending_approval'
    })

    const isRejected = computed(() => {
        const status = complaint.value?.status
        console.log('=== DEBUG isRejected ===')
        console.log('status:', status)
        console.log('is rejected?', status === 'rejected')
        return status === 'rejected'
    })

    const isResolved = computed(() => {
        const status = complaint.value?.status
        console.log('=== DEBUG isResolved ===')
        console.log('status:', status)
        console.log(
            'includes resolved?',
            status === 'resolved' || status === 'closed'
        )
        return status === 'resolved' || status === 'closed'
    })

    const isApproved = computed(() => {
        const status = complaint.value?.status
        console.log('=== DEBUG isApproved ===')
        console.log('status:', status)
        console.log('is approved?', status === 'approved')
        return status === 'approved'
    })

    const hasDirectorAssumedCase = computed(() => {
        if (!isDirector.value) return false

        const status = directorValidationStatus.value
        console.log('hasDirectorAssumedCase - status:', status)
        // Director assume quando aprova
        return status === 'approved'
    })

    const hasDirectorCommentedAndReturned = computed(() => {
        if (!isEscalatedToDirector.value) return false

        const status = directorValidationStatus.value
        console.log('hasDirectorCommentedAndReturned - status:', status)
        // Director comentou quando status é 'commented'
        return status === 'commented'
    })

    const commentDebugInfo = computed(() => {
        return {
            isResolved: isResolved.value,
            isRejected: isRejected.value,
            status: complaint.value?.status,
            isEscalated: isEscalatedToDirector.value,
            hasDirectorValidation: hasDirectorValidation.value,
            canComment: canComment.value,
            isDirector: isDirector.value,
            isManager: isManager.value
        }
    })

    const canManagerSeeActions = computed(() => {
        if (!isManager.value) return false

        console.log('=== DEBUG canManagerSeeActions ===')
        console.log('isEscalatedToDirector:', isEscalatedToDirector.value)
        console.log('hasDirectorValidation:', hasDirectorValidation.value)
        console.log('directorValidationStatus:', directorValidationStatus.value)
        console.log('hasDirectorAssumedCase:', hasDirectorAssumedCase.value)
        console.log(
            'hasDirectorCommentedAndReturned:',
            hasDirectorCommentedAndReturned.value
        )

        // Se não foi escalado, gestor tem todas ações
        if (!isEscalatedToDirector.value) {
            console.log('Caso não foi escalado - gestor tem ações')
            return true
        }
        if (!hasDirectorValidation.value) {
            console.log('Director ainda não respondeu - gestor sem ações')
            return false
        }

        // Se director aprovou (assumiu): gestor sem ações
        if (hasDirectorAssumedCase.value) {
            console.log('Director assumiu caso - gestor sem ações')
            return false
        }

        // Se director comentou (devolveu): gestor com ações
        if (hasDirectorCommentedAndReturned.value) {
            console.log('Director comentou e devolveu - gestor com ações')
            return true
        }

        console.log('Nenhuma condição atendida - gestor sem ações')
        return false
    })

    const canDirectorSeeActions = computed(() => {
        // Se não é director, retorna falso
        if (!isDirector.value) return false

        // Director só tem ações se assumiu o caso
        return hasDirectorAssumedCase.value
    })

    const hasDirectorResponded = computed(() => {
        return (
            hasDirectorValidation.value &&
            directorValidationStatus.value !== null
        )
    })

    const isWaitingDirectorIntervention = computed(() => {
        // Gestor aguardando resposta do Director
        return (
            isManager.value &&
            isEscalatedToDirector.value &&
            !hasDirectorValidation.value
        )
    })

    const isCaseAssumedByDirector = computed(() => {
        // Director assumiu o caso (respondeu "Aprovado")
        return (
            isEscalatedToDirector.value &&
            hasDirectorValidation.value &&
            directorValidationStatus.value === 'approved'
        )
    })

    const isCaseReturnedToManager = computed(() => {
        // Director devolveu o caso ao Gestor (respondeu "Comentado")
        return (
            isEscalatedToDirector.value &&
            hasDirectorValidation.value &&
            directorValidationStatus.value === 'commented'
        )
    })

    // Atualize a lógica para mostrar/ocultar botões
    const shouldShowActions = computed(() => {
        // Director: mostra ações apenas se assumiu o caso
        if (isDirector.value) {
            return isCaseAssumedByDirector.value
        }

        // Gestor:
        if (isManager.value) {
            // Se não foi escalado, mostra todas as ações
            if (!isEscalatedToDirector.value) {
                return true
            }

            // Se foi escalado e ainda não há resposta do Director
            if (isWaitingDirectorIntervention.value) {
                return false // Oculta ações enquanto aguarda
            }

            // Se Director devolveu o caso
            if (isCaseReturnedToManager.value) {
                return true // Restaura ações
            }

            // Se Director assumiu o caso
            if (isCaseAssumedByDirector.value) {
                // Apenas comentários permanecem disponíveis
                // Mas retornamos false para ocultar outros botões
                return false // Oculta outras ações, mas comentários serão forçados a aparecer
            }
        }

        // Técnico e outros: lógica padrão
        return !isResolved.value && !isRejected.value && !isApproved.value
    })

    const shouldManagerSeeActions = computed(() => {
        if (!isManager.value) return false

        return shouldShowActions.value
    })

    const canComment = computed(() => {
        console.log('=== DEBUG canComment ===')
        console.log('isResolved:', isResolved.value)
        console.log('isRejected:', isRejected.value)
        console.log('status:', complaint.value?.status)
        console.log('isEscalated:', isEscalatedToDirector.value)
        console.log('hasDirectorValidation:', hasDirectorValidation.value)
        console.log('directorValidationStatus:', directorValidationStatus.value)
        console.log('isDirector:', isDirector.value)
        console.log('isManager:', isManager.value)

        // REGRA SIMPLIFICADA: Permitir comentários SEMPRE, exceto para resolved ou rejected
        const disabled = isResolved.value || isRejected.value

        console.log('Resultado canComment:', !disabled)
        return !disabled
    })

    // ========== FUNÇÕES AUXILIARES ==========

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

    const priorityLabel = priority => {
        const map = {
            low: 'Baixa',
            medium: 'Média',
            high: 'Alta',
            critical: 'Crítica'
        }
        return map[priority] ?? priority ?? 'N/D'
    }

    const showToast = (message, type = 'success') => {
        toast.show = false

        setTimeout(() => {
            toast.message = message
            toast.type = type
            toast.show = true

            setTimeout(() => {
                if (toast.show) {
                    toast.show = false
                }
            }, 5000)
        }, 100)
    }

    // ========== HANDLERS DE MODAIS SIMPLIFICADOS ==========

    const openCommentModal = () => {
        console.log('=== DEBUG openCommentModal ===')
        console.log('canComment:', canComment.value)
        console.log('isResolved:', isResolved.value)
        console.log('isRejected:', isRejected.value)
        console.log('status:', complaint.value?.status)
        console.log('complaint object:', complaint.value)

        if (!canComment.value) {
            showToast(
                'Comentários não estão disponíveis para submissões resolvidas ou rejeitadas',
                'warning'
            )
            return
        }

        showCommentModal.value = true
    }

    const openPriorityModal = () => {
        showPriorityModal.value = true
    }

    const openReassignModal = () => {
        showReassignModal.value = true
    }

    const openSendToDirectorModal = () => {
        if (isDirector.value) {
            showToast(
                'Director não pode enviar submissões para si mesmo',
                'warning'
            )
            return
        }

        if (isEscalatedToDirector.value) {
            showToast('Esta submissão já foi enviada ao Director', 'warning')
            return
        }

        showSendToDirectorModal.value = true
    }

    const openMarkCompleteModal = () => {
        // NÃO USAR MAIS - substituído por "Validar Aprovação"
        showToast('Use "Validar Aprovação" para aprovar conclusões', 'warning')
    }

    const openApprovalDirectorModal = () => {
        if (!isDirector.value) {
            showToast('Apenas o Director pode validar solicitações', 'warning')
            return
        }

        showApprovalDirectorModal.value = true
    }

    const openReopenModal = () => {
        if (!isRejected.value) {
            showToast(
                'Apenas submissões rejeitadas podem ser reabertas',
                'warning'
            )
            return
        }

        showReopenModal.value = true
    }

    const closeModal = modalType => {
        switch (modalType) {
            case 'priority':
                showPriorityModal.value = false
                break
            case 'reassign':
                showReassignModal.value = false
                break
            case 'comment':
                showCommentModal.value = false
                break
            case 'sendToDirector':
                showSendToDirectorModal.value = false
                break
            case 'approvalDirector':
                showApprovalDirectorModal.value = false
                break
            case 'markComplete':
                showMarkCompleteModal.value = false
                break
            case 'reject':
                showRejectModal.value = false
                break
            case 'validateSubmission':
                showValidateSubmissionModal.value = false
                break
            case 'reopen':
                showReopenModal.value = false
                break
        }
    }

    const handleOpenModal = modalType => {
        switch (modalType) {
            case 'priority':
                openPriorityModal()
                break
            case 'reassign':
                openReassignModal()
                break
            case 'comment':
                openCommentModal()
                break
            case 'sendToDirector':
                openSendToDirectorModal()
                break
            case 'approvalDirector':
                openApprovalDirectorModal()
                break
            case 'markComplete':
                openMarkCompleteModal()
                break
            case 'reject':
                openRejectModal()
                break
            case 'validateSubmission':
                openValidateSubmissionModal()
                break
            case 'reopen':
                openReopenModal()
                break
        }
    }

    const openRejectModal = () => {
        showRejectModal.value = true
    }

    const openValidateSubmissionModal = () => {
        showValidateSubmissionModal.value = true
    }

    // ========== FUNÇÕES DE AÇÃO SIMPLIFICADAS ==========

    const submitDirectorValidation = async formData => {
        console.log('Nova validação do Director:', formData)
        console.log('Status do frontend:', formData.status) // 'approved' ou 'commented'

        try {
            // O backend espera 'approved' ou 'commented' diretamente
            const backendStatus = formData.status

            console.log('Status para backend:', backendStatus)

            const data = {
                status: backendStatus, // 'approved' ou 'commented'
                comment: formData.comment,
                assumed_by_director: formData.status === 'approved',
                notify_manager: true,
                notify_technician: true,
                notify_user: formData.status === 'approved'
            }

            console.log('Dados enviados:', data)

            const url = `/director/complaints/${complaint.value.id}/validate-case`

            await router.post(url, data, {
                preserveScroll: true,
                onSuccess: page => {
                    console.log('Resposta do servidor:', page.props)

                    if (page.props.validation) {
                        // Atualizar complaint com os novos dados
                        complaint.value.metadata =
                            complaint.value.metadata || {}
                        complaint.value.metadata.director_validation =
                            page.props.validation

                        // Atualizar o campo director_validation também
                        complaint.value.director_validation =
                            page.props.validation

                        // Se o director assumiu o caso, atualizar assigned_to
                        if (
                            formData.status === 'approved' &&
                            isDirector.value
                        ) {
                            complaint.value.assigned_to =
                                page.props.user?.id || authUser.value.id
                            complaint.value.status = 'under_review'
                        } else if (formData.status === 'commented') {
                            complaint.value.status = 'under_review'
                        }
                    }

                    showApprovalDirectorModal.value = false

                    let message = 'Resposta enviada com sucesso!'
                    if (formData.status === 'approved') {
                        message =
                            'Caso assumido com sucesso! Você agora é responsável por esta submissão.'
                    } else if (formData.status === 'commented') {
                        message =
                            'Parecer enviado com sucesso! O caso foi devolvido ao gestor.'
                    }

                    showToast(message, 'success')

                    // Atualizar os dados
                    setTimeout(() => {
                        refreshComplaintData()
                    }, 1000)
                },
                onError: errors => {
                    console.error('Erros:', errors)
                    const errorMessage =
                        errors?.message ||
                        errors?.error ||
                        'Erro ao enviar resposta'
                    showToast(errorMessage, 'error')
                }
            })
        } catch (error) {
            console.error('Erro catch:', error)
            showToast('Erro ao processar resposta: ' + error.message, 'error')
        }
    }

    const updateDirectorValidation = async formData => {
        console.log('Editando validação do Director:', formData)

        try {
            const validationId = formData.validationId

            if (!validationId) {
                showToast('ID da validação não encontrado', 'error')
                return
            }

            const url = `/director/complaints/${complaint.value.id}/validate/${validationId}`

            // O backend espera 'approved' ou 'commented' diretamente
            const backendStatus = formData.status // 'approved' ou 'commented'

            await router.post(
                url,
                {
                    status: backendStatus,
                    comment: formData.comment,
                    notify_manager: true,
                    notify_technician: true,
                    notify_user: false,
                    _method: 'PUT'
                },
                {
                    preserveScroll: true,
                    onSuccess: page => {
                        console.log('Sucesso na edição:', page.props)

                        if (page.props.validation) {
                            if (!complaint.value.metadata) {
                                complaint.value.metadata = {}
                            }
                            complaint.value.metadata.director_validation =
                                page.props.validation

                            // Atualizar também o campo director_validation
                            complaint.value.director_validation =
                                page.props.validation

                            // Atualizar status e assigned_to baseado na resposta
                            if (backendStatus === 'approved') {
                                complaint.value.status = 'under_review'
                                // Director assume o caso - atribuir a si mesmo
                                if (isDirector.value) {
                                    complaint.value.assigned_to =
                                        authUser.value.id
                                }
                            } else if (backendStatus === 'commented') {
                                complaint.value.status = 'under_review'
                                // Devolver ao gestor original
                                // O backend deve atualizar o assigned_to
                            }
                        }

                        showApprovalDirectorModal.value = false

                        if (page.props.flash?.success) {
                            showToast(page.props.flash.success, 'success')
                        } else {
                            showToast(
                                'Resposta atualizada com sucesso!',
                                'success'
                            )
                        }

                        setTimeout(() => {
                            refreshComplaintData()
                        }, 1000)
                    },
                    onError: errors => {
                        console.error('Erros na edição:', errors)

                        if (errors.csrf_token) {
                            showToast(
                                'Token de segurança expirado. Por favor, recarregue a página.',
                                'error'
                            )
                        } else if (
                            errors.status &&
                            errors.status.includes('needs_revision')
                        ) {
                            // O backend ainda está esperando 'needs_revision' em vez de 'commented'
                            showToast(
                                'Erro: O backend está configurado para aceitar "needs_revision" em vez de "commented".',
                                'error'
                            )
                        } else {
                            showToast(
                                errors.message || 'Erro ao atualizar resposta',
                                'error'
                            )
                        }
                    }
                }
            )
        } catch (error) {
            console.error('Erro ao atualizar validação:', error)
            showToast('Erro: ' + error.message, 'error')
        }
    }

    const rejectSubmission = async formData => {
        loading.reject = true

        try {
            let url = ''

            // URLs diretas baseadas no papel do usuário
            if (isManager.value) {
                url = `/gestor/complaints/${complaint.value.id}/reject-submission`
            } else if (isDirector.value) {
                url = `/director/complaints/${complaint.value.id}/reject-submission`
            } else {
                throw new Error(
                    'Usuário não autorizado para rejeitar submissões'
                )
            }

            const data = {
                reason: formData.reason_label || formData.reason, // Priorizar label para usuário
                reason_value: formData.reason, // Manter o valor para lógica interna
                comment: formData.comment,
                internal_comment: formData.comment, // Para o backend
                rejection_type: formData.reason, // Usar o value para compatibilidade
                notify_user: true, // Sempre notificar para rejeição
                notify_technician: true,
                status: 'rejected',
                _method: 'POST'
            }

            console.log('Enviando dados de rejeição:', data)

            // USAR ROUTER.POST PARA CONSISTÊNCIA
            await router.post(url, data, {
                preserveScroll: true,
                onSuccess: page => {
                    complaint.value.status = 'rejected'
                    complaint.value.rejection_reason =
                        formData.reason_label || formData.reason
                    complaint.value.rejection_comment = formData.comment
                    complaint.value.rejected_at = new Date().toISOString()

                    showRejectModal.value = false
                    showToast('Submissão rejeitada com sucesso!', 'success')

                    setTimeout(() => {
                        refreshComplaintData()
                    }, 1000)
                },
                onError: errors => {
                    const errorMessage =
                        errors?.message ||
                        errors?.error ||
                        'Erro ao rejeitar submissão'
                    showToast(errorMessage, 'error')
                }
            })
        } catch (error) {
            console.error('Erro na rejeição:', error)
            showToast('Erro ao processar rejeição: ' + error.message, 'error')
        } finally {
            loading.reject = false
        }
    }

    const reopenSubmission = async formData => {
        loading.reopen = true

        try {
            let url = ''

            // URLs baseadas no papel do usuário
            if (isManager.value) {
                url = `/gestor/complaints/${complaint.value.id}/reopen-submission`
            } else if (isDirector.value) {
                url = `/director/complaints/${complaint.value.id}/reopen-submission`
            } else {
                throw new Error(
                    'Usuário não autorizado para reabrir submissões'
                )
            }

            const data = {
                reason: formData.reason || 'Submissão reaberta',
                comment: formData.comment || '',
                notify_user: formData.notify_user || false,
                notify_technician: formData.notify_technician || false,
                status: 'pending', // Retorna ao estado inicial
                _method: 'POST'
            }

            console.log('POST para reabrir:', url, 'com dados:', data)

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute('content')
                },
                body: JSON.stringify(data)
            })

            const result = await response.json()

            if (response.ok) {
                complaint.value.status = 'pending'
                complaint.value.reopened_at = new Date().toISOString()
                complaint.value.reopened_by = props.user?.id
                complaint.value.reopened_reason = formData.reason

                // Limpar dados de rejeição
                complaint.value.rejection_reason = null
                complaint.value.rejection_comment = null
                complaint.value.rejected_at = null

                showReopenModal.value = false
                showToast('Submissão reaberta com sucesso!', 'success')

                setTimeout(() => {
                    refreshComplaintData()
                }, 1000)
            } else {
                throw new Error(result.message || 'Erro ao reabrir submissão')
            }
        } catch (error) {
            console.error('Erro na reabertura:', error)
            showToast('Erro ao processar reabertura: ' + error.message, 'error')
        } finally {
            loading.reopen = false
        }
    }

    const validateSubmission = async formData => {
        loading.submitValidation = true

        try {
            if (formData.status === 'rejected') {
                const url = `/gestor/${complaint.value.id}/reject-completion`

                const rejectionData = {
                    comment:
                        formData.comment || 'Conclusão rejeitada pelo gestor',
                    is_public: true,
                    notify_technician: true,
                    _method: 'PATCH'
                }

                await router.patch(url, rejectionData, {
                    preserveScroll: true,
                    onSuccess: page => {
                        complaint.value.status = 'in_progress'
                        complaint.value.pending_approval = false
                        showValidateSubmissionModal.value = false
                        showToast(
                            'Conclusão rejeitada. Submissão devolvida ao técnico.',
                            'success'
                        )
                        setTimeout(() => refreshComplaintData(), 1000)
                    },
                    onError: errors => {
                        const errorMessage =
                            errors?.message ||
                            errors?.response?.data?.message ||
                            errors?.error ||
                            'Erro ao rejeitar conclusão'
                        showToast(errorMessage, 'error')
                    }
                })
            } else if (formData.status === 'approved') {
                const url = `/gestor/${complaint.value.id}/complete`

                const approvalData = {
                    approval_comment:
                        formData.comment || 'Aprovado pelo gestor',
                    notify_user: true,
                    status: 'approved'
                }

                await router.patch(url, approvalData, {
                    preserveScroll: true,
                    onSuccess: page => {
                        complaint.value.status = 'approved'
                        complaint.value.approved_at = new Date().toISOString()
                        complaint.value.pending_approval = false
                        showValidateSubmissionModal.value = false
                        showToast('Submissão aprovada com sucesso!', 'success')
                        setTimeout(() => refreshComplaintData(), 1000)
                    },
                    onError: errors => {
                        const errorMessage =
                            errors?.message ||
                            errors?.response?.data?.message ||
                            errors?.error ||
                            'Erro ao aprovar submissão'
                        showToast(errorMessage, 'error')
                    }
                })
            }
        } catch (error) {
            showToast('Erro ao processar validação: ' + error.message, 'error')
        } finally {
            loading.submitValidation = false
        }
    }

    const markCompleteAsDirector = async () => {
        loading.markComplete = true

        try {
            const url = `/director/${complaint.value.id}/mark-complete`

            await router.post(
                url,
                {},
                {
                    preserveScroll: true,
                    onSuccess: page => {
                        complaint.value.status = 'resolved'
                        showMarkCompleteModal.value = false
                        showToast(
                            'Reclamação marcada como resolvida pelo Director!',
                            'success'
                        )
                        setTimeout(() => refreshComplaintData(), 500)
                    },
                    onError: errors => {
                        showToast(
                            errors?.message || 'Erro ao marcar como completo',
                            'error'
                        )
                    }
                }
            )
        } finally {
            loading.markComplete = false
        }
    }

    const markComplete = async () => {
        // NÃO USAR MAIS - substituído por "Validar Aprovação"
        showToast('Use "Validar Aprovação" para aprovar conclusões', 'warning')
    }

    const updatePriority = async priority => {
        loading.priority = true

        try {
            let url

            if (isDirector.value) {
                url = `/director/${complaint.value.id}/update-priority`
            } else if (isManager.value) {
                url = `/gestor/${complaint.value.id}/update-priority`
            } else {
                url = `/api/complaints/${complaint.value.id}/update-priority`
            }

            await router.patch(
                url,
                { priority: priority },
                {
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: page => {
                        complaint.value.priority = priority
                        showToast(
                            'Prioridade atualizada com sucesso!',
                            'success'
                        )
                        showPriorityModal.value = false
                        setTimeout(() => refreshComplaintData(), 500)
                    },
                    onError: errors => {
                        const errorMessage =
                            errors?.message ||
                            errors?.error ||
                            errors?.priority?.[0] ||
                            'Erro ao atualizar prioridade'
                        showToast(errorMessage, 'error')
                    }
                }
            )
        } catch (error) {
            showToast('Erro de conexão ao atualizar prioridade', 'error')
        } finally {
            loading.priority = false
        }
    }

    const reassignTechnician = async technicianId => {
        loading.reassign = true
        try {
            const basePath = isDirector.value ? '/director' : '/gestor'
            const url = `${basePath}/${complaint.value.id}/reassign`

            await router.patch(
                url,
                { technician_id: technicianId },
                {
                    preserveScroll: true,
                    onSuccess: response => {
                        complaint.value.status = 'assigned'
                        complaint.value.assigned_to = technicianId

                        const technician = technicians.value.find(
                            t => t.id === technicianId
                        )
                        if (technician) {
                            complaint.value.technician = technician
                        }

                        showReassignModal.value = false
                        showToast('Técnico reatribuído com sucesso!', 'success')

                        // RESET DO LOADING APÓS SUCESSO
                        loading.reassign = false

                        setTimeout(() => refreshComplaintData(), 500)
                    },
                    onError: errors => {
                        showToast('Erro ao atribuir técnico', 'error')
                        loading.reassign = false
                    }
                }
            )
        } catch (error) {
            showToast('Erro de conexão ao atribuir técnico', 'error')
            loading.reassign = false
        }
    }

    const sendComment = async commentData => {
        loading.comment = true
        console.log('=== DEBUG sendComment ===')
        console.log('Comment data:', commentData)
        console.log('User role:', role.value)
        console.log('Complaint ID:', complaint.value?.id)

        try {
            // Determinar a URL baseada no role
            let baseUrl = ''

            if (isDirector.value) {
                baseUrl = '/apiComments/director'
            } else if (isManager.value) {
                baseUrl = '/apiComments'
            } else if (isTechnician.value) {
                baseUrl = '/apiComments/technician'
            } else {
                throw new Error('Usuário não autorizado para comentar')
            }

            const url = `${baseUrl}/comments/${complaint.value.id}/add`
            console.log('URL de envio:', url)

            // Criar FormData
            const formData = new FormData()
            formData.append('comment', commentData.comment || '')

            // Adicionar comment_type apenas se fornecido
            if (commentData.comment_type) {
                formData.append('comment_type', commentData.comment_type)
            }

            // Processar anexos se existirem
            if (
                commentData.attachments &&
                Array.isArray(commentData.attachments)
            ) {
                commentData.attachments.forEach((file, index) => {
                    if (file instanceof File) {
                        formData.append(`attachments[${index}]`, file)
                    }
                })
            }

            // Mostrar dados do FormData no console
            console.log('FormData entries:')
            for (let pair of formData.entries()) {
                console.log(pair[0] + ':', pair[1])
            }

            // Enviar requisição
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute('content')
                },
                body: formData
            })

            console.log('Response status:', response.status)
            console.log('Response headers:', response.headers)

            const result = await response.json()
            console.log('Server response:', result)

            if (!response.ok) {
                throw new Error(
                    result.message || `HTTP error! status: ${response.status}`
                )
            }

            if (result.success) {
                showToast(
                    result.message || 'Comentário enviado com sucesso!',
                    'success'
                )

                console.log('Comment returned from server:', result.comment)

                // Atualizar o contador de comentários localmente
                if (complaint.value) {
                    complaint.value.comments_count =
                        (complaint.value.comments_count || 0) + 1
                }

                // Retornar o comentário formatado
                return {
                    id: result.comment?.id || Date.now(),
                    content: result.comment?.content || result.comment?.comment,
                    comment: result.comment?.comment || result.comment?.content,
                    type: result.comment?.type || 'internal',
                    action_type: result.comment?.action_type || 'comment',
                    created_at:
                        result.comment?.created_at || new Date().toISOString(),
                    user: result.comment?.user || {
                        id: authUser.value?.id,
                        name: authUser.value?.name,
                        role: role.value
                    },
                    attachments: result.comment?.attachments || [],
                    metadata: result.comment?.metadata || {}
                }
            } else {
                throw new Error(result.message || 'Erro ao enviar comentário')
            }
        } catch (error) {
            console.error('Error in sendComment:', error)
            showToast(
                error.message ||
                    'Erro ao enviar comentário. Verifique a conexão.',
                'error'
            )
            throw error
        } finally {
            loading.comment = false
            console.log('=== END sendComment ===')
        }
    }

    // Adicione também esta função para buscar comentários
    const fetchComments = async () => {
        console.log('=== DEBUG fetchComments ===')
        console.log('User role:', role.value)
        console.log('Is Director:', isDirector.value)
        console.log('Is Manager:', isManager.value)
        console.log('Is Technician:', isTechnician.value)

        try {
            let url = ''

            // Determinar URL baseada no role
            if (isDirector.value) {
                url = `/apiComments/director/comments/${complaint.value.id}`
            } else if (isManager.value) {
                url = `/apiComments/comments/${complaint.value.id}`
            } else if (isTechnician.value) {
                url = `/apiComments/technician/comments/${complaint.value.id}`
            } else {
                console.log('Usuário sem role válido para buscar comentários')
                return []
            }

            console.log('URL para fetch comments:', url)

            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute('content')
                }
            })

            console.log('Response status:', response.status)
            console.log('Response URL:', response.url)

            if (response.ok) {
                const result = await response.json()
                console.log('Server response:', result)

                if (result.success) {
                    console.log(
                        'Comments fetched:',
                        result.comments?.length || 0
                    )
                    return result.comments || []
                } else {
                    console.error('Error in response:', result.message)
                    return []
                }
            } else if (response.status === 404) {
                console.error(
                    'Rota não encontrada (404). Verifique se a rota GET existe para este role.'
                )
                return []
            } else {
                console.error('HTTP error:', response.status)
                return []
            }
        } catch (error) {
            console.error('Error in fetchComments:', error)
            return []
        }
    }

    const sendToDirector = async commentData => {
        showSendToDirectorModal.value = false
        loading.sendToDirector = true

        try {
            const url = `/complaints/${complaint.value.id}/send-to-director`

            await router.post(
                url,
                {
                    comment: commentData.comment,
                    reason: commentData.reason
                },
                {
                    preserveScroll: true,
                    onSuccess: page => {
                        if (page.props.flash?.updatedGrievance) {
                            const updated = page.props.flash.updatedGrievance
                            complaint.value.escalated = updated.escalated
                            complaint.value.status = 'escalated'
                            complaint.value.escalation_reason =
                                updated.escalation_reason
                            complaint.value.escalated_at = updated.escalated_at
                        }

                        showToast(
                            'Submissão enviada ao Director com sucesso!',
                            'success'
                        )
                        setTimeout(() => refreshComplaintData(), 1000)
                    },
                    onError: errors => {
                        const errorMessage =
                            errors?.error ||
                            errors?.message ||
                            'Erro ao enviar para o Director'
                        showToast(errorMessage, 'error')
                    }
                }
            )
        } catch (error) {
            showToast('Erro de conexão ao enviar para o Director', 'error')
            loading.sendToDirector = false
        }
    }

    const refreshComplaintData = () => {
        router.reload({
            only: ['complaint'],
            preserveScroll: true,
            preserveState: true,
            onSuccess: page => {
                if (page.props.complaint) {
                    complaint.value = page.props.complaint
                }
            }
        })
    }

    return {
        // Estados
        complaint,
        technicians,
        showPriorityModal,
        showReassignModal,
        showCommentModal,
        showSendToDirectorModal,
        showApprovalDirectorModal,
        showMarkCompleteModal,
        showRejectModal,
        showValidateSubmissionModal,
        showReopenModal,
        loading,
        toast,

        // Auth helpers
        isDirector: isDirector.value,
        isManager: isManager.value,
        isTechnician: isTechnician.value,
        role: role.value,

        // Computed properties de estado
        isPendingApproval,
        isRejected,
        isResolved,
        isApproved,
        isEscalatedToDirector,
        hasDirectorValidation,
        directorValidationStatus,
        isWaitingDirectorIntervention,
        isCaseAssumedByDirector,
        isCaseReturnedToManager,
        shouldShowActions,
        shouldManagerSeeActions,
        canComment,
        openCommentModal,

        // Computed properties para botões (mantidas para compatibilidade)
        canUpdatePriority: computed(() => true), // Sempre verdadeiro
        canReassignTechnician: computed(() => true), // Sempre verdadeiro
        canSendToDirector: computed(
            () => !isDirector.value && !isEscalatedToDirector.value
        ), // Apenas validação básica
        canMarkComplete: computed(() => true), // Sempre verdadeiro
        canRejectSubmission: computed(() => true), // Sempre verdadeiro
        markCompleteButtonText: computed(() => 'Validar Aprovação'), // Texto fixo
        showSendToDirectorButton: computed(
            () => !isDirector.value && !isEscalatedToDirector.value
        ),
        openCommentModal: () => {
            console.log('=== DEBUG openCommentModal (exportado) ===')
            console.log('canComment:', canComment.value)

            if (!canComment.value) {
                showToast(
                    'Comentários não estão disponíveis para submissões resolvidas ou rejeitadas',
                    'warning'
                )
                return
            }

            showCommentModal.value = true
        },
        showEscalatedIndicator: computed(
            () => isEscalatedToDirector.value && !isDirector.value
        ),
        escalationStatusText: computed(() => {
            if (!isEscalatedToDirector.value) return null
            return hasDirectorValidation.value
                ? 'Validação recebida'
                : 'Solicitação enviada'
        }),
        sendToDirectorButtonText: computed(() => {
            if (isEscalatedToDirector.value && !isDirector.value) {
                return hasDirectorValidation.value
                    ? 'Solicitação respondida'
                    : 'Solicitada a Intervenção do Director'
            }
            return 'Enviar ao Director'
        }),
        hasDirectorAssumedCase: computed(() => false), // Não utilizado
        hasDirectorCommentedAndReturned: computed(() => false), // Não utilizado
        directorResponseStatusText: computed(() => {
            if (!isEscalatedToDirector.value) return null
            return hasDirectorValidation.value
                ? 'Solicitação respondida'
                : 'Solicitada a Intervenção do Director - aguarde'
        }),

        // Métodos auxiliares
        formatDate,
        priorityLabel,
        showToast,
        // Métodos de UI
        openPriorityModal,
        openReassignModal,
        openSendToDirectorModal,
        openApprovalDirectorModal,
        openMarkCompleteModal,
        openValidateSubmissionModal,
        closeModal,
        handleOpenModal,
        openRejectModal,
        // Ações
        updatePriority,
        reassignTechnician,
        sendToDirector,
        markComplete,
        validateSubmission,
        sendComment,
        fetchComments,
        refreshComplaintData,
        rejectSubmission,
        updateDirectorValidation,
        submitDirectorValidation
    }
}
