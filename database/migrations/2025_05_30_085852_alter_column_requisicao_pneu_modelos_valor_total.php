<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('requisicao_pneu_modelos', function (Blueprint $table) {
            $table->renameColumn('valor_unitario', 'valor_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requisicao_pneu_modelos', function (Blueprint $table) {
            $table->renameColumn('valor_total', 'valor_unitario');
        });
    }
};
