
# Estado das Funcionalidades por Fluxo

Este documento reflete o estado atual do sistema FUNAE, incluindo fluxos principais, funcionalidades técnicas, integrações, notificações e backlog. Cada fluxo está dividido em: **Implementado**, **Parcialmente Implementado** e **Por Implementar**.

## Sumário Rápido

- [Fluxo 1 — Submissão de Reclamação](#fluxo-1-submissão-de-reclamação-pelo-utente)
- [Fluxo 2 — Triagem e Atribuição](#fluxo-2-triagem-e-atribuição-de-reclamação)
- [Fluxo 3 — Resolução pelo Técnico](#fluxo-3-resolução-da-reclamação-pelo-técnico)
- [Fluxo 4 — Acompanhamento pelo Utente](#fluxo-4-acompanhamento-da-reclamação-pelo-utente)
- [Fluxo 5 — Relatórios e Estatísticas](#fluxo-5-gera%C3%A7%C3%A3o-de-relat%C3%B3rios-e-estat%C3%ADsticas)
- [Sistema de Notificações](#sistema-de-notificações)
- [Backlog (Por Implementar)](#backlog-por-implementar)

## Legenda de Status

- ✅ Implementado
- 🚧 Parcialmente Implementado
- ❌ Por Implementar

## Resumo por Fluxo (visão rápida)

| Fluxo | Descrição curta | Estado agregado |
|-------|-----------------|-----------------|
| Fluxo 01 | Submissão de Reclamações | ✅ Implementado |
| Fluxo 02 | Triagem e Atribuição (Sistema Automático) | ✅ Implementado |
| Fluxo 03 | Resolução pelo Técnico | ✅ Implementado |
| Fluxo 04 | Acompanhamento pelo Utente | ✅ Implementado |
| Fluxo 05 | Notificações via Email | Implementado |
| Fluxo 06 | Dashboard de Utente | 🚧 Parcialmente Implementado |
| Fluxo 07 | Dashboard de Gestor | 🚧 Parcial |
| Fluxo 08 | Dashboard de Técnico | 🚧 Parcial |
| Fluxo 09 | Dashboard de Director | 🚧 Parcial |
| Fluxo 10 | Dashboard de PCA | 🚧 Iniciado |


## Fluxo 1: Submissão de Reclamação pelo Utente

### Implementado (Fluxo 1)

- Acesso à plataforma via web/app
- Escolha entre submissão anónima ou identificada *(toggle visual SIM/NÃO com cards)*
- **Escolha de Projecto** *(lista de projectos do FUNAE disponível, opcional)*
- **Escolha de tipo (Reclamação, Sugestão ou Queixa)** *(cards visuais interactivos com ícones)*
- Preenchimento do formulário (descrição com limite 50-1500 caracteres, localização)
- **Gravação ou anexo de áudio** *(suporte a gravação via microfone até 2min e upload de ficheiros)*
- **Segmentação clara do formulário em passos/seções** *(3 steps: Informações, Localização, Evidências)*
- **Feedback visual após submissão** *(toast notifications, loading states, modal de confirmação com código de rastreio)*
- Validação dos dados do formulário
- Geração de código único de rastreio
- Envio de notificação de confirmação por email *(configuração de emails automáticos realizada, recomenda-se validação em produção)*
- **Uso consistente de ícones (sem emojis)** *(Heroicons implementados em todo o formulário)*

### Parcialmente Implementado (Fluxo 1)

- Alocação automática da reclamação a um técnico *(algoritmo de auto-assign precisa de ajustes para casos complexos)*
- Notificação ao Gestor e Técnico alocado *(funciona, mas pode falhar se email não estiver corretamente configurado)*

### Concluído (Fluxo 1) - Anteriormente "Por Implementar"

- ✅ Escolha de Projecto *(implementado em 02/12/2025)*
- ✅ Escolha de tipo (Reclamação, Sugestão ou Dúvida) *(implementado em 02/12/2025)*
- ✅ Possibilidade de falar ou anexar um áudio *(implementado em 02/12/2025)*
- ✅ Segmentação clara do formulário em passos/seções *(implementado em 02/12/2025)*
- ✅ Feedback visual após submissão (toast, loading, confirmação) *(implementado em 02/12/2025)*
- ✅ Remoção de categoria/subcategoria (simplificação) *(implementado em 03/12/2025)*
- ✅ Toggle anónimo melhorado com SIM/NÃO visual *(implementado em 03/12/2025)*
- ✅ Limite de descrição 50-1500 caracteres *(implementado em 03/12/2025)*
- ✅ Substituição de emojis por ícones *(implementado em 03/12/2025)*

## Fluxo 2: Triagem e Atribuição de Reclamação

### Implementado (Fluxo 2)

- Acesso ao painel de gestão e visualização de novas reclamações
- Análise da descrição, categoria e anexos
- Definição do nível de prioridade
- Troca da atribuição automática do técnico (se necessário)
- Notificação ao técnico reatribuído (via email automático)
- Encaminhamento para o director em casos críticos

### Por Implementar (Fluxo 2)

- Filtros avançados e comparação de períodos
- Relatórios agendados por email


## Fluxo 3: Resolução da Reclamação pelo Técnico

### Implementado (Fluxo 3)

- Recepção de notificação da reclamação atribuída (email automático)
- Acesso ao painel e visualização de detalhes
- Alteração do estado para 'Em Andamento'
- Notificação ao utente sobre mudança de estado
- Execução de acções corretivas
- Inserção de actualizações, comentários e evidências
- Solicitação ao Gestor para conclusão do processo
- Revisão da solicitação e marcação como 'Resolvido'
- Notificação ao utente sobre a resolução (email automático)

### Por Implementar (Fluxo 3)

- Sistema de aprovação de conclusão mais robusto
- Melhorias no fluxo de atualização de técnicos
- Clarificação de quando evidências devem ser submetidas (início ou fim)


## Fluxo 4: Acompanhamento da Reclamação pelo Utente

### Implementado (Fluxo 4)

- Acesso à plataforma e selecção de 'Acompanhar Reclamação'
- Inserção do código de rastreio
- Exibição do estado actual da reclamação
- Visualização do histórico de actualizações e comentários
- Consulta de anexos e evidências de resolução

### Por Implementar (Fluxo 4)

- Tracking interno no dashboard (evitar abrir nova aba)


## Fluxo 5: Geração de Relatórios e Estatísticas

### Implementado (Fluxo 5)

- Acesso à secção de Relatórios e Estatísticas
- Definição de filtros (período, tipo, departamento, estado)
- Geração de dashboard com indicadores e gráficos
- Análise de gráficos e indicadores

### Parcialmente Implementado (Fluxo 5)

- Exportação de relatório em PDF/Excel (exportação avançada em backlog)

### Por Implementar (Fluxo 5)

- Exportação avançada customizada
- Relatórios customizados por perfil

## Estados da Reclamação

| Estado                  | Descrição | Estado |
|-------------------------|-----------|--------|
| Submetida               | Registada, a aguardar triagem | ✅ |
| Em Análise              | Gestor a analisar/classificar | ✅ |
| Atribuída               | Alocada a técnico | ✅ |
| Em Andamento            | Técnico a trabalhar na resolução | ✅ |
| Pendente de Aprovação   | A aguardar aprovação do Gestor | ✅ |
| Resolvida               | Concluída e utente notificado | ✅ |
| Rejeitada               | Considerada inválida/fora do âmbito | ✅ |


## Sistema de Notificações

### Implementado

- Confirmação de submissão (utente)
- Alteração de estado (utente)
- Conclusão do processo (utente)
- Nova reclamação atribuída (gestor/técnico)
- Solicitação de conclusão pendente (gestor/técnico)
- Comentários/actualizações (gestor/técnico)

### Parcialmente Implementado

- Alertas de prazos (gestor/técnico)

**Observação:** O sistema de notificações depende da correta configuração dos emails automáticos (SMTP). Recomenda-se validar as configurações em ambiente de produção para garantir o envio confiável de todas as notificações.


## Backlog (Por Implementar) — Agrupado

### UX / Produto

- Segmentação do formulário e feedback visual (toast, loading)
- Onboarding guiado e documentação multilíngue (PT, EN, Changana)
- Melhorias de UX: toast notifications, loading spinners, pop-ups de confirmação

### Relatórios / Dados

- Exportação avançada de relatórios (PDF/Excel customizado)
- Relatórios agendados por email
- Análise preditiva e alertas automáticos
- Comparação entre períodos nos relatórios
- Relatórios customizados por perfil (Gestor, PCA, Técnico)

### Técnicas / Infraestrutura

- Refino de validações finais e tratamento de excepções
- Auditoria detalhada de acções e logs de sistema
- Painel de administração para gestão de parâmetros do sistema

### Integrações

- Integração com SMS gateway alternativos
- Integração com sistemas de autenticação externa (SSO, OAuth)

### Ações rápidas / Prioridade para apresentação

- Finalizar padronização de layout e components críticos
- Implementar gravação de áudio mínima (MP3) para submissão
- Garantir SMTP configurado e testes de envio OK
