# Estado das Funcionalidades por Fluxo

Este documento reflete o estado atual do sistema FUNAE, incluindo fluxos principais, funcionalidades técnicas, integrações, notificações e backlog. Cada fluxo está dividido em: **Implementado**, **Parcialmente Implementado** e **Por Implementar**.

**Última atualização:** 13/12/2025, 22:30

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
| Fluxo 05 | Notificações via Email |  ✅ Implementado |
| Fluxo 06 | Dashboard de Utente |  ✅ Implementado |
| Fluxo 07 | Dashboard de Gestor |  ✅ Implementado |
| Fluxo 08 | Dashboard de Técnico | ✅ Implementado |
| Fluxo 09 | Dashboard de Director | ✅ Implementado |
| Fluxo 10 | Dashboard de PCA | ✅ Implementado  |
| Fluxo 11 | Sistema de Anexos | ✅ Implementado |
| Fluxo 12 | Downloads de Evidências | ✅ Implementado |
| Fluxo 13 | Sistema de Autenticação Aprimorado | ✅ Implementado |
| Fluxo 14 | Seeder de Performance Avançado | ✅ Implementado |
| Fluxo 15 | Sistema de Anexos Aprimorado | ✅ Implementado |
| Fluxo 16 | Admin Dashboard e Gestão de Departamentos | ✅ Implementado |

### Distribuição de Usuários

- **Total de Usuários**: 37
- **PCA**: 1
- **Director**: 6
- **Gestor**: 9
- **Técnico**: 17 (todos com workload configurado)
- **Utente**: 2


## Fluxo 1: Submissão de Reclamação pelo Utente

### Implementado (Fluxo 1)

- Acesso à plataforma via web/app
- Escolha entre submissão anónima ou identificada *(toggle visual SIM/NÃO com cards)*
- **Escolha de Projecto** *(lista de projectos do FUNAE disponível, opcional)*
- **Escolha de tipo (Reclamação, Sugestão ou Queixa)** *(cards visuais interactivos com ícones)*
- Preenchimento do formulário (descrição com limite 50-1500 caracteres, localização)
- **Gravação ou anexo de áudio** *(suporte a gravação via microfone até 1 minuto e upload de ficheiros — limite reduzido em 04/12/2025)*
- **Segmentação clara do formulário em passos/seções** *(3 steps: Informações, Localização, Evidências)*
- **Feedback visual após submissão** *(toast notifications, loading states, modal de confirmação com código de rastreio)*
- Validação dos dados do formulário
- Geração de código único de rastreio
- Envio de notificação de confirmação por email *(configuração de emails automáticos realizada, recomenda-se validação em produção)*
- **Uso consistente de ícones (sem emojis)** *(Heroicons implementados em todo o formulário)*
- **Modal de submissão acessível diretamente da landing page** *(implementado em 06/12/2025)*

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
- ✅ Campo `project_id` no formulário é opcional no frontend e aceito como `nullable` pelo backend *(implementado em 04/12/2025)*
- ✅ Aumento do tempo de auto-fechamento do modal de sucesso de 5 para 60 segundos *(implementado em 06/12/2025)*
- ✅ Campos de contato (nome e email) tornados opcionais para submissões anônimas, permitindo que usuários anônimos forneçam informações de contato opcionais para acompanhamento *(implementado em 06/12/2025)*
- ✅ Melhoria da mensagem do modal de sucesso para alertar explicitamente que o modal fechará em 60 segundos e que o usuário deve salvar o código de rastreio *(implementado em 06/12/2025)*

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
- **Sistema completo de visualização de anexos** *(suporte a imagens, PDFs, áudio)*
- **Abertura inline de arquivos** *(PDFs, imagens, áudio reproduzem diretamente no navegador)*
- **Interface responsiva com ícones apropriados** *(nota musical para áudio, documento para PDFs)*
- **Controle de permissões por status** *(reclamações confidenciais/restritas não permitem visualização pública)*
- **Ocultar seção de busca após rastreamento** *(experiência mais limpa focada nos resultados)*
- **Reexibir seção de busca ao consultar nova reclamação** *(navegação intuitiva)*

### Concluído (Fluxo 4) - Implementado recentemente

- ✅ **Sistema de visualização de anexos completo** *(implementado em 07/12/2025)*
- ✅ **Suporte a arquivos de áudio** *(WebM, MP3, WAV, OGG, M4A, MPEG - implementado em 07/12/2025)*
- ✅ **Ocultar/mostrar seção de busca** *(implementado em 07/12/2025)*
- ✅ **Refatoração da interface de rastreamento** *(implementado em 07/12/2025)*


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


## Fluxo 9: Dashboard de Director

### Implementado (Fluxo 9)

- **Dashboard Executivo**: 
  - Visão geral com métricas críticas (Reclamações Pendentes, Críticas, Taxa de Resolução).
  - Gráficos interativos (Distribuição por Status, Tipo, Prioridade e Tendências Mensais).
  - Ações rápidas para validação e visualização de casos críticos.
- **Gestão de Equipe e Departamentos**: 
  - **Gestão de Gestores**: Acompanhamento de desempenho individual (casos atribuídos, resolvidos, tempo médio).
  - **Gestão de Províncias**: Estatísticas detalhadas por província com comparativos de desempenho.
- **Relatórios e Inteligência**: 
  - **Sistema de Relatórios**: Geração e exportação de relatórios estatísticos (PDF/Excel).
  - **KPIs Avançados**: Indicadores operacionais (tempo de resposta, conformidade) e financeiros.
  - **Análise de Tendências**: Visualização de evolução de casos ao longo do tempo (filtros por período: semana, mês, trimestre, ano).
- **Integração Completa**: 
  - Rotas protegidas e exclusivas para perfil Director.
  - API dedicada para fornecimento de dados em tempo real.

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
- **Suporte completo a arquivos de áudio** *(WebM, MP3, WAV, OGG, M4A, MPEG)*
- **Detecção automática de tipo MIME** *(ícones apropriados para cada tipo)*
- **Interface responsiva com galeria organizada** *(modal informativo e botões de ação)*
- **Visualização inline de anexos** *(preview direto no navegador — implementado em 08/12/2025)*
- **URLs públicos para anexos** *(acesso direto via links públicos — implementado em 08/12/2025)*
- **Gestão melhorada de ficheiros** *(caminhos corrigidos e logs aprimorados — implementado em 08/12/2025)*
- **Limite de upload ajustado** *(tamanho máximo de ficheiro atualizado para 2MB — implementado em 08/12/2025)*
- **Galeria de anexos melhorada** *(modal redesenhado com controles UI aprimorados — implementado em 08/12/2025)*
- **Sistema de acesso restrito** *(visualização de anexos públicos com restrições — implementado em 08/12/2025)*
- **Diretório uploads excluído do Git** *(/public/uploads adicionado ao .gitignore — implementado em 08/12/2025)*

### Concluído (Fluxo 11) - Implementado recentemente

- ✅ **Sistema de visualização de anexos completo** *(implementado em 07/12/2025)*
- ✅ **Suporte a gravação de áudio** *(até 1 minuto via microfone — limite reduzido em 04/12/2025)*
- ✅ **Suporte a múltiplos formatos de áudio** *(WebM, MP3, WAV, OGG, M4A, MPEG - implementado em 07/12/2025)*
- ✅ **Ícones específicos por tipo de arquivo** *(nota musical para áudio, documento para PDFs - implementado em 07/12/2025)*
- ✅ **Visualização inline aprimorada** *(preview direto de imagens, PDFs e áudios - implementado em 08/12/2025)*
- ✅ **URLs públicos com restrições** *(acesso seguro via links públicos - implementado em 08/12/2025)*
- ✅ **Limite de 2MB** *(ajuste de tamanho máximo de upload - implementado em 08/12/2025)*
- ✅ **Galeria redesenhada** *(controles UI melhorados - implementado em 08/12/2025)*

### Implementado (Fluxo 12)

- **Download para usuários autenticados** *(utentes podem baixar seus próprios anexos)*
- **Download para usuários não autenticados** *(via rastreamento público)*
- **Abertura inline no navegador** *(PDFs, imagens, áudio abrem diretamente)*
- **Controle de permissões** *(utentes só acessam seus próprios arquivos)*
- **URLs seguras** *(roteamento protegido com validação)*
- **Suporte completo a reprodução de áudio** *(WebM, MP3, WAV, OGG, M4A, MPEG)*
- **Controle de acesso baseado no status da reclamação** *(confidencial/restrito não permite visualização)*
- **Cache otimizado** *(1 hora de cache para performance)*
- **Preview inline de todos os formatos** *(imagens, PDFs, áudios - implementado em 08/12/2025)*
- **URLs públicos diretos** *(acesso via links públicos seguros - implementado em 08/12/2025)*
- **Logs aprimorados** *(rastreamento detalhado do processamento de anexos - implementado em 08/12/2025)*

### Concluído (Fluxo 12) - Implementado recentemente

- ✅ **Download de anexos habilitado** *(implementado em 02/12/2025)*
- ✅ **Abertura inline no navegador** *(implementado em 03/12/2025)*
- ✅ **Acesso público via rastreamento** *(implementado em 03/12/2025)*
- ✅ **Correção de rotas** *(URLs corrigidas para funcionamento adequado)*
- ✅ **Sistema de permissões por status** *(implementado em 07/12/2025)*
- ✅ **Suporte completo a áudio** *(implementado em 07/12/2025)*
- ✅ **Preview inline aprimorado** *(visualização direta de todos os tipos - implementado em 08/12/2025)*
- ✅ **URLs públicos seguros** *(implementado em 08/12/2025)*
- ✅ **Logging detalhado** *(rastreamento de processamento - implementado em 08/12/2025)*

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

## Fluxo 15: Sistema de Anexos Aprimorado e Eventos de Atribuição

### Implementado (Fluxo 15)

- **Visualização inline de anexos** *(preview direto de imagens, PDFs e áudios no navegador)*
- **Suporte expandido para áudio** *(tipos adicionais: MP3, WAV, OGG, M4A, MPEG)*
- **URLs públicos para anexos** *(acesso direto via links públicos com restrições de segurança)*
- **Gestão melhorada de ficheiros** *(caminhos corrigidos e logs aprimorados para anexos)*
- **Limite de upload ajustado para 2MB** *(tamanho máximo de ficheiro atualizado)*
- **Galeria de anexos melhorada** *(modal redesenhado com controles UI aprimorados)*
- **Segurança reforçada** *(sistema de acesso restrito para visualização de anexos públicos)*
- **Exclusão do Git** *(diretório /public/uploads adicionado ao .gitignore)*
- **Evento GrievanceAutoAssigned** *(nova classe de evento para rastreamento de atribuições automáticas)*
- **Logging aprimorado** *(melhor rastreamento do processo de atribuição de técnicos)*
- **Performance otimizada** *(processamento de eventos assíncronos para atribuições)*

### Concluído (Fluxo 15) - Implementado recentemente

- ✅ **Visualização inline aprimorada** *(implementado em 08/12/2025)*
- ✅ **URLs públicos seguros** *(implementado em 08/12/2025)*
- ✅ **Limite de 2MB** *(implementado em 08/12/2025)*
- ✅ **Galeria redesenhada** *(implementado em 08/12/2025)*
- ✅ **Evento de atribuição automática** *(implementado em 08/12/2025)*
- ✅ **Logs detalhados** *(implementado em 08/12/2025)*
- ✅ **Exclusão do diretório uploads** *(implementado em 08/12/2025)*


## Fluxo 16: Admin Dashboard e Gestão de Departamentos

### Implementado (Fluxo 16)

- **Admin Dashboard Completo** *(interface dinâmica com estatísticas em tempo real, acções rápidas baseadas em permissões)*
- **Gestão de Departamentos** *(5 departamentos com estrutura organizacional completa)*
- **Alocação de Usuários** *(37 usuários distribuídos entre departamentos)*
- **Relações Departamento-Projeto** *(9 projectos vinculados a departamentos)*
- **Sistema de Workload para Técnicos** *(campos específicos para gestão de carga de trabalho)*
- **Permissões Granulares** *(acções baseadas em permissões do usuário)*
- **Seeders Avançados** *(criação automática de estrutura organizacional)*
- **Redirecionamento Inteligente** *(Admin/Super Admin → admin.dashboard)*
- **Menu Lateral Dinâmico** *(navegação específica para role Admin)*
- **Estatísticas em Tempo Real** *(contadores dinâmicos de recursos do sistema)*

### Estrutura de Departamentos Criada

| Departamento | Director | Gestores | Técnicos | Projectos |
|--------------|----------|----------|----------|-----------|
| Infraestrutura e Construção | Director de Infraestrutura | 3 | 5 | 3 |
| Energia e Electrificação | Director de Energia | 2 | 5 | 2 |
| Água e Saneamento | Director de Água e Saneamento | 2 | 3 | 2 |
| Educação e Desenvolvimento Social | Director de Educação | 1 | 2 | 1 |
| Saúde Pública | Director de Saúde | 1 | 2 | 1 |



### Campos de Workload (Técnicos)

- `workload_capacity`: 10 (capacidade máxima de casos)
- `current_workload`: 0-5 (carga atual aleatória)
- `is_available`: true (disponível para alocação)
- **Outros usuários**: Todos os campos NULL

### Concluído (Fluxo 16) - Implementado recentemente

- ✅ **Admin Dashboard completo** *(implementado em 10/12/2025)*
- ✅ **Sistema de Departamentos** *(implementado em 10/12/2025)*
- ✅ **Alocação de Usuários** *(implementado em 10/12/2025)*
- ✅ **Relações Departamento-Projeto** *(implementado em 10/12/2025)*
- ✅ **Workload para Técnicos** *(implementado em 11/12/2025)*
- ✅ **Seeders Organizacionais** *(implementado em 10-11/12/2025)*
- ✅ **Permissões Granulares** *(implementado em 10/12/2025)*
- ✅ **Migrations de Relacionamento** *(implementado em 10/12/2025)*
- ✅ **Modelos Atualizados** *(implementado em 10/12/2025)*
- ✅ **Redirecionamento Admin** *(implementado em 10/12/2025)*


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

### Ações rápidas / Prioridade para apresentação

- Finalizar padronização de layout e components críticos
- Implementar gravação de áudio mínima (MP3) para submissão
- Garantir SMTP configurado e testes de envio OK

## Novas funcionalidades (04/12/2025)

- **Limite de gravação reduzido para 1 minuto**: UX e backend atualizados para encurtar gravações de áudio a 60s.
- **Campo `description` opcional**: `description` agora aceita null no banco de dados e é opcional no formulário; validação só é aplicada se preenchido.
- **`project_id` opcional no frontend**: o formulário aceita submissões sem projeto; backend aceita `project_id` como `nullable`.
- **PCA Dashboard reimaginado**: reorganização de seções, foco nos 3 tipos (Reclamação/Queixa/Sugestão), métricas e insights de projetos.
- **RedirectIfAuthenticated**: middleware refatorado para redirecionamento por papel (PCA, Gestor, Técnico, Utente) e cobertura de todas rotas de autenticação.
- **PerformanceTestSeeder**: criado/ajustado para gerar 15 projetos, associar técnicos, criar grandes volumes de dados (500 utentes, 2000 reclamações) e priorizar atribuição por projeto.
- **Anexos & Downloads**: suporte a upload múltiplo, armazenamento seguro, abertura inline no navegador e downloads por utente/público via rastreamento.
- **Testes**: novos testes de redirecionamento de autenticação e seeding validados via tinker.

## Novas funcionalidades (06/12/2025)

- **Aumento do tempo de auto-fechamento do modal de sucesso**: Timer aumentado de 5 para 60 segundos para dar mais tempo aos usuários salvarem o código de rastreio.
- **Campos de contato opcionais para submissões anônimas**: Nome e email agora são opcionais, permitindo que usuários anônimos forneçam informações de contato voluntariamente para acompanhamento.
- **Melhoria da mensagem do modal de sucesso**: Adicionado aviso explícito de que o modal fechará em 60 segundos e instrução para salvar o código de rastreio.
- **Modal de submissão acessível diretamente da landing page**: Implementado acesso direto ao formulário de reclamações a partir da página inicial, facilitando a submissão imediata.
- **Melhorias no footer da landing page**: Atualizações visuais e de conteúdo no rodapé para melhor usabilidade e informações.
- **Melhorias nos textos da landing page**: Revisão e aprimoramento dos textos para maior clareza, engajamento e alinhamento com a identidade do projeto.


## Novas funcionalidades (07/12/2025)

- **Sistema de Visualização de Anexos Completo**: Implementado sistema completo de visualização de anexos com suporte a múltiplos formatos (imagens, PDFs, áudio WebM/MP3/WAV/OGG/M4A/MPEG), abertura inline no navegador, controle de permissões por status da reclamação, e interface responsiva com ícones apropriados.
- **Ocultar Seção de Busca Após Rastreamento**: Implementada funcionalidade onde a seção "Rastreamento Seguro" é automaticamente ocultada após uma busca bem-sucedida, reaparecendo apenas ao clicar em "Consultar Outra Reclamação" para uma experiência mais limpa e focada nos resultados.
- **Melhorias na Interface de Rastreamento**: Refatoração completa do componente de rastreamento com melhor estrutura de código, tratamento aprimorado de erros, e experiência de usuário mais fluida.
- **Suporte a Áudio em Anexos**: Adicionado suporte completo para arquivos de áudio (WebM, MP3, WAV, OGG, M4A, MPEG) com detecção automática de tipo MIME, ícone de nota musical, e reprodução inline no navegador.
- **Controle de Acesso a Anexos**: Implementado sistema de permissões baseado no status da reclamação (restrito/confidencial não permite visualização pública), com validação de tipos de arquivo seguros.
- **Melhorias na Landing Page**: Adicionado link destacado de acompanhamento, melhorias visuais no footer, atualização de textos para maior clareza e engajamento, e abertura automática do modal de submissão via parâmetro de URL.
- **Campos de Contato Opcionais**: Implementado sistema onde campos de nome e email são opcionais para submissões anônimas, permitindo contato voluntário para acompanhamento.
- **Timer de Auto-fechamento do Modal**: Aumentado o tempo de auto-fechamento do modal de sucesso de 5 para 60 segundos com aviso explícito para salvar o código de rastreio.
- **Dashboard PCA Reimaginado**: Reorganização completa com foco nos 3 tipos de submissão (Reclamação/Queixa/Sugestão), métricas por projeto, distribuição por estado e tipo, e visualização color-coded.
- **Sistema de Autenticação Aprimorado**: Middleware de redirecionamento inteligente baseado em papéis (PCA, Gestor, Técnico, Utente), proteção contra acesso não autorizado, e cobertura completa de rotas de autenticação.
- **Seeder de Performance Avançado**: Sistema completo de seeding com 15 projetos, associação inteligente de técnicos a projetos, 2000 reclamações realistas, e priorização de atribuição por especialização.
- **Documentação Atualizada**: README.md atualizado com mudanças de dezembro 2025, guia de deploy, revisão de notificações por email, e documentação completa do status das funcionalidades.

## Novas funcionalidades (08/12/2025)

- **Visualização Inline Aprimorada de Anexos**: Sistema completo de preview direto no navegador para imagens, PDFs e áudios, eliminando necessidade de download para visualização inicial.
- **URLs Públicos para Anexos**: Implementado sistema de acesso direto via links públicos com restrições de segurança baseadas no status da reclamação.
- **Suporte Expandido para Áudio**: Adicionados tipos de ficheiros de áudio adicionais (MP3, WAV, OGG, M4A, MPEG) além do WebM já suportado.
- **Gestão Melhorada de Ficheiros**: Caminhos de anexos corrigidos e sistema de logs aprimorado para melhor rastreamento do processamento de ficheiros.
- **Limite de Upload Ajustado para 2MB**: Tamanho máximo de ficheiro atualizado de forma consistente em todo o sistema para 2MB.
- **Galeria de Anexos Redesenhada**: Modal completamente redesenhado com controles UI aprimorados, melhor responsividade e experiência de usuário mais intuitiva.
- **Sistema de Acesso Restrito**: Implementado controle granular de acesso para visualização de anexos públicos com validações de segurança.
- **Diretório Uploads Excluído do Git**: Adicionado `/public/uploads` ao `.gitignore` para evitar versionamento de ficheiros carregados por usuários.
- **Evento GrievanceAutoAssigned**: Nova classe de evento para rastreamento detalhado de atribuições automáticas de reclamações a técnicos.
- **Logging Aprimorado**: Sistema de logs melhorado para melhor rastreamento do processo de atribuição de técnicos e processamento de anexos.
- **Performance Otimizada**: Processamento de eventos assíncronos para atribuições, melhorando performance geral do sistema.
- **Refatoração do Controller de Rastreamento**: Código do controller de rastreamento refatorado para melhor estrutura, clareza e manutenibilidade.
- **Melhorias no Componente de Rastreamento**: Componente Vue.js refatorado com melhor organização de código e tratamento de erros aprimorado.
- **Controle de Visibilidade da Pesquisa**: Seção de pesquisa com controle dinâmico de exibição para melhor experiência de usuário após rastreamento bem-sucedido.

## Novas funcionalidades (10-11/12/2025)

- **Admin Dashboard Completo**: Implementado dashboard administrativo dinâmico com estatísticas em tempo real, acções rápidas baseadas em permissões, e menu lateral com navegação para Departamentos, Projectos, Usuários e Configurações.
- **Sistema de Departamentos**: Estrutura organizacional completa com 5 departamentos (Infraestrutura, Energia, Água e Saneamento, Educação, Saúde), cada um com Director, Gestores e Técnicos alocados.
- **Gestão de Usuários por Departamento**: Sistema de alocação de usuários (Gestores e Técnicos) a departamentos específicos, com 37 usuários distribuídos estrategicamente.
- **Relações Departamento-Projeto**: Projectos agora vinculados a departamentos específicos, permitindo melhor organização e gestão de recursos.
- **Campos de Workload para Técnicos**: Implementado sistema de carga de trabalho exclusivo para técnicos com campos `workload_capacity`, `current_workload` e `is_available` (nullable para não-técnicos).
- **Seeder de Departamentos**: Criação automática de 5 departamentos com Directores, distribuição inteligente de 9 Gestores e 17 Técnicos, e alocação de 9 projectos.
- **Seeder de Usuários Adicionais**: Sistema de criação de técnicos especializados por departamento (Técnico de Construção Civil, Electricista, Hidráulica, etc.).
- **Atualização de Workload Automática**: Seeder específico para configurar campos de workload apenas para técnicos, mantendo null para outros usuários.
- **Migrations de Relacionamento**: Adicionadas colunas `department_id` em `users` e `projects` para estabelecer relações organizacionais.
- **Modelos Atualizados**: Models `Department`, `Project` e `User` atualizados com relacionamentos Eloquent completos.
- **Dashboard Admin com Permissões**: Sistema de visualização de acções rápidas baseado nas permissões do usuário (manage-users, manage-departments, manage-projects, manage-settings).
- **Roles Expandidos**: Adicionados roles 'Admin' e 'Super Admin' com permissões específicas e redirecionamento automático para `/admin/dashboard`.
- **Estatísticas Dinâmicas**: Dashboard mostra contadores em tempo real de usuários, departamentos, projectos e usuários ativos.
- **Distribuição Organizacional**: Sistema completo de hierarquia: Departamento → Director → Gestores → Técnicos → Projectos.

## Novas funcionalidades (11/12/2025 - Tarde)

- **Localização Detalhada no Formulário**: Implementação de hierarquia completa de localização no formulário de reclamações (Província → Distrito/Distrito Municipal → Posto Administrativo → Localidade) com dados dinâmicos via API.
- **Distinção Maputo Cidade vs Província**: Lógica específica para lidar com a divisão administrativa de Maputo, apresentando "Distritos Municipais" para a Cidade e "Distritos" para a Província.
- **Validação de Localização Mandatória**: Tornados obrigatórios todos os campos de localização aplicáveis (cascata) para garantir qualidade dos dados para roteamento.
- **Privacidade em Submissões Anônimas**: Ocultação automática dos campos de dados pessoais quando o modo anônimo é ativado, com opção ("checkbox") para o usuário fornecer contato voluntariamente.
- **Estatísticas de Gênero**: Adicionado campo "Gênero" (Masculino, Feminino, Outro) no formulário para fins estatísticos, visível apenas quando dados pessoais são fornecidos.
- **Routing e Filtragem por Departamento**: Gestores agora visualizam no dashboard apenas as reclamações associadas a projectos do seu departamento específico, garantindo segregação de acesso e foco.
- **Auto-Reset de Campos**: Implementada lógica de limpeza automática de campos dependentes (ex: mudar província limpa distrito) e dados pessoais ao alternar modos.

## Novas funcionalidades (12/12/2025)

- **Fluxo 9 (Dashboard de Director) Concluído**: Implementação total do dashboard para Directors, incluindo:
    - **Dashboard Executivo**: Métricas de resolução, pendências e satisfação.
    - **Gestão de Províncias e Gestores**: Acompanhamento detalhado de desempenho por região e membro da equipe.
    - **Relatórios**: Geração de relatórios estatísticos e indicadores de desempenho.
- **Modal de Submissão**: Correção de comportamento onde o modal fechava prematuramente antes da confirmação. Implementada exibição correta de mensagens de sucesso/erro e bloqueio de fechamento acidental.
- **Integração Dashboard Utente**:
    - **Acesso Rápido**: Adicionado modal de submissão direto via "Nova Reclamação" em QuickActions.
    - **UX Simplificada**: Removido botão redundante "Minhas Reclamações" e coluna de ações da tabela de resumo para interface mais limpa.
    - **Atualização em Tempo Real**: Recarregamento automático de estatísticas e lista de submissões após nova submissão com sucesso.
- **Backend (GrievanceController)**: Refinamento na lógica de associação de usuários. O sistema agora preenche automaticamente dados de contato e associa o `user_id` para usuários autenticados, mantendo a consistência dos dados.

## Correções e Melhorias (12/12/2025)

- **Modal de Submissão**: Correção de comportamento onde o modal fechava prematuramente antes da confirmação. Implementada exibição correta de mensagens de sucesso/erro e bloqueio de fechamento acidental.
- **Integração Dashboard Utente**:
    - **Acesso Rápido**: Adicionado modal de submissão direto via "Nova Reclamação" em QuickActions.
    - **UX Simplificada**: Removido botão redundante "Minhas Reclamações" e coluna de ações da tabela de resumo para interface mais limpa.
    - **Atualização em Tempo Real**: Recarregamento automático de estatísticas e lista de submissões após nova submissão com sucesso.
- **Backend (GrievanceController)**: Refinamento na lógica de associação de usuários. O sistema agora preenche automaticamente dados de contato e associa o `user_id` para usuários autenticados, mantendo a consistência dos dados.

## Novas funcionalidades (13/12/2025)

- **Menu Unificado com Links Diretos**: Substituição do link genérico "Dashboard" por links específicos para cada papel (Admin, Director, Gestor, PCA, Técnico, Utente) no menu lateral, facilitando a navegação direta para os painéis apropriados.
- **Melhoria na UX de Navegação**: Usuários com múltiplos papéis ou permissões podem ter acesso facilitado aos seus respectivos dashboards através de rotas explícitas no menu.

## Melhorias Visuais e UX (13/12/2025 - Tarde)

### Dashboard Admin - Redesign Completo
- **Cards de Estatísticas Premium**: Redesign completo dos 4 cards principais (Usuários, Departamentos, Projectos, Activos) com:
  - Fundo branco/dark mode (não mais gradientes sólidos)
  - Ícones com gradientes coloridos e sombras matching
  - Números muito grandes (text-4xl) para melhor visualização
  - Badges de status com ícones contextuais
  - Background blur decorativo que expande no hover
  - Animações suaves (elevação, scale e rotate nos ícones)
  - Sombras coloridas que aumentam no hover

- **Ações Rápidas Melhoradas**: Cards de navegação rápida com:
  - Background gradient sutil por cor
  - Ícones maiores (h-14 w-14) com efeito 3D
  - Títulos em bold com descrições detalhadas
  - Animação de rotação leve nos ícones ao hover
  - Setas que se movem 2x mais no hover
  - Sombras XL coloridas matching o tema

- **Widget de Distribuição de Usuários Redesenhado**: 
  - Cards individuais para cada role (5 tipos)
  - Ícones SVG únicos e contextuais:
    - 👤 Utentes (azul)
    - ⚙️ Técnicos (âmbar)
    - 👥 Gestores (emerald)
    - 🏆 Directores (índigo)
    - 🛡️ PCA (roxo)
  - Gradientes coloridos por tipo de usuário
  - Números em destaque (text-2xl)
  - Borders coloridas matching
  - Hover aumenta intensidade do background
  - Contadores dinâmicos conectados ao banco de dados

### Seção de Boas-Vindas Padronizada
- **Todos os Dashboards (6 roles)**: Implementada seção "Bem-vindo(a), [Nome]!" com:
  - Fundo 100% transparente (removidos gradientes coloridos)
  - Tipografia responsiva (text-2xl → text-4xl)
  - Suporte completo dark mode
  - Consistência visual em todos os painéis:
    - Admin/SuperAdmin ✅
    - Gestor (Manager) ✅ (anteriormente não tinha)
    - Director ✅
    - PCA ✅
    - Técnico ✅
    - Utente ✅

### CRUD - Design Moderno e Dinâmico
- **Departamentos**: 
  - Header com gradiente esmeralda
  - Cards com hover effects 3D
  - Formulários com campos arredondados
  - Ícones de validação inline
  
- **Projectos**:
  - Tabela moderna com header gradient
  - Avatares circulares
  - Badges coloridos para status
  - Empty states elegantes
  
- **Usuários**:
  - Header com gradiente laranja primário
  - Campo de Departamento obrigatório para: Técnico, Director, Gestor, PCA
  - Avatares com gradiente
  - Badges de role com bordas coloridas
  - Validação frontend e backend integrada

### Sistema de Cores Primárias
- **Paleta Consistente Aplicada**:
  - Primary (laranja): Cards de usuários e admin
  - Emerald (verde): Departamentos
  - Purple (roxo): Projectos
  - Amber (âmbar): Activos e técnicos
  - Blue, Indigo, Purple: Distribuição de roles

### Correções Técnicas
- **Role "Gestor"**: Corrigido nome do role de "Gestor de Reclamações" para "Gestor" em:
  - AdminDashboardController (queries de contagem)
  - UserController (validação de departamento)
  - Users/Create.vue e Edit.vue (campo departamento)
  - Agora mostra corretamente 9 gestores no widget
  
- **Dashboard Manager**: Corrigido erro `Cannot read properties of undefined (reading 'name')` usando `$page.props.auth?.user?.name`

### Dark Mode
- **100% Compatível**: Todos os cards, badges, backgrounds e textos adaptam-se perfeitamente ao dark mode
- Classes consistentes: `dark:bg-gray-800`, `dark:text-white`, `dark:border-gray-700`

### Performance
- **Build Otimizado**: Assets compilados com sucesso (média 7.5s)
- **Sem Breaking Changes**: Funcionalidades existentes mantidas
- **Responsividade**: Design adaptável mantido para mobile, tablet e desktop

## Melhorias Finais (13/12/2025 - Noite)

### Campo Departamento - Validação Refinada
- **Obrigatório apenas para**: Gestor e Técnico
- **Removido de**: Director, PCA (anteriormente era obrigatório)
- **Validação sincronizada**: Frontend (Vue.js) e Backend (Laravel)
- **Lógica clara**: Campo só aparece se role estiver em `['Técnico', 'Gestor']`

### user_id em Reclamações Autenticadas
- **Implementação**: user_id SEMPRE enviado quando utente está autenticado
- **Funciona para**:
  - ✅ Submissões identificadas (user_id + dados de contato)
  - ✅ Submissões anônimas (user_id registrado, mas dados de contato ocultos)
- **Benefícios**:
  - Utente vê todas suas reclamações no dashboard pessoal
  - Sistema mantém rastreamento completo
  - Privacidade garantida (dados públicos ocultos quando anônimo)
- **Validação**: Backend aceita `user_id` como `nullable|exists:users,id`
- **Prioridade**: `user_id` do request → `auth()->user()->id` → `null`

### Footer Reorganizado
- **Problema corrigido**: Removidas duplicações de seções SERVIÇOS e CONTACTOS
- **Estrutura atual**: 4 colunas únicas
  1. **FUNAE**: Logo + Descrição + Redes Sociais
  2. **SERVIÇOS**: Sugestão, Reclamação, Queixa
  3. **LINKS ÚTEIS**: Início, Login, Registar, Acompanhar (nova seção)
  4. **CONTACTOS**: Endereço, Email, Telefone
- **Melhorias**: Grid responsivo, transitions suaves, textos consistentes
- **Limpeza**: Removido link de debug "Teste Email"

### Documentação Técnica Criada
- ✅ `USER_ID_ANONYMOUS_LOGIC.md` - Explicação completa da lógica de anonimato
- ✅ `FIELD_DEPARTMENT_UPDATE.md` - Documentação do campo departamento
- ✅ `DASHBOARD_IMPROVEMENTS_SUMMARY.md` - Resumo do redesign
- ✅ `VISUAL_IMPROVEMENTS_SUMMARY.md` - Melhorias visuais
- ✅ `WELCOME_SECTION_UPDATE.md` - Seção Boas-Vindas

### Commits do PR #119
1. `4980df0` - 🎨 Redesign Premium Dashboard Admin
2. `5f17570` - 🔧 Campo Departamento (Gestor + Técnico)
3. `ba55afe` - ✨ Envio de user_id no formulário
4. `aa098bd` - 📝 Documentação user_id anônimo
5. `fcdfccd` - 🔧 Footer reorganizado
6. `2e4a334` - 🔧 Remover link Teste Email

**Status**: ✅ **Todas as funcionalidades implementadas, testadas e documentadas**