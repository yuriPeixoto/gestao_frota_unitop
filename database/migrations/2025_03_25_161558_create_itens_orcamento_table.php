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
        Schema::create('itens_orcamento', function (Blueprint $table) {
            $table->id('id_item_orcamento');
            $table->unsignedBigInteger('id_orcamento');
            $table->unsignedBigInteger('id_item_pedido')->nullable();
            $table->string('descricao', 500)->nullable();
            $table->decimal('quantidade', 10, 2);
            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('valor_total', 10, 2);
            $table->text('observacao')->nullable();
            $table->timestamp('data_inclusao')->useCurrent();
            $table->timestamp('data_alteracao')->nullable();
            $table->softDeletes();

            // Índices e chaves estrangeiras
            $table->index('id_orcamento');
            $table->index('id_item_pedido');

            $table->foreign('id_orcamento')
                ->references('id_orcamento')
                ->on('orcamentos')
                ->onDelete('cascade');

            $table->foreign('id_item_pedido')
                ->references('id_item_pedido')
                ->on('itens_pedido_compra')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itens_orcamento');
    }
};
