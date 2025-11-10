<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPneusAplicadosToOrdemServicoPecas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('ordem_servico_pecas', 'pneus_aplicados')) {
            Schema::table('ordem_servico_pecas', function (Blueprint $table) {
                // Usar jsonb quando possível (Postgres). Laravel Schema converte para JSON na maioria dos DBs.
                $table->json('pneus_aplicados')->nullable()->after('observacoes');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('ordem_servico_pecas', 'pneus_aplicados')) {
            Schema::table('ordem_servico_pecas', function (Blueprint $table) {
                $table->dropColumn('pneus_aplicados');
            });
        }
    }
}
