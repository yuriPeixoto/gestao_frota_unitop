<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pneus_aplicados', function (Blueprint $table) {
            $table->softDeletes(); // adiciona a coluna deleted_at
            $table->boolean('is_ativo')->default(true); // adiciona a coluna is_ativo

        });
    }

    public function down()
    {
        Schema::table('pneus_aplicados', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('is_ativo'); // remove a coluna is_ativo

        });
    }
};
