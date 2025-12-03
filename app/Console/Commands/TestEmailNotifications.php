<?php

namespace App\Console\Commands;

use App\Models\Grievance;
use App\Models\GrievanceUpdate;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<EOF
email:test {type? : Tipo de email a testar (created, status-changed, assigned, comment, resolved, rejected, all)}
{--email= : Email de destino para os testes}
{--grievance= : ID da reclamação existente para usar nos testes}
{--grievance-type= : Tipo da reclamação (grievance, complaint, suggestion)}
EOF;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testar envio de emails para todos os cenários do sistema';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $type = $this->argument('type') ?? 'all';
        $testEmail = $this->option('email');
        $grievanceId = $this->option('grievance');
        $grievanceType = $this->option('grievance-type');

        // Se não especificado, escolher aleatoriamente um tipo diferente
        if (!$grievanceType) {
            $grievanceType = collect(['grievance', 'complaint', 'suggestion'])->random();
        }

        $validTypes = ['created', 'status-changed', 'assigned', 'comment', 'resolved', 'rejected', 'all'];
        if (!in_array($type, $validTypes)) {
            $this->error("Tipo inválido: {$type}. Tipos válidos: " . implode(', ', $validTypes));
            return Command::FAILURE;
        }

        $validGrievanceTypes = ['grievance', 'complaint', 'suggestion'];
        if (!in_array($grievanceType, $validGrievanceTypes)) {
            $this->error("Tipo de reclamação inválido: {$grievanceType}. Tipos válidos: " . implode(', ', $validGrievanceTypes));
            return Command::FAILURE;
        }

        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('  Teste de Envio de Emails - Sistema de Reclamações');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        // Verificar configuração de email
        if (config('mail.default') === 'log') {
            $this->warn('[ATENÇÃO] O mailer está configurado como "log".');
            $this->warn('   Os emails serão apenas registados em logs, não serão enviados realmente.');
            $this->warn('   Para enviar emails reais, configure MAIL_MAILER=smtp no .env');
            $this->newLine();
        }

        // Preparar dados de teste
        $grievance = $grievanceId
            ? Grievance::with(['user', 'assignedUser'])->find($grievanceId)
            : $this->createTestData($testEmail, $grievanceType);

        if (!$grievance) {
            $this->error('Não foi possível criar ou encontrar a reclamação de teste.');
            return Command::FAILURE;
        }

        $this->info("[INFO] Reclamação de teste: {$grievance->reference_number}");
        $this->info("   Tipo: {$grievance->type}");
        $this->info("   Status: {$grievance->status}");
        $this->info("   Email destinatário: " . ($grievance->user?->email ?? $grievance->contact_email ?? 'N/A'));
        $this->newLine();

        // Executar testes
        $results = [];

        if ($type === 'all' || $type === 'created') {
            $results[] = $this->testGrievanceCreated($notificationService, $grievance);
        }

        if ($type === 'all' || $type === 'status-changed') {
            $results[] = $this->testStatusChanged($notificationService, $grievance);
        }

        if ($type === 'all' || $type === 'assigned') {
            $results[] = $this->testAssigned($notificationService, $grievance);
        }

        if ($type === 'all' || $type === 'comment') {
            $results[] = $this->testCommentAdded($notificationService, $grievance);
        }

        if ($type === 'all' || $type === 'resolved') {
            $results[] = $this->testResolved($notificationService, $grievance);
        }

        if ($type === 'all' || $type === 'rejected') {
            $results[] = $this->testRejected($notificationService, $grievance);
        }

        // Mostrar resumo
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('  Resumo dos Testes');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        $table = [];
        foreach ($results as $result) {
            $status = $result['success'] ? '[OK] Enviado' : '[ERRO] Falhou';
            $table[] = [
                'Tipo' => $result['type'],
                'Status' => $status,
                'Email' => $result['email'],
                'Mensagem' => $result['message'],
            ];
        }

        $this->table(['Tipo', 'Status', 'Email', 'Mensagem'], $table);

        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $totalCount = count($results);

        $this->newLine();
        if ($successCount === $totalCount) {
            $this->info("[OK] Todos os {$totalCount} emails foram enviados com sucesso!");
        } else {
            $this->warn("[ATENÇÃO] {$successCount} de {$totalCount} emails foram enviados com sucesso.");
        }

        return Command::SUCCESS;
    }

    /**
     * Criar dados de teste
     */
    protected function createTestData(?string $testEmail, string $grievanceType = 'complaint'): Grievance
    {
        $this->info('[INFO] Criando dados de teste...');

        // Criar usuário se necessário
        $user = null;
        if ($testEmail) {
            $user = User::firstOrCreate(
                ['email' => $testEmail],
                [
                    'name' => 'Utente de Teste',
                    'username' => 'teste_' . time(),
                    'password' => bcrypt('password'),
                ]
            );
        } else {
            $user = User::factory()->create([
                'email' => 'teste@example.com',
                'name' => 'Utente de Teste',
            ]);
        }

        // Criar técnico
        $technician = User::firstOrCreate(
            ['email' => 'tecnico@example.com'],
            [
                'name' => 'João Técnico',
                'username' => 'tecnico_test',
                'password' => bcrypt('password'),
            ]
        );

        // Criar reclamação
        $grievance = Grievance::factory()->identified()->create([
            'user_id' => $user->id,
            'status' => 'submitted',
            'assigned_to' => $technician->id,
            'assigned_at' => now(),
            'type' => $grievanceType,
        ]);

        return $grievance;
    }

    /**
     * Testar email de reclamação criada
     */
    protected function testGrievanceCreated(NotificationService $service, Grievance $grievance): array
    {
        $email = $grievance->user?->email ?? $grievance->contact_email ?? 'N/A';
        try {
            $service->notifyGrievanceCreated($grievance);
            return [
                'type' => 'Reclamação Criada',
                'success' => true,
                'email' => $email,
                'message' => 'Email enviado com sucesso',
            ];
        } catch (\Exception $e) {
            return [
                'type' => 'Reclamação Criada',
                'success' => false,
                'email' => $email,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Testar email de mudança de status
     */
    protected function testStatusChanged(NotificationService $service, Grievance $grievance): array
    {
        $email = $grievance->user?->email ?? $grievance->contact_email ?? 'N/A';
        try {
            $service->notifyStatusChanged($grievance, 'submitted', 'under_review');
            return [
                'type' => 'Status Alterado',
                'success' => true,
                'email' => $email,
                'message' => 'Email enviado com sucesso',
            ];
        } catch (\Exception $e) {
            return [
                'type' => 'Status Alterado',
                'success' => false,
                'email' => $email,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Testar email de atribuição
     */
    protected function testAssigned(NotificationService $service, Grievance $grievance): array
    {
        $email = $grievance->user?->email ?? $grievance->contact_email ?? 'N/A';
        try {
            $assignedUser = $grievance->assignedUser;
            if (!$assignedUser) {
                $assignedUser = User::factory()->create(['name' => 'Técnico Teste']);
                $grievance->update(['assigned_to' => $assignedUser->id]);
            }

            $service->notifyAssigned($grievance, $assignedUser);
            return [
                'type' => 'Reclamação Atribuída',
                'success' => true,
                'email' => $email,
                'message' => 'Email enviado com sucesso',
            ];
        } catch (\Exception $e) {
            return [
                'type' => 'Reclamação Atribuída',
                'success' => false,
                'email' => $email,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Testar email de comentário adicionado
     */
    protected function testCommentAdded(NotificationService $service, Grievance $grievance): array
    {
        $email = $grievance->user?->email ?? $grievance->contact_email ?? 'N/A';
        try {
            $technician = User::factory()->create(['name' => 'Técnico Comentário']);
            $update = GrievanceUpdate::create([
                'grievance_id' => $grievance->id,
                'user_id' => $technician->id,
                'action_type' => 'comment_added',
                'comment' => 'Este é um comentário de teste para verificar o envio de email.',
                'is_public' => true,
            ]);

            $service->notifyCommentAdded($grievance, $update);
            return [
                'type' => 'Comentário Adicionado',
                'success' => true,
                'email' => $email,
                'message' => 'Email enviado com sucesso',
            ];
        } catch (\Exception $e) {
            return [
                'type' => 'Comentário Adicionado',
                'success' => false,
                'email' => $email,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Testar email de reclamação resolvida
     */
    protected function testResolved(NotificationService $service, Grievance $grievance): array
    {
        $email = $grievance->user?->email ?? $grievance->contact_email ?? 'N/A';
        try {
            $grievance->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolved_by' => User::factory()->create()->id,
                'resolution_notes' => 'Reclamação resolvida com sucesso durante teste.',
            ]);

            $service->notifyResolved($grievance);
            return [
                'type' => 'Reclamação Resolvida',
                'success' => true,
                'email' => $email,
                'message' => 'Email enviado com sucesso',
            ];
        } catch (\Exception $e) {
            return [
                'type' => 'Reclamação Resolvida',
                'success' => false,
                'email' => $email,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Testar email de reclamação rejeitada
     */
    protected function testRejected(NotificationService $service, Grievance $grievance): array
    {
        $email = $grievance->user?->email ?? $grievance->contact_email ?? 'N/A';
        try {
            $grievance->update([
                'status' => 'rejected',
                'resolution_notes' => 'Reclamação não procedente - teste de email.',
            ]);

            $service->notifyRejected($grievance, 'A reclamação não atende aos critérios estabelecidos.');
            return [
                'type' => 'Reclamação Rejeitada',
                'success' => true,
                'email' => $email,
                'message' => 'Email enviado com sucesso',
            ];
        } catch (\Exception $e) {
            return [
                'type' => 'Reclamação Rejeitada',
                'success' => false,
                'email' => $email,
                'message' => $e->getMessage(),
            ];
        }
    }
}

