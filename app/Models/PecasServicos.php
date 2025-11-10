<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class PecasServicos extends Model
{
    use LogsActivity;

    protected $table = 'pecasxservicos';
    protected $primaryKey = 'id_pecas_servicos';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_servico',
        'id_peca',
    ];

    public function Servico()
    {
        return $this->belongsTo(Servico::class, 'id_servico', 'id_servico');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_peca', 'id_produto');
    }
}
