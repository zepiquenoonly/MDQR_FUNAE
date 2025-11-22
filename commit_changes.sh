#!/bin/bash

cd /Users/edson/DEV_SETUP/PROJECTOS_CLIENTES/TECHSOLUTIONS/FUNAE

# Adicionar modelo Grievance
git add app/Models/Grievance.php
git commit -m "feat(model): implementar suporte para tipos de grievance no modelo

- Adicionado campo 'type' ao array fillable
- Constantes definidas: TYPE_GRIEVANCE, TYPE_COMPLAINT, TYPE_SUGGESTION
- Accessor type_label: retorna 'Queixa', 'Reclamação' ou 'Sugestão'
- Accessor type_label_lowercase: versão minúscula para uso em frases
- Permite uso dinâmico de terminologia correta em toda aplicação"

# Adicionar templates de email HTML
git add resources/views/emails/grievances/created.blade.php
git add resources/views/emails/grievances/status-changed.blade.php
git add resources/views/emails/grievances/assigned.blade.php
git add resources/views/emails/grievances/resolved.blade.php
git add resources/views/emails/grievances/rejected.blade.php
git add resources/views/emails/grievances/comment-added.blade.php
git commit -m "feat(emails): atualizar templates HTML para usar tipo dinâmico de grievance

- Substituído 'reclamação' hardcoded por \$grievance->type_label
- Títulos agora mostram: 'Queixa', 'Reclamação' ou 'Sugestão'
- Texto corrido usa \$grievance->type_label_lowercase
- 6 templates HTML atualizados: created, status-changed, assigned, resolved, rejected, comment-added
- Comunicação contextualizada por tipo de submissão"

# Adicionar templates de email texto
git add resources/views/emails/grievances/created-text.blade.php
git add resources/views/emails/grievances/status-changed-text.blade.php
git add resources/views/emails/grievances/assigned-text.blade.php
git add resources/views/emails/grievances/resolved-text.blade.php
git add resources/views/emails/grievances/rejected-text.blade.php
git add resources/views/emails/grievances/comment-added-text.blade.php
git commit -m "feat(emails): atualizar templates de texto para usar tipo dinâmico de grievance

- Templates de texto plain agora também usam tipo dinâmico
- Consistência entre versões HTML e texto dos emails
- 6 templates texto atualizados: created-text, status-changed-text, assigned-text, resolved-text, rejected-text, comment-added-text
- Garante experiência correta em clientes de email sem suporte HTML"

# Adicionar seeder
git add database/seeders/GrievanceSeeder.php
git commit -m "feat(seeder): adicionar tipos variados aos dados de teste

- 8 grievances atualizadas com campo 'type'
- Distribuição realista: 3 queixas, 3 reclamações, 2 sugestões
- Códigos específicos mantidos para testes em produção
- Permite testar notificações com diferentes tipos de grievance"

# Remover arquivo temporário
git add .github_issue_body.md
git commit -m "chore: adicionar documentação do issue detectado"

echo "✅ Todos os commits realizados com sucesso!"
echo "📊 Total de commits: 5"
