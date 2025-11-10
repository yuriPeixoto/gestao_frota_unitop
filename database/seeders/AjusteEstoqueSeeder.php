<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class AjusteEstoqueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 15; $i++) {
            DB::connection('pgsql')->table('produto')->insert([
            DB::connection('pgsql')->table('produto')->insert([
                'id_filial' => $faker->numberBetween(1, 10),
                'data_inclusao' => now(),
                'data_alteracao' => now(),
                'descricao_produto' => $faker->word(),
                'is_original' => $faker->boolean(),
                'curva_abc' => $faker->randomElement(['A', 'B', 'C']),
                'tempo_garantia' => $faker->numberBetween(0, 36),
                'id_unidade_produto' => $faker->numberBetween(1, 5),
                'ncm' => $faker->numerify('########'),
                'estoque_minimo' => rand(1, 100),
                'estoque_maximo' => rand(1, 100),
                'localizacao_produto' => $faker->word(),
                'quantidade_atual_produto' => $faker->randomFloat(2, 0, 500),
                'imagem_produto' => $faker->imageUrl(),
                'id_estoque_produto' => $faker->randomElement([44, 45]),
                'id_grupo_servico' => $faker->numberBetween(1, 10),
                'id_produto_subgrupo' => $faker->numberBetween(1, 10),
                'valor_medio' => $faker->randomFloat(2, 10, 500),
                'nome_imagem' => $faker->word() . '.jpg',
                'codigo_produto' => $faker->unique()->numerify('PROD#####'),
                'cod_fabricante_' => $faker->unique()->numerify('FAB#####'),
                'cod_alternativo_1_' => $faker->optional()->numerify('ALT#####'),
                'cod_alternativo_2_' => $faker->optional()->numerify('ALT#####'),
                'cod_alternativo_3_' => $faker->optional()->numerify('ALT#####'),
                'id_modelo_pneu' => $faker->optional()->numberBetween(1, 10),
                'is_ativo' => $faker->boolean(),
                'descricao_min' => $faker->sentence(),
                'is_imobilizado' => $faker->boolean(),
                'id_tipo_imobilizados' => $faker->optional()->numberBetween(1, 5),
                'marca' => $faker->company(),
                'modelo' => $faker->word(),
                'pre_cadastro' => $faker->boolean(),
                'id_user_edicao' => $faker->numberBetween(1, 10),
                'id_user_cadastro' => $faker->numberBetween(1, 10),
            ]);
        }

        for ($i = 0; $i < 2; $i++) {
            DB::connection('pgsql')->table('produtos_por_filial')->insert([
            DB::connection('pgsql')->table('produtos_por_filial')->insert([
                'id_produtos_por_filial' => rand(100, 1000),
                'data_inclusao' => now(),
                'data_alteracao' => now(),
                'id_filial' => $faker->numberBetween(1, 10),
                'id_produto_unitop' => rand(1, 15), // Relacionado com Produto
                'quantidade_produto' => $faker->randomFloat(2, 1, 100),
                'valor_medio' => $faker->randomFloat(2, 10, 500),
                'localizacao' => $faker->word(),
                'id_estoque' => 46
            ]);
        }
        // for ($i=0; $i < 20; $i++) {
        //     DB::connection('pgsql')->table('acerto_estoque')->insert([
        //         'quantidade_acerto' =>rand(1,10),
        //         'preco_medio'       =>$faker->randomFloat(2, 10, 1000),
        //         'data_acerto'       =>$faker->date(),
        //         'quantidade_atual'  =>rand(1,100),
        //         'id_usuario_acerto' =>rand(1,3),
        //         'data_acerto'       =>$faker->date(),
        //         'data_inclusao'     =>$faker->date()
        //     ]);
        // }
    }
}
