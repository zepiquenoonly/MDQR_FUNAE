# Sistema GRM - FUNAE
### Plataforma Digital de Gestão de Queixas e Reclamações

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-Proprietary-yellow.svg)]()

## 📋 Sobre o Projeto

Sistema de Gestão de Mecanismo de Queixas e Reclamações (Grievance Redress Mechanism - GRM) desenvolvido para o **Fundo de Energia de Moçambique (FUNAE)**, permitindo que comunidades e partes interessadas submetam queixas, reclamações e sugestões de forma eficiente, transparente e segura.

### 🏢 Partes Envolvidas

- **Desenvolvedor**: TECHSOLUTIONS, LDA
- **Contratante**: ENABEL Belgian Development Agency
- **Beneficiário**: Fundo de Energia de Moçambique (FUNAE)

## 🎯 Funcionalidades Principais

### Para Utentes
- ✅ Submissão de reclamações/queixas/sugestões (anonimamente ou identificado)
- 📎 Anexo de evidências (fotos, documentos)
- 📊 Acompanhamento do estado em tempo real
- 🔔 Notificações automáticas (Email/SMS)
- 🌍 Interface multilingue (Português, Inglês e línguas locais)

### Para Gestão
- 📋 Visualização e análise de reclamações
- 🏷️ Classificação e triagem automática
- 👥 Atribuição de técnicos e departamentos
- 📈 Dashboards e relatórios estatísticos
- ⏱️ Controle de prazos e SLAs
- 🔄 Monitoramento de fluxo de trabalho

### Para Administração
- 📊 Painel de estatísticas globais
- 📑 Relatórios consolidados
- 🎯 Indicadores de desempenho (KPIs)
- 👁️ Visão geral do sistema

## 👥 Atores do Sistema

| Ator | Responsabilidades |
|------|-------------------|
| **Utente** | Submete e acompanha reclamações |
| **Gestor de Reclamações** | Coordena todo o processo de gestão |
| **Gestor Adjunto** | Apoia na triagem e acompanhamento |
| **Técnicos** | Executam ações corretivas |
| **Director de Departamento** | Supervisiona casos críticos |
| **PCA** | Monitora desempenho global |
| **Sistema** | Automação e notificações |

## 🛠️ Tecnologias Utilizadas

- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Base de Dados**: MySQL 8.0 / PostgreSQL
- **Frontend**: Blade Templates, Livewire, Alpine.js ou VueJS
- **Notificações**: Email (SMTP), SMS Gateway
- **Autenticação**: Laravel Sanctum
- **Filas**: Redis/Laravel Queue
- **Cache**: Redis
- **Armazenamento**: Laravel Storage (Local/S3)

## 📦 Requisitos do Sistema

- PHP >= 8.2
- Composer >= 2.5
- MySQL >= 8.0 ou PostgreSQL >= 13
- Redis >= 6.0
- Node.js >= 18.x e NPM >= 9.x

## 🚀 Instalação

### 1. Clonar o Repositório

```bash
git clone https://github.com/TECHSOLUTIONS-PROJECTS/www.mdqr.co.mz
```

### 2. Instalar Dependências

```bash
# Dependências PHP
composer install

# Dependências JavaScript
npm install
```

### 3. Configurar Ambiente

```bash
# Copiar arquivo de configuração
cp .env.example .env

# Gerar chave da aplicação
php artisan key:generate
```

### 4. Configurar Base de Dados

Edite o arquivo `.env` com suas credenciais:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mdqr_funae
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

### 5. Executar Migrações e Seeders

```bash
# Migrar base de dados
php artisan migrate

# Popular dados iniciais
php artisan db:seed
```

### 6. Compilar Assets

```bash
# Desenvolvimento
npm run dev

# Produção
npm run build
```

### 7. Iniciar Servidor

```bash
# Servidor de desenvolvimento
composer run dev

# Worker de filas (em outro terminal)
php artisan queue:work
```

Acesse: `http://localhost:8000`

## ⚙️ Configuração

### Notificações Email

#### Configuração com Hostinger

Para configurar o envio de emails usando o servidor SMTP da Hostinger, adicione as seguintes variáveis no arquivo `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@seu-dominio.com
MAIL_PASSWORD=sua-senha-de-email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=seu-email@seu-dominio.com
MAIL_FROM_NAME="Sistema GRM FUNAE"
```

**Notas importantes:**
- Use a porta **587** com **TLS** ou a porta **465** com **SSL** (altere `MAIL_ENCRYPTION` para `ssl` neste caso)
- O `MAIL_USERNAME` deve ser o endereço de email completo (ex: `noreply@funae.co.mz`)
- O `MAIL_PASSWORD` é a senha da conta de email, não a senha do painel da Hostinger
- Certifique-se de que o email está ativado e configurado corretamente no painel da Hostinger

#### Configuração Genérica (Outros Provedores)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.exemplo.com
MAIL_PORT=587
MAIL_USERNAME=seu_email
MAIL_PASSWORD=sua_senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@funae.co.mz
MAIL_FROM_NAME="GRM FUNAE"
```

#### Testando o Envio de Emails

O sistema inclui um comando Artisan para testar todos os cenários de envio de email:

```bash
# Testar todos os tipos de email
php artisan email:test

# Testar um tipo específico
php artisan email:test created
php artisan email:test status-changed
php artisan email:test assigned
php artisan email:test comment
php artisan email:test resolved
php artisan email:test rejected

# Especificar email de destino
php artisan email:test all --email=teste@example.com

# Usar uma reclamação existente
php artisan email:test all --grievance=1
```

**Tipos de email testados:**
- `created` - Reclamação criada
- `status-changed` - Mudança de status
- `assigned` - Reclamação atribuída a técnico
- `comment` - Comentário público adicionado
- `resolved` - Reclamação resolvida
- `rejected` - Reclamação rejeitada
- `all` - Todos os tipos (padrão)

#### Testes Automatizados

Execute os testes automatizados de email:

```bash
php artisan test --filter=EmailNotificationTest
```

Os testes verificam:
- Envio correto de todos os 6 tipos de email
- Destinatários corretos (usuário autenticado vs anônimo)
- Assuntos e conteúdos dos emails
- Registros de notificações no banco de dados
- Tratamento de erros e falhas

### 🚀 Configuração em Produção (Hostinger/Sevalla)

#### 1. Variáveis de Ambiente (.env)

Edite o arquivo `.env` no servidor de produção via SSH ou File Manager:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com

# Timezone correto para Moçambique
APP_TIMEZONE=Africa/Maputo

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=seu_database
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha_segura

# Queue - IMPORTANTE: Use database em produção
QUEUE_CONNECTION=database

# Email - Hostinger SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@seu-dominio.com
MAIL_PASSWORD=sua-senha-email-segura
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@seu-dominio.com
MAIL_FROM_NAME="Sistema GRM FUNAE"
```

#### 2. Criar Cron Job para Queue Worker

**Via Hostinger/Sevalla Dashboard:**

1. Acesse **Advanced → Cron Jobs**
2. Adicione um novo Cron Job:

```bash
* * * * * cd /home/seu-usuario/domains/seu-dominio.com/public_html && php artisan schedule:run >> /dev/null 2>&1
```

**Frequência:** A cada minuto (`* * * * *`)

#### 3. Configurar o Scheduler

Edite `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Processar fila de emails a cada minuto
    $schedule->command('queue:work --stop-when-empty --tries=3 --timeout=60')
             ->everyMinute()
             ->withoutOverlapping()
             ->runInBackground();
}
```

#### 4. Comandos de Deploy

Execute via SSH:

```bash
# Navegar para o diretório do projeto
cd /home/seu-usuario/domains/seu-dominio.com/public_html

# Atualizar código do repositório
git pull origin main

# Instalar dependências
composer install --no-dev --optimize-autoloader

# Limpar e otimizar cache
php artisan config:clear
php artisan cache:clear
php artisan route:cache
php artisan view:cache
php artisan config:cache

# Executar migrações
php artisan migrate --force

# Compilar assets (se necessário)
npm run build

# Definir permissões corretas
chmod -R 755 storage bootstrap/cache
```

#### 5. Testar Sistema de Notificações

```bash
# Testar envio de email
php artisan email:test created --email=seu-email@teste.com

# Verificar jobs na fila
php artisan queue:monitor

# Ver logs
tail -f storage/logs/laravel.log
```

#### 6. Monitoramento e Troubleshooting

**Verificar se o cron está a funcionar:**
```bash
# Ver logs do cron
tail -f /var/log/cron.log

# Verificar jobs pendentes
php artisan tinker
>>> DB::table('jobs')->count()
```

**Se emails não estão a ser enviados:**

1. **Verificar configuração do timezone:**
   ```bash
   php artisan tinker
   >>> config('app.timezone')
   # Deve retornar: "Africa/Maputo"
   ```

2. **Verificar jobs falhados:**
   ```bash
   php artisan queue:failed
   php artisan queue:retry all
   ```

3. **Testar conexão SMTP:**
   ```bash
   php artisan email:test created
   ```

4. **Verificar logs:**
   ```bash
   tail -100 storage/logs/laravel.log | grep -i "error\|exception"
   ```

#### 7. Alternativa: Supervisor (Servidores com Acesso Root)

Se tiver acesso root, use Supervisor para gerenciar o queue worker:

```bash
# Instalar Supervisor
sudo apt-get install supervisor

# Criar configuração
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

Conteúdo:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/seu-usuario/domains/seu-dominio.com/public_html/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=seu-usuario
numprocs=2
redirect_stderr=true
stdout_logfile=/home/seu-usuario/domains/seu-dominio.com/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Recarregar Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

#### 8. Otimizações de Produção

**Para melhor performance:**

```env
# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis (se disponível)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

**Comandos de otimização:**
```bash
php artisan optimize
php artisan event:cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### ⚠️ Checklist Final de Produção

- [ ] Timezone configurado como `Africa/Maputo` em `config/app.php`
- [ ] `.env` configurado com `APP_ENV=production` e `APP_DEBUG=false`
- [ ] Cron job criado para `schedule:run`
- [ ] Scheduler configurado para processar queue
- [ ] Email SMTP testado e funcional
- [ ] Permissões corretas em `storage/` e `bootstrap/cache/`
- [ ] Cache otimizado (config, route, view)
- [ ] SSL/HTTPS configurado
- [ ] Backups automáticos configurados
- [ ] Monitoramento de logs ativo

### Notificações SMS

```env
SMS_GATEWAY=seu_gateway
SMS_API_KEY=sua_chave_api
SMS_FROM=FUNAE
```

### Cache e Filas

```env
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

## 🔒 Segurança e Conformidade

- ✅ Conformidade com Lei de Proteção de Dados Pessoais de Moçambique
- 🔐 Encriptação de dados sensíveis
- 👤 Suporte para submissões anónimas
- 🔑 Autenticação multi-factor (2FA)
- 📝 Auditoria completa de ações
- 🛡️ Proteção contra CSRF, XSS e SQL Injection

## 🌐 Suporte Multilingue

O sistema suporta:
- 🇵🇹 Português (padrão)
- 🇬🇧 Inglês
- 🗣️ Línguas locais de Moçambique

## 📊 Relatórios e KPIs

- Total de reclamações por período
- Tempo médio de resolução
- Taxa de conclusão
- Reclamações por categoria/departamento
- Análise de tendências
- Exportação (PDF, Excel, CSV)

## 🔄 Fluxo de Trabalho

1. **Submissão** → Utente submete reclamação
2. **Triagem** → Gestor classifica e atribui
3. **Análise** → Técnico analisa e investiga
4. **Ação** → Execução de medidas corretivas
5. **Validação** → Gestor valida conclusão
6. **Encerramento** → Processo concluído
7. **Feedback** → Utente recebe resposta

## 📄 Licença

Este projeto é propriedade de **TECHSOLUTIONS, LDA** e foi desenvolvido para o **FUNAE**.
Todos os direitos reservados © 2025.

## 👨‍💻 Equipa de Desenvolvimento - www.techsolutions.co.mz

Desenvolvido com ❤️ pela equipa TECHSOLUTIONS, LDA.

## 📝 Changelog Recente

### Versão 0.3 - 22 de Novembro de 2025

#### 🎨 Atualização de Branding e Terminologia
Atualização completa da terminologia utilizada no sistema, substituindo "denúncia" por "queixa" para melhor alinhar com a natureza do mecanismo de diálogo e reclamações:

**Impacto:**
- ✅ Consistência de branding em toda a aplicação
- ✅ Melhor alinhamento com a natureza do mecanismo de diálogo
- ✅ Linguagem mais acolhedora e menos punitiva
- ✅ Experiência de usuário mais positiva

#### 🔧 Outras Melhorias
- Simplificação do `GrievanceSeeder` com pattern `firstOrCreate`
- Correção de variáveis indefinidas em templates de email (`$oldStatusLabel`, `$newStatusLabel`)
- Sistema de notificações por email totalmente operacional com `GrievanceObserver` e `NotificationService`

---

**Versão**: 0.3  
**Última Atualização**: 22 de Novembro de 2025  
**Status**: Em Desenvolvimento
