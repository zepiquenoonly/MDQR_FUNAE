Reclamação Não Procedente
=========================

Prezado(a),

Após análise detalhada, informamos que a sua reclamação foi considerada não procedente.

Número de Referência: {{ $grievance->reference_number }}

Status: Rejeitada

A sua reclamação foi analisada pela nossa equipa técnica e considerada não procedente.

@if($reason)
Justificativa:
{{ $reason }}
@endif

Para ver os detalhes completos, aceda a:
{{ route('grievance.track') }}?ref={{ $grievance->reference_number }}

O que fazer agora?
- Pode rever os detalhes da análise através do link acima
- Se discordar da decisão, pode submeter uma nova reclamação com informações adicionais
- Para esclarecimentos, pode entrar em contacto através dos nossos canais habituais

Agradecemos a sua compreensão e continuamos ao seu dispor.

Atenciosamente,
Equipa FUNAE

---
Esta é uma mensagem automática. Por favor não responda a este email.
© {{ date('Y') }} FUNAE - Fundo Nacional de Energia
