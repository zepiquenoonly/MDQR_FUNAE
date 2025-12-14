# 🔧 Atualização: Campo Departamento - Apenas Gestor e Técnico

## 📋 Alteração Realizada

O campo **Departamento** agora é **obrigatório apenas** para:
- ✅ **Gestor** (Manager)
- ✅ **Técnico**

## ❌ Removido de

- Director
- PCA
- Utente
- Admin
- Super Admin

---

## 📝 Arquivos Modificados

### Backend
**`app/Http/Controllers/UserController.php`**

```php
// Store method (linha 79)
$rolesWithDepartment = ['Técnico', 'Gestor'];

// Update method (linha 140)
$rolesWithDepartment = ['Técnico', 'Gestor'];
```

### Frontend
**`resources/js/Pages/Admin/Users/Create.vue` (linha 166)**
```javascript
const rolesWithDepartment = ['Técnico', 'Gestor'];
```

**`resources/js/Pages/Admin/Users/Edit.vue` (linha 142)**
```javascript
const rolesWithDepartment = ['Técnico', 'Gestor'];
```

---

## 🎯 Comportamento Atual

### Criação/Edição de Usuário

| Role | Campo Departamento | Validação |
|------|-------------------|-----------|
| **Gestor** | ✅ Aparece | Obrigatório |
| **Técnico** | ✅ Aparece | Obrigatório |
| Director | ❌ Não aparece | N/A |
| PCA | ❌ Não aparece | N/A |
| Utente | ❌ Não aparece | N/A |
| Admin | ❌ Não aparece | N/A |
| Super Admin | ❌ Não aparece | N/A |

---

## ✅ Validações

### Frontend (Vue)
- Campo só aparece se role estiver na lista
- Computed property `shouldShowDepartmentField` controla visibilidade
- Campo marcado como `required` quando visível

### Backend (Laravel)
- Validação customizada no `UserController`
- Retorna erro se role requerer departamento e campo estiver vazio
- Mensagem: "O departamento é obrigatório para o role selecionado."

---

## 📦 Build

```bash
✓ built in 6.79s
✓ Assets compilados com sucesso
✓ Commit: 5f17570
✓ Push: Completo
```

---

## 🔄 Git Status

```bash
Branch: edson/admin_dashboard
Commit: 5f17570 - fix: Campo Departamento apenas para Gestor e Técnico
PR: https://github.com/TECHSOLUTIONS-PROJECTS/www.mdqr.co.mz/pull/119
Status: Atualizado automaticamente
```

---

## 📸 Exemplo Visual

```
┌─────────────────────────────────────┐
│  Role: [Gestor] ▼                   │
├─────────────────────────────────────┤
│                                     │
│  Departamento * 🔴                  │
│  (Obrigatório para este role)       │
│  [Selecione um departamento ▼]      │
│                                     │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  Role: [Director] ▼                 │
├─────────────────────────────────────┤
│                                     │
│  (Campo departamento não aparece)   │
│                                     │
└─────────────────────────────────────┘
```

---

**Status:** ✅ **IMPLEMENTADO**  
**Data:** 13 de Dezembro de 2025  
**Build Time:** 6.79s  
**PR:** #119
