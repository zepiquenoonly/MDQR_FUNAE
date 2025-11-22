#!/bin/bash

###############################################################################
# Script de Deploy para Produção - Sistema GRM FUNAE
# Hostinger/Sevalla Production Server
###############################################################################

set -e

echo "🚀 Iniciando deploy..."

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função para log colorido
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 1. Verificar se estamos no diretório correto
if [ ! -f "artisan" ]; then
    log_error "Arquivo artisan não encontrado. Execute este script na raiz do projeto Laravel."
    exit 1
fi

# 2. Colocar aplicação em modo de manutenção
log_info "Colocando aplicação em modo de manutenção..."
php artisan down --retry=60 || log_warn "Falha ao ativar modo de manutenção"

# 3. Atualizar código do repositório
log_info "Atualizando código do repositório..."
git pull origin main

# 4. Instalar/Atualizar dependências do Composer
log_info "Instalando dependências do Composer..."
composer install --no-dev --optimize-autoloader --no-interaction

# 5. Instalar/Atualizar dependências do NPM
if [ -f "package.json" ]; then
    log_info "Instalando dependências do NPM..."
    npm ci --production
    
    log_info "Compilando assets..."
    npm run build
fi

# 6. Limpar caches
log_info "Limpando caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 7. Executar migrações
log_info "Executando migrações do banco de dados..."
php artisan migrate --force

# 8. Otimizar aplicação
log_info "Otimizando aplicação..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 9. Definir permissões corretas
log_info "Configurando permissões..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || log_warn "Não foi possível alterar owner (pode não ter permissões root)"

# 10. Limpar jobs falhados antigos (opcional)
log_info "Limpando jobs falhados antigos..."
php artisan queue:flush

# 11. Verificar configuração
log_info "Verificando configurações..."

# Verificar timezone
TIMEZONE=$(php artisan tinker --execute="echo config('app.timezone');")
if [[ "$TIMEZONE" != *"Africa/Maputo"* ]]; then
    log_error "Timezone incorreto! Deve ser 'Africa/Maputo', mas está: $TIMEZONE"
    log_error "Corrija em config/app.php ou .env"
fi

# Verificar queue connection
QUEUE_CONN=$(php artisan tinker --execute="echo config('queue.default');")
log_info "Queue Connection: $QUEUE_CONN"

# Verificar email config
MAIL_HOST=$(php artisan tinker --execute="echo config('mail.mailers.smtp.host');")
log_info "Mail Host: $MAIL_HOST"

# 12. Testar envio de email (opcional - comentado por padrão)
# log_info "Testando envio de email..."
# php artisan email:test created --email=teste@example.com

# 13. Retirar aplicação do modo de manutenção
log_info "Retirando aplicação do modo de manutenção..."
php artisan up

# 14. Verificar status da aplicação
log_info "Verificando status da aplicação..."
php artisan about

echo ""
log_info "✅ Deploy concluído com sucesso!"
echo ""
log_info "📋 Próximos passos:"
echo "   1. Verifique se o Cron Job está configurado:"
echo "      * * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1"
echo ""
echo "   2. Monitore os logs:"
echo "      tail -f storage/logs/laravel.log"
echo ""
echo "   3. Verifique jobs na fila:"
echo "      php artisan queue:monitor"
echo ""
log_info "🎉 Aplicação pronta para uso!"
