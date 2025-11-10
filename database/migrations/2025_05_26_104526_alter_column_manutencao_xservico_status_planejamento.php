<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('planejamentomanutencao', function (Blueprint $table) {
            $table->boolean('status_planejamento')->default(true)->change();
        });
    }

    public function down()
    {
        Schema::table('planejamentomanutencao', function (Blueprint $table) {
            $table->boolean('status_planejamento')->change(); // ou o valor anterior
        });
    }
};
