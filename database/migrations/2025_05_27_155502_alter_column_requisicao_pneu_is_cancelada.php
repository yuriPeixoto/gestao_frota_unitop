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
        Schema::table('requisicao_pneu', function (Blueprint $table) {
            $table->boolean('is_impresso')->default(false)->change();
            $table->boolean('is_aprovado')->default(false)->change();
            $table->boolean('is_cancelada')->default(false)->change();
            $table->boolean('venda')->default(false)->change();
            $table->boolean('transferencia_entre_filiais')->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requisicao_pneu', function (Blueprint $table) {
            $table->boolean('is_impresso')->change(); // ou o valor anterior
            $table->boolean('is_aprovado')->change();
            $table->boolean('is_cancelada')->change();
            $table->boolean('venda')->change();
            $table->boolean('transferencia_entre_filiais')->change();
        });
    }
};
