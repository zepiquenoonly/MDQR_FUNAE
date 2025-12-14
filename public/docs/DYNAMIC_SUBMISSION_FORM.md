# Formulário Dinâmico de Submissão

## Resumo
Implementado formulário dinâmico de submissão de queixas/reclamações/sugestões que se adapta automaticamente ao estado de autenticação do usuário.

## Comportamento

### 🔐 Usuário Logado
Quando o utilizador está autenticado no sistema:

**Passo 1 - Identificação:**
- ✅ Apresenta apenas opções: **Anónimo** ou **Identificado**
- ❌ NÃO apresenta campos de dados pessoais (nome, email, telefone, género)
- ℹ️ Mostra mensagem informativa com os dados do usuário

**Mensagens exibidas:**

1. **Se escolher "Identificado":**
   ```
   📘 Submissão Identificada
   Seus dados pessoais serão utilizados a partir da sua conta: 
   [Nome do Usuário] ([email@exemplo.com])
   ```

2. **Se escolher "Anónimo":**
   ```
   🛡️ Submissão Anónima
   A sua identidade será protegida. Seus dados de conta não serão 
   associados a esta submissão. No entanto, não poderemos contactá-lo 
   directamente sobre o progresso da sua submissão.
   ```

**Dados utilizados:**
- Sistema pega automaticamente da **sessão do usuário**:
  - Nome completo
  - Email
  - Telefone
  - Género

### 👤 Usuário NÃO Logado
Quando o utilizador acessa sem autenticação:

**Passo 1 - Identificação:**
- ✅ Apresenta opções: **Anónimo** ou **Identificado**
- ✅ Se identificado: mostra formulário completo de dados pessoais
- ✅ Se anónimo: pode optar por fornecer dados (checkbox opcional)

**Campos do formulário (quando aplicável):**
- Nome Completo (obrigatório)
- Email (obrigatório)
- Telefone (opcional)
- Género (obrigatório)

## Alterações Realizadas

### 1. Frontend - ComplaintForm.vue

#### Template:
```vue
<!-- Apenas para usuários NÃO logados -->
<div v-if="!authUser && (!formData.is_anonymous || showOptionalContact)">
    <!-- Campos de dados pessoais -->
</div>

<!-- Informação para usuários logados identificados -->
<div v-if="authUser && !formData.is_anonymous">
    <!-- Mensagem informativa -->
</div>
```

#### Mensagem de Anonimato:
- Adaptada para diferenciar entre usuários logados e não logados
- Checkbox de "fornecer dados" apenas aparece para não logados

#### Validação:
```javascript
if (currentStep.value === 1) {
    // Para usuários NÃO logados: validar dados pessoais
    if (!authUser.value && (!formData.value.is_anonymous || showOptionalContact.value)) {
        // Validar nome, email, género
    }
    // Para usuários logados: não valida (dados vêm da sessão)
}
```

### 2. Backend - GrievanceController.php

#### Lógica de Dados:
```php
$user = auth()->user();
$isAnonymous = $validated['is_anonymous'] ?? false;

// Se usuário está logado
if ($user) {
    $grievanceData['user_id'] = $user->id;
    
    // Se identificado, usar dados da conta
    if (!$isAnonymous) {
        $grievanceData['contact_name'] = $user->name;
        $grievanceData['contact_email'] = $user->email;
        $grievanceData['contact_phone'] = $user->phone;
        $grievanceData['gender'] = $user->gender;
    }
} else {
    // Se NÃO logado, usar dados do formulário
    $grievanceData['contact_name'] = $validated['contact_name'] ?? null;
    $grievanceData['contact_email'] = $validated['contact_email'] ?? null;
    // ...
}
```

## Fluxo de Dados

### Cenário 1: Usuário Logado + Identificado
```
1. Usuário abre formulário → Sistema detecta autenticação
2. Step 1: Apenas escolhe "Identificado"
3. Sistema pega dados automaticamente da sessão
4. Step 2: Preenche detalhes da submissão
5. Backend: Associa user_id e dados pessoais da conta
```

### Cenário 2: Usuário Logado + Anónimo
```
1. Usuário abre formulário → Sistema detecta autenticação
2. Step 1: Escolhe "Anónimo"
3. Não fornece dados pessoais
4. Step 2: Preenche detalhes da submissão
5. Backend: Associa user_id MAS sem dados de contacto
   (permite rastreamento no dashboard, mas mantém anonimato)
```

### Cenário 3: Usuário NÃO Logado + Identificado
```
1. Usuário abre formulário → Sistema detecta não autenticado
2. Step 1: Escolhe "Identificado" e preenche formulário completo
3. Fornece: nome, email, telefone, género
4. Step 2: Preenche detalhes da submissão
5. Backend: user_id = null, usa dados fornecidos no formulário
```

### Cenário 4: Usuário NÃO Logado + Anónimo
```
1. Usuário abre formulário → Sistema detecta não autenticado
2. Step 1: Escolhe "Anónimo"
3. Pode optar por fornecer dados (checkbox)
4. Step 2: Preenche detalhes da submissão
5. Backend: user_id = null, sem dados de contacto
```

## Vantagens

### Para Usuários Logados:
✅ Processo mais rápido (sem preencher dados repetidos)
✅ Dados sempre corretos (vêm da conta)
✅ Possibilidade de submissões anônimas mantendo rastreabilidade
✅ Visualização de todas suas submissões no dashboard

### Para Usuários Não Logados:
✅ Podem fazer submissões sem criar conta
✅ Opção de anonimato completo
✅ Flexibilidade de fornecer dados opcionalmente

### Para o Sistema:
✅ Melhor gestão de dados
✅ Redução de erros de digitação
✅ Rastreabilidade melhorada
✅ Experiência do usuário otimizada

## Validações

### Frontend:
- Usuário logado: **não valida** campos de dados pessoais no Step 1
- Usuário não logado identificado: **valida** nome, email, género
- Usuário não logado anônimo com dados opcionais: **valida** se checkbox marcado

### Backend:
- Sempre valida tipo, projeto, localização
- Dados pessoais são opcionais no request
- Prioriza dados da sessão quando usuário autenticado

## Arquivos Modificados

1. **resources/js/Components/UtenteDashboard/ComplaintForm.vue**
   - Template adaptado com condições v-if baseadas em authUser
   - Validação atualizada no script
   - Mensagens contextuais adicionadas

2. **app/Http/Controllers/GrievanceController.php**
   - Método store() atualizado
   - Lógica de priorização de dados da sessão
   - Tratamento diferenciado para anônimo vs identificado

## Impacto no Banco de Dados

Nenhuma alteração na estrutura do banco de dados foi necessária.

Campos utilizados (já existentes):
- `user_id` - ID do usuário (null se não logado)
- `is_anonymous` - Flag de anonimato
- `contact_name` - Nome (da sessão ou formulário)
- `contact_email` - Email (da sessão ou formulário)
- `contact_phone` - Telefone (da sessão ou formulário)
- `gender` - Género (da sessão ou formulário)

## Compatibilidade

✅ Mantém compatibilidade com submissões existentes
✅ Não quebra funcionalidade atual
✅ Funciona tanto para usuários logados quanto não logados
✅ Respeita preferências de anonimato

## Status

✅ Frontend atualizado
✅ Backend atualizado
✅ Validações adaptadas
✅ Mensagens contextuais implementadas
✅ Testado e funcional

## Data de Implementação
13 de Dezembro de 2025
