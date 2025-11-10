<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferenciaImobilizadoVeiculo extends Model
{
    use LogsActivity;

    const STATUS_TRAFEGO = 'TRAFEGO';

    const STATUS_JURIDICO = 'JURIDICO';

    const STATUS_FROTA = 'FROTA';

    const STATUS_PATRIMONIO = 'PATRIMONIO';

    const STATUS_FILIAL = 'FILIAL';

    const STATUS_CONCLUIDO = 'CONCLUIDO';

    const STATUS_REPROVADO = 'REPROVADO';


    protected $connection = 'pgsql';
    protected $table = 'transferencia_imobilizado_veiculo';

    protected $primaryKey = 'id_transferencia_imobilizado_veiculo';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'observacao',
        'status',
        'data_inicio',
        'data_fim',
        'id_modelo_veiculo',
        'id_usuario',
        'id_veiculo',
        'justificativa',
        'tipo',
        'id_filial_origem',
        'id_filial_destino',
        'anexo_documento',
        'anexo_checklist',
        'id_fornecedor',
        'id_categoria',
        'id_departamento',
        'id_tipo_equipamento',
        'checklist_id',
        'checklist_devo',
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
    ];

    public function modeloVeiculo(): BelongsTo
    {
        return $this->belongsTo(ModeloVeiculo::class, 'id_modelo_veiculo');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaVeiculo::class, 'id_categoria');
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo', 'id_veiculo');
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function departamentoTransferencia(): BelongsTo
    {
        return $this->belongsTo(DepartamentoTransferencia::class, 'status', 'id_departamento_transferencia');
    }

    public function log()
    {
        return $this->hasOne(LogsTransferenciaImobilizadoVeiculo::class, 'id_transferencia_imobilizado_veiculo');
    }

    public function filialOrigem()
    {
        return $this->belongsTo(Filial::class, 'id_filial_origem', 'id');
    }

    public function filialDestino()
    {
        return $this->belongsTo(Filial::class, 'id_filial_destino', 'id');
    }

    public function checklist()
    {
        return $this->belongsTo(CheckList::class, 'checklist_id', 'id');
    }
    public function checklistDevo()
    {
        return $this->belongsTo(CheckList::class, 'checklist_devo', 'id');
    }

    public function getTipoEquipamentoEixo()
    {
        if (!$this->tipoEquipamento) {
            return '-';
        }
        return $this->tipoEquipamento->descricao_tipo . ' Eixo: ' . $this->tipoEquipamento->numero_eixos;
    }

    public function getStatusClassAttribute()
    {
        $status = $this->status;
        $situacao = '';

        if ($status === 2) {
            $situacao = self::STATUS_TRAFEGO;
        } elseif ($status === 3) {
            $situacao = self::STATUS_JURIDICO;
        } elseif ($status === 4) {
            $situacao = self::STATUS_FROTA;
        } elseif ($status === 5) {
            $situacao = self::STATUS_PATRIMONIO;
        } elseif ($status === 8) {
            $situacao = self::STATUS_REPROVADO;
        } elseif ($status === 9) {
            $situacao = self::STATUS_CONCLUIDO;
        } elseif ($status === 10) {
            $situacao = self::STATUS_FILIAL;
        }

        return match ($situacao) {
            self::STATUS_TRAFEGO => 'bg-yellow-100 text-yellow-800',
            self::STATUS_JURIDICO   => 'bg-blue-100 text-blue-800',
            self::STATUS_FROTA                    => 'bg-indigo-100 text-indigo-800',
            self::STATUS_PATRIMONIO => 'bg-purple-100 text-purple-800',
            self::STATUS_FILIAL          => 'bg-orange-100 text-orange-800',
            self::STATUS_CONCLUIDO        => 'bg-green-100 text-green-800',
            self::STATUS_REPROVADO        => 'bg-yellow-200 text-yellow-900', // Cor diferente para aprovação final
            default                                  => 'bg-gray-100 text-gray-800',
        };
    }
}
