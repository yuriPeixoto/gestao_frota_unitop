<?php

namespace App\Modules\Abastecimentos\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class EstoqueCombustivel extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'estoque_combustivel';
    protected $primaryKey = 'id_estoque_combustivel';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_tanque',
        'id_filial',
        'quantidade_em_estoque',
        'data_encerramento',
        'valor_medio',
        'valor_unitario',
        'quantidade_anterior'
    ];

    public function tanque()
    {
        return $this->belongsTo(Tanque::class, 'id_tanque');
    }
}
