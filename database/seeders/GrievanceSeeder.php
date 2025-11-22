<?php

namespace Database\Seeders;

use App\Models\Grievance;
use App\Models\GrievanceUpdate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GrievanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Deletar grievances de teste existentes para evitar duplicação
        $testReferenceCodes = [
            'GRM-2025-R20UUE0R',
            'GRM-2025-38INYZQH',
            'GRM-2025-7ILUPSHQ',
            'GRM-2025-Z50UL6DN',
            'GRM-2025-LXEHZZGL',
            'GRM-2025-5TSZY14N',
            'GRM-2025-3TDNOZNZ',
            'GRM-2025-CIADSGG4',
        ];
        
        Grievance::whereIn('reference_number', $testReferenceCodes)->delete();

        $utente = User::whereHas('roles', function ($query) {
            $query->where('name', 'Utente');
        })->first();

        $tecnico = User::whereHas('roles', function ($query) {
            $query->where('name', 'Técnico');
        })->first();

        $gestor = User::whereHas('roles', function ($query) {
            $query->where('name', 'Gestor');
        })->first();

        // 1. Reclamação Submetida (recente)
        $grievance1 = Grievance::create([
            'user_id' => $utente?->id,
            'reference_number' => 'GRM-2025-R20UUE0R',
            'type' => 'complaint',
            'description' => 'Verificamos que o projeto de construção da linha de transmissão está a causar desflorestação excessiva na área de Moamba. As árvores centenárias estão a ser cortadas sem autorização ambiental adequada.',
            'category' => 'ambiental',
            'subcategory' => 'Desflorestação',
            'contact_name' => 'João Manuel',
            'contact_email' => 'joao.manuel@example.com',
            'contact_phone' => '+258 84 123 4567',
            'province' => 'Maputo',
            'district' => 'Moamba',
            'location_details' => 'Próximo ao Rio Incomati, coordenadas: -25.2345, 32.1234',
            'status' => 'submitted',
            'priority' => 'high',
            'is_anonymous' => false,
            'submitted_at' => now()->subHours(2),
        ]);

        GrievanceUpdate::create([
            'grievance_id' => $grievance1->id,
            'user_id' => null,
            'action_type' => 'created',
            'description' => 'Reclamação criada e submetida ao sistema',
            'is_public' => true,
        ]);

        // 2. Reclamação Em Análise
        $grievance2 = Grievance::create([
            'user_id' => $utente?->id,
            'reference_number' => 'GRM-2025-38INYZQH',
            'type' => 'grievance',
            'description' => 'As obras de construção do posto de transformação estão a ser realizadas durante a noite, causando ruído excessivo que perturba o sono dos moradores locais. Já reclamamos várias vezes mas nada foi feito.',
            'category' => 'social',
            'subcategory' => 'Poluição Sonora',
            'contact_name' => 'Maria Silva',
            'contact_email' => 'maria.silva@example.com',
            'contact_phone' => '+258 82 987 6543',
            'province' => 'Sofala',
            'district' => 'Beira',
            'location_details' => 'Bairro da Manga, próximo à escola primária',
            'status' => 'under_review',
            'priority' => 'medium',
            'assigned_to' => $tecnico?->id,
            'assigned_at' => now()->subDays(1),
            'is_anonymous' => false,
            'submitted_at' => now()->subDays(3),
        ]);

        GrievanceUpdate::create([
            'grievance_id' => $grievance2->id,
            'user_id' => null,
            'action_type' => 'created',
            'description' => 'Reclamação criada e submetida ao sistema',
            'is_public' => true,
        ]);

        GrievanceUpdate::create([
            'grievance_id' => $grievance2->id,
            'user_id' => $gestor?->id,
            'action_type' => 'assigned',
            'description' => 'Reclamação atribuída ao técnico para análise',
            'is_public' => true,
        ]);

        // 3. Reclamação Em Andamento
        $grievance3 = Grievance::create([
            'user_id' => null,
            'reference_number' => 'GRM-2025-7ILUPSHQ',
            'type' => 'complaint',
            'description' => 'Quero reportar que os trabalhadores da FUNAE não estão a usar equipamento de segurança adequado. Vejo-os a trabalhar em postes de alta tensão sem capacetes ou arneses de segurança. Isto é muito perigoso.',
            'category' => 'social',
            'subcategory' => 'Condições de Trabalho',
            'contact_name' => 'Anónimo',
            'contact_email' => 'anonimo123@tempmail.com',
            'contact_phone' => '+258 86 555 0000',
            'province' => 'Nampula',
            'district' => 'Nampula',
            'location_details' => 'Zona industrial, perto do mercado central',
            'status' => 'in_progress',
            'priority' => 'high',
            'assigned_to' => $tecnico?->id,
            'assigned_at' => now()->subDays(5),
            'is_anonymous' => true,
            'submitted_at' => now()->subDays(7),
        ]);

        GrievanceUpdate::create([
            'grievance_id' => $grievance3->id,
            'user_id' => null,
            'action_type' => 'created',
            'description' => 'Reclamação anónima criada',
            'is_public' => true,
        ]);

        GrievanceUpdate::create([
            'grievance_id' => $grievance3->id,
            'user_id' => $gestor?->id,
            'action_type' => 'assigned',
            'description' => 'Reclamação atribuída ao técnico',
            'is_public' => true,
        ]);

        GrievanceUpdate::create([
            'grievance_id' => $grievance3->id,
            'user_id' => $tecnico?->id,
            'action_type' => 'status_changed',
            'old_value' => 'under_review',
            'new_value' => 'in_progress',
            'description' => 'Iniciada investigação no local',
            'comment' => 'Visitei o local e confirmo a situação reportada. Já contactei o supervisor da obra para tomar medidas imediatas. Será agendada uma formação sobre segurança no trabalho.',
            'is_public' => true,
        ]);

        // 4. Reclamação Pendente de Aprovação
        $grievance4 = Grievance::create([
            'user_id' => $utente?->id,
            'reference_number' => 'GRM-2025-Z50UL6DN',
            'type' => 'suggestion',
            'description' => 'Os postes de electricidade instalados na nossa comunidade estão muito baixos e representam um perigo, especialmente para os camiões que passam. Já houve dois acidentes onde os cabos foram arrancados.',
            'category' => 'social',
            'subcategory' => 'Segurança Pública',
            'contact_name' => 'Carlos Muianga',
            'contact_email' => 'carlos.muianga@example.com',
            'contact_phone' => '+258 84 321 9876',
            'province' => 'Gaza',
            'district' => 'Xai-Xai',
            'location_details' => 'Estrada Nacional N1, km 156',
            'status' => 'pending_approval',
            'priority' => 'high',
            'assigned_to' => $tecnico?->id,
            'assigned_at' => now()->subDays(10),
            'is_anonymous' => false,
            'submitted_at' => now()->subDays(15),
        ]);

        GrievanceUpdate::create([
            'grievance_id' => $grievance4->id,
            'user_id' => null,
            'action_type' => 'created',
            'description' => 'Reclamação criada',
            'is_public' => true,
        ]);

        GrievanceUpdate::create([
            'grievance_id' => $grievance4->id,
            'user_id' => $tecnico?->id,
            'action_type' => 'status_changed',
            'old_value' => 'under_review',
            'new_value' => 'in_progress',
            'description' => 'Análise técnica iniciada',
            'comment' => 'Realizei inspeção técnica no local. Os postes estão com altura de 5.2m quando o regulamento exige mínimo de 6m. Necessário substituição de 8 postes.',
            'is_public' => true,
        ]);

        GrievanceUpdate::create([
            'grievance_id' => $grievance4->id,
            'user_id' => $tecnico?->id,
            'action_type' => 'status_changed',
            'old_value' => 'in_progress',
            'new_value' => 'pending_approval',
            'description' => 'Solicitada aprovação para conclusão',
            'comment' => 'Já foram encomendados os postes novos com altura correcta. A instalação está agendada para a próxima semana. Aguardo aprovação do gestor para fechar o caso.',
            'is_public' => true,
        ]);

        // 5. Reclamação Resolvida
        $grievance5 = Grievance::create([
            'user_id' => $utente?->id,
            'reference_number' => 'GRM-2025-LXEHZZGL',
            'type' => 'grievance',
            'description' => 'O transformador instalado na nossa rua está a fazer um ruído muito alto e a vazar óleo. Há risco de explosão e contaminação do solo.',
            'category' => 'ambiental',
            'subcategory' => 'Contaminação do Solo',
            'contact_name' => 'Ana Costa',
            'contact_email' => 'ana.costa@example.com',
            'contact_phone' => '+258 87 654 3210',
            'province' => 'Zambézia',
            'district' => 'Quelimane',
            'location_details' => 'Bairro Chuabo Dembe, Rua 12',
            'status' => 'resolved',
            'priority' => 'high',
            'assigned_to' => $tecnico?->id,
            'assigned_at' => now()->subDays(20),
            'resolved_at' => now()->subDays(2),
            'resolved_by' => $gestor?->id,
            'resolution_notes' => 'O transformador defeituoso foi substituído por um novo. A área contaminada foi limpa e o solo tratado. Foram realizados testes e os níveis de contaminação estão agora dentro dos parâmetros aceitáveis.',
            'is_anonymous' => false,
            'submitted_at' => now()->subDays(25),
        ]);

        GrievanceUpdate::create([
            'grievance_id' => $grievance5->id,
            'user_id' => null,
            'action_type' => 'created',
            'description' => 'Reclamação criada',
            'is_public' => true,
        ]);

        GrievanceUpdate::create([
            'grievance_id' => $grievance5->id,
            'user_id' => $tecnico?->id,
            'action_type' => 'status_changed',
            'old_value' => 'submitted',
            'new_value' => 'in_progress',
            'description' => 'Emergência confirmada - ação imediata',
            'comment' => 'Equipas deslocadas ao local. Transformador isolado e área cordoada por segurança.',
            'is_public' => true,
        ]);

        GrievanceUpdate::create([
            'grievance_id' => $grievance5->id,
            'user_id' => $tecnico?->id,
            'action_type' => 'comment_added',
            'comment' => 'Novo transformador instalado. Processo de limpeza ambiental em curso.',
            'is_public' => true,
        ]);

        GrievanceUpdate::create([
            'grievance_id' => $grievance5->id,
            'user_id' => $gestor?->id,
            'action_type' => 'resolved',
            'old_value' => 'in_progress',
            'new_value' => 'resolved',
            'description' => 'Caso resolvido com sucesso',
            'is_public' => true,
        ]);

        // 6. Reclamação Rejeitada
        $grievance6 = Grievance::create([
            'user_id' => null,
            'reference_number' => 'GRM-2025-5TSZY14N',
            'description' => 'Quero reclamar que a luz vai sempre abaixo na minha casa. Isto acontece porque os meus vizinhos estão a roubar electricidade e a sobrecarregar o sistema.',
            'category' => 'economico',
            'subcategory' => 'Ligações Ilegais',
            'contact_name' => 'António Santos',
            'contact_email' => 'antonio.santos@example.com',
            'contact_phone' => '+258 85 111 2222',
            'province' => 'Inhambane',
            'district' => 'Maxixe',
            'location_details' => 'Bairro Chambone',
            'status' => 'rejected',
            'priority' => 'low',
            'assigned_to' => $tecnico?->id,
            'assigned_at' => now()->subDays(12),
            'resolved_at' => now()->subDays(8),
            'resolved_by' => $gestor?->id,
            'resolution_notes' => 'Após investigação técnica, verificou-se que o problema não está relacionado com ligações ilegais mas sim com a subestação eléctrica local. Este caso foi encaminhado para a EDM para resolução. Não se enquadra no âmbito do GRM da FUNAE.',
            'is_anonymous' => false,
            'submitted_at' => now()->subDays(14),
        ]);

        GrievanceUpdate::create([
            'grievance_id' => $grievance6->id,
            'user_id' => null,
            'action_type' => 'created',
            'description' => 'Reclamação criada',
            'is_public' => true,
        ]);

        GrievanceUpdate::create([
            'grievance_id' => $grievance6->id,
            'user_id' => $tecnico?->id,
            'action_type' => 'status_changed',
            'old_value' => 'submitted',
            'new_value' => 'in_progress',
            'description' => 'Investigação iniciada',
            'comment' => 'A verificar situação no local',
            'is_public' => true,
        ]);

        GrievanceUpdate::create([
            'grievance_id' => $grievance6->id,
            'user_id' => $gestor?->id,
            'action_type' => 'rejected',
            'old_value' => 'in_progress',
            'new_value' => 'rejected',
            'description' => 'Reclamação fora do âmbito',
            'comment' => 'Caso encaminhado para a entidade competente (EDM)',
            'is_public' => true,
        ]);

        // 7. Reclamação Atribuída (prioridade alta)
        $grievance7 = Grievance::create([
            'user_id' => $utente?->id,
            'reference_number' => 'GRM-2025-3TDNOZNZ',
            'type' => 'grievance',
            'description' => 'URGENTE: Cabo de alta tensão partido a cair sobre a estrada. Representa perigo iminente de electrocussão. Já chamamos a linha de emergência mas ninguém apareceu.',
            'category' => 'social',
            'subcategory' => 'Segurança Pública',
            'contact_name' => 'Fernando Macamo',
            'contact_email' => 'fernando.macamo@example.com',
            'contact_phone' => '+258 84 999 8888',
            'province' => 'Tete',
            'district' => 'Tete',
            'location_details' => 'EN7, próximo ao Hospital Provincial',
            'status' => 'assigned',
            'priority' => 'high',
            'assigned_to' => $tecnico?->id,
            'assigned_at' => now()->subHour(),
            'is_anonymous' => false,
            'submitted_at' => now()->subHours(2),
        ]);

        GrievanceUpdate::create([
            'grievance_id' => $grievance7->id,
            'user_id' => null,
            'action_type' => 'created',
            'description' => 'EMERGÊNCIA - Reclamação criada',
            'is_public' => true,
        ]);

        GrievanceUpdate::create([
            'grievance_id' => $grievance7->id,
            'user_id' => $gestor?->id,
            'action_type' => 'assigned',
            'description' => 'Atribuído com prioridade máxima',
            'comment' => 'Equipas de emergência activadas',
            'is_public' => true,
        ]);

        // 8. Reclamação Anónima Submetida
        $grievance8 = Grievance::create([
            'user_id' => null,
            'reference_number' => 'GRM-2025-CIADSGG4',
            'type' => 'suggestion',
            'description' => 'Gostaria de reportar que existe corrupção no processo de ligação eléctrica. Os técnicos estão a pedir subornos para fazer as ligações mais rapidamente.',
            'category' => 'economico',
            'subcategory' => 'Má Conduta',
            'contact_name' => 'Anónimo',
            'contact_email' => 'whistleblower@tempmail.com',
            'contact_phone' => null,
            'province' => 'Cabo Delgado',
            'district' => 'Pemba',
            'location_details' => 'Escritório Regional da FUNAE',
            'status' => 'submitted',
            'priority' => 'medium',
            'is_anonymous' => true,
            'submitted_at' => now()->subDays(1),
        ]);

        GrievanceUpdate::create([
            'grievance_id' => $grievance8->id,
            'user_id' => null,
            'action_type' => 'created',
            'description' => 'Denúncia anónima recebida',
            'is_public' => false,
        ]);

        $this->command->info('✅ Criadas 8 reclamações fictícias com diferentes estados');
        $this->command->info('📋 Estados: Submetida (2), Em Análise (1), Em Andamento (1), Pendente (1), Resolvida (1), Rejeitada (1), Atribuída (1)');
    }
}
