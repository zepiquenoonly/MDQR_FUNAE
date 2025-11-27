# Review: Sistema de Notificações por Email

## 📋 Visão Geral

Este documento apresenta uma revisão completa das funcionalidades de notificação por email implementadas no Sistema GRM (Gestão de Reclamações) da FUNAE. O sistema envia automaticamente emails para utentes em diferentes momentos do ciclo de vida de uma reclamação.

### Arquitetura do Sistema

O sistema de notificações utiliza:

- **NotificationService** (`app/Services/NotificationService.php`): Serviço central que gerencia o envio de todos os tipos de email
- **GrievanceObserver** (`app/Observers/GrievanceObserver.php`): Observer que detecta eventos no modelo Grievance e dispara notificações automaticamente
- **Classes Mailable** (`app/Mail/`): Classes que definem o formato e conteúdo de cada tipo de email
- **GrievanceNotification Model**: Modelo que registra todas as notificações enviadas no banco de dados

### Fluxo de Notificação

```
Evento no Sistema → GrievanceObserver → NotificationService → Mailable Class → SMTP → Email Enviado
                                                              ↓
                                                    GrievanceNotification (Registro)
```

---

## 📧 Funcionalidades Implementadas

O sistema implementa **6 tipos diferentes de notificações por email**, cada uma disparada em momentos específicos do processo de gestão de reclamações.

### 1. Email: Reclamação Criada (`GrievanceCreated`)

**Quando é enviado:**
- Automaticamente quando uma nova reclamação é criada no sistema
- Disparado pelo evento `created` do modelo `Grievance`

**Destinatário:**
- Email do usuário autenticado (se `user_id` estiver preenchido)
- Email de contato (`contact_email`) se for reclamação anônima
- Não envia se não houver email disponível

**Conteúdo do Email:**
- Número de referência da reclamação
- Categoria da reclamação
- Status inicial (geralmente "submitted")
- Mensagem de confirmação de recebimento
- Link para acompanhamento (se disponível)

**Assunto:** `Reclamação Recebida - {REFERENCE_NUMBER}`

**Dados armazenados na notificação:**
```json
{
  "reference_number": "GRM-2024-ABC12345",
  "category": "Infraestrutura",
  "status": "submitted"
}
```

---

### 2. Email: Mudança de Status (`GrievanceStatusChanged`)

**Quando é enviado:**
- Automaticamente quando o status de uma reclamação é alterado
- Disparado pelo evento `updating` do modelo `Grievance` quando `status` é modificado

**Transições de Status que disparam o email:**
- `submitted` → `under_review`
- `under_review` → `assigned`
- `assigned` → `in_progress`
- `in_progress` → `pending_approval`
- `pending_approval` → `resolved` ou `rejected`
- Qualquer outra mudança de status

**Destinatário:**
- Email do utente (autenticado ou anônimo com email)

**Conteúdo do Email:**
- Status anterior e novo status
- Mensagem personalizada baseada no novo status:
  - `under_review`: "A sua reclamação está a ser analisada pela nossa equipa técnica."
  - `assigned`: "A sua reclamação foi atribuída a um técnico especializado."
  - `in_progress`: "O processamento da sua reclamação está em andamento."
  - `pending_approval`: "A resolução da sua reclamação está pendente de aprovação."
  - `resolved`: "A sua reclamação foi resolvida com sucesso."
  - `rejected`: "A sua reclamação foi considerada não procedente após análise."

**Assunto:** `Atualização de Status - {REFERENCE_NUMBER}`

**Dados armazenados:**
```json
{
  "reference_number": "GRM-2024-ABC12345",
  "old_status": "submitted",
  "new_status": "under_review"
}
```

---

### 3. Email: Reclamação Atribuída (`GrievanceAssigned`)

**Quando é enviado:**
- Quando uma reclamação é atribuída pela primeira vez a um técnico
- Quando uma reclamação é reatribuída a outro técnico
- Disparado quando o campo `assigned_to` é modificado no modelo `Grievance`

**Destinatário:**
- Email do utente (proprietário da reclamação)

**Conteúdo do Email:**
- Nome do técnico atribuído
- Email do técnico
- Informação de que a reclamação está sendo analisada
- Número de referência

**Assunto:** `Reclamação Atribuída - {REFERENCE_NUMBER}`

**Dados armazenados:**
```json
{
  "reference_number": "GRM-2024-ABC12345",
  "assigned_to": "João Técnico",
  "assigned_to_email": "joao.tecnico@funae.co.mz"
}
```

---

### 4. Email: Comentário Adicionado (`GrievanceCommentAdded`)

**Quando é enviado:**
- Quando um comentário público é adicionado à reclamação
- **Importante:** Apenas comentários marcados como `is_public = true` disparam o email
- Comentários privados (`is_public = false`) não geram notificação

**Destinatário:**
- Email do utente

**Conteúdo do Email:**
- Conteúdo do comentário
- Nome de quem adicionou o comentário
- Data/hora do comentário
- Número de referência

**Assunto:** `Nova Atualização - {REFERENCE_NUMBER}`

**Dados armazenados:**
```json
{
  "reference_number": "GRM-2024-ABC12345",
  "comment": "Texto do comentário...",
  "commented_by": "Nome do Técnico"
}
```

**Nota:** Este email não é disparado automaticamente pelo Observer. Deve ser chamado manualmente quando um comentário público é criado.

---

### 5. Email: Reclamação Resolvida (`GrievanceResolved`)

**Quando é enviado:**
- Quando o status da reclamação muda para `resolved`
- Disparado automaticamente pelo `GrievanceObserver` quando `status = 'resolved'`

**Destinatário:**
- Email do utente

**Conteúdo do Email:**
- Confirmação de resolução
- Data de resolução
- Notas de resolução (se disponíveis)
- Nome de quem resolveu (se disponível)
- Número de referência

**Assunto:** `Reclamação Resolvida - {REFERENCE_NUMBER}`

**Dados armazenados:**
```json
{
  "reference_number": "GRM-2024-ABC12345",
  "resolved_at": "2024-11-20T10:30:00Z",
  "resolved_by": "Nome do Resolvedor"
}
```

---

### 6. Email: Reclamação Rejeitada (`GrievanceRejected`)

**Quando é enviado:**
- Quando o status da reclamação muda para `rejected`
- Disparado automaticamente pelo `GrievanceObserver` quando `status = 'rejected'`

**Destinatário:**
- Email do utente

**Conteúdo do Email:**
- Notificação de que a reclamação foi considerada não procedente
- Motivo da rejeição (do campo `resolution_notes` ou mensagem padrão)
- Número de referência

**Assunto:** `Reclamação Não Procedente - {REFERENCE_NUMBER}`

**Dados armazenados:**
```json
{
  "reference_number": "GRM-2024-ABC12345",
  "reason": "A reclamação não atende aos critérios estabelecidos."
}
```

---

## ⚙️ Configuração

### Configuração com Hostinger

Para configurar o envio de emails usando o servidor SMTP da Hostinger, edite o arquivo `.env`:

```env
# Configuração de Email - Hostinger
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@seu-dominio.com
MAIL_PASSWORD=sua-senha-de-email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=seu-email@seu-dominio.com
MAIL_FROM_NAME="Sistema GRM FUNAE"
```

**Configurações Alternativas:**

**Opção 1: TLS (Recomendado)**
```env
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

**Opção 2: SSL**
```env
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
```

### Verificação de Configuração

1. **Verificar variáveis de ambiente:**
```bash
php artisan tinker
>>> config('mail.default')
>>> config('mail.mailers.smtp.host')
>>> config('mail.from.address')
```

2. **Testar conexão SMTP:**
```bash
php artisan tinker
>>> Mail::raw('Teste de email', function($message) {
    $message->to('seu-email@example.com')
            ->subject('Teste SMTP');
});
```

3. **Verificar logs:**
```bash
tail -f storage/logs/laravel.log
```

### Configuração para Desenvolvimento

Durante o desenvolvimento, você pode usar o driver `log` para não enviar emails reais:

```env
MAIL_MAILER=log
```

Os emails serão salvos em `storage/logs/laravel.log` em formato texto.

---

## 🧪 Guia de Testes

### Método 1: Testes Automatizados (PHPUnit)

O sistema inclui testes automatizados que verificam o envio correto de todos os tipos de email.

**Executar todos os testes de email:**
```bash
php artisan test --filter=EmailNotificationTest
```

**Executar teste específico:**
```bash
php artisan test --filter=test_sends_email_when_grievance_created
```

**Cenários testados:**
- ✅ Email de reclamação criada (usuário autenticado)
- ✅ Email de reclamação criada (usuário anônimo com email)
- ✅ Não envia email quando não há email disponível
- ✅ Email de mudança de status
- ✅ Email de atribuição
- ✅ Email de comentário público
- ✅ Não envia email para comentário privado
- ✅ Email de resolução
- ✅ Email de rejeição
- ✅ Verificação de assuntos corretos
- ✅ Verificação de dados armazenados

### Método 2: Testes Manuais via Interface Web

#### Teste 1: Email de Reclamação Criada

1. **Acesse o sistema como utente**
2. **Crie uma nova reclamação:**
   - Preencha todos os campos obrigatórios
   - Submeta a reclamação
3. **Verifique:**
   - Email deve ser enviado imediatamente
   - Verifique a caixa de entrada do email cadastrado
   - Confirme que o assunto contém o número de referência

**Para reclamação anônima:**
1. Crie uma reclamação sem fazer login
2. Informe um email válido no campo de contato
3. Submeta a reclamação
4. Verifique o email informado

#### Teste 2: Email de Mudança de Status

1. **Acesse como Gestor ou Técnico**
2. **Localize uma reclamação existente**
3. **Altere o status:**
   - Exemplo: `submitted` → `under_review`
4. **Verifique:**
   - Email enviado ao utente
   - Assunto: "Atualização de Status - {REF}"
   - Conteúdo menciona a mudança de status

**Teste todas as transições:**
- `submitted` → `under_review`
- `under_review` → `assigned`
- `assigned` → `in_progress`
- `in_progress` → `pending_approval`
- `pending_approval` → `resolved`

#### Teste 3: Email de Atribuição

1. **Acesse como Gestor**
2. **Localize uma reclamação sem técnico atribuído**
3. **Atribua a um técnico:**
   - Selecione um técnico da lista
   - Salve a alteração
4. **Verifique:**
   - Email enviado ao utente
   - Email contém nome e email do técnico atribuído

#### Teste 4: Email de Comentário

1. **Acesse como Técnico ou Gestor**
2. **Localize uma reclamação**
3. **Adicione um comentário público:**
   - Marque como "Público" ou `is_public = true`
   - Adicione o comentário
4. **Verifique:**
   - Email enviado ao utente
   - Email contém o texto do comentário

**Teste comentário privado:**
1. Adicione um comentário marcado como "Privado"
2. Verifique que **NÃO** foi enviado email

#### Teste 5: Email de Resolução

1. **Acesse como Gestor ou Técnico**
2. **Localize uma reclamação em andamento**
3. **Altere o status para `resolved`:**
   - Adicione notas de resolução (opcional)
   - Salve
4. **Verifique:**
   - Email enviado ao utente
   - Assunto: "Reclamação Resolvida - {REF}"
   - Email menciona que foi resolvida

#### Teste 6: Email de Rejeição

1. **Acesse como Gestor**
2. **Localize uma reclamação**
3. **Altere o status para `rejected`:**
   - Adicione motivo da rejeição no campo `resolution_notes`
   - Salve
4. **Verifique:**
   - Email enviado ao utente
   - Assunto: "Reclamação Não Procedente - {REF}"
   - Email contém o motivo da rejeição

### Método 3: Verificação no Banco de Dados

Todas as notificações são registradas na tabela `grievance_notifications`. Você pode verificar:

```sql
-- Ver todas as notificações enviadas
SELECT * FROM grievance_notifications 
WHERE status = 'sent' 
ORDER BY created_at DESC;

-- Ver notificações por tipo
SELECT type, COUNT(*) as total 
FROM grievance_notifications 
GROUP BY type;

-- Ver notificações falhadas
SELECT * FROM grievance_notifications 
WHERE status = 'failed';

-- Ver notificações de uma reclamação específica
SELECT * FROM grievance_notifications 
WHERE grievance_id = 1;
```

### Método 4: Verificação de Logs

Os envios de email são registrados nos logs do Laravel:

```bash
# Ver logs em tempo real
tail -f storage/logs/laravel.log | grep -i "notificação\|email\|mail"

# Buscar logs de sucesso
grep "Notificação enviada com sucesso" storage/logs/laravel.log

# Buscar logs de erro
grep "Falha ao enviar notificação" storage/logs/laravel.log
```

---

## 🔍 Troubleshooting

### Problema 1: Emails não estão sendo enviados

**Sintomas:**
- Reclamações são criadas mas nenhum email é recebido
- Notificações aparecem como "pending" no banco de dados

**Soluções:**

1. **Verificar configuração SMTP:**
```bash
php artisan tinker
>>> config('mail.mailers.smtp')
```

2. **Verificar se há email disponível:**
   - Reclamações anônimas sem `contact_email` não enviam email
   - Usuários sem email cadastrado não recebem notificações

3. **Verificar logs de erro:**
```bash
grep -i "error\|exception" storage/logs/laravel.log | tail -20
```

4. **Testar conexão SMTP:**
```bash
php artisan tinker
>>> Mail::raw('Teste', function($m) { $m->to('teste@example.com')->subject('Teste'); });
```

5. **Verificar filas (se usando queues):**
```bash
php artisan queue:work
# Verificar se há jobs pendentes
php artisan queue:failed
```

### Problema 2: Emails vão para spam

**Soluções:**

1. **Configurar SPF no DNS:**
```
TXT @ "v=spf1 include:hostinger.com ~all"
```

2. **Configurar DKIM no painel Hostinger**

3. **Usar email do mesmo domínio:**
   - `MAIL_FROM_ADDRESS` deve usar o domínio configurado na Hostinger

4. **Evitar palavras suspeitas no assunto:**
   - Evite palavras como "GRÁTIS", "URGENTE", etc.

### Problema 3: Erro de autenticação SMTP

**Sintomas:**
- Erro: "Authentication failed" ou "535 5.7.8 Error: authentication failed"

**Soluções:**

1. **Verificar credenciais:**
   - Confirme `MAIL_USERNAME` e `MAIL_PASSWORD` no `.env`
   - Use o email completo como username

2. **Verificar senha:**
   - Use a senha da conta de email, não a senha do painel Hostinger
   - Se usar autenticação de dois fatores, use senha de aplicativo

3. **Verificar porta e encriptação:**
   - Porta 587 com TLS
   - Porta 465 com SSL

### Problema 4: Notificações aparecem como "failed"

**Sintomas:**
- Na tabela `grievance_notifications`, status = 'failed'

**Soluções:**

1. **Verificar mensagem de erro:**
```sql
SELECT error_message FROM grievance_notifications WHERE status = 'failed';
```

2. **Reenviar notificações falhadas:**
```bash
php artisan notifications:retry
```

3. **Verificar logs:**
```bash
grep "Falha ao enviar notificação" storage/logs/laravel.log
```

### Problema 5: Email duplicado

**Sintomas:**
- Utente recebe o mesmo email múltiplas vezes

**Causas possíveis:**

1. **Observer sendo chamado múltiplas vezes:**
   - Verificar se o Observer está registrado apenas uma vez
   - Verificar em `app/Providers/AppServiceProvider.php`

2. **Mudança de status disparando múltiplos emails:**
   - Verificar se `notifyStatusChanged` e `notifyResolved`/`notifyRejected` estão sendo chamados separadamente
   - Isso é esperado: um email de mudança de status + um email específico (resolved/rejected)

### Problema 6: Comentários não disparam email

**Causa:**
- O método `notifyCommentAdded` não é chamado automaticamente pelo Observer

**Solução:**
- Chamar manualmente após criar comentário público:
```php
$update = GrievanceUpdate::create([...]);
if ($update->is_public) {
    app(NotificationService::class)->notifyCommentAdded($grievance, $update);
}
```

---

## 📊 Monitoramento

### Métricas Importantes

1. **Taxa de sucesso de envio:**
```sql
SELECT 
    COUNT(CASE WHEN status = 'sent' THEN 1 END) * 100.0 / COUNT(*) as taxa_sucesso
FROM grievance_notifications;
```

2. **Notificações por tipo:**
```sql
SELECT type, COUNT(*) as total
FROM grievance_notifications
GROUP BY type;
```

3. **Notificações falhadas recentes:**
```sql
SELECT * FROM grievance_notifications
WHERE status = 'failed'
AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY created_at DESC;
```

### Dashboard de Notificações

Você pode criar uma interface para visualizar:
- Total de notificações enviadas
- Taxa de sucesso
- Notificações por tipo
- Notificações falhadas
- Histórico de envios

---

## 📝 Notas Importantes

1. **Reclamações sem email:** O sistema não envia email se não houver email disponível (nem `user.email` nem `contact_email`). Isso é comportamento esperado.

2. **Comentários privados:** Apenas comentários públicos disparam notificações por email.

3. **Múltiplos emails:** Quando uma reclamação muda para `resolved` ou `rejected`, são enviados 2 emails:
   - Um de mudança de status (`GrievanceStatusChanged`)
   - Um específico (`GrievanceResolved` ou `GrievanceRejected`)

4. **Registro de notificações:** Todas as tentativas de envio são registradas na tabela `grievance_notifications`, mesmo quando falham.

5. **Retry automático:** O sistema inclui funcionalidade de retry para notificações falhadas via comando `php artisan notifications:retry`.

---

## 🔗 Arquivos Relacionados

- `app/Services/NotificationService.php` - Serviço principal de notificações
- `app/Observers/GrievanceObserver.php` - Observer que dispara notificações
- `app/Mail/` - Classes Mailable para cada tipo de email
- `app/Models/GrievanceNotification.php` - Modelo de notificações
- `resources/views/emails/grievances/` - Templates de email
- `config/mail.php` - Configuração de email

---

**Última atualização:** Novembro 2024  
**Versão do documento:** 1.0

