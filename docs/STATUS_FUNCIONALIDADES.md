# Estado das Funcionalidades por Fluxo

## Fluxo 1: Submissão de Reclamação pelo Utente

| Etapa | Actor  | Acção | Estado |
|-------|--------|------|--------|
| 1     | Utente | Acede à plataforma via web/app | ✅ Implementado |
| 2     | Utente | Escolhe entre submissão anónima ou identificada | ✅ Implementado |
| 3     | Utente | Preenche o formulário (tipo, descrição, localização, categoria, anexos) | ✅ Implementado |
| 4     | Sistema| Valida os dados do formulário | ✅ Implementado |
| 5     | Sistema| Gera código único de rastreio | ✅ Implementado |
| 6     | Sistema| Envia notificação de confirmação (email/SMS) | ✅ Implementado |
| 7     | Sistema| Aloca automaticamente a reclamação a um técnico | ✅ Implementado |
| 8     | Sistema| Notifica o Gestor e Técnico alocado | ✅ Implementado |

## Fluxo 2: Triagem e Atribuição de Reclamação

| Etapa | Actor  | Acção | Estado |
|-------|--------|------|--------|
| 1     | Gestor | Acede ao painel de gestão e visualiza novas reclamações | ✅ Implementado |
| 2     | Gestor | Analisa a descrição, categoria e anexos | ✅ Implementado |
| 3     | Gestor | Define o nível de prioridade | ✅ Implementado |
| 4     | Gestor | Troca a atribuição automática do técnico (se necessário) | ✅ Implementado |
| 5     | Sistema| Notifica o técnico reatribuído | ✅ Implementado |
| 6     | Gestor | Encaminha para o director em casos críticos | ✅ Implementado |

## Fluxo 3: Resolução da Reclamação pelo Técnico

| Etapa | Actor    | Acção | Estado |
|-------|----------|------|--------|
| 1     | Técnico  | Recebe notificação da reclamação atribuída | ✅ Implementado |
| 2     | Técnico  | Acede ao painel e visualiza detalhes | ✅ Implementado |
| 3     | Técnico  | Altera o estado para 'Em Andamento' | ✅ Implementado |
| 4     | Sistema  | Notifica o utente sobre mudança de estado | ✅ Implementado |
| 5     | Técnico  | Executa acções corretivas | ✅ Implementado |
| 6     | Técnico  | Insere actualizações, comentários e evidências | ✅ Implementado |
| 7     | Técnico  | Solicita ao Gestor a conclusão do processo | ✅ Implementado |
| 8     | Gestor   | Revê a solicitação e marca como 'Resolvido' | ✅ Implementado |
| 9     | Sistema  | Notifica o utente sobre a resolução | ✅ Implementado |

## Fluxo 4: Acompanhamento da Reclamação pelo Utente

| Etapa | Actor  | Acção | Estado |
|-------|--------|------|--------|
| 1     | Utente | Acede à plataforma e selecciona 'Acompanhar Reclamação' | ✅ Implementado |
| 2     | Utente | Insere o código de rastreio | ✅ Implementado |
| 3     | Sistema| Exibe o estado actual da reclamação | ✅ Implementado |
| 4     | Utente | Visualiza o histórico de actualizações e comentários | ✅ Implementado |
| 5     | Utente | Consulta anexos e evidências de resolução | ✅ Implementado |

## Fluxo 5: Geração de Relatórios e Estatísticas

| Etapa | Actor        | Acção | Estado |
|-------|-------------|------|--------|
| 1     | PCA/Gestor  | Acede à secção de Relatórios e Estatísticas | ✅ Implementado |
| 2     | PCA/Gestor  | Define filtros (período, tipo, departamento, estado) | ✅ Implementado |
| 3     | Sistema     | Gera dashboard com indicadores e gráficos | ✅ Implementado |
| 4     | PCA/Gestor  | Analisa gráficos e indicadores | ✅ Implementado |
| 5     | PCA/Gestor  | Exporta relatório em PDF/Excel | 🚧 Parcial (exportação avançada em backlog) |

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

| Evento | Estado |
|--------|--------|
| Confirmação de submissão (utente) | ✅ |
| Alteração de estado (utente) | ✅ |
| Conclusão do processo (utente) | ✅ |
| Nova reclamação atribuída (gestor/técnico) | ✅ |
| Solicitação de conclusão pendente (gestor/técnico) | ✅ |
| Alertas de prazos (gestor/técnico) | 🚧 Parcial |
| Comentários/actualizações (gestor/técnico) | ✅ |

## Backlog (Por Implementar)

- Dashboard de monitorização em tempo real (WebSockets)
- Exportação avançada de relatórios (PDF/Excel customizado)
- Relatórios agendados por email
- Análise preditiva e alertas automáticos
- Filtros avançados e comparação de períodos
- Integração com sistemas externos (ex: atendimento externo, ERPs)
- Acessibilidade avançada (atalhos, contraste, navegação por teclado, textos alternativos)
- Testes E2E automatizados e cobertura de testes unitários
- Onboarding guiado e documentação multilíngue
- Feature flags para activação/desactivação de módulos
- Dashboard de PCA com análise preditiva e relatórios agendados
- Alertas automáticos para métricas críticas (SLA, prazos)
- Comparação entre períodos nos relatórios
- Integração com SMS gateway alternativos
- Relatórios customizados por perfil (Gestor, PCA, Técnico)
- Melhorias de UX: toast notifications, loading spinners, pop-ups de confirmação
- Refino de validações finais e tratamento de excepções
- Integração com sistemas de autenticação externa (SSO, OAuth)
- Auditoria detalhada de acções e logs de sistema
- Painel de administração para gestão de parâmetros do sistema

# Plano de Melhorias da Plataforma FUNAE

## Resumo Executivo
Baseado na reunião da equipe realizada, foram identificados vários problemas críticos de UI/UX, funcionalidades e consistência na plataforma de gestão de reclamações. O plano de melhorias é prioritizado para preparação da apresentação ao diretor até sexta-feira, com deadline do projeto na próxima semana.

## 🔴 Prioridades Críticas (Para apresentação ao diretor)

### 1. Padronização de Layout

- **Problemas adicionais identificados:**
    - Falta de padronização de cores e fontes entre páginas
    - Footer inconsistente ou ausente em algumas telas
    - Elementos desalinhados em diferentes resoluções/tamanhos de tela

- **Problemas adicionais identificados:**
    - Footer ausente em algumas páginas
    - Paleta de cores não segue identidade visual única
- [ ] Garantir que sidebar permaneça fixa durante scroll

- **Problemas adicionais identificados:**
    - Falta de validação clara de campos obrigatórios
    - Falta de confirmação visual após submissão bem-sucedida
    - Falta de loading/spinner durante operações demoradas
    - Falta de segmentação clara no formulário (passos/seções)
- **Problema**: Cor do footer inadequada

- **Problemas adicionais identificados:**
    - Falta de contraste adequado para acessibilidade
    - Elementos pequenos/difíceis de clicar em dispositivos móveis
    - Falta de textos alternativos em imagens e ícones
#### Tarefas:

- **Problemas adicionais identificados:**
    - Navegação inconsistente entre usuários autenticados e não autenticados
    - Falta de atalhos de teclado ou navegação facilitada

### 3. Flow de Submissão de Reclamações
- **Problema**: Botão "New Claim" redireciona para página ao invés de abrir modal
- **Impacto**: Experiência de usuário confusa e lenta
- **Solução**: Implementar modal direto para nova reclamação

#### Tarefas:
- [ ] Modificar botão "New Claim" para abrir modal de reclamação
- [ ] Remover necessidade de redirecionamento desnecessário
- [ ] Otimizar fluxo de navegação

## 🟡 Melhorias de Funcionalidades

### 4. Suporte a Gravação de Áudio
- **Problema**: Usuários com dificuldades de escrita não conseguem submeter reclamações
- **Impacto**: Acessibilidade limitada da plataforma
- **Solução**: Implementar gravação de áudio para reclamações

#### Tarefas:
- [ ] Adicionar componente de gravação de áudio
- [ ] Implementar submissão de arquivos de áudio (MP3, WAV)
- [ ] Criar interface para reprodução de áudio
- [ ] Integrar com sistema de attachments existente

### 5. Feedback Visual e Notificações
- **Problema**: Sem feedback após submissão de formulários
- **Impacto**: Usuário não sabe se ação foi bem-sucedida
- **Solução**: Implementar sistema de notificações e feedback visual

#### Tarefas:
- [ ] Implementar toast notifications para feedback
- [ ] Adicionar indicadores de loading em formulários
- [ ] Criar sistema de notificações por email
- [ ] Implementar pop-ups de confirmação

### 6. Sistema de Tracking Interno
- **Problema**: Tracking de reclamações abre em nova aba
- **Impacto**: Navegação confusa para usuários autenticados
- **Solução**: Implementar seção de tracking no dashboard

#### Tarefas:
- [ ] Criar seção de tracking no dashboard
- [ ] Remover redirecionamento para página externa
- [ ] Implementar visualização de status de reclamações

## 🟠 Melhorias Técnicas

### 7. Substituição de Emojis por Ícones
- **Problema**: Uso de emojis ao invés de biblioteca de ícones
- **Impacto**: Aparência não profissional
- **Solução**: Implementar biblioteca de ícones adequada

#### Tarefas:
- [ ] Identificar todos os emojis na plataforma
- [ ] Substituir por ícones de biblioteca (Lucide, Heroicons, etc.)
- [ ] Garantir consistência visual

### 8. Responsividade dos Cards de Estatísticas
- **Problema**: Cards de estatísticas muito apertados em telas pequenas
- **Impacto**: Usabilidade móvel comprometida
- **Solução**: Melhorar responsividade

#### Tarefas:
- [ ] Revisar CSS dos cards de estatísticas
- [ ] Implementar grid responsivo adequado
- [ ] Testar em diferentes tamanhos de tela

### 9. Melhorias no Dashboard de Técnicos

#### Tarefas:
- [ ] Clarificar quando evidências devem ser submetidas (início ou fim)
- [ ] Melhorar fluxo de atualizações de técnicos
- [ ] Implementar sistema de aprovação de conclusão
- [ ] Adicionar funcionalidade "Register Updates" melhorada

## 🟢 Melhorias de UX

### 10. Organização do Formulário de Reclamação
- **Problema**: Formulário não tem segmentação clara de informações
- **Impacto**: Confusão para usuários ao preencher
- **Solução**: Reorganizar formulários com seções claras

#### Tarefas:
- [ ] Dividir formulário em seções: dados pessoais, projeto, reclamação
- [ ] Implementar validação passo a passo
- [ ] Melhorar indicação de campos obrigatórios

### 11. Correção de Problemas de Navegação
- **Problema**: Usuários são redirecionados para login desnecessariamente
- **Impacto**: Frustração e perda de contexto
- **Solução**: Melhorar sistema de autenticação e navegação

#### Tarefas:
- [ ] Revisar middleware de autenticação
- [ ] Implementar manutenção de sessão adequada
- [ ] Melhorar fluxo de redirecionamentos

## 📅 Timeline

### Quinta-feira (26/11/2025)
- [ ] Finalizar padronização de layout
- [ ] Implementar novos componentes de footer
- [ ] Corrigir flow de submissão de reclamações
- [ ] Testar todas as melhorias implementadas

### Sexta-feira (27/11/2025)
- **APRESENTAÇÃO AO DIRETOR**
- [ ] Preparar apresentação com todas as melhorias
- [ ] Testar funcionalidades em ambiente de demonstração
- [ ] Documentar melhorias implementadas

### Próxima Semana (Deadline do Projeto)
- [ ] Finalizar todas as melhorias técnicas
- [ ] Implementar funcionalidades restantes
- [ ] Realizar testes finais
- [ ] Deploy da versão final

## 🎯 Critérios de Sucesso

### Para Apresentação ao Diretor
- ✅ Layout consistente em todas as telas
- ✅ Logo FUNAI presente em todas as páginas
- ✅ Sidebar fixa e funcional
- ✅ Flow de reclamações otimizado
- ✅ Interface profissional sem emojis

### Para Finalização do Projeto
- ✅ Sistema de áudio implementado
- ✅ Notificações funcionais
- ✅ Responsividade completa
- ✅ Código componentizado
- ✅ Testes funcionais passarem

## ⚠️ Riscos e Considerações

1. **Coordenação da Equipe**: Necessário melhorar comunicação entre membros
2. **Tempo Limitado**: Priorizar funcionalidades críticas para apresentação
3. **Compatibilidade**: Testar em diferentes dispositivos e navegadores
4. **Performance**: Manter otimização durante melhorias

## 📋 Checklist de Preparação para Apresentação

- [ ] Verificar todas as páginas com layout padronizado
- [ ] Testar flow completo de submissão de reclamação
- [ ] Confirmar logo FUNAI em todas as telas
- [ ] Validar sidebar fixa em todas as páginas
- [ ] Verificar que não há emojis na interface
- [ ] Testar responsividade em diferentes dispositivos
- [ ] Preparar demonstrações das funcionalidades principais
- [ ] Documentar melhorias implementadas
