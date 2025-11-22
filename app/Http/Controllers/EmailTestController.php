<?php

namespace App\Http\Controllers;

use App\Mail\GrievanceAssigned;
use App\Mail\GrievanceCreated;
use App\Mail\GrievanceRejected;
use App\Mail\GrievanceResolved;
use App\Mail\GrievanceStatusChanged;
use App\Models\Grievance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailTestController extends Controller
{
    /**
     * Exibir formulário de teste de emails
     */
    public function showForm()
    {
        return view('email-test');
    }

    /**
     * Enviar todos os templates de email para o endereço fornecido
     */
    public function sendTestEmails(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        // Buscar uma grievance de teste ou criar uma fictícia
        $grievance = Grievance::first() ?? $this->createTestGrievance();
        
        // Buscar ou criar um usuário de teste
        $testUser = User::first() ?? $this->createTestUser();

        $emailsSent = [];

        try {
            // 1. Email de Reclamação Criada
            Mail::to($email)->send(new GrievanceCreated($grievance));
            $emailsSent[] = '✅ Reclamação Criada';

            // 2. Email de Status Alterado (para cada status único)
            $statuses = [
                'under_review' => 'Em Análise',
                'in_progress' => 'Em Andamento',
                'resolved' => 'Resolvida',
                'closed' => 'Fechada',
                'rejected' => 'Rejeitada',
            ];

            foreach ($statuses as $newStatus => $label) {
                Mail::to($email)->send(new GrievanceStatusChanged($grievance, 'submitted', $newStatus));
                $emailsSent[] = "✅ Status Alterado: {$label}";
            }

            // 3. Email de Reclamação Atribuída
            Mail::to($email)->send(new GrievanceAssigned($grievance, $testUser));
            $emailsSent[] = '✅ Reclamação Atribuída';

            // 4. Email de Reclamação Resolvida
            Mail::to($email)->send(new GrievanceResolved($grievance));
            $emailsSent[] = '✅ Reclamação Resolvida';

            // 5. Email de Reclamação Rejeitada
            Mail::to($email)->send(new GrievanceRejected($grievance, 'Esta reclamação foi rejeitada após análise detalhada. O motivo não se enquadra nos critérios estabelecidos.'));
            $emailsSent[] = '✅ Reclamação Rejeitada';

            return back()->with('success', [
                'message' => "Todos os emails de teste foram enviados para: {$email}",
                'emails' => $emailsSent,
                'total' => count($emailsSent),
            ]);

        } catch (\Exception $e) {
            return back()->with('error', [
                'message' => 'Erro ao enviar emails: ' . $e->getMessage(),
                'sent' => $emailsSent,
            ]);
        }
    }

    /**
     * Criar uma grievance fictícia para teste
     */
    private function createTestGrievance(): Grievance
    {
        $grievance = new Grievance();
        $grievance->reference_number = 'GRM-2025-TEST' . strtoupper(substr(md5(time()), 0, 8));
        // $grievance->type = 'complaint'; // Pode testar: 'complaint', 'grievance', 'suggestion'
        $grievance->description = 'Esta é uma reclamação de teste para validação dos templates de email do sistema GRM FUNAE.';
        $grievance->category = 'ambiental';
        $grievance->subcategory = 'Teste';
        $grievance->status = 'submitted';
        $grievance->priority = 'medium';
        $grievance->contact_name = 'Utilizador de Teste';
        $grievance->contact_email = 'teste@example.com';
        $grievance->province = 'Maputo';
        $grievance->district = 'Maputo';
        $grievance->submitted_at = now();
        $grievance->resolved_at = now();
        $grievance->resolution_notes = 'Reclamação resolvida com sucesso após análise e implementação das medidas corretivas necessárias.';
        $grievance->is_anonymous = false;

        // Não salva no BD, apenas cria objeto em memória
        return $grievance;
    }

    /**
     * Criar um usuário fictício para teste
     */
    private function createTestUser(): User
    {
        $user = new User();
        $user->name = 'Técnico de Teste';
        $user->email = 'tecnico.teste@funae.co.mz';
        
        // Não salva no BD, apenas cria objeto em memória
        return $user;
    }
}
