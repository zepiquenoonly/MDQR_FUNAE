Nova Atualização
================

Prezado(a),

Foi adicionado um novo comentário à sua {{ $grievance->type_label_lowercase }}.

Número de Referência: {{ $grievance->reference_number }}

{{ $update->user ? $update->user->name : 'Sistema' }} • {{ $update->created_at->format('d/m/Y H:i') }}

@if($update->comment)
{{ $update->comment }}
@else
{{ $update->description }}
@endif

Para ver todas as atualizações, aceda a:
{{ route('grievance.track') }}?ref={{ $grievance->reference_number }}

Continue a acompanhar o progresso da sua {{ $grievance->type_label_lowercase }} através do link acima.

Atenciosamente,
Equipa FUNAE

---
Esta é uma mensagem automática. Por favor não responda a este email.
© {{ date('Y') }} FUNAE - Fundo Nacional de Energia
