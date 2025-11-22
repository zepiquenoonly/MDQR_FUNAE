<?php

namespace App\Mail;

use App\Models\Grievance;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GrievanceStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Grievance $grievance,
        public string $oldStatus,
        public string $newStatus
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Atualização de Status - {$this->grievance->reference_number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $statusLabels = [
            'submitted' => 'Submetida',
            'under_review' => 'Em Análise',
            'in_progress' => 'Em Andamento',
            'resolved' => 'Resolvida',
            'closed' => 'Fechada',
            'rejected' => 'Rejeitada',
        ];

        return new Content(
            view: 'emails.grievances.status-changed',
            text: 'emails.grievances.status-changed-text',
            with: [
                'oldStatusLabel' => $statusLabels[$this->oldStatus] ?? $this->oldStatus,
                'newStatusLabel' => $statusLabels[$this->newStatus] ?? $this->newStatus,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
