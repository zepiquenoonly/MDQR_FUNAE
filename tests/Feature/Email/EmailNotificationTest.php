<?php

namespace Tests\Feature\Email;

use App\Mail\GrievanceAssigned;
use App\Mail\GrievanceCommentAdded;
use App\Mail\GrievanceCreated;
use App\Mail\GrievanceRejected;
use App\Mail\GrievanceResolved;
use App\Mail\GrievanceStatusChanged;
use App\Models\Grievance;
use App\Models\GrievanceNotification;
use App\Models\GrievanceUpdate;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationService = app(NotificationService::class);
        Mail::fake();
    }

    /**
     * Teste: Email de reclamação criada (autenticada)
     */
    public function test_sends_email_when_grievance_created_for_authenticated_user(): void
    {
        $user = User::factory()->create([
            'email' => 'utente@example.com',
        ]);

        $grievance = Grievance::factory()->identified()->create([
            'user_id' => $user->id,
            'status' => 'submitted',
        ]);

        $this->notificationService->notifyGrievanceCreated($grievance);

        Mail::assertSent(GrievanceCreated::class, function ($mail) use ($user, $grievance) {
            return $mail->hasTo($user->email) &&
                   $mail->grievance->id === $grievance->id;
        });

        $this->assertDatabaseHas('grievance_notifications', [
            'grievance_id' => $grievance->id,
            'type' => GrievanceNotification::TYPE_GRIEVANCE_CREATED,
            'recipient_email' => $user->email,
            'status' => GrievanceNotification::STATUS_SENT,
        ]);
    }

    /**
     * Teste: Email de reclamação criada (anônima com email)
     */
    public function test_sends_email_when_grievance_created_for_anonymous_user_with_email(): void
    {
        $grievance = Grievance::factory()->anonymous()->create([
            'contact_email' => 'anonimo@example.com',
        ]);

        $this->notificationService->notifyGrievanceCreated($grievance);

        Mail::assertSent(GrievanceCreated::class, function ($mail) use ($grievance) {
            return $mail->hasTo('anonimo@example.com') &&
                   $mail->grievance->id === $grievance->id;
        });
    }

    /**
     * Teste: Não envia email quando não há email disponível
     */
    public function test_does_not_send_email_when_no_email_available(): void
    {
        $grievance = Grievance::factory()->anonymous()->create([
            'contact_email' => null,
            'user_id' => null,
        ]);

        $this->notificationService->notifyGrievanceCreated($grievance);

        Mail::assertNothingSent();
    }

    /**
     * Teste: Email de mudança de status
     */
    public function test_sends_email_when_status_changed(): void
    {
        $user = User::factory()->create(['email' => 'utente@example.com']);
        $grievance = Grievance::factory()->identified()->create([
            'user_id' => $user->id,
            'status' => 'submitted',
        ]);

        $this->notificationService->notifyStatusChanged($grievance, 'submitted', 'under_review');

        Mail::assertSent(GrievanceStatusChanged::class, function ($mail) use ($user, $grievance) {
            return $mail->hasTo($user->email) &&
                   $mail->grievance->id === $grievance->id;
        });

        $this->assertDatabaseHas('grievance_notifications', [
            'grievance_id' => $grievance->id,
            'type' => GrievanceNotification::TYPE_STATUS_CHANGED,
            'status' => GrievanceNotification::STATUS_SENT,
        ]);
    }

    /**
     * Teste: Email de mudança de status - todas as transições
     */
    public function test_sends_email_for_all_status_transitions(): void
    {
        $user = User::factory()->create(['email' => 'utente@example.com']);
        $grievance = Grievance::factory()->identified()->create([
            'user_id' => $user->id,
        ]);

        $transitions = [
            ['submitted', 'under_review'],
            ['under_review', 'assigned'],
            ['assigned', 'in_progress'],
            ['in_progress', 'pending_approval'],
            ['pending_approval', 'resolved'],
        ];

        foreach ($transitions as [$oldStatus, $newStatus]) {
            $grievance->status = $newStatus;
            $this->notificationService->notifyStatusChanged($grievance, $oldStatus, $newStatus);
        }

        Mail::assertSent(GrievanceStatusChanged::class, 5);
    }

    /**
     * Teste: Email de reclamação atribuída
     */
    public function test_sends_email_when_grievance_assigned(): void
    {
        $user = User::factory()->create(['email' => 'utente@example.com']);
        $technician = User::factory()->create([
            'name' => 'João Técnico',
            'email' => 'tecnico@example.com',
        ]);

        $grievance = Grievance::factory()->identified()->create([
            'user_id' => $user->id,
        ]);

        $this->notificationService->notifyAssigned($grievance, $technician);

        Mail::assertSent(GrievanceAssigned::class, function ($mail) use ($user, $grievance, $technician) {
            return $mail->hasTo($user->email) &&
                   $mail->grievance->id === $grievance->id &&
                   $mail->assignedUser->id === $technician->id;
        });

        $this->assertDatabaseHas('grievance_notifications', [
            'grievance_id' => $grievance->id,
            'type' => GrievanceNotification::TYPE_ASSIGNED,
            'status' => GrievanceNotification::STATUS_SENT,
        ]);
    }

    /**
     * Teste: Email de comentário adicionado (público)
     */
    public function test_sends_email_when_public_comment_added(): void
    {
        $user = User::factory()->create(['email' => 'utente@example.com']);
        $technician = User::factory()->create();
        $grievance = Grievance::factory()->identified()->create([
            'user_id' => $user->id,
        ]);

        $update = GrievanceUpdate::create([
            'grievance_id' => $grievance->id,
            'user_id' => $technician->id,
            'action_type' => 'comment_added',
            'comment' => 'Este é um comentário público',
            'is_public' => true,
        ]);

        $this->notificationService->notifyCommentAdded($grievance, $update);

        Mail::assertSent(GrievanceCommentAdded::class, function ($mail) use ($user, $grievance, $update) {
            return $mail->hasTo($user->email) &&
                   $mail->grievance->id === $grievance->id &&
                   $mail->update->id === $update->id;
        });

        $this->assertDatabaseHas('grievance_notifications', [
            'grievance_id' => $grievance->id,
            'type' => GrievanceNotification::TYPE_COMMENT_ADDED,
            'status' => GrievanceNotification::STATUS_SENT,
        ]);
    }

    /**
     * Teste: Não envia email quando comentário é privado
     */
    public function test_does_not_send_email_when_private_comment_added(): void
    {
        $user = User::factory()->create(['email' => 'utente@example.com']);
        $grievance = Grievance::factory()->identified()->create([
            'user_id' => $user->id,
        ]);

        $update = GrievanceUpdate::create([
            'grievance_id' => $grievance->id,
            'user_id' => User::factory()->create()->id,
            'action_type' => 'comment_added',
            'comment' => 'Este é um comentário privado',
            'is_public' => false,
        ]);

        $this->notificationService->notifyCommentAdded($grievance, $update);

        Mail::assertNothingSent();
    }

    /**
     * Teste: Email de reclamação resolvida
     */
    public function test_sends_email_when_grievance_resolved(): void
    {
        $user = User::factory()->create(['email' => 'utente@example.com']);
        $grievance = Grievance::factory()->identified()->create([
            'user_id' => $user->id,
            'status' => 'pending_approval',
            'resolved_at' => now(),
            'resolved_by' => User::factory()->create()->id,
        ]);

        $this->notificationService->notifyResolved($grievance);

        Mail::assertSent(GrievanceResolved::class, function ($mail) use ($user, $grievance) {
            return $mail->hasTo($user->email) &&
                   $mail->grievance->id === $grievance->id;
        });

        $this->assertDatabaseHas('grievance_notifications', [
            'grievance_id' => $grievance->id,
            'type' => GrievanceNotification::TYPE_RESOLVED,
            'status' => GrievanceNotification::STATUS_SENT,
        ]);
    }

    /**
     * Teste: Email de reclamação rejeitada
     */
    public function test_sends_email_when_grievance_rejected(): void
    {
        $user = User::factory()->create(['email' => 'utente@example.com']);
        $grievance = Grievance::factory()->identified()->create([
            'user_id' => $user->id,
            'status' => 'rejected',
            'resolution_notes' => 'Reclamação não procedente',
        ]);

        $reason = 'A reclamação não atende aos critérios estabelecidos.';
        $this->notificationService->notifyRejected($grievance, $reason);

        Mail::assertSent(GrievanceRejected::class, function ($mail) use ($user, $grievance, $reason) {
            return $mail->hasTo($user->email) &&
                   $mail->grievance->id === $grievance->id &&
                   $mail->reason === $reason;
        });

        $this->assertDatabaseHas('grievance_notifications', [
            'grievance_id' => $grievance->id,
            'type' => GrievanceNotification::TYPE_REJECTED,
            'status' => GrievanceNotification::STATUS_SENT,
        ]);
    }

    /**
     * Teste: Email de reclamação rejeitada sem motivo
     */
    public function test_sends_email_when_grievance_rejected_without_reason(): void
    {
        $user = User::factory()->create(['email' => 'utente@example.com']);
        $grievance = Grievance::factory()->identified()->create([
            'user_id' => $user->id,
            'status' => 'rejected',
        ]);

        $this->notificationService->notifyRejected($grievance, '');

        Mail::assertSent(GrievanceRejected::class);
    }

    /**
     * Teste: Verifica assunto do email de criação
     */
    public function test_grievance_created_email_has_correct_subject(): void
    {
        $user = User::factory()->create(['email' => 'utente@example.com']);
        $grievance = Grievance::factory()->identified()->create([
            'user_id' => $user->id,
            'reference_number' => 'GRM-2024-ABC12345',
        ]);

        $this->notificationService->notifyGrievanceCreated($grievance);

        Mail::assertSent(GrievanceCreated::class, function ($mail) use ($grievance) {
            return $mail->envelope()->subject === "Reclamação Recebida - {$grievance->reference_number}";
        });
    }

    /**
     * Teste: Verifica que notificação é criada mesmo quando não há email
     */
    public function test_notification_created_even_without_email(): void
    {
        $grievance = Grievance::factory()->anonymous()->create([
            'contact_email' => null,
            'user_id' => null,
        ]);

        // Não deve enviar email, mas também não deve lançar exceção
        $this->notificationService->notifyGrievanceCreated($grievance);

        Mail::assertNothingSent();
        
        // Não deve criar notificação se não há email
        $notification = GrievanceNotification::where('grievance_id', $grievance->id)->first();
        $this->assertNull($notification);
    }

    /**
     * Teste: Reclamação anônima sem email não envia notificação
     */
    public function test_anonymous_grievance_without_email_does_not_send_notification(): void
    {
        $grievance = Grievance::factory()->anonymous()->create([
            'contact_email' => null,
            'user_id' => null,
        ]);

        $this->notificationService->notifyGrievanceCreated($grievance);
        $this->notificationService->notifyStatusChanged($grievance, 'submitted', 'under_review');
        $this->notificationService->notifyResolved($grievance);

        Mail::assertNothingSent();
    }

    /**
     * Teste: Verifica dados armazenados na notificação
     */
    public function test_notification_stores_correct_data(): void
    {
        $user = User::factory()->create(['email' => 'utente@example.com']);
        $grievance = Grievance::factory()->identified()->create([
            'user_id' => $user->id,
            'reference_number' => 'GRM-2024-TEST123',
            'category' => 'Infraestrutura',
            'status' => 'submitted',
        ]);

        $this->notificationService->notifyGrievanceCreated($grievance);

        $notification = GrievanceNotification::where('grievance_id', $grievance->id)
            ->where('type', GrievanceNotification::TYPE_GRIEVANCE_CREATED)
            ->first();

        $this->assertNotNull($notification);
        $this->assertEquals($grievance->reference_number, $notification->data['reference_number']);
        $this->assertEquals('Infraestrutura', $notification->data['category']);
        $this->assertEquals('submitted', $notification->data['status']);
        $this->assertEquals("Reclamação Recebida - {$grievance->reference_number}", $notification->subject);
    }
}

