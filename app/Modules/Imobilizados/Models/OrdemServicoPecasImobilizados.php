<?php

namespace App\Modules\Imobilizados\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdemServicoPecasImobilizados extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'ordem_servico_pecas_imobilizados';

    protected $primaryKey = 'id_ordem_servico_pecas_imobilizado';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_manutencao_imobilizado',
        'id_produto',
        'quantidade',
        'ja_solicitada',
        'data_solicitacao',
        'situacao_pecas',
    ];

    protected $casts = [
        'data_inclusao'     => 'datetime',
        'data_alteracao'    => 'datetime',
        'data_solicitacao'  => 'datetime',
    ];

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'id_produto');
    }
    public function manutencaoImobilizado(): BelongsTo
    {
        return $this->belongsTo(ManutencaoImobilizado::class, 'id_manutencao_imobilizado', 'id_manutencao_imobilizado');
    }
}
