<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class EntradaAfericaoAbastecimento extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'entrada_afericao_abastecimento';
    protected $primaryKey = 'id_entrada_afericao_abastecimento';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_usuario',
        'id_abastecimento_integracao',
        'id_tanque',
        'volume_abastecimento',
        'volume_entrada'
    ];

    public function abastecimentoIntegracao()
    {
        return $this->belongsTo(AbastecimentoIntegracao::class, 'id_abastecimento_integracao');
    }
}
