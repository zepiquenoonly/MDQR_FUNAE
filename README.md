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

# Popular dados iniciais (roles, usuários admin, dados de exemplo)
php artisan db:seed
```

### 5.1. Popular Dados para Testes de Performance

Para realizar testes de performance e usabilidade com grandes volumes de dados, utilize o comando dedicado:

```bash
# Popular com valores padrão (500 utentes, 20 técnicos, 5 gestores, 2000 reclamações)
php artisan db:seed-performance

# Personalizar volumes de dados
php artisan db:seed-performance --utentes=1000 --tecnicos=50 --gestores=10 --reclamacoes=5000

# Opções disponíveis:
# --utentes=N     : Número de utentes a criar (padrão: 500)
# --tecnicos=N    : Número de técnicos a criar (padrão: 20)
# --gestores=N    : Número de gestores a criar (padrão: 5)
# --reclamacoes=N : Número de reclamações a criar (padrão: 2000)
# --fresh         : Executar migrate:fresh antes (⚠️ apaga todos os dados existentes)
```

**Distribuição Realista de Dados:**

O seeder cria dados com distribuição realista:
- **Status das Reclamações**: 15% submetidas, 20% em análise, 10% atribuídas, 25% em andamento, 5% pendentes, 20% resolvidas, 5% rejeitadas
- **Prioridades**: 30% baixa, 40% média, 25% alta, 5% urgente
- **Tipo**: 30% anônimas, 70% identificadas
- **Histórico**: Cada reclamação possui histórico completo de atualizações conforme seu status

**Exemplo de Uso:**

```bash
# Ambiente de desenvolvimento completo
php artisan migrate:fresh
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed-performance --utentes=500 --reclamacoes=2000

# Ou tudo de uma vez (com --fresh)
php artisan db:seed-performance --fresh --utentes=500 --reclamacoes=2000
```

**Nota**: O seeder de performance utiliza inserção em batch para otimizar o tempo de execução, mas volumes muito grandes podem levar alguns minutos para completar.

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

---

**Versão**: 2.0.0  
**Última Atualização**: 20 de Novembro de 2025  
**Status**: Em Desenvolvimento
