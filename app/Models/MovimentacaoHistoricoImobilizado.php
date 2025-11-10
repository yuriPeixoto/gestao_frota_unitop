<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimentacaoHistoricoImobilizado extends Model
{
    use LogsActivity;

    protected $table = 'movimentacao_historico_imobilizado';
    protected $primaryKey = 'id_historico_movi_imobilizado';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_produto',
        'id_produto_imobilizado',
        'vida_imobilizado',
        'tipo',
        'id_veiculo',
        'id_os'
    ];

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }
}
