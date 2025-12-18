{{ $grievance->type_label }} Recebida
====================

Prezado(a),

A sua {{ $grievance->type_label_lowercase }} foi recebida com sucesso pelo sistema de gestão de reclamações da FUNAE.

Número de Referência: {{ $grievance->reference_number }}

Categoria: {{ $grievance->category }}
Data de Submissão: {{ $grievance->submitted_at->format('d/m/Y H:i') }}
Estado Actual: {{ $grievance->status_label }}

Guarde este número de referência para poder acompanhar o progresso da sua reclamação.

Próximos Passos:
- A sua {{ $grievance->type_label_lowercase }} será analisada pela nossa equipa
- Receberá notificações por email sobre cada atualização
- Pode acompanhar o progresso online a qualquer momento

Se tiver alguma dúvida, pode entrar em contacto connosco através dos canais habituais.

Atenciosamente,
Equipa FUNAE

---
Esta é uma mensagem automática. Por favor não responda a este email.
© {{ date('Y') }} FUNAE - Fundo Nacional de Energia
