<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class DevolucaoMatrizItens extends Model
{
    use LogsActivity;

    protected $table = 'devolucao_matriz_itens';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_devolucao_matriz',
        'id_produto',
        'qtd_disponivel_envio',
        'qtd_enviada',
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'qtd_disponivel_envio' => 'integer',
        'qtd_enviada' => 'integer',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_produto');
    }

    public function estoque()
    {
        return $this->belongsTo(ProdutosPorFilial::class, 'id_produto', 'id_produto_unitop')
            ->where('id_filial', GetterFilial());
    }
}