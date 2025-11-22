# Implementação de Tipos de Grievance - Solicitação de Funcionalidade

## Descrição do Problema

O modelo Grievance atual no sistema FUNAE não está adequadamente estruturado para lidar com os três tipos de grievance necessários: **RECLAMACAO** (Reclamação), **QUEIXA** (Queixa), e **SUGESTAO** (Sugestão).

### Problemas Identificados:

1. **Campo de Tipo em Falta**: O modelo Grievance possui apenas um campo de string genérico `category` em vez de um campo enum adequado para tipos de grievance
2. **Categorização Inconsistente**: As categorias actuais são categorias genéricas baseadas em serviços (ex: "Serviços Públicos", "Infraestrutura") em vez dos tipos específicos necessários
3. **Validação de Tipo em Falta**: Não há validação a nível de base de dados para garantir que apenas os três tipos válidos são utilizados
4. **Lógica Específica por Tipo em Falta**: Não há componentes de lógica ou interface do utilizador para processar diferentes fluxos de trabalho baseados no tipo de grievance

## Estrutura Actual da Base de Dados

**Ficheiro**: `database/migrations/2025_11_14_121054_create_grievances_table.php`

```php
$table->string('category'); // ❌ Actualmente apenas uma string
```

**Esperado**: 
```php
$table->enum('type', ['RECLAMACAO', 'QUEIXA', 'SUGESTAO']); // ✅ Deve ser enum
```

## Avaliação do Impacto

- **Integridade de Dados**: Não há garantia de que os grievances são categorizados adequadamente por tipo
- **Lógica de Negócio**: Não é possível implementar fluxos de trabalho específicos por tipo ou encaminhamento
- **Experiência do Utilizador**: Não é possível fornecer interfaces ou processos adequados por tipo
- **Relatórios**: Não é possível gerar estatísticas precisas por tipo de grievance
- **Integração do Sistema**: Sistemas externos não conseguem identificar de forma fiável os tipos de grievance

## Solução Proposta

### 1. Migração da Base de Dados
Criar uma nova migração para adicionar o campo enum `type`:

```php
// Nova migração: add_grievance_types_enum.php
Schema::table('grievances', function (Blueprint $table) {
    $table->enum('type', ['RECLAMACAO', 'QUEIXA', 'SUGESTAO'])->default('RECLAMACAO')->after('category');
});
```

### 2. Actualizações do Modelo
**Ficheiro**: `app/Models/Grievance.php`

- Adicionar `type` ao array fillable
- Adicionar casting para o enum
- Adicionar constantes de validação
- Adicionar métodos e scopes específicos por tipo

### 3. Actualizações da Factory
**Ficheiro**: `database/factories/GrievanceFactory.php`

- Actualizar para usar os novos valores enum em vez de categorias genéricas
- Adicionar estados específicos por tipo da factory

### 4. Actualizações do Frontend
Actualizar componentes Vue para:
- Exibir campos de formulário apropriados baseados no tipo
- Mostrar ícones e etiquetas específicas por tipo
- Implementar regras de validação específicas por tipo

### 5. Lógica de Negócio
Implementar por tipo específico:
- Lógica de encaminhamento e atribuição
- Determinação de prioridade
- Fluxos de trabalho de processamento
- Modelos de notificação

## Prioridade de Implementação

**ALTA PRIORIDADE** - Esta é uma funcionalidade central do sistema que afecta:
- Consistência de dados
- Fluxo de trabalho do utilizador
- Fiabilidade do sistema
- Conformidade com requisitos de negócio

## Critérios de Aceitação

- [ ] Migração de base de dados adiciona campo enum `type` com três valores
- [ ] Validação do modelo garante que apenas tipos válidos são aceites
- [ ] Factory gera grievances com distribuição adequada por tipo
- [ ] Formulários do frontend suportam os três tipos
- [ ] Fluxos de trabalho de processamento específicos por tipo são implementados
- [ ] Grievances existentes podem ser migrados/classificados adequadamente

## Notas Técnicas

- O campo `category` deve ser mantido para retrocompatibilidade e classificação detalhada
- Considere adicionar caminho de migração para dados existentes
- Type deve ser campo obrigatório para novas submissões
- Implemente restrições adequadas da base de dados e índices

## Ficheiros Relacionados para Actualizar

- `database/migrations/2025_11_14_121054_create_grievances_table.php`
- `app/Models/Grievance.php`
- `database/factories/GrievanceFactory.php`
- Componentes Vue do frontend para formulários de grievance
- Controladores de API e regras de validação
- Modelos de notificação e email

## Recomendação de Timeline

Esta funcionalidade deve ser implementada dentro do próximo sprint para manter a integridade de dados e consistência do sistema.