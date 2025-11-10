<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_filial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('filial_id'); // Sem restrição de chave estrangeira porque v_filial é uma view
            $table->timestamps();

            // Garantir que cada combinação de usuário-filial seja única
            $table->unique(['user_id', 'filial_id']);
        });

        // Migração de dados existentes (SEM usar relacionamentos Eloquent, apenas queries brutas)
        $this->migrateExistingData();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_filial');
    }

    /**
     * Migra os dados existentes para a nova estrutura.
     * Usa SQL puro para evitar problemas com relacionamentos que ainda não estão configurados.
     *
     * @return void
     */
    private function migrateExistingData()
    {
        // Usar SQL puro para evitar problemas com relacionamentos ainda não disponíveis
        $users = DB::connection('pgsql')->table('users')
            ->whereNotNull('filial_id')
            ->select('id', 'filial_id')
            ->orderBy('id')
            ->get();

        foreach ($users as $user) {
            // Verificar se o relacionamento já não existe
            $exists = DB::connection('pgsql')->table('user_filial')
                ->where('user_id', $user->id)
                ->where('filial_id', $user->filial_id)
                ->exists();

            if (!$exists) {
                // Inserir o relacionamento na nova tabela
                DB::connection('pgsql')->table('user_filial')->insert([
                    'user_id' => $user->id,
                    'filial_id' => $user->filial_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
