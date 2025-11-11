<?php

namespace App\Modules\Imobilizados\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ManutencaoImobilizado extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'manutencao_imobilizado';
    protected $primaryKey = 'id_manutencao_imobilizado';
    public $timestamps = false;

    protected $fillable = [
        'id_filial',
        'id_fornecedor',
        'data_inclusao',
        'data_alteracao',
        'id_usario',
        'situacao',
        'id_produtos_imobilizados',
    ];

    protected $casts = [
        'data_inclusao'     => 'datetime',
        'data_alteracao'    => 'datetime',
    ];

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor', 'id_fornecedor');
    }

    public function filial(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'id_filial', 'id');
    }

    public function manutencaoImobilizadoItens(): BelongsTo
    {
        return $this->belongsTo(ManutencaoImobilizadoItens::class, 'id_manutencao_imobilizado', 'id_manutencao_imobilizado');
    }

    public function ordemServicoPecasImobilizados(): BelongsTo
    {
        return $this->belongsTo(OrdemServicoPecasImobilizados::class, 'id_manutencao_imobilizado', 'id_manutencao_imobilizado');
    }
}
