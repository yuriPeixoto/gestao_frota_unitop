<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Modules\Configuracoes\Models\User;
use App\Models\Departamento;

class UserSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $faker = Faker::create();

        $cargo = [
            'Gerente',
            'Funcionario',
            'Diretor(a)',
            'Coordenador(a)',
            'Supervisor(a)',
            'Lider Administrador',
            'Lider Operacional',
            'Analista Administrativo',
            'Estoque',
            'Comprador',
            'Recepcionista - Frota',
            'Instrutor de Motorista',
            'Auxiliar Administrativo',
            'Jovem Aprendiz'
        ];

        // $usuarios = [
        //     'vini@gmail.com',
        //     'vini2@gmail.com',
        //     'vini3@gmail.com',
        // ];

        foreach ($cargo as $value) {
            DB::connection('pgsql')->table('cargo_usuario')->insert([
                'data_inclusao' => now(),
                'data_alteracao' => now(),
                'descricao_cargo' => $value,
            ]);
        }

        foreach ($usuarios as $user) {
            User::create([
                'name' => 'Marcelo',
                'email' => 'marcelo.augsd@gmail.com',
                'password' => bcrypt('12345678'),
            ]);
        }

        // $users = DB::connection('pgsql')->table('users')->get();

        foreach ($users as $user) {
            DB::connection('pgsql')->table('usuario_deparmanto')->insert([
                'data_inclusao' => now(),
                'data_alteracao' => now(),
                'id_user' => $user->id,
                'id_departamento' => rand(1, 3),
                'id_filial' => 1,
                'id_cargo' => rand(1, 4),
            ]);
        }

        for ($i = 1; $i <= 3; $i++) {
            DB::connection('pgsql')->table('usuario_deparmanto')->insert([
                'data_inclusao' => now(),
                'data_alteracao' => now(),
                'id_user' => rand(1, 4),
                'id_departamento' => rand(1, 4),
                'id_cargo' => rand(1, 4),
            ]);
        }
    }
}
