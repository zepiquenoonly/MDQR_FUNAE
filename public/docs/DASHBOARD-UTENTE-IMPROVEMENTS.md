# Melhorias do Dashboard de Utente - Sistema GRM FUNAE

## 📋 Resumo das Melhorias Implementadas

Este documento descreve as melhorias implementadas no Dashboard de Utente do Sistema de Gestão de Reclamações e Mecanismo de Queixas da FUNAE.

---

## ✨ Funcionalidades Implementadas

### 1. **Controller Dedicado para Dashboard de Utente**
📁 **Arquivo**: `app/Http/Controllers/UtenteDashboardController.php`

**Funcionalidades:**
- ✅ Visualização de estatísticas de reclamações do utente
- ✅ Listagem paginada de reclamações com filtros
- ✅ Visualização detalhada de reclamações individuais
- ✅ Histórico completo de submissões
- ✅ Acompanhamento em tempo real do status
- ✅ Gestão de notificações não lidas

**Endpoints:**
```php
GET  /utente/dashboard                              // Dashboard principal
GET  /utente/grievances/history                     // Histórico completo
GET  /utente/grievances/{id}                        // Detalhes da reclamação
GET  /utente/grievances/{id}/status-updates         // Atualizações em tempo real
POST /utente/notifications/read                     // Marcar notificações como lidas
```

---

### 2. **Sistema de Notificações Aprimorado**
📁 **Arquivo**: `app/Services/NotificationService.php`

**Canais de Notificação:**
- ✅ **Email**: Notificações detalhadas via email
- ✅ **SMS**: Notificações curtas via SMS (estrutura implementada)

**Tipos de Notificações:**
1. **Reclamação Criada** - Confirmação de recebimento
2. **Mudança de Status** - Atualizações do estado
3. **Atribuição** - Quando atribuída a um técnico
4. **Comentário Adicionado** - Novas atualizações públicas
5. **Resolvida** - Conclusão da reclamação
6. **Rejeitada** - Quando não procedente

**Funcionalidades Avançadas:**
- 📧 Registro de todas as notificações enviadas
- 🔄 Sistema de retry para notificações falhadas
- 📊 Tracking de leitura (opened_at, clicked_at)
- 📱 Formatação automática de números de telefone para SMS

**Configuração SMS:**
```env
# .env
SMS_ENABLED=false
SMS_PROVIDER=africastalking
AFRICASTALKING_USERNAME=your_username
AFRICASTALKING_API_KEY=your_api_key
AFRICASTALKING_FROM=FUNAE
```

---

### 3. **Componente de Reclamações Aprimorado**
📁 **Arquivo**: `resources/js/Components/UtenteDashboard/Complaints.vue`

**Funcionalidades:**
- ✅ Integração completa com backend
- ✅ Estatísticas em tempo real
- ✅ Filtros por status, prioridade e categoria
- ✅ Pesquisa por código ou descrição
- ✅ Paginação
- ✅ Visualização de notificações não lidas
- ✅ Estados de loading e erro

**Filtros Disponíveis:**
- **Status**: Submetida, Em Progresso, Resolvida, Fechada
- **Prioridade**: Baixa, Média, Alta, Urgente
- **Categoria**: Serviços Públicos, Infraestrutura, Ambiental, Social, Administração
- **Pesquisa**: Por código de referência ou descrição

---

### 4. **Modal de Detalhes da Reclamação**
📁 **Arquivo**: `resources/js/Components/UtenteDashboard/GrievanceDetails.vue`

**Funcionalidades:**
- ✅ Visualização completa de informações da reclamação
- ✅ Status atual e histórico de mudanças
- ✅ Timeline de atualizações em tempo real
- ✅ Download de anexos e documentos
- ✅ Informações de localização
- ✅ Notas de resolução (quando aplicável)
- ✅ Botão de atualização manual do status

**Informações Exibidas:**
- Número de referência
- Categoria e subcategoria
- Status atual e prioridade
- Descrição detalhada
- Localização (província, distrito)
- Técnico responsável
- Datas de submissão e resolução
- Anexos com download
- Timeline completa de atualizações

---

### 5. **Formulário de Submissão de Reclamações**
📁 **Arquivo**: `resources/js/Components/UtenteDashboard/ComplaintForm.vue`

**Funcionalidades Existentes (Mantidas):**
- ✅ Formulário multi-step com validação
- ✅ Submissão anónima ou identificada
- ✅ Upload de múltiplos anexos (documentos, imagens)
- ✅ Seleção de categoria e subcategoria
- ✅ Informações de localização
- ✅ Validação em tempo real

---

## 🎨 Interface do Usuário

### Cards de Estatísticas
```
┌─────────────────────┐  ┌─────────────────────┐
│ Total Reclamações   │  │ Resolvidas         │
│      18             │  │      10            │
└─────────────────────┘  └─────────────────────┘

┌─────────────────────┐  ┌─────────────────────┐
│ Em Progresso        │  │ Pendentes          │
│       5             │  │       3            │
└─────────────────────┘  └─────────────────────┘
```

### Tabela de Reclamações
| Referência    | Categoria        | Status       | Prioridade | Data       | Ações        |
|--------------|------------------|--------------|------------|------------|--------------|
| GRM-2025-... | Infraestrutura   | Em Progresso | Alta       | 23/11/2025 | Ver detalhes |
| GRM-2025-... | Ambiental        | Resolvida    | Média      | 20/11/2025 | Ver detalhes |

---

## 🔧 Configuração e Uso

### 1. Executar Migrações
```bash
php artisan migrate
```

### 2. Configurar Variáveis de Ambiente
```env
# Email (já configurado)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@funae.gov.mz
MAIL_FROM_NAME="FUNAE - Sistema GRM"

# SMS (opcional)
SMS_ENABLED=false
SMS_PROVIDER=africastalking
AFRICASTALKING_USERNAME=sandbox
AFRICASTALKING_API_KEY=your_api_key
AFRICASTALKING_FROM=FUNAE
```

### 3. Testar o Sistema
```bash
# Limpar cache
php artisan config:clear
php artisan cache:clear

# Compilar assets
npm run build

# Iniciar servidor
php artisan serve
```

---

## 📊 Fluxo de Uso do Utente

### 1. Acesso ao Dashboard
```
Login → Dashboard → Seção "MDQR" → Reclamações
```

### 2. Nova Reclamação
```
Botão "Nova Reclamação" → 
  Passo 1: Informações (categoria, descrição) →
  Passo 2: Localização (província, distrito) →
  Passo 3: Anexos (documentos, fotos) →
  Submeter
```

### 3. Acompanhamento
```
Dashboard → Ver Detalhes da Reclamação →
  - Visualizar status atual
  - Ver timeline de atualizações
  - Download de anexos
  - Actualizar status (botão refresh)
```

### 4. Notificações
```
Receber notificação (email/SMS) →
Ver no dashboard (banner azul) →
Clicar para ver detalhes →
Marcar como lida
```

---

## 🔐 Segurança e Permissões

### Controle de Acesso
- ✅ Utente só visualiza suas próprias reclamações
- ✅ Verificação de proprietário em todas as rotas
- ✅ Reclamações anónimas acessíveis via email de contato
- ✅ Middleware de autenticação obrigatório

### Validações
- ✅ Validação de dados no backend
- ✅ Sanitização de inputs
- ✅ Proteção CSRF
- ✅ Validação de arquivos (tipo e tamanho)

---

## 📈 Melhorias Futuras Sugeridas

### 1. Notificações Push
- Implementar notificações push no navegador
- Service Worker para notificações offline

### 2. Chat em Tempo Real
- Sistema de chat com técnico responsável
- WebSockets para comunicação instantânea

### 3. Dashboard Analítico
- Gráficos de evolução de reclamações
- Tempo médio de resolução
- Taxa de satisfação

### 4. App Mobile
- Aplicação mobile nativa
- Notificações push nativas
- Offline-first approach

### 5. Integração SMS Completa
- Implementar provedor de SMS real
- SMS de confirmação por etapa
- Opção de preferência de canal (email ou SMS)

---

## 🐛 Resolução de Problemas

### Notificações não estão sendo enviadas
```bash
# Verificar configuração de email
php artisan config:clear
php artisan queue:work  # Se usando filas

# Testar envio de email
php artisan tinker
>>> Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```

### Dados não aparecem no dashboard
```bash
# Verificar se o utente tem role correto
php artisan tinker
>>> $user = User::find(1);
>>> $user->roles;

# Limpar cache
php artisan cache:clear
php artisan view:clear
```

### Erro ao carregar detalhes
- Verificar se as rotas estão registradas
- Verificar permissões do utente
- Verificar logs: `storage/logs/laravel.log`

---

## 📝 Notas Importantes

1. **Backup**: Sempre faça backup antes de aplicar em produção
2. **Testes**: Teste todas as funcionalidades em ambiente de desenvolvimento
3. **Permissões**: Verifique as roles e permissões dos usuários
4. **Performance**: Configure cache e otimização de queries para produção
5. **Monitoramento**: Configure logs e monitoramento de erros

---

## 👥 Suporte

Para suporte técnico ou dúvidas:
- Email: suporte@funae.gov.mz
- Documentação: `/docs`
- Logs: `storage/logs/laravel.log`

---

## 📅 Histórico de Versões

### Versão 2.0.0 - 23/11/2025
- ✅ Dashboard de Utente completamente redesenhado
- ✅ Sistema de notificações via email e SMS
- ✅ Tracking em tempo real
- ✅ Modal de detalhes completo
- ✅ Filtros e pesquisa avançada
- ✅ Integração completa com backend

---

**Desenvolvido para FUNAE - Fundo Nacional de Energia**
**Sistema de Gestão de Reclamações e Mecanismo de Queixas**
