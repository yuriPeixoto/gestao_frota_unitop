<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Motorista extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'obtermotoristas';
    protected $primaryKey = 'idobtermotorista';
    public $timestamps = false;
    protected $fillable = [
        'idmotorista',
        'nome',
        'tipomotorista',
        'datacontratacao',
        'tipodocumento',
        'numerodocumento',
        'tipocnh',
        'vencimentocnh',
        'telefone',
        'celular',
        'login',
        'senha',
        'generico',
        'ativo',
        'filial',
        'id_configuracao_jornada',
        'id_uf',
        'id_filial',
        'id_operacao'
    ];

    protected static function boot()
    {
        parent::boot();

        // Limpar cache quando um fornecedor for modificado
        static::saved(function ($motorista) {
            Cache::forget('motorista_' . $motorista->idobtermotorista);

            // Limpar o cache dos fornecedores frequentes
            Cache::forget('pre_ordem_listagem');
        });

        static::deleted(function ($fornecedor) {
            Cache::forget('motorista_' . $fornecedor->idobtermotorista);
            Cache::forget('pre_ordem_listagem');
        });
    }

    public function pessoal()
    {
        return $this->hasOne(Pessoal::class, 'id_sascar', 'idmotorista');
    }
}
