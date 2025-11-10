<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Factory as Faker;

class ChecklistSeed extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();


        foreach (range(1, 3) as $index) {
            DB::connection('pgsql')->table('tipo_checklist')->insert([
                'nome' => $faker->name,
                'descricao' => $faker->text,
                'departamento_id' => 1,
                'multiplas_etapas' => true,
            ]);
        }

        foreach (range(1, 101) as $index) {
            $numero = rand(1, 3);
            $startDate = Carbon::create(2022, 1, 1, 0, 0, 0);
            $endDate = Carbon::create(2024, 12, 31, 23, 59, 59);

            $randomSeconds = rand(0, $endDate->diffInSeconds($startDate));
            $randomTimestamp = $startDate->addSeconds($randomSeconds);

            DB::connection('pgsql')->table('checklists')->insert([
                'nome' =>    $faker->name,
                'descricao' => $faker->text,
                'tipo_checklist_id' => $numero,
                'created_at' => $randomTimestamp
            ]);
        }

        foreach (range(1, 10) as $index) {
            $opcoes = ['foto', 'assinatura', 'assinatura'];
            $randomico = $opcoes[array_rand($opcoes)];
            DB::connection('pgsql')->table('coluna_checklists')->insert([
                'descricao' => $faker->text,
                'checklist_id' => 1,
                'relacionado_a_tabela' => 1,
                'chave_estrangeira_relacionado' => 1,
                'tipo' =>   'foto'
            ]);
        }
    }
}
