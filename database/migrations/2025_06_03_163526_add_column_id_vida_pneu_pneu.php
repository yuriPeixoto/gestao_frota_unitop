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
        Schema::table('pneu', function (Blueprint $table) {
            $table->foreignId('id_controle_vida_pneu')
                ->nullable()
                ->constrained('controle_vida_pneu', 'id_controle_vida_pneu')
                ->onUpdate('no action')
                ->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pneu', function (Blueprint $table) {
            $table->dropForeign(['id_controle_vida_pneu']);
            $table->dropColumn('id_controle_vida_pneu');
        });
    }
};
