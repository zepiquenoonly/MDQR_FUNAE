# 🔧 Correção: Nome do Role "Gestor"

## 🐛 Problema Identificado

A contagem de **Gestores** no Dashboard Admin estava aparecendo como **0** (zero), mesmo havendo gestores cadastrados no sistema.

### Causa Raiz:
O código estava procurando pelo role **"Gestor de Reclamações"**, mas no banco de dados o role está cadastrado apenas como **"Gestor"**.

---

## ✅ Solução Implementada

### 1. AdminDashboardController.php
**Linha 35-37**

**Antes:**
```php
'gestores' => \App\Models\User::whereHas('roles', function($query) {
    $query->where('name', 'Gestor de Reclamações');
})->count() ?? 0,
```

**Depois:**
```php
'gestores' => \App\Models\User::whereHas('roles', function($query) {
    $query->where('name', 'Gestor');
})->count() ?? 0,
```

---

### 2. UserController.php
**Store Method (linha 79)**

**Antes:**
```php
$rolesWithDepartment = ['Técnico', 'Director', 'Gestor de Reclamações', 'PCA'];
```

**Depois:**
```php
$rolesWithDepartment = ['Técnico', 'Director', 'Gestor', 'PCA'];
```

**Update Method (linha 140)**

Mesma correção aplicada.

---

### 3. Users/Create.vue
**Linha 170**

**Antes:**
```javascript
const rolesWithDepartment = ['Técnico', 'Director', 'Gestor de Reclamações', 'PCA'];
```

**Depois:**
```javascript
const rolesWithDepartment = ['Técnico', 'Director', 'Gestor', 'PCA'];
```

---

### 4. Users/Edit.vue
**Linha 146**

Mesma correção aplicada.

---

## 📊 Roles Existentes no Sistema

Consultando o banco de dados:

```
✓ Utente
✓ Técnico
✓ Gestor              ← Nome correto
✓ Director
✓ PCA
✓ Admin
✓ Super Admin
```

---

## 🔍 Verificação da Correção

### Contagem Actual de Usuários por Role:

```bash
Distribuição de Usuários:
├─ Utentes: 2
├─ Técnicos: 17
├─ Gestores: 9       ← Agora aparece corretamente!
├─ Directores: 6
├─ PCA: 1
├─ Admin: 1
└─ Super Admin: 1
```

---

## 📦 Arquivos Alterados

1. ✅ `app/Http/Controllers/AdminDashboardController.php`
2. ✅ `app/Http/Controllers/UserController.php`
3. ✅ `resources/js/Pages/Admin/Users/Create.vue`
4. ✅ `resources/js/Pages/Admin/Users/Edit.vue`

---

## 🎯 Resultado

O widget **"Distribuição de Usuários"** no Dashboard Admin agora mostra:

```
┌─────────────────────────────────────┐
│  👥 Distribuição de Usuários        │
├─────────────────────────────────────┤
│  🔵 Utentes         →    2          │
│  🟡 Técnicos        →    17         │
│  🟢 Gestores        →    9  ✅      │  ← CORRIGIDO
│  🔷 Directores      →    6          │
│  🟣 PCA             →    1          │
└─────────────────────────────────────┘
```

---

## ✅ Impacto das Alterações

### 1. Dashboard Admin
- ✅ Widget de distribuição agora mostra contagem correta de gestores

### 2. Criação de Usuários
- ✅ Campo de departamento aparece corretamente para role "Gestor"
- ✅ Validação funciona corretamente

### 3. Edição de Usuários
- ✅ Campo de departamento aparece corretamente para gestores
- ✅ Validação funciona corretamente

---

## 📝 Lição Aprendida

**Sempre verificar os nomes exatos dos roles no banco de dados** antes de implementar lógica que dependa deles.

### Comando útil para listar roles:
```bash
php artisan tinker --execute="
Spatie\Permission\Models\Role::all()->pluck('name')->each(fn(\$r) => print(\$r . '\n'));
"
```

---

## 🚀 Status

**Status:** ✅ **CORRIGIDO E TESTADO**  
**Build Time:** 6.32s  
**Verificação:** Gestores agora aparecem: **9 usuários**  
**Data:** 13 de Dezembro de 2025
