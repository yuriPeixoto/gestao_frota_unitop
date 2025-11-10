<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class grupoServicoSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            "ARREFECIMENTO",
            "MOTOR",
            "INTERCOOLER",
            "TURBINA",
            "BOMBA INJETORA",
            "BICOS INJETORES",
            "ALIMENTAÇÃO",
            "ADMISSÃO",
            "COMPRESSOR DE AR",
            "EMBREAGEM",
            "CAIXA DE CÂMBIO MECÂNICA",
            "CAIXA DE CÂMBIO AUTOM.",
            "DIREÇÃO",
            "DIREÇÃO HIDRÁULICA",
            "FREIOS",
            "TRANSMISSÃO (CARDAN)",
            "DIFERENCIAL",
            "SUSPENSÃO",
            "SUSPENSÃO A AR",
            "QUINTA RODA",
            "CHASSIS",
            "EIXO DIANT. DIRECIONAL",
            "EIXO TRASEIRO TRAÇÃO",
            "EIXO TRUCK",
            "PRIMEIRO EIXO",
            "SEGUNDO EIXO",
            "TERCEIRO EIXO",
            "GEOMETRIA",
            "RODAGEM/PNEUS",
            "RODOAR",
            "TACÓGRAFO",
            "AR CONDICIONADO",
            "CARROCERIA",
            "CAPOTARIA",
            "PINTURA",
            "CABINE",
            "ACESSÓRIOS",
            "LANTERNAGEM",
            "LIMPEZA",
            "LUBRIFICANTE/COMBUSTÍVEL",
            "ELÉTRICA",
            "MOTOR DE PARTIDA",
            "ALTERNADOR",
            "ESCAPAMENTO",
            "FIXADORES",
            "SISTEMA HIDRÁULICO",
            "SISTEMA DE GIRO",
            "SISTEMA DE VIBRAÇÃO",
            "SISTEMA DE COMANDO",
            "MATERIAL DESGASTE",
            "REFRIGERAÇÃO",
            "INJEÇÃO ELETRÔNICA",
            "FERRAMENTAS",
            "E.P.I.",
            "SISTEMA DE AR",
            "LAVAGEM",
            "ARLA",
            "RASTREADOR",
            "BORRACHARIA",
            "LUBRIFICAÇÃO",
            "LAVA-JATO",
            "TORNEARIA",
            "APARELHOS TELEFÔNICOS",
            "DIVERSOS",
            "PASSAGEM AÉREA",
            "SERVIÇO EXTERNO",
            "HOSPEDAGEM",
            "INFORMATICA",
            "SOFTWARE",
            "POSTOS COMBUSTIVEIS",
            "COMBUSTIVEIS",
            "INCENDIO",
            "EQUIPAMENTO",
            "OFICINA",
            "BRINDES",
            "GUINDASTE / MUNCK",
            "JARDINAGEM",
            "MATERIAIS DE CONSUMO",
            "MANUTENÇÃO PREDIAL (SERVIÇOS E MATERIAIS)",
            "MOVEIS",
            "OPERACIONAIS (MADEIRITE, PALLET E GAIOLA)",
            "GRÁFICA / IMPRESSÕES / PLOTAGEM",
            "CONSTRUÇÃO",
            "MEDICINA DO TRABALHO",
            "FISCAL",
            "MODULOS / UNID DE COMANDO",
        ];


        foreach ($categorias as  $value) {
            DB::connection('pgsql')->table('grupo_servico')->insert([
                [
                    "descricao_grupo" => $value,
                    "data_inclusao" => date('Y-m-d H:i:s'),
                    "data_alteracao" => date('Y-m-d H:i:s')
                ]
            ]);
        }
    }
}
