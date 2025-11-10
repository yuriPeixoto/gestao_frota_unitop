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
        Schema::table('licenciamentoveiculo', function (Blueprint $table) {
            $table->string('situacao')->nullable(); // Adiciona a coluna situacao do tipo string, permitindo valores nulos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('licenciamentoveiculo', function (Blueprint $table) {
            $table->dropColumn('situacao'); // Remove a coluna situacao
        });
    }
};
