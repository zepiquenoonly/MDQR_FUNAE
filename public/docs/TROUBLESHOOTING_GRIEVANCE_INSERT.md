# Troubleshooting - Problema de Inserção de Grievance

## Problema Reportado
O controller não está inserindo os dados na tabela `grievances` quando o usuário está logado.

## Análise Realizada

### 1. Estrutura da Tabela ✅
Todos os campos necessários existem na tabela:
- ✅ `user_id` (nullable, FK para users)
- ✅ `project_id` (nullable, FK para projects)  
- ✅ `type` (enum: grievance, complaint, suggestion)
- ✅ `description` (text, **NOT NULL**)
- ✅ `contact_name` (nullable)
- ✅ `contact_email` (nullable)
- ✅ `contact_phone` (nullable)
- ✅ `gender` (nullable)
- ✅ `is_anonymous` (boolean, default: false)
- ✅ Campos de localização (province, district, etc.)

### 2. Modelo Grievance ✅
- Todos os campos estão no `$fillable`
- Observer `boot()` gera automaticamente o `reference_number`
- Observer define `submitted_at` automaticamente se não fornecido

### 3. Controller - Alterações Implementadas ✅

#### Lógica de Dados da Sessão:
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
}
```

#### Correção Aplicada:
- Campo `description` agora tem valor padrão: `'Sem descrição fornecida.'`
- Logs adicionados para debug antes e depois de criar o Grievance

### 4. Possíveis Causas do Problema

#### A. Campo Description Vazio
**Sintoma:** Erro de banco de dados porque `description` é NOT NULL
**Solução Aplicada:** ✅ Valor padrão adicionado
```php
'description' => $validated['description'] ?? 'Sem descrição fornecida.'
```

#### B. Transação Não Commitada
**Sintoma:** Dados não aparecem no banco
**Status:** Código tem `DB::commit()` - ✅ OK

#### C. Exceção Sendo Lançada
**Sintoma:** Try-catch captura erro mas não loga adequadamente
**Solução Aplicada:** ✅ Logs de debug adicionados

#### D. Validação Falhando
**Sintoma:** Validação rejeita dados antes de chegar ao insert
**Como Verificar:** Checar logs do Laravel

## Como Testar

### 1. Verificar Logs
```bash
tail -f storage/logs/laravel.log
```

Procurar por:
- `"Criando grievance com dados:"`
- `"Grievance criada com sucesso:"`
- `"Erro ao submeter submissão:"`

### 2. Teste Manual via Tinker
```php
php artisan tinker

$user = User::first();
Auth::login($user);

$data = [
    'user_id' => $user->id,
    'project_id' => 1,
    'type' => 'complaint',
    'description' => 'Teste',
    'province' => 'Maputo',
    'status' => 'submitted',
    'priority' => 'medium',
    'is_anonymous' => false,
    'contact_name' => $user->name,
    'contact_email' => $user->email,
];

$grievance = \App\Models\Grievance::create($data);
echo "ID: " . $grievance->id;
echo "Ref: " . $grievance->reference_number;
```

### 3. Verificar Resposta da API
No frontend, adicionar log:
```javascript
console.log('Response:', response);
console.log('Success:', response.success);
console.log('Reference:', response.reference_number);
```

## Checklist de Debugging

- [ ] Verificar se logs estão sendo gerados
- [ ] Confirmar que `auth()->user()` retorna usuário válido
- [ ] Verificar se validação está passando
- [ ] Checar se há erro de foreign key (project_id inválido)
- [ ] Confirmar que transação está sendo commitada
- [ ] Verificar se há observers/listeners interferindo
- [ ] Checar permissões do usuário logado

## Logs Implementados

### Log 1: Antes de Criar
```php
Log::info('Criando grievance com dados:', [
    'user_logged_in' => $user ? true : false,
    'user_id' => $grievanceData['user_id'] ?? null,
    'is_anonymous' => $isAnonymous,
    'has_contact_data' => isset($grievanceData['contact_name']),
    'data' => $grievanceData
]);
```

### Log 2: Após Criar com Sucesso
```php
Log::info('Grievance criada com sucesso:', [
    'id' => $grievance->id,
    'reference_number' => $grievance->reference_number
]);
```

### Log 3: Em Caso de Erro
```php
Log::error('Erro ao submeter submissão: ' . $e->getMessage(), [
    'exception' => $e,
    'user_id' => auth()->id(),
    'error_type' => get_class($e),
]);
```

## Próximos Passos

1. **Executar a aplicação** e tentar criar uma submissão com usuário logado
2. **Verificar logs** em `storage/logs/laravel.log`
3. **Enviar logs encontrados** para análise mais detalhada
4. **Verificar banco de dados** diretamente:
   ```sql
   SELECT * FROM grievances ORDER BY id DESC LIMIT 5;
   ```

## Arquivos Modificados

- `app/Http/Controllers/GrievanceController.php` - Lógica de dados da sessão + logs
- `resources/js/Components/UtenteDashboard/ComplaintForm.vue` - Formulário dinâmico

## Status

🔍 Alterações implementadas, aguardando teste para confirmar se resolve o problema.
