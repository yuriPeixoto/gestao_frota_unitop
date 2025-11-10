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
        Schema::create('estoque_movimentos', function (Blueprint $table) {
            $table->id('id_movimento');
            $table->integer('id_estoque_item');
            $table->enum('tipo_movimento', ['entrada', 'saida', 'ajuste', 'transferencia']);
            $table->decimal('quantidade', 10, 2);
            $table->string('origem', 50)->nullable()->comment('Origem do movimento (compra, devolução, etc) - para entradas');
            $table->string('destino', 50)->nullable()->comment('Destino do movimento (requisição, venda, etc) - para saídas');
            $table->integer('id_referencia')->nullable()->comment('ID do documento que originou o movimento (pedido, requisição, etc)');
            $table->integer('id_usuario')->nullable()->comment('ID do usuário que realizou o movimento');
            $table->text('observacao')->nullable();
            $table->timestamp('data_movimento');

            // Índices
            $table->index('id_estoque_item');
            $table->index('tipo_movimento');
            $table->index('data_movimento');
            $table->index('id_referencia');

            // Chaves estrangeiras
            $table->foreign('id_estoque_item')
                ->references('id_estoque_item')
                ->on('estoque_itens')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estoque_movimentos');
    }
};
