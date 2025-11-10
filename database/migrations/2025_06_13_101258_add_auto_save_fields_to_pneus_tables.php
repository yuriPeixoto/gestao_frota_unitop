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
        // ==========================================
        // TABELA: pneus_aplicados
        // Adicionar campos para rastreamento do auto-save
        // ==========================================

        Schema::table('pneus_aplicados', function (Blueprint $table) {
            // Origem da operação (AUTO_SAVE ou MANUAL)
            $table->string('origem_operacao', 20)->default('MANUAL')->after('sulco_pneu_removido');

            // Destino quando removido (ESTOQUE, MANUTENCAO, DESCARTE)
            $table->string('destino', 20)->nullable()->after('origem_operacao');

            // KM no momento da remoção (para auditoria)
            $table->decimal('km_remocao', 10, 2)->nullable()->after('destino');

            // Sulco no momento da remoção (para auditoria)
            $table->decimal('sulco_remocao', 5, 2)->nullable()->after('km_remocao');

            // Índices para performance
            $table->index('origem_operacao', 'idx_origem_operacao');
            $table->index(['id_veiculo_x_pneu', 'data_alteracao'], 'idx_veiculo_remocao');
        });

        // ==========================================
        // TABELA: pneu
        // Adicionar campo para KM de aplicação
        // ==========================================

        Schema::table('pneu', function (Blueprint $table) {
            // KM quando o pneu foi aplicado
            $table->decimal('km_aplicacao', 10, 2)->nullable()->after('status_pneu');

            // Índice para consultas de performance
            $table->index('km_aplicacao', 'idx_km_aplicacao');
        });

        // ==========================================
        // TABELA: historicopneu (nome correto da tabela existente)
        // Adicionar campos para melhor rastreamento
        // ==========================================

        Schema::table('historicopneu', function (Blueprint $table) {
            // Origem da operação
            $table->string('origem_operacao', 20)->default('MANUAL')->after('status_movimentacao');

            // Observações da operação
            $table->text('observacoes_operacao')->nullable()->after('origem_operacao');

            // Índice para consultas
            $table->index('origem_operacao', 'idx_historico_origem');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pneus_aplicados', function (Blueprint $table) {
            $table->dropIndex('idx_origem_operacao');
            $table->dropIndex('idx_veiculo_remocao');
            $table->dropColumn([
                'origem_operacao',
                'destino',
                'km_remocao',
                'sulco_remocao'
            ]);
        });

        Schema::table('pneu', function (Blueprint $table) {
            $table->dropIndex('idx_km_aplicacao');
            $table->dropColumn('km_aplicacao');
        });

        Schema::table('historicopneu', function (Blueprint $table) {
            $table->dropIndex('idx_historico_origem');
            $table->dropColumn([
                'origem_operacao',
                'observacoes_operacao'
            ]);
        });
    }
};
