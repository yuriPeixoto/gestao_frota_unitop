<?php

namespace App\Modules\Certificados\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class ClassificacaoMulta extends Model
{
    use LogsActivity;

    protected $table = 'classificacao_multa';
    protected $primaryKey = 'id_classificacao_multa';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'descricao_multa',
        'pontos',
        'id_classificacao_multa_cliente'
    ];

    public function multa(): HasMany
    {
        return $this->hasMany(Multa::class, 'id_classificacao_multa');
    }

    public function getDescricaoTipoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
