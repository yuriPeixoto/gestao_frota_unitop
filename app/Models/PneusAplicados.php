<?php

namespace App\Models;

use App\Traits\LogsActivity;
use App\Traits\ToggleIsActiveOnSoftDelete;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Carbon\Carbon;

class PneusAplicados extends Model
{
    use LogsActivity, SoftDeletes, ToggleIsActiveOnSoftDelete;

    protected $table = 'pneus_aplicados';
    protected $primaryKey = 'id_pneu_aplicado';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_pneu',
        'km_adicionado',
        'km_removido',
        'total_km',
        'id_veiculo_x_pneu',
        'localizacao',
        'sulco_pneu_adicionado',
        'sulco_pneu_removido',
        'origem_operacao',
        'destino',
        'km_remocao',
        'sulco_remocao',
        'is_ativo',
        'deleted_at'  // ✅ Adicionado para permitir mass assignment
    ];

    protected $dates = [
        'data_inclusao',
        'data_alteracao',
        'deleted_at'
    ];

    protected $casts = [
        'is_ativo' => 'boolean',
    ];

    // ✅ RELACIONAMENTOS CORRIGIDOS
    public function pneu(): BelongsTo
    {
        return $this->belongsTo(Pneu::class, 'id_pneu');
    }

    public function veiculoXPneu(): BelongsTo
    {
        return $this->belongsTo(VeiculoXPneu::class, 'id_veiculo_x_pneu');
    }

    // ✅ RELACIONAMENTO CORRIGIDO - usando hasOneThrough corretamente
    public function veiculo(): HasOneThrough
    {
        return $this->hasOneThrough(
            Veiculo::class,        // Modelo final
            VeiculoXPneu::class,   // Modelo intermediário
            'id_veiculo_pneu',     // Foreign key no modelo intermediário (VeiculoXPneu)
            'id_veiculo',          // Foreign key no modelo final (Veiculo)
            'id_veiculo_x_pneu',   // Local key no modelo atual (PneusAplicados)
            'id_veiculo'           // Local key no modelo intermediário (VeiculoXPneu)
        );
    }

    // ✅ ALTERNATIVA MAIS SIMPLES - usando accessor
    public function getVeiculoAttribute()
    {
        return $this->veiculoXPneu?->veiculo;
    }

    // ✅ SCOPES PARA CONSULTAS
    public function scopeAtivos($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeInativos($query)
    {
        return $query->whereNotNull('deleted_at');
    }

    public function scopeAutoSave($query)
    {
        return $query->where('origem_operacao', 'AUTO_SAVE');
    }

    public function scopeManual($query)
    {
        return $query->where('origem_operacao', 'MANUAL');
    }

    public function scopeRemovidosPara($query, $destino)
    {
        return $query->where('destino', $destino)->whereNotNull('km_remocao');
    }

    public function scopePorVeiculo($query, $idVeiculo)
    {
        return $query->whereHas('veiculoXPneu', function ($q) use ($idVeiculo) {
            $q->where('id_veiculo', $idVeiculo);
        });
    }

    public function scopePorLocalizacao($query, $localizacao)
    {
        return $query->where('localizacao', $localizacao);
    }

    public function scopeEntreDatas($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_inclusao', [$dataInicio, $dataFim]);
    }

    // ✅ MÉTODOS HELPER
    public function isAtivo()
    {
        return is_null($this->deleted_at);
    }

    public function isAutoSave()
    {
        return $this->origem_operacao === 'AUTO_SAVE';
    }

    public function getTotalKmRodados()
    {
        if ($this->km_adicionado && $this->km_removido) {
            return $this->km_removido - $this->km_adicionado;
        }
        return null;
    }

    public function getDesgasteSulco()
    {
        if ($this->sulco_pneu_adicionado && $this->sulco_pneu_removido) {
            return $this->sulco_pneu_adicionado - $this->sulco_pneu_removido;
        }
        return null;
    }

    public function getDuracaoAplicacao()
    {
        if ($this->data_inclusao && $this->deleted_at) {
            $inicio = Carbon::parse($this->data_inclusao);
            $fim = Carbon::parse($this->deleted_at);
            return $inicio->diffInDays($fim);
        }

        if ($this->data_inclusao && !$this->deleted_at) {
            $inicio = Carbon::parse($this->data_inclusao);
            return $inicio->diffInDays(Carbon::now()) . ' (ainda ativo)';
        }

        return null;
    }

    public function getStatusDescricao()
    {
        if ($this->isAtivo()) {
            return 'APLICADO';
        }

        return $this->destino ?? 'REMOVIDO';
    }

    public function getResumoAplicacao()
    {
        return [
            'id' => $this->id_pneu_aplicado,
            'pneu_id' => $this->id_pneu,
            'localizacao' => $this->localizacao,
            'status' => $this->getStatusDescricao(),
            'origem_operacao' => $this->origem_operacao,
            'km_inicial' => $this->km_adicionado,
            'km_final' => $this->km_removido,
            'km_rodados' => $this->getTotalKmRodados(),
            'sulco_inicial' => $this->sulco_pneu_adicionado,
            'sulco_final' => $this->sulco_pneu_removido,
            'desgaste_sulco' => $this->getDesgasteSulco(),
            'duracao_dias' => $this->getDuracaoAplicacao(),
            'data_aplicacao' => $this->data_inclusao,
            'data_remocao' => $this->deleted_at,
            'ativo' => $this->isAtivo()
        ];
    }

    // ✅ MUTATORS E ACCESSORS
    public function setDataInclusaoAttribute($value)
    {
        $this->attributes['data_inclusao'] = $value ? Carbon::parse($value) : null;
    }

    public function setDataAlteracaoAttribute($value)
    {
        $this->attributes['data_alteracao'] = $value ? Carbon::parse($value) : null;
    }

    public function getDataInclusaoFormattedAttribute()
    {
        return $this->data_inclusao ? $this->data_inclusao->format('d/m/Y H:i:s') : null;
    }

    public function getDataAlteracaoFormattedAttribute()
    {
        return $this->data_alteracao ? $this->data_alteracao->format('d/m/Y H:i:s') : null;
    }

    // ✅ EVENTOS DO MODEL
    protected static function boot()
    {
        parent::boot();

        // Ao criar um novo registro
        static::creating(function ($pneuAplicado) {
            if (!$pneuAplicado->data_inclusao) {
                $pneuAplicado->data_inclusao = Carbon::now();
            }
            $pneuAplicado->data_alteracao = Carbon::now();
        });

        // Ao atualizar um registro
        static::updating(function ($pneuAplicado) {
            $pneuAplicado->data_alteracao = Carbon::now();
        });

        // Ao fazer soft delete (remoção)
        static::deleting(function ($pneuAplicado) {
            if (!$pneuAplicado->isForceDeleting()) {
                $pneuAplicado->data_alteracao = Carbon::now();
                $pneuAplicado->save();
            }
        });

        // Ao restaurar um registro
        static::restoring(function ($pneuAplicado) {
            $pneuAplicado->data_alteracao = Carbon::now();

            // Limpar campos de remoção ao restaurar
            $pneuAplicado->km_removido = null;
            $pneuAplicado->sulco_pneu_removido = null;
            $pneuAplicado->destino = null;
            $pneuAplicado->km_remocao = null;
            $pneuAplicado->sulco_remocao = null;
        });
    }

    // ✅ VALIDAÇÕES
    public function validarAplicacao()
    {
        $erros = [];

        if (!$this->id_pneu) {
            $erros[] = 'ID do pneu é obrigatório';
        }

        if (!$this->id_veiculo_x_pneu) {
            $erros[] = 'Relação veículo-pneu é obrigatória';
        }

        if (!$this->localizacao) {
            $erros[] = 'Localização é obrigatória';
        }

        // Verificar se já existe pneu ativo na mesma localização
        $pneuNaLocalizacao = static::where('id_veiculo_x_pneu', $this->id_veiculo_x_pneu)
            ->where('localizacao', $this->localizacao)
            ->where('id_pneu_aplicado', '!=', $this->id_pneu_aplicado)
            ->whereNull('deleted_at')
            ->first();

        if ($pneuNaLocalizacao) {
            $erros[] = "Já existe um pneu aplicado na localização {$this->localizacao}";
        }

        return [
            'valido' => empty($erros),
            'erros' => $erros
        ];
    }

    // ✅ MÉTODOS ESTÁTICOS PARA CONSULTAS COMUNS
    public static function buscarPorVeiculoELocalizacao($idVeiculo, $localizacao)
    {
        return static::whereHas('veiculoXPneu', function ($q) use ($idVeiculo) {
            $q->where('id_veiculo', $idVeiculo);
        })
            ->where('localizacao', $localizacao)
            ->whereNull('deleted_at')
            ->first();
    }

    public static function buscarHistoricoPneu($idPneu)
    {
        return static::where('id_pneu', $idPneu)
            ->withTrashed()
            ->orderBy('data_inclusao', 'desc')
            ->get();
    }

    public static function estatisticasPorVeiculo($idVeiculo)
    {
        $veiculoXPneu = VeiculoXPneu::where('id_veiculo', $idVeiculo)
            ->where('situacao', true)
            ->first();

        if (!$veiculoXPneu) {
            return [];
        }

        return [
            'total_aplicados' => static::where('id_veiculo_x_pneu', $veiculoXPneu->id_veiculo_pneu)
                ->whereNull('deleted_at')->count(),
            'total_removidos' => static::where('id_veiculo_x_pneu', $veiculoXPneu->id_veiculo_pneu)
                ->whereNotNull('deleted_at')->count(),
            'por_origem' => static::where('id_veiculo_x_pneu', $veiculoXPneu->id_veiculo_pneu)
                ->groupBy('origem_operacao')
                ->selectRaw('origem_operacao, count(*) as total')
                ->pluck('total', 'origem_operacao'),
            'por_destino' => static::where('id_veiculo_x_pneu', $veiculoXPneu->id_veiculo_pneu)
                ->whereNotNull('destino')
                ->groupBy('destino')
                ->selectRaw('destino, count(*) as total')
                ->pluck('total', 'destino')
        ];
    }
}
