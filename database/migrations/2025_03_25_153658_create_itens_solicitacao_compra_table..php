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
        Schema::create('itens_solicitacao_compra', function (Blueprint $table) {
            $table->id('id_item_solicitacao');
            $table->unsignedBigInteger('id_solicitacao');
            $table->enum('tipo', ['produto', 'servico']);
            $table->unsignedBigInteger('id_produto')->nullable();
            $table->unsignedBigInteger('id_servico')->nullable();
            $table->string('descricao', 500);
            $table->decimal('quantidade', 10, 2);
            $table->string('unidade_medida', 20)->nullable();
            $table->string('status', 50)->default('pendente'); // pendente, aprovado, reprovado, finalizado
            $table->text('justificativa')->nullable();
            $table->string('anexo')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Chaves estrangeiras
            $table->foreign('id_solicitacao')->references('id_solicitacoes_compras')->on('solicitacoescompras')
                ->onDelete('cascade');

            // As relações com produto e serviço dependem das tabelas existentes
            // Verificar a estrutura original antes de adicionar estas constraints
            //$table->foreign('id_produto')->references('id_produto')->on('produto');
            //$table->foreign('id_servico')->references('id_servico')->on('servico');

            // Índices para melhorar a performance
            $table->index('id_solicitacao');
            $table->index('id_produto');
            $table->index('id_servico');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itens_solicitacao_compra');
    }
};
