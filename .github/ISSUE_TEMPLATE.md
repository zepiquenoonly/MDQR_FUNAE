---
title: "Popular Dados Fictícios no Sistema para Testes de Performance e Usabilidade"
labels: enhancement, database, testing, performance, documentation
assignees: 
---

## 📋 Descrição

Implementar um sistema robusto para popular o banco de dados com grandes volumes de dados fictícios, permitindo realizar testes de performance e usabilidade do sistema GRM FUNAE.

## 🎯 Objetivos

- Criar um seeder dedicado para gerar grandes volumes de dados de teste
- Popular usuários (utentes, técnicos, gestores) com perfis completos
- Gerar reclamações com distribuição realista de status e prioridades
- Criar histórico completo de atualizações para cada reclamação
- Otimizar inserção em batch para melhor performance

## ✅ Critérios de Aceitação

- [ ] Criar `PerformanceTestSeeder` para gerar grandes volumes de dados
- [ ] Atualizar Factory do `User` para incluir campos de perfil (phone, province, district, etc)
- [ ] Criar comando artisan `db:seed-performance` para facilitar a execução
- [ ] Seeder gera dados com distribuição realista:
  - Status das reclamações: 15% submetidas, 20% em análise, 10% atribuídas, 25% em andamento, 5% pendentes, 20% resolvidas, 5% rejeitadas
  - Prioridades: 30% baixa, 40% média, 25% alta, 5% urgente
  - Tipo: 30% anônimas, 70% identificadas
- [ ] Cada reclamação possui histórico completo de atualizações baseado no status
- [ ] README atualizado com instruções de uso
- [ ] Documentação completa de como popular dados para testes

## 📊 Volumes Configuráveis

O seeder deve permitir configurar:
- Número de utentes (padrão: 500)
- Número de técnicos (padrão: 20)
- Número de gestores (padrão: 5)
- Número de reclamações (padrão: 2000)

## 🚀 Uso Esperado

```bash
# Uso básico (valores padrão)
php artisan db:seed-performance

# Personalizar volumes
php artisan db:seed-performance --utentes=1000 --tecnicos=50 --gestores=10 --reclamacoes=5000

# Com migrate:fresh (cuidado: apaga todos os dados!)
php artisan db:seed-performance --fresh
```

## 📝 Notas Adicionais

- O seeder deve utilizar inserção em batch para otimizar performance
- Volumes muito grandes podem levar alguns minutos para completar
- Recomendado para ambientes de desenvolvimento e staging
- ⚠️ Não usar em produção sem cuidados adequados

