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
        Schema::create('orcamentos', function (Blueprint $table) {
            $table->id('id_orcamento');
            $table->unsignedBigInteger('id_pedido');
            $table->unsignedBigInteger('id_fornecedor');
            $table->date('data_orcamento');
            $table->decimal('valor_total', 15, 4);
            $table->integer('prazo_entrega')->nullable()->comment('Prazo de entrega em dias');
            $table->date('validade')->nullable()->comment('Data de validade do orçamento');
            $table->text('observacao')->nullable();
            $table->string('anexo')->nullable();
            $table->boolean('selecionado')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Chaves estrangeiras
            $table->foreign('id_pedido')->references('id_pedido_compras')->on('pedido_compras')
                ->onDelete('cascade');

            $table->foreign('id_fornecedor')->references('id_fornecedor')->on('fornecedor')
                ->onDelete('cascade');

            // Índices para melhorar a performance
            $table->index('id_pedido');
            $table->index('id_fornecedor');
            $table->index('selecionado');
        });

        Schema::create('itens_orcamento', function (Blueprint $table) {
            $table->id('id_item_orcamento');
            $table->unsignedBigInteger('id_orcamento');
            $table->unsignedBigInteger('id_item_pedido');
            $table->decimal('valor_unitario', 15, 4);
            $table->decimal('quantidade', 10, 2);
            $table->decimal('valor_total', 15, 4);
            $table->text('observacao')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Chaves estrangeiras
            $table->foreign('id_orcamento')->references('id_orcamento')->on('orcamentos')
                ->onDelete('cascade');

            $table->foreign('id_item_pedido')->references('id_item_pedido')->on('itens_pedido_compra')
                ->onDelete('cascade');

            // Índices para melhorar a performance
            $table->index('id_orcamento');
            $table->index('id_item_pedido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itens_orcamento');
        Schema::dropIfExists('orcamentos');
    }
};
