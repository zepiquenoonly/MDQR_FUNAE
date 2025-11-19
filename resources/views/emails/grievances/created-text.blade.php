Reclamação Recebida
====================

Prezado(a),

A sua reclamação foi recebida com sucesso pelo sistema de gestão de reclamações da FUNAE.

Número de Referência: {{ $grievance->reference_number }}

Categoria: {{ $grievance->category }}
Data de Submissão: {{ $grievance->submitted_at->format('d/m/Y H:i') }}
Estado Atual: {{ $grievance->status_label }}

Guarde este número de referência para poder acompanhar o progresso da sua reclamação.

Para acompanhar a sua reclamação online, aceda a:
{{ route('grievance.track') }}?ref={{ $grievance->reference_number }}

Próximos Passos:
- A sua reclamação será analisada pela nossa equipa
- Receberá notificações por email sobre cada atualização
- Pode acompanhar o progresso online a qualquer momento

Se tiver alguma dúvida, pode entrar em contacto connosco através dos canais habituais.

Atenciosamente,
Equipa FUNAE

---
Esta é uma mensagem automática. Por favor não responda a este email.
© {{ date('Y') }} FUNAE - Fundo Nacional de Energia
