<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManutencaoImobilizadoItens extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'manutencao_imobilizado_itens';
    protected $primaryKey = 'id_manutencao_imobilizado_itens';
    public $timestamps = false;

    protected $fillable = [
        'id_manutencao_imobilizado',
        'id_produtos_imobilizados',
        'id_tipo_manutencao_imobilizado',
        'data_inclusao',
        'data_alteracao',
    ];

    protected $casts = [
        'data_inclusao'     => 'datetime',
        'data_alteracao'    => 'datetime',
    ];


    public function produtoImobilizado()
    {
        return $this->belongsTo(ProdutosImobilizados::class, 'id_produtos_imobilizados');
    }


    public function tipoManutencaoImobilizado(): BelongsTo
    {
        return $this->belongsTo(TipoManutencaoImobilizado::class, 'id_tipo_manutencao_imobilizado', 'id_tipo_manutencao_imobilizado');
    }

    public function produto()
    {
        return $this->hasOneThrough(
            Produto::class,                  // Modelo final
            ProdutosImobilizados::class,       // Modelo intermediário
            'id',                            // id em produtos_imobilizados
            'id',                            // id em produto
            'id_produtos_imobilizados',      // chave estrangeira em manutencao_imobilizado_itens
            'id_produto'                     // chave estrangeira em produtos_imobilizados
        );
    }
}
