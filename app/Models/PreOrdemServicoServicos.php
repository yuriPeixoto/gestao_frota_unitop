<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreOrdemServicoServicos extends Model
{
    use LogsActivity;

    protected $table = 'pre_os_servicos';
    protected $primaryKey = 'id_pre_os_servicos';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_servico',
        'id_pre_os',
        'imagem_servico_pre_os',
        'observacao',
    ];

    public function servico()
    {
        return $this->belongsTo(Servico::class, 'id_servico', 'id_servico');
    }

    public function preOS(): BelongsTo
    {
        return $this->belongsTo(Servico::class, 'id_servico', 'id_servico');
    }
}
