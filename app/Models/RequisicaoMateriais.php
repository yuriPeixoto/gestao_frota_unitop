<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequisicaoMateriais extends Model
{
    use LogsActivity;
    use HasPermissions;

    protected $connection = 'pgsql';
    protected $table = 'requisicao_materiais';

    protected $primaryKey = 'id_requisicao_materiais';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_filial',
        'situacao',
        'id_usuario_estoque',
        'observacao',
        'justificativa_de_finalizacao',
        'id_terceiro',
        'id_usuario_solicitante',
        'observacao_solicitante',
        'transferencia_entre_filiais',
        'venda',
        'documento_autorizacao',
        'is_impresso',
        'is_aprovado',
        'data_aprovacao',
        'is_cancelada',
        'id_ordem_servico'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime'
    ];

    public function filial(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'id_filial', 'id');
    }

    public function usuarioSolicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_solicitante', 'id');
    }
}
