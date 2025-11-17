<?php

namespace App\Modules\Pneus\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequisicaoPneu extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'requisicao_pneu';

    protected $primaryKey = 'id_requisicao_pneu';
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
        'id_filial_destino',
        'venda',
        'documento_autorizacao',
        'is_impressao',
        'is_aprovado',
        'data_aprovacao',
        'is_cancelada',
        'id_ordem_servico',
        'id_soliciatacao_pecas'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'data_aprovacao' => 'datetime',
        'venda' => 'boolean',
        'is_impressao' => 'boolean',
        'is_aprovado' => 'boolean',
        'is_cancelada' => 'boolean',
        'transferencia_entre_filiais' => 'boolean',
        'id_filial_destino' => 'integer',
    ];



    public function filial(): BelongsTo
    {
        return $this->belongsTo(VFilial::class, 'id_filial', 'id');
    }

    public function filialDestino(): BelongsTo
    {
        return $this->belongsTo(VFilial::class, 'id_filial_destino', 'id');
    }

    public function usuarioEstoque(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_estoque');
    }

    public function usuarioSolicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_solicitante');
    }

    public function usuarioVendas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_solicitante');
    }

    public function terceiro(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'id_terceiro');
    }

    public function requisicaoPneuModelos(): HasMany
    {
        return $this->hasMany(RequisicaoPneuModelos::class, 'id_requisicao_pneu', 'id_requisicao_pneu');
    }
}
