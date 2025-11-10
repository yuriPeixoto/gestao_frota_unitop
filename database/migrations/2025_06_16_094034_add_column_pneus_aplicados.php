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
        Schema::table('pneus_aplicados', function (Blueprint $table) {
            // Verificar se as colunas já existem antes de adicionar
            if (!Schema::hasColumn('pneus_aplicados', 'origem_operacao')) {
                $table->string('origem_operacao', 20)->nullable()->after('sulco_pneu_removido')->comment('AUTO_SAVE ou MANUAL');
            }

            if (!Schema::hasColumn('pneus_aplicados', 'destino')) {
                $table->string('destino', 50)->nullable()->after('origem_operacao')->comment('Destino do pneu quando removido');
            }

            if (!Schema::hasColumn('pneus_aplicados', 'km_remocao')) {
                $table->decimal('km_remocao', 10, 2)->nullable()->after('destino')->comment('KM no momento da remoção');
            }

            if (!Schema::hasColumn('pneus_aplicados', 'sulco_remocao')) {
                $table->decimal('sulco_remocao', 5, 2)->nullable()->after('km_remocao')->comment('Sulco no momento da remoção');
            }

            // Adicionar índices para melhor performance
            if (!Schema::hasIndex('pneus_aplicados', ['id_veiculo_x_pneu', 'deleted_at'])) {
                $table->index(['id_veiculo_x_pneu', 'deleted_at'], 'idx_veiculo_pneu_ativo');
            }

            if (!Schema::hasIndex('pneus_aplicados', ['id_pneu', 'localizacao'])) {
                $table->index(['id_pneu', 'localizacao'], 'idx_pneu_localizacao');
            }

            if (!Schema::hasIndex('pneus_aplicados', 'origem_operacao')) {
                $table->index('origem_operacao');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pneus_aplicados', function (Blueprint $table) {
            // Remover índices
            $table->dropIndex('idx_veiculo_pneu_ativo');
            $table->dropIndex('idx_pneu_localizacao');
            $table->dropIndex(['origem_operacao']);

            // Remover colunas
            $table->dropColumn([
                'origem_operacao',
                'destino',
                'km_remocao',
                'sulco_remocao'
            ]);
        });
    }
};
