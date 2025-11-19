Atualização de Status
=====================

Prezado(a),

O estado da sua reclamação foi atualizado.

Número de Referência: {{ $grievance->reference_number }}

Status Anterior: {{ ucfirst(str_replace('_', ' ', $oldStatus)) }}
Novo Status: {{ $grievance->status_label }}

@if($newStatus === 'under_review')
✅ A sua reclamação está a ser analisada pela nossa equipa técnica.
@elseif($newStatus === 'assigned')
✅ A sua reclamação foi atribuída a um técnico especializado.
@elseif($newStatus === 'in_progress')
✅ O processamento da sua reclamação está em andamento.
@elseif($newStatus === 'pending_approval')
✅ A resolução da sua reclamação está pendente de aprovação.
@elseif($newStatus === 'resolved')
✅ A sua reclamação foi resolvida com sucesso!
@elseif($newStatus === 'rejected')
ℹ️ A sua reclamação foi considerada não procedente após análise.
@else
O status da sua reclamação foi atualizado.
@endif

Para ver os detalhes completos, aceda a:
{{ route('grievance.track') }}?ref={{ $grievance->reference_number }}

Pode continuar a acompanhar o progresso da sua reclamação online a qualquer momento usando o número de referência acima.

Atenciosamente,
Equipa FUNAE

---
Esta é uma mensagem automática. Por favor não responda a este email.
© {{ date('Y') }} FUNAE - Fundo Nacional de Energia
