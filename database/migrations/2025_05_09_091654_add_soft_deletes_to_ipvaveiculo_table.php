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
        Schema::table('ipvaveiculo', function (Blueprint $table) {
            $table->softDeletes(); // Adiciona a coluna deleted_at para soft deletes
            $table->boolean('is_ativo')->default(true); // Adiciona a coluna is_ativo com valor padrão true
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipvaveiculo', function (Blueprint $table) {
            $table->dropSoftDeletes(); // Remove a coluna deleted_at
            $table->dropColumn('is_ativo'); // Remove a coluna is_ativo
        });
    }
};
