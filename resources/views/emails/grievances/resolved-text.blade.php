{{ $grievance->type_label }} Resolvida
====================

Prezado(a),

Temos o prazer de informar que a sua {{ $grievance->type_label_lowercase }} foi resolvida com sucesso!

Número de Referência: {{ $grievance->reference_number }}

[RESOLVIDA] Reclamação Resolvida

@if($grievance->resolved_at)
Data de Resolução: {{ $grievance->resolved_at->format('d/m/Y H:i') }}
@endif
@if($grievance->resolvedBy)
Resolvida por: {{ $grievance->resolvedBy->name }}
@endif

@if($grievance->resolution_notes)
Detalhes da Resolução:
{{ $grievance->resolution_notes }}
@endif

Agradecemos a sua colaboração!

Se tiver alguma dúvida sobre a resolução ou se o problema persistir,
não hesite em entrar em contacto connosco.

Obrigado por utilizar o nosso sistema de reclamações.

Atenciosamente,
Equipa FUNAE

---
Esta é uma mensagem automática. Por favor não responda a este email.
© {{ date('Y') }} FUNAE - Fundo Nacional de Energia
