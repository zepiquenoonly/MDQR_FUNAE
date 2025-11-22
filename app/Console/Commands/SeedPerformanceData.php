<?php

namespace App\Console\Commands;

use Database\Seeders\PerformanceTestSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedPerformanceData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed-performance
                            {--utentes=500 : Número de utentes a criar}
                            {--tecnicos=20 : Número de técnicos a criar}
                            {--gestores=5 : Número de gestores a criar}
                            {--reclamacoes=2000 : Número de reclamações a criar}
                            {--fresh : Executar migrate:fresh antes de popular}
                            {--only-data : Popular apenas dados de performance (sem roles/usuários admin)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Popular o banco de dados com grandes volumes de dados fictícios para testes de performance e usabilidade';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('  Seed de Performance - Sistema GRM FUNAE');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        // Verificar se deve fazer migrate:fresh
        if ($this->option('fresh')) {
            if (!$this->confirm('⚠️  ATENÇÃO: Isso irá apagar TODOS os dados existentes. Deseja continuar?')) {
                $this->info('Operação cancelada.');
                return Command::SUCCESS;
            }

            $this->warn('🔄 Executando migrate:fresh...');
            $this->call('migrate:fresh');
            $this->info('✅ Migrations executadas');
            $this->newLine();

            // Criar roles e usuários admin
            $this->info('📋 Criando roles e usuários admin...');
            $this->call('db:seed', ['--class' => 'RoleSeeder']);
            $this->call('db:seed', ['--class' => 'AdminUserSeeder']);
            $this->info('✅ Roles e usuários admin criados');
            $this->newLine();
        }

        // Obter parâmetros
        $utentes = (int) $this->option('utentes');
        $tecnicos = (int) $this->option('tecnicos');
        $gestores = (int) $this->option('gestores');
        $reclamacoes = (int) $this->option('reclamacoes');

        // Validar parâmetros
        if ($utentes < 1 || $tecnicos < 1 || $gestores < 1 || $reclamacoes < 1) {
            $this->error('❌ Todos os valores devem ser maiores que zero!');
            return Command::FAILURE;
        }

        // Mostrar resumo
        $this->info('📊 Configuração do Seed:');
        $this->table(
            ['Tipo', 'Quantidade'],
            [
                ['Utentes', number_format($utentes, 0, ',', '.')],
                ['Técnicos', number_format($tecnicos, 0, ',', '.')],
                ['Gestores', number_format($gestores, 0, ',', '.')],
                ['Reclamações', number_format($reclamacoes, 0, ',', '.')],
            ]
        );
        $this->newLine();

        // Estimativa de tempo
        $estimatedTime = $this->estimateTime($utentes, $tecnicos, $gestores, $reclamacoes);
        $this->info("⏱️  Tempo estimado: {$estimatedTime}");
        $this->newLine();

        if (!$this->confirm('Deseja continuar?')) {
            $this->info('Operação cancelada.');
            return Command::SUCCESS;
        }

        // Configurar parâmetros do seeder
        $seeder = new PerformanceTestSeeder();
        $seeder->configure($utentes, $tecnicos, $gestores, $reclamacoes);

        // Executar seeder
        $this->info('🚀 Iniciando seed de performance...');
        $this->newLine();

        $startTime = microtime(true);

        try {
            $seeder->run();
        } catch (\Exception $e) {
            $this->error('❌ Erro durante o seed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }

        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);

        // Estatísticas finais
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('  ✅ Seed de Performance Concluído!');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();
        $this->info("⏱️  Tempo de execução: {$executionTime} segundos");
        $this->newLine();

        return Command::SUCCESS;
    }

    /**
     * Estimar tempo de execução baseado nos volumes
     */
    private function estimateTime(int $utentes, int $tecnicos, int $gestores, int $reclamacoes): string
    {
        // Estimativa baseada em benchmarks aproximados
        // Ajustar conforme necessário
        $totalRecords = $utentes + $tecnicos + $gestores + $reclamacoes;
        $estimatedSeconds = ($totalRecords / 100) * 2; // ~2 segundos por 100 registos

        if ($estimatedSeconds < 60) {
            return round($estimatedSeconds) . ' segundos';
        } elseif ($estimatedSeconds < 3600) {
            $minutes = round($estimatedSeconds / 60);
            return "{$minutes} minutos";
        } else {
            $hours = round($estimatedSeconds / 3600, 1);
            return "{$hours} horas";
        }
    }
}

