<?php

namespace App\Modules\Imobilizados\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class DevolucaoImobilizadoVeiculo extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'devolucao_imobilizado_veiculo';

    protected $primaryKey = 'id_devolucao_imobilizado_veiculo';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'tipo_processo',
        'titulo',
        'observacao',
        'status',
        'data_inicio',
        'data_fim',
        'id_veiculo',
        'id_usuario',
        'justificativa',
        'tipo',
        'id_filial_origem',
        'id_filial_destino',
        'id_modelo_veiculo',
        'observacao_gestor',
        'anexo_documento',
        'anexo_checklist',
        'id_fornecedor',
        'id_categoria',
        'id_ordem_servico',
        'id_departamento',
        'id_sinistro',
        'id_tipo_equipamento',
        'checklist_id',
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
    ];

    public function modeloVeiculo()
    {
        return $this->belongsTo(ModeloVeiculo::class, 'id_modelo_veiculo', 'id_modelo_veiculo');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaVeiculo::class, 'id_categoria');
    }

    public function tipoEquipamento(): BelongsTo
    {
        return $this->belongsTo(TipoEquipamento::class, 'id_tipo_equipamento');
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo', 'id_veiculo');
    }

    public function departamentoTransferencia(): BelongsTo
    {
        return $this->belongsTo(DepartamentoTransferencia::class, 'status', 'id_departamento_transferencia');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function log()
    {
        return $this->hasOne(LogsDevolucaoImobilizadoVeiculo::class, 'id_devolucao_imobilizado_veiculo');
    }

    public function filialOrigem()
    {
        return $this->belongsTo(Filial::class, 'id_filial_origem', 'id');
    }

    public function filialDestino()
    {
        return $this->belongsTo(Filial::class, 'id_filial_destino', 'id');
    }

    public function sinistro()
    {
        return $this->belongsTo(Sinistro::class, 'id_sinistro');
    }

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'id_ordem_servico');
    }
}
