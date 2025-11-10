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
        Schema::create('itens_nota_fiscal', function (Blueprint $table) {
            $table->id('id_item_nota');
            $table->unsignedBigInteger('id_nota_fiscal');
            $table->unsignedBigInteger('id_item_pedido')->nullable();
            $table->string('descricao', 500);
            $table->decimal('quantidade', 10, 2);
            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('valor_total', 10, 2);
            $table->string('unidade_medida', 50)->nullable();
            $table->string('cfop', 10)->nullable();
            $table->string('ncm', 20)->nullable();
            $table->decimal('valor_icms', 10, 2)->nullable();
            $table->decimal('valor_ipi', 10, 2)->nullable();
            $table->decimal('aliquota_icms', 5, 2)->nullable();
            $table->decimal('aliquota_ipi', 5, 2)->nullable();
            $table->string('codigo_produto_fornecedor', 100)->nullable();
            $table->timestamp('data_inclusao')->useCurrent();
            $table->timestamp('data_alteracao')->nullable();
            $table->softDeletes();

            // Índices e chaves estrangeiras
            $table->index('id_nota_fiscal');
            $table->index('id_item_pedido');

            $table->foreign('id_nota_fiscal')
                ->references('id_nota_fiscal')
                ->on('notas_fiscais')
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
        Schema::dropIfExists('itens_nota_fiscal');
    }
};
