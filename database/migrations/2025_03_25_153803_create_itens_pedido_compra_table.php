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
        Schema::create('itens_pedido_compra', function (Blueprint $table) {
            $table->id('id_item_pedido');
            $table->unsignedBigInteger('id_pedido');
            $table->unsignedBigInteger('id_item_solicitacao')->nullable();
            $table->enum('tipo', ['produto', 'servico']);
            $table->string('descricao', 500);
            $table->decimal('quantidade', 10, 2);
            $table->decimal('valor_unitario', 15, 4);
            $table->decimal('valor_total', 15, 4);
            $table->string('unidade_medida', 20)->nullable();
            $table->string('status', 50)->default('pendente'); // pendente, em_processamento, finalizado, cancelado
            $table->timestamps();
            $table->softDeletes();

            // Chaves estrangeiras
            $table->foreign('id_pedido')->references('id_pedido_compras')->on('pedido_compras')
                ->onDelete('cascade');

            $table->foreign('id_item_solicitacao')->references('id_item_solicitacao')->on('itens_solicitacao_compra')
                ->onDelete('set null');

            // Índices para melhorar a performance
            $table->index('id_pedido');
            $table->index('id_item_solicitacao');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itens_pedido_compra');
    }
};
