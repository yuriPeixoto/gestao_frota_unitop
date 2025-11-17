<?php

namespace App\Modules\Pneus\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasPermissions;

class DescartePneu extends Model
{
    use HasPermissions;
    use LogsActivity;

    protected $connection = 'pgsql';

    protected $table = 'descartepneu';

    protected $primaryKey = 'id_descarte_pneu';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_pneu',
        'id_tipo_descarte',
        'observacao',
        'id_foto',
        'nome_arquivo',
        'valor_venda_pneu',
        'id_user_alter',
        // ✅ NOVOS CAMPOS ADICIONADOS
        'status_processo',
        'origem',
        'id_manutencao_origem',
        'finalizado_em',
        'finalizado_por',
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        // ✅ NOVOS CASTS PARA OS CAMPOS ADICIONADOS
        'finalizado_em' => 'datetime',
        'valor_venda_pneu' => 'float', // ✅ CORRIGE O ERRO DO number_format
    ];

    // ✅ RELACIONAMENTOS EXISTENTES
    public function tipoDescarte(): BelongsTo
    {
        return $this->belongsTo(TipoDescarte::class, 'id_tipo_descarte');
    }

    public function pneu(): BelongsTo
    {
        return $this->belongsTo(Pneu::class, 'id_pneu');
    }

    // ✅ NOVOS RELACIONAMENTOS
    public function manutencaoOrigem(): BelongsTo
    {
        return $this->belongsTo(ManutencaoPneusEntradaItens::class, 'id_manutencao_origem', 'id_manutencao_pneu_entrada_itens');
    }

    public function finalizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalizado_por');
    }

    // ✅ MUTATOR EXISTENTE PARA VALOR (mantido para compatibilidade com input formatado)
    public function getValorVendaPneuAttribute($value)
    {
        if (! is_null($value) && $value !== '') {
            return 'R$ '.number_format((float) $value, 2, ',', '.');
        }

        return $value;
    }

    public function setValorVendaPneuAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['valor_venda_pneu'] = 0;

            return;
        }

        $value = (string) $value;
        $value = trim($value);

        if (strpos($value, 'R$') !== false) {
            $value = str_replace('R$', '', $value);
            $value = trim($value);
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '.', $value);
        }

        $value = preg_replace('/[^0-9.]/', '', $value);

        if (is_numeric($value)) {
            $floatValue = (float) $value;
            $this->attributes['valor_venda_pneu'] = $floatValue;
        } else {
            $this->attributes['valor_venda_pneu'] = 0;
        }
    }

    // ✅ NOVOS MÉTODOS HELPER
    public function isAguardandoInicio(): bool
    {
        return $this->status_processo === 'aguardando_inicio';
    }

    public function isEmAndamento(): bool
    {
        return $this->status_processo === 'em_andamento';
    }

    public function isFinalizado(): bool
    {
        return $this->status_processo === 'finalizado';
    }

    public function isOrigemManual(): bool
    {
        return $this->origem === 'manual';
    }

    public function isOrigemManutencao(): bool
    {
        return $this->origem === 'manutencao';
    }

    public function temLaudo(): bool
    {
        return ! empty($this->nome_arquivo) || ! empty($this->id_foto);
    }

    // ✅ SCOPES PARA CONSULTAS
    public function scopeAguardandoInicio($query)
    {
        return $query->where('status_processo', 'aguardando_inicio');
    }

    public function scopeEmAndamento($query)
    {
        return $query->where('status_processo', 'em_andamento');
    }

    public function scopeFinalizado($query)
    {
        return $query->where('status_processo', 'finalizado');
    }

    public function scopeOrigemManual($query)
    {
        return $query->where('origem', 'manual');
    }

    public function scopeOrigemManutencao($query)
    {
        return $query->where('origem', 'manutencao');
    }

    public function scopeComLaudo($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('nome_arquivo')
                ->orWhereNotNull('id_foto');
        });
    }

    public function scopeSemLaudo($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('nome_arquivo')
                ->whereNull('id_foto');
        });
    }
}
