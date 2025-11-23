# 📊 Milestones e Issues - Sistema GRM FUNAE

## Visão Geral do Projeto

**Total de Milestones**: 3  
**Total de Issues**: 17  
**Repositório**: https://github.com/TECHSOLUTIONS-PROJECTS/www.mdqr.co.mz  
**Stack Tecnológica**: Laravel + Inertia.js + Vue.js 3

---

## 📅 Milestone 1: Semana 1 - Base & Utente
**Objetivo**: Submissão de Queixas totalmente funcional. Estrutura de autenticação e base de dados prontas.  
**Data Limite**: 19 de Janeiro, 2025  
**Issues**: 5  
**URL**: https://github.com/TECHSOLUTIONS-PROJECTS/www.mdqr.co.mz/milestone/1

### Issues da Semana 1

#### 🔹 Issue #1: P1 - Setup de Projeto e Auth/ACL
**Atribuído**: Programador 1  
**Label**: `enhancement`  

**Descrição**:
Configurar o projeto Laravel, instalar dependências. Implementar o sistema de autenticação (Login/Registo) para Utentes e a gestão de Atores internos (Gestor, Técnico, PCA). Utilizar o pacote Spatie Laravel Permission para o controlo de acesso baseado em funções.

**Critérios de Aceitação**:
- [ ] O projeto está funcional com ambiente de desenvolvimento
- [ ] Autenticação de Utentes (registo/login) concluída
- [ ] Criação de perfis de Atores internos e atribuição de permissões básicas concluída

**Tags**: `laravel` `authentication` `spatie-permission` `setup`

---

#### 🔹 Issue #2: P1 - Criação de Models e Migrações (BD)
**Atribuído**: Programador 1  
**Label**: `enhancement`  

**Descrição**:
Criar os Models (ex: Grievance, User, Attachment) e as respetivas migrações para a Base de Dados. Definir os relacionamentos chaves (one-to-many) e os campos essenciais, em conformidade com a Lei de Proteção de Dados (anonimato, campos obrigatórios).

**Critérios de Aceitação**:
- [ ] Todos os Models críticos (Queixa, Utente) e as suas migrações estão criados
- [ ] A BD está estruturada para registar queixas identificadas e anónimas
- [ ] Relacionamentos entre User e Grievance definidos

**Tags**: `laravel` `database` `models` `migrations`

---

#### 🔹 Issue #3: P2 - Formulário de Submissão de Queixa (Utente)
**Atribuído**: Programador 2  
**Label**: `enhancement`  

**Descrição**:
Desenvolver a interface (Vue Component com Inertia.js) para o Utente preencher e submeter a reclamação. O formulário deve suportar submissão anónima e identificada.

**Critérios de Aceitação**:
- [ ] O formulário front-end está completo e validado (requerimentos obrigatórios)
- [ ] Submissão de dados via Inertia.js para o backend Laravel é bem-sucedida
- [ ] A submissão cria um registo na BD com o status inicial 'Submetida'

**Tags**: `inertia` `vue` `frontend` `forms` `validation`

---

#### 🔹 Issue #4: P2 - Upload e Armazenamento de Anexos
**Atribuído**: Programador 2  
**Label**: `enhancement`  

**Descrição**:
Integrar a funcionalidade de upload de ficheiros ao formulário de submissão do Utente. O armazenamento deve ser seguro (ex: Storage/S3) e o caminho do ficheiro registado na BD.

**Critérios de Aceitação**:
- [ ] O Utente pode selecionar e anexar ficheiros
- [ ] Os ficheiros são armazenados de forma segura e o seu registo está associado à queixa na BD
- [ ] A validação de tipo e tamanho de ficheiro está implementada

**Tags**: `storage` `file-upload` `validation` `security`

---

#### 🔹 Issue #5: P4 - Configuração do Ambiente e Pipeline
**Atribuído**: Programador 4  
**Label**: `documentation`  

**Descrição**:
Finalizar a configuração do ambiente de desenvolvimento (ex: Docker, se aplicável) e a documentação para a equipa. Configurar o sistema de gestão de código (Git/GitHub).

**Critérios de Aceitação**:
- [ ] Instruções de setup do ambiente estão documentadas e funcionais para todos os programadores
- [ ] O código está versionado e a estrutura de branches no GitHub está definida (ex: main, develop, feature/*)

**Tags**: `devops` `documentation` `git` `setup`

---

## 📅 Milestone 2: Semana 2 - Operacional & Fluxo
**Objetivo**: Ciclo de Resolução Mínimo Funcional. Técnico pode receber, atualizar e o Utente notificado via Email.  
**Data Limite**: 26 de Janeiro, 2025  
**Issues**: 5  
**URL**: https://github.com/TECHSOLUTIONS-PROJECTS/www.mdqr.co.mz/milestone/2

### Issues da Semana 2

#### 🔹 Issue #6: P3 - Lógica de Alocação Simples (GRM Logic)
**Atribuído**: Programador 3  
**Label**: `enhancement`  

**Descrição**:
Desenvolver a lógica de negócio que, após a submissão, classifica a queixa e a aloca automaticamente a um Técnico ou Gestor de Reclamações Adjunto (com base em regras simples de área/tipo).

**Critérios de Aceitação**:
- [ ] Após a submissão (Step 3 do Fluxo), a queixa é automaticamente atribuída a um Técnico/Gestor Adjunto
- [ ] A atribuição é persistida na BD e o Técnico notificado internamente

**Tags**: `business-logic` `allocation` `automation`

---

#### 🔹 Issue #7: P2 - Painel Básico do Técnico (Inertia + Vue)
**Atribuído**: Programador 2  
**Label**: `enhancement`  

**Descrição**:
Criar a interface de gestão (Vue Component com Inertia.js) para o Técnico. Deve exibir apenas a lista de reclamações que lhe foram atribuídas.

**Critérios de Aceitação**:
- [ ] O Técnico pode aceder ao seu painel e visualizar as queixas que lhe foram alocadas
- [ ] A lista exibe informações cruciais (ID, Título, Status)

**Tags**: `inertia` `vue` `frontend` `dashboard` `technician`

---

#### 🔹 Issue #8: P3 - Funcionalidade: Inserir Atualizações e Status 'Em Andamento'
**Atribuído**: Programador 3  
**Label**: `enhancement`  

**Descrição**:
No painel do Técnico, implementar a funcionalidade para adicionar um log (update) ou comentário sobre o andamento do caso. A primeira atualização deve alterar o status da queixa para 'Em Andamento'.

**Critérios de Aceitação**:
- [ ] O Técnico pode submeter uma atualização/comentário à queixa
- [ ] O status da queixa é corretamente alterado para 'Em Andamento' na BD
- [ ] O log de atualizações é persistido

**Tags**: `business-logic` `status-management` `updates`

---

#### 🔹 Issue #9: P4 - Configuração de Queues e Serviço de Email
**Atribuído**: Programador 4  
**Label**: `enhancement`  

**Descrição**:
Configurar o serviço de Queues (Filas) do Laravel para processamento assíncrono de tarefas (ex: envio de emails). Configurar e testar a integração com o provedor de Email.

**Critérios de Aceitação**:
- [ ] O sistema de Queues está ativo e a processar tarefas
- [ ] O envio de emails de teste (e.g., via Mail::to()->send()) está funcional

**Tags**: `laravel` `queues` `email` `infrastructure`

---

#### 🔹 Issue #10: P4 - Notificação Automática (Submissão e Andamento)
**Atribuído**: Programador 4  
**Label**: `enhancement`  

**Descrição**:
Implementar o envio de notificações por Email (via Queues) para o Utente em duas etapas: Confirmação de Submissão (Step 2) e Alteração de Status para 'Em Andamento' (após Step 5).

**Critérios de Aceitação**:
- [ ] O Utente recebe email de confirmação imediatamente após submeter a queixa
- [ ] O Utente recebe email quando o status muda para 'Em Andamento'
- [ ] As notificações incluem o número de acompanhamento da queixa

**Tags**: `notifications` `email` `automation`

---

## 📅 Milestone 3: Semana 3 - Conclusão & MVP
**Objetivo**: Finalizar o ciclo crítico (Conclusão do Gestor), implementar o painel do PCA e preparar o deploy final.  
**Data Limite**: 2 de Fevereiro, 2025  
**Issues**: 7  
**URL**: https://github.com/TECHSOLUTIONS-PROJECTS/www.mdqr.co.mz/milestone/3

### Issues da Semana 3

#### 🔹 Issue #11: P3 - Funcionalidade: Solicitar Conclusão (Técnico)
**Atribuído**: Programador 3  
**Label**: `enhancement`  

**Descrição**:
No painel do Técnico, adicionar um botão/funcionalidade para Solicitar a Conclusão do processo ao Gestor de Reclamações. Isso deve mudar o status interno da queixa (ex: 'Aguardando Conclusão do Gestor').

**Critérios de Aceitação**:
- [ ] O Técnico pode disparar o pedido de conclusão
- [ ] O status da queixa é atualizado e o Gestor de Reclamações é notificado (internamente)

**Tags**: `business-logic` `workflow` `completion`

---

#### 🔹 Issue #12: P3 - Painel e Funcionalidade: Conclusão Final (Gestor)
**Atribuído**: Programador 3  
**Label**: `enhancement`  

**Descrição**:
Criar a interface e a lógica para o Gestor de Reclamações receber e visualizar os pedidos de conclusão. Implementar a funcionalidade para Marcar como Concluído (Step 7), alterando o status final da queixa.

**Critérios de Aceitação**:
- [ ] O Gestor pode visualizar as queixas com o status 'Aguardando Conclusão do Gestor'
- [ ] O Gestor pode Concluir o processo, e o status é definido como 'Concluída' na BD

**Tags**: `business-logic` `manager` `workflow` `completion`

---

#### 🔹 Issue #13: P2 - Painel do Utente: Acompanhamento de Estado e Histórico
**Atribuído**: Programador 2  
**Label**: `enhancement`  

**Descrição**:
Desenvolver a interface (Vue Component com Inertia.js) para o Utente aceder, através do seu número de acompanhamento, ao status em tempo real da sua queixa e visualizar o histórico de submissões.

**Critérios de Aceitação**:
- [ ] O Utente pode pesquisar e ver o status atual da sua queixa
- [ ] O Utente pode ver o histórico de atualizações inseridas pelo Técnico/Sistema

**Tags**: `inertia` `vue` `frontend` `tracking` `user-dashboard`

---

#### 🔹 Issue #14: P4 - Notificação Automática: Conclusão de Resolução
**Atribuído**: Programador 4  
**Label**: `enhancement`  

**Descrição**:
Implementar o envio da notificação por Email/SMS (se aplicável) para o Utente, informando que a sua queixa foi Concluída (resolvida). (Step 8 do Fluxo).

**Critérios de Aceitação**:
- [ ] O Utente recebe email/SMS quando o status da queixa muda para 'Concluída'
- [ ] A notificação é enviada automaticamente após a ação do Gestor

**Tags**: `notifications` `email` `sms` `completion`

---

#### 🔹 Issue #15: P4 - Relatório Estatístico Básico (PCA)
**Atribuído**: Programador 4  
**Label**: `enhancement`  

**Descrição**:
Implementar a consulta e a visualização simples (Vue Component com Inertia.js) para o PCA, focada no indicador 'Queixas Abertas vs. Fechadas'.

**Critérios de Aceitação**:
- [ ] O PCA pode aceder ao painel básico
- [ ] O painel exibe o contador e/ou gráfico simples dos indicadores Abertas/Fechadas

**Tags**: `inertia` `vue` `reports` `statistics` `dashboard`

---

#### 🔹 Issue #16: P1, P2, P3 - Refinamento e Validações Finais
**Atribuído**: Equipa (P1, P2, P3)  
**Label**: `enhancement`  

**Descrição**:
Revisão de código, testes unitários básicos e testes de integração do ciclo completo (Utente -> Técnico -> Gestor). Validação final da segurança, UX/UI e conformidade com os requisitos.

**Critérios de Aceitação**:
- [ ] O ciclo completo (Submissão à Conclusão) funciona sem erros
- [ ] As validações de front-end e back-end estão implementadas
- [ ] O código foi revisto e corrigido

**Tags**: `testing` `code-review` `validation` `security`

---

#### 🔹 Issue #17: P4 - Preparação e Execução do Deploy (Produção)
**Atribuído**: Programador 4  
**Label**: `enhancement`  

**Descrição**:
Preparar o ambiente de produção (Deployment) e realizar a transferência do código e BD para o servidor final para testes de aceitação.

**Critérios de Aceitação**:
- [ ] A aplicação está acessível no ambiente de produção/staging
- [ ] A BD em produção está configurada e ligada à aplicação
- [ ] Testes de fumo em produção são bem-sucedidos

**Tags**: `deployment` `devops` `production`

---

## 📋 Resumo de Atribuições

### Programador 1 (P1)
**Total de Issues**: 2 (+ 1 em equipa)

- Issue #1: Setup de Projeto e Auth/ACL
- Issue #2: Criação de Models e Migrações (BD)
- Issue #16: Refinamento e Validações Finais (Equipa)

### Programador 2 (P2)
**Total de Issues**: 4 (+ 1 em equipa)

- Issue #3: Formulário de Submissão de Queixa (Utente)
- Issue #4: Upload e Armazenamento de Anexos
- Issue #7: Painel Básico do Técnico (Livewire)
- Issue #13: Painel do Utente: Acompanhamento de Estado e Histórico
- Issue #16: Refinamento e Validações Finais (Equipa)

### Programador 3 (P3)
**Total de Issues**: 5 (+ 1 em equipa)

- Issue #6: Lógica de Alocação Simples (GRM Logic)
- Issue #8: Funcionalidade: Inserir Atualizações e Status 'Em Andamento'
- Issue #11: Funcionalidade: Solicitar Conclusão (Técnico)
- Issue #12: Painel e Funcionalidade: Conclusão Final (Gestor)
- Issue #16: Refinamento e Validações Finais (Equipa)

### Programador 4 (P4)
**Total de Issues**: 5

- Issue #5: Configuração do Ambiente e Pipeline
- Issue #9: Configuração de Queues e Serviço de Email
- Issue #10: Notificação Automática (Submissão e Andamento)
- Issue #14: Notificação Automática: Conclusão de Resolução
- Issue #15: Relatório Estatístico Básico (PCA)
- Issue #17: Preparação e Execução do Deploy (Produção)

---

## 🔗 Links Úteis

- **Repositório GitHub**: https://github.com/TECHSOLUTIONS-PROJECTS/www.mdqr.co.mz
- **Milestone 1**: https://github.com/TECHSOLUTIONS-PROJECTS/www.mdqr.co.mz/milestone/1
- **Milestone 2**: https://github.com/TECHSOLUTIONS-PROJECTS/www.mdqr.co.mz/milestone/2
- **Milestone 3**: https://github.com/TECHSOLUTIONS-PROJECTS/www.mdqr.co.mz/milestone/3
- **Issues Board**: https://github.com/TECHSOLUTIONS-PROJECTS/www.mdqr.co.mz/issues

---

## 🚀 Features Implementadas Recentemente

### Novembro 2025

#### ✅ Sistema Completo de Notificações por Email
**Commits**: `#28` a `#34`, `e3bfa8b`, `02663b7`, `a9256b5`, `f343658`, `559065e`  
**Status**: ✅ Concluído

**Funcionalidades:**
- Sistema completo de notificações automáticas por email
- 6 tipos de emails implementados:
  - ✉️ Reclamação criada (GrievanceCreated)
  - 🔄 Status alterado (GrievanceStatusChanged)
  - 👤 Atribuída a técnico (GrievanceAssigned)
  - 💬 Comentário adicionado (GrievanceCommentAdded)
  - ✅ Reclamação resolvida (GrievanceResolved)
  - ❌ Reclamação rejeitada (GrievanceRejected)
- Templates HTML e texto para todos os emails
- Observer pattern (GrievanceObserver) para disparo automático
- Service layer (NotificationService) para lógica de notificação
- Sistema de tracking de notificações (grievance_notifications table)

**Melhorias Técnicas:**
- Labels de status traduzidos em português
- Data de resolução nos emails de conclusão
- Validação de destinatários (user_id vs contact_email)
- Tratamento de erros e logging

#### ✅ Sistema de Teste de Emails
**Commits**: `1b7b41c`, `b8effd1`, `264da76`, `cb87ebb`, `6cc8b41`, `02663b7`, `559065e`  
**Status**: ✅ Concluído

**Funcionalidades:**
- Interface web para testar todos os tipos de emails
- Comando Artisan para testes via terminal
- Criação automática de grievances de teste
- Seleção de tipo de email específico ou todos
- Feedback visual de sucesso/erro
- Estados de loading durante envio
- Link de acesso nos menus mobile e desktop

**Comandos Disponíveis:**
```bash
# Testar todos os emails
php artisan email:test

# Testar tipo específico
php artisan email:test created
php artisan email:test status-changed
php artisan email:test assigned
php artisan email:test resolved
php artisan email:test rejected

# Especificar destinatário
php artisan email:test --email=teste@example.com

# Usar grievance existente
php artisan email:test --grievance=1
```

#### ✅ Sistema de Tipos de Grievances
**Commits**: `718c6ef`, `2d77438`, `47ac8e0`  
**Status**: ✅ Concluído

**Funcionalidades:**
- 3 tipos distintos de submissões:
  - 🔴 Queixa (Grievance)
  - 📋 Reclamação (Complaint)
  - 💡 Sugestão (Suggestion)
- Campo enum na base de dados
- Validação de tipo no backend
- Interface de seleção no formulário
- Lógica específica por tipo quando necessário

#### ✅ Atualização de Branding e Terminologia
**Commits**: `3df181a`, `86dd1b4`, `e2edd2f`, `750c1d6`  
**Data**: 22 de Novembro de 2025  
**Status**: ✅ Concluído

**Alterações:**
- Mudança de "denúncia" para "queixa" em toda aplicação
- Landing page estática (landingpage.html):
  - Hero: "DENUNCIE, SUGIRA, RECLAME!" → "RECLAME, SUGIRA, QUEIXA!"
  - CTA: "DENUNCIE JÁ" → "RECLAME JÁ"
  - Cards: "DENÚNCIAS" → "QUEIXAS"
  - Stats: "DENÚNCIAS RESOLVIDAS" → "QUEIXAS RESOLVIDAS"
  - FAQs atualizadas
  - Footer: "Denúncia" → "Queixa"

- Componentes Vue (landing page):
  - HeroSection.vue
  - CardsSection.vue
  - StatsSection.vue
  - FaqSection.vue
  - Footer.vue
  - PlatformSection.vue

- Página de Autenticação (Main.vue):
  - Carrossel mobile
  - Overlays desktop (esquerdo e direito)
  - "Denuncie aqui" → "Queixe-se aqui"

**Impacto:**
- ✅ Consistência de branding
- ✅ Linguagem mais acolhedora
- ✅ Melhor alinhamento com propósito do sistema
- ✅ Experiência de usuário aprimorada

#### ✅ Correções e Melhorias no Seeder
**Commits**: `df6bc38`, `b944b70`, `d3b8472`, `426d9f1`, `044c77e`  
**Status**: ✅ Concluído

**Melhorias:**
- Uso de `firstOrCreate` para evitar duplicação de updates
- Uso de `updateOrCreate` para garantir idempotência
- Limpeza de dados duplicados
- Validação de campo `type` em todos os grievances
- 8 grievances de exemplo com diferentes estados:
  - Submetida (recente e anônima)
  - Em Análise
  - Em Andamento
  - Pendente de Aprovação
  - Resolvida
  - Rejeitada
  - Atribuída (prioridade alta)

#### ✅ Correção de Templates de Email
**Commits**: `e0dd0a6`, `c1b172b`  
**Status**: ✅ Concluído

**Correções:**
- Variáveis `$oldStatusLabel` e `$newStatusLabel` definidas
- Array de statusLabels passado via método `content()`
- Remoção de código redundante
- Templates limpos e consistentes

#### ✅ Melhorias de Dashboard
**Commit**: `f35bbaf`  
**Status**: ✅ Concluído

**Correções:**
- Problemas no dashboard do gestor corrigidos
- Melhor visualização de estatísticas
- Performance otimizada

---

## 📈 Progresso Geral

### Features Core Implementadas (v0.3)
- ✅ Sistema de autenticação completo
- ✅ Gestão de roles e permissões (Spatie)
- ✅ Submissão de queixas/reclamações/sugestões
- ✅ Upload de anexos (Storage)
- ✅ Sistema de tipos (Queixa, Reclamação, Sugestão)
- ✅ Alocação automática de técnicos
- ✅ Dashboard de técnico
- ✅ Dashboard de gestor
- ✅ Dashboard de PCA
- ✅ Sistema completo de notificações por email
- ✅ Templates de email (HTML + texto)
- ✅ Observer pattern para automação
- ✅ Sistema de teste de emails
- ✅ Tracking de status em tempo real
- ✅ Histórico de atualizações
- ✅ Landing page responsiva
- ✅ Branding atualizado

### Próximas Features (Backlog)
- 🔄 Notificações SMS
- 🔄 Sistema de feedback do utente
- 🔄 Avaliação de satisfação
- 🔄 Relatórios avançados e exportação
- 🔄 Dashboard analítico completo
- 🔄 API REST para integrações
- 🔄 App mobile (Flutter/React Native)
- 🔄 Chat em tempo real (WebSockets)
- 🔄 Sistema de escalação automática

---

**Última Atualização**: 22 de Novembro, 2025  
**Versão Atual**: 0.3  
**Equipa**: TECHSOLUTIONS, LDA
