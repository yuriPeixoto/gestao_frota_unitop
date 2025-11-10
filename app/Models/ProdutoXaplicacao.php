<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdutoXaplicacao extends Model
{
    use LogsActivity;

    protected $table = 'produtoxaplicacao';
    protected $primaryKey = 'id_produto_aplicacao';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_produto',
        'id_modelo_veiculo',
    ];

    public function modelo(): BelongsTo
    {
        return $this->belongsTo(ModeloVeiculo::class, 'id_modelo_veiculo', 'id_modelo_veiculo');
    }
}
