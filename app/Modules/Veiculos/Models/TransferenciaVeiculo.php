<?php

namespace App\Modules\Veiculos\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransferenciaVeiculo extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'transferencia_veiculos';
    protected $primaryKey = 'id_transferencia';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_filial_origem',
        'id_filial_destino',
        'km_transferencia',
        'id_veiculo',
        'data_transferencia',
        'checklist'
    ];


    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }

    public function filialOrigem(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'id_filial_origem', 'id');
    }

    public function filialDestino(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'id_filial_destino', 'id');
    }
}
