<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Departamento;


class DepartamentoSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 3; $i++) {
           
            Departamento::create([
                'sigla' => $faker->companySuffix,
                'descricao_departamento' => $faker->text(100),
                'id_filial' => 1,
                'data_inclusao'=> date('Y-m-d H:i:s'),
                'data_alteracao'=> date('Y-m-d H:i:s')
            ]);
        }
    }
}
