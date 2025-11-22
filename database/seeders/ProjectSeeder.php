<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Objective;
use App\Models\Finance;
use App\Models\Deadline;

class ProjectSeeder extends Seeder
{
    public function run()
    {
        // ==================== PROJECTOS EM ANDAMENTO ====================

        // Projecto 1 - Parque Eólico de Pemba
        $project1 = Project::create([
            'name' => 'PROJETO PARQUE EÓLICO DE PEMBA',
            'description' => 'O projecto Parque Eólico de Pemba, uma iniciativa inovadora implementada pelo Fundo de Energia (FUNAE FP), representa um marco no compromisso de Moçambique com a transição energética sustentável e a redução da dependência de fontes de energia convencionais.',
            'image_url' => '/images/Emblem_of_Mozambique.svg-2.png',
            'provincia' => 'Cabo Delgado',
            'distrito' => 'Pemba',
            'bairro' => 'Zimpeto',
            'category' => 'andamento',
            'data_criacao' => '2024-05-25'
        ]);

        // Objectivos do Projecto 1
        Objective::create([
            'project_id' => $project1->id,
            'title' => 'Produção de Energia Renovável',
            'description' => 'Aproveitar o potencial eólico da região de Pemba para gerar eletricidade limpa e renovável, contribuindo para a diversificação da matriz energética de Moçambique.',
            'order' => 1
        ]);

        Objective::create([
            'project_id' => $project1->id,
            'title' => 'Redução da Dependência de Combustíveis Fósseis',
            'description' => 'Diminuir a dependência de fontes de energia convencionais, como carvão e petróleo, alinhando-se aos objetivos globais de redução de emissões de carbono.',
            'order' => 2
        ]);

        Objective::create([
            'project_id' => $project1->id,
            'title' => 'Acesso à Energia para Comunidades Locais',
            'description' => 'Melhorar o acesso à energia elétrica em comunidades remotas, promovendo o desenvolvimento social e econômico na região.',
            'order' => 3
        ]);

        Objective::create([
            'project_id' => $project1->id,
            'title' => 'Fomento da Sustentabilidade Ambiental',
            'description' => 'Reduzir o impacto ambiental causado pela geração de energia a partir de fontes não renováveis, protegendo os ecossistemas locais.',
            'order' => 4
        ]);

        // Financiamento do Projecto 1
        Finance::create([
            'project_id' => $project1->id,
            'financiador' => 'Enabel',
            'beneficiario' => 'Município de Pemba',
            'responsavel' => 'FUNAE, FP',
            'valor_financiado' => 'USD 25,5 Milhões',
            'codigo' => '#2024/ENABEL/FP'
        ]);

        // Prazos do Projecto 1
        Deadline::create([
            'project_id' => $project1->id,
            'data_aprovacao' => '2024-05-25',
            'data_inicio' => '2024-10-25',
            'data_inspecao' => '2025-05-25',
            'data_finalizacao' => '2026-05-25',
            'data_inauguracao' => '2026-06-25'
        ]);

        // Projecto 2 - Centro de Saúde Comunitário
        $project2 = Project::create([
            'name' => 'CENTRO DE SAÚDE COMUNITÁRIO',
            'description' => 'Construção de centro de saúde para atendimento básico à comunidade.',
            'image_url' => '/images/Emblem_of_Mozambique.svg-2.png',
            'provincia' => 'Maputo',
            'distrito' => 'Matola',
            'bairro' => 'Khongolote',
            'category' => 'andamento',
            'data_criacao' => '2024-04-20'
        ]);

        // Objectivos do Projecto 2
        Objective::create([
            'project_id' => $project2->id,
            'title' => 'Melhorar o Acesso aos Cuidados de Saúde',
            'description' => 'Proporcionar atendimento médico básico à comunidade local.',
            'order' => 1
        ]);

        Objective::create([
            'project_id' => $project2->id,
            'title' => 'Reduzir Mortalidade Infantil',
            'description' => 'Oferecer cuidados pré-natais e pós-natais para reduzir a mortalidade infantil.',
            'order' => 2
        ]);

        // Financiamento do Projecto 2
        Finance::create([
            'project_id' => $project2->id,
            'financiador' => 'MISAU',
            'beneficiario' => 'Comunidade de Khongolote',
            'responsavel' => 'MISAU',
            'valor_financiado' => 'MT 12 Milhões',
            'codigo' => '#2024/MISAU/CS'
        ]);

        // Prazos do Projecto 2
        Deadline::create([
            'project_id' => $project2->id,
            'data_aprovacao' => '2024-05-01',
            'data_inicio' => '2024-06-01',
            'data_inspecao' => '2024-11-15',
            'data_finalizacao' => '2024-12-15',
            'data_inauguracao' => '2025-01-10'
        ]);

        // Projecto 3 - Escola Primária Completa
        $project3 = Project::create([
            'name' => 'ESCOLA PRIMÁRIA COMPLETA',
            'description' => 'Construção de escola primária com 12 salas de aula, biblioteca e campo desportivo.',
            'image_url' => '/images/Emblem_of_Mozambique.svg-2.png',
            'provincia' => 'Gaza',
            'distrito' => 'Xai-Xai',
            'bairro' => 'Mugudo',
            'category' => 'andamento',
            'data_criacao' => '2024-03-15'
        ]);

        // Objectivos do Projecto 3
        Objective::create([
            'project_id' => $project3->id,
            'title' => 'Melhorar o Acesso à Educação',
            'description' => 'Proporcionar infraestrutura educacional adequada para 500 alunos.',
            'order' => 1
        ]);

        Objective::create([
            'project_id' => $project3->id,
            'title' => 'Reduzir Taxa de Abandono Escolar',
            'description' => 'Criar ambiente propício para a permanência das crianças na escola.',
            'order' => 2
        ]);

        // Financiamento do Projecto 3
        Finance::create([
            'project_id' => $project3->id,
            'financiador' => 'MINEDH',
            'beneficiario' => 'Comunidade de Mugudo',
            'responsavel' => 'MINEDH',
            'valor_financiado' => 'MT 28 Milhões',
            'codigo' => '#2024/MINEDH/EP'
        ]);

        // Prazos do Projecto 3
        Deadline::create([
            'project_id' => $project3->id,
            'data_aprovacao' => '2024-03-20',
            'data_inicio' => '2024-04-15',
            'data_inspecao' => '2024-10-30',
            'data_finalizacao' => '2025-02-28',
            'data_inauguracao' => '2025-03-15'
        ]);

        // ==================== PROJECTOS FINALIZADOS ====================

        // Projecto 4 - Sistema de Água Potável
        $project4 = Project::create([
            'name' => 'SISTEMA DE ÁGUA POTÁVEL',
            'description' => 'Projecto de implementação de sistema de abastecimento de água potável para comunidades rurais.',
            'image_url' => '/images/Emblem_of_Mozambique.svg-2.png',
            'provincia' => 'Maputo',
            'distrito' => 'Matola',
            'bairro' => 'Machava',
            'category' => 'finalizados',
            'data_criacao' => '2022-01-10'
        ]);

        // Objectivos do Projecto 4
        Objective::create([
            'project_id' => $project4->id,
            'title' => 'Melhorar o Acesso à Água Potável',
            'description' => 'Garantir o acesso à água potável para comunidades carentes.',
            'order' => 1
        ]);

        Objective::create([
            'project_id' => $project4->id,
            'title' => 'Reduzir Doenças de Veiculação Hídrica',
            'description' => 'Diminuir a incidência de doenças relacionadas com água contaminada.',
            'order' => 2
        ]);

        // Financiamento do Projecto 4
        Finance::create([
            'project_id' => $project4->id,
            'financiador' => 'AIAS, EP',
            'beneficiario' => 'Comunidade de Machava',
            'responsavel' => 'AIAS, EP',
            'valor_financiado' => 'MT 18 Milhões',
            'codigo' => '#2022/AIAS/EP'
        ]);

        // Prazos do Projecto 4
        Deadline::create([
            'project_id' => $project4->id,
            'data_aprovacao' => '2022-01-15',
            'data_inicio' => '2022-03-15',
            'data_inspecao' => '2023-06-30',
            'data_finalizacao' => '2023-08-30',
            'data_inauguracao' => '2023-09-15'
        ]);

        // Projecto 5 - Mercado Municipal
        $project5 = Project::create([
            'name' => 'MERCADO MUNICIPAL',
            'description' => 'Construção de mercado municipal com 50 bancas, sistema de saneamento e área de descarga.',
            'image_url' => '/images/Emblem_of_Mozambique.svg-2.png',
            'provincia' => 'Sofala',
            'distrito' => 'Beira',
            'bairro' => 'Munhava',
            'category' => 'finalizados',
            'data_criacao' => '2021-08-05'
        ]);

        // Objectivos do Projecto 5
        Objective::create([
            'project_id' => $project5->id,
            'title' => 'Fortalecer Economia Local',
            'description' => 'Criar espaço adequado para comércio de produtos locais.',
            'order' => 1
        ]);

        Objective::create([
            'project_id' => $project5->id,
            'title' => 'Melhorar Condições de Trabalho',
            'description' => 'Oferecer infraestrutura digna para os vendedores ambulantes.',
            'order' => 2
        ]);

        // Financiamento do Projecto 5
        Finance::create([
            'project_id' => $project5->id,
            'financiador' => 'CMB',
            'beneficiario' => 'Vendedores de Munhava',
            'responsavel' => 'CMB',
            'valor_financiado' => 'MT 15 Milhões',
            'codigo' => '#2021/CMB/MM'
        ]);

        // Prazos do Projecto 5
        Deadline::create([
            'project_id' => $project5->id,
            'data_aprovacao' => '2021-08-10',
            'data_inicio' => '2021-09-01',
            'data_inspecao' => '2022-05-20',
            'data_finalizacao' => '2022-07-15',
            'data_inauguracao' => '2022-08-01'
        ]);

        // Projecto 6 - Ponte sobre Rio Limpopo
        $project6 = Project::create([
            'name' => 'PONTE SOBRE RIO LIMPOPO',
            'description' => 'Construção de ponte rodoviária com 150 metros de comprimento sobre o Rio Limpopo.',
            'image_url' => '/images/Emblem_of_Mozambique.svg-2.png',
            'provincia' => 'Gaza',
            'distrito' => 'Chokwé',
            'bairro' => 'Sede',
            'category' => 'finalizados',
            'data_criacao' => '2020-11-20'
        ]);

        // Objectivos do Projecto 6
        Objective::create([
            'project_id' => $project6->id,
            'title' => 'Melhorar Acessibilidade',
            'description' => 'Garantir passagem segura durante todo o ano, incluindo época chuvosa.',
            'order' => 1
        ]);

        Objective::create([
            'project_id' => $project6->id,
            'title' => 'Impulsionar Comércio Regional',
            'description' => 'Facilitar o transporte de mercadorias entre distritos.',
            'order' => 2
        ]);

        // Financiamento do Projecto 6
        Finance::create([
            'project_id' => $project6->id,
            'financiador' => 'ANE',
            'beneficiario' => 'População de Chokwé',
            'responsavel' => 'ANE',
            'valor_financiado' => 'MT 45 Milhões',
            'codigo' => '#2020/ANE/PL'
        ]);

        // Prazos do Projecto 6
        Deadline::create([
            'project_id' => $project6->id,
            'data_aprovacao' => '2020-11-25',
            'data_inicio' => '2021-01-10',
            'data_inspecao' => '2022-03-15',
            'data_finalizacao' => '2022-06-30',
            'data_inauguracao' => '2022-07-25'
        ]);

        // ==================== PROJECTOS PARADOS ====================

        // Projecto 7 - Usina Solar de Nampula
        $project7 = Project::create([
            'name' => 'USINA SOLAR DE NAMPULA',
            'description' => 'Instalação de usina solar com capacidade de 10MW para suprir deficit energético da região.',
            'image_url' => '/images/Emblem_of_Mozambique.svg-2.png',
            'provincia' => 'Nampula',
            'distrito' => 'Nampula',
            'bairro' => 'Napipine',
            'category' => 'parados',
            'data_criacao' => '2023-07-10'
        ]);

        // Objectivos do Projecto 7
        Objective::create([
            'project_id' => $project7->id,
            'title' => 'Diversificar Matriz Energética',
            'description' => 'Aumentar a participação de energias renováveis no fornecimento regional.',
            'order' => 1
        ]);

        Objective::create([
            'project_id' => $project7->id,
            'title' => 'Reduzir Apagões',
            'description' => 'Diminuir a frequência de interrupções no fornecimento de energia.',
            'order' => 2
        ]);

        // Financiamento do Projecto 7
        Finance::create([
            'project_id' => $project7->id,
            'financiador' => 'EDM, EP',
            'beneficiario' => 'População de Nampula',
            'responsavel' => 'EDM, EP',
            'valor_financiado' => 'USD 8,5 Milhões',
            'codigo' => '#2023/EDM/US'
        ]);

        // Prazos do Projecto 7
        Deadline::create([
            'project_id' => $project7->id,
            'data_aprovacao' => '2023-07-15',
            'data_inicio' => '2023-08-01',
            'data_inspecao' => '2024-01-30', // Inspeção não realizada
            'data_finalizacao' => '2024-06-30', // Data original de finalização
            'data_inauguracao' => '2024-07-15' // Data original de inauguração
        ]);

        // Projecto 8 - Complexo Habitacional
        $project8 = Project::create([
            'name' => 'COMPLEXO HABITACIONAL',
            'description' => 'Construção de 100 habitações sociais para famílias de baixa renda.',
            'image_url' => '/images/Emblem_of_Mozambique.svg-2.png',
            'provincia' => 'Zambézia',
            'distrito' => 'Quelimane',
            'bairro' => 'Nicolé',
            'category' => 'parados',
            'data_criacao' => '2023-02-28'
        ]);

        // Objectivos do Projecto 8
        Objective::create([
            'project_id' => $project8->id,
            'title' => 'Reduzir Défice Habitacional',
            'description' => 'Oferecer habitação digna para famílias carentes.',
            'order' => 1
        ]);

        Objective::create([
            'project_id' => $project8->id,
            'title' => 'Promover Urbanização Ordenada',
            'description' => 'Contribuir para o desenvolvimento urbano planeado.',
            'order' => 2
        ]);

        // Financiamento do Projecto 8
        Finance::create([
            'project_id' => $project8->id,
            'financiador' => 'IFH',
            'beneficiario' => 'Famílias de Nicolé',
            'responsavel' => 'IFH',
            'valor_financiado' => 'MT 32 Milhões',
            'codigo' => '#2023/IFH/CH'
        ]);

        // Prazos do Projecto 8
        Deadline::create([
            'project_id' => $project8->id,
            'data_aprovacao' => '2023-03-05',
            'data_inicio' => '2023-04-01',
            'data_inspecao' => '2023-09-15', // Inspeção não realizada
            'data_finalizacao' => '2024-03-31', // Data original de finalização
            'data_inauguracao' => '2024-04-15' // Data original de inauguração
        ]);

        // Projecto 9 - Sistema de Drenagem Pluvial
        $project9 = Project::create([
            'name' => 'SISTEMA DE DRENAGEM PLUVIAL',
            'description' => 'Implementação de sistema de drenagem para prevenir inundações em área urbana.',
            'image_url' => '/images/Emblem_of_Mozambique.svg-2.png',
            'provincia' => 'Maputo',
            'distrito' => 'Cidade de Maputo',
            'bairro' => 'Urbanização',
            'category' => 'parados',
            'data_criacao' => '2023-09-12'
        ]);

        // Objectivos do Projecto 9
        Objective::create([
            'project_id' => $project9->id,
            'title' => 'Prevenir Inundações',
            'description' => 'Reduzir impactos das chuvas intensas na comunidade.',
            'order' => 1
        ]);

        Objective::create([
            'project_id' => $project9->id,
            'title' => 'Melhorar Saneamento Básico',
            'description' => 'Garantir escoamento adequado de águas pluviais.',
            'order' => 2
        ]);

        // Financiamento do Projecto 9
        Finance::create([
            'project_id' => $project9->id,
            'financiador' => 'CMCM',
            'beneficiario' => 'Residentes da Urbanização',
            'responsavel' => 'CMCM',
            'valor_financiado' => 'MT 25 Milhões',
            'codigo' => '#2023/CMCM/SD'
        ]);

        // Prazos do Projecto 9
        Deadline::create([
            'project_id' => $project9->id,
            'data_aprovacao' => '2023-09-18',
            'data_inicio' => '2023-10-05',
            'data_inspecao' => '2024-02-20', // Inspeção não realizada
            'data_finalizacao' => '2024-05-30', // Data original de finalização
            'data_inauguracao' => '2024-06-15' // Data original de inauguração
        ]);

        $this->command->info('9 projectos criados com sucesso: 3 em andamento, 3 finalizados e 3 parados!');
    }
}
