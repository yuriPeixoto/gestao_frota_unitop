<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtrelamentoVeiculo extends Model
{
    use LogsActivity;

    protected $table = 'atrelamento';
    protected $primaryKey = 'id_atrelamento';
    public $timestamps = false;
    protected $fillable = [
        "data_inclusao",
        "data_alteracao",
        "id_cavalo",
        "status",
        "data_atrelamento",
        "data_desatrelamento",
        "km_inicial_cavalo",
        "km_final_cavalo",
        "hr_inicial_atrelamento_cavalo",
        "hr_final_atrelamento_cavalo",
        "km_rodado_carreta",
        "id_usuario",
        "id_filial",
        "km_hr_inicial_cavalo",
        "km_hr_final_cavalo",
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
        return $this->belongsTo(Veiculo::class, 'id_cavalo', 'id_veiculo');
    }
}
