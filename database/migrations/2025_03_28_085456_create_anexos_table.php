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
        Schema::create('anexos', function (Blueprint $table) {
            $table->id();
            $table->string('entidade_tipo', 50)->comment('Tipo da entidade relacionada (solicitacao, pedido, etc)');
            $table->unsignedBigInteger('entidade_id')->nullable()->comment('ID da entidade relacionada');
            $table->string('arquivo_nome')->comment('Nome original do arquivo');
            $table->string('arquivo_path')->comment('Caminho do arquivo no sistema');
            $table->string('arquivo_tipo', 20)->comment('Extensão/tipo do arquivo');
            $table->unsignedBigInteger('tamanho')->comment('Tamanho do arquivo em bytes');
            $table->unsignedBigInteger('usuario_id')->comment('Usuário que fez o upload');
            $table->text('descricao')->nullable()->comment('Descrição opcional do anexo');
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['entidade_tipo', 'entidade_id']);
            $table->index('usuario_id');

            // Chave estrangeira
            $table->foreign('usuario_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anexos');
    }
};
