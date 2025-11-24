# 🚀 Guia Rápido - Dashboard de Utente

## Passos para Ativação

### 1️⃣ Executar no Terminal
```bash
# Limpar cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Executar migrações (se necessário)
php artisan migrate

# Compilar assets frontend
npm install
npm run build
```

### 2️⃣ Verificar Configurações (.env)
```env
# Email (obrigatório para notificações)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu_email@gmail.com
MAIL_PASSWORD=sua_senha_app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@funae.gov.mz
MAIL_FROM_NAME="FUNAE GRM"

# SMS (opcional)
SMS_ENABLED=false
```

### 3️⃣ Acessar como Utente
1. Login no sistema
2. Navegar para: **MDQR → Reclamações**
3. Ver estatísticas e lista de reclamações
4. Clicar em "Nova Reclamação" para testar

### 4️⃣ Testar Funcionalidades

#### ✅ Visualizar Reclamações
- Dashboard mostra estatísticas
- Tabela lista todas as reclamações do utente
- Filtros funcionam (status, prioridade, categoria)

#### ✅ Ver Detalhes
- Clicar em "Ver detalhes" em qualquer reclamação
- Modal abre com informações completas
- Timeline de atualizações visível
- Botão de atualizar status funciona

#### ✅ Notificações
- Banner azul mostra notificações não lidas
- Clicar em "Marcar todas como lidas" funciona

#### ✅ Nova Reclamação
- Formulário multi-step funciona
- Upload de anexos funciona
- Email de confirmação é enviado

### 5️⃣ Verificar Logs
```bash
# Ver últimos logs
tail -f storage/logs/laravel.log

# Ver notificações enviadas
php artisan tinker
>>> App\Models\GrievanceNotification::latest()->take(5)->get();
```

## 🎯 URLs Importantes

| Descrição | URL |
|-----------|-----|
| Dashboard Utente | `/utente/dashboard` |
| Nova Reclamação | Botão no dashboard |
| Tracking Público | `/track` |
| Histórico | `/utente/grievances/history` |

## 🔍 Troubleshooting Rápido

### Problema: Dados não aparecem
```bash
php artisan config:clear
php artisan cache:clear
```

### Problema: Notificações não enviam
```bash
# Verificar configuração de email
php artisan config:cache

# Testar envio
php artisan tinker
>>> Mail::raw('Test', fn($msg) => $msg->to('test@email.com')->subject('Test'));
```

### Problema: Erro 403
- Verificar se usuário tem role "Utente"
- Verificar middleware de autenticação

## 📋 Checklist de Validação

- [ ] Dashboard carrega sem erros
- [ ] Estatísticas aparecem corretamente
- [ ] Filtros funcionam
- [ ] Modal de detalhes abre
- [ ] Timeline de atualizações funciona
- [ ] Botão refresh atualiza status
- [ ] Notificações aparecem
- [ ] Email de confirmação é enviado
- [ ] Nova reclamação pode ser criada
- [ ] Anexos podem ser baixados

## ✨ Principais Melhorias

1. **Dados Reais**: Integração completa com backend
2. **Notificações**: Sistema de email e SMS (estrutura)
3. **Tracking**: Atualizações em tempo real
4. **Detalhes**: Modal completo com timeline
5. **Filtros**: Pesquisa e filtros avançados
6. **UX**: Interface moderna e responsiva

---

**Desenvolvido por TechSolutions para FUNAE**
**Data: 23/11/2025**
