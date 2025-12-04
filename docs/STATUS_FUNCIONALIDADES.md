
# Estado das Funcionalidades por Fluxo

Este documento reflete o estado atual do sistema FUNAE, incluindo fluxos principais, funcionalidades técnicas, integrações, notificações e backlog. Cada fluxo está dividido em: **Implementado**, **Parcialmente Implementado** e **Por Implementar**.

## Sumário Rápido

- [Fluxo 1 — Submissão de Reclamação](#fluxo-1-submissão-de-reclamação-pelo-utente)
- [Fluxo 2 — Triagem e Atribuição](#fluxo-2-triagem-e-atribuição-de-reclamação)
- [Fluxo 3 — Resolução pelo Técnico](#fluxo-3-resolução-da-reclamação-pelo-técnico)
- [Fluxo 4 — Acompanhamento pelo Utente](#fluxo-4-acompanhamento-da-reclamação-pelo-utente)
- [Fluxo 5 — Relatórios e Estatísticas](#fluxo-5-gera%C3%A7%C3%A3o-de-relat%C3%B3rios-e-estat%C3%ADsticas)
- [Sistema de Notificações](#sistema-de-notificações)
- [Backlog (Por Implementar)](#backlog-por-implementar-agrupado)

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
| Fluxo 07 | Dashboard de Gestor | 🚧 Parcialmente Implementado |
| Fluxo 08 | Dashboard de Técnico | 🚧 Parcialmente Implementado |
| Fluxo 09 | Dashboard de Director |    Por Implementar |
| Fluxo 10 | Dashboard de PCA | ✅ Implementado  |
| Fluxo 11 | Sistema de Anexos | ✅ Implementado |
| Fluxo 12 | Downloads de Evidências | ✅ Implementado |
| Fluxo 13 | Sistema de Autenticação Aprimorado | ✅ Implementado |
| Fluxo 14 | Seeder de Performance Avançado | ✅ Implementado |


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
- ✅ Campo `description` agora pode ser nulo *(migration e controller atualizados — implementado em 04/12/2025)*
- ✅ Campo `project_id` no formulário é obrigatório e aceito pelo backend *(implementado em 04/12/2025)*

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

## Fluxo 10: Dashboard de PCA (Reimaginado)

### Implementado (Fluxo 10)

- **Dashboard reimaginado com foco nos tipos de submissão** *(Reclamação, Queixa, Sugestão)*
- **Distribuição por Estado e Tipo** *(cada estado mostra breakdown por tipo de submissão)*
- **Tendência de Submissões por Tipo** *(gráfico de linha com 3 linhas distintas para cada tipo)*
- **Insights de Projetos** *(submissões por projeto, projetos com técnicos disponíveis)*
- **Métricas de Projeto** *(total de projetos, projetos com técnicos, média de submissões)*
- **Filtros por período** *(últimos 7 dias, 30 dias, 3 meses, 6 meses)*
- **Visualização color-coded** *(Reclamações: vermelho, Queixas: laranja, Sugestões: verde)*

### Parcialmente Implementado (Fluxo 10)

- Exportação de relatórios (funcionalidade básica implementada)
- Filtros avançados por departamento/categoria

### Concluído (Fluxo 10) - Implementado recentemente

- ✅ **Reimaginação completa do dashboard** *(implementado em 03/12/2025)*
- ✅ **Foco nos 3 tipos de submissão** *(Reclamação, Queixa, Sugestão)*
- ✅ **Seções reimaginadas**: Distribuição por Estado, Tendências, Categorias *(implementado em 03/12/2025)*
- ✅ **Insights de projetos** *(submissões por projeto, técnicos disponíveis)*
- ✅ **Correção de bugs Chart.js** *(importações corrigidas para funcionamento adequado)*
- ✅ **Reorganização do layout** *(Distribuição por Prioridade movida acima da Distribuição por Estado e Tipo - implementado em 04/12/2025)*

## Fluxo 11: Sistema de Anexos

### Implementado (Fluxo 11)

- **Upload de múltiplos anexos** *(suporte a imagens, documentos, áudio)*
- **Armazenamento seguro** *(disco privado com controle de acesso)*
- **Validação de tipos de arquivo** *(limitações por tamanho e tipo)*
- **Associação com reclamações** *(relacionamento direto no banco de dados)*
- **Visualização de anexos** *(ícones e nomes de arquivo)*

### Concluído (Fluxo 11) - Implementado recentemente

- ✅ **Sistema completo de anexos implementado** *(implementado em 02/12/2025)*
- ✅ **Suporte a gravação de áudio** *(até 2 minutos via microfone)*
- ✅ **Upload de arquivos** *(imagens, documentos, áudio)*

## Fluxo 12: Downloads de Evidências

### Implementado (Fluxo 12)

- **Download para usuários autenticados** *(utentes podem baixar seus próprios anexos)*
- **Download para usuários não autenticados** *(via rastreamento público)*
- **Abertura inline no navegador** *(PDFs, imagens, áudio abrem diretamente)*
- **Controle de permissões** *(utentes só acessam seus próprios arquivos)*
- **URLs seguras** *(roteamento protegido com validação)*

### Concluído (Fluxo 12) - Implementado recentemente

- ✅ **Download de anexos habilitado** *(implementado em 02/12/2025)*
- ✅ **Abertura inline no navegador** *(implementado em 03/12/2025)*
- ✅ **Acesso público via rastreamento** *(implementado em 03/12/2025)*
- ✅ **Correção de rotas** *(URLs corrigidas para funcionamento adequado)*

## Fluxo 13: Sistema de Autenticação Aprimorado

### Implementado (Fluxo 13)

- **Redirecionamento inteligente baseado no papel** *(PCA → pca.dashboard, Gestor → manager.dashboard, etc.)*
- **Proteção completa contra acesso não autorizado** *(usuários logados não acessam login/register)*
- **Middleware aprimorado** *(RedirectIfAuthenticated com lógica avançada)*
- **Cobertura de todas as rotas de autenticação** *(login, register, auth, password reset)*
- **Testes automatizados** *(cobertura completa de cenários de redirecionamento)*

### Concluído (Fluxo 13) - Implementado recentemente

- ✅ **Middleware de redirecionamento refatorado** *(implementado em 04/12/2025)*
- ✅ **Lógica baseada em papéis implementada** *(PCA, Gestor, Técnico, Utente)*
- ✅ **Proteção contra acesso não autorizado** *(usuários logados redirecionados automaticamente)*
- ✅ **Testes de autenticação expandidos** *(cobertura de todos os cenários)*
- ✅ **Rota dashboard genérica adicionada** *(compatibilidade com controladores padrão)*

## Fluxo 14: Seeder de Performance Avançado

### Implementado (Fluxo 14)

- **Criação automática de projetos** *(15 projetos com dados realistas)*
- **Associação de técnicos a projetos** *(1-3 projetos por técnico)*
- **Reclamações associadas a projetos** *(70% das reclamações relacionadas)*
- **Atribuição inteligente de técnicos** *(prioriza técnicos do projeto relacionado)*
- **Dados de performance massivos** *(500 utentes, 20 técnicos, 2000 reclamações)*
- **Configuração flexível** *(parâmetros ajustáveis via método configure())*

### Concluído (Fluxo 14) - Implementado recentemente

- ✅ **PerformanceTestSeeder completamente aprimorado** *(implementado em 04/12/2025)*
- ✅ **Sistema de projetos integrado** *(criação automática + associações)*
- ✅ **Lógica de atribuição inteligente** *(técnicos especializados por projeto)*
- ✅ **Dados realistas e distribuídos** *(70% reclamações com projetos)*
 - ✅ **Seed executado com sucesso** *(seed completo: 15 projetos, 500 utentes, 20 técnicos, 5 gestores, 2000 reclamações; implementado em 04/12/2025)*
 - ✅ **Verificação pós-seed** *(migrations aplicadas e queries verificadas via tinker)*
- ✅ **Inserção otimizada em batch** *(performance mantida com volumes altos)*

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
