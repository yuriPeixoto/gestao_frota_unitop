<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VconferenciaRotativoDiario extends Model
{
    protected $connection = 'pgsql';
    // necessário para o soft delete identificar o campo

    protected $table = 'v_conferencia_rotativo_diario';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'id_produto',
        'codigo_produto',
        'descricao_produto',
        'valor_medio',
        'quantidade_atual_produto',
        'localizacao_produto',
        'contagem',
        'data_baixa',
        'id_filial',
        'id_estoque_produto'
    ];

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'id_filial');
    }
    public function estoque()
    {
        return $this->belongsTo(Estoque::class, 'id_estoque_produto');
    }
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_produto');
    }
}
