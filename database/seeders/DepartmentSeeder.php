<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\User;
use App\Models\Project;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🏢 Criando Departamentos com Directores, Usuários e Projectos...\n\n";

        // Criar Directores primeiro
        $directors = [];
        
        $directorData = [
            ['name' => 'Director de Infraestrutura', 'email' => 'director.infra@funae.co.mz', 'username' => 'director_infra'],
            ['name' => 'Director de Energia', 'email' => 'director.energia@funae.co.mz', 'username' => 'director_energia'],
            ['name' => 'Director de Água e Saneamento', 'email' => 'director.agua@funae.co.mz', 'username' => 'director_agua'],
            ['name' => 'Director de Educação', 'email' => 'director.educacao@funae.co.mz', 'username' => 'director_educacao'],
            ['name' => 'Director de Saúde', 'email' => 'director.saude@funae.co.mz', 'username' => 'director_saude'],
        ];

        foreach ($directorData as $data) {
            $director = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => bcrypt('password'),
                'phone' => '+258 84 ' . rand(1000000, 9999999),
                'province' => 'Maputo',
                'district' => 'Maputo',
                'neighborhood' => 'Centro',
            ]);
            $director->assignRole('Director');
            $directors[] = $director;
        }

        // Definir departamentos
        $departments = [
            [
                'name' => 'Infraestrutura e Construção',
                'description' => 'Responsável por projectos de construção de estradas, pontes e infraestrutura civil.',
                'director' => $directors[0],
            ],
            [
                'name' => 'Energia e Electrificação',
                'description' => 'Gestão de projectos de energia solar, eólica e electrificação rural.',
                'director' => $directors[1],
            ],
            [
                'name' => 'Água e Saneamento',
                'description' => 'Projectos de sistemas de água potável, saneamento e drenagem.',
                'director' => $directors[2],
            ],
            [
                'name' => 'Educação e Desenvolvimento Social',
                'description' => 'Construção e manutenção de escolas e centros comunitários.',
                'director' => $directors[3],
            ],
            [
                'name' => 'Saúde Pública',
                'description' => 'Projectos de centros de saúde e infraestrutura médica.',
                'director' => $directors[4],
            ],
        ];

        $createdDepartments = [];

        foreach ($departments as $deptData) {
            $department = Department::create([
                'name' => $deptData['name'],
                'description' => $deptData['description'],
                'manager_id' => $deptData['director']->id,
            ]);

            // Actualizar o department_id do Director para corresponder ao departamento criado
            $deptData['director']->update(['department_id' => $department->id]);
            
            $createdDepartments[] = $department;
            echo "✅ Departamento criado: {$department->name} (Director: {$deptData['director']->name})\n";
        }

        echo "\n📋 Atribuindo Usuários aos Departamentos...\n";

        // Atribuir Gestores e Técnicos aos departamentos
        $managers = User::whereHas('roles', function($q) {
            $q->where('name', 'Gestor');
        })->get();

        $technicians = User::whereHas('roles', function($q) {
            $q->where('name', 'Técnico');
        })->get();

        // Distribuir gestores
        foreach ($managers as $index => $manager) {
            $dept = $createdDepartments[$index % count($createdDepartments)];
            $manager->update(['department_id' => $dept->id]);
            echo "  👔 Gestor '{$manager->name}' → {$dept->name}\n";
        }

        // Distribuir técnicos
        foreach ($technicians as $index => $technician) {
            $dept = $createdDepartments[$index % count($createdDepartments)];
            $technician->update(['department_id' => $dept->id]);
            echo "  🔧 Técnico '{$technician->name}' → {$dept->name}\n";
        }

        echo "\n🏗️ Atribuindo Projectos aos Departamentos...\n";

        // Atribuir projectos aos departamentos
        $projects = Project::all();
        
        $projectDepartmentMap = [
            'PROJETO PARQUE EÓLICO' => 1, // Energia
            'CENTRO DE SAÚDE' => 4, // Saúde
            'ESCOLA PRIMÁRIA' => 3, // Educação
            'SISTEMA DE ÁGUA' => 2, // Água e Saneamento
            'MERCADO MUNICIPAL' => 0, // Infraestrutura
            'PONTE SOBRE RIO' => 0, // Infraestrutura
            'USINA SOLAR' => 1, // Energia
            'COMPLEXO HABITACIONAL' => 0, // Infraestrutura
            'SISTEMA DE DRENAGEM' => 2, // Água e Saneamento
        ];

        foreach ($projects as $project) {
            foreach ($projectDepartmentMap as $keyword => $deptIndex) {
                if (stripos($project->name, $keyword) !== false) {
                    $dept = $createdDepartments[$deptIndex];
                    $project->update(['department_id' => $dept->id]);
                    echo "  📁 Projecto '{$project->name}' → {$dept->name}\n";
                    break;
                }
            }
        }

        echo "\n✅ Departamentos criados com sucesso!\n";
        echo "📊 Resumo:\n";
        foreach ($createdDepartments as $dept) {
            $userCount = $dept->users()->count();
            $projectCount = $dept->projects()->count();
            echo "  • {$dept->name}: {$userCount} usuários, {$projectCount} projectos\n";
        }
    }
}
