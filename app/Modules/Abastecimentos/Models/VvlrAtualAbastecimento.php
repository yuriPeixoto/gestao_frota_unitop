<?php

namespace App\Modules\Abastecimentos\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VvlrAtualAbastecimento extends Model
{
    use LogsActivity;

    protected $table = 'v_vlr_atual_abastecimento';
    protected $primaryKey = 'id_tanque';
    public $timestamps = false;
    protected $fillable = ['tanque',
        'id_bomba',
        'descricao_bomba',
        'combustivel',
        'tipo_combustivel',
        'vlrunitario_interno',
        'data_inicio'
    ];

    public function ValorCombustivelTerceiro(): HasMany
    {
        return $this->hasMany(ValorCombustivelTerceiro::class, 'id_bomba');
    }
}
