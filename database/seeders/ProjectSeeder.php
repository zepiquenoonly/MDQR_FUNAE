<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Department;

class ProjectSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🏗️ Criando Projectos e vinculando aos Departamentos...');

        // Obter departamentos
        $departments = [
            'energia' => Department::where('name', 'like', '%Energia%')->first(),
            'saude' => Department::where('name', 'like', '%Saúde%')->first(),
            'educacao' => Department::where('name', 'like', '%Educação%')->first(),
            'agua' => Department::where('name', 'like', '%Água%')->first(),
            'infraestrutura' => Department::where('name', 'like', '%Infraestrutura%')->first(),
        ];

        // Verificar se os departamentos existem
        if (in_array(null, $departments, true)) {
            $this->command->error('⚠️ Alguns departamentos não foram encontrados. Execute DepartmentSeeder primeiro.');
            return;
        }

        // ==================== PROJECTOS EM ANDAMENTO ====================

        Project::firstOrCreate(
            ['name' => 'PARQUE EÓLICO DE PEMBA'],
            [
                'description' => 'Projecto de energia eólica para diversificação da matriz energética, aproveitando o potencial eólico da região de Pemba para geração de eletricidade limpa e renovável.',
                'image_url' => '/images/Emblem_of_Mozambique.svg-2.png',
                'provincia' => 'Cabo Delgado',
                'distrito' => 'Pemba',
                'bairro' => 'Zimpeto',
                'category' => 'andamento',
                'data_criacao' => '2024-05-25',
                'department_id' => $departments['energia']->id,
            ]
        );

        Project::firstOrCreate(
            ['name' => 'CENTRO DE SAÚDE COMUNITÁRIO DE MATOLA'],
            [
                'description' => 'Construção de centro de saúde para atendimento básico à comunidade de Khongolote, incluindo maternidade e farmácia.',
                'image_url' => '/images/Emblem_of_Mozambique.svg-2.png',
                'provincia' => 'Maputo',
                'distrito' => 'Matola',
                'bairro' => 'Khongolote',
                'category' => 'andamento',
                'data_criacao' => '2024-04-20',
                'department_id' => $departments['saude']->id,
            ]
        );

        Project::firstOrCreate(
            ['name' => 'ESCOLA PRIMÁRIA COMPLETA DE XAI-XAI'],
            [
                'description' => 'Construção de escola primária com 12 salas de aula, biblioteca, laboratório de informática e campo desportivo.',
                'image_url' => '/images/Emblem_of_Mozambique.svg-2.png',
                'provincia' => 'Gaza',
                'distrito' => 'Xai-Xai',
                'bairro' => 'Mugudo',
                'category' => 'andamento',
                'data_criacao' => '2024-03-15',
                'department_id' => $departments['educacao']->id,
            ]
        );

        Project::firstOrCreate(
            ['name' => 'SISTEMA DE DRENAGEM PLUVIAL DA URBANIZAÇÃO'],
            [
                'description' => 'Implementação de sistema de drenagem para prevenir inundações em área urbana, incluindo canais, bueiros e estações de bombeamento.',
                'image_url' => '/images/Emblem_of_Mozambique.svg-2.png',
                'provincia' => 'Maputo',
                'distrito' => 'Cidade de Maputo',
                'bairro' => 'Urbanização',
                'category' => 'andamento',
                'data_criacao' => '2024-01-12',
                'department_id' => $departments['agua']->id,
            ]
        );

        Project::firstOrCreate(
            ['name' => 'PONTE SOBRE RIO LIMPOPO'],
            [
                'description' => 'Construção de ponte rodoviária com 150 metros de comprimento sobre o Rio Limpopo para melhorar acessibilidade regional.',
                'image_url' => '/images/Emblem_of_Mozambique.svg-2.png',
                'provincia' => 'Gaza',
                'distrito' => 'Chokwé',
                'bairro' => 'Sede',
                'category' => 'andamento',
                'data_criacao' => '2024-02-05',
                'department_id' => $departments['infraestrutura']->id,
            ]
        );

        // ==================== PROJECTOS FINALIZADOS ====================

        Project::firstOrCreate(
            ['name' => 'SISTEMA DE ÁGUA POTÁVEL DE MACHAVA'],
            [
                'description' => 'Sistema de abastecimento de água potável para comunidades rurais, incluindo poços, reservatórios e rede de distribuição.',
                'image_url' => '/images/Emblem_of_Mozambique.svg-2.png',
                'provincia' => 'Maputo',
                'distrito' => 'Matola',
                'bairro' => 'Machava',
                'category' => 'finalizados',
                'data_criacao' => '2022-01-10',
                'department_id' => $departments['agua']->id,
            ]
        );

        Project::firstOrCreate(
            ['name' => 'MERCADO MUNICIPAL DA BEIRA'],
            [
                'description' => 'Mercado municipal com 50 bancas, sistema de saneamento, área de descarga e câmaras frigoríficas.',
                'image_url' => '/images/Emblem_of_Mozambique.svg-2.png',
                'provincia' => 'Sofala',
                'distrito' => 'Beira',
                'bairro' => 'Munhava',
                'category' => 'finalizados',
                'data_criacao' => '2021-08-05',
                'department_id' => $departments['infraestrutura']->id,
            ]
        );

        Project::firstOrCreate(
            ['name' => 'ELECTRIFICAÇÃO RURAL DE INHAMBANE'],
            [
                'description' => 'Projecto de electrificação rural utilizando sistemas solares fotovoltaicos para comunidades remotas.',
                'image_url' => '/images/Emblem_of_Mozambique.svg-2.png',
                'provincia' => 'Inhambane',
                'distrito' => 'Inhambane',
                'bairro' => 'Muelé',
                'category' => 'finalizados',
                'data_criacao' => '2022-06-20',
                'department_id' => $departments['energia']->id,
            ]
        );

        // ==================== PROJECTOS PARADOS ====================

        Project::firstOrCreate(
            ['name' => 'USINA SOLAR DE NAMPULA'],
            [
                'description' => 'Instalação de usina solar com capacidade de 10MW para suprir deficit energético da região.',
                'image_url' => '/images/Emblem_of_Mozambique.svg-2.png',
                'provincia' => 'Nampula',
                'distrito' => 'Nampula',
                'bairro' => 'Napipine',
                'category' => 'parados',
                'data_criacao' => '2023-07-10',
                'department_id' => $departments['energia']->id,
            ]
        );

        Project::firstOrCreate(
            ['name' => 'COMPLEXO HABITACIONAL DE QUELIMANE'],
            [
                'description' => 'Construção de 100 habitações sociais para famílias de baixa renda, incluindo infraestrutura básica.',
                'image_url' => '/images/Emblem_of_Mozambique.svg-2.png',
                'provincia' => 'Zambézia',
                'distrito' => 'Quelimane',
                'bairro' => 'Nicolé',
                'category' => 'parados',
                'data_criacao' => '2023-02-28',
                'department_id' => $departments['infraestrutura']->id,
            ]
        );

        $totalProjects = Project::count();
        $this->command->info("✅ {$totalProjects} projectos criados/atualizados com sucesso!");
        
        // Resumo por departamento
        $this->command->info("\n📊 Resumo de Projectos por Departamento:");
        foreach ($departments as $key => $dept) {
            $count = Project::where('department_id', $dept->id)->count();
            $this->command->info("  • {$dept->name}: {$count} projectos");
        }
    }
}
