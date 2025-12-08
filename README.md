# 🏛️ Sistema GRM - FUNAE

> **Plataforma Digital de Gestão de Queixas e Reclamações**  
> Sistema desenvolvido para o Fundo de Energia de Moçambique (FUNAE)

---
## 🆕 Novidades & Changelog

### Dezembro 2025

#### Sistema de Anexos Aprimorado (08/12/2025)
- 📎 **Visualização inline de anexos**: Preview direto de imagens, PDFs e áudios no navegador
- 🔊 **Suporte expandido para áudio**: Tipos de ficheiros de áudio adicionais suportados (MP3, WAV, OGG)
- 🔗 **URLs públicos para anexos**: Acesso direto via links públicos com restrições de segurança
- 📂 **Gestão melhorada de ficheiros**: Caminhos corrigidos e logs aprimorados para anexos
- 📏 **Limite de upload ajustado**: Tamanho máximo de ficheiro atualizado para 2MB
- 🎨 **Galeria de anexos melhorada**: Modal redesenhado com controles UI aprimorados
- 🔒 **Segurança**: Sistema de acesso restrito para visualização de anexos públicos
- 🗂️ **Exclusão do Git**: Diretório `/public/uploads` adicionado ao `.gitignore`

#### Eventos e Atribuição Automática (08/12/2025)
- 🤖 **Evento GrievanceAutoAssigned**: Nova classe de evento para rastreamento de atribuições automáticas
- 📊 **Logging aprimorado**: Melhor rastreamento do processo de atribuição de técnicos
- ⚡ **Performance otimizada**: Processamento de eventos assíncronos para atribuições

#### Melhorias no Acompanhamento (07/12/2025)
- 🔍 **Controle de visibilidade da pesquisa**: Seção de pesquisa com controle de exibição
- 🎯 **Refatoração da busca**: Tratamento de erros aprimorado na busca de reclamações
- 🧹 **Código limpo**: Refatoração do controller e componente para melhor manutenibilidade

#### Melhorias no Formulário de Submissão (06/12/2025)
- ⏱️ **Aumento do tempo de auto-fechamento do modal de sucesso**: Timer aumentado de 5 para 60 segundos
- 📧 **Campos de contato opcionais**: Nome e email agora opcionais para submissões anônimas
- ✉️ **Melhoria da mensagem do modal**: Aviso explícito sobre fechamento em 60s e necessidade de salvar código
- 🚀 **Modal de submissão direto da landing page**: Acesso imediato ao formulário desde a página inicial
- 🎨 **Melhorias na landing page**: Textos revisados e footer aprimorado para melhor usabilidade

#### Seeder de Performance (04/12/2025)
- 📊 **PerformanceTestSeeder**: Geração de 15 projetos, 500 utentes, 20 técnicos, 2000 reclamações
- 🎯 **Atribuição inteligente**: Técnicos priorizados por projeto relacionado
- ⚡ **Inserção otimizada**: Performance mantida com grandes volumes de dados

#### Sistema de Autenticação (04/12/2025)
- 🔐 **RedirectIfAuthenticated refatorado**: Redirecionamento baseado em papel do usuário
- 🛡️ **Proteção completa**: Usuários logados não acessam rotas de login/register
- ✅ **Cobertura de testes**: Testes automatizados para todos cenários de redirecionamento

### Novembro 2025

#### Funcionalidades Implementadas
- Dashboard Utente, PCA, Técnico e Gestor completos com análise por tipos de submissão
- Padronização completa Dashboard Utente
- Theme Toggle (Dark/Light Mode) funcional
- Sidebars dinâmicos por role (PCA, Gestor, Técnico, Utente)
- Menus específicos para cada role
- Botão "Sair" funcional em todos os dashboards
- Links "Meu Perfil" e "Acompanhamento" em todos os menus
- Novo usuário 'Utente' com mesmas credenciais padrão

#### Erros Corrigidos
- MenuItem.vue: popupTimer duplicado removido
- Complaints.vue: Erro "Unexpected token '<'" (fetch HTML como JSON) resolvido
- Complaints.vue: Código duplicado (console.error e finally) removido

---

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Vue.js](https://img.shields.io/badge/Vue.js-3-green.svg)](https://vuejs.org)

---

## 📖 Índice

- [Sobre](#-sobre)
- [Início Rápido](#-início-rápido)
- [Configuração](#️-configuração)
- [Deploy em Produção](#-deploy-em-produção)
- [Funcionalidades](#-funcionalidades)
- [Documentação](#-documentação)

---

## 📋 Sobre

Sistema de **Grievance Redress Mechanism (GRM)** que permite a comunidades e partes interessadas submeterem queixas, reclamações e sugestões de forma eficiente, transparente e segura.

**Desenvolvido por:** TECHSOLUTIONS, LDA  
**Cliente:** ENABEL Belgian Development Agency  
**Beneficiário:** FUNAE

### Stack Tecnológica

- **Backend:** Laravel 12.x + PHP 8.2+
- **Frontend:** Vue.js 3 + Inertia.js + Tailwind CSS
- **Database:** MySQL 8.0 / PostgreSQL 13+
- **Notificações:** Email (SMTP Hostinger) + SMS Gateway
- **Filas:** Laravel Queue (Database Driver)

---

## 🚀 Início Rápido

### Requisitos
- PHP >= 8.2
- Composer >= 2.5
- MySQL >= 8.0 ou PostgreSQL >= 13
- Node.js >= 18.x e NPM >= 9.x

### Instalação (5 minutos)

```bash
# 1. Clonar repositório
git clone https://github.com/TECHSOLUTIONS-PROJECTS/www.mdqr.co.mz
cd www.mdqr.co.mz

# 2. Instalar dependências
composer install
npm install

# 3. Configurar ambiente
cp .env.example .env
php artisan key:generate

# 4. Configurar banco de dados no .env
DB_DATABASE=mdqr_funae
DB_USERNAME=root
DB_PASSWORD=sua_senha

# 5. Migrar e popular dados
php artisan migrate --seed

# 6. Compilar assets
npm run dev

# 7. Iniciar servidor
php artisan serve
```

**Acesse:** http://localhost:8000

### Credenciais Padrão

Após o seeding, use estas credenciais para login:

| Papel | Email | Senha |
|-------|-------|-------|
| Admin | admin@funae.co.mz | password |
| Gestor | gestor@funae.co.mz | password |
| Técnico | tecnico@funae.co.mz | password |
| Utente | utente@gmail.com | password |

---

## ⚙️ Configuração

### 📧 Email (SMTP Hostinger)

Edite o `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@seu-dominio.com
MAIL_PASSWORD=sua-senha-email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@seu-dominio.com
MAIL_FROM_NAME="Sistema GRM FUNAE"
```

**Testar envio:**
```bash
php artisan email:test created --email=seu-email@teste.com
```

### 🔔 Notificações Automáticas

O sistema envia emails automaticamente para:
- ✉️ Nova queixa criada
- 🔄 Mudança de status
- 👤 Atribuição a técnico
- 💬 Novo comentário
- ✅ Queixa resolvida
- ❌ Queixa rejeitada

**Para funcionar, o queue worker deve estar rodando:**
```bash
php artisan queue:work
```

---

## 🌐 Deploy em Produção

### Opção 1: Script Automático

```bash
chmod +x deploy.sh
./deploy.sh
```

### Opção 2: Hostinger/Sevalla

**Consulte o guia completo:** [PRODUCTION-DEPLOY.md](./PRODUCTION-DEPLOY.md)

**Guia rápido:** [HOSTINGER-SETUP.txt](./HOSTINGER-SETUP.txt)

#### Passos Essenciais

1. **Configurar .env em produção**
```env
APP_ENV=production
APP_DEBUG=false
QUEUE_CONNECTION=database
```

2. **Criar Cron Job no Hostinger** (Crítico para emails!)
```bash
* * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
```

3. **Executar deploy**
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

4. **Verificar**
```bash
php artisan email:test
tail -f storage/logs/laravel.log
```

**✅ Checklist de Produção:**
- [ ] Cron job configurado (* * * * *)
- [ ] QUEUE_CONNECTION=database
- [ ] Email SMTP configurado
- [ ] Timezone: Africa/Maputo
- [ ] SSL/HTTPS ativo
- [ ] APP_DEBUG=false

---

## 🎯 Funcionalidades

### Para Utentes
- 📝 Submeter queixas/reclamações/sugestões (anônimo ou identificado)
- 📎 Anexar evidências (fotos, documentos)
- 📊 Acompanhar status em tempo real
- 🔔 Receber notificações por email

### Para Gestores
- 📋 Visualizar e analisar todas as queixas
- 🏷️ Classificar e atribuir a técnicos
- 📈 Dashboards e relatórios estatísticos
- ⏱️ Controlar prazos e SLAs

### Para Técnicos
- 📝 Receber queixas atribuídas
- 💬 Adicionar atualizações e comentários
- ✅ Marcar como resolvido
- 📊 Ver histórico completo

### Sistema
- 🤖 Atribuição automática de técnicos
- 📧 Notificações automáticas por email
- 📊 Tracking de status e histórico
- 🔒 Segurança e conformidade com LGPD

---

## 📚 Documentação

---

## 🌱 Seeders Especiais & Testes de Performance

O sistema inclui seeders avançados para popular o banco de dados com dados realistas e para testes de performance em larga escala.

### Seeders Padrão
Ao rodar `php artisan migrate --seed`, os seguintes seeders são executados:
- **RoleSeeder**: Cria todos os papéis e permissões do sistema
- **AdminUserSeeder**: Cria usuários padrão (Gestor, Técnico, PCA, Utente)
- **GrievanceSeeder**: Cria exemplos reais de queixas em diferentes estados
- **UserSpecializationsSeeder**: Atribui especializações e capacidade de trabalho aos técnicos

### Seeder de Performance (opcional)
Para gerar grandes volumes de dados para testes de stress e relatórios:

```bash
php artisan db:seed-performance --utentes=500 --tecnicos=20 --gestores=5 --reclamacoes=2000
```
> Altere os parâmetros conforme necessário. Use apenas em ambiente de desenvolvimento!

### Dicas
- Sempre rode o `RoleSeeder` antes de outros seeders customizados.
- Para rodar seeders individualmente:
```bash
php artisan db:seed --class=UserSpecializationsSeeder
php artisan db:seed --class=GrievanceSeeder

```
---

### Guias Disponíveis

| Documento | Descrição |
|-----------|-----------|
| [README.md](./README.md) | Este arquivo - Visão geral e início rápido |
| [PRODUCTION-DEPLOY.md](./PRODUCTION-DEPLOY.md) | Guia completo de deploy em produção |
| [HOSTINGER-SETUP.txt](./HOSTINGER-SETUP.txt) | Guia rápido para Hostinger/Sevalla |
| [MILESTONES.md](./MILESTONES.md) | Features implementadas e roadmap |

### Comandos Úteis

```bash
# Desenvolvimento
php artisan serve              # Iniciar servidor
php artisan queue:work         # Processar filas
npm run dev                    # Watch assets

# Testes
php artisan test               # Executar todos os testes
php artisan email:test         # Testar emails

# Produção
php artisan optimize           # Otimizar aplicação
php artisan queue:monitor      # Monitorar filas
php artisan schedule:list      # Ver tarefas agendadas

# Debug
php artisan about              # Info do sistema
php artisan route:list         # Listar rotas
tail -f storage/logs/laravel.log  # Ver logs em tempo real
```

### Estrutura do Projeto

```
.
├── app/
│   ├── Http/Controllers/      # Controladores
│   ├── Models/                # Models Eloquent
│   ├── Mail/                  # Classes de Email
│   ├── Observers/             # Observadores (Notificações)
│   └── Services/              # Serviços de negócio
├── database/
│   ├── migrations/            # Migrações de BD
│   └── seeders/               # Seeders de dados
├── resources/
│   ├── js/                    # Vue.js components
│   └── views/                 # Templates de email
├── routes/
│   ├── web.php                # Rotas web
│   ├── api.php                # Rotas API
│   └── console.php            # Scheduler e comandos
├── deploy.sh                  # Script de deploy
└── README.md                  # Este arquivo
```

---

## 🆘 Suporte e Troubleshooting

### Problemas Comuns

**Emails não estão sendo enviados?**
1. Verificar se queue worker está rodando: `php artisan queue:work`
2. Verificar jobs na fila: `php artisan tinker` → `DB::table('jobs')->count()`
3. Ver logs: `tail -f storage/logs/laravel.log`
4. Testar SMTP: `php artisan email:test`

**Erro de timezone?**
- Verificar `config/app.php`: deve ser `'timezone' => 'Africa/Maputo'`
- Limpar cache: `php artisan config:clear && php artisan config:cache`

**Erro 500 em produção?**
- Ver logs: `tail -50 storage/logs/laravel.log`
- Verificar permissões: `chmod -R 755 storage bootstrap/cache`
- Limpar cache: `php artisan optimize:clear`

### Contactos

📧 **Email:** suporte@techsolutions.co.mz  
🌐 **Website:** www.techsolutions.co.mz  
📱 **WhatsApp:** +258 XX XXX XXXX

---

## 📜 Licença e Créditos

**Desenvolvido por:** TECHSOLUTIONS, LDA  
**Cliente:** ENABEL Belgian Development Agency  
**Beneficiário:** Fundo de Energia de Moçambique (FUNAE)

© 2025 TECHSOLUTIONS, LDA. Todos os direitos reservados.

---

**Versão:** 1.1  
**Última Atualização:** 8 de Dezembro de 2025  
**Status:** ✅ Em Produção

