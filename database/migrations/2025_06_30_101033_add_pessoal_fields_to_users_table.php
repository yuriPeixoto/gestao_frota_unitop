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
        Schema::table('users', function (Blueprint $table) {
            // Campos básicos pessoais
            $table->integer('matricula')->nullable()->unique()->after('cpf');
            $table->string('rg', 25)->nullable()->after('matricula');
            $table->string('orgao_emissor', 25)->nullable()->after('rg');
            $table->date('data_nascimento')->nullable()->after('orgao_emissor');
            $table->date('data_admissao')->nullable()->after('data_nascimento');

            // Campos CNH
            $table->bigInteger('cnh')->nullable()->after('data_admissao');
            $table->date('validade_cnh')->nullable()->after('cnh');
            $table->string('tipo_cnh', 10)->nullable()->after('validade_cnh');

            // Campos complementares
            $table->bigInteger('pis')->nullable()->after('tipo_cnh');
            $table->string('imagem_pessoal', 150)->nullable()->after('pis');

            // Índices para melhor performance
            $table->index('matricula');
            $table->index('rg');
            $table->index('data_admissao');
            $table->index('validade_cnh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['matricula']);
            $table->dropIndex(['rg']);
            $table->dropIndex(['data_admissao']);
            $table->dropIndex(['validade_cnh']);

            $table->dropColumn([
                'matricula',
                'rg',
                'orgao_emissor',
                'data_nascimento',
                'data_admissao',
                'cnh',
                'validade_cnh',
                'tipo_cnh',
                'pis',
                'imagem_pessoal'
            ]);
        });
    }
};
