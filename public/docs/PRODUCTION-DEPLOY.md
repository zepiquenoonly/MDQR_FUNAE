# 🚀 Guia de Deploy em Produção - Hostinger/Sevalla

## 📋 Pré-requisitos

- [x] Acesso SSH ao servidor
- [x] Conta de email configurada no painel Hostinger
- [x] Domínio apontado para o servidor
- [x] Certificado SSL ativo

---

## 🔧 Configuração Inicial (Primeira Vez)

### 1. Acesso SSH

```bash
ssh seu-usuario@seu-dominio.com
# ou
ssh seu-usuario@IP_DO_SERVIDOR
```

### 2. Clonar Repositório

```bash
cd ~/domains/seu-dominio.com/public_html
git clone https://github.com/TECHSOLUTIONS-PROJECTS/www.mdqr.co.mz .
```

### 3. Configurar .env

```bash
cp .env.example .env
nano .env
```

**Configurações essenciais:**

```env
APP_NAME="Sistema GRM FUNAE"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com

# Database (obter do painel Hostinger)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=seu_database_name
DB_USERNAME=seu_database_user
DB_PASSWORD=SUA_SENHA_SEGURA

# Queue - CRÍTICO para emails funcionarem
QUEUE_CONNECTION=database

# Email - Hostinger SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@seu-dominio.com
MAIL_PASSWORD=SUA_SENHA_EMAIL
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@seu-dominio.com
MAIL_FROM_NAME="Sistema GRM FUNAE"
```

### 4. Instalar Dependências

```bash
# Gerar chave da aplicação
php artisan key:generate

# Instalar dependências
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build

# Migrar banco de dados
php artisan migrate --seed --force

# Otimizar
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permissões
chmod -R 755 storage bootstrap/cache
```

### 5. Configurar Cron Job (CRÍTICO PARA EMAILS)

**Via Hostinger Dashboard:**

1. Acesse: **Advanced → Cron Jobs**
2. Adicione:

```bash
* * * * * cd /home/seu-usuario/domains/seu-dominio.com/public_html && php artisan schedule:run >> /dev/null 2>&1
```

**Frequência:** Every minute (`* * * * *`)

**Verificar se está funcionando:**

```bash
# Ver últimos logs do scheduler
tail -f storage/logs/scheduler.log

# Verificar jobs processados
php artisan queue:monitor
```

---

## 🔄 Deploy de Atualizações

### Método 1: Script Automático (Recomendado)

```bash
cd ~/domains/seu-dominio.com/public_html
chmod +x deploy.sh
./deploy.sh
```

### Método 2: Manual

```bash
cd ~/domains/seu-dominio.com/public_html

# Modo manutenção
php artisan down

# Actualizar código
git pull origin main

# Dependências
composer install --no-dev --optimize-autoloader
npm ci --production && npm run build

# Migrações
php artisan migrate --force

# Otimizar
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Finalizar
php artisan up
```

---

## ✅ Checklist de Produção

### Configurações Essenciais

- [ ] `.env` configurado com `APP_ENV=production`
- [ ] `APP_DEBUG=false` no `.env`
- [ ] `APP_URL` correto com HTTPS
- [ ] Timezone: `Africa/Maputo` em `config/app.php`
- [ ] `QUEUE_CONNECTION=database` no `.env`
- [ ] Credenciais SMTP configuradas e testadas
- [ ] Cron job criado e ativo (`* * * * *`)
- [ ] SSL/HTTPS ativo e funcional
- [ ] Permissões corretas em `storage/` e `bootstrap/cache/`

### Segurança

- [ ] `APP_DEBUG=false`
- [ ] `.env` não está versionado no Git
- [ ] Senhas fortes no `.env`
- [ ] CSRF protection ativa
- [ ] SQL injection prevention (Eloquent ORM)

### Performance

- [ ] Config cached (`php artisan config:cache`)
- [ ] Routes cached (`php artisan route:cache`)
- [ ] Views cached (`php artisan view:cache`)
- [ ] Autoloader otimizado (`composer install --optimize-autoloader`)
- [ ] Assets compilados (`npm run build`)

---

## 🔍 Troubleshooting

### Emails não estão sendo enviados

**1. Verificar se o cron está rodando:**

```bash
ps aux | grep schedule:run
```

**2. Verificar jobs na fila:**

```bash
php artisan tinker
>>> DB::table('jobs')->count()
```

Se retornar > 0, há jobs pendentes. O cron deve processá-los.

**3. Verificar logs:**

```bash
tail -100 storage/logs/laravel.log | grep -i "error"
tail -f storage/logs/scheduler.log
```

**4. Testar manualmente:**

```bash
# Processar fila manualmente (teste)
php artisan queue:work --stop-when-empty

# Testar email específico
php artisan email:test created --email=seu-email@teste.com
```

**5. Verificar configuração SMTP:**

```bash
php artisan tinker
>>> config('mail.mailers.smtp')
```

### Jobs falhando

```bash
# Ver jobs falhados
php artisan queue:failed

# Tentar novamente
php artisan queue:retry all

# Limpar jobs antigos falhados
php artisan queue:flush
```

### Erro de timezone

```bash
# Verificar timezone
php artisan tinker
>>> config('app.timezone')

# Deve retornar: "Africa/Maputo"
```

Se estiver errado, edite `config/app.php`:

```php
'timezone' => 'Africa/Maputo',
```

Depois:

```bash
php artisan config:clear
php artisan config:cache
```

### Erro 500

```bash
# Ver logs
tail -50 storage/logs/laravel.log

# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Verificar permissões
chmod -R 755 storage bootstrap/cache
```

---

## 📊 Monitoramento

### Verificar Status

```bash
# Status geral
php artisan about

# Queue workers
php artisan queue:monitor

# Jobs falhados
php artisan queue:failed
```

### Logs em Tempo Real

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Scheduler logs
tail -f storage/logs/scheduler.log

# Nginx/Apache logs
tail -f /var/log/nginx/error.log
tail -f /var/log/apache2/error.log
```

### Comandos Úteis

```bash
# Limpar toda cache
php artisan optimize:clear

# Recriar cache otimizada
php artisan optimize

# Ver rotas
php artisan route:list

# Ver comandos agendados
php artisan schedule:list

# Processar fila manualmente (debug)
php artisan queue:listen --tries=3
```

---

## 🆘 Suporte

**Em caso de problemas:**

1. Verificar logs: `storage/logs/laravel.log`
2. Verificar scheduler: `storage/logs/scheduler.log`
3. Testar email: `php artisan email:test`
4. Verificar cron: `crontab -l` ou painel Hostinger
5. Contactar suporte: suporte@techsolutions.co.mz

---

## 📚 Documentação Adicional

- [Documentação Laravel](https://laravel.com/docs)
- [Hostinger Tutorials](https://www.hostinger.com/tutorials/)
- [README.md](./README.md) - Documentação completa do projeto

---

**Última Atualização:** 22 de Novembro, 2025  
**Versão:** 0.3  
**Equipa:** TECHSOLUTIONS, LDA
