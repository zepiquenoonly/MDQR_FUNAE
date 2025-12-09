import { router } from "@inertiajs/vue3";
import { ref, reactive, computed } from "vue";

export const useGrievanceDetail = (props) => {
  // Estados reativos
  const complaint = ref(props.complaint);
  const technicians = ref(props.technicians);
  
  // Estados de modais
  const showPriorityModal = ref(false);
  const showReassignModal = ref(false);
  const showCommentModal = ref(false);
  const showSendToDirectorModal = ref(false);
  const showRejectModal = ref(false);
  
  // Estados de loading
  const loading = reactive({
    priority: false,
    reassign: false,
    sendToDirector: false,
    comment: false,
    markComplete: false,
    reject: false,
    rejectCompletion: false,
    rejectSubmission: false,
  });
  
  // Toast notification
  const toast = reactive({
    show: false,
    message: "",
    type: "success",
  });
  
  // Computed properties - ATUALIZADAS conforme seus requisitos
  const canReassignTechnician = computed(() => {
    const allowedStatuses = ["submitted", "under_review", "assigned"];
    return allowedStatuses.includes(complaint.value?.status);
  });
  
  const canUpdatePriority = computed(() => {
    const allowedStatuses = ["submitted", "under_review"];
    return allowedStatuses.includes(complaint.value?.status);
  });
  
  const canSendToDirector = computed(() => {
    const allowedStatuses = ["submitted", "under_review", "assigned"];
    return allowedStatuses.includes(complaint.value?.status);
  });
  
  const canMarkComplete = computed(() => {
    return complaint.value?.status === "pending_approval";
  });
  
  const canRejectSubmission = computed(() => {
    // Sem restrição - sempre ativo
    return true;
  });
  
  const shouldShowResolution = computed(() => {
    return ['pending_approval', 'resolved'].includes(complaint.value.status);
  });
  
  // Computed para botões de rejeição
  const canRejectCompletion = computed(() => {
    return complaint.value?.status === "pending_approval";
  });
  
  const rejectCompletionText = computed(() => {
    return complaint.value?.status === "pending_approval" 
      ? "Rejeitar Conclusão" 
      : "Rejeitar";
  });
  
  // Funções auxiliares
  const formatDate = (dateString) => {
    if (!dateString) return "N/D";
    return new Date(dateString).toLocaleDateString("pt-PT", {
      day: "2-digit",
      month: "long",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  };
  
  const priorityLabel = (priority) => {
    const map = {
      low: "Baixa",
      medium: "Média",
      high: "Alta",
    };
    return map[priority] ?? priority ?? "N/D";
  };
  
  // Função para mostrar toast
  const showToast = (message, type = "success") => {
    toast.show = false;
    
    setTimeout(() => {
      toast.message = message;
      toast.type = type;
      toast.show = true;
      
      setTimeout(() => {
        if (toast.show) {
          toast.show = false;
        }
      }, 5000);
    }, 100);
  };
  
  // Handlers de modais - COM VALIDAÇÕES
  const openPriorityModal = () => {
    if (!complaint.value) return;
    
    if (!canUpdatePriority.value) {
      showToast(
        "Só pode definir prioridade quando o status for 'Submetida' ou 'Em Análise'",
        "warning"
      );
      return;
    }
    
    showPriorityModal.value = true;
  };
  
  const openReassignModal = () => {
    if (!complaint.value) return;
    
    if (!canReassignTechnician.value) {
      showToast(
        "Só pode reatribuir técnico quando o status for 'Submetida', 'Em Análise' ou 'Atribuída'",
        "warning"
      );
      return;
    }
    
    showReassignModal.value = true;
  };
  
  const openCommentModal = () => {
    if (!complaint.value) return;
    showCommentModal.value = true;
  };
  
  const openSendToDirectorModal = () => {
    if (!complaint.value) return;
    
    if (!canSendToDirector.value) {
      showToast(
        "Só pode enviar ao director quando o status for 'Submetida', 'Em Análise' ou 'Atribuída'",
        "warning"
      );
      return;
    }
    showSendToDirectorModal.value = true;
  };
  
  const openRejectModal = () => {
    if (!complaint.value) return;
    showRejectModal.value = true;
  };
  
  const closeModal = (modalType) => {
    switch (modalType) {
      case 'priority':
        showPriorityModal.value = false;
        break;
      case 'reassign':
        showReassignModal.value = false;
        break;
      case 'comment':
        showCommentModal.value = false;
        break;
      case 'sendToDirector':
        showSendToDirectorModal.value = false;
        break;
      case 'reject':
        showRejectModal.value = false;
        break;
    }
  };
  
  const handleOpenModal = (modalType) => {
    switch (modalType) {
      case 'priority':
        openPriorityModal();
        break;
      case 'reassign':
        openReassignModal();
        break;
      case 'comment':
        openCommentModal();
        break;
      case 'sendToDirector':
        openSendToDirectorModal();
        break;
      case 'reject':
        openRejectModal();
        break;
    }
  };
  
  // Ações (as mesmas funções que você já tinha...)
  const updatePriority = async (priority) => {
    loading.priority = true;
    try {
      router.patch(
        route("complaints.update-priority", { grievance: complaint.value.id }),
        { priority: priority },
        {
          preserveScroll: true,
          onSuccess: (page) => {
            complaint.value.priority = priority;
            showPriorityModal.value = false;
            
            // Adicionar atividade à timeline
            const activity = {
              type: "priority_changed",
              action_type: "priority_changed",
              description: `Prioridade alterada para ${priorityLabel(priority)}`,
              created_at: new Date().toISOString(),
              metadata: {
                new_priority: priority,
              },
              user: {
                name: "Gestor",
                role: "Gestor",
              },
            };
            
            if (complaint.value.activities) {
              complaint.value.activities.unshift(activity);
            } else {
              complaint.value.activities = [activity];
            }
            
            showToast("Prioridade atualizada com sucesso!", "success");
          },
          onError: () => {
            showToast("Erro ao atualizar prioridade", "error");
          },
        }
      );
    } finally {
      loading.priority = false;
    }
  };
  
  const reassignTechnician = async (technicianId) => {
    loading.reassign = true;
    try {
      await router.patch(
        route("complaints.reassign", { grievance: complaint.value.id }),
        { technician_id: technicianId },
        {
          preserveScroll: true,
          preserveState: true,
          onSuccess: (page) => {
            if (page.props.flash?.updatedTechnician) {
              complaint.value.technician = page.props.flash.updatedTechnician;
            } else if (page.props?.complaint?.technician) {
              complaint.value.technician = page.props.complaint.technician;
            } else {
              const technician = technicians.value.find((t) => t.id === technicianId);
              if (technician) {
                complaint.value.technician = technician;
              }
            }
            
            complaint.value.status = "assigned";
            complaint.value.assigned_to = technicianId;
            
            const newTechnician =
              complaint.value.technician ||
              technicians.value.find((t) => t.id === technicianId);
            
            // Adicionar atividade à timeline
            const activity = {
              type: "technician_assigned",
              action_type: "technician_assigned",
              description: `Técnico reatribuído para ${
                newTechnician?.name || "Novo técnico"
              }`,
              created_at: new Date().toISOString(),
              metadata: {
                previous_technician_id: complaint.value.assigned_to,
                new_technician_id: technicianId,
                new_technician_name: newTechnician?.name,
              },
              user: {
                name: "Gestor",
                role: "Gestor",
              },
            };
            
            if (complaint.value.activities) {
              complaint.value.activities.unshift(activity);
            } else {
              complaint.value.activities = [activity];
            }
            
            showReassignModal.value = false;
            showToast(
              page.props.flash?.success || "Técnico reatribuído com sucesso!",
              "success"
            );
            
            setTimeout(() => {
              refreshComplaintData();
            }, 500);
          },
          onError: (errors) => {
            console.error("Erro detalhado ao reatribuir técnico:", errors);
            let errorMessage = "Erro ao atribuir técnico";
            
            if (typeof errors === "string") {
              errorMessage = errors;
            } else if (errors?.message) {
              errorMessage = errors.message;
            } else if (errors?.error) {
              errorMessage = errors.error;
            } else if (errors?.technician_id) {
              errorMessage = Array.isArray(errors.technician_id)
                ? errors.technician_id[0]
                : errors.technician_id;
            } else if (typeof errors === "object") {
              const firstKey = Object.keys(errors)[0];
              if (firstKey && errors[firstKey]) {
                errorMessage = Array.isArray(errors[firstKey])
                  ? errors[firstKey][0]
                  : errors[firstKey];
              }
            }
            
            showToast(errorMessage, "error");
          },
          onFinish: () => {
            loading.reassign = false;
          },
        }
      );
    } catch (error) {
      console.error("Erro de rede ao atribuir técnico:", error);
      showToast("Erro de conexão ao atribuir técnico. Verifique sua internet.", "error");
      loading.reassign = false;
    }
  };
  
  const submitComment = async (commentData) => {
    loading.comment = true;
    try {
      router.post(
        route("complaints.comment", { grievance: complaint.value.id }),
        commentData,
        {
          preserveScroll: true,
          onSuccess: (page) => {
            showCommentModal.value = false;
            
            // Adicionar atividade à timeline
            const activity = {
              type: "comment_added",
              action_type: "comment_added",
              description: `Comentário adicionado pelo gestor`,
              comment: commentData.comment,
              created_at: new Date().toISOString(),
              metadata: {
                is_public: commentData.is_public,
                comment_length: commentData.comment.length,
              },
              user: {
                name: "Gestor",
                role: "Gestor",
              },
            };
            
            if (complaint.value.activities) {
              complaint.value.activities.unshift(activity);
            } else {
              complaint.value.activities = [activity];
            }
            
            showToast(
              page.props.flash?.success || "Comentário adicionado com sucesso!",
              "success"
            );
            
            setTimeout(() => {
              refreshComplaintData();
            }, 500);
          },
          onError: (errors) => {
            showToast(errors?.message || "Erro ao adicionar comentário", "error");
          },
        }
      );
    } finally {
      loading.comment = false;
    }
  };
  
  const sendToDirector = async (commentData) => {
    showSendToDirectorModal.value = false;
    loading.sendToDirector = true;
    
    try {
      await router.post(
        route('complaints.send-to-director', { grievance: complaint.value.id }),
        {
          comment: commentData.comment,
          reason: commentData.reason,
        },
        {
          preserveScroll: true,
          preserveState: true,
          onSuccess: (page) => {
            if (page.props.flash?.updatedGrievance) {
              const updated = page.props.flash.updatedGrievance;
              
              // Atualizar os dados locais
              complaint.value.escalated = updated.escalated;
              complaint.value.priority = updated.priority;
              complaint.value.assigned_to = updated.assigned_to?.id;
              
              if (updated.assigned_to) {
                complaint.value.technician = {
                  id: updated.assigned_to.id,
                  name: updated.assigned_to.name,
                  email: updated.assigned_to.email,
                };
              }
            }
            
            // Adicionar atividade à timeline
            const activity = {
              type: 'escalated_to_director',
              action_type: 'escalated_to_director',
              description: 'Submissão enviada ao Director',
              comment: commentData.comment,
              created_at: new Date().toISOString(),
              metadata: {
                reason: commentData.reason,
                director: page.props.flash?.updatedGrievance?.assigned_to?.name || 'Director',
              },
              user: {
                name: "Gestor",
                role: "Gestor",
              },
            };
            
            if (complaint.value.activities) {
              complaint.value.activities.unshift(activity);
            } else {
              complaint.value.activities = [activity];
            }
            
            showToast(
              page.props.flash?.success || 'Submissão enviada ao Director com sucesso!',
              'success'
            );
            
            setTimeout(() => {
              refreshComplaintData();
            }, 1000);
          },
          onError: (errors) => {
            console.error('Erros ao enviar para director:', errors);
            
            let errorMessage = 'Erro ao enviar para o Director';
            if (errors?.error) {
              errorMessage = errors.error;
            } else if (errors?.message) {
              errorMessage = errors.message;
            } else if (typeof errors === 'string') {
              errorMessage = errors;
            }
            
            showToast(errorMessage, 'error');
          },
          onFinish: () => {
            loading.sendToDirector = false;
          }
        }
      );
    } catch (error) {
      console.error('Erro de rede ao enviar para director:', error);
      showToast('Erro de conexão ao enviar para o Director. Verifique sua internet.', 'error');
      loading.sendToDirector = false;
    }
  };
  
  const markComplete = async () => {
    if (complaint.value.status !== "pending_approval") {
      showToast(
        "Apenas reclamações pendentes de aprovação podem ser marcadas como completas",
        "warning"
      );
      return;
    }
    
    loading.markComplete = true;
    try {
      router.patch(
        route("complaints.complete", { grievance: complaint.value.id }),
        {},
        {
          preserveScroll: true,
          onSuccess: (page) => {
            complaint.value.status = "resolved";
            
            // Adicionar atividade à timeline
            const activity = {
              type: "status_changed",
              action_type: "status_changed",
              description: "Submissão marcada como resolvida pelo gestor",
              created_at: new Date().toISOString(),
              metadata: {
                previous_status: "pending_approval",
                new_status: "resolved",
              },
              user: {
                name: "Gestor",
                role: "Gestor",
              },
            };
            
            if (complaint.value.activities) {
              complaint.value.activities.unshift(activity);
            } else {
              complaint.value.activities = [activity];
            }
            
            showToast(
              page.props.flash?.success || "Reclamação marcada como resolvida!",
              "success"
            );
            
            setTimeout(() => {
              refreshComplaintData();
            }, 500);
          },
          onError: (errors) => {
            showToast(errors?.message || "Erro ao marcar como completo", "error");
          },
        }
      );
    } finally {
      loading.markComplete = false;
    }
  };
  
  // Novas funções para rejeição
  const rejectCompletion = async (commentData = {}) => {
    if (!canRejectCompletion.value) {
      showToast("Apenas reclamações pendentes de aprovação podem ser rejeitadas", "warning");
      return;
    }
    
    loading.rejectCompletion = true;
    try {
      router.patch(
        route("complaints.reject-completion", { grievance: complaint.value.id }),
        {
          comment: commentData.comment || "Conclusão rejeitada pelo gestor",
          is_public: commentData.is_public || false,
        },
        {
          preserveScroll: true,
          onSuccess: (page) => {
            complaint.value.status = "in_progress";
            
            // Adicionar atividade à timeline
            const activity = {
              type: "manager_rejected",
              action_type: "manager_rejected",
              description: "Conclusão rejeitada pelo gestor",
              comment: commentData.comment || "Conclusão rejeitada pelo gestor",
              created_at: new Date().toISOString(),
              metadata: {
                is_public: commentData.is_public || false,
                rejection_type: "completion_rejection",
              },
              user: {
                name: "Gestor",
                role: "Gestor",
              },
            };
            
            if (complaint.value.activities) {
              complaint.value.activities.unshift(activity);
            } else {
              complaint.value.activities = [activity];
            }
            
            showToast(
              page.props.flash?.success || "Conclusão rejeitada com sucesso!",
              "success"
            );
            
            setTimeout(() => {
              refreshComplaintData();
            }, 500);
          },
          onError: (errors) => {
            showToast(errors?.message || "Erro ao rejeitar conclusão", "error");
          },
        }
      );
    } finally {
      loading.rejectCompletion = false;
    }
  };
  
  const rejectSubmission = async (commentData = {}) => {
    loading.rejectSubmission = true;
    try {
      router.patch(
        route("complaints.reject-submission", { grievance: complaint.value.id }),
        {
          comment: commentData.comment || "Submissão rejeitada pelo gestor",
          is_public: commentData.is_public || false,
        },
        {
          preserveScroll: true,
          onSuccess: (page) => {
            complaint.value.status = "rejected";
            
            // Adicionar atividade à timeline
            const activity = {
              type: "status_changed",
              action_type: "status_changed",
              description: "Submissão rejeitada pelo gestor",
              comment: commentData.comment || "Submissão rejeitada pelo gestor",
              created_at: new Date().toISOString(),
              metadata: {
                is_public: commentData.is_public || false,
                previous_status: complaint.value.status,
                new_status: "rejected",
                rejection_type: "submission_rejection",
              },
              user: {
                name: "Gestor",
                role: "Gestor",
              },
            };
            
            if (complaint.value.activities) {
              complaint.value.activities.unshift(activity);
            } else {
              complaint.value.activities = [activity];
            }
            
            showToast(
              page.props.flash?.success || "Submissão rejeitada com sucesso!",
              "success"
            );
            
            setTimeout(() => {
              refreshComplaintData();
            }, 500);
          },
          onError: (errors) => {
            showToast(errors?.message || "Erro ao rejeitar submissão", "error");
          },
        }
      );
    } finally {
      loading.rejectSubmission = false;
    }
  };
  
  const reject = async () => {
    // Função genérica que decide qual tipo de rejeição usar
    if (complaint.value.status === "pending_approval") {
      await rejectCompletion();
    } else {
      await rejectSubmission();
    }
  };
  
  const refreshComplaintData = () => {
    router.reload({
      only: ["complaint"],
      preserveScroll: true,
      preserveState: true,
      onSuccess: (page) => {
        if (page.props.complaint) {
          complaint.value = page.props.complaint;
        }
      },
    });
  };
  
  return {
    // Estados
    complaint,
    technicians,
    showPriorityModal,
    showReassignModal,
    showCommentModal,
    showSendToDirectorModal,
    showRejectModal,
    loading,
    toast,
    
    // Computed - ATUALIZADAS
    canReassignTechnician,
    canUpdatePriority,
    canSendToDirector,
    canMarkComplete,
    canRejectSubmission,
    canRejectCompletion,
    rejectCompletionText,
    shouldShowResolution,
    
    // Métodos auxiliares
    formatDate,
    priorityLabel,
    
    // Métodos de UI
    showToast,
    openPriorityModal,
    openReassignModal,
    openCommentModal,
    openSendToDirectorModal,
    openRejectModal,
    closeModal,
    handleOpenModal,
    
    // Ações
    updatePriority,
    reassignTechnician,
    submitComment,
    sendToDirector,
    markComplete,
    reject,
    rejectCompletion,
    rejectSubmission,
    refreshComplaintData,
  };
};