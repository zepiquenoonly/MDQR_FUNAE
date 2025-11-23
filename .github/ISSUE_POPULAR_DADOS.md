# Popular Dados Fictícios no Sistema para Testes de Performance e Usabilidade

## 📋 Descrição

Implementar um sistema robusto para popular o banco de dados com grandes volumes de dados fictícios, permitindo realizar testes de performance e usabilidade do sistema GRM FUNAE.

## 🎯 Objetivos

- Criar um seeder dedicado para gerar grandes volumes de dados de teste
- Popular usuários (utentes, técnicos, gestores) com perfis completos
- Gerar reclamações com distribuição realista de status e prioridades
- Criar histórico completo de atualizações para cada reclamação
- Otimizar inserção em batch para melhor performance

## ✅ Critérios de Aceitação

- [x] Criado `PerformanceTestSeeder` para gerar grandes volumes de dados
- [x] Factory do `User` atualizada para incluir campos de perfil (phone, province, district, etc)
- [x] Comando artisan `db:seed-performance` criado para facilitar a execução
- [x] Seeder gera dados com distribuição realista:
  - Status das reclamações: 15% submetidas, 20% em análise, 10% atribuídas, 25% em andamento, 5% pendentes, 20% resolvidas, 5% rejeitadas
  - Prioridades: 30% baixa, 40% média, 25% alta, 5% urgente
  - Tipo: 30% anônimas, 70% identificadas
- [x] Cada reclamação possui histórico completo de atualizações baseado no status
- [x] README atualizado com instruções de uso
- [x] Documentação completa de como popular dados para testes

## 📊 Volumes Configuráveis

O seeder permite configurar:
- Número de utentes (padrão: 500)
- Número de técnicos (padrão: 20)
- Número de gestores (padrão: 5)
- Número de reclamações (padrão: 2000)

## 🚀 Como Usar

```bash
# Uso básico (valores padrão)
php artisan db:seed-performance

# Personalizar volumes
php artisan db:seed-performance --utentes=1000 --tecnicos=50 --gestores=10 --reclamacoes=5000

# Com migrate:fresh (cuidado: apaga todos os dados!)
php artisan db:seed-performance --fresh
```

## 📁 Arquivos Criados/Modificados

### Novos Arquivos
- `database/seeders/PerformanceTestSeeder.php` - Seeder principal
- `app/Console/Commands/SeedPerformanceData.php` - Comando artisan

### Arquivos Modificados
- `database/factories/UserFactory.php` - Adicionados campos de perfil
- `database/seeders/DatabaseSeeder.php` - Referência ao seeder de performance
- `README.md` - Instruções de uso adicionadas

## 🏷️ Labels Sugeridas

- `enhancement`
- `database`
- `testing`
- `performance`
- `documentation`

## 📝 Notas Adicionais

- O seeder utiliza inserção em batch para otimizar performance
- Volumes muito grandes podem levar alguns minutos para completar
- Recomendado para ambientes de desenvolvimento e staging
- ⚠️ Não usar em produção sem cuidados adequados

## 👤 Implementado por

Equipa de Desenvolvimento TECHSOLUTIONS

