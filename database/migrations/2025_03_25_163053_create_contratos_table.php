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
        Schema::create('contratos', function (Blueprint $table) {
            $table->id('id_contrato');
            $table->unsignedBigInteger('id_fornecedor');
            $table->string('numero_contrato', 100)->nullable();
            $table->string('descricao', 500);
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->boolean('ativo')->default(true);
            $table->unsignedBigInteger('id_responsavel')->nullable();
            $table->string('tipo_contrato', 50)->nullable(); // Exemplo: 'produto', 'serviço', 'misto'
            $table->text('observacao')->nullable();
            $table->string('anexo')->nullable(); // Caminho para o arquivo do contrato
            $table->string('status', 50)->default('ativo'); // Exemplo: 'ativo', 'encerrado', 'cancelado', 'em análise'
            $table->boolean('renovacao_automatica')->default(false);
            $table->integer('prazo_notificacao_vencimento')->default(30); // Dias antes do vencimento para notificar
            $table->timestamp('data_inclusao')->useCurrent();
            $table->timestamp('data_alteracao')->nullable();
            $table->softDeletes();

            // Índices e chaves estrangeiras
            $table->index('id_fornecedor');
            $table->index('id_responsavel');
            $table->index(['data_inicio', 'data_fim']);
            $table->index('status');

            $table->foreign('id_fornecedor')
                ->references('id_fornecedor')
                ->on('fornecedor')
                ->onDelete('restrict');

            $table->foreign('id_responsavel')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
