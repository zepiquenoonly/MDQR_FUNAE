# 📋 Lógica de user_id em Submissões Anônimas

## 🎯 Comportamento Implementado

### ✅ SEMPRE Registrar user_id se Autenticado

Quando um **utente autenticado** submete uma reclamação, o `user_id` é **SEMPRE** registrado, **independentemente** de escolher submissão anônima ou identificada.

---

## 📊 Cenários de Submissão

### 1️⃣ Utente Autenticado + Submissão Identificada
```
┌─────────────────────────────────────────┐
│ Usuário: João Silva (ID: 5)            │
│ is_anonymous: false                     │
├─────────────────────────────────────────┤
│ ✅ user_id: 5                           │
│ ✅ contact_name: "João Silva"           │
│ ✅ contact_email: "joao@email.com"      │
│ ✅ contact_phone: "+258 84 123 4567"    │
└─────────────────────────────────────────┘

Dashboard Utente: ✅ Vê a reclamação
Público: ✅ Dados visíveis
```

### 2️⃣ Utente Autenticado + Submissão Anônima
```
┌─────────────────────────────────────────┐
│ Usuário: João Silva (ID: 5)            │
│ is_anonymous: true                      │
├─────────────────────────────────────────┤
│ ✅ user_id: 5         ← SEMPRE SALVO!  │
│ ❌ contact_name: null                   │
│ ❌ contact_email: null                  │
│ ❌ contact_phone: null                  │
└─────────────────────────────────────────┘

Dashboard Utente: ✅ Vê a reclamação
Público: ❌ Identidade oculta
```

### 3️⃣ Guest (Não Autenticado) + Submissão Anônima
```
┌─────────────────────────────────────────┐
│ Usuário: Visitante (sem login)         │
│ is_anonymous: true                      │
├─────────────────────────────────────────┤
│ ❌ user_id: null                        │
│ ❌ contact_name: null                   │
│ ❌ contact_email: null                  │
│ ❌ contact_phone: null                  │
└─────────────────────────────────────────┘

Dashboard Utente: ❌ Não vê (sem login)
Público: ❌ Identidade oculta
Rastreamento: ✅ Apenas por código
```

---

## 🔍 Diferença: Anônimo vs Não Identificável

| Aspecto | Anônimo (Autenticado) | Guest |
|---------|----------------------|-------|
| **user_id** | ✅ Salvo | ❌ Null |
| **Dashboard** | ✅ Visível | ❌ Não acessível |
| **Dados Contato** | ❌ Ocultos | ❌ Não fornecidos |
| **Rastreamento** | ✅ Código + user_id | ✅ Apenas código |
| **Notificações** | ✅ Via sistema interno | ❌ Não recebe |

---

## 💡 Benefícios da Implementação

### Para o Utente:
✅ **Anonimato Garantido**: Dados pessoais não são expostos  
✅ **Rastreamento Privado**: Vê suas reclamações no dashboard  
✅ **Notificações**: Recebe atualizações via sistema  
✅ **Histórico**: Mantém registro de todas submissões  

### Para o Sistema:
✅ **Estatísticas**: Conta reclamações por utente  
✅ **Qualidade**: Identifica padrões de uso  
✅ **Suporte**: Admin pode contactar via sistema interno  
✅ **Auditoria**: Rastreamento completo mantido  

---

## 🔧 Implementação Técnica

### Frontend (ComplaintForm.vue)

```javascript
// Acessa usuário autenticado do Inertia
const page = usePage()
const authUser = computed(() => page.props.auth?.user || null)

// FormData SEMPRE inclui user_id se existir
const formData = ref({
    // ... outros campos
    is_anonymous: props.isAnonymous,
    user_id: authUser.value?.id || null  // ← Sempre enviado
})

// Na submissão
const formDataToSend = new FormData()
Object.keys(formData.value).forEach(key => {
    let value = formData.value[key]
    if (value !== null && value !== '' && value !== undefined) {
        formDataToSend.append(key, value)  // user_id incluído aqui
    }
})
```

### Backend (GrievanceController.php)

```php
// Validação aceita user_id
$validated = $request->validate([
    'user_id' => 'nullable|exists:users,id',
    'is_anonymous' => 'sometimes|boolean',
    // ... outros campos
]);

// Dados da reclamação
$grievanceData = [
    'is_anonymous' => $validated['is_anonymous'] ?? false,
    
    // user_id é SEMPRE associado se disponível
    'user_id' => $validated['user_id'] ?? auth()->user()?->id ?? null,
    
    // Dados de contato (podem estar vazios se anônimo)
    'contact_name' => $validated['contact_name'] ?? null,
    'contact_email' => $validated['contact_email'] ?? null,
    'contact_phone' => $validated['contact_phone'] ?? null,
];
```

---

## 🎨 UX no Formulário

### Toggle Anônimo

```
┌──────────────────────────────────────────────┐
│  Submeter de forma anónima?                  │
│                                              │
│  ○ Não (Identificado)  ● Sim (Anónimo)      │
│                                              │
│  ℹ️  Mesmo anónimo, você poderá ver esta    │
│     reclamação no seu dashboard pessoal     │
└──────────────────────────────────────────────┘
```

### Campos de Contato

**Identificado:**
```
┌──────────────────────────────────────┐
│ Nome Completo * (obrigatório)        │
│ [João Silva                    ]     │
└──────────────────────────────────────┘
┌──────────────────────────────────────┐
│ Email * (obrigatório)                │
│ [joao@email.com                ]     │
└──────────────────────────────────────┘
```

**Anônimo:**
```
┌──────────────────────────────────────┐
│ 🔒 Dados pessoais ocultos            │
│                                      │
│ ✓ Identidade protegida               │
│ ✓ Reclamação visível no seu painel   │
└──────────────────────────────────────┘
```

---

## 📊 Query de Verificação

### Ver reclamações anônimas de utente autenticado

```sql
SELECT 
    id,
    type,
    description,
    is_anonymous,
    user_id,
    contact_name,
    contact_email,
    created_at
FROM grievances
WHERE user_id = 5
  AND is_anonymous = 1
ORDER BY created_at DESC;
```

**Resultado esperado:**
```
+----+-----------+--------------+--------------+---------+--------------+---------------+
| id | type      | description  | is_anonymous | user_id | contact_name | contact_email |
+----+-----------+--------------+--------------+---------+--------------+---------------+
| 42 | complaint | Problema X   |            1 |       5 | NULL         | NULL          |
| 39 | grievance | Questão Y    |            1 |       5 | NULL         | NULL          |
+----+-----------+--------------+--------------+---------+--------------+---------------+
```

✅ **user_id = 5** (utente associado)  
✅ **contact_name = NULL** (identidade oculta)  
✅ **is_anonymous = 1** (flag de anonimato)

---

## 🔐 Segurança & Privacidade

### Garantias:
✅ Dados de contato **nunca são expostos** publicamente quando `is_anonymous = true`  
✅ Admin pode ver `user_id` mas não deve expor identidade  
✅ Técnicos veem apenas informações necessárias para resolução  
✅ Dashboard do utente mostra **apenas suas próprias** reclamações  

### Queries do Dashboard Utente:
```php
// Apenas reclamações do usuário autenticado
Grievance::where('user_id', auth()->id())
    ->orderBy('created_at', 'desc')
    ->get();
```

---

## ✅ Checklist de Implementação

- [x] Frontend envia `user_id` sempre que autenticado
- [x] Backend aceita e valida `user_id`
- [x] `user_id` registrado mesmo com `is_anonymous = true`
- [x] Dados de contato ocultos quando anônimo
- [x] Dashboard do utente filtra por `user_id`
- [x] Documentação atualizada
- [x] Comentários explicativos no código
- [x] Build e testes realizados

---

## 📦 Commits

**Commits relacionados:**
- `ba55afe` - Envio inicial de user_id no formulário
- `aa098bd` - Documentação da lógica de anonimato

---

**Status:** ✅ **IMPLEMENTADO E DOCUMENTADO**  
**Data:** 13 de Dezembro de 2025  
**PR:** #119  
**Build:** 5.25s ✅
