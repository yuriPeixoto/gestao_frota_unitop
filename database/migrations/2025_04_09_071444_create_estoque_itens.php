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
        Schema::create('estoque_itens', function (Blueprint $table) {
            $table->id('id_estoque_item');
            $table->integer('id_estoque');
            $table->integer('id_produto');
            $table->decimal('quantidade_atual', 10, 2)->default(0);
            $table->decimal('quantidade_minima', 10, 2)->default(0);
            $table->decimal('quantidade_maxima', 10, 2)->nullable();
            $table->string('localizacao', 100)->nullable()->comment('Localização física do item no estoque (prateleira, corredor, etc)');
            $table->timestamp('data_ultima_entrada')->nullable();
            $table->timestamp('data_ultima_saida')->nullable();
            $table->timestamp('data_inclusao');
            $table->timestamp('data_alteracao')->nullable();
            $table->boolean('ativo')->default(true);

            // Índices
            $table->index('id_estoque');
            $table->index('id_produto');
            $table->unique(['id_estoque', 'id_produto']);

            // Chaves estrangeiras
            $table->foreign('id_estoque')
                ->references('id_estoque')
                ->on('estoque')
                ->onDelete('cascade');

            // Não adicionamos foreign key em id_produto aqui porque
            // a DDL da tabela produtos não foi fornecida e não queremos
            // assumir como é a chave primária dela
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estoque_itens');
    }
};
