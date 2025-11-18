Reclamação Atribuída
====================

Prezado(a),

A sua reclamação foi atribuída a um técnico especializado da nossa equipa.

Número de Referência: {{ $grievance->reference_number }}

Técnico Responsável: {{ $assignedUser->name }}
Email: {{ $assignedUser->email }}

O que isto significa?
- A sua reclamação está a ser analisada por um técnico especializado
- O técnico irá investigar a situação e trabalhar na resolução
- Receberá atualizações regulares sobre o progresso

Para acompanhar o progresso, aceda a:
{{ route('grievance.track') }}?ref={{ $grievance->reference_number }}

Continuamos comprometidos em resolver a sua reclamação no menor tempo possível.

Atenciosamente,
Equipa FUNAE

---
Esta é uma mensagem automática. Por favor não responda a este email.
© {{ date('Y') }} FUNAE - Fundo Nacional de Energia
