<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequisicaoMateriaisItens extends Model
{
    use LogsActivity;
    use HasPermissions;

    protected $connection = 'pgsql';
    protected $table = 'requisicao_materiais_itens';

    protected $primaryKey = 'id_requisicao_materiais_itens';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_requisicao_materiais',
        'id_produto',
        'quantidade'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime'
    ];

    public function requisicaoMateriais(): BelongsTo
    {
        return $this->belongsTo(RequisicaoMateriais::class, 'id_requisicao_materiais');
    }
}
