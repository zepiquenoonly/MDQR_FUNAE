<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;

class AdditionalUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "👥 Criando Técnicos e Gestores adicionais...\n\n";

        $departments = Department::all();

        if ($departments->isEmpty()) {
            echo "⚠️  Nenhum departamento encontrado. Execute DepartmentSeeder primeiro.\n";
            return;
        }

        // Criar Gestores adicionais (1-2 por departamento)
        $gestorData = [
            // Infraestrutura
            ['name' => 'Gestor de Infraestrutura', 'email' => 'gestor.infra@funae.co.mz', 'username' => 'gestor_infra', 'dept' => 0],
            ['name' => 'Gestor de Obras', 'email' => 'gestor.obras@funae.co.mz', 'username' => 'gestor_obras', 'dept' => 0],
            
            // Energia
            ['name' => 'Gestor de Energia', 'email' => 'gestor.energia@funae.co.mz', 'username' => 'gestor_energia', 'dept' => 1],
            ['name' => 'Gestor de Electrificação', 'email' => 'gestor.electrificacao@funae.co.mz', 'username' => 'gestor_electrificacao', 'dept' => 1],
            
            // Água e Saneamento
            ['name' => 'Gestor de Água', 'email' => 'gestor.agua@funae.co.mz', 'username' => 'gestor_agua', 'dept' => 2],
            ['name' => 'Gestor de Saneamento', 'email' => 'gestor.saneamento@funae.co.mz', 'username' => 'gestor_saneamento', 'dept' => 2],
            
            // Educação
            ['name' => 'Gestor de Educação', 'email' => 'gestor.educacao@funae.co.mz', 'username' => 'gestor_educacao', 'dept' => 3],
            
            // Saúde
            ['name' => 'Gestor de Saúde', 'email' => 'gestor.saude@funae.co.mz', 'username' => 'gestor_saude', 'dept' => 4],
        ];

        echo "📋 Criando Gestores:\n";
        foreach ($gestorData as $data) {
            $dept = $departments[$data['dept']];
            
            $gestor = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => bcrypt('password'),
                'phone' => '+258 84 ' . rand(1000000, 9999999),
                'province' => 'Maputo',
                'district' => 'Maputo',
                'neighborhood' => 'Centro',
                'department_id' => $dept->id,
            ]);
            
            $gestor->assignRole('Gestor');
            echo "  ✅ {$gestor->name} → {$dept->name}\n";
        }

        // Criar Técnicos adicionais (2-4 por departamento)
        $tecnicoData = [
            // Infraestrutura (4 técnicos)
            ['name' => 'Técnico de Construção Civil', 'email' => 'tec.civil@funae.co.mz', 'username' => 'tec_civil', 'dept' => 0],
            ['name' => 'Técnico de Estradas', 'email' => 'tec.estradas@funae.co.mz', 'username' => 'tec_estradas', 'dept' => 0],
            ['name' => 'Técnico de Pontes', 'email' => 'tec.pontes@funae.co.mz', 'username' => 'tec_pontes', 'dept' => 0],
            ['name' => 'Técnico de Edificações', 'email' => 'tec.edificacoes@funae.co.mz', 'username' => 'tec_edificacoes', 'dept' => 0],
            
            // Energia (4 técnicos)
            ['name' => 'Técnico Electricista', 'email' => 'tec.electricista@funae.co.mz', 'username' => 'tec_electricista', 'dept' => 1],
            ['name' => 'Técnico de Energia Solar', 'email' => 'tec.solar@funae.co.mz', 'username' => 'tec_solar', 'dept' => 1],
            ['name' => 'Técnico de Energia Eólica', 'email' => 'tec.eolica@funae.co.mz', 'username' => 'tec_eolica', 'dept' => 1],
            ['name' => 'Técnico de Redes Eléctricas', 'email' => 'tec.redes@funae.co.mz', 'username' => 'tec_redes', 'dept' => 1],
            
            // Água e Saneamento (3 técnicos)
            ['name' => 'Técnico de Hidráulica', 'email' => 'tec.hidraulica@funae.co.mz', 'username' => 'tec_hidraulica', 'dept' => 2],
            ['name' => 'Técnico de Saneamento', 'email' => 'tec.saneamento@funae.co.mz', 'username' => 'tec_saneamento', 'dept' => 2],
            ['name' => 'Técnico de Tratamento de Água', 'email' => 'tec.tratamento@funae.co.mz', 'username' => 'tec_tratamento', 'dept' => 2],
            
            // Educação (2 técnicos)
            ['name' => 'Técnico de Manutenção Escolar', 'email' => 'tec.escolar@funae.co.mz', 'username' => 'tec_escolar', 'dept' => 3],
            ['name' => 'Técnico de Infraestrutura Educacional', 'email' => 'tec.educacional@funae.co.mz', 'username' => 'tec_educacional', 'dept' => 3],
            
            // Saúde (2 técnicos)
            ['name' => 'Técnico de Infraestrutura Hospitalar', 'email' => 'tec.hospitalar@funae.co.mz', 'username' => 'tec_hospitalar', 'dept' => 4],
            ['name' => 'Técnico de Equipamentos Médicos', 'email' => 'tec.medico@funae.co.mz', 'username' => 'tec_medico', 'dept' => 4],
        ];

        echo "\n🔧 Criando Técnicos:\n";
        foreach ($tecnicoData as $data) {
            $dept = $departments[$data['dept']];
            
            $tecnico = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => bcrypt('password'),
                'phone' => '+258 84 ' . rand(1000000, 9999999),
                'province' => 'Maputo',
                'district' => 'Maputo',
                'neighborhood' => 'Centro',
                'department_id' => $dept->id,
            ]);
            
            $tecnico->assignRole('Técnico');
            echo "  ✅ {$tecnico->name} → {$dept->name}\n";
        }

        echo "\n✅ Usuários adicionais criados com sucesso!\n";
        echo "\n📊 Resumo por Departamento:\n";
        
        foreach ($departments as $dept) {
            $gestores = $dept->users()->whereHas('roles', function($q) {
                $q->where('name', 'Gestor');
            })->count();
            
            $tecnicos = $dept->users()->whereHas('roles', function($q) {
                $q->where('name', 'Técnico');
            })->count();
            
            echo "  🏢 {$dept->name}:\n";
            echo "     - Gestores: {$gestores}\n";
            echo "     - Técnicos: {$tecnicos}\n";
        }
        
        echo "\n🔑 Todos os usuários têm a senha: password\n";
    }
}
