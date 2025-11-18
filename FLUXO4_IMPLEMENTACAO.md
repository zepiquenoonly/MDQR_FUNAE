# 📋 Fluxo 4: Acompanhamento da Reclamação pelo Utente

**Data de Implementação**: 18 de Novembro, 2025  
**Branch**: `edson/acompanhamento_utente`  
**Status**: ✅ Concluído

---

## 📖 Descrição

Este fluxo permite que os utentes acompanhem o estado das suas reclamações em tempo real através de um código de rastreamento único, sem necessidade de autenticação.

### Etapas do Processo

| Etapa | Actor | Ação |
|-------|-------|------|
| 1 | Utente | Acessa a plataforma e seleciona 'Acompanhar Reclamação' |
| 2 | Utente | Insere o código de rastreamento (ex: GRM-2025-XXXXXXXX) |
| 3 | Sistema | Exibe o status atual da reclamação com badge visual |
| 4 | Utente | Visualiza histórico de atualizações e comentários |
| 5 | Utente | Consulta todos os anexos e evidências de resolução |

---

## 🎯 Estados da Reclamação

| Estado | Label PT | Descrição | Badge |
|--------|----------|-----------|-------|
| `submitted` | Submetida | Reclamação registada, aguarda triagem | 🔵 Azul |
| `under_review` | Em Análise | Gestor está a analisar e classificar | 🟡 Amarelo |
| `assigned` | Atribuída | Alocada a um técnico específico | 🟣 Roxo |
| `in_progress` | Em Andamento | Técnico trabalha ativamente na resolução | 🔷 Índigo |
| `pending_approval` | Pendente de Aprovação | Aguarda aprovação do Gestor | 🟠 Laranja |
| `resolved` | Resolvida | Concluída com sucesso, utente notificado | 🟢 Verde |
| `rejected` | Rejeitada | Considerada inválida ou fora do âmbito | 🔴 Vermelho |

---

## 🗄️ Base de Dados

### Migrations Criadas

#### 1. `2025_11_18_000001_update_grievance_status_enum.php`
Atualiza a coluna `status` da tabela `grievances` com os novos estados:

```php
ENUM('submitted', 'under_review', 'assigned', 'in_progress', 
     'pending_approval', 'resolved', 'rejected')
```

#### 2. `2025_11_18_000002_create_grievance_updates_table.php`
Cria tabela para rastrear histórico completo de atualizações:

**Campos principais:**
- `grievance_id` - FK para grievances
- `user_id` - FK para users (quem fez a ação)
- `action_type` - Tipo de ação (ENUM)
- `old_value` / `new_value` - Valores antes/depois
- `description` - Descrição da ação
- `comment` - Comentário do técnico/gestor
- `metadata` - Dados adicionais (JSON)
- `is_public` - Se é visível para o utente

**Tipos de ação (`action_type`):**
- `created` - Reclamação criada
- `status_changed` - Mudança de status
- `assigned` - Atribuída a técnico
- `reassigned` - Reatribuída
- `comment_added` - Comentário adicionado
- `priority_changed` - Prioridade alterada
- `attachment_added` - Anexo adicionado
- `resolved` - Marcada como resolvida
- `rejected` - Rejeitada
- `reopened` - Reaberta

---

## 💻 Backend

### Models

#### `GrievanceUpdate.php` (novo)
**Localização**: `app/Models/GrievanceUpdate.php`

**Relacionamentos:**
```php
public function grievance(): BelongsTo
public function user(): BelongsTo
```

**Scopes úteis:**
```php
->public() // Apenas updates públicos
->ofType(['created', 'status_changed']) // Por tipo de ação
```

**Método estático para logging:**
```php
GrievanceUpdate::log(
    grievanceId: $id,
    actionType: 'status_changed',
    userId: auth()->id(),
    description: 'Estado alterado',
    oldValue: 'submitted',
    newValue: 'in_progress',
    isPublic: true
);
```

**Atributos computados:**
- `action_label` - Label em português da ação
- `formatted_description` - Descrição formatada automaticamente

#### `Grievance.php` (atualizado)
**Novos relacionamentos:**
```php
public function updates(): HasMany
public function publicUpdates(): HasMany
```

**Novo atributo:**
```php
$grievance->status_label // Retorna label em português
```

**Método atualizado:**
```php
isInProgress() // Agora inclui: assigned, under_review, in_progress, pending_approval
```

### Observer

#### `GrievanceObserver.php` (novo)
**Localização**: `app/Observers/GrievanceObserver.php`

**Auto-logging de:**
- ✅ Criação de reclamação
- ✅ Mudanças de status
- ✅ Atribuições e reatribuições
- ✅ Mudanças de prioridade
- ✅ Resoluções

**Registrado em**: `app/Providers/AppServiceProvider.php`

### Controller

#### `GrievanceTrackingController.php`
**Localização**: `app/Http/Controllers/GrievanceTrackingController.php`

**Rotas:**
```php
GET  /track      -> index()   // Renderiza página de tracking
POST /track      -> track()   // Busca reclamação por código
```

**Método `track()` retorna:**
```json
{
  "success": true,
  "grievance": {
    "reference_number": "GRM-2025-XXXXXXXX",
    "status": "in_progress",
    "status_label": "Em Andamento",
    "description": "...",
    "updates": [...],
    "attachments": [...],
    "assigned_user": {...},
    "resolved_by": {...}
  }
}
```

---

## 🎨 Frontend

### Componentes Vue

#### 1. `StatusBadge.vue`
**Localização**: `resources/js/Components/Grievance/StatusBadge.vue`

Badge visual colorido para status da reclamação.

**Props:**
```vue
<StatusBadge 
  status="in_progress"
  label="Em Andamento" 
  size="md" 
/>
```

**Tamanhos**: `sm`, `md`, `lg`

#### 2. `UpdatesTimeline.vue`
**Localização**: `resources/js/Components/Grievance/UpdatesTimeline.vue`

Timeline vertical com histórico de atualizações.

**Props:**
```vue
<UpdatesTimeline :updates="grievance.updates" />
```

**Features:**
- ✅ Linha temporal vertical
- ✅ Ícones específicos por tipo de ação
- ✅ Formatação de datas relativas ("2 horas atrás")
- ✅ Destaque para update mais recente
- ✅ Exibição de comentários técnicos

#### 3. `AttachmentsGallery.vue`
**Localização**: `resources/js/Components/Grievance/AttachmentsGallery.vue`

Galeria de anexos e evidências.

**Props:**
```vue
<AttachmentsGallery :attachments="grievance.attachments" />
```

**Features:**
- ✅ Grid responsivo (1 col mobile, 2 cols desktop)
- ✅ Ícones por tipo de arquivo (PDF, imagem, vídeo, etc.)
- ✅ Formatação de tamanho (Bytes, KB, MB)
- ✅ Links para download
- ✅ Preview de informações

### Página Principal

#### `GrievanceTracking/Index.vue`
**Localização**: `resources/js/Pages/GrievanceTracking/Index.vue`

**Seções:**
1. **Header** - Título e botão voltar
2. **Formulário de busca** - Input + botão buscar
3. **Info box** - Dicas sobre código de rastreamento
4. **Resultados**:
   - Cabeçalho da reclamação (código, status, datas)
   - Detalhes (categoria, localização, técnico)
   - Descrição completa (HTML renderizado)
   - Notas de resolução (se resolvida)
   - Timeline de atualizações
   - Galeria de anexos
   - Botão "Consultar outra reclamação"

**Estados:**
- ✅ Loading durante busca
- ✅ Erro quando não encontrado
- ✅ Empty state quando sem resultados

---

## 🚀 Rotas

### Rotas Públicas (sem autenticação)
```php
// routes/web.php
Route::middleware('guest')->group(function () {
    // ... outras rotas guest
    
    Route::get('/track', [GrievanceTrackingController::class, 'index'])
        ->name('grievance.track');
    
    Route::post('/track', [GrievanceTrackingController::class, 'track'])
        ->name('grievance.track.search');
});
```

### Acesso via Login
Link adicionado no formulário de login (`LoginForm.vue`):
```html
<a href="/track">
  🔍 Acompanhar Reclamação
</a>
```

---

## 🧪 Como Testar

### 1. Executar Migrations
```bash
php artisan migrate
```

### 2. Popular Base de Dados (opcional)
```bash
php artisan db:seed
```

### 3. Acessar Página de Tracking

**Opção 1 - Direto:**
```
http://localhost/track
```

**Opção 2 - Via Login:**
1. Acessar `http://localhost/login`
2. Clicar em "Acompanhar Reclamação"

### 4. Testar Busca

**Código de exemplo:**
```
GRM-2025-XXXXXXXX
```

Para obter um código real:
1. Criar uma reclamação no sistema
2. Verificar o `reference_number` na base de dados
3. Usar esse código na busca

---

## 📊 Fluxo de Dados

```
┌─────────────┐
│   Utente    │
└──────┬──────┘
       │ 1. Insere código
       ▼
┌─────────────────────┐
│  /track (POST)      │
│  TrackingController │
└──────┬──────────────┘
       │ 2. Busca Grievance
       ▼
┌─────────────────────┐
│  Grievance Model    │
│  + publicUpdates    │
│  + attachments      │
│  + assignedUser     │
└──────┬──────────────┘
       │ 3. Retorna JSON
       ▼
┌─────────────────────┐
│  Vue Component      │
│  - StatusBadge      │
│  - Timeline         │
│  - Gallery          │
└─────────────────────┘
```

---

## 🔒 Segurança

### Dados Públicos vs Privados

**Visível para todos (is_public = true):**
- ✅ Status da reclamação
- ✅ Histórico de mudanças de status
- ✅ Atribuições a técnicos
- ✅ Comentários técnicos públicos
- ✅ Anexos da reclamação
- ✅ Resolução final

**Privado (is_public = false):**
- ❌ Mudanças de prioridade
- ❌ Notas internas
- ❌ Discussões entre gestores/técnicos

### Validação de Acesso

Não é necessária autenticação, mas:
- ✅ Código de rastreamento é único e não sequencial
- ✅ Formato: `GRM-AAAA-XXXXXXXX` (8 caracteres aleatórios)
- ✅ Sem listagem de códigos disponíveis
- ✅ Rate limiting pode ser adicionado se necessário

---

## 📈 Melhorias Futuras

### Prioridade Alta
- [ ] Rota para download de anexos
- [ ] Notificações por email em mudanças de status
- [ ] Rate limiting para evitar abuso

### Prioridade Média
- [ ] Sistema de comentários públicos do utente
- [ ] Histórico de visualizações do tracking
- [ ] Export PDF do histórico da reclamação

### Prioridade Baixa
- [ ] Gráficos de tempo de resolução
- [ ] Comparação com média de tempo de resolução
- [ ] QR Code para acesso rápido ao tracking

---

## 📝 Notas Técnicas

### Performance

**Eager Loading implementado:**
```php
->with([
    'attachments',
    'assignedUser:id,name',
    'resolvedBy:id,name',
    'publicUpdates' => function ($query) {
        $query->with('user:id,name')
              ->orderBy('created_at', 'asc');
    }
])
```

**Índices na tabela `grievance_updates`:**
- `grievance_id` + `created_at`
- `action_type`
- `user_id`

### Formatação de Datas

Timeline usa formatação relativa:
- "2 minutos atrás"
- "5 horas atrás"
- "3 dias atrás"
- Datas antigas: "15 Nov 2025, 14:30"

### Renderização de HTML

Descrições de reclamações podem conter HTML (do editor WYSIWYG):
```vue
<div v-html="grievance.description" class="prose"></div>
```

---

## 🛠️ Troubleshooting

### Problema: Updates não aparecem
**Solução**: Verificar se o Observer está registrado no `AppServiceProvider`

### Problema: Código não encontrado
**Solução**: Verificar formato exato do código (maiúsculas, sem espaços)

### Problema: Anexos não carregam
**Solução**: Implementar rota de download de anexos (pendente)

---

## ✅ Checklist de Implementação

- [x] Migration para novos estados
- [x] Migration para tabela de updates
- [x] Model GrievanceUpdate
- [x] Atualização do Model Grievance
- [x] Observer para auto-logging
- [x] Controller com método track()
- [x] Rotas públicas
- [x] Componente StatusBadge
- [x] Componente UpdatesTimeline
- [x] Componente AttachmentsGallery
- [x] Página principal Index.vue
- [x] Link na tela de login
- [x] Documentação

---

**Desenvolvido por**: TECHSOLUTIONS  
**Projeto**: Sistema GRM FUNAE  
**Última atualização**: 18 de Novembro, 2025
