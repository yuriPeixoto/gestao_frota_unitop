<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class TipoVeiculo extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $tipo = [
            "caminhão",
            "Furgão",
            "Carreta",
            "HR",
            "Leve",
            "Motocicleta",
            "thermo King",
        ];


        foreach ($tipo as  $value) {
            DB::connection('pgsql')->table('tipo_veiculo')->insert([
                [
                    "descricao" => $value,
                    "data_inclusao" => date('Y-m-d H:i:s'),
                    "data_alteracao" => date('Y-m-d H:i:s')
                ]
            ]);
        }
    }
}
