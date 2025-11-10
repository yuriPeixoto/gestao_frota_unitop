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
        Schema::create('itens_contrato', function (Blueprint $table) {
            $table->id('id_item_contrato');
            $table->unsignedBigInteger('id_contrato');
            $table->enum('tipo', ['produto', 'servico'])->default('produto');
            $table->unsignedBigInteger('id_produto')->nullable();
            $table->unsignedBigInteger('id_servico')->nullable();
            $table->string('descricao', 500);
            $table->decimal('valor_unitario', 15, 2);
            $table->decimal('quantidade_estimada', 10, 2)->default(0);
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->string('unidade_medida', 50)->nullable();
            $table->decimal('valor_minimo', 15, 2)->nullable();
            $table->decimal('valor_maximo', 15, 2)->nullable();
            $table->boolean('tem_preco_fixo')->default(true);
            $table->text('observacao')->nullable();
            $table->integer('prazo_entrega')->nullable(); // Em dias
            $table->timestamp('data_inclusao')->useCurrent();
            $table->timestamp('data_alteracao')->nullable();
            $table->softDeletes();

            // Índices e chaves estrangeiras
            $table->index('id_contrato');
            $table->index('id_produto');
            $table->index('id_servico');

            $table->foreign('id_contrato')
                ->references('id_contrato')
                ->on('contratos')
                ->onDelete('cascade');

            $table->foreign('id_produto')
                ->references('id_produto')
                ->on('produto')
                ->onDelete('restrict');

            $table->foreign('id_servico')
                ->references('id_servico')
                ->on('servico')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itens_contrato');
    }
};
