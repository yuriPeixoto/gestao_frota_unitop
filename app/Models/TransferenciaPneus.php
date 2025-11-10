<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransferenciaPneus extends Model
{
    use LogsActivity;

    protected $table = 'transferencia_pneus';

    protected $primaryKey = 'id_transferencia_pneus';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_filial',
        'id_usuario',
        'aprovado',
        'situacao',
        'usuario_baixa',
        'filial_baixa',
        'observacao_baixa',
        'observacao_saida',
        'recebido',
        'id_saida_pneu'
    ];

    public function filial(): BelongsTo
    {
        return $this->belongsTo(VFilial::class, 'id_filial');
    }

    public function filialBaixa(): BelongsTo
    {
        return $this->belongsTo(VFilial::class, 'filial_baixa');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function usuarioBaixa()
    {
        return $this->belongsTo(User::class, 'usuario_baixa');
    }

    public function transferenciaPneuModelos(): HasMany
    {
        return $this->hasMany(TransferenciaPneusModelos::class, 'id_transferencia_pneu');
    }
}
