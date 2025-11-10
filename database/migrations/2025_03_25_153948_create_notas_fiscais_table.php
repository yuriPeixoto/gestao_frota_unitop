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
        Schema::create('notas_fiscais', function (Blueprint $table) {
            $table->id('id_nota_fiscal');
            $table->unsignedBigInteger('id_pedido');
            $table->unsignedBigInteger('id_fornecedor');
            $table->string('numero_nota', 50);
            $table->string('serie', 20)->nullable();
            $table->date('data_emissao');
            $table->date('data_recebimento')->nullable();
            $table->decimal('valor_total', 15, 4);
            $table->string('anexo')->nullable();
            $table->text('observacao')->nullable();
            $table->string('status', 50)->default('recebida'); // recebida, conferida, aprovada, recusada
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
            $table->index('numero_nota');
            $table->index('status');
        });

        Schema::create('itens_nota_fiscal', function (Blueprint $table) {
            $table->id('id_item_nota');
            $table->unsignedBigInteger('id_nota_fiscal');
            $table->unsignedBigInteger('id_item_pedido')->nullable();
            $table->string('descricao', 500);
            $table->decimal('quantidade', 10, 2);
            $table->decimal('valor_unitario', 15, 4);
            $table->decimal('valor_total', 15, 4);
            $table->timestamps();
            $table->softDeletes();

            // Chaves estrangeiras
            $table->foreign('id_nota_fiscal')->references('id_nota_fiscal')->on('notas_fiscais')
                ->onDelete('cascade');

            $table->foreign('id_item_pedido')->references('id_item_pedido')->on('itens_pedido_compra')
                ->onDelete('set null');

            // Índices para melhorar a performance
            $table->index('id_nota_fiscal');
            $table->index('id_item_pedido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itens_nota_fiscal');
        Schema::dropIfExists('notas_fiscais');
    }
};
