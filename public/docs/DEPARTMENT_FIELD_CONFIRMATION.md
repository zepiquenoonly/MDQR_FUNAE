# ✅ Confirmação: Campo Departamento para Gestor/Manager

## 📋 Status Actual

O campo **Departamento** já está **corretamente configurado** para aparecer como **obrigatório** para o role **"Gestor"** (Manager).

---

## 🔍 Configuração nos Arquivos

### 1. Create.vue (Criação de Usuário)
**Arquivo:** `resources/js/Pages/Admin/Users/Create.vue`  
**Linha 166:**

```javascript
// Roles que requerem departamento
const rolesWithDepartment = ['Técnico', 'Director', 'Gestor', 'PCA'];
                                                         ^^^^^^^^
                                                         ✅ INCLUÍDO
```

---

### 2. Edit.vue (Edição de Usuário)
**Arquivo:** `resources/js/Pages/Admin/Users/Edit.vue`  
**Linha 142:**

```javascript
// Roles que requerem departamento
const rolesWithDepartment = ['Técnico', 'Director', 'Gestor', 'PCA'];
                                                         ^^^^^^^^
                                                         ✅ INCLUÍDO
```

---

### 3. UserController.php (Backend - Store)
**Arquivo:** `app/Http/Controllers/UserController.php`  
**Linha 79:**

```php
$rolesWithDepartment = ['Técnico', 'Director', 'Gestor', 'PCA'];
                                                ^^^^^^^^
                                                ✅ INCLUÍDO
```

---

### 4. UserController.php (Backend - Update)
**Arquivo:** `app/Http/Controllers/UserController.php`  
**Linha 140:**

```php
$rolesWithDepartment = ['Técnico', 'Director', 'Gestor', 'PCA'];
                                                ^^^^^^^^
                                                ✅ INCLUÍDO
```

---

## 🎯 Comportamento Actual

### Quando o Admin seleciona o role "Gestor":

```
┌──────────────────────────────────────────┐
│  Role *                                  │
│  [Gestor] ◄─── Selecionado              │
└──────────────────────────────────────────┘
              ⬇️
┌──────────────────────────────────────────┐
│  Departamento * 🔴                       │
│  (Obrigatório para este role)            │
│  [Selecione um departamento ▼]           │
└──────────────────────────────────────────┘
       Campo aparece automaticamente
```

---

## ✅ Validação Backend

O backend valida que o departamento é obrigatório:

```php
'department_id' => [
    'nullable',
    'exists:departments,id',
    function ($attribute, $value, $fail) use ($request, $rolesWithDepartment) {
        if (in_array($request->role, $rolesWithDepartment) && empty($value)) {
            $fail('O departamento é obrigatório para o role selecionado.');
        }
    },
],
```

Se o admin tentar salvar um **Gestor** sem departamento, receberá erro:
```
❌ O departamento é obrigatório para o role selecionado.
```

---

## 📊 Roles com Campo de Departamento Obrigatório

| Role | Campo Departamento | Status |
|------|-------------------|--------|
| **Técnico** | ✅ Obrigatório | Configurado |
| **Director** | ✅ Obrigatório | Configurado |
| **Gestor** | ✅ Obrigatório | Configurado |
| **PCA** | ✅ Obrigatório | Configurado |
| Utente | ❌ Não aparece | N/A |
| Admin | ❌ Não aparece | N/A |
| Super Admin | ❌ Não aparece | N/A |

---

## 🎨 Interface Visual

### Formulário de Criação/Edição

Quando "Gestor" é selecionado:

```html
<!-- Campo de Departamento - Aparece automaticamente -->
<div v-if="shouldShowDepartmentField">
    <label for="department_id">
        Departamento *
        <span>(Obrigatório para este role)</span>
    </label>
    <select v-model="form.department_id" required>
        <option value="">Selecione um departamento</option>
        <option v-for="dept in departments" :value="dept.id">
            {{ dept.name }}
        </option>
    </select>
    <p class="text-xs text-gray-500">
        Atribua o usuário a um departamento específico
    </p>
</div>
```

---

## 🔄 Fluxo Completo

### 1️⃣ Criar Novo Gestor
```
Admin acessa: /admin/users/create
   ↓
Seleciona role: "Gestor"
   ↓
Campo "Departamento" aparece (obrigatório)
   ↓
Seleciona departamento
   ↓
Salva usuário
   ↓
Backend valida presença do departamento
   ↓
✅ Usuário criado com departamento
```

### 2️⃣ Editar Gestor Existente
```
Admin acessa: /admin/users/{id}/edit
   ↓
Se role = "Gestor"
   ↓
Campo "Departamento" aparece
   ↓
Mostra departamento atual (se houver)
   ↓
Admin pode alterar
   ↓
✅ Departamento atualizado
```

---

## ✅ Testes de Validação

### Teste 1: Criar Gestor SEM departamento
```bash
Resultado: ❌ Erro de validação
Mensagem: "O departamento é obrigatório para o role selecionado."
```

### Teste 2: Criar Gestor COM departamento
```bash
Resultado: ✅ Sucesso
Usuário criado com department_id preenchido
```

### Teste 3: Campo aparece apenas para roles corretos
```bash
Role "Utente": ❌ Campo não aparece
Role "Admin": ❌ Campo não aparece
Role "Gestor": ✅ Campo aparece
Role "Técnico": ✅ Campo aparece
Role "Director": ✅ Campo aparece
Role "PCA": ✅ Campo aparece
```

---

## 📝 Nota Importante

**"Gestor"** e **"Manager"** referem-se ao **mesmo role**:
- No banco de dados: `name = 'Gestor'`
- Na interface: Pode aparecer como "Gestor" ou "Gestor de Reclamações"
- No código backend: `'Gestor'`
- No sistema: Role responsável por gerenciar reclamações

---

## 🚀 Conclusão

✅ **TUDO CONFIGURADO CORRETAMENTE**

O campo de **Departamento** já está:
- ✅ Aparecendo para o role "Gestor"
- ✅ Marcado como obrigatório
- ✅ Com validação frontend (required)
- ✅ Com validação backend (custom rule)
- ✅ Funcionando em Create e Edit

**Nenhuma alteração adicional necessária!**

---

**Status:** ✅ **CONFIRMADO E FUNCIONAL**  
**Última Verificação:** 13 de Dezembro de 2025  
**Assets:** Compilados e atualizados
