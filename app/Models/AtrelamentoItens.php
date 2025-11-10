<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtrelamentoItens extends Model
{
    use LogsActivity;

    protected $table = 'atrelamento_itens';
    protected $primaryKey = 'id_atrelamento_itens';
    public $timestamps = false;
    protected $fillable = [
        "data_inclusao",
        "data_alteracao",
        "id_atrelamento",
        "id_carreta",
        "km_inicial_carreta",
        "km_final_carreta",
        "hr_inicial_atrelamento_carreta",
        "hr_final_atrelamento_carreta",
        "thermo_king",
    ];

    public function filialAtrelamento(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'id_filial', 'id');
    }

    public function userAtrelamento()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo', 'id_veiculo');
    }
}
