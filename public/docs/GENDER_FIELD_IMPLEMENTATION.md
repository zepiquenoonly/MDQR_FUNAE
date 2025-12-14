# Implementação do Campo de Género

## Resumo
Adicionado o campo de género (genero) ao formulário de registo "Dados do Munícipe" para permitir que os utilizadores indiquem o seu género durante o processo de registo.

## Alterações Realizadas

### 1. Base de Dados
**Arquivo:** `database/migrations/2025_12_14_013242_add_gender_to_users_table.php`

- Criada migração para adicionar coluna `gender` à tabela `users`
- Tipo: `ENUM('masculino', 'feminino', 'outro')`
- Posicionamento: Após a coluna `email`
- Permite valores nulos (nullable)
- Migração executada com sucesso

### 2. Modelo User
**Arquivo:** `app/Models/User.php`

- Adicionado `'gender'` ao array `$fillable`
- Permite que o campo seja preenchido em massa

### 3. Controller de Autenticação
**Arquivo:** `app/Http/Controllers/AuthController.php`

**Método:** `completeRegistration()`
- Adicionada validação para o campo `genero`:
  ```php
  'genero' => 'required|in:masculino,feminino,outro'
  ```
- Adicionado mapeamento do campo no create do User:
  ```php
  'gender' => $request->genero
  ```

### 4. Formulário Frontend
**Arquivo:** `resources/js/Components/Authenticate/CompleteRegistrationForm.vue`

#### Alterações no Template:
- Adicionado campo select para género após o campo "Apelido"
- Opções disponíveis:
  - Masculino
  - Feminino
  - Outro
- Validação visual com borda vermelha em caso de erro
- Responsivo para diferentes tamanhos de tela

#### Alterações no Script:
- Adicionado `genero: ''` ao objeto `formData`
- Atualizado `isStep1Valid` para incluir validação do campo género
- Campo obrigatório para prosseguir ao próximo passo

## Estrutura do Campo

### No Formulário (Step 1 - Dados Pessoais):
```
Email (readonly)
Nome
Apelido
Género (novo)    ← Campo adicionado
Celular
```

### Opções de Género:
1. **Masculino** - valor: `masculino`
2. **Feminino** - valor: `feminino`
3. **Outro** - valor: `outro`

## Validação

### Backend (Laravel):
- Campo obrigatório (`required`)
- Deve ser um dos valores: `masculino`, `feminino`, `outro` (`in:masculino,feminino,outro`)

### Frontend (Vue):
- Campo obrigatório para habilitar botão "Próximo"
- Validação em tempo real
- Feedback visual de erro

## Fluxo de Dados

1. Utilizador preenche o formulário de registo (Step 1)
2. Selecciona o género no dropdown
3. Sistema valida que todos os campos obrigatórios estão preenchidos
4. Ao submeter, dados são enviados para `/register/complete`
5. Backend valida os dados (incluindo género)
6. Cria utilizador com todos os dados, incluindo género
7. Atribui role "Utente"
8. Redireciona para dashboard

## Localização no Sistema

O formulário "Dados do Munícipe" é exibido quando:
- Utilizador completa o registo inicial
- É redirecionado para `/register/complete`
- Página: `Auth/CompleteRegistration`

## Notas Técnicas

- Campo é nullable no banco de dados para suportar utilizadores antigos
- Novos registos exigem o campo obrigatoriamente
- Valores são armazenados em português para consistência com a interface
- Campo é incluído no mass assignment através do array fillable

## Status

✅ Migração criada e executada
✅ Modelo atualizado
✅ Controller atualizado
✅ Formulário frontend atualizado
✅ Validação implementada (backend e frontend)
✅ Testado e funcional

## Data de Implementação
13 de Dezembro de 2025
