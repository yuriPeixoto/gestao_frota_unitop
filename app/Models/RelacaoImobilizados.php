<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RelacaoImobilizados extends Model
{
    use LogsActivity;
    use HasPermissions;

    protected $connection = 'pgsql';
    protected $table = 'relacao_imobilizados';

    protected $primaryKey = 'id_relacao_imobilizados';

    public $timestamps = false;

    protected $fillable = [
        'motivo_transferencia',
        'id_usuario',
        'id_filial',
        'data_inclusao',
        'data_alteracao',
        'status',
        'aprovado',
        'finalizado_aprovacao',
        'aprovado_gestor',
        'caminho_imobilizado',
        'id_departamento',
        'id_veiculo',
        'id_orderm_servico',
        'situacao',
        'id_filial_manutencao',
        'id_usuario_estoque',
        'observacao_lider',
        'observacao_gestor',
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime'
    ];

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo', 'id_veiculo');
    }

    public function filialManutencao(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'id_filial_manutencao', 'id');
    }

    public function filial(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'id_filial', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    public function relacaoImobilizadosItens(): HasMany
    {
        return $this->hasMany(RelacaoImobilizadosItens::class, 'id_relacao_imobilizados', 'id_relacao_imobilizados');
    }

    public function transferenciaEstoqueImobilizadoAux(): BelongsTo
    {
        return $this->belongsTo(TransferenciaEstoqueImobilizadoAux::class, 'id_relacao_imobilizados', 'id_relacao_novo');
    }
}
