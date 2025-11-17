<?php

namespace App\Modules\Abastecimentos\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Compras\Models\TipoFornecedor;
use App\Models\Estado;
use Illuminate\Database\Eloquent\Relations\belongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ValorCombustivelTerceiro extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'valor_combustivel_terceiro';
    protected $primaryKey = 'id_valor_combustivel_terceiro';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'valor_diesel',
        'valor_acrescimo',
        'id_tipo_combustivel',
        'data_inicio',
        'data_fim',
        'boma_combustivel',
        'id_usuario',
        'id_filial',
        'valor_terceiro'
    ];

    public function tipoCombustivel(): BelongsTo
    {
        return $this->belongsTo(TipoCombustivel::class, 'id_tipo_combustivel');
    }

    public function bomba(): BelongsTo
    {
        return $this->belongsTo(Bomba::class, 'boma_combustivel');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
