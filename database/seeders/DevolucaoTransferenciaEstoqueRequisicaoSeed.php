<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;


class DevolucaoTransferenciaEstoqueRequisicaoSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        DB::connection('pgsql')->table('devolucao_transferencia_estoque_requisicao')->insert([
            'id_devolucao_transferencia_estoque_requisicao' => rand(1, 15),
            'id_produtos_solicitacoes' => rand(1, 15),
            'id_relacao_solicitacoes' => rand(1, 15),
            'id_protudos' => rand(1, 15),
            'situacao_pecas' => rand(1, 15),
            'data_inclusao' => now(),
            'data_alteracao' => now(),
            'qtde_devolucao' => rand(1, 15),
            'quantidade_baixa' => rand(1, 15),
        ]);
    }
}
