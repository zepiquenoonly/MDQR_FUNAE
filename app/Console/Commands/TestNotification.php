<?php

namespace App\Console\Commands;

use App\Models\Grievance;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class TestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-notification {--type= : Type of grievance to test (complaint, grievance, suggestion)} {--email= : Email to send notification to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test notification system for grievance creation';

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type') ?? 'complaint';
        $email = $this->option('email') ?? 'test@example.com';

        // Validar tipo
        $validTypes = ['complaint', 'grievance', 'suggestion'];
        if (!in_array($type, $validTypes)) {
            $this->error("Invalid type. Valid types: " . implode(', ', $validTypes));
            return 1;
        }

        // Criar uma grievance de teste
        $grievance = Grievance::create([
            'project_id' => 1, // Assumindo que existe um projeto com ID 1
            'type' => $type,
            'description' => "Teste de notificação para tipo: {$type}",
            'contact_email' => $email,
            'is_anonymous' => true,
            'status' => 'submitted',
            'priority' => 'medium',
            'submitted_at' => now(),
        ]);

        $this->info("Criando grievance de teste:");
        $this->info("- Tipo: {$type}");
        $this->info("- Email: {$email}");
        $this->info("- Reference: {$grievance->reference_number}");
        $this->info("- Type Label: {$grievance->type_label}");

        // Enviar notificação
        try {
            $this->notificationService->notifyGrievanceCreated($grievance);
            $this->info("✅ Notificação enviada com sucesso!");
            $this->info("Verifique os logs de email para confirmar o envio.");
        } catch (\Exception $e) {
            $this->error("❌ Erro ao enviar notificação: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
