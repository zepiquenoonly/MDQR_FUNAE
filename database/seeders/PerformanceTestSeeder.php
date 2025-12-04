<?php

namespace Database\Seeders;

use App\Models\Grievance;
use App\Models\GrievanceUpdate;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class PerformanceTestSeeder extends Seeder
{
    /**
     * Números configuráveis para ajustar o volume de dados
     */
    private int $totalUtentes = 500;
    private int $totalTecnicos = 20;
    private int $totalGestores = 5;
    private int $totalProjects = 15;
    private int $totalGrievances = 2000;
    private int $updatesPerGrievance = 3; // Média de atualizações por reclamação

    /**
     * Helper para output seguro
     */
    private function output(string $message, string $type = 'info'): void
    {
        if ($this->command) {
            $this->command->{$type}($message);
        }
    }

    /**
     * Helper para nova linha segura
     */
    private function newLine(): void
    {
        if ($this->command) {
            $this->command->newLine();
        }
    }

    /**
     * Configurar os volumes de dados
     */
    public function configure(int $utentes = 500, int $tecnicos = 20, int $gestores = 5, int $projects = 15, int $grievances = 2000): self
    {
        $this->totalUtentes = $utentes;
        $this->totalTecnicos = $tecnicos;
        $this->totalGestores = $gestores;
        $this->totalProjects = $projects;
        $this->totalGrievances = $grievances;
        return $this;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->output('🚀 Iniciando seed de performance...');
        $this->newLine();

        // Criar roles se não existirem
        $this->ensureRolesExist();

        // Obter roles existentes
        $utenteRole = Role::where('name', 'Utente')->first();
        $tecnicoRole = Role::where('name', 'Técnico')->first();
        $gestorRole = Role::where('name', 'Gestor')->first();

        if (!$utenteRole || !$tecnicoRole || !$gestorRole) {
            $this->output('❌ Roles não encontradas. Execute primeiro: php artisan db:seed --class=RoleSeeder', 'error');
            return;
        }

        // Criar projetos
        $projects = $this->createProjects();

        // Criar usuários
        $utentes = $this->createUtentes($utenteRole);
        $tecnicos = $this->createTecnicos($tecnicoRole, $projects);
        $gestores = $this->createGestores($gestorRole);

        // Criar reclamações
        $grievances = $this->createGrievances($utentes, $tecnicos, $gestores, $projects);

        // Criar atualizações/histórico
        $this->createGrievanceUpdates($grievances, $tecnicos, $gestores);

        $this->newLine();
        $this->output('✅ Seed de performance concluído!');
        $this->output("📊 Estatísticas:");
        $this->output("   - Projetos criados: {$projects->count()}");
        $this->output("   - Utentes criados: {$utentes->count()}");
        $this->output("   - Técnicos criados: {$tecnicos->count()}");
        $this->output("   - Gestores criados: {$gestores->count()}");
        $this->output("   - Reclamações criadas: {$grievances->count()}");
        $this->output("   - Total de atualizações: " . GrievanceUpdate::count());
    }

    /**
     * Garantir que as roles existem
     */
    private function ensureRolesExist(): void
    {
        $roles = ['Utente', 'Técnico', 'Gestor', 'PCA'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    /**
     * Criar projetos
     */
    private function createProjects()
    {
        $this->output("🏗️ Criando {$this->totalProjects} projetos...");

        $projects = collect();
        $batchSize = 10;

        $projectTemplates = [
            [
                'name' => 'Linha de Transmissão %s kV',
                'description' => 'Projeto de construção de linha de transmissão de energia elétrica de alta tensão na região de %s.',
                'provincia' => 'Maputo',
                'category' => 'andamento'
            ],
            [
                'name' => 'Subestação Elétrica %s',
                'description' => 'Construção e instalação de subestação elétrica para distribuição de energia na região de %s.',
                'provincia' => 'Sofala',
                'category' => 'andamento'
            ],
            [
                'name' => 'Parque Eólico %s',
                'description' => 'Desenvolvimento de parque eólico para geração de energia renovável em %s.',
                'provincia' => 'Cabo Delgado',
                'category' => 'parados'
            ],
            [
                'name' => 'Sistema Solar Fotovoltaico %s',
                'description' => 'Instalação de sistema solar fotovoltaico para fornecimento de energia sustentável em %s.',
                'provincia' => 'Inhambane',
                'category' => 'finalizados'
            ],
            [
                'name' => 'Rede de Distribuição %s',
                'description' => 'Expansão e modernização da rede de distribuição elétrica na região de %s.',
                'provincia' => 'Nampula',
                'category' => 'andamento'
            ]
        ];

        $locations = [
            'Pemba', 'Beira', 'Nampula', 'Quelimane', 'Xai-Xai',
            'Tete', 'Maputo', 'Matola', 'Inhambane', 'Chimoio',
            'Lichinga', 'Gurúè', 'Angoche', 'Cuamba', 'Montepuez'
        ];

        for ($i = 0; $i < $this->totalProjects; $i += $batchSize) {
            $currentBatch = min($batchSize, $this->totalProjects - $i);
            $batch = [];

            for ($j = 0; $j < $currentBatch; $j++) {
                $template = fake()->randomElement($projectTemplates);
                $location = fake()->randomElement($locations);

                $project = [
                    'name' => sprintf($template['name'], fake()->numberBetween(66, 400)),
                    'description' => sprintf($template['description'], $location),
                    'image_url' => '/images/projects/' . fake()->uuid() . '.jpg',
                    'provincia' => $template['provincia'],
                    'distrito' => $location,
                    'bairro' => fake()->randomElement([
                        'Centro', 'Zimpeto', 'Mavalane', 'Polana', 'KaMpfumu',
                        'KaMaxaquene', 'KaMavota', 'KaTembe', 'Nlhamankulu',
                        'KaMubukwana', 'Beira Centro', 'Nampula Centro'
                    ]),
                    'category' => $template['category'],
                    'data_criacao' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $batch[] = $project;
            }

            // Inserir em batch
            DB::table('projects')->insert($batch);

            // Recuperar projetos inseridos
            $insertedProjects = Project::whereIn('name', collect($batch)->pluck('name'))->get();
            $projects = $projects->merge($insertedProjects);

            if (($i + $currentBatch) % 50 === 0) {
                $this->output("   ✓ Criados " . ($i + $currentBatch) . " projetos...");
            }
        }

        $this->output("   ✅ {$projects->count()} projetos criados");
        return $projects;
    }

    /**
     * Criar utentes
     */
    private function createUtentes(Role $role)
    {
        $this->output("👥 Criando {$this->totalUtentes} utentes...");

        $utentes = collect();
        $batchSize = 50;

        for ($i = 0; $i < $this->totalUtentes; $i += $batchSize) {
            $currentBatch = min($batchSize, $this->totalUtentes - $i);
            
            $batch = User::factory($currentBatch)->create();
            
            foreach ($batch as $user) {
                $user->assignRole($role);
                $utentes->push($user);
            }

            if (($i + $currentBatch) % 100 === 0) {
                $this->output("   ✓ Criados " . ($i + $currentBatch) . " utentes...");
            }
        }

        $this->output("   ✅ {$utentes->count()} utentes criados");
        return $utentes;
    }

    /**
     * Criar técnicos e associá-los a projetos
     */
    private function createTecnicos(Role $role, $projects)
    {
        $this->output("🔧 Criando {$this->totalTecnicos} técnicos e associando a projetos...");

        $tecnicos = collect();
        $batch = User::factory($this->totalTecnicos)->create();

        foreach ($batch as $user) {
            $user->assignRole($role);

            // Associar técnico a 1-3 projetos aleatórios
            $numProjects = fake()->numberBetween(1, 3);
            $assignedProjects = $projects->random(min($numProjects, $projects->count()));

            if ($assignedProjects instanceof Project) {
                $assignedProjects = collect([$assignedProjects]);
            }

            foreach ($assignedProjects as $project) {
                $user->projects()->attach($project->id, [
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $tecnicos->push($user);
        }

        $this->output("   ✅ {$tecnicos->count()} técnicos criados e associados a projetos");
        return $tecnicos;
    }

    /**
     * Criar gestores
     */
    private function createGestores(Role $role)
    {
        $this->output("👔 Criando {$this->totalGestores} gestores...");

        $gestores = collect();
        $batch = User::factory($this->totalGestores)->create();
        
        foreach ($batch as $user) {
            $user->assignRole($role);
            $gestores->push($user);
        }

        $this->output("   ✅ {$gestores->count()} gestores criados");
        return $gestores;
    }

    /**
     * Criar reclamações com distribuição realista de status e associação a projetos
     */
    private function createGrievances($utentes, $tecnicos, $gestores, $projects)
    {
        $this->output("📋 Criando {$this->totalGrievances} reclamações com associação a projetos...");

        $grievances = collect();
        $batchSize = 100;

        // Distribuição realista de status
        $statusDistribution = [
            'submitted' => 0.15,      // 15% - Recém submetidas
            'under_review' => 0.20,   // 20% - Em análise
            'assigned' => 0.10,       // 10% - Atribuídas
            'in_progress' => 0.25,    // 25% - Em andamento
            'pending_approval' => 0.05, // 5% - Pendentes de aprovação
            'resolved' => 0.20,       // 20% - Resolvidas
            'rejected' => 0.05,       // 5% - Rejeitadas
        ];

        // Distribuição de prioridades
        $priorityDistribution = [
            'low' => 0.30,
            'medium' => 0.40,
            'high' => 0.25,
            'urgent' => 0.05,
        ];

        // Distribuição anônimas vs identificadas (70% identificadas)
        $anonymousRate = 0.30;

        for ($i = 0; $i < $this->totalGrievances; $i += $batchSize) {
            $currentBatch = min($batchSize, $this->totalGrievances - $i);
            $batchGrievances = [];

            $referenceNumbers = [];

            for ($j = 0; $j < $currentBatch; $j++) {
                $status = $this->weightedRandom($statusDistribution);
                $priority = $this->weightedRandom($priorityDistribution);
                $isAnonymous = rand(1, 100) <= ($anonymousRate * 100);

                // Escolher usuário (70% identificadas)
                $user = null;
                $contactName = null;
                $contactEmail = null;
                $contactPhone = null;

                if (!$isAnonymous && $utentes->isNotEmpty()) {
                    $user = $utentes->random();
                } else {
                    $contactName = fake()->optional(0.8)->name();
                    $contactEmail = fake()->optional(0.7)->safeEmail();
                    $contactPhone = fake()->optional(0.6)->phoneNumber();
                }

                // Escolher projeto relacionado (70% das reclamações estão relacionadas a projetos)
                $projectId = null;
                if (rand(1, 100) <= 70 && $projects->isNotEmpty()) {
                    $projectId = $projects->random()->id;
                }

                // Atribuir técnico baseado no status
                $assignedTo = null;
                $assignedAt = null;
                $resolvedAt = null;
                $resolvedBy = null;
                $resolutionNotes = null;

                if (in_array($status, ['assigned', 'in_progress', 'pending_approval', 'resolved', 'rejected'])) {
                    // Se há projeto associado, priorizar técnicos do projeto
                    if ($projectId) {
                        $project = $projects->firstWhere('id', $projectId);
                        if ($project && $project->technicians->isNotEmpty()) {
                            $assignedTo = $project->technicians->random()->id;
                        } else {
                            $assignedTo = $tecnicos->random()->id;
                        }
                    } else {
                        $assignedTo = $tecnicos->random()->id;
                    }
                    $assignedAt = fake()->dateTimeBetween('-6 months', 'now');
                }

                if ($status === 'resolved') {
                    $resolvedAt = fake()->dateTimeBetween($assignedAt ? $assignedAt : '-6 months', 'now');
                    $resolvedBy = $gestores->random()->id;
                    $resolutionNotes = fake()->optional(0.9)->paragraph();
                }

                if ($status === 'rejected') {
                    $resolvedAt = fake()->dateTimeBetween($assignedAt ? $assignedAt : '-6 months', 'now');
                    $resolvedBy = $gestores->random()->id;
                    $resolutionNotes = fake()->optional(0.8)->sentence();
                }

                $submittedAt = fake()->dateTimeBetween('-12 months', 'now');
                $referenceNumber = Grievance::generateReferenceNumber();
                $referenceNumbers[] = $referenceNumber;

                $grievance = [
                    'user_id' => $user?->id,
                    'project_id' => $projectId,
                    'reference_number' => $referenceNumber,
                    'description' => $this->generateRealisticDescription(),
                    'category' => $this->getRandomCategory(),
                    'subcategory' => fake()->optional(0.7)->word(),
                    'contact_name' => $contactName,
                    'contact_email' => $contactEmail,
                    'contact_phone' => $contactPhone,
                    'province' => fake()->optional(0.85)->randomElement([
                        'Maputo', 'Gaza', 'Inhambane', 'Sofala', 'Manica',
                        'Tete', 'Zambézia', 'Nampula', 'Cabo Delgado', 'Niassa'
                    ]),
                    'district' => fake()->optional(0.75)->randomElement([
                        'KaMpfumu', 'Nlhamankulu', 'KaMaxaquene', 'KaMavota',
                        'KaMubukwana', 'KaTembe', 'Beira', 'Nampula',
                        'Quelimane', 'Xai-Xai', 'Pemba', 'Tete'
                    ]),
                    'location_details' => fake()->optional(0.6)->address(),
                    'status' => $status,
                    'priority' => $priority,
                    'assigned_to' => $assignedTo,
                    'assigned_at' => $assignedAt,
                    'resolved_at' => $resolvedAt,
                    'resolved_by' => $resolvedBy,
                    'resolution_notes' => $resolutionNotes,
                    'is_anonymous' => $isAnonymous,
                    'submitted_at' => $submittedAt,
                    'created_at' => $submittedAt,
                    'updated_at' => $resolvedAt ?? $assignedAt ?? $submittedAt,
                ];

                $batchGrievances[] = $grievance;
            }

            // Inserir em batch para melhor performance
            DB::table('grievances')->insert($batchGrievances);
            
            // Recuperar reclamações inseridas usando reference_numbers
            $insertedGrievances = Grievance::whereIn('reference_number', $referenceNumbers)->get();
            $grievances = $grievances->merge($insertedGrievances);

            if (($i + $currentBatch) % 500 === 0) {
                $this->output("   ✓ Criadas " . ($i + $currentBatch) . " reclamações...");
            }
        }

        $this->output("   ✅ {$grievances->count()} reclamações criadas");
        return $grievances;
    }

    /**
     * Criar atualizações/histórico para as reclamações
     */
    private function createGrievanceUpdates($grievances, $tecnicos, $gestores)
    {
        $this->output("📝 Criando histórico de atualizações...");

        $updates = [];
        $count = 0;

        foreach ($grievances as $grievance) {
            // Sempre criar update de criação (usar o user_id da grievance)
            $updates[] = [
                'grievance_id' => $grievance->id,
                'user_id' => $grievance->user_id, // Usar o criador da reclamação
                'action_type' => 'created',
                'old_value' => null,
                'new_value' => null,
                'description' => 'Reclamação criada e submetida ao sistema',
                'comment' => null,
                'is_public' => true,
                'created_at' => $grievance->submitted_at,
                'updated_at' => $grievance->submitted_at,
            ];
            $count++;

            // Criar updates baseados no status da reclamação
            $statusUpdates = $this->generateUpdatesForStatus(
                $grievance,
                $tecnicos,
                $gestores
            );
            $updates = array_merge($updates, $statusUpdates);

            // Inserir em batch a cada 500 updates
            if (count($updates) >= 500) {
                DB::table('grievance_updates')->insert($updates);
                $count += count($updates);
                $updates = [];
            }
        }

        // Inserir restante
        if (!empty($updates)) {
            DB::table('grievance_updates')->insert($updates);
            $count += count($updates);
        }

        $this->output("   ✅ {$count} atualizações criadas");
    }

    /**
     * Gerar updates baseados no status da reclamação
     */
    private function generateUpdatesForStatus($grievance, $tecnicos, $gestores)
    {
        $updates = [];
        $timestamp = $grievance->submitted_at;

        switch ($grievance->status) {
            case 'under_review':
                $timestamp = fake()->dateTimeBetween($grievance->submitted_at, 'now');
                $updates[] = [
                    'grievance_id' => $grievance->id,
                    'user_id' => $gestores->random()->id,
                    'action_type' => 'status_changed',
                    'old_value' => 'submitted',
                    'new_value' => 'under_review',
                    'description' => 'Reclamação em análise',
                    'comment' => fake()->optional(0.6)->sentence(),
                    'is_public' => true,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
                break;

            case 'assigned':
                $timestamp = $grievance->assigned_at ?? fake()->dateTimeBetween($grievance->submitted_at, 'now');
                $updates[] = [
                    'grievance_id' => $grievance->id,
                    'user_id' => $gestores->random()->id,
                    'action_type' => 'assigned',
                    'old_value' => null,
                    'new_value' => null,
                    'description' => 'Reclamação atribuída ao técnico',
                    'comment' => null,
                    'is_public' => true,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
                break;

            case 'in_progress':
                // Passar por under_review primeiro
                $reviewTime = fake()->dateTimeBetween($grievance->submitted_at, 'now');
                $updates[] = [
                    'grievance_id' => $grievance->id,
                    'user_id' => $gestores->random()->id,
                    'action_type' => 'status_changed',
                    'old_value' => 'submitted',
                    'new_value' => 'under_review',
                    'description' => 'Reclamação em análise',
                    'comment' => null,
                    'is_public' => true,
                    'created_at' => $reviewTime,
                    'updated_at' => $reviewTime,
                ];

                // Atribuição
                $assignTime = $grievance->assigned_at ?? fake()->dateTimeBetween($reviewTime, 'now');
                $updates[] = [
                    'grievance_id' => $grievance->id,
                    'user_id' => $gestores->random()->id,
                    'action_type' => 'assigned',
                    'old_value' => null,
                    'new_value' => null,
                    'description' => 'Reclamação atribuída',
                    'comment' => null,
                    'is_public' => true,
                    'created_at' => $assignTime,
                    'updated_at' => $assignTime,
                ];

                // Em andamento
                $progressTime = fake()->dateTimeBetween($assignTime, 'now');
                $updates[] = [
                    'grievance_id' => $grievance->id,
                    'user_id' => $grievance->assigned_to,
                    'action_type' => 'status_changed',
                    'old_value' => 'assigned',
                    'new_value' => 'in_progress',
                    'description' => 'Investigação em curso',
                    'comment' => fake()->optional(0.7)->sentence(),
                    'is_public' => true,
                    'created_at' => $progressTime,
                    'updated_at' => $progressTime,
                ];

                // Adicionar comentários aleatórios
                if (rand(1, 100) <= 50) {
                    $commentTime = fake()->dateTimeBetween($progressTime, 'now');
                    $updates[] = [
                        'grievance_id' => $grievance->id,
                        'user_id' => $grievance->assigned_to,
                        'action_type' => 'comment_added',
                        'old_value' => null,
                        'new_value' => null,
                        'description' => 'Comentário adicionado',
                        'comment' => fake()->sentence(),
                        'is_public' => true,
                        'created_at' => $commentTime,
                        'updated_at' => $commentTime,
                    ];
                }
                break;

            case 'pending_approval':
                // Similar a in_progress mas com pending_approval no final
                $progressTime = $grievance->assigned_at ?? fake()->dateTimeBetween($grievance->submitted_at, 'now');
                $updates[] = [
                    'grievance_id' => $grievance->id,
                    'user_id' => $grievance->assigned_to,
                    'action_type' => 'status_changed',
                    'old_value' => 'in_progress',
                    'new_value' => 'pending_approval',
                    'description' => 'Aguardando aprovação',
                    'comment' => fake()->optional(0.8)->sentence(),
                    'is_public' => true,
                    'created_at' => fake()->dateTimeBetween($progressTime, 'now'),
                    'updated_at' => fake()->dateTimeBetween($progressTime, 'now'),
                ];
                break;

            case 'resolved':
                // Fluxo completo até resolução
                $assignTime = $grievance->assigned_at ?? fake()->dateTimeBetween($grievance->submitted_at, 'now');
                $updates[] = [
                    'grievance_id' => $grievance->id,
                    'user_id' => $gestores->random()->id,
                    'action_type' => 'assigned',
                    'old_value' => null,
                    'new_value' => null,
                    'description' => 'Reclamação atribuída',
                    'comment' => null,
                    'is_public' => true,
                    'created_at' => $assignTime,
                    'updated_at' => $assignTime,
                ];

                $progressTime = fake()->dateTimeBetween($assignTime, 'now');
                $updates[] = [
                    'grievance_id' => $grievance->id,
                    'user_id' => $grievance->assigned_to,
                    'action_type' => 'status_changed',
                    'old_value' => 'assigned',
                    'new_value' => 'in_progress',
                    'description' => 'Investigação em curso',
                    'comment' => null,
                    'is_public' => true,
                    'created_at' => $progressTime,
                    'updated_at' => $progressTime,
                ];

                $resolveTime = $grievance->resolved_at ?? fake()->dateTimeBetween($progressTime, 'now');
                $updates[] = [
                    'grievance_id' => $grievance->id,
                    'user_id' => $grievance->resolved_by,
                    'action_type' => 'resolved',
                    'old_value' => 'in_progress',
                    'new_value' => 'resolved',
                    'description' => 'Reclamação resolvida',
                    'comment' => $grievance->resolution_notes ?? fake()->optional(0.8)->sentence(),
                    'is_public' => true,
                    'created_at' => $resolveTime,
                    'updated_at' => $resolveTime,
                ];
                break;

            case 'rejected':
                $assignTime = $grievance->assigned_at ?? fake()->dateTimeBetween($grievance->submitted_at, 'now');
                $updates[] = [
                    'grievance_id' => $grievance->id,
                    'user_id' => $gestores->random()->id,
                    'action_type' => 'assigned',
                    'old_value' => null,
                    'new_value' => null,
                    'description' => 'Reclamação atribuída para análise',
                    'comment' => null,
                    'is_public' => true,
                    'created_at' => $assignTime,
                    'updated_at' => $assignTime,
                ];

                $rejectTime = $grievance->resolved_at ?? fake()->dateTimeBetween($assignTime, 'now');
                $updates[] = [
                    'grievance_id' => $grievance->id,
                    'user_id' => $grievance->resolved_by,
                    'action_type' => 'rejected',
                    'old_value' => 'in_progress',
                    'new_value' => 'rejected',
                    'description' => 'Reclamação rejeitada',
                    'comment' => $grievance->resolution_notes ?? fake()->sentence(),
                    'is_public' => true,
                    'created_at' => $rejectTime,
                    'updated_at' => $rejectTime,
                ];
                break;
        }

        return $updates;
    }

    /**
     * Gerar descrição realista de reclamação
     */
    private function generateRealisticDescription(): string
    {
        $templates = [
            'Verificamos que {problema} está a causar {impacto} na área de {local}. {detalhes}.',
            'Queremos reportar que {problema} está a afetar a nossa comunidade em {local}. {detalhes}.',
            'Gostaria de denunciar que {problema}. Isto está a causar {impacto} para os moradores locais.',
            'As obras de {projeto} estão a ser realizadas durante {periodo}, causando {problema}.',
            'O {equipamento} instalado na nossa rua está a {problema}. Há risco de {risco}.',
            'Reportamos que {problema} na região de {local}. Já reclamamos várias vezes mas nada foi feito.',
        ];

        $problemas = [
            'o projeto de construção da linha de transmissão',
            'as obras de construção do posto de transformação',
            'os trabalhadores da FUNAE',
            'o transformador instalado',
            'os postes de electricidade',
            'o cabo de alta tensão',
            'a subestação eléctrica',
            'a rede de distribuição',
        ];

        $impactos = [
            'desflorestação excessiva',
            'ruído excessivo que perturba o sono',
            'perigo iminente de electrocussão',
            'risco de explosão e contaminação',
            'interrupções constantes de energia',
            'poluição ambiental',
            'riscos de segurança',
            'má qualidade do serviço',
        ];

        $locais = [
            'Moamba', 'Beira', 'Nampula', 'Quelimane', 'Xai-Xai',
            'Tete', 'Pemba', 'Maputo', 'Matola', 'Inhambane',
        ];

        $detalhes = [
            'A situação requer atenção imediata das autoridades competentes.',
            'Solicitamos uma inspeção urgente no local.',
            'Esperamos uma resposta rápida para resolver este problema.',
            'A comunidade local está muito preocupada com esta situação.',
        ];

        $riscos = [
            'electrocussão',
            'explosão',
            'contaminação do solo',
            'acidentes de trânsito',
            'incêndios',
        ];

        $template = fake()->randomElement($templates);
        $descricao = str_replace(
            ['{problema}', '{impacto}', '{local}', '{detalhes}', '{risco}', '{projeto}', '{periodo}', '{equipamento}'],
            [
                fake()->randomElement($problemas),
                fake()->randomElement($impactos),
                fake()->randomElement($locais),
                fake()->randomElement($detalhes),
                fake()->randomElement($riscos),
                'expansão da rede eléctrica',
                fake()->randomElement(['a noite', 'o fim de semana', 'horários inadequados']),
                fake()->randomElement(['transformador', 'poste de electricidade', 'cabine']),
            ],
            $template
        );

        return $descricao;
    }

    /**
     * Obter categoria aleatória
     */
    private function getRandomCategory(): string
    {
        return fake()->randomElement([
            'Serviços Públicos',
            'Infraestrutura',
            'Saúde',
            'Educação',
            'Segurança',
            'Transportes',
            'Ambiente',
            'Administração',
            'Ambiental',
            'Social',
            'Económico',
        ]);
    }

    /**
     * Escolha aleatória ponderada
     */
    private function weightedRandom(array $weights): string
    {
        $total = array_sum($weights);
        $random = mt_rand() / mt_getrandmax() * $total;

        $current = 0;
        foreach ($weights as $key => $weight) {
            $current += $weight;
            if ($random <= $current) {
                return $key;
            }
        }

        return array_key_first($weights);
    }
}

