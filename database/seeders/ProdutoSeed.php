<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class ProdutoSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 20; $i++) {
            DB::connection('pgsql')->table('produto')->insert([
                'id_filial' => rand(1, 27),
                'data_inclusao' => now(),
                'descricao_produto' => $faker->word(),
                'is_original' => $faker->boolean(),
                'curva_abc' => $faker->randomElement(['A', 'B', 'C']),
                'tempo_garantia' => rand(1, 27),

            ]);
        }
    }
}
