# Sistema de Triagem Automática de Reclamações

## Visão Geral

O sistema de triagem automática classifica e atribui reclamações aos técnicos disponíveis com base em:
- Carga de trabalho atual
- Especialização por categoria
- Localização geográfica
- Prioridade da reclamação

## Comandos Artisan

### Atribuir todas reclamações pendentes
```bash
php artisan grievance:auto-assign-pending
```

### Recalcular carga de trabalho
```bash
php artisan grievance:rebalance-workload
```

## Instalação

```bash
# 1. Executar migrations
php artisan migrate

# 2. Popular especializações (opcional)
php artisan db:seed --class=UserSpecializationsSeeder
```
